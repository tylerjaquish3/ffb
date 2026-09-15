const { chromium } = require('playwright');
const { parseArgs } = require('./lib/args');
const { getExtractor } = require('./lib/dispatch');
const { resolveUrl } = require('./lib/sectionUrls');
const { getAuthenticatedContext } = require('./login');

// Yahoo's league standings page (https://football.fantasysports.yahoo.com/f1/<leagueId>,
// resolved by resolveUrl for section "team_names") renders a `#standingstable`
// table with one row per team. Confirmed via a real inspect.js capture
// (scraper/inspection-output/team_names-1789487642135/page.html) that:
//   - none of the 27 captured network JSON responses are Yahoo's own fantasy
//     data endpoints (they're all ad-tech/analytics calls: prebid, rubicon,
//     ybar, signal-service, etc.) — there is no structured JSON to intercept
//     for this section, so this extractor scrapes the rendered DOM instead.
//   - the standings table has a "Team" column (name + yahoo team id, via the
//     row's `data-target="/f1/<leagueId>/<teamId>"` attribute and the team
//     link's href) and a "Moves" column, but NO trades count anywhere on the
//     page or in any network response. `trades` is therefore always null
//     here — see handle_scraped_team_names() in yahooScrapeRequest.php for
//     how that's handled on the write side (existing trades values are left
//     untouched, never zeroed out).
async function extractTeamNames(args, context) {
    if (!args['league-id']) {
        throw new Error('Missing required --league-id=<id> argument.');
    }

    const url = resolveUrl(args);
    const page = await context.newPage();
    try {
        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });

        if (page.url().includes('login.yahoo.com')) {
            throw new Error(
                'Redirected to Yahoo login while loading the standings page — ' +
                'the saved session has expired. Re-run to trigger a fresh login.'
            );
        }

        await page.waitForSelector('#standingstable tbody tr', { timeout: 15000 });

        const rows = await page.$$eval('#standingstable tbody tr', (trs) =>
            trs.map((tr) => {
                const target = tr.getAttribute('data-target') || '';
                const idMatch = target.match(/\/(\d+)$/);
                const nameLink = tr.querySelector('td.Tst-manager a.F-reset');
                const movesCell = tr.querySelector('td.last');
                return {
                    yahooTeamId: idMatch ? idMatch[1] : null,
                    name: nameLink ? nameLink.textContent.trim() : null,
                    movesRaw: movesCell ? movesCell.textContent.trim() : null,
                };
            })
        );

        const teams = [];
        for (const row of rows) {
            if (!row.yahooTeamId || !row.name) {
                console.error(`Skipping unparseable standings row: ${JSON.stringify(row)}`);
                continue;
            }
            teams.push({
                yahooTeamId: Number(row.yahooTeamId),
                name: row.name,
                moves: !row.movesRaw || row.movesRaw === '-' ? 0 : Number(row.movesRaw),
                trades: null,
            });
        }
        return teams;
    } finally {
        await page.close();
    }
}

const extractors = {
    team_names: extractTeamNames,
};

async function main() {
    const args = parseArgs(process.argv.slice(2));
    if (!args.section) {
        throw new Error('Missing required --section=<name> argument.');
    }
    const extractor = getExtractor(extractors, args.section);

    const browser = await chromium.launch({ headless: true });
    try {
        const context = await getAuthenticatedContext(browser);
        try {
            const result = await extractor(args, context);
            process.stdout.write(JSON.stringify(result));
        } finally {
            await context.close();
        }
    } finally {
        await browser.close();
    }
}

main().catch((err) => {
    console.error(err.message);
    process.exitCode = 1;
});
