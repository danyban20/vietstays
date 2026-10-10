<template>
    <div id="dashboard" :class="pageClass">
        <div class="container">
            <div v-if="loading" class="member-loading">Loading your account…</div>
            <div v-else-if="error" class="member-error">
                <p>{{ error }}</p>
                <button type="button" class="btn" @click="load">Try again</button>
            </div>
            <template v-else>
                <h1>Welcome back, {{ data.first_name }}</h1>
                <div class="current_date_text"><p>{{ data.today }}</p></div>

                <div v-if="isCurrent" class="msz_box">
                    <div class="msz_text">
                        {{ featured.access.door_code_visible || featured.access.wifi_visible ? 'Your stay is active — access details are ready.' : 'Your stay is active.' }}
                    </div>
                    <router-link :to="detailRoute(featured)" class="open_msz">View access details</router-link>
                </div>
                <div v-else-if="data.unread.count > 0" class="msz_box">
                    <div class="msz_text">
                        {{ data.unread.count }} unread {{ data.unread.count === 1 ? 'message' : 'messages' }} from your host, {{ data.unread.from }}
                    </div>
                    <router-link :to="{ name: 'member-messages', params: { conversation: data.unread.conversation_id } }" class="open_msz">
                        Open Messages
                    </router-link>
                </div>

                <div v-if="featured" class="app_white_block">
                    <div class="img">
                        <img :src="featured.apartment.image || placeholderImage" :alt="featured.apartment.name" @error="onImageError" />
                    </div>
                    <div class="desc">
                        <div class="confirm_msz">
                            <strong :class="`badge_${featured.badge.tone}`">{{ isCurrent ? 'Active stay' : featured.badge.label }}</strong>
                            {{ countdownLabel }}
                        </div>
                        <h2>{{ featured.apartment.name }}</h2>
                        <div v-if="featured.apartment.area" class="place">
                            <img :src="asset('/member/images/map-pin.svg')" alt="" />{{ featured.apartment.area }}
                        </div>
                        <div class="check_in_info">
                            <p>
                                CHECK-IN<strong>{{ formatDay(featured.check_in) }}</strong>
                                {{ isCurrent ? 'Checked in from' : 'at' }} {{ featured.check_in_time }}
                            </p>
                            <p>
                                CHECK-OUT<strong>{{ formatDay(featured.check_out) }}</strong>
                                {{ isCurrent ? 'Check-out at' : 'by' }} {{ featured.check_out_time }}
                            </p>
                        </div>
                        <div class="btn_wrap">
                            <router-link :to="detailRoute(featured)" class="btn">View stay details</router-link>
                            <router-link
                                v-if="featured.conversation_id"
                                :to="{ name: 'member-messages', params: { conversation: featured.conversation_id }, query: { booking: featured.id } }"
                                class="btn border_btn"
                            >
                                Contact Host
                            </router-link>
                        </div>
                        <div v-if="isCurrent && (featured.access.door_code || featured.access.wifi_network)" class="facility_room">
                            <div v-if="featured.access.door_code" class="block">
                                <p>DOOR ACCESS CODE<strong>{{ featured.access.door_code }}</strong></p>
                            </div>
                            <div v-if="featured.access.wifi_network" class="block">
                                <p>
                                    WI-FI DETAILS
                                    <strong>{{ featured.access.wifi_network }}<template v-if="featured.access.wifi_password"> · {{ featured.access.wifi_password }}</template></strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="icon_white_block">
                    <div class="img"><img :src="asset('/member/images/pin_2.svg')" alt="" /></div>
                    <h3>No upcoming stays</h3>
                    <p>Ready to explore Vietnam? Find your perfect premium apartment.</p>
                    <router-link :to="{ name: 'public-apartments' }" class="btn">Find an apartment</router-link>
                </div>

                <div :class="featured ? 'plan_next_stay' : 'plan_next_stay_wrap'">
                    <div class="subtitle_top">
                        <h4>{{ featured ? 'Plan your next stay' : 'Where to next?' }}</h4>
                    </div>
                    <PlanStaySearch />
                </div>

                <div v-if="data.past.length" class="app_past_expereince">
                    <div class="subtitle_top">
                        <h4>Your past experiences</h4>
                        <router-link :to="{ name: 'member-reservations', query: { tab: 'previous' } }" class="view_all_link">
                            View all reservations
                        </router-link>
                    </div>
                    <div class="block_wrap">
                        <router-link v-for="stay in data.past" :key="stay.id" :to="detailRoute(stay)" class="block">
                            <div class="img">
                                <img :src="stay.apartment.image || placeholderImage" :alt="stay.apartment.name" loading="lazy" @error="onImageError" />
                            </div>
                            <div class="desc">
                                <p>
                                    <strong>{{ stay.apartment.name }}</strong>{{ stay.apartment.area }}<br />
                                    {{ formatMonthYear(stay.check_in) }} · {{ plural(stay.nights, 'night') }}
                                </p>
                            </div>
                        </router-link>
                    </div>
                </div>

                <PopularPlaces v-if="!data.past.length || isCurrent" />
            </template>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import apiClient from '@/api/client';
import PlanStaySearch from '@/components/member/PlanStaySearch.vue';
import PopularPlaces from '@/components/member/PopularPlaces.vue';
import { useMemberAreaStore } from '@/stores/memberArea';
import { withAppBase } from '@/utils/app-base';
import { formatDay, formatMonthYear, plural } from '@/utils/member-format';

const area = useMemberAreaStore();

const loading = ref(true);
const error = ref('');
const data = ref(null);

const placeholderImage = withAppBase('/member/images/place_image_1.png');
const asset = (path) => withAppBase(path);

const featured = computed(() => data.value?.featured ?? null);
const isCurrent = computed(() => featured.value?.stage === 'current');

const pageClass = computed(() => {
    if (loading.value || error.value) {
        return 'dashboard_main';
    }

    if (isCurrent.value) {
        return 'dashboard_current_stay';
    }

    return featured.value ? 'dashboard_main' : 'dashboard_no_booking';
});

const countdownLabel = computed(() => {
    const stay = featured.value;

    if (!stay) {
        return '';
    }

    if (stay.stage === 'current') {
        return 'Currently staying';
    }

    const days = stay.days_until_check_in ?? 0;

    if (days === 0) {
        return 'Check-in is today';
    }

    return days === 1 ? '1 day until check-in' : `${days} days until check-in`;
});

function detailRoute(stay) {
    return { name: 'member-reservation', params: { id: stay.id } };
}

function onImageError(event) {
    if (!event.target.src.endsWith(placeholderImage)) {
        event.target.src = placeholderImage;
    }
}

async function load() {
    loading.value = true;
    error.value = '';

    try {
        const res = await apiClient.get('/member/dashboard');
        data.value = res.data;
        area.unread = res.data.unread?.count ?? 0;
    } catch (err) {
        error.value = err.message || 'Could not load your account.';
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>
