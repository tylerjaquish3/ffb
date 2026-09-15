const path = require('path');
const dotenv = require('dotenv');

function loadDotenv() {
    dotenv.config({ path: path.join(__dirname, '..', '..', '.env.local') });
}

function requireEnv(name, env = process.env) {
    const value = env[name];
    if (!value) {
        throw new Error(`Missing required env var ${name} in .env.local`);
    }
    return value;
}

module.exports = { loadDotenv, requireEnv };
