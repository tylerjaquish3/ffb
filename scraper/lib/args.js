function parseArgs(argv) {
    const args = {};
    for (const raw of argv) {
        const match = /^--([^=]+)=(.*)$/.exec(raw);
        if (!match) {
            throw new Error(`Unrecognized argument: ${raw}. Expected --key=value.`);
        }
        args[match[1]] = match[2];
    }
    return args;
}

module.exports = { parseArgs };
