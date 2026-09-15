const SECTION_URLS = {
    yahoo_ids: ({ leagueId }) => `https://football.fantasysports.yahoo.com/f1/${leagueId}`,
    team_names: ({ leagueId }) => `https://football.fantasysports.yahoo.com/f1/${leagueId}`,
    // NOTE: the originally-planned `/f1/<leagueId>/scoreboard?week=<N>` URL
    // 404s for real ("The document you requested was not found") — confirmed
    // against a live logged-in session, not a guess. The real "all matchups
    // for a week" view lives on the league home page's "Matchups" module,
    // reachable by requesting that module directly via query params (this is
    // literally the same URL the module's own prev/next-week links use, e.g.
    // `?matchup_week=1&module=matchups&lhst=matchups`, confirmed by
    // inspecting those links in a real captured page).
    matchups: ({ leagueId, week }) => `https://football.fantasysports.yahoo.com/f1/${leagueId}?matchup_week=${week}&module=matchups&lhst=matchups`,
    rosters: ({ leagueId, manager, week }) => `https://football.fantasysports.yahoo.com/f1/${leagueId}/${manager}?week=${week}`,
    // The originally-assumed `?scope=all&type=trade` query params don't do
    // anything real — confirmed against a live logged-in session: that URL
    // loads fine (no 404, unlike matchups' original guess) but Yahoo just
    // ignores the unrecognized params and silently renders the default "All
    // Transactions" tab (adds/drops included), not filtered to trades at
    // all. The page's own "Trades" tab link
    // (`<a class="Navtarget" href="?transactionsfilter=trade">Trades</a>`,
    // confirmed via a real inspect.js capture) reveals the real query param
    // name: `transactionsfilter=trade`. Re-requesting with that param
    // flips the nav's "Selected" state to the Trades tab and correctly
    // renders only trade rows (confirmed empty for the current, real 2026
    // league — see scrape.js's extractTrades for how that empty state is
    // detected).
    trades: ({ leagueId }) => `https://football.fantasysports.yahoo.com/f1/${leagueId}/transactions?transactionsfilter=trade`,
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
