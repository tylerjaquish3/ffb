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
