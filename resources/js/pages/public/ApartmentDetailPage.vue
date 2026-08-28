<template>
    <div class="host-apartment-detail">
        <div id="topbar" :class="{ 'fixed-topbar': topbarFixed }">
            <div class="container">
                <div class="topbar">
                    <div class="topbar_left">
                        <router-link :to="{ name: 'home' }">
                            <img v-if="!logoFailed" :src="HOME_IMAGES.logo" alt="Visit Vietnam" @error="logoFailed = true" />
                            <span v-else class="host-home-logo-fallback">Visit Vietnam</span>
                        </router-link>
                    </div>
                    <div class="topbar_right">
                        <ul>
                            <li><a href="#menucontent_1" @click.prevent="scrollTo('menucontent_1')">Pictures</a></li>
                            <li><a href="#menucontent_2" @click.prevent="scrollTo('menucontent_2')">Facilities</a></li>
                            <li><a href="#menucontent_3" @click.prevent="scrollTo('menucontent_3')">Practical information</a></li>
                            <li><a href="#menucontent_4" @click.prevent="scrollTo('menucontent_4')">Host</a></li>
                            <li>
                                <a href="#book-now" @click.prevent="scrollTo('book-now')">
                                    Price {{ formatVnd(apartment.price_daily || 0) }}
                                </a>
                            </li>
                        </ul>
                        <a href="#book-now" class="btn" @click.prevent="scrollTo('book-now')">Book now</a>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="loading" class="host-apartment-loading">Loading apartment…</div>

        <template v-else-if="apartment.id">
            <div class="single_app_top" id="menucontent_1">
                <div class="container">
                    <div class="app_title">
                        <div>
                            <h1 class="heading-2">{{ apartment.name }}</h1>
                            <h5>
                                <img :src="DETAIL_ICONS.pin" alt="" />
                                {{ apartment.address || apartment.district || 'Vietnam' }}
                            </h5>
                        </div>
                        <a href="#" class="share_link" @click.prevent="shareApartment">
                            <img :src="DETAIL_ICONS.share" alt="" />
                            Share link
                        </a>
                    </div>

                    <div class="app_gall">
                        <div class="app_gall_left">
                            <div class="gall_img" :class="{ 'district-greenbox': !mainImage }">
                                <a
                                    v-if="mainImage"
                                    href="#"
                                    class="gall_img_inn"
                                    @click.prevent="galleryOpen = true"
                                >
                                    <img :src="mainImage" :alt="apartment.name" />
                                </a>
                                <img
                                    v-else
                                    :src="DISTRICT_GREENBOX_LOGO"
                                    alt=""
                                    class="district-greenbox__logo"
                                />
                            </div>
                        </div>
                        <div class="app_gall_right">
                            <div class="app_gall_right_inn">
                                <div class="gall_col_wrap">
                                    <div v-for="col in 3" :key="col" class="gall_col">
                                        <div
                                            v-for="(image, rowIndex) in thumbColumns[col - 1]"
                                            :key="`${col}-${rowIndex}`"
                                            class="gall_img"
                                            :class="{ 'district-greenbox': !image }"
                                        >
                                            <a
                                                v-if="image"
                                                href="#"
                                                class="gall_img_inn"
                                                @click.prevent="galleryOpen = true"
                                            >
                                                <img :src="image.thumb || image.full" alt="" />
                                            </a>
                                            <img
                                                v-else
                                                :src="DISTRICT_GREENBOX_LOGO"
                                                alt=""
                                                class="district-greenbox__logo"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <a
                                    v-if="galleryImages.length"
                                    href="#"
                                    class="show_all"
                                    @click.prevent="galleryOpen = true"
                                >
                                    Show all images ({{ galleryImages.length }})
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="app_feature">
                        <div class="row">
                            <div class="col-sm-12">
                                <div v-if="apartment.description" class="app_feature__desc" v-html="apartment.description" />
                                <ul>
                                    <li>
                                        <div class="icon"><img :src="DETAIL_ICONS.rooms" alt="" /></div>
                                        <h4>{{ apartment.rooms || 0 }} rooms</h4>
                                    </li>
                                    <li>
                                        <div class="icon"><img :src="DETAIL_ICONS.beds" alt="" /></div>
                                        <h4>{{ apartment.num_beds || 0 }} beds</h4>
                                    </li>
                                    <li>
                                        <div class="icon"><img :src="DETAIL_ICONS.guests" alt="" /></div>
                                        <h4>{{ apartment.max_guests || 0 }} guests</h4>
                                    </li>
                                    <li>
                                        <div class="icon"><img :src="DETAIL_ICONS.baths" alt="" /></div>
                                        <h4>{{ apartment.num_bathrooms || 0 }} baths</h4>
                                    </li>
                                    <li>
                                        <div class="icon"><img :src="DETAIL_ICONS.size" alt="" /></div>
                                        <h4>{{ apartment.area_sqm || 0 }} m<sup>2</sup></h4>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app_content" id="app_content">
                <div class="container">
                    <div class="app_content_inn">
                        <div class="app_leftbar" id="app_leftbar">
                            <div v-if="apartment.facilities?.length" class="app_facility" id="menucontent_2">
                                <h3>Facilities</h3>
                                <ul>
                                    <li v-for="facility in visibleFacilities" :key="facility.id">
                                        <span class="icon">
                                            <img :src="facilityIcon(facility.name)" alt="" />
                                        </span>
                                        {{ facility.name }}
                                    </li>
                                </ul>
                                <a
                                    v-if="apartment.facilities.length > 8"
                                    href="#"
                                    class="btn border_btn"
                                    @click.prevent="facilitiesOpen = true"
                                >
                                    Show all facilities ({{ apartment.facilities.length }})
                                </a>
                                <hr />
                            </div>

                            <div v-if="apartment.about_this_short" class="app_about">
                                {{ apartment.about_this_short }}
                            </div>
                            <hr v-if="apartment.about_this_short" />

                            <div v-if="activePracticalBlocks.length" class="app_per_info" id="menucontent_3">
                                <h3>Practical information</h3>
                                <div class="app_per_info_inn">
                                    <div v-for="block in activePracticalBlocks" :key="block.key" class="block">
                                        <span class="icon"><img :src="block.icon" alt="" /></span>
                                        <h4>{{ block.title }}</h4>
                                        <p>{{ block.text }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="app_per_info_bot">
                                <div class="block">
                                    <h4>House Rules</h4>
                                    <p>– {{ checkInRule }}</p>
                                    <p>– Check-out before {{ formatClock(apartment.check_out_time) }}</p>
                                    <p>– Maximum {{ apartment.max_guests || 0 }} guests</p>
                                </div>
                                <div v-if="apartment.security_features?.length" class="block">
                                    <h4>Security and property</h4>
                                    <p v-for="feature in apartment.security_features" :key="feature.id">
                                        - {{ feature.name }}
                                    </p>
                                </div>
                            </div>

                            <a
                                v-if="apartment.about_this"
                                href="#"
                                class="btn border_btn"
                                @click.prevent="aboutOpen = true"
                            >
                                Read more
                            </a>
                            <hr v-if="apartment.about_this" />

                            <div class="app_here_app pt-4">
                                <h3>
                                    Here is the apartment
                                    <a
                                        v-if="mapsUrl"
                                        :href="mapsUrl"
                                        class="map_open"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        Open in Google Maps
                                    </a>
                                </h3>
                                <div class="map_img">
                                    <div class="map_placeholder" />
                                </div>
                                <div class="app_here_app_inn" id="menucontent_4">
                                    <h3>This is your host</h3>
                                    <div class="host_info">
                                        <div class="user_img">
                                            <img :src="DETAIL_ICONS.user" alt="" />
                                        </div>
                                        <div class="user_desc">
                                            <h4>{{ apartment.host?.name || 'Visit Vietnam Host' }}</h4>
                                            <h5>Your host</h5>
                                        </div>
                                        <div class="btns">
                                            <a href="#" class="btn cont_btn" @click.prevent>Host</a>
                                            <a href="#" class="btn" @click.prevent>View profile</a>
                                        </div>
                                    </div>
                                    <h3>Reviews</h3>
                                    <div class="nano">
                                        <div class="content">
                                            <div
                                                v-for="(review, index) in SAMPLE_REVIEWS"
                                                :key="index"
                                                class="app_rev_block"
                                            >
                                                <h4>{{ review.title }}</h4>
                                                <p>{{ review.body }}</p>
                                                <p class="name">– {{ review.author }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="app_rightbar">
                            <div id="app_rightbar_inn">
                                <a id="book-now" name="book-now" />
                                <div class="app_book_block" id="app_book_block">
                                    <form @submit.prevent="submitBooking">
                                        <h2>Book now</h2>
                                        <h5>Add dates and check availability &amp; prices</h5>
                                        <hr />
                                        <div class="check_in_out_date">
                                            <div class="input_wrap date_sel">
                                                <label>Check in</label>
                                                <input
                                                    ref="checkInInput"
                                                    type="text"
                                                    name="datefilter"
                                                    placeholder="Select dates"
                                                    class="start_date"
                                                    :value="checkInDisplay"
                                                    readonly
                                                />
                                            </div>
                                            <div class="input_wrap date_sel">
                                                <label>Checkout</label>
                                                <input
                                                    ref="checkOutInput"
                                                    type="text"
                                                    name="datefilter"
                                                    placeholder="Select dates"
                                                    class="end_date"
                                                    :value="checkOutDisplay"
                                                    readonly
                                                />
                                            </div>
                                        </div>
                                        <div class="guest_opt_wrap">
                                            <div class="guest_opt d-block pr-3" :class="{ open: guestsOpen }">
                                                <a href="#" class="room_btn pr-3" @click.prevent="guestsOpen = !guestsOpen">
                                                    <div class="guest_opt_left float-left">
                                                        <span style="font-size: 14px">{{ guestsLabel }}</span>
                                                    </div>
                                                    <div class="room_sel float-right w-auto pl-0">
                                                        <span>Guests</span>
                                                    </div>
                                                    <div style="clear: both" />
                                                </a>
                                                <div class="rooom_dropdown pt-4 pb-4">
                                                    <ul>
                                                        <li>
                                                            <span class="lbltxt">
                                                                Adults
                                                                <i>13 years old or older</i>
                                                            </span>
                                                            <div class="number">
                                                                <span class="minus" @click.prevent="adjustGuests('adults', -1)" />
                                                                <input v-model.number="booking.adults" type="text" readonly />
                                                                <span class="plus" @click.prevent="adjustGuests('adults', 1)" />
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <span class="lbltxt">
                                                                Children
                                                                <i>below 13 years old</i>
                                                            </span>
                                                            <div class="number">
                                                                <span class="minus" @click.prevent="adjustGuests('children', -1)" />
                                                                <input v-model.number="booking.children" type="text" readonly />
                                                                <span class="plus" @click.prevent="adjustGuests('children', 1)" />
                                                            </div>
                                                        </li>
                                                    </ul>
                                                    <div class="text-center mt-2">
                                                        <a href="#" class="btn" @click.prevent="guestsOpen = false">✓</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="book_now_btn">Book now</button>
                                        <div class="app_price">
                                            <h2>Your price:</h2>
                                            <ul>
                                                <li v-for="(line, index) in priceLines" :key="index">
                                                    <span class="lbltxt">{{ line.label }}</span>
                                                    <span class="valtxt">
                                                        <i v-if="line.strike">{{ line.strike }}</i>
                                                        {{ line.value }}
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </form>
                                </div>
                                <div class="checkout_late_block">
                                    <h2>Check out late</h2>
                                    <h5>This apartment is available for late checkout</h5>
                                    <hr />
                                    <p>
                                        Did you know that you can check out late without charge when you become gold
                                        member on our site?
                                    </p>
                                    <a href="#" class="btn" @click.prevent>Read more about our gold membership</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div v-else class="host-apartment-empty">
            <p>This apartment is not available.</p>
            <router-link :to="{ name: 'public-apartments' }">Back to apartments</router-link>
        </div>

        <div v-if="galleryOpen" class="gallery-modal" @click.self="galleryOpen = false">
            <div class="gallery-modal__inner">
                <div class="gallery-modal__head">
                    <h2>Show all images ({{ galleryImages.length }})</h2>
                    <button type="button" class="gallery-modal__close" @click="galleryOpen = false">Close</button>
                </div>
                <div class="gallery-modal__grid">
                    <img
                        v-for="(image, index) in galleryImages"
                        :key="index"
                        :src="image.full || image.thumb"
                        :alt="image.caption || apartment.name"
                    />
                </div>
            </div>
        </div>

        <div v-if="facilitiesOpen" class="facilities-modal" @click.self="facilitiesOpen = false">
            <div class="facilities-modal__panel">
                <h2>All facilities ({{ apartment.facilities?.length || 0 }})</h2>
                <ul>
                    <li v-for="facility in apartment.facilities || []" :key="facility.id">
                        <span class="icon">
                            <img :src="facilityIcon(facility.name)" alt="" />
                        </span>
                        {{ facility.name }}
                    </li>
                </ul>
            </div>
        </div>

        <div v-if="aboutOpen" class="facilities-modal" @click.self="aboutOpen = false">
            <div class="facilities-modal__panel">
                <div class="popup_scroll" v-html="apartment.about_this" />
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
import { computed, nextTick, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import apiClient from '@/api/client';
import { useApartmentBookingDates } from '@/composables/useApartmentBookingDates';
import { usePublicLegacyStyles } from '@/composables/usePublicLegacyStyles';
import {
    DETAIL_ICONS,
    HOME_IMAGES,
    PRACTICAL_BLOCKS,
    SAMPLE_REVIEWS,
    facilityIcon,
} from '@/data/apartment-detail-content';
import { DISTRICT_GREENBOX_LOGO } from '@/data/apartments-content';
import { formatVnd, nightsBetween } from '@/utils/format';

const route = useRoute();
const router = useRouter();
const { mount, unmount } = usePublicLegacyStyles('apartment-detail');
const { initSingleDatePicker, destroy: destroyDatePickers } = useApartmentBookingDates();

const loading = ref(true);
const logoFailed = ref(false);
const topbarFixed = ref(false);
const galleryOpen = ref(false);
const facilitiesOpen = ref(false);
const aboutOpen = ref(false);
const guestsOpen = ref(false);

const checkInInput = ref(null);
const checkOutInput = ref(null);

const apartment = ref({});
const booking = reactive({
    checkIn: '',
    checkOut: '',
    adults: 2,
    children: 0,
});

const galleryImages = computed(() => {
    const images = apartment.value.images ?? [];
    return images.filter((image) => image.thumb || image.full);
});

const mainImage = computed(() => {
    const first = galleryImages.value[0];
    return first?.full || first?.thumb || apartment.value.image || null;
});

const thumbSlots = computed(() => {
    const slots = galleryImages.value.slice(1, 7);
    while (slots.length < 6) {
        slots.push(null);
    }
    return slots;
});

const thumbColumns = computed(() => [
    thumbSlots.value.slice(0, 2),
    thumbSlots.value.slice(2, 4),
    thumbSlots.value.slice(4, 6),
]);

const visibleFacilities = computed(() => (apartment.value.facilities ?? []).slice(0, 8));

const activePracticalBlocks = computed(() =>
    PRACTICAL_BLOCKS.filter((block) => apartment.value[block.key]),
);

const mapsUrl = computed(() => {
    const lat = apartment.value.latitude;
    const lng = apartment.value.longitude;
    if (!lat || !lng) {
        return null;
    }
    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(`${lat},${lng}`)}`;
});

const checkInRule = computed(() => {
    const from = formatClock(apartment.value.check_in_time1);
    const to = formatClock(apartment.value.check_in_time2);
    if (from && to && from !== to) {
        return `Check-in is ${from} to ${to}`;
    }
    if (from) {
        return `Check-in is ${from}`;
    }
    return 'Check-in times vary';
});

const guestsLabel = computed(() => {
    const parts = [];
    if (booking.adults === 1) parts.push('1 adult');
    else parts.push(`${booking.adults} adults`);
    if (booking.children === 1) parts.push('1 child');
    else if (booking.children > 1) parts.push(`${booking.children} children`);
    return parts.join(' & ');
});

const checkInDisplay = computed(() => formatUsDate(booking.checkIn));
const checkOutDisplay = computed(() => formatUsDate(booking.checkOut));

const priceLines = computed(() => {
    if (!booking.checkIn || !booking.checkOut) {
        return [{ label: 'Select dates to see your price', value: '—' }];
    }

    const nights = nightsBetween(booking.checkIn, booking.checkOut);
    const nightly = Number(apartment.value.price_daily || 0);
    const subtotal = nightly * nights;
    const cleaning = Number(apartment.value.cleaning_fee || 0);
    const total = subtotal + cleaning;

    const lines = [
        {
            label: `${formatVnd(nightly)} x ${nights} night${nights === 1 ? '' : 's'}`,
            value: formatVnd(subtotal),
        },
    ];

    if (cleaning > 0) {
        lines.push({ label: 'Cleaning fee', value: formatVnd(cleaning) });
    }

    lines.push({ label: 'Total', value: formatVnd(total), strike: null });

    return lines;
});

function formatClock(value) {
    if (!value) {
        return '';
    }
    return String(value).slice(0, 5);
}

function formatUsDate(isoDate) {
    if (!isoDate) {
        return '';
    }
    const [year, month, day] = isoDate.split('-');
    return `${month}/${day}/${year}`;
}

function defaultCheckIn() {
    const date = new Date();
    date.setDate(date.getDate() + 1);
    return date.toISOString().slice(0, 10);
}

function defaultCheckOut(checkIn) {
    const date = new Date(`${checkIn}T00:00:00`);
    date.setDate(date.getDate() + 1);
    return date.toISOString().slice(0, 10);
}

async function loadApartment() {
    loading.value = true;
    try {
        const res = await apiClient.get(`/public/apartments/${route.params.id}`);
        apartment.value = res?.data ?? {};
    } catch {
        apartment.value = {};
    } finally {
        loading.value = false;
    }
}

async function setupDatePickers() {
    await nextTick();
    destroyDatePickers();

    await initSingleDatePicker(checkInInput.value, {
        onApply: (isoDate) => {
            booking.checkIn = isoDate;
            if (!booking.checkOut || booking.checkOut <= isoDate) {
                booking.checkOut = defaultCheckOut(isoDate || defaultCheckIn());
            }
        },
    });

    await initSingleDatePicker(checkOutInput.value, {
        minDate: booking.checkIn ? new Date(`${booking.checkIn}T00:00:00`) : new Date(),
        onApply: (isoDate) => {
            booking.checkOut = isoDate;
        },
    });
}

function adjustGuests(field, delta) {
    const min = field === 'children' ? 0 : 1;
    booking[field] = Math.max(min, Number(booking[field] || 0) + delta);
}

function scrollTo(id) {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function shareApartment() {
    const url = window.location.href;
    if (navigator.share) {
        try {
            await navigator.share({ title: apartment.value.name, url });
            return;
        } catch {
            // fall through to clipboard
        }
    }
    await navigator.clipboard.writeText(url);
}

function submitBooking() {
    if (!booking.checkIn || !booking.checkOut) {
        window.alert('Please select check-in and check-out dates.');
        return;
    }

    if (!apartment.value.id) {
        return;
    }

    router.push({
        name: 'booking-checkout',
        params: { apartmentId: apartment.value.id },
        query: {
            check_in: booking.checkIn,
            check_out: booking.checkOut,
            adults: String(booking.adults),
            children: String(booking.children),
        },
    });
}

function onScroll() {
    topbarFixed.value = window.scrollY > 80;
}

function onDocumentClick(event) {
    if (!event.target.closest('.guest_opt')) {
        guestsOpen.value = false;
    }
}

onMounted(async () => {
    mount();
    booking.checkIn = defaultCheckIn();
    booking.checkOut = defaultCheckOut(booking.checkIn);
    await loadApartment();
    await setupDatePickers();
    window.addEventListener('scroll', onScroll, { passive: true });
    document.addEventListener('click', onDocumentClick);
    onScroll();
});

onUnmounted(() => {
    unmount();
    destroyDatePickers();
    window.removeEventListener('scroll', onScroll);
    document.removeEventListener('click', onDocumentClick);
});

watch(
    () => route.params.id,
    async () => {
        await loadApartment();
        await setupDatePickers();
    },
);
</script>

<style scoped>
.app_feature__desc {
    margin-bottom: 24px;
    font-size: 20px;
    line-height: 1.5;
    color: #013735;
}
</style>
