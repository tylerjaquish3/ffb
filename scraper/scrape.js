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
