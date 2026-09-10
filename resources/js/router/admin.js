import { createRouter, createWebHistory } from 'vue-router';
import HostDashboardLayout from '@/layouts/HostDashboardLayout.vue';
import { getAppBasePath, withAppBase } from '@/utils/app-base';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('@/pages/auth/LoginPage.vue'),
        meta: { guest: true, breadcrumb: 'Login' },
    },
    {
        path: '/',
        component: HostDashboardLayout,
        children: [
            {
                path: '',
                name: 'dashboard',
                component: () => import('@/pages/DashboardPage.vue'),
                meta: { breadcrumbKey: 'nav.dashboard' },
            },
            {
                path: 'bookings',
                name: 'bookings',
                component: () => import('@/pages/bookings/BookingsListPage.vue'),
                meta: { breadcrumbKey: 'nav.allBookings' },
            },
            {
                path: 'bookings/calendar',
                name: 'bookings-calendar',
                component: () => import('@/pages/bookings/BookingCalendarPage.vue'),
                meta: { breadcrumbKey: 'nav.myBookings' },
            },
            {
                path: 'bookings/:id',
                name: 'booking-detail',
                component: () => import('@/pages/bookings/BookingDetailPage.vue'),
                meta: { breadcrumbKey: 'nav.allBookings', detailShell: true },
            },
            {
                path: 'apartments/add',
                name: 'apartment-add',
                component: () => import('@/pages/apartments/AddApartmentPage.vue'),
                meta: { breadcrumbKey: 'nav.addApartment', wizard: true },
            },
            {
                path: 'apartments',
                name: 'apartments',
                component: () => import('@/pages/apartments/ApartmentsListPage.vue'),
                meta: { breadcrumbKey: 'nav.allApartments' },
            },
            {
                path: 'apartments/:id',
                name: 'apartment-detail',
                component: () => import('@/pages/apartments/ApartmentDetailPage.vue'),
                meta: { breadcrumbKey: 'nav.allApartments', detailShell: true },
            },
            {
                path: 'settings/email',
                name: 'settings-email',
                component: () => import('@/pages/settings/EmailSettingsPage.vue'),
                meta: { breadcrumbKey: 'nav.emailSettings', settingsSection: true },
            },
            {
                path: 'settings/email-templates',
                name: 'settings-email-templates',
                component: () => import('@/pages/settings/EmailTemplatesPage.vue'),
                meta: { breadcrumbKey: 'nav.emailTemplates', settingsSection: true },
            },
            {
                path: 'settings/languages',
                name: 'settings-languages',
                component: () => import('@/pages/settings/LanguagesSettingsPage.vue'),
                meta: { breadcrumbKey: 'nav.languages', settingsSection: true },
            },
            {
                path: 'host-applications',
                name: 'host-applications',
                component: () => import('@/pages/host-applications/HostApplicationsListPage.vue'),
                meta: { breadcrumbKey: 'nav.hostApplications', adminSection: true },
            },
            {
                path: 'host-applications/:id',
                name: 'host-application-detail',
                component: () => import('@/pages/host-applications/HostApplicationDetailPage.vue'),
                meta: { breadcrumbKey: 'nav.hostApplications', adminSection: true },
            },
            {
                path: 'users',
                name: 'admin-users',
                component: () => import('@/pages/admin-users/AdminUsersPage.vue'),
                meta: { breadcrumbKey: 'nav.adminUsers', adminSection: true },
            },
            {
                path: 'team/sales',
                name: 'team-sales',
                component: () => import('@/pages/team/TeamPage.vue'),
                meta: { breadcrumbKey: 'nav.salesTeam', teamMode: 'sales' },
            },
            {
                path: 'team/operations',
                name: 'team-operations',
                component: () => import('@/pages/team/TeamPage.vue'),
                meta: { breadcrumbKey: 'nav.operationsTeam', teamMode: 'operations' },
            },
            {
                path: 'team/sales/:id',
                name: 'team-sales-member',
                component: () => import('@/pages/team/TeamMemberDetailPage.vue'),
                meta: { breadcrumbKey: 'nav.salesTeam', detailShell: true },
            },
            {
                path: 'team/management-company',
                name: 'team-management-company',
                component: () => import('@/pages/team/ManagementCompanyPage.vue'),
                meta: { breadcrumbKey: 'nav.managementCompany' },
            },
            {
                path: 'customers',
                name: 'customers',
                component: () => import('@/pages/customers/CustomersPage.vue'),
                meta: { breadcrumbKey: 'nav.customers' },
            },
            {
                path: 'customers/:id',
                name: 'customer-detail',
                component: () => import('@/pages/customers/CustomerDetailPage.vue'),
                meta: { breadcrumbKey: 'nav.customers', detailShell: true },
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(`${getAppBasePath()}/admin`.replace('//', '/')),
    routes,
});

router.beforeEach(async (to) => {
    if (to.meta.guest) {
        return true;
    }

    try {
        const res = await fetch(withAppBase('/api/user'), { credentials: 'include' });
        if (res.status === 401 || res.status === 419 || res.status >= 500) {
            return to.name === 'login'
                ? true
                : { name: 'login', query: { redirect: to.fullPath } };
        }
    } catch {
        return { name: 'login' };
    }

    return true;
});

export default router;
