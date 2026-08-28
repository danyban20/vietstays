const IMG = '/home/images';

export const HOME_IMAGES = {
    logo: `${IMG}/theme/logo.png`,
    shield: `${IMG}/theme/shield.png`,
    skyline: `${IMG}/theme/skyline2.png`,
    tag: '/home/assets/tag.svg',
    next: '/home/assets/next_1.svg',
    play: '/home/images/play.svg',
    sliderSide: `${IMG}/uploads/2023/08/a7d0079d-067c-4503-9bd0-1574230d9a1f.jpeg`,
    videoThumb: `${IMG}/uploads/2023/08/5a7ff7a8-1f58-43c3-afd7-509209093aeb.jpeg`,
    fb: '/home/assets/fb.svg',
    insta: '/home/assets/insta.svg',
};

export const HERO_SLIDES = [
    {
        title: 'Book your next Stay in Vietnam',
        text: 'We provide luxury stays in safe environments\nGet ready to treat yourself with a stay with Visit Vietnam',
        image: `${IMG}/uploads/2023/08/5a7ff7a8-1f58-43c3-afd7-509209093aeb.jpeg`,
    },
    {
        title: 'Book your next Stay in Vietnam',
        text: 'We provide luxury stays in safe environments\nGet ready to treat yourself with a stay with Visit Vietnam',
        image: `${IMG}/uploads/2023/08/a7d0079d-067c-4503-9bd0-1574230d9a1f.jpeg`,
    },
];

export const EXPLORE_CITIES = [
    {
        name: 'Da Nang',
        subtitle: '',
        image: `${IMG}/uploads/2023/10/img_02.png`,
        districtId: 449,
    },
    {
        name: 'Hanoi',
        subtitle: '',
        image: `${IMG}/uploads/2023/10/img_03.png`,
        districtId: 453,
    },
    {
        name: 'Ho Chi Minh City',
        subtitle: 'Ho Chi Minh City test',
        image: `${IMG}/uploads/2023/11/img_03-1.png`,
        districtId: 440,
    },
    {
        name: 'Nha Trang',
        subtitle: '',
        image: `${IMG}/uploads/2023/10/img_03-1.png`,
        districtId: null,
    },
];

export const MEMBERSHIP_BENEFITS = [
    'Save point and get discount on your next stay',
    'Get discount on the most popular apartments',
    'Register and win exclusive stays for your family',
    'Be the first to know of new offers',
];

export const CAMPAIGN_OFFERS = [
    {
        image: `${IMG}/uploads/2023/08/581de46c5b1c1490133ddd281304fb50@2x.png`,
        percent: null,
        oldPrice: null,
        newPrice: '1.050 NOK',
        title: 'Luxury Aprt 3BR – Zenity , Center View , D1',
        disabled: false,
    },
    {
        image: `${IMG}/uploads/2023/08/a7d0079d-067c-4503-9bd0-1574230d9a1f@2x.png`,
        percent: '25%',
        oldPrice: '1.500 NOK',
        newPrice: '1.050 NOK',
        title: '3BR, Luksus Oslo Aprt, Zenity, utsikt Bitexco #1',
        disabled: false,
    },
    {
        image: `${IMG}/uploads/2023/08/d79a6b9e-61da-454b-a583-36e0931c06a2@2x.png`,
        percent: '20%',
        oldPrice: '1.500 NOK',
        newPrice: '1.050 NOK',
        title: '3BR, Luksus Oslo Aprt, Zenity, utsikt Bitexco #1',
        disabled: false,
    },
    {
        image: `${IMG}/uploads/2023/08/img_2.png`,
        percent: '30%',
        oldPrice: '1.500 NOK',
        newPrice: '1.050 NOK',
        title: '3BR, Luksus Oslo Aprt, Zenity, utsikt Bitexco #1',
        disabled: true,
    },
    {
        image: `${IMG}/uploads/2023/08/img_2.png`,
        percent: '25%',
        oldPrice: '1.500 NOK',
        newPrice: '1.050 NOK',
        title: '3BR, Luksus Oslo Aprt, Zenity, utsikt Bitexco #1',
        disabled: true,
    },
    {
        image: `${IMG}/uploads/2023/08/img_2.png`,
        percent: '20%',
        oldPrice: '1.500 NOK',
        newPrice: '1.050 NOK',
        title: '3BR, Luksus Oslo Aprt, Zenity, utsikt Bitexco #1',
        disabled: true,
    },
];

export const SERVICE_ITEMS = [
    {
        icon: '/home/assets/np_airplane_888646_000000.svg',
        title: 'We pick you up at the Airport',
    },
    {
        icon: '/home/assets/np_platter_2357752_000000.svg',
        title: 'Fast and accessible food delivery',
    },
    {
        icon: '/home/assets/np_police_1584054_000000.svg',
        title: 'Safe and secure enviroment',
    },
    {
        icon: '/home/assets/np_scooter_975378_000000.svg',
        title: 'Ready to Go - Scooter and Bus Tours',
    },
];

export const STAY_BENEFITS = [
    { icon: '/home/assets/np_star_1359493_000000.svg', text: 'Luxury apartments in high quality' },
    { icon: '/home/assets/np_pool_2432206_000000.svg', text: 'Private swimming pool included' },
    { icon: '/home/assets/np_like_1555605_000000.svg', text: 'Perfect environment for family and business trips' },
];

export const DEFAULT_CITIES = [
    { district_id: 449, name: 'Da Nang' },
    { district_id: 452, name: 'Haiphong' },
    { district_id: 453, name: 'Hanoi' },
    { district_id: 440, name: 'Ho Chi Minh City' },
];
