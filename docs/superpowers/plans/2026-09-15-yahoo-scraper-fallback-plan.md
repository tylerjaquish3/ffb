# Yahoo Web-Scrape Fallback Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the infrastructure for a local Playwright-based fallback to the Yahoo Fantasy Sports API — scraper scaffold, login/session handling, a discovery tool, and the admin-page wiring to invoke it — without yet implementing any of the 5 sections' real data extraction (that happens in follow-up increments, one per section, once each section's real Yahoo page structure is discovered via the tool built in Task 3).

**Architecture:** A standalone Node project (`scraper/`) drives a real Playwright/Chromium session logged into Yahoo, independent of `suntown`'s existing Node setup. `admin.php`'s existing Yahoo API tab gets a mode toggle; in "Web Scrape" mode it calls a new `yahooScrapeRequest.php`, which shells out to `scraper/scrape.js` via `proc_open` and gets back a normalized JSON blob per section. `scrape.js` itself is a thin dispatcher over per-section extractor functions, currently empty — each is added in its own follow-up task after the section's real Yahoo page structure is inspected.

**Tech Stack:** Node.js 22 (via nvm, not on default PATH — see Task 1), Playwright (Chromium), `dotenv`, PHP 8 (`proc_open`), plain jQuery (existing `admin.php` pattern), `node:test` for the pieces of Node code that are pure logic.

**Spec:** `docs/superpowers/specs/2026-09-15-yahoo-scraper-fallback-design.md`

## Global Constraints

- **Local-only.** This scraper never runs against production — only against the local dev environment (Valet + local `database/ffb.sqlite`). No production hosting changes.
- **Credentials** (`YAHOO_USERNAME`, `YAHOO_PASSWORD`) live in `ffb/.env.local` (repo root), loaded via `dotenv`. `.env.local` is gitignored — do not commit it, do not print its contents.
- **Never run `git add`/`git commit`/any git staging or committing command.** The user handles all commits themselves. Every task below ends at "verify it works" — stop there.
- **`scraper/` is a standalone Node project** — its own `package.json`/`node_modules`, unrelated to `suntown`'s Vite/Tailwind Node setup. Node itself is installed via nvm at `~/.nvm/versions/node/v22.23.0/bin` and is **not** on the default shell PATH — every command below that invokes `node`/`npm`/`npx` must either run in a shell that has sourced nvm, or use the full path.
- **No automated PHP test suite exists in this repo** (plain PHP, no phpunit) — PHP changes are verified manually (curl or a browser), consistent with how the rest of the codebase is tested.
- **This plan covers infrastructure only** (Tasks 1-4). The 5 data sections (`yahoo_ids`, `team_names`, `matchups`, `rosters`, `trades`) are NOT implemented here — `scrape.js`'s extractor table starts empty and stays empty at the end of this plan. Each section is added in a separate follow-up task once its real Yahoo page structure has been inspected via the tool built in Task 3.

---

### Task 1: Scraper project scaffold + shared helpers

**Files:**
- Create: `scraper/package.json`
- Create: `scraper/lib/args.js`
- Create: `scraper/lib/args.test.js`
- Create: `scraper/lib/env.js`
- Create: `scraper/lib/env.test.js`
- Modify: `.gitignore`

**Interfaces:**
- Produces: `parseArgs(argv: string[]): Record<string, string>` from `scraper/lib/args.js` — throws `Error` on any argument not matching `--key=value`.
- Produces: `loadDotenv(): void` and `requireEnv(name: string, env?: Record<string, string>): string` from `scraper/lib/env.js` — `requireEnv` throws `Error` when `name` is missing from `env` (defaults to `process.env`).

- [ ] **Step 1: Create the scraper package**

Create `scraper/package.json`:

```json
{
  "name": "ffb-yahoo-scraper",
  "version": "1.0.0",
  "private": true,
  "description": "Local Playwright fallback for pulling Yahoo Fantasy Football data when the OAuth API path is unavailable.",
  "scripts": {
    "postinstall": "playwright install chromium",
    "test": "node --test lib/"
  },
  "dependencies": {
    "playwright": "^1.48.0",
    "dotenv": "^16.4.5"
  }
}
```

- [ ] **Step 2: Install dependencies (downloads Chromium via postinstall)**

Run (using the nvm-managed node/npm):
```bash
export PATH="$HOME/.nvm/versions/node/v22.23.0/bin:$PATH"
cd scraper && npm install
```
Expected: completes without error; `scraper/node_modules/playwright` exists; Chromium download logged near the end.

