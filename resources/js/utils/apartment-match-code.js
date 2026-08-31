export function parseApartmentName(apartment) {
    const parts = String(apartment || '').split(' - ');

    return {
        building: parts[0] || '',
        feature: parts[1] || '',
        district: parts[2] || '',
        br: parts[3] || '',
    };
}

export function matchCodeFromApartmentName(apartment) {
    const parsed = parseApartmentName(apartment);
    const buildingCode = (parsed.building.trim()[0] || 'X').toUpperCase();
    const feature = parsed.feature.trim();
    const featureCode = feature ? `${feature[0]}${feature[feature.length - 1]}`.toUpperCase() : 'XX';
    const district = parsed.district.trim();
    let districtCode = '';

    if (district) {
        districtCode = /^D\d+$/i.test(district)
            ? district.replace(/\D/g, '')
            : district
                  .split(/\s+/)
                  .filter(Boolean)
                  .map((word) => word[0].toUpperCase())
                  .join('');
    }

    const bedroomNumber = (String(parsed.br).match(/\d+/) || ['0'])[0].padStart(2, '0');

    return `${buildingCode}${featureCode}${districtCode}${bedroomNumber}`;
}
