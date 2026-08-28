import { HOME_IMAGES } from '@/data/home-content';

export { HOME_IMAGES };

export const DETAIL_ICONS = {
    pin: '/home/images/np_pin1.svg',
    share: '/home/images/share.svg',
    rooms: '/home/images/icon_rooms.svg',
    beds: '/home/images/icon_beds.svg',
    guests: '/home/images/icon_guests.svg',
    baths: '/home/images/icon_baths.svg',
    size: '/home/images/icon_size.svg',
    map: '/home/images/theme/map.png',
    user: '/home/images/theme/user_img.png',
    checkin: '/home/images/icon-checkinwithouthost.svg',
    flexible: '/home/images/icon-flexiblereservation.svg',
    airport: '/home/images/icon-airportpickup.svg',
    scooter: '/home/images/icon-scootersavailable.svg',
};

export const PRACTICAL_BLOCKS = [
    {
        key: 'checkin_without_host',
        icon: DETAIL_ICONS.checkin,
        title: 'Check-in without host',
        text: 'Personal check-in can be arranged',
    },
    {
        key: 'flexible_reservation',
        icon: DETAIL_ICONS.flexible,
        title: 'Flexible reservation',
        text: 'Free of charge cancellation 2 days before arrival',
    },
    {
        key: 'airport_pickup',
        icon: DETAIL_ICONS.airport,
        title: 'Air Port Pick Up',
        text: 'Pick up at airport can be arranged',
    },
    {
        key: 'scooter_rental',
        icon: DETAIL_ICONS.scooter,
        title: 'Scooters availability',
        text: 'We can arrange scooters for your stay',
    },
];

export const SAMPLE_REVIEWS = [
    {
        title: 'Very clean apartment!',
        body: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
        author: 'Thao, July 2023',
    },
    {
        title: 'Beautiful view',
        body: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua!',
        author: 'Charles, June 2023',
    },
];

export function facilityIcon(name) {
    const slug = String(name || '')
        .trim()
        .toLowerCase()
        .replace(/\s+/g, '-');

    return `/home/images/facilities/icon-${slug}.svg`;
}
