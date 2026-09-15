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
