const test = require('node:test');
const assert = require('node:assert/strict');
const { resolveUrl } = require('./sectionUrls');

test('builds the default team_names URL from league-id', () => {
    const url = resolveUrl({ section: 'team_names', 'league-id': '18261' });
    assert.equal(url, 'https://football.fantasysports.yahoo.com/f1/18261');
});

test('builds the default matchups URL from league-id and week', () => {
    const url = resolveUrl({ section: 'matchups', 'league-id': '18261', week: '1' });
    assert.equal(url, 'https://football.fantasysports.yahoo.com/f1/18261?matchup_week=1&module=matchups&lhst=matchups');
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
