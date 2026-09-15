const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');
const { loadDotenv, requireEnv } = require('./lib/env');

loadDotenv();

const STATE_PATH = path.join(__dirname, '.auth', 'yahoo-state.json');
const FANTASY_URL = 'https://football.fantasysports.yahoo.com/';

async function hasValidSession(browser) {
    if (!fs.existsSync(STATE_PATH)) {
        return false;
    }
    const context = await browser.newContext({ storageState: STATE_PATH });
    const page = await context.newPage();
    await page.goto(FANTASY_URL, { waitUntil: 'domcontentloaded' });
    const stillLoggedIn = !page.url().includes('login.yahoo.com');
    await context.close();
    return stillLoggedIn;
}

// Yahoo's push-notification 2-step-verification challenge screen. Seen after
// submitting valid credentials on an account with 2FA always on.
const PUSH_CHALLENGE_PATTERN = /login\.yahoo\.com\/account\/challenge\/push/;
// How long to give a human to notice and approve the push notification on
// their phone, plus Yahoo's own redirect lag afterward. 120s proved too tight
// in practice (a real approval can still lose the race), so this is generous.
const PUSH_APPROVAL_TIMEOUT_MS = 180000;
// Yahoo's login page redirects back to whatever URL is passed as `.done`
// after a successful login. The bare https://login.yahoo.com/ defaults that
// to https://www.yahoo.com/, which strands a successful login on the generic
// homepage instead of the Fantasy Sports app. Fantasy Sports' own "Sign in"
// link sets `.done` to itself, so do the same here.
const LOGIN_URL = `https://login.yahoo.com/?.done=${encodeURIComponent(FANTASY_URL)}`;

function reachedFantasySite(page) {
    return page.url().includes('fantasysports.yahoo.com');
}

async function submitCredentials(page, username, password) {
    await page.goto(LOGIN_URL, { waitUntil: 'domcontentloaded' });
    await page.fill('input#username', username);
    await page.click('button[name="signin"]');
    await page.waitForSelector('input#login-passwd', { timeout: 15000 });
    await page.fill('input#login-passwd', password);
    await page.click('button[name="validate"]');

    try {
        await page.waitForLoadState('networkidle', { timeout: 15000 });
    } catch (err) {
        // Yahoo/fantasy pages can keep background network activity (ads,
        // polling) alive indefinitely; fall back to checking the URL below
        // rather than treating a lingering "idle" wait as a login failure.
    }
}

async function finishLogin(context, page) {
    if (!reachedFantasySite(page)) {
        await context.close();
        throw new Error(
            'Yahoo did not complete login automatically — it likely presented ' +
            'an additional verification challenge, or the post-login redirect ' +
            'did not land on Fantasy Sports as expected. Automated login cannot ' +
            'proceed; see the "Credentials & session" fallback in the design spec.'
        );
    }

    fs.mkdirSync(path.dirname(STATE_PATH), { recursive: true });
    await context.storageState({ path: STATE_PATH });
    await context.close();
}

async function loginWithCredentials(browser) {
    const username = requireEnv('YAHOO_USERNAME');
    const password = requireEnv('YAHOO_PASSWORD');

    let context = await browser.newContext();
    let page = await context.newPage();
    await submitCredentials(page, username, password);

    if (!PUSH_CHALLENGE_PATTERN.test(page.url())) {
        await finishLogin(context, page);
        return;
    }

    // Headless credential submission landed on Yahoo's push-notification 2FA
    // challenge. That challenge is approved on the user's phone and, per the
    // design spec's "Credentials & session" fallback, only resolves reliably
    // against a visible browser window — close the headless attempt and redo
    // the same submission in a headed one so the user can see and approve it.
    await context.close();

    const headedBrowser = await chromium.launch({ headless: false });
    try {
        context = await headedBrowser.newContext();
        page = await context.newPage();
        await submitCredentials(page, username, password);

        if (PUSH_CHALLENGE_PATTERN.test(page.url())) {
            console.error(
                `Approve the push notification on your phone now (Yahoo Fantasy ` +
                `Football app) — waiting up to ${PUSH_APPROVAL_TIMEOUT_MS / 1000}s...`
            );
            const deadline = Date.now() + PUSH_APPROVAL_TIMEOUT_MS;
            while (Date.now() < deadline && !reachedFantasySite(page)) {
                await page.waitForTimeout(1000);
            }
            if (!reachedFantasySite(page)) {
                await context.close();
                throw new Error(
                    `Push notification was not approved within ${PUSH_APPROVAL_TIMEOUT_MS / 1000}s ` +
                    '— try again.'
                );
            }
        }

        await finishLogin(context, page);
    } finally {
        await headedBrowser.close();
    }
}

async function getAuthenticatedContext(browser) {
    if (!(await hasValidSession(browser))) {
        await loginWithCredentials(browser);
    }
    return browser.newContext({ storageState: STATE_PATH });
}

if (require.main === module) {
    (async () => {
        const browser = await chromium.launch({ headless: true });
        try {
            const context = await getAuthenticatedContext(browser);
            const page = await context.newPage();
            await page.goto(FANTASY_URL, { waitUntil: 'domcontentloaded' });
            console.log('Logged in. Page title:', await page.title());
            await context.close();
        } finally {
            await browser.close();
        }
    })().catch((err) => {
        console.error(err.message);
        process.exitCode = 1;
    });
}

module.exports = { getAuthenticatedContext, STATE_PATH };
