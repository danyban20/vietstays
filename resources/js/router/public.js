import { createRouter, createWebHistory } from 'vue-router';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { getAppBasePath } from '@/utils/app-base';
import { rewriteLegacySearchQuery } from '@/utils/legacy-search-query';

const routes = [
    {
        path: '/wp/:pathMatch(.*)*',
        redirect: (to) => {
            const rest = to.params.pathMatch;
            const suffix = Array.isArray(rest) ? rest.filter(Boolean).join('/') : (rest || '');

            return {
                path: `/${suffix}`,
                query: rewriteLegacySearchQuery(to.query),
                hash: to.hash,
            };
        },
    },
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
                path: 'apartment/:slug',
                name: 'public-apartment',
                component: () => import('@/pages/public/ApartmentDetailPage.vue'),
            },
            {
                path: 'apartments/:id(\\d+)',
                name: 'public-apartment-id',
                component: () => import('@/pages/public/ApartmentDetailPage.vue'),
            },
            {
                path: 'booking/:apartmentId',
                name: 'booking-checkout',
                component: () => import('@/pages/public/BookingCheckoutPage.vue'),
            },
            {
                path: 'register',
                name: 'member-register',
                component: () => import('@/pages/public/MemberRegisterPage.vue'),
            },
            {
                path: 'login',
                name: 'member-login',
                component: () => import('@/pages/public/MemberLoginPage.vue'),
            },
            {
                path: 'account',
                name: 'member-account',
                component: () => import('@/pages/public/MemberAccountPage.vue'),
            },
            {
                path: 'terms',
                name: 'terms',
                component: () => import('@/pages/public/TermsPage.vue'),
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
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        }

        return to.hash ? { el: to.hash, top: 16 } : { top: 0 };
    },
});

export default router;
