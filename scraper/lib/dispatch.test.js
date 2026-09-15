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

test('does not resolve prototype-chain properties as registered extractors', () => {
    assert.throws(
        () => getExtractor({}, 'constructor'),
        /No scrape extractor implemented yet for section "constructor"/
    );
    assert.throws(
        () => getExtractor({}, 'toString'),
        /No scrape extractor implemented yet for section "toString"/
    );
    assert.throws(
        () => getExtractor({}, 'hasOwnProperty'),
        /No scrape extractor implemented yet for section "hasOwnProperty"/
    );
});
