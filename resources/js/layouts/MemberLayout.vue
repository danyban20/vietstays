<template>
    <div v-if="ready" class="member-shell">
        <div id="header">
            <div class="container">
                <div class="header">
                    <a v-if="route.meta.backTo" href="#" class="backlink" aria-label="Back" @click.prevent="goBack" />
                    <div class="logo">
                        <router-link :to="{ name: 'home' }" aria-label="Vietstays home" />
                    </div>
                    <div id="nav">
                        <div class="desk_nav">
                            <ul>
                                <li><router-link :to="{ name: 'public-apartments' }">Find a stay</router-link></li>
                                <li v-for="item in navItems" :key="item.name" :class="{ 'current-menu-item': isActive(item) }">
                                    <router-link :to="{ name: item.name }">{{ item.label }}</router-link>
                                </li>
                            </ul>
                        </div>
                        <div class="mob_nav">
                            <ul>
                                <li v-for="item in navItems" :key="item.name" :class="{ 'current-menu-item': isActive(item) }">
                                    <router-link :to="{ name: item.name }">{{ item.mobileLabel || item.label }}</router-link>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="head_right">
                        <div class="notification">
                            <router-link
                                :to="{ name: 'member-messages' }"
                                class="bell_btn"
                                :class="{ has_unread: area.unread > 0 }"
                                :aria-label="area.unread > 0 ? `${area.unread} unread messages` : 'Messages'"
                            />
                        </div>
                        <div class="user_login" :class="{ open: menuOpen }">
                            <a href="#" class="user_login_btn" aria-label="Account menu" aria-haspopup="true" :aria-expanded="menuOpen" @click.prevent="menuOpen = !menuOpen">
                                <span class="member-avatar">{{ initials }}</span>
                            </a>
                            <div v-if="menuOpen" class="member-user-menu">
                                <p class="member-user-menu__name">{{ member.user?.name }}<span>{{ member.user?.email }}</span></p>
                                <router-link :to="{ name: 'member-profile' }" @click="menuOpen = false">Profile</router-link>
                                <a v-if="member.user?.can_use_dashboard" href="/admin">Host dashboard</a>
                                <a href="#" @click.prevent="signOut">Sign out</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <router-view />

        <div v-if="area.toast" class="member-toast" :class="`member-toast--${area.toast.tone}`" role="status">
            {{ area.toast.message }}
        </div>
    </div>
    <div v-else class="member-shell-loading" aria-busy="true" />
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useMemberAreaStyles } from '@/composables/useMemberAreaStyles';
import { useMemberStore } from '@/stores/member';
import { useMemberAreaStore } from '@/stores/memberArea';

const route = useRoute();
const router = useRouter();
const member = useMemberStore();
const area = useMemberAreaStore();
const { mount, unmount } = useMemberAreaStyles();

const ready = ref(false);
const menuOpen = ref(false);

const navItems = [
    { name: 'member-account', label: 'Dashboard', mobileLabel: 'Home', section: 'dashboard' },
    { name: 'member-reservations', label: 'Reservations', section: 'reservations' },
    { name: 'member-messages', label: 'Messages', section: 'messages' },
    { name: 'member-profile', label: 'Profile', section: 'profile' },
];

const initials = computed(() => {
    const name = (member.user?.name ?? '').trim();

    return (
        name
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2)
            .map((part) => part[0].toUpperCase())
            .join('') || '?'
    );
});

function isActive(item) {
    return route.meta.memberSection === item.section;
}

function goBack() {
    if (window.history.state?.back) {
        router.back();
    } else {
        router.push({ name: route.meta.backTo });
    }
}

async function signOut() {
    menuOpen.value = false;
    await member.logout();
    router.push({ name: 'home' });
}

function onDocumentClick(event) {
    if (!event.target.closest('.user_login')) {
        menuOpen.value = false;
    }
}

watch(
    () => route.fullPath,
    () => {
        menuOpen.value = false;
    },
);

onMounted(async () => {
    document.addEventListener('click', onDocumentClick);
    await mount();
    ready.value = true;
    area.refreshUnread();
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onDocumentClick);
    unmount();
});
</script>
