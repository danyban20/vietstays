import { EXPLORE_CITIES, HOME_IMAGES, MEMBERSHIP_BENEFITS } from '@/data/home-content';

export { HOME_IMAGES, EXPLORE_CITIES as OTHER_CITIES };

/** UI filter chips mapped to vv_facilities IDs or apartment boolean fields. */
export const SEARCH_FACILITIES = [
    { id: 'entire', name: 'Entire home', icon: '/home/images/home_h.svg' },
    { id: 'beach', name: 'Beach', icon: '/home/images/facilities/icon-beach.svg', facilityId: 8 },
    { id: 'kitchen', name: 'Kitchen', icon: '/home/images/facilities/icon-kitchen.svg', facilityId: 4 },
    {
        id: 'accessible',
        name: 'Wheelchair accessible',
        icon: '/home/images/icon-flexiblereservation.svg',
        filter: 'flexible_reservation',
    },
    {
        id: 'checkin',
        name: 'Self check-in',
        icon: '/home/images/icon-checkinwithouthost.svg',
        filter: 'checkin_without_host',
    },
    { id: 'view', name: 'Lake view', icon: '/home/images/facilities/icon-view-to-city.svg', facilityId: 7 },
    { id: 'wifi', name: 'Wifi', icon: '/home/images/facilities/icon-wifi.svg', facilityId: 2 },
];

export const SECTION_EXPLORE = {
    title: 'Explore more exclusive apartments from Visit Vietnam',
    subtitle:
        'We provide luxury stays in safe environments. Get ready to treat yourself with a stay with Visit Vietnam.',
    listTitle: 'Free Membership and Rewards',
    listItems: MEMBERSHIP_BENEFITS,
};

export const SECTION_HCM = {
    title: 'Excited to visit the amazing Ho Chi Minh City?',
    subtitle: 'Click here to see the most exciting and vibrant city attractions.',
    buttonText: 'Explore now',
    buttonLink: '#',
    leftImage: '/home/images/uploads/2023/10/img_03-1.png',
    centerImage: '/home/images/uploads/2023/11/img_03-1.png',
    rightImage: '/home/images/uploads/2023/10/img_02.png',
};

/** Centered script logo used inside the district greenbox placeholder. */
export const DISTRICT_GREENBOX_LOGO = HOME_IMAGES.logo;
