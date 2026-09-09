const FLAGS = {
    vietnam: '🇻🇳',
    norway: '🇳🇴',
    'south korea': '🇰🇷',
    korea: '🇰🇷',
    sweden: '🇸🇪',
    germany: '🇩🇪',
    japan: '🇯🇵',
    india: '🇮🇳',
    china: '🇨🇳',
    italy: '🇮🇹',
    netherlands: '🇳🇱',
    'united states': '🇺🇸',
    usa: '🇺🇸',
    france: '🇫🇷',
    'united kingdom': '🇬🇧',
    uk: '🇬🇧',
    australia: '🇦🇺',
    singapore: '🇸🇬',
    thailand: '🇹🇭',
};

export function countryFlag(country) {
    if (!country) return '🌐';
    return FLAGS[country.trim().toLowerCase()] ?? '🌐';
}
