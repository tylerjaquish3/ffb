const test = require('node:test');
const assert = require('node:assert/strict');
const { requireEnv } = require('./env');

test('requireEnv returns the value when present', () => {
    assert.equal(requireEnv('FOO', { FOO: 'bar' }), 'bar');
});

test('requireEnv throws when missing', () => {
    assert.throws(() => requireEnv('FOO', {}), /Missing required env var FOO/);
});
