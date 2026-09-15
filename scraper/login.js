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

async function loginWithCredentials(browser) {
    const username = requireEnv('YAHOO_USERNAME');
    const password = requireEnv('YAHOO_PASSWORD');

    const context = await browser.newContext();
    const page = await context.newPage();

    await page.goto('https://login.yahoo.com/', { waitUntil: 'domcontentloaded' });
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

    if (page.url().includes('login.yahoo.com')) {
        await context.close();
        throw new Error(
            'Yahoo did not complete login automatically — it likely presented ' +
            'an additional verification challenge. Automated login cannot proceed; ' +
            'see the "Credentials & session" fallback in the design spec.'
        );
    }

    fs.mkdirSync(path.dirname(STATE_PATH), { recursive: true });
    await context.storageState({ path: STATE_PATH });
    await context.close();
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
