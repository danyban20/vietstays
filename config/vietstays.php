<?php

return [
    'site_currency' => env('VIETSTAYS_CURRENCY', 'VND'),
    'booking_fee_percent' => (float) env('VIETSTAYS_BOOKING_FEE_PERCENT', 5),
    'airport_pickup_cost' => (float) env('VIETSTAYS_AIRPORT_PICKUP_COST', 0),

    'email_template_codes' => [
        'user_registration',
        'user_booking_confirmation',
        'admin_booking_confirmation',
        'staff_welcome',
        'user_new_task_notification',
        'user_task_updated_notification',
        'user_task_new_comment_notifica',
        'user_new_task_comment_notification',
        'user_task_comment_updated_notification',
        'host_application_submitted',
        'host_application_under_review',
        'host_application_approved',
        'host_application_rejected',
        'host_welcome',
        'host_activation_reminder',
        'team_invitation',
        'guest_message',
        'host_new_booking',
        'guest_booking_confirmed',
        'guest_booking_cancelled',
        'guest_booking_updated',
        'host_guest_cancelled',
        'host_extra_cleaning_requested',
        'guest_extra_cleaning_updated',
    ],

    // Shown on the public terms & contact page; leave empty to hide a line.
    'contact_email' => env('VIETSTAYS_CONTACT_EMAIL', ''),
    'contact_phone' => env('VIETSTAYS_CONTACT_PHONE', ''),

    // Customer dashboard ("My account").
    'guest_area' => [
        // The door code and Wi-Fi show up this many hours before check-in.
        'access_reveal_hours' => (int) env('VIETSTAYS_ACCESS_REVEAL_HOURS', 24),
        // Guests can cancel free of charge until this many days before
        // check-in (0 = until the day of arrival). After that the late fee
        // below applies. Bookings are paid on arrival, so the fee is only
        // shown as information for now.
        'free_cancellation_days' => (int) env('VIETSTAYS_FREE_CANCELLATION_DAYS', 0),
        'late_cancellation_percent' => (int) env('VIETSTAYS_LATE_CANCELLATION_PERCENT', 0),
        // Same-day extra cleaning must be ordered before this hour (local time).
        'same_day_cleaning_cutoff_hour' => 10,
        'cleaning_slots' => [
            'morning' => ['label' => 'Morning', 'hours' => '9:00 AM – 12:00 PM'],
            'afternoon' => ['label' => 'Afternoon', 'hours' => '1:00 PM – 5:00 PM'],
        ],
    ],

    'email_locales' => [
        'en' => ['short' => 'EN', 'label' => 'English'],
        'no' => ['short' => 'NO', 'label' => 'Norwegian'],
        'vi' => ['short' => 'VI', 'label' => 'Vietnamese'],
        'tl' => ['short' => 'TL', 'label' => 'Tagalog'],
    ],

    /*
    | English source strings for the Vue host dashboard.
    | Values are translated via lang/vietstays/{locale}.php|.json files (WP legacy format).
    */
    'ui_messages' => [
        'brand' => 'Vietstays',
        'common' => [
            'save' => 'Save',
            'cancel' => 'Cancel',
            'searchPlaceholder' => 'Search booking, guest or apartment…',
            'hostDashboard' => 'Host dashboard',
            'hostApartments' => 'Host {count} apartment',
            'hostApartments_other' => 'Host {count} apartments',
            'loading' => 'Loading…',
            'automatic' => 'Automatic (based on location)',
        ],
        'locale' => [
            'label' => 'Language',
            'adminLanguage' => 'Admin language',
            'chooseAdminLanguage' => 'Choose the language for the host/admin portal.',
            'currentPortalLanguage' => 'Current portal language',
            'english' => 'English',
            'norwegian' => 'Norwegian',
            'vietnamese' => 'Vietnamese',
            'tagalog' => 'Tagalog',
            'auto' => 'Automatic (based on location)',
            'preferenceSaved' => 'Language preference updated.',
        ],
        'nav' => [
            'dashboard' => 'Dashboard',
            'manager' => 'Manager',
            'apartmentsBookings' => 'Apartments & bookings',
            'myApartments' => 'My apartments',
            'addApartment' => 'Add new apartment',
            'prefilledApartment' => 'Prefilled apartment details',
            'allApartments' => 'All apartments',
            'myBookings' => 'My bookings',
            'addBooking' => 'Add new booking',
            'allBookings' => 'All bookings',
            'calendar' => 'Calendar',
            'administration' => 'Administration',
            'hostApplications' => 'Host applications',
            'myTeam' => 'My team',
            'operationsTeam' => 'Operations team',
            'managementCompany' => 'Management company',
            'hostAgents' => 'Host Agents',
            'ambassadors' => 'Ambassadors',
            'campaign' => 'Campaign',
            'financeReports' => 'Finance & reports',
            'finance' => 'Finance',
            'reports' => 'Reports',
            'communicationAccount' => 'Communication & account',
            'communication' => 'Communication',
            'marketing' => 'Marketing',
            'hostPoints' => 'Host points / Exit',
            'settings' => 'Settings',
            'generalSettings' => 'General settings',
            'emailSettings' => 'Email settings',
            'emailTemplates' => 'Email templates',
            'languages' => 'Language files',
            'notificationPreferences' => 'Notification preferences',
            'changePassword' => 'Change password',
        ],
        'auth' => [
            'login' => 'Sign in',
            'logout' => 'Logout',
        ],
        'languages' => [
            'title' => 'Language Files',
            'installed' => 'Installed languages',
            'language' => 'Language',
            'stringsLoaded' => 'Strings loaded',
            'sourceLanguage' => 'Source language',
            'default' => 'Default',
            'manage' => 'Manage',
            'addLanguage' => 'Add language',
            'languageCode' => 'Language code',
            'displayName' => 'Display name',
            'shortCode' => 'Short code',
            'saveTranslations' => 'Save translations',
            'uploadHint' => 'Upload a JSON file or paste JSON below. Keys must be English source strings; values are the translated text.',
            'preview' => 'Translation preview',
            'deleteLanguage' => 'Delete language',
            'deleteConfirm' => 'Delete this language and its translation file?',
        ],
    ],
];
