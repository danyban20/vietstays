<template>
    <div class="host-home">
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
                                <li><a href="#" @click.prevent>Districts</a></li>
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

        <div id="home_slider">
            <div class="cap">
                <form id="formBooking" class="host-home-search" @submit.prevent="submitSearch">
                    <div class="book_block">
                        <div
                            class="input_wrap choose_city_sel"
                            :class="{ open: openDropdown === 'city' }"
                        >
                            <label>Where</label>
                            <a href="#" class="room_btn city_label" @click.prevent="toggleDropdown('city')">
                                {{ cityLabel }}
                            </a>
                            <div class="city_dropdown book_dropdown">
                                <ul>
                                    <li v-for="city in cities" :key="city.district_id">
                                        <a href="#" @click.prevent="selectCity(city)">
                                            <strong>{{ city.name }}</strong>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="input_wrap room_sel" :class="{ open: openDropdown === 'guests' }">
                            <label>Rooms &amp; Guests</label>
                            <a href="#" class="room_btn rooms_guests_label" @click.prevent="toggleDropdown('guests')">
                                {{ roomsGuestsLabel }}
                            </a>
                            <div class="rooom_dropdown book_dropdown">
                                <ul>
                                    <li>
                                        <span class="lbltxt">Rooms<i>Choose amount of rooms</i></span>
                                        <div class="number">
                                            <span class="minus" @click.prevent="adjustCount('rooms', -1)" />
                                            <input
                                                v-model.number="search.rooms"
                                                type="text"
                                                name="rooms"
                                                data-min="1"
                                                readonly
                                            />
                                            <span class="plus" @click.prevent="adjustCount('rooms', 1)" />
                                        </div>
                                    </li>
                                    <li>
                                        <span class="lbltxt">Adults<i>13 years old or older</i></span>
                                        <div class="number">
                                            <span class="minus" @click.prevent="adjustCount('adults', -1)" />
                                            <input
                                                v-model.number="search.adults"
                                                type="text"
                                                name="adults"
                                                data-min="1"
                                                readonly
                                            />
                                            <span class="plus" @click.prevent="adjustCount('adults', 1)" />
                                        </div>
                                    </li>
                                    <li>
                                        <span class="lbltxt">Children<i>below 13 years old</i></span>
                                        <div class="number">
                                            <span class="minus" @click.prevent="adjustCount('children', -1)" />
                                            <input v-model.number="search.children" type="text" name="children" readonly />
                                            <span class="plus" @click.prevent="adjustCount('children', 1)" />
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="input_wrap date_sel">
                            <label>Check in &amp; out</label>
                            <input
                                ref="dateInputRef"
                                type="text"
                                name="datefilter"
                                placeholder="Select dates"
                                readonly
                            />
                        </div>
                        <div class="input_submit">
                            <input type="submit" value="Search" />
                        </div>
                    </div>
                </form>
            </div>

            <Swiper
                class="mySwiper hero-swiper"
                :modules="heroModules"
                :loop="true"
                :autoplay="{ delay: 6000, disableOnInteraction: false }"
                :pagination="{ clickable: true }"
            >
                <SwiperSlide v-for="(slide, index) in HERO_SLIDES" :key="index">
                    <div class="slider_cap">
                        <h1>{{ slide.title }}</h1>
                        <p v-html="slide.text.replace(/\n/g, '<br>')" />
                    </div>
                    <div class="slider_img" :style="{ backgroundImage: `url(${slide.image})` }" />
                </SwiperSlide>
            </Swiper>
        </div>

        <div id="explore">
            <div class="container">
                <div class="top_title">
                    <h2 class="heading-1">Explore our Exclusive Stays</h2>
                    <div class="prev_next">
                        <div ref="explorePrev" class="swiper-button-prev" />
                        <div ref="exploreNext" class="swiper-button-next" />
                    </div>
                </div>
                <Swiper
                    class="explore_slider"
                    :modules="exploreModules"
                    :slides-per-view="1.15"
                    :space-between="38"
                    :navigation="{ prevEl: explorePrev, nextEl: exploreNext }"
                    :breakpoints="{
                        768: { slidesPerView: 2.2 },
                        1100: { slidesPerView: 3.2 },
                        1400: { slidesPerView: 4 },
                    }"
                >
                    <SwiperSlide v-for="city in EXPLORE_CITIES" :key="city.name">
                        <router-link
                            :to="cityRoute(city)"
                            class="explore_block"
                            :style="{ backgroundImage: `url(${city.image})` }"
                        >
                            <div class="cap">
                                <h4 v-if="city.subtitle">{{ city.subtitle }}</h4>
                                <h3>{{ city.name }}</h3>
                            </div>
                            <span class="btn">Show available apartments</span>
                        </router-link>
                    </SwiperSlide>
                </Swiper>
            </div>
        </div>

        <div id="free_membership">
            <div class="shield_img"><img :src="HOME_IMAGES.shield" alt="" /></div>
            <div class="container">
                <h2 class="heading-1">Free Membership and Rewards</h2>
                <ul>
                    <li v-for="item in MEMBERSHIP_BENEFITS" :key="item">{{ item }}</li>
                </ul>
                <a href="#" class="btn" @click.prevent>Register for free now</a>
                <hr />
            </div>
        </div>

        <div id="campaigns">
            <div class="container">
                <h3>Recent campaigns and offers we have sent out to our members</h3>
                <div class="offer_list">
                    <div class="row">
                        <div v-for="(offer, index) in CAMPAIGN_OFFERS" :key="index" class="col-sm-4">
                            <a
                                href="#"
                                class="offer_block"
                                :class="{ disable: offer.disabled }"
                                :style="{ backgroundImage: `url(${offer.image})` }"
                                @click.prevent
                            >
                                <span v-if="offer.percent" class="per">{{ offer.percent }}</span>
                                <div class="cap">
                                    <div v-if="offer.oldPrice" class="old_price">{{ offer.oldPrice }}</div>
                                    <div class="new_price">{{ offer.newPrice }}</div>
                                    <p>{{ offer.title }}</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <a href="#" class="seel_all_btn btn" @click.prevent>
                Don’t miss it - See all here<img :src="HOME_IMAGES.tag" alt="" />
            </a>
        </div>

        <div id="we_make">
            <div class="container">
                <h2 class="heading-1">We make it easy for you <br />And your family</h2>
                <div class="row v_flex">
                    <div class="col-md-6">
                        <div class="we_make_left">
                            <div v-for="item in SERVICE_ITEMS" :key="item.title" class="title_box">
                                <div class="icon"><img :src="item.icon" alt="" /></div>
                                <a href="#" @click.prevent>
                                    <h3>{{ item.title }}</h3>
                                    <span class="title_box_link"><img :src="HOME_IMAGES.next" alt="" /></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="we_make_slider">
                            <Swiper
                                class="mySwiper2"
                                :modules="weMakeModules"
                                :pagination="{ clickable: true }"
                            >
                                <SwiperSlide>
                                    <div class="slider_img">
                                        <img :src="HOME_IMAGES.sliderSide" alt="" />
                                    </div>
                                </SwiperSlide>
                                <SwiperSlide>
                                    <div class="slider_img">
                                        <img :src="HOME_IMAGES.sliderSide" alt="" />
                                    </div>
                                </SwiperSlide>
                                <SwiperSlide>
                                    <div class="video_block">
                                        <img :src="HOME_IMAGES.sliderSide" alt="" />
                                        <a
                                            href="https://youtu.be/ePYwFrUbEIE"
                                            class="play_btn"
                                            target="_blank"
                                            rel="noopener"
                                        />
                                    </div>
                                </SwiperSlide>
                            </Swiper>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="benifits">
            <div class="container">
                <h2 class="heading-1">The Benefits of an Exclusive Stay</h2>
                <div class="row v_flex">
                    <div class="col-md-6">
                        <div class="video_block">
                            <img :src="HOME_IMAGES.videoThumb" alt="" />
                            <a
                                href="https://youtu.be/ePYwFrUbEIE"
                                class="play_btn"
                                target="_blank"
                                rel="noopener"
                            />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="desc">
                            <ul>
                                <li v-for="item in STAY_BENEFITS" :key="item.text">
                                    <span class="icon"><img :src="item.icon" alt="" /></span>
                                    {{ item.text }}
                                </li>
                            </ul>
                            <router-link :to="{ name: 'public-apartments' }" class="btn yellow_btn">
                                Book apartment now
                            </router-link>
                        </div>
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
import { computed, nextTick, onMounted, onUnmounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { Autoplay, Navigation, Pagination } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/vue';
import apiClient from '@/api/client';
import { useHomeLegacyStyles } from '@/composables/useHomeLegacyStyles';
import { useHomeSearchBar } from '@/composables/useHomeSearchBar';
import {
    CAMPAIGN_OFFERS,
    DEFAULT_CITIES,
    EXPLORE_CITIES,
    HERO_SLIDES,
    HOME_IMAGES,
    MEMBERSHIP_BENEFITS,
    SERVICE_ITEMS,
    STAY_BENEFITS,
} from '@/data/home-content';

const router = useRouter();
const { mount, unmount } = useHomeLegacyStyles();
const { initDateRangePicker, hideDatePicker, destroyDateRangePicker } = useHomeSearchBar();

const dateInputRef = ref(null);

const heroModules = [Autoplay, Pagination];
const exploreModules = [Navigation];
const weMakeModules = [Pagination];

const explorePrev = ref(null);
const exploreNext = ref(null);

const headerFixed = ref(false);
const mobileNavOpen = ref(false);
const logoFailed = ref(false);
const openDropdown = ref(null);
const cities = ref([...DEFAULT_CITIES]);

const search = reactive({
    cityId: '',
    cityName: '',
    rooms: 2,
    adults: 2,
    children: 0,
    checkIn: '',
    checkOut: '',
});

const cityLabel = computed(() => search.cityName || 'Choose city');

const roomsGuestsLabel = computed(
    () => `${search.rooms} rooms & ${search.adults + search.children} guests`,
);

function closeDropdowns() {
    openDropdown.value = null;
    document.body.classList.remove('book_overlay_open');
}

function toggleDropdown(name) {
    hideDatePicker();

    if (openDropdown.value === name) {
        closeDropdowns();
        return;
    }

    openDropdown.value = name;
    document.body.classList.add('book_overlay_open');
}

function onDatePickerShow() {
    openDropdown.value = null;
}

function selectCity(city) {
    search.cityId = String(city.district_id);
    search.cityName = city.name;
    closeDropdowns();
}

function adjustCount(field, delta) {
    const min = field === 'children' ? 0 : 1;
    search[field] = Math.max(min, Number(search[field] || 0) + delta);
}

function cityRoute(city) {
    if (city.districtId) {
        return { name: 'public-apartments', query: { district: city.districtId } };
    }

    return { name: 'public-apartments' };
}

function submitSearch() {
    const query = {};

    if (search.cityId) query.district = search.cityId;
    if (search.rooms) query.rooms = String(search.rooms);
    if (search.adults) query.adults = String(search.adults);
    if (search.children) query.children = String(search.children);
    if (search.checkIn) query.from = search.checkIn;
    if (search.checkOut) query.to = search.checkOut;

    router.push({ name: 'public-apartments', query });
}

function onScroll() {
    headerFixed.value = window.scrollY > 80;
}

function onDocumentClick(event) {
    if (!event.target.closest('.book_block')) {
        closeDropdowns();
    }
}

function onDatesApplied(checkIn, checkOut) {
    search.checkIn = checkIn;
    search.checkOut = checkOut;
}

function onDatesClear() {
    search.checkIn = '';
    search.checkOut = '';
}

async function loadDistricts() {
    try {
        const res = await apiClient.get('/public/districts');
        if (Array.isArray(res?.data) && res.data.length) {
            cities.value = res.data;
        }
    } catch {
        cities.value = [...DEFAULT_CITIES];
    }
}

onMounted(async () => {
    mount();
    loadDistricts();
    window.addEventListener('scroll', onScroll, { passive: true });
    document.addEventListener('click', onDocumentClick);
    onScroll();

    await nextTick();
    await initDateRangePicker(dateInputRef.value, {
        onApply: onDatesApplied,
        onClear: onDatesClear,
        onShow: onDatePickerShow,
    });
});

onUnmounted(() => {
    destroyDateRangePicker();
    unmount();
    window.removeEventListener('scroll', onScroll);
    document.removeEventListener('click', onDocumentClick);
    document.body.classList.remove('book_overlay_open');
});
</script>

<style scoped>
.host-home-menu-btn {
    display: none;
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
