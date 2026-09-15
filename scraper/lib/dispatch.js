function getExtractor(extractors, section) {
    if (!Object.prototype.hasOwnProperty.call(extractors, section)) {
        throw new Error(`No scrape extractor implemented yet for section "${section}".`);
    }
    return extractors[section];
}

module.exports = { getExtractor };
