<template>
    <div id="dashboard" class="booking_list" :class="{ booking_list_noreservation: !loading && !reservations.length }">
        <div class="container">
            <h1>Reservations</h1>
            <div class="booking_tabs_wrap">
                <ul class="booking_tabs" role="tablist">
                    <li
                        v-for="tab in tabs"
                        :key="tab.id"
                        role="tab"
                        tabindex="0"
                        :aria-selected="activeTab === tab.id"
                        :class="{ active: activeTab === tab.id }"
                        @click="setTab(tab.id)"
                        @keydown.enter="setTab(tab.id)"
                    >
                        {{ tab.label }}<span v-if="counts[tab.id]" class="member-tab-count">{{ counts[tab.id] }}</span>
                    </li>
                </ul>
                <select v-if="reservations.length" v-model="sort" class="sortby_sel" aria-label="Sort reservations">
                    <option value="newest">Sort by: Date (newest)</option>
                    <option value="oldest">Sort by: Date (oldest)</option>
                </select>
            </div>

            <div v-if="loading" class="member-loading">Loading reservations…</div>
            <div v-else-if="error" class="member-error">
                <p>{{ error }}</p>
                <button type="button" class="btn" @click="load">Try again</button>
            </div>

            <div v-else-if="!reservations.length" class="icon_white_block">
                <div class="img"><img :src="asset('/member/images/ticket.svg')" alt="" /></div>
                <h3>No reservations yet</h3>
                <p>Start planning your stay in Vietnam with Vietstays.</p>
                <router-link :to="{ name: 'public-apartments' }" class="btn">Browse apartments</router-link>
            </div>

            <div v-else class="booking_content_wrap">
                <div class="booking_content" style="display: block">
                    <p v-if="!visible.length" class="member-empty-tab">{{ emptyTabText }}</p>
                    <div v-for="stay in visible" :key="stay.id" class="reservation_block">
                        <div class="block_left">
                            <div class="img">
                                <img :src="stay.apartment.image || placeholderImage" :alt="stay.apartment.name" loading="lazy" @error="onImageError" />
                            </div>
                            <div class="desc">
                                <div class="book_status">
                                    <strong :class="stay.badge.tone">{{ stay.badge.label }}</strong>Booking ID: {{ stay.booking_num }}
                                </div>
                                <h5>
                                    <router-link :to="detailRoute(stay)">{{ stay.apartment.name }}</router-link>
                                </h5>
                                <p>{{ stay.apartment.area }}</p>
                                <div v-if="stay.stage === 'cancelled'" class="refound_process">
                                    {{ stay.cancelled_by_guest ? 'Cancelled by you · nothing to pay' : 'Cancelled · nothing to pay' }}
                                </div>
                            </div>
                        </div>
                        <div class="block_right">
                            <div class="stay_dates">
                                <span>STAY DATES </span><strong>{{ formatShortRange(stay.check_in, stay.check_out) }}</strong>
                                {{ plural(stay.nights, 'night') }} · {{ plural(stay.guests, 'Guest') }}
                            </div>
                            <div class="total">
                                <div class="total_paid" :class="{ ref_amount: stay.stage === 'cancelled' }">
                                    {{ totalLabel(stay) }}<strong>{{ formatMoney(stay.total, stay.currency) }}</strong>
                                </div>
                                <router-link :to="detailRoute(stay)" class="view_details_link">View details</router-link>
                                <a
                                    v-if="stay.stage !== 'cancelled'"
                                    href="#"
                                    class="download_btn"
                                    :class="{ is_busy: downloading === stay.id }"
                                    @click.prevent="download(stay)"
                                >
                                    {{ downloading === stay.id ? 'Preparing…' : 'Download' }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import apiClient, { downloadFile } from '@/api/client';
import { useMemberAreaStore } from '@/stores/memberArea';
import { withAppBase } from '@/utils/app-base';
import { formatMoney, formatShortRange, plural } from '@/utils/member-format';

const route = useRoute();
const router = useRouter();
const area = useMemberAreaStore();

const tabs = [
    { id: 'all', label: 'All' },
    { id: 'upcoming', label: 'Upcoming' },
    { id: 'current', label: 'Current stay' },
    { id: 'previous', label: 'Previous' },
    { id: 'cancelled', label: 'Cancelled' },
];

const loading = ref(true);
const error = ref('');
const reservations = ref([]);
const sort = ref('newest');
const downloading = ref(null);
const activeTab = ref(tabs.some((tab) => tab.id === route.query.tab) ? route.query.tab : 'all');

const placeholderImage = withAppBase('/member/images/place_image_1.png');
const asset = (path) => withAppBase(path);

const stageForTab = { upcoming: 'upcoming', current: 'current', previous: 'past', cancelled: 'cancelled' };

const counts = computed(() => {
    const result = {};

    for (const [tab, stage] of Object.entries(stageForTab)) {
        result[tab] = reservations.value.filter((stay) => stay.stage === stage).length;
    }

    return result;
});

const visible = computed(() => {
    const stage = stageForTab[activeTab.value];
    const list = stage ? reservations.value.filter((stay) => stay.stage === stage) : [...reservations.value];

    return list.sort((a, b) =>
        sort.value === 'newest' ? b.check_in.localeCompare(a.check_in) : a.check_in.localeCompare(b.check_in),
    );
});

const emptyTabText = computed(() => {
    switch (activeTab.value) {
        case 'upcoming':
            return 'You have no upcoming stays.';
        case 'current':
            return 'You are not staying with us right now.';
        case 'previous':
            return 'No previous stays yet.';
        case 'cancelled':
            return 'No cancelled reservations.';
        default:
            return '';
    }
});

function setTab(id) {
    activeTab.value = id;
    router.replace({ query: id === 'all' ? {} : { tab: id } });
}

function totalLabel(stay) {
    if (stay.stage === 'cancelled') {
        return 'Booking total';
    }

    return stay.stage === 'past' ? 'Total paid' : 'Total';
}

function detailRoute(stay) {
    return { name: 'member-reservation', params: { id: stay.id } };
}

function onImageError(event) {
    if (!event.target.src.endsWith(placeholderImage)) {
        event.target.src = placeholderImage;
    }
}

async function download(stay) {
    if (downloading.value) {
        return;
    }

    downloading.value = stay.id;
    const type = stay.stage === 'past' && stay.status === 'confirmed' ? 'receipt' : 'confirmation';

    try {
        await downloadFile(`/member/reservations/${stay.id}/documents/${type}`, `vietstays-${type}.pdf`);
    } catch (err) {
        area.notify(err.message || 'Could not download the document.', 'error');
    } finally {
        downloading.value = null;
    }
}

async function load() {
    loading.value = true;
    error.value = '';

    try {
        const res = await apiClient.get('/member/reservations');
        reservations.value = res.data ?? [];
    } catch (err) {
        error.value = err.message || 'Could not load your reservations.';
    } finally {
        loading.value = false;
    }
}

watch(
    () => route.query.tab,
    (tab) => {
        activeTab.value = tabs.some((item) => item.id === tab) ? tab : 'all';
    },
);

onMounted(load);
</script>
