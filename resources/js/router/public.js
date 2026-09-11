import { createRouter, createWebHistory } from 'vue-router';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { getAppBasePath } from '@/utils/app-base';

const routes = [
    {
        path: '/',
        component: PublicLayout,
        children: [
            {
                path: '',
                name: 'home',
                component: () => import('@/pages/public/HomePage.vue'),
            },
            {
                path: 'apartments',
                name: 'public-apartments',
                component: () => import('@/pages/public/ApartmentsPage.vue'),
            },
            {
                path: 'apartments/:id',
                name: 'public-apartment',
                component: () => import('@/pages/public/ApartmentDetailPage.vue'),
            },
            {
                path: 'booking/:apartmentId',
                name: 'booking-checkout',
                component: () => import('@/pages/public/BookingCheckoutPage.vue'),
            },
            {
                path: 'host-application',
                name: 'host-application',
                component: () => import('@/pages/public/HostApplicationPage.vue'),
            },
            {
                path: 'team-invite/:token',
                name: 'team-invitation-accept',
                component: () => import('@/pages/public/TeamInvitationAcceptPage.vue'),
            },
            {
                path: 'messages/:token',
                name: 'guest-message-thread',
                component: () => import('@/pages/public/GuestMessageThreadPage.vue'),
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(`${getAppBasePath()}/`.replace('//', '/')),
    routes,
});

export default router;