- [ ] **Step 3: Add gitignore entries**

Add to `.gitignore` (new lines, anywhere in the file):
```
scraper/node_modules
scraper/.auth
scraper/inspection-output
```

- [ ] **Step 4: Write the failing test for `parseArgs`**

Create `scraper/lib/args.test.js`:
```js
const test = require('node:test');
const assert = require('node:assert/strict');
const { parseArgs } = require('./args');

test('parses --key=value pairs', () => {
    const result = parseArgs(['--section=rosters', '--year=2026', '--week=3']);
    assert.deepEqual(result, { section: 'rosters', year: '2026', week: '3' });
});

test('throws on a malformed argument', () => {
    assert.throws(() => parseArgs(['--bad']), /Unrecognized argument/);
});
```

- [ ] **Step 5: Run it to confirm it fails**

```bash
export PATH="$HOME/.nvm/versions/node/v22.23.0/bin:$PATH"
cd scraper && node --test lib/args.test.js
```
Expected: FAIL — `Cannot find module './args'` (the file doesn't exist yet).

- [ ] **Step 6: Implement `parseArgs`**

Create `scraper/lib/args.js`:
```js
function parseArgs(argv) {
    const args = {};
    for (const raw of argv) {
        const match = /^--([^=]+)=(.*)$/.exec(raw);
        if (!match) {
            throw new Error(`Unrecognized argument: ${raw}. Expected --key=value.`);
        }
        args[match[1]] = match[2];
    }
    return args;
}

module.exports = { parseArgs };
```

- [ ] **Step 7: Run it to confirm it passes**

```bash
export PATH="$HOME/.nvm/versions/node/v22.23.0/bin:$PATH"
cd scraper && node --test lib/args.test.js
```
Expected: PASS, 2 tests.

- [ ] **Step 8: Write the failing test for `requireEnv`**

Create `scraper/lib/env.test.js`:
```js
const test = require('node:test');
const assert = require('node:assert/strict');
const { requireEnv } = require('./env');

test('requireEnv returns the value when present', () => {
    assert.equal(requireEnv('FOO', { FOO: 'bar' }), 'bar');
});

test('requireEnv throws when missing', () => {
    assert.throws(() => requireEnv('FOO', {}), /Missing required env var FOO/);
});
```

- [ ] **Step 9: Run it to confirm it fails**

```bash
export PATH="$HOME/.nvm/versions/node/v22.23.0/bin:$PATH"
cd scraper && node --test lib/env.test.js
```
Expected: FAIL — `Cannot find module './env'`.

- [ ] **Step 10: Implement `env.js`**

Create `scraper/lib/env.js`:
```js
const path = require('path');
const dotenv = require('dotenv');

function loadDotenv() {
    dotenv.config({ path: path.join(__dirname, '..', '..', '.env.local') });
}

function requireEnv(name, env = process.env) {
    const value = env[name];
    if (!value) {
        throw new Error(`Missing required env var ${name} in .env.local`);
    }
    return value;
}

module.exports = { loadDotenv, requireEnv };
```

- [ ] **Step 11: Run both test files to confirm everything passes**

```bash
export PATH="$HOME/.nvm/versions/node/v22.23.0/bin:$PATH"
cd scraper && npm test
```
Expected: PASS, 4 tests total, 0 failures.

---

### Task 2: Yahoo login/session module

**Files:**
- Create: `scraper/login.js`

**Interfaces:**
- Consumes: `loadDotenv()`, `requireEnv(name)` from `scraper/lib/env.js` (Task 1).
- Produces: `getAuthenticatedContext(browser: Browser): Promise<BrowserContext>` — returns a Playwright context with a valid logged-in Yahoo session (reusing saved state if valid, logging in fresh otherwise). `STATE_PATH: string` — the absolute path to the saved session-state file, for use by later scripts/tests that need to know where it lives.

- [ ] **Step 1: Write `login.js`**

Create `scraper/login.js`:
```js
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
    await page.fill('input#login-username', username);
    await page.click('button#login-signin, input#login-signin');
    await page.waitForSelector('input#login-passwd', { timeout: 15000 });
    await page.fill('input#login-passwd', password);
    await page.click('button#login-signin, input#login-signin');

    await page.waitForLoadState('networkidle');

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
```

- [ ] **Step 2: Run it as a standalone check**

```bash
export PATH="$HOME/.nvm/versions/node/v22.23.0/bin:$PATH"
cd scraper && node login.js
```
Expected: prints `Logged in. Page title: ...` (a real Yahoo Fantasy page title, not an error), and `scraper/.auth/yahoo-state.json` now exists.

If it instead prints the "additional verification challenge" error: the credentials or the login page's field selectors (`input#login-username`, `input#login-passwd`, `button#login-signin`) need adjusting — Yahoo's login page structure was written from current public knowledge of it, not confirmed against this account, so this step is the actual verification of whether those selectors still hold. Adjust the selectors in `loginWithCredentials` to match what's really on the page (inspect it in a real browser) and re-run until it succeeds.

- [ ] **Step 3: Run it a second time to confirm session reuse**

```bash
export PATH="$HOME/.nvm/versions/node/v22.23.0/bin:$PATH"
cd scraper && node login.js
```
Expected: same success output, but this run should be noticeably faster (it reused `.auth/yahoo-state.json` via `hasValidSession` instead of logging in fresh).

---

### Task 3: Discovery tool (`inspect.js`)

**Files:**
- Create: `scraper/lib/sectionUrls.js`
- Create: `scraper/lib/sectionUrls.test.js`
- Create: `scraper/inspect.js`

**Interfaces:**
- Consumes: `getAuthenticatedContext(browser)` from `scraper/login.js` (Task 2); `parseArgs(argv)` from `scraper/lib/args.js` (Task 1).
- Produces: `resolveUrl(args: Record<string,string>): string` from `scraper/lib/sectionUrls.js` — used by `inspect.js` (and, later, by `scrape.js`'s real extractors) to turn `{section, 'league-id', week, manager, url}` into the Yahoo page URL to load, with an explicit `--url` override always taking precedence over the section's default.

- [ ] **Step 1: Write the failing tests for `resolveUrl`**

Create `scraper/lib/sectionUrls.test.js`:
```js
const test = require('node:test');
const assert = require('node:assert/strict');
const { resolveUrl } = require('./sectionUrls');

test('builds the default team_names URL from league-id', () => {
    const url = resolveUrl({ section: 'team_names', 'league-id': '18261' });
    assert.equal(url, 'https://football.fantasysports.yahoo.com/f1/18261');
});

test('builds the default rosters URL from league-id, manager, and week', () => {
    const url = resolveUrl({ section: 'rosters', 'league-id': '18261', manager: '4', week: '3' });
    assert.equal(url, 'https://football.fantasysports.yahoo.com/f1/18261/4?week=3');
});

test('an explicit --url always wins over the default', () => {
    const url = resolveUrl({ section: 'rosters', url: 'https://example.com/override' });
    assert.equal(url, 'https://example.com/override');
});

test('throws a clear error for an unknown section with no override', () => {
    assert.throws(() => resolveUrl({ section: 'not_a_real_section' }), /No default URL known/);
});
```

- [ ] **Step 2: Run it to confirm it fails**

```bash
export PATH="$HOME/.nvm/versions/node/v22.23.0/bin:$PATH"
cd scraper && node --test lib/sectionUrls.test.js
```
Expected: FAIL — `Cannot find module './sectionUrls'`.

- [ ] **Step 3: Implement `sectionUrls.js`**

Create `scraper/lib/sectionUrls.js`:
```js
const SECTION_URLS = {
    yahoo_ids: ({ leagueId }) => `https://football.fantasysports.yahoo.com/f1/${leagueId}`,
    team_names: ({ leagueId }) => `https://football.fantasysports.yahoo.com/f1/${leagueId}`,
    matchups: ({ leagueId, week }) => `https://football.fantasysports.yahoo.com/f1/${leagueId}/scoreboard?week=${week}`,
    rosters: ({ leagueId, manager, week }) => `https://football.fantasysports.yahoo.com/f1/${leagueId}/${manager}?week=${week}`,
    trades: ({ leagueId }) => `https://football.fantasysports.yahoo.com/f1/${leagueId}/transactions?scope=all&type=trade`,
};

function resolveUrl(args) {
    if (args.url) {
        return args.url;
    }
    const builder = SECTION_URLS[args.section];
    if (!builder) {
        throw new Error(
            `No default URL known for section "${args.section}". ` +
            `Pass --url=<the actual Yahoo page URL> to override.`
        );
    }
    return builder({ leagueId: args['league-id'], week: args.week, manager: args.manager });
}

module.exports = { resolveUrl, SECTION_URLS };
```

- [ ] **Step 4: Run it to confirm it passes**

```bash
export PATH="$HOME/.nvm/versions/node/v22.23.0/bin:$PATH"
cd scraper && node --test lib/sectionUrls.test.js
```
Expected: PASS, 4 tests.

- [ ] **Step 5: Write `inspect.js`**

Create `scraper/inspect.js`:
```js
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');
const { getAuthenticatedContext } = require('./login');
const { parseArgs } = require('./lib/args');
const { resolveUrl } = require('./lib/sectionUrls');

async function main() {
    const args = parseArgs(process.argv.slice(2));
    if (!args.section) {
        throw new Error('Missing required --section=<name> argument.');
    }

    const url = resolveUrl(args);
    const outDir = path.join(__dirname, 'inspection-output', `${args.section}-${Date.now()}`);
    const networkDir = path.join(outDir, 'network');
    fs.mkdirSync(networkDir, { recursive: true });

    const browser = await chromium.launch({ headless: true });
    try {
        const context = await getAuthenticatedContext(browser);
        const page = await context.newPage();

        let responseCount = 0;
        page.on('response', async (response) => {
            const contentType = response.headers()['content-type'] || '';
            if (!response.url().includes('yahoo.com') || !contentType.includes('json')) {
                return;
            }
            try {
                const body = JSON.parse(await response.text());
                responseCount += 1;
                const fileName = String(responseCount).padStart(3, '0') + '.json';
                fs.writeFileSync(
                    path.join(networkDir, fileName),
                    JSON.stringify({ url: response.url(), status: response.status(), body }, null, 2)
                );
            } catch (err) {
                // Content-type said JSON but body didn't parse — not useful, skip it.
            }
        });

        await page.goto(url, { waitUntil: 'networkidle', timeout: 30000 });
        await page.waitForTimeout(2000);

        fs.writeFileSync(path.join(outDir, 'page.html'), await page.content());
        await context.close();

        console.log(`Loaded: ${url}`);
        console.log(`Wrote inspection output to ${outDir}`);
        console.log(`Captured ${responseCount} JSON network response(s) in ${networkDir}`);
    } finally {
        await browser.close();
    }
}

main().catch((err) => {
    console.error(err.message);
    process.exitCode = 1;
});
```

- [ ] **Step 6: Run it against a real section to confirm it works end-to-end**

```bash
export PATH="$HOME/.nvm/versions/node/v22.23.0/bin:$PATH"
cd scraper && node inspect.js --section=team_names --league-id=18261
```
Expected: prints the loaded URL, the output directory, and a captured-response count; `scraper/inspection-output/team_names-<timestamp>/page.html` exists and is non-empty; the `network/` subfolder contains `.json` files (or the count is 0 with a clear message, if Yahoo's team page happens not to make separate JSON calls — either outcome is a valid result of this step, not a failure of the tool).

---

### Task 4: Admin UI mode toggle + `yahooScrapeRequest.php` + `scrape.js` dispatcher

**Files:**
- Create: `scraper/lib/dispatch.js`
- Create: `scraper/lib/dispatch.test.js`
- Create: `scraper/scrape.js`
- Create: `yahooScrapeRequest.php`
- Modify: `yahooApi.php` (mode toggle + wrap the OAuth-only fields)
- Modify: `admin.php` (JS: mode-aware submit + endpoint selection)

**Interfaces:**
- Produces: `getExtractor(extractors: Record<string, Function>, section: string): Function` from `scraper/lib/dispatch.js` — throws `Error` with message `No scrape extractor implemented yet for section "<section>".` when `section` isn't a key in `extractors`.
- Produces: `scrape.js` — a CLI entry point (`node scrape.js --section=X --year=Y [--weeks=1,2] [--manager=Z]`) that writes one JSON blob to stdout on success, or a message to stderr + exit code 1 on failure. Its `extractors` table is empty at the end of this task — no section is registered yet.

- [ ] **Step 1: Write the failing test for the dispatcher**

Create `scraper/lib/dispatch.test.js`:
```js
const test = require('node:test');
const assert = require('node:assert/strict');
const { getExtractor } = require('./dispatch');

test('returns the matching extractor', () => {
    const fake = () => {};
    assert.equal(getExtractor({ team_names: fake }, 'team_names'), fake);
});

test('throws a clear error when no extractor is registered', () => {
    assert.throws(
        () => getExtractor({}, 'rosters'),
        /No scrape extractor implemented yet for section "rosters"/
    );
});
```

- [ ] **Step 2: Run it to confirm it fails**

```bash
export PATH="$HOME/.nvm/versions/node/v22.23.0/bin:$PATH"
cd scraper && node --test lib/dispatch.test.js
```
Expected: FAIL — `Cannot find module './dispatch'`.

- [ ] **Step 3: Implement `dispatch.js`**

Create `scraper/lib/dispatch.js`:
```js
function getExtractor(extractors, section) {
    const extractor = extractors[section];
    if (!extractor) {
        throw new Error(`No scrape extractor implemented yet for section "${section}".`);
    }
    return extractor;
}

module.exports = { getExtractor };
```

- [ ] **Step 4: Run it to confirm it passes**

```bash
export PATH="$HOME/.nvm/versions/node/v22.23.0/bin:$PATH"
cd scraper && node --test lib/dispatch.test.js
```
Expected: PASS, 2 tests.

- [ ] **Step 5: Write `scrape.js`**

Create `scraper/scrape.js`:
```js
const { parseArgs } = require('./lib/args');
const { getExtractor } = require('./lib/dispatch');

// Registered one at a time, in follow-up tasks, once each section's real
// Yahoo page structure has been discovered via inspect.js. See
// docs/superpowers/specs/2026-09-15-yahoo-scraper-fallback-design.md.
const extractors = {};

async function main() {
    const args = parseArgs(process.argv.slice(2));
    if (!args.section) {
        throw new Error('Missing required --section=<name> argument.');
    }
    const extractor = getExtractor(extractors, args.section);
    const result = await extractor(args);
    process.stdout.write(JSON.stringify(result));
}

main().catch((err) => {
    console.error(err.message);
    process.exitCode = 1;
});
```

- [ ] **Step 6: Run it directly to confirm the dispatcher's error path works**

```bash
export PATH="$HOME/.nvm/versions/node/v22.23.0/bin:$PATH"
cd scraper && node scrape.js --section=team_names --year=2026
```
Expected: stderr prints `No scrape extractor implemented yet for section "team_names".`, process exits non-zero.

- [ ] **Step 7: Write `yahooScrapeRequest.php`**

Create `yahooScrapeRequest.php` at the repo root (sibling to `yahooApiRequest.php`):
```php
<?php
set_time_limit(300);

include 'yahooSharedFunctions.php';

$year = (int)($_POST['year'] ?? 0);
$section = $_POST['section'] ?? '';
$weeks = $_POST['weeks'] ?? [];
$manager = $_POST['manager'] ?? '';

$scraperDir = __DIR__ . '/scraper';
$args = ['--section=' . $section, '--year=' . $year];
if (!empty($weeks)) {
    $args[] = '--weeks=' . implode(',', (array)$weeks);
}
if ($manager !== '') {
    $args[] = '--manager=' . $manager;
}

$cmd = 'node ' . escapeshellarg($scraperDir . '/scrape.js') . ' ' . implode(' ', array_map('escapeshellarg', $args));

$descriptorSpec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
$process = proc_open($cmd, $descriptorSpec, $pipes, $scraperDir);

if (!is_resource($process)) {
    echo '<div class="alert alert-danger">Error: could not start the scraper process.</div>';
    exit;
}

fclose($pipes[0]);
$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
$exitCode = proc_close($process);

if ($exitCode !== 0) {
    $safeError = htmlspecialchars(trim($stderr) ?: 'Unknown scraper error.');
    echo '<div class="alert alert-danger"><strong>Scrape failed for ' . htmlspecialchars($section) . ':</strong> ' . $safeError . '</div>';
    exit;
}

$data = json_decode($stdout, true);
if ($data === null) {
    echo '<div class="alert alert-danger">Scraper returned invalid JSON for ' . htmlspecialchars($section) . '.</div>';
    exit;
}

echo '<div class="alert alert-success">Scraped ' . htmlspecialchars($section) . ' successfully (' . count($data) . ' item(s)). Writing this section to the database is added in a follow-up task.</div>';
```

Note: `node` here must resolve to the nvm-managed binary. If the PHP process's `PATH` (as run by Valet/Apache) doesn't include nvm's bin directory, replace `'node '` above with the absolute path, e.g. `escapeshellarg($_SERVER['HOME'] . '/.nvm/versions/node/v22.23.0/bin/node') . ' '` — confirm which is needed in Step 9 below.

- [ ] **Step 8: Add the mode toggle to `yahooApi.php`**

In `yahooApi.php`, find this block (the "Verify" card, currently right after the setup-issues warning block):
```php
            <div class="row">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header"></div>
                        <div class="card-body info-card">
                            <!-- 2. Direct user to Yahoo! for authorization (retrieve verifier) -->
                            <h2>First click Verify and then come back and enter the code</h2>
                            <a class="btn btn-secondary" href="<?php echo $request_token_url; ?>" target="_blank">Verify</a>
                        </div>
                    </div>
                </div>
            </div>
```

Replace it with (adds the toggle above it, wraps the Verify card in `.api-mode-field`):
```php
            <div class="row mb-1">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-body info-card">
                            <h3>Fetch Mode</h3>
                            <label><input type="radio" name="fetch_mode" value="api" checked> Yahoo API</label><br>
                            <label><input type="radio" name="fetch_mode" value="scrape"> Web Scrape (bypass Yahoo login)</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row api-mode-field">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header"></div>
                        <div class="card-body info-card">
                            <!-- 2. Direct user to Yahoo! for authorization (retrieve verifier) -->
                            <h2>First click Verify and then come back and enter the code</h2>
                            <a class="btn btn-secondary" href="<?php echo $request_token_url; ?>" target="_blank">Verify</a>
                        </div>
                    </div>
                </div>
            </div>
```

Then find the Code field:
```php
                            <h3>Code</h3>
                            <input type="text" name="code">
```

Replace it with:
```php
                            <div class="api-mode-field">
                                <h3>Code</h3>
                                <input type="text" name="code">
                            </div>
```

- [ ] **Step 9: Update `admin.php`'s JS — add mode-visibility toggle**

In `admin.php`, find this block (right after the manager-checkbox change handlers, right before the `// Yahoo API Submit` comment):
```js
    $('input[name="managers[]"]:not([value="all"])').change(function() {
        if ($(this).is(':checked')) {
            $('input[name="managers[]"][value="all"]').prop('checked', false);
        }
    });

    // Yahoo API Submit
```

Insert the toggle handler between them:
```js
    $('input[name="managers[]"]:not([value="all"])').change(function() {
        if ($(this).is(':checked')) {
            $('input[name="managers[]"][value="all"]').prop('checked', false);
        }
    });

    // Fetch mode toggle (Yahoo API vs Web Scrape)
    function applyFetchModeVisibility() {
        var mode = $('input[name="fetch_mode"]:checked').val();
        if (mode === 'scrape') {
            $('.api-mode-field').hide();
        } else {
            $('.api-mode-field').show();
        }
    }
    $('input[name="fetch_mode"]').change(applyFetchModeVisibility);
    applyFetchModeVisibility();

    // Yahoo API Submit
```

- [ ] **Step 10: Update the submit handler to branch on mode**

In `admin.php`, replace the `$('#make_request').click(...)` handler:
```js
    $('#make_request').click(function () {
        var year = $('input[name="year"]').val();
        var weeks = [];
        $('input[name="weeks[]"]:checked').each(function () {
            weeks.push(parseInt($(this).val()));
        });

        var managers = [];
        $('input[name="managers[]"]:checked').each(function () {
            managers.push($(this).val());
        });

        var matchupsSelected = $('input[name="sections[]"][value="matchups"]:checked').length > 0;
        var playoffWeeksSelected = weeks.some(function(week) {
            return week > 14;
        });

        if (matchupsSelected && playoffWeeksSelected) {
            $('#output').html('<div class="alert alert-danger"><strong>Error:</strong> Playoff matchups (weeks 15+) are not available from the Yahoo API. Please deselect weeks 15-17 when updating matchups, or deselect the Matchups option.</div>');
            return false;
        }

        $('#loading').show();
        $('#output').html('');

        if (!access_token) {
            $.ajax({
                url: 'yahooApiToken.php',
                type: 'POST',
                data: {
                    code: $('input[name="code"]').val(),
                    year: year
                },
                success: function(response) {
                    access_token = response;
                    makeRequest(year, weeks, managers);
                },
                error: function() {
                    $('#loading').hide();
                    $('#output').html('<div class="alert alert-danger">Error fetching access token. Please try again.</div>');
                }
            });
        } else {
            makeRequest(year, weeks, managers);
        }
    });
```

with:
```js
    $('#make_request').click(function () {
        var mode = $('input[name="fetch_mode"]:checked').val();
        var year = $('input[name="year"]').val();
        var weeks = [];
        $('input[name="weeks[]"]:checked').each(function () {
            weeks.push(parseInt($(this).val()));
        });

        var managers = [];
        $('input[name="managers[]"]:checked').each(function () {
            managers.push($(this).val());
        });

        var matchupsSelected = $('input[name="sections[]"][value="matchups"]:checked').length > 0;
        var playoffWeeksSelected = weeks.some(function(week) {
            return week > 14;
        });

        if (mode === 'api' && matchupsSelected && playoffWeeksSelected) {
            $('#output').html('<div class="alert alert-danger"><strong>Error:</strong> Playoff matchups (weeks 15+) are not available from the Yahoo API. Please deselect weeks 15-17 when updating matchups, or deselect the Matchups option.</div>');
            return false;
        }

        $('#loading').show();
        $('#output').html('');

        if (mode === 'scrape') {
            makeRequest(year, weeks, managers, mode);
            return;
        }

        if (!access_token) {
            $.ajax({
                url: 'yahooApiToken.php',
                type: 'POST',
                data: {
                    code: $('input[name="code"]').val(),
                    year: year
                },
                success: function(response) {
                    access_token = response;
                    makeRequest(year, weeks, managers, mode);
                },
                error: function() {
                    $('#loading').hide();
                    $('#output').html('<div class="alert alert-danger">Error fetching access token. Please try again.</div>');
                }
            });
        } else {
            makeRequest(year, weeks, managers, mode);
        }
    });
```

- [ ] **Step 11: Update `makeRequest` and `makeRosterRequest` to be mode-aware**

Replace:
```js
function makeRequest(year, weeks, managers) {
    var pendingRequests = $('input[name="sections[]"]:checked').length;
    var hasRosters = $('input[name="sections[]"][value="rosters"]:checked').length > 0;

    if (pendingRequests === 0) {
        $('#loading').hide();
        $('#output').html('<div class="alert alert-warning">Please select at least one section to update.</div>');
        return;
    }

    if (hasRosters) {
        pendingRequests--;
    }

    $('input[name="sections[]"]:checked').each(function () {
        let section = $(this).val();
        if (section == 'rosters') {
            makeRosterRequest(year, weeks, managers, 0, function() {
                if (pendingRequests === 0) {
                    $('#loading').hide();
                }
            });
        } else {
            $.ajax({
                url: 'yahooApiRequest.php',
                type: 'POST',
                data: {
                    token: access_token,
                    year: year,
                    section: section,
                    weeks: weeks
                },
                success: function(response) {
                    $('#output').append(response);
                    pendingRequests--;
                    if (pendingRequests === 0 && !hasRosters) {
                        $('#loading').hide();
                    }
                },
                error: function() {
                    $('#output').append('<div class="alert alert-danger">Error processing ' + section + '. Please try again.</div>');
                    pendingRequests--;
                    if (pendingRequests === 0 && !hasRosters) {
                        $('#loading').hide();
                    }
                }
            });
        }
    });
}

function makeRosterRequest(year, weeks, managers, managerIndex, callback)
{
    var managersToProcess = [];

    if (managers.includes('all')) {
        for (var i = 1; i <= 10; i++) {
            managersToProcess.push(i);
        }
    } else {
        managersToProcess = managers.filter(function(manager) {
            return manager !== 'all' && !isNaN(manager);
        });
    }

    if (managerIndex >= managersToProcess.length) {
        if (callback) callback();
        return;
    }

    var currentManager = managersToProcess[managerIndex];

    $.ajax({
        url: 'yahooApiRequest.php',
        type: 'POST',
        data: {
            token: access_token,
            year: year,
            section: 'rosters',
            weeks: weeks,
            manager: currentManager
        },
        success: function(response) {
            $('#output').append(response);
            setTimeout(function () {
                makeRosterRequest(year, weeks, managers, managerIndex + 1, callback);
            }, 2000);
        },
        error: function() {
            $('#output').append('<div class="alert alert-danger">Error processing rosters for manager ' + currentManager + '. Continuing with next manager.</div>');
            setTimeout(function () {
                makeRosterRequest(year, weeks, managers, managerIndex + 1, callback);
            }, 2000);
        }
    });
}
```

with:
```js
function makeRequest(year, weeks, managers, mode) {
    var pendingRequests = $('input[name="sections[]"]:checked').length;
    var hasRosters = $('input[name="sections[]"][value="rosters"]:checked').length > 0;
    var endpoint = mode === 'scrape' ? 'yahooScrapeRequest.php' : 'yahooApiRequest.php';

    if (pendingRequests === 0) {
        $('#loading').hide();
        $('#output').html('<div class="alert alert-warning">Please select at least one section to update.</div>');
        return;
    }

    if (hasRosters) {
        pendingRequests--;
    }

    $('input[name="sections[]"]:checked').each(function () {
        let section = $(this).val();
        if (section == 'rosters') {
            makeRosterRequest(year, weeks, managers, mode, 0, function() {
                if (pendingRequests === 0) {
                    $('#loading').hide();
                }
            });
        } else {
            var requestData = {
                year: year,
                section: section,
                weeks: weeks
            };
            if (mode !== 'scrape') {
                requestData.token = access_token;
            }
            $.ajax({
                url: endpoint,
                type: 'POST',
                data: requestData,
                success: function(response) {
                    $('#output').append(response);
                    pendingRequests--;
                    if (pendingRequests === 0 && !hasRosters) {
                        $('#loading').hide();
                    }
                },
                error: function() {
                    $('#output').append('<div class="alert alert-danger">Error processing ' + section + '. Please try again.</div>');
                    pendingRequests--;
                    if (pendingRequests === 0 && !hasRosters) {
                        $('#loading').hide();
                    }
                }
            });
        }
    });
}

function makeRosterRequest(year, weeks, managers, mode, managerIndex, callback)
{
    var managersToProcess = [];

    if (managers.includes('all')) {
        for (var i = 1; i <= 10; i++) {
            managersToProcess.push(i);
        }
    } else {
        managersToProcess = managers.filter(function(manager) {
            return manager !== 'all' && !isNaN(manager);
        });
    }

    if (managerIndex >= managersToProcess.length) {
        if (callback) callback();
        return;
    }

    var currentManager = managersToProcess[managerIndex];
    var endpoint = mode === 'scrape' ? 'yahooScrapeRequest.php' : 'yahooApiRequest.php';
    var requestData = {
        year: year,
        section: 'rosters',
        weeks: weeks,
        manager: currentManager
    };
    if (mode !== 'scrape') {
        requestData.token = access_token;
    }

    $.ajax({
        url: endpoint,
        type: 'POST',
        data: requestData,
        success: function(response) {
            $('#output').append(response);
            setTimeout(function () {
                makeRosterRequest(year, weeks, managers, mode, managerIndex + 1, callback);
            }, 2000);
        },
        error: function() {
            $('#output').append('<div class="alert alert-danger">Error processing rosters for manager ' + currentManager + '. Continuing with next manager.</div>');
            setTimeout(function () {
                makeRosterRequest(year, weeks, managers, mode, managerIndex + 1, callback);
            }, 2000);
        }
    });
}
```

- [ ] **Step 12: Verify end-to-end in a browser**

With Valet serving the site locally, open `admin.php`, go to the Yahoo API tab:
1. Confirm "Yahoo API" is selected by default and the Code field + Verify button are visible.
2. Click "Web Scrape" — confirm the Code field and Verify card both disappear.
3. Check "Team Names" under Sections, leave Weeks/Managers as default, click Submit.
4. Expected: the loading spinner shows, then `#output` shows a red alert reading `Scrape failed for team_names: No scrape extractor implemented yet for section "team_names".`

This confirms the full chain — UI toggle → AJAX → `yahooScrapeRequest.php` → `proc_open` → `scrape.js` → dispatcher error → back to the browser — works before any real extraction logic exists.

- [ ] **Step 13: If Step 12 instead shows "could not start the scraper process" or a PATH-related error**

Update `yahooScrapeRequest.php`'s `$cmd` line to use the absolute node path instead of relying on Valet/Apache's `PATH`:
```php
$nodeBin = getenv('HOME') . '/.nvm/versions/node/v22.23.0/bin/node';
$cmd = escapeshellarg($nodeBin) . ' ' . escapeshellarg($scraperDir . '/scrape.js') . ' ' . implode(' ', array_map('escapeshellarg', $args));
```
Re-run Step 12 to confirm this resolves it.

---

## After this plan

With Tasks 1-4 done, the scraper can log in, dump inspection data for any section, and the admin UI can round-trip to it end-to-end — but nothing is actually extracted yet. The next step (outside this plan) is, for each of `team_names`, `yahoo_ids`, `matchups`, `rosters`, `trades`, in turn:

1. Run `node scraper/inspect.js --section=<name> ...` for that section and share the `page.html` / `network/*.json` output.
2. From that output, add a real extractor function to `scrape.js`'s `extractors` table, plus a matching `handle_scraped_<name>` function in `yahooScrapeRequest.php` that writes the extracted data to the same tables the API path uses (see the spec's "Data mapping" table).
3. Verify by comparing the scraped result against a week/section already known-good from a prior API fetch.

Each of those is small enough to be its own short plan (or a single subagent-driven task) once its inspection output exists — there's no value in pre-writing them now against data nobody has looked at yet.
