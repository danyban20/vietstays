import { createI18n } from 'vue-i18n';
import apiClient from '@/api/client';

export const DEFAULT_LOCALE = 'en';

const englishMessages = {
    brand: 'Vietstays',
    common: {
        save: 'Save',
        cancel: 'Cancel',
        searchPlaceholder: 'Search booking, guest or apartment…',
        hostDashboard: 'Host dashboard',
        hostApartments: 'Host {count} apartment',
        hostApartments_other: 'Host {count} apartments',
        loading: 'Loading…',
        automatic: 'Automatic (based on location)',
    },
    locale: {
        label: 'Language',
        adminLanguage: 'Admin language',
        chooseAdminLanguage: 'Choose the language for the host/admin portal.',
        currentPortalLanguage: 'Current portal language',
        english: 'English',
        norwegian: 'Norwegian',
        vietnamese: 'Vietnamese',
        tagalog: 'Tagalog',
        auto: 'Automatic (based on location)',
        preferenceSaved: 'Language preference updated.',
    },
    nav: {
        dashboard: 'Dashboard',
        manager: 'Manager',
        apartments: 'Apartments',
        bookings: 'Bookings',
        apartmentsBookings: 'Apartments & bookings',
        myApartments: 'My apartments',
        addApartment: 'Add new apartment',
        prefilledApartment: 'Prefilled apartment details',
        prefilledApartmentDemo: 'Prefilled demo apartment',
        allApartments: 'All apartments',
        myBookings: 'My bookings',
        addBooking: 'Add new booking',
        addNew: 'Add new',
        allBookings: 'All bookings',
        calendar: 'Calendar',
        customers: 'Customers',
        administration: 'Administration',
        hostsPartners: 'Hosts / Partners',
        managementCompanies: 'Management companies',
        hostsList: 'Hosts',
        hostApplications: 'Host applications',
        hostPointsSystem: 'Host points system',
        users: 'Users',
        adminUsers: 'Administrators',
        platformContent: 'Platform content',
        buildings: 'Buildings',
        locations: 'Countries & locations',
        priceMatrix: 'Price matrix',
        houseRulesFacilities: 'House rules & facilities',
        opsChecklists: 'Operations checklists',
        configuration: 'Configuration',
        payouts: 'Payouts',
        myTeam: 'My team',
        salesTeam: 'Sales team',
        operationsTeam: 'Operations team',
        managementCompany: 'Management company',
        hostAgents: 'Host Agents',
        ambassadors: 'Ambassadors',
        campaign: 'Campaign',
        discountVisibility: 'Discounts & visibility',
        financeReports: 'Finance & reports',
        finance: 'Finance',
        reports: 'Reports',
        communicationAccount: 'Communication & account',
        communication: 'Communication',
        marketing: 'Marketing',
        hostPoints: 'Host points / Exit',
        settings: 'Settings',
        generalSettings: 'General settings',
        emailSettings: 'Email settings',
        emailTemplates: 'Email templates',
        languages: 'Language files',
        notificationPreferences: 'Notification preferences',
        changePassword: 'Change password',
    },
    auth: {
        login: 'Sign in',
        logout: 'Logout',
    },
    adminUsers: {
        title: 'Administrators',
        subtitle: 'Create accounts, change roles, or reset passwords.',
        createTitle: 'Add user',
        createButton: 'Create user',
        name: 'Name',
        email: 'Email',
        password: 'Password',
        role: 'Role',
        updated: 'Updated',
        actions: 'Actions',
        searchPlaceholder: 'Search name or email…',
        allRoles: 'All roles',
        filter: 'Filter',
        empty: 'No users found.',
        editUser: 'Edit',
        editTitle: 'Edit user',
        newPassword: 'New password',
        passwordHint: 'Leave blank to keep the current password.',
        passwordTooShort: 'Password must be at least 8 characters.',
        accessDenied: 'Only administrators can manage users.',
        created: 'User created successfully.',
        updateSuccess: 'User updated successfully.',
        loadFailed: 'Could not load users.',
        createFailed: 'Could not create user.',
        updateFailed: 'Could not update user.',
    },
    languages: {
        title: 'Language Files',
        installed: 'Installed languages',
        language: 'Language',
        stringsLoaded: 'Strings loaded',
        sourceLanguage: 'Source language',
        default: 'Default',
        manage: 'Manage',
        addLanguage: 'Add language',
        languageCode: 'Language code',
        displayName: 'Display name',
        shortCode: 'Short code',
        saveTranslations: 'Save translations',
        uploadHint: 'Upload a JSON file or paste JSON below. Keys must be English source strings; values are the translated text.',
        preview: 'Translation preview',
        deleteLanguage: 'Delete language',
        deleteConfirm: 'Delete this language and its translation file?',
    },
};

export const i18n = createI18n({
    legacy: false,
    globalInjection: true,
    locale: DEFAULT_LOCALE,
    fallbackLocale: DEFAULT_LOCALE,
    messages: {
        [DEFAULT_LOCALE]: englishMessages,
    },
});

function mergeMessages(base, overlay) {
    const result = { ...base };

    for (const [key, value] of Object.entries(overlay ?? {})) {
        if (value && typeof value === 'object' && !Array.isArray(value)) {
            result[key] = mergeMessages(base[key] ?? {}, value);
        } else if (value !== undefined && value !== '') {
            result[key] = value;
        }
    }

    return result;
}

export async function loadLocaleMessages(locale) {
    const slug = locale || DEFAULT_LOCALE;

    if (slug === DEFAULT_LOCALE) {
        i18n.global.setLocaleMessage(DEFAULT_LOCALE, englishMessages);
        i18n.global.locale.value = DEFAULT_LOCALE;
        document.documentElement.lang = 'en';
        return DEFAULT_LOCALE;
    }

    try {
        const payload = await apiClient.get(`/locales/${slug}/messages`);
        const messages = mergeMessages(englishMessages, payload?.data?.messages ?? {});

        i18n.global.setLocaleMessage(slug, messages);
        i18n.global.locale.value = slug;
        document.documentElement.lang = payload?.data?.intl ?? slug;
    } catch {
        i18n.global.locale.value = DEFAULT_LOCALE;
        document.documentElement.lang = 'en';
    }

    return i18n.global.locale.value;
}

export async function bootstrapLocale() {
    try {
        const payload = await apiClient.get('/locale');
        const locale = payload?.data?.locale ?? DEFAULT_LOCALE;
        await loadLocaleMessages(locale);
        return payload?.data ?? payload;
    } catch {
        await loadLocaleMessages(DEFAULT_LOCALE);
        return { locale: DEFAULT_LOCALE };
    }
}

export async function setUserLocale(preference) {
    let payload;

    try {
        payload = await apiClient.put('/locale', { locale: preference });
    } catch (err) {
        if (err.status === 401) {
            payload = await apiClient.put('/public/locale', { locale: preference });
        } else {
            throw err;
        }
    }

    const resolved = payload?.data?.locale ?? DEFAULT_LOCALE;
    await loadLocaleMessages(resolved);
    return payload?.data ?? payload;
}

export function localeLabelKey(slug) {
    const map = {
        en: 'locale.english',
        no: 'locale.norwegian',
        vi: 'locale.vietnamese',
        tl: 'locale.tagalog',
    };
    return map[slug] ?? 'locale.label';
}
