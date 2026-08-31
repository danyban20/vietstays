<template>
    <nav class="host-settings-subnav" aria-label="Settings sections">
        <router-link
            v-for="item in items"
            :key="item.name"
            :to="{ name: item.name }"
            class="host-settings-subnav__link"
            :class="{ 'host-settings-subnav__link--active': route.name === item.name }"
        >
            {{ t(item.labelKey) }}
        </router-link>
    </nav>
</template>

<script setup>
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';

const route = useRoute();
const { t } = useI18n();

const items = [
    { name: 'settings-email', labelKey: 'nav.emailSettings' },
    { name: 'settings-email-templates', labelKey: 'nav.emailTemplates' },
    { name: 'settings-languages', labelKey: 'nav.languages' },
];
</script>
