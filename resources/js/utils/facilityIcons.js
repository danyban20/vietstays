export function facilityIcon(name) {
    const key = String(name ?? '').toLowerCase();
    const map = [
        ['wifi', '◉'],
        ['tv', '▣'],
        ['ac', '❄'],
        ['air', '❄'],
        ['kitchen', '⌂'],
        ['kjøkken', '⌂'],
        ['pool', '≋'],
        ['basseng', '≋'],
        ['parking', 'P'],
        ['elevator', '⇅'],
        ['heis', '⇅'],
        ['pet', '♡'],
        ['washer', '↻'],
        ['vask', '↻'],
        ['balcony', '▭'],
        ['balkong', '▭'],
        ['safe', '🔒'],
        ['heat', '♨'],
        ['varme', '♨'],
        ['coffee', '☕'],
        ['workspace', '⌨'],
        ['arbeid', '⌨'],
        ['bath', '◐'],
        ['smoke', '∿'],
        ['view', '👁'],
        ['mountain', '▲'],
        ['sea', '≈'],
    ];

    for (const [needle, icon] of map) {
        if (key.includes(needle)) {
            return icon;
        }
    }

    return '✓';
}
