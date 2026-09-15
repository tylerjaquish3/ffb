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

        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
        try {
            await page.waitForLoadState('networkidle', { timeout: 15000 });
        } catch (err) {
            // Yahoo fantasy pages can keep background network activity (ads,
            // polling) alive indefinitely; fall back to the fixed wait below
            // rather than treating a lingering "idle" wait as a load failure.
        }
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
