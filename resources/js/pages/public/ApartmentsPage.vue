<template>
    <div class="host-apartments">
        <div id="header" :class="{ 'fixed-header': headerFixed }">
            <div class="container">
                <div class="header">
                    <div class="logo">
                        <router-link :to="{ name: 'home' }">
                            <img v-if="!logoFailed" :src="HOME_IMAGES.logo" alt="Visit Vietnam" @error="logoFailed = true" />
                            <span v-else class="host-home-logo-fallback">Visit Vietnam</span>
                        </router-link>
                    </div>
                    <a href="#" id="menubtn" class="host-home-menu-btn" @click.prevent="mobileNavOpen = !mobileNavOpen">
                        <span /><span /><span /><span />
                    </a>
                    <div class="head_right" :class="{ 'host-home-nav-open': mobileNavOpen }">
                        <div id="nav">
                            <ul class="menu">
                                <li><router-link :to="{ name: 'public-apartments' }">Districts</router-link></li>
                                <li>
                                    <router-link :to="{ name: 'host-application' }">Share your apartment</router-link>
                                </li>
                                <li><a href="#" @click.prevent>Save 30% now</a></li>
                            </ul>
                        </div>
                        <div class="lang">
                            <a href="#" class="lang_btn" @click.prevent>EN</a>
                        </div>
                        <a href="#" class="btn" @click.prevent>Register now</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="apartments" id="city_wrap">
            <div class="container">
                <div class="head">
                    <div class="breadcrumbs">
                        <ul>
                            <li><router-link :to="{ name: 'home' }">Home</router-link></li>
                            <li><span>Apartments</span></li>
                        </ul>
                    </div>
                    <h1 class="title heading-1">{{ pageTitle }}</h1>
                </div>

                <form class="host-apartments-search" @submit.prevent="applyFilters">
                    <div class="city_filter">
                        <div class="city_filter_left">
                            <select v-model="filters.city" name="city_id">
                                <option value="">All Cities</option>
                                <option v-for="city in cities" :key="city.city_id" :value="String(city.city_id)">
                                    {{ city.name }}
                                </option>
                            </select>

                            <div class="dropdown_wrap input_wrap room_sel" :class="{ open: guestsOpen }">
                                <a href="#" class="room_btn rooms_guests_label" @click.prevent="guestsOpen = !guestsOpen">
                                    {{ roomsGuestsLabel }}
                                </a>
                                <div class="rooom_dropdown">
                                    <ul>
                                        <li>
                                            <span class="lbltxt">Rooms<i>Choose amount of rooms</i></span>
                                            <div class="number">
                                                <span class="minus" @click.prevent="adjustCount('rooms', -1)" />
                                                <input v-model.number="filters.rooms" type="text" readonly />
                                                <span class="plus" @click.prevent="adjustCount('rooms', 1)" />
                                            </div>
                                        </li>
                                        <li>
                                            <span class="lbltxt">Adults<i>13 years old or older</i></span>
                                            <div class="number">
                                                <span class="minus" @click.prevent="adjustCount('adults', -1)" />
                                                <input v-model.number="filters.adults" type="text" readonly />
                                                <span class="plus" @click.prevent="adjustCount('adults', 1)" />
                                            </div>
                                        </li>
                                        <li>
                                            <span class="lbltxt">Children<i>below 13 years old</i></span>
                                            <div class="number">
                                                <span class="minus" @click.prevent="adjustCount('children', -1)" />
                                                <input v-model.number="filters.children" type="text" readonly />
                                                <span class="plus" @click.prevent="adjustCount('children', 1)" />
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <select v-model="filters.price">
                                <option value="">Price</option>
                                <option value="low">Low to high</option>
                                <option value="high">High to low</option>
                            </select>
                            <select v-model="filters.sort">
                                <option value="">Sort by recommended</option>
                                <option value="newest">Just added</option>
                                <option value="popular">Popularity</option>
                            </select>
                        </div>
                        <div class="city_filter_right">
                            <a href="#" class="clear_all_btn show_btn" @click.prevent="clearFilters">Clear All</a>
                        </div>
                    </div>

                    <div class="facilities">
                        <ul>
                            <li v-for="item in SEARCH_FACILITIES" :key="item.id">
                                <a
                                    href="#"
                                    :class="{ active: activeFacilities.includes(item.id) }"
                                    @click.prevent="toggleFacility(item.id)"
                                >
                                    <span class="icon"><img :src="item.icon" alt="" /></span>
                                    <span class="name">{{ item.name }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </form>

                <div id="search_result">
                    <div class="city_app_wrap" :class="{ map_shown: viewMode === 'map' }">
                        <div class="title_wrap">
                            <div class="row">
                                <div class="col-md-9">
                                    <div class="mt-4 mb-4">
                                        <div v-if="!selectedDistrict" class="district_headers district_headers_all_districts">
                                            <h3>{{ districtsHeading }}</h3>
                                        </div>
                                        <div v-else class="district_headers">
                                            <a href="#" class="back_btn" @click.prevent="selectedDistrict = null">Back to Districts</a>
                                            <h3>{{ apartmentsHeading }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 text-right city_display_title">
                                    <div class="city_tab">
                                        <a
                                            href="#"
                                            class="apartments_tab_btn"
                                            :class="{ active: viewMode === 'list' }"
                                            @click.prevent="viewMode = 'list'"
                                        >
                                            <span class="icon">
                                                <img :src="listIcon" alt="" />
                                                <img :src="listActiveIcon" class="h_icon" alt="" />
                                            </span>
                                            List
                                        </a>
                                        <a
                                            href="#"
                                            class="apartments_tab_btn"
                                            :class="{ active: viewMode === 'map' }"
                                            @click.prevent="viewMode = 'map'"
                                        >
                                            <span class="icon">
                                                <img :src="mapIcon" alt="" />
                                                <img :src="mapActiveIcon" class="h_icon" alt="" />
                                            </span>
                                            Map
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="loading" class="host-apartments-loading">Loading…</div>

                        <template v-else>
                            <div class="list_wrap">
                                <div v-if="!selectedDistrict" class="districts_list">
                                    <div
                                        v-for="district in districts"
                                        :key="district.district_id"
                                        class="district_block_1"
                                    >
                                        <div class="district_block_1_inner">
                                            <div class="img" :class="{ 'district-greenbox': !district.image }">
                                                <img
                                                    v-if="district.image"
                                                    :src="district.image"
                                                    :alt="district.name"
                                                />
                                                <img
                                                    v-else
                                                    :src="DISTRICT_GREENBOX_LOGO"
                                                    alt=""
                                                    class="district-greenbox__logo"
                                                />
                                            </div>
                                            <div class="cap_1">
                                                <p>{{ district.city_name }}</p>
                                                <h5>{{ district.name }}</h5>
                                            </div>
                                            <div class="cap_2">
                                                <span class="date_text">
                                                    {{ district.apartment_count }}
                                                    {{ district.apartment_count === 1 ? 'apartment' : 'apartments' }}
                                                    available
                                                </span>
                                                <div class="btn_wrap">
                                                    <a
                                                        href="#"
                                                        class="btn view_btn"
                                                        @click.prevent="selectedDistrict = district.district_id"
                                                    >
                                                        Show apartments
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="apartments_list">
                                    <div
                                        v-for="apt in visibleApartments"
                                        :key="apt.id"
                                        class="city_app_block_1"
                                    >
                                        <router-link
                                            :to="{ name: 'public-apartment', params: { id: apt.id } }"
                                            class="city_app_block_1_inner"
                                        >
                                            <div class="img" :class="{ 'district-greenbox': !apt.image }">
                                                <img v-if="apt.image" :src="apt.image" :alt="apt.name" />
                                                <img
                                                    v-else
                                                    :src="DISTRICT_GREENBOX_LOGO"
                                                    alt=""
                                                    class="district-greenbox__logo"
                                                />
                                            </div>
                                            <div class="cap">
                                                <div class="price">{{ formatVnd(apt.price_daily) }}</div>
                                                <div class="name">{{ apt.name }}</div>
                                                <div class="address">{{ apt.district }}</div>
                                            </div>
                                        </router-link>
                                    </div>
                                    <p v-if="!visibleApartments.length" class="host-apartments-empty">No apartments found.</p>
                                </div>
                            </div>
                            <div class="map_wrap">
                                <div class="map_placeholder city_map" />
                            </div>
                            <div style="clear: both" />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div id="section2">
            <div class="container">
                <div class="row">
                    <div class="col-md-7 sec2_left">
                        <h3 class="sec2_title">{{ SECTION_EXPLORE.title }}</h3>
                        <div class="sec2_subtitle">{{ SECTION_EXPLORE.subtitle }}</div>
                        <div class="sec2_list">
                            <h4>{{ SECTION_EXPLORE.listTitle }}</h4>
                            <ul>
                                <li v-for="item in SECTION_EXPLORE.listItems" :key="item">{{ item }}</li>
                            </ul>
                        </div>
                        <hr />
                        <div class="sec2_list">
                            <h4>{{ SECTION_EXPLORE.listTitle }}</h4>
                            <ul>
                                <li v-for="item in SECTION_EXPLORE.listItems" :key="`b-${item}`">{{ item }}</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-5 sec2_right">
                        <div class="contact_form">
                            <h3>Do you need help for your search?</h3>
                            <form @submit.prevent="submitHelp">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <input v-model="helpForm.firstName" type="text" placeholder="First name" />
                                    </div>
                                    <div class="col-sm-6">
                                        <input v-model="helpForm.lastName" type="text" placeholder="Last name" />
                                    </div>
                                </div>
                                <input v-model="helpForm.email" type="email" placeholder="Email" />
                                <input v-model="helpForm.phone" type="text" placeholder="Phone" />
                                <textarea v-model="helpForm.message" rows="5" placeholder="Message" />
                                <button type="submit">Send</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="section3">
            <div class="container">
                <h2>{{ SECTION_HCM.title }}</h2>
                <div class="subtitle">{{ SECTION_HCM.subtitle }}</div>
                <div class="text-center mb-4">
                    <a :href="SECTION_HCM.buttonLink" class="btn">{{ SECTION_HCM.buttonText }}</a>
                </div>
                <div class="images">
                    <img :src="SECTION_HCM.leftImage" class="left_image" alt="" />
                    <img :src="SECTION_HCM.centerImage" class="center_image" alt="" />
                    <img :src="SECTION_HCM.rightImage" class="right_image" alt="" />
                </div>
            </div>
        </div>

        <div id="other_city" class="pb-4">
            <div class="container pb-4">
                <h2>Other Cities</h2>
                <div class="row">
                    <div v-for="city in OTHER_CITIES" :key="city.name" class="col-sm-4 mb-4">
                        <router-link
                            :to="cityLink(city)"
                            class="other_city_block"
                            :style="{ backgroundImage: `url(${city.image})` }"
                        >
                            <div class="cap">
                                <h3>{{ city.name }}</h3>
                            </div>
                            <span class="btn">Show available apartments</span>
                        </router-link>
                    </div>
                </div>
            </div>
        </div>

        <div id="footer">
            <div class="skyline_img_2">
                <img :src="HOME_IMAGES.skyline" alt="" />
            </div>
            <div class="footer">
                <div class="container">
                    <div class="f_inn">
                        <p>© Copyright VisitVietnam 2023 - All rights reserved.</p>
                        <div class="social">
                            <a href="#" target="_blank" rel="noopener"><img :src="HOME_IMAGES.fb" alt="" /></a>
                            <a href="#" target="_blank" rel="noopener"><img :src="HOME_IMAGES.insta" alt="" /></a>
                        </div>
                        <ul class="menu">
                            <li><a href="#">Property Management</a></li>
                            <li><a href="#">Support</a></li>
                            <li><a href="#">Legal &amp; Privacy</a></li>
                            <li><a href="#">Cookies</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import apiClient from '@/api/client';
import { usePublicLegacyStyles } from '@/composables/usePublicLegacyStyles';
import {
    HOME_IMAGES,
    OTHER_CITIES,
    DISTRICT_GREENBOX_LOGO,
    SEARCH_FACILITIES,
    SECTION_EXPLORE,
    SECTION_HCM,
} from '@/data/apartments-content';
import { formatVnd } from '@/utils/format';

const listIcon = String('/home/images/list2.svg');
const listActiveIcon = String('/home/images/list_h.svg');
const mapIcon = String('/home/images/map.svg');
const mapActiveIcon = String('/home/images/map_h.svg');

const route = useRoute();
const router = useRouter();
const { mount, unmount } = usePublicLegacyStyles('apartments');

const loading = ref(true);
const logoFailed = ref(false);
const headerFixed = ref(false);
const mobileNavOpen = ref(false);
const guestsOpen = ref(false);
const viewMode = ref('list');
const selectedDistrict = ref(null);

const districts = ref([]);
const apartments = ref([]);
const cities = ref([]);
const activeFacilities = ref([]);

const filters = reactive({
    city: '',
    district: '',
    rooms: 2,
    adults: 2,
    children: 0,
    price: '',
    sort: '',
});

const helpForm = reactive({
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    message: '',
});

const roomsGuestsLabel = computed(
    () => `${filters.rooms} Rooms / ${filters.adults + filters.children} Guests`,
);

const pageTitle = computed(() => {
    if (filters.city) {
        const city = cities.value.find((c) => String(c.city_id) === String(filters.city));
        if (city) return `All apartments from ${city.name}`;
    }
    return 'All Apartments';
});

const districtsHeading = computed(() => {
    if (filters.city) {
        const city = cities.value.find((c) => String(c.city_id) === String(filters.city));
        if (city) return `Now displaying ${districts.value.length} districts from ${city.name}`;
    }
    return 'All Districts';
});

const apartmentsHeading = computed(() => {
    const count = visibleApartments.value.length;
    const district = districts.value.find((d) => d.district_id === selectedDistrict.value);
    const suffix = district ? ` from ${district.city_name}, ${district.name}` : '';
    if (count === 1) return `Now displaying 1 apartment${suffix}`;
    return `Now displaying ${count} apartments${suffix}`;
});

const visibleApartments = computed(() => {
    let list = apartments.value;
    if (selectedDistrict.value) {
        list = list.filter((apt) => apt.district_id === selectedDistrict.value);
    }
    if (filters.price === 'low') {
        list = [...list].sort((a, b) => a.price_daily - b.price_daily);
    } else if (filters.price === 'high') {
        list = [...list].sort((a, b) => b.price_daily - a.price_daily);
    } else if (filters.sort === 'newest') {
        list = [...list];
    }
    return list;
});

function syncFromRoute() {
    filters.city = route.query.city ? String(route.query.city) : '';
    filters.district = route.query.district ? String(route.query.district) : '';
    filters.rooms = route.query.rooms ? Number(route.query.rooms) : 2;
    filters.adults = route.query.adults ? Number(route.query.adults) : 2;
    filters.children = route.query.children ? Number(route.query.children) : 0;
    if (filters.district) {
        selectedDistrict.value = Number(filters.district);
    }
}

function buildQuery() {
    const query = {};
    if (filters.city) query.city = filters.city;
    if (filters.district) query.district = filters.district;
    if (filters.rooms) query.rooms = String(filters.rooms);
    if (filters.adults) query.adults = String(filters.adults);
    if (filters.children) query.children = String(filters.children);
    return query;
}

function buildSearchParams() {
    const params = new URLSearchParams();
    if (filters.city) params.set('city', filters.city);
    if (filters.district) params.set('district', filters.district);

    for (const slug of activeFacilities.value) {
        const item = SEARCH_FACILITIES.find((entry) => entry.id === slug);
        if (!item) continue;
        if (item.facilityId) {
            params.append('facility[]', String(item.facilityId));
        } else if (item.filter) {
            params.set(item.filter, '1');
        }
    }

    return params;
}

async function loadSearch() {
    loading.value = true;
    try {
        const params = buildSearchParams();
        const res = await apiClient.get(`/public/apartments/search?${params.toString()}`);
        districts.value = res?.data?.districts ?? [];
        apartments.value = res?.data?.apartments ?? [];
        cities.value = res?.data?.cities ?? [];
    } catch {
        districts.value = [];
        apartments.value = [];
        cities.value = [];
    } finally {
        loading.value = false;
    }
}

function applyFilters() {
    guestsOpen.value = false;
    router.replace({ name: 'public-apartments', query: buildQuery() });
    loadSearch();
}

function clearFilters() {
    filters.city = '';
    filters.district = '';
    filters.rooms = 2;
    filters.adults = 2;
    filters.children = 0;
    filters.price = '';
    filters.sort = '';
    activeFacilities.value = [];
    selectedDistrict.value = null;
    router.replace({ name: 'public-apartments' });
    loadSearch();
}

function toggleFacility(id) {
    if (activeFacilities.value.includes(id)) {
        activeFacilities.value = activeFacilities.value.filter((f) => f !== id);
    } else {
        activeFacilities.value.push(id);
    }
    loadSearch();
}

function adjustCount(field, delta) {
    const min = field === 'children' ? 0 : 1;
    filters[field] = Math.max(min, Number(filters[field] || 0) + delta);
}

function cityLink(city) {
    if (city.districtId) {
        return { name: 'public-apartments', query: { district: String(city.districtId) } };
    }
    return { name: 'public-apartments' };
}

function submitHelp() {
    helpForm.firstName = '';
    helpForm.lastName = '';
    helpForm.email = '';
    helpForm.phone = '';
    helpForm.message = '';
}

function onScroll() {
    headerFixed.value = window.scrollY > 80;
}

function onDocumentClick(event) {
    if (!event.target.closest('.dropdown_wrap')) {
        guestsOpen.value = false;
    }
}

onMounted(() => {
    mount();
    syncFromRoute();
    loadSearch();
    window.addEventListener('scroll', onScroll, { passive: true });
    document.addEventListener('click', onDocumentClick);
    onScroll();
});

onUnmounted(() => {
    unmount();
    window.removeEventListener('scroll', onScroll);
    document.removeEventListener('click', onDocumentClick);
});

watch(
    () => route.query,
    () => {
        syncFromRoute();
        loadSearch();
    },
);
</script>

<style scoped>
.host-home-menu-btn {
    display: none;
}

.host-apartments-loading,
.host-apartments-empty {
    padding: 40px 0;
    color: #013735;
}

@media (max-width: 991px) {
    .host-home-menu-btn {
        display: block;
    }

    .head_right.host-home-nav-open {
        display: flex;
    }
}
</style>
