import { createRouter, createWebHistory } from 'vue-router';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { useMemberStore } from '@/stores/member';
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
                // The customer dashboard ("My account").
                path: 'account',
                component: () => import('@/layouts/MemberLayout.vue'),
                meta: { memberArea: true, requiresMember: true },
                children: [
                    {
                        path: '',
                        name: 'member-account',
                        component: () => import('@/pages/member/MemberDashboardPage.vue'),
                        meta: { memberSection: 'dashboard', title: 'My account' },
                    },
                    {
                        path: 'reservations',
                        name: 'member-reservations',
                        component: () => import('@/pages/member/MemberReservationsPage.vue'),
                        meta: { memberSection: 'reservations', title: 'Reservations', backTo: 'member-account' },
                    },
                    {
                        path: 'reservations/:id(\\d+)',
                        name: 'member-reservation',
                        component: () => import('@/pages/member/MemberReservationDetailPage.vue'),
                        meta: { memberSection: 'reservations', title: 'Reservation', backTo: 'member-reservations' },
                    },
                    {
                        path: 'messages/:conversation?',
                        name: 'member-messages',
                        component: () => import('@/pages/member/MemberMessagesPage.vue'),
                        meta: { memberSection: 'messages', title: 'Messages', backTo: 'member-account' },
                    },
                    {
                        path: 'profile',
                        name: 'member-profile',
                        component: () => import('@/pages/member/MemberProfilePage.vue'),
                        meta: { memberSection: 'profile', title: 'Profile', backTo: 'member-account' },
                    },
                ],
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

router.beforeEach(async (to) => {
    if (!to.matched.some((record) => record.meta.requiresMember)) {
        return true;
    }

    const member = useMemberStore();
    await member.fetchUser();

    if (!member.isSignedIn) {
        return { name: 'member-login', query: { redirect: to.fullPath } };
    }

    return true;
});

const defaultTitle = typeof document !== 'undefined' ? document.title : '';

router.afterEach((to) => {
    const title = [...to.matched].reverse().find((record) => record.meta.title)?.meta.title;

    if (title && to.matched.some((record) => record.meta.memberArea)) {
        document.title = `${title} · Vietstays`;
    } else if (defaultTitle) {
        document.title = defaultTitle;
    }
});

export default router;
