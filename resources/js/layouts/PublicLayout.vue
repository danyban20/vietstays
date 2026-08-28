<template>
    <div v-if="isLegacyRoute" :class="legacyLayoutClass">
        <main class="public-main">
            <router-view />
        </main>
    </div>
    <div v-else class="public-site">
        <header class="public-header">
            <div class="public-header__inner">
                <router-link :to="{ name: 'home' }" class="public-header__brand">Vietstays</router-link>
                <nav class="public-header__nav">
                    <router-link :to="{ name: 'public-apartments' }" class="public-header__link">
                        Apartments
                    </router-link>
                    <router-link :to="{ name: 'host-application' }" class="public-header__link">
                        Become a host
                    </router-link>
                    <a href="/admin" class="public-header__link public-header__link--host">Host login</a>
                </nav>
            </div>
        </header>

        <main class="public-main">
            <router-view />
        </main>

        <footer class="public-footer">
            <div class="public-footer__inner">
                <span>© {{ year }} Vietstays</span>
                <a href="/admin">Property hosts</a>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const year = new Date().getFullYear();
const isLegacyRoute = computed(() =>
    route.name === 'home' ||
    route.name === 'public-apartments' ||
    route.name === 'public-apartment',
);
const legacyLayoutClass = computed(() => ({
    'public-site': true,
    'public-site--legacy': true,
    'public-site--home': route.name === 'home',
}));
</script>
