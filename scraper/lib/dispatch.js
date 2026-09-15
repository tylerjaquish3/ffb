function getExtractor(extractors, section) {
    const extractor = extractors[section];
    if (!extractor) {
        throw new Error(`No scrape extractor implemented yet for section "${section}".`);
    }
    return extractor;
}

module.exports = { getExtractor };
