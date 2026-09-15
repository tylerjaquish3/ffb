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
