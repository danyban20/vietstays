<template>
    <svg
        class="host-sidebar__icon"
        width="15"
        height="15"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
    >
        <template v-if="name === 'dashboard'">
            <rect x="3" y="3" width="8" height="8" rx="1.5" />
            <rect x="13" y="3" width="8" height="8" rx="1.5" />
            <rect x="3" y="13" width="8" height="8" rx="1.5" />
            <rect x="13" y="13" width="8" height="8" rx="1.5" />
        </template>

        <template v-else-if="name === 'apartments'">
            <path d="M3 11l9-7 9 7" />
            <path d="M5 10v10h14V10" />
        </template>

        <template v-else-if="name === 'bookings'">
            <rect x="5" y="4" width="14" height="17" rx="1.5" />
            <line x1="9" y1="9" x2="15" y2="9" />
            <line x1="9" y1="13" x2="15" y2="13" />
            <line x1="9" y1="17" x2="13" y2="17" />
        </template>

        <template v-else-if="name === 'team' || name === 'customers'">
            <circle cx="12" cy="8" r="3.4" />
            <path d="M5 20c0-3.6 3.1-6 7-6s7 2.4 7 6" />
        </template>

        <template v-else-if="name === 'users'">
            <circle cx="12" cy="8" r="3.4" />
            <path d="M5 20c0-3.6 3.1-6 7-6s7 2.4 7 6" />
        </template>

        <template v-else-if="name === 'sales-team'">
            <circle cx="8" cy="9" r="3" />
            <circle cx="16" cy="9" r="3" />
            <path d="M2 20c0-3 2.7-5 6-5s6 2 6 5" />
            <path d="M10 20c0-3 2.7-5 6-5s6 2 6 5" />
        </template>

        <template v-else-if="name === 'operations'">
            <path d="M4 20h16" />
            <path d="M9 20V9l4-5 4 5v11" />
            <path d="M6 20v-6l3-2" />
        </template>

        <template v-else-if="name === 'content'">
            <rect x="4" y="3" width="16" height="18" rx="1" />
            <line x1="8" y1="8" x2="16" y2="8" />
            <line x1="8" y1="12" x2="16" y2="12" />
            <line x1="8" y1="16" x2="13" y2="16" />
        </template>

        <template v-else-if="name === 'payouts'">
            <rect x="3" y="6" width="18" height="12" rx="2" />
            <line x1="3" y1="10" x2="21" y2="10" />
        </template>

        <template v-else-if="name === 'management'">
            <rect x="4" y="3" width="16" height="18" rx="1" />
            <line x1="8" y1="8" x2="8" y2="8.01" />
            <line x1="12" y1="8" x2="12" y2="8.01" />
            <line x1="16" y1="8" x2="16" y2="8.01" />
            <line x1="8" y1="12" x2="8" y2="12.01" />
            <line x1="12" y1="12" x2="12" y2="12.01" />
            <line x1="16" y1="12" x2="16" y2="12.01" />
            <line x1="10" y1="21" x2="10" y2="16" />
            <line x1="14" y1="21" x2="14" y2="16" />
        </template>

        <template v-else-if="name === 'host-agents'">
            <circle cx="8" cy="9" r="3" />
            <circle cx="16" cy="9" r="3" />
            <path d="M2 20c0-3 2.7-5 6-5s6 2 6 5" />
            <path d="M10 20c0-3 2.7-5 6-5s6 2 6 5" />
        </template>

        <template v-else-if="name === 'ambassadors'">
            <path d="M12 3l2.6 5.8 6.4.6-4.8 4.2 1.5 6.3L12 16.8 6.3 19.9l1.5-6.3-4.8-4.2 6.4-.6z" />
        </template>

        <template v-else-if="name === 'campaign'">
            <circle cx="12" cy="12" r="8" />
            <circle cx="12" cy="12" r="4" />
            <circle cx="12" cy="12" r="0.5" fill="currentColor" />
        </template>

        <template v-else-if="name === 'finance'">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v10" />
            <path d="M9 9c0-1.5 1.3-2 3-2s3 .8 3 2-1.3 1.8-3 2-3 .5-3 2 1.3 2 3 2 3-.5 3-2" />
        </template>

        <template v-else-if="name === 'reports'">
            <line x1="5" y1="20" x2="5" y2="12" />
            <line x1="12" y1="20" x2="12" y2="6" />
            <line x1="19" y1="20" x2="19" y2="15" />
            <line x1="3" y1="20" x2="21" y2="20" />
        </template>

        <template v-else-if="name === 'communication'">
            <rect x="3" y="5" width="18" height="14" rx="1.5" />
            <path d="M3 6l9 7 9-7" />
        </template>

        <template v-else-if="name === 'marketing'">
            <path d="M3 11l16-7-6 16-3-7z" />
            <path d="M13 13l-4 6" />
        </template>

        <template v-else-if="name === 'host-points'">
            <circle cx="12" cy="9" r="5" />
            <path d="M8 13l-2 8 6-3 6 3-2-8" />
        </template>

        <template v-else-if="name === 'settings'">
            <circle cx="12" cy="12" r="3" />
            <path
                d="M19.4 13.5a1.7 1.7 0 000-3l1.1-1.9-2-2-1.9 1.1a1.7 1.7 0 00-3 0L13 4.5h-2l-.6 2.2a1.7 1.7 0 00-3 0L5.5 5.6l-2 2 1.1 1.9a1.7 1.7 0 000 3L3.5 14.5l2 2 1.9-1.1a1.7 1.7 0 003 0l.6 2.1h2l.6-2.1a1.7 1.7 0 003 0l1.9 1.1 2-2z"
            />
        </template>

        <template v-else-if="name === 'administration'">
            <rect x="3" y="3" width="18" height="18" rx="2" />
            <path d="M9 9h6v6H9z" />
            <path d="M9 3v6" />
            <path d="M15 3v6" />
            <path d="M9 15v6" />
            <path d="M15 15v6" />
            <path d="M3 9h6" />
            <path d="M15 9h6" />
            <path d="M3 15h6" />
            <path d="M15 15h6" />
        </template>
    </svg>
</template>

<script setup>
defineProps({
    name: {
        type: String,
        required: true,
    },
});
</script>
