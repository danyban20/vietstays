<template>
    <div id="dashboard" :class="pageClass">
        <div class="container">
            <router-link :to="{ name: 'member-reservations' }" class="back_reservation">← Back to reservations</router-link>

            <div v-if="loading" class="member-loading">Loading your reservation…</div>
            <div v-else-if="error" class="member-error">
                <p>{{ error }}</p>
                <button type="button" class="btn" @click="load">Try again</button>
            </div>

            <div v-else class="booking_details_wrap">
                <div class="booking_details_left">
                    <!-- Countdown (mobile: above the photos) -->
                    <div v-if="isUpcoming" class="remaing_days mob_show">
                        <span class="icon" />
                        <p><strong>{{ countdownTitle }}</strong>{{ countdownText }}</p>
                    </div>

                    <!-- Photos, status, name, address -->
                    <div class="gallary_app_block">
                        <div v-if="isUpcoming && gallery.length >= 3" class="img_wrap">
                            <div class="block">
                                <div class="img">
                                    <a href="#" @click.prevent="openGallery(0)"><img :src="gallery[0].full" :alt="booking.apartment.name" /></a>
                                </div>
                            </div>
                            <div class="block">
                                <div class="img">
                                    <a href="#" @click.prevent="openGallery(1)"><img :src="gallery[1].thumb" alt="" /></a>
                                </div>
                                <div class="img">
                                    <a href="#" @click.prevent="openGallery(2)"><img :src="gallery[2].thumb" alt="" /></a>
                                    <a href="#" class="view_gall_btn" @click.prevent="openGallery(0)">View all {{ gallery.length }} photos</a>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="gallery.length" class="img_single" :class="{ img_single_mob_show: isPast }">
                            <a href="#" @click.prevent="openGallery(0)"><img :src="gallery[0].full" :alt="booking.apartment.name" /></a>
                        </div>
                        <div class="desc">
                            <div class="top_desc">
                                <span class="left_text" :class="statusClass">{{ booking.badge.label }}</span>
                                <span class="right_text"><strong>Booking ID:</strong> {{ booking.booking_num }}</span>
                            </div>
                            <h2>{{ booking.apartment.name }}</h2>
                            <div class="location">
                                <img :src="asset('/member/images/pin_3.svg')" alt="" />{{ detail.address }}
                                <a v-if="detail.map_url && !isPast" :href="detail.map_url" target="_blank" rel="noopener"> View on map</a>
                            </div>
                            <div v-if="isPast || isCancelled" class="btn_wrap">
                                <router-link v-if="apartmentRoute" :to="apartmentRoute" class="btn border_btn">View apartment</router-link>
                                <router-link v-if="booking.apartment.id" :to="{ name: 'booking-checkout', params: { apartmentId: booking.apartment.id } }" class="btn">
                                    Book again
                                </router-link>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Before check-in ===== -->
                    <template v-if="isUpcoming">
                        <div class="remaing_days mob_hide">
                            <span class="icon" />
                            <p><strong>{{ countdownTitle }}</strong>{{ countdownText }}</p>
                        </div>

                        <div class="guests_reg_block">
                            <div class="registerd">{{ booking.passports.registered }} of {{ booking.passports.expected }} guests registered</div>
                            <div class="guests_reg_block_inner">
                                <div class="left_desc">
                                    <h5>{{ passportsComplete ? 'Guest registration complete' : 'Complete your guest registration' }}</h5>
                                    <p>
                                        Save time at check-in by adding passport details for every guest before arrival. Your host uses them to register
                                        international guests’ temporary stay in Vietnam.
                                    </p>
                                    <div class="btn_wrap">
                                        <a href="#" class="btn" @click.prevent="openPassports()">Manage guest passports</a>
                                    </div>
                                    <a href="#" class="link_text" @click.prevent="privacyOpen = true">How your information is used</a>
                                </div>
                                <div class="right_desc">
                                    <h5>Your stay address</h5>
                                    <p>{{ detail.address }}</p>
                                    <div class="btn_wrap">
                                        <a href="#" class="copy_btn" @click.prevent="copy(detail.address, 'Address copied.')">Copy address</a>
                                        <a v-if="detail.map_url" :href="detail.map_url" target="_blank" rel="noopener" class="view_map_btn">View on map</a>
                                    </div>
                                    <div v-if="mapEmbedUrl" class="img member-map">
                                        <iframe :src="mapEmbedUrl" title="Map of the apartment" loading="lazy" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="detail.amenities.length" class="aminities_block">
                            <div class="aminities_title">
                                <h4>Apartment amenities</h4>
                                <a v-if="detail.amenities.length > 6" href="#" class="view_all_link" @click.prevent="amenitiesOpen = true">View all amenities</a>
                            </div>
                            <ul>
                                <li v-for="item in detail.amenities.slice(0, 6)" :key="item">{{ item }}</li>
                            </ul>
                        </div>
                    </template>

                    <!-- ===== During the stay: codes ===== -->
                    <div v-if="isCurrent" class="access_code_block">
                        <div class="door_code_block">
                            <h6>Door Access Code</h6>
                            <template v-if="access.door_code">
                                <div class="code_text">
                                    <span>{{ access.door_code }}</span>
                                    <a href="#" class="copybtn" @click.prevent="copy(access.door_code, 'Door code copied.')">Copy</a>
                                </div>
                                <p>{{ access.door_code_note || 'Use the keypad on the door lock.' }}</p>
                            </template>
                            <p v-else>Your host will give you access on arrival. Message them if you need help getting in.</p>
                        </div>
                        <div class="wifi_block">
                            <h6>Wi-Fi Connection</h6>
                            <ul v-if="access.wifi_network || access.wifi_password">
                                <li v-if="access.wifi_network">
                                    <span>Network</span>
                                    <span>{{ access.wifi_network }}<a href="#" class="copy_icon" aria-label="Copy network name" @click.prevent="copy(access.wifi_network, 'Network name copied.')" /></span>
                                </li>
                                <li v-if="access.wifi_password">
                                    <span>Password</span>
                                    <span>{{ access.wifi_password }}<a href="#" class="copy_icon" aria-label="Copy password" @click.prevent="copy(access.wifi_password, 'Password copied.')" /></span>
                                </li>
                            </ul>
                            <p v-else>Wi-Fi details are in the apartment. Ask your host if you can’t find them.</p>
                        </div>
                    </div>

                    <!-- Housekeeping plan / stay timeline -->
                    <div v-if="(isUpcoming || isCurrent) && showHousekeeping" class="house_keepingplan">
                        <div class="house_keepingplan_title">
                            <h5>{{ isCurrent ? 'Stay Timeline & Cleaning' : 'Housekeeping plan' }}</h5>
                            <div class="included">{{ housekeeping.badge }}</div>
                        </div>
                        <ul>
                            <li
                                v-for="item in housekeeping.timeline"
                                :key="`${item.date}-${item.label}`"
                                :class="{ current: item.state === 'done', active: item.state === 'next' }"
                            >
                                <strong>{{ item.date_label }}</strong>{{ item.label }}
                            </li>
                        </ul>
                        <div v-if="openRequests.length" class="member-service-list">
                            <p v-for="service in openRequests" :key="service.id">
                                Extra cleaning {{ service.date_label }} · {{ service.time_label }} —
                                <strong>{{ service.status === 'confirmed' ? 'confirmed' : 'waiting for host' }}</strong>
                                <a v-if="service.status === 'requested'" href="#" @click.prevent="withdraw(service)">Withdraw</a>
                            </p>
                        </div>
                        <div v-if="extraCleaning.available" class="btn_wrap">
                            <p>
                                <template v-if="isUpcoming">Extra cleaning is {{ money(extraCleaning.price) }}. </template>
                                Same-day requests must be placed before {{ extraCleaning.cutoff_label }}.
                                <template v-if="isCurrent"> Additional fees apply.</template>
                            </p>
                            <a v-if="extraCleaning.earliest_date" href="#" class="btn border_btn" @click.prevent="cleaningOpen = true">
                                {{ isCurrent ? 'Add extra cleaning' : 'Request extra cleaning' }}
                            </a>
                        </div>
                    </div>

                    <!-- ===== Before check-in: codes not yet shown ===== -->
                    <template v-if="isUpcoming">
                        <div class="acc_code_block">
                            <div class="block">
                                <p>Door Access Code</p>
                                <p v-if="access.door_code">
                                    <strong class="member-code-ready">{{ access.door_code }}</strong>{{ access.door_code_note || 'Ready for your arrival.' }}
                                </p>
                                <p v-else-if="booking.status === 'pending'">
                                    <strong>Available once your booking is confirmed</strong>Your host has not confirmed yet
                                </p>
                                <p v-else>
                                    <strong>Code available {{ revealHours }}h before check-in</strong>Unlocks on {{ access.door_code_reveal_label }}
                                </p>
                            </div>
                            <div class="block">
                                <p>Wi-Fi Details</p>
                                <p><strong>Available at check-in</strong>Once you enter the property</p>
                            </div>
                        </div>

                        <div class="arrival_block">
                            <h4>Arrival Instructions</h4>
                            <p v-if="arrival.instructions" class="member-pre">{{ arrival.instructions }}</p>
                            <p v-else>
                                Your host will share check-in details before you arrive. You’ll see the access code here {{ revealHours }} hours before check-in.
                            </p>
                            <p v-if="arrival.parking"><strong>Parking Information</strong>{{ arrival.parking }}</p>
                            <p v-if="arrival.contact_phone">
                                <strong>Arrival Assistance</strong>For arrival assistance, contact {{ arrival.contact_label || 'the building reception' }}:
                                <a :href="`tel:${tel(arrival.contact_phone)}`">{{ arrival.contact_phone }}</a>
                            </p>
                        </div>

                        <p v-if="booking.cancellation.allowed" class="member-cancel-link">
                            Plans changed? <a href="#" @click.prevent="cancelOpen = true">Cancel reservation</a>
                        </p>
                    </template>

                    <!-- ===== During the stay: house rules ===== -->
                    <div v-if="isCurrent && detail.house_rules.length" class="house_rules_block">
                        <h5>House Rules Summary</h5>
                        <ul>
                            <li v-for="rule in detail.house_rules.slice(0, 3)" :key="rule">{{ rule }}</li>
                        </ul>
                        <a v-if="detail.house_rules.length > 3" href="#" class="view_all_link" @click.prevent="rulesOpen = true">View full house rules →</a>
                    </div>

                    <!-- ===== After check-out ===== -->
                    <template v-if="isPast">
                        <StayReviewCard
                            v-if="booking.review.eligible && showReview"
                            :booking-id="booking.id"
                            :review="booking.review"
                            :apartment-name="booking.apartment.name"
                            :host-name="booking.host.name"
                            @done="onReviewDone"
                        />
                    </template>

                    <div v-if="isPast || isCancelled" class="doucments_rec">
                        <h5>Documents &amp; Receipts</h5>
                        <ul>
                            <li v-if="booking.documents.receipt">
                                <strong>Receipt</strong>PDF format
                                <div>
                                    <a href="#" class="btn" @click.prevent="download('receipt')">{{ downloading === 'receipt' ? 'Preparing…' : 'Download receipt' }}</a>
                                </div>
                            </li>
                            <li v-if="booking.documents.confirmation">
                                <strong>Booking Confirmation Document</strong>PDF format
                                <div>
                                    <a href="#" class="btn" @click.prevent="download('confirmation')">
                                        {{ downloading === 'confirmation' ? 'Preparing…' : 'Download confirmation' }}
                                    </a>
                                </div>
                            </li>
                            <li v-if="isCancelled" class="member-doc-note">
                                <strong>Cancelled</strong>{{ booking.cancelled_by_guest ? 'You cancelled this booking.' : 'This booking was cancelled.' }} Nothing is due.
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- ===== Right column ===== -->
                <div class="booking_details_right">
                    <div class="hosted_by_block">
                        <div class="user_info">
                            <img :src="asset('/member/images/users_ico_2.png')" alt="" />
                            <p>
                                <strong>{{ isUpcoming ? `Hosted by ${booking.host.name}` : booking.host.name }}</strong>
                                {{ booking.host.is_team ? 'Managed by Vietstays' : 'Your host' }}
                            </p>
                        </div>
                        <router-link
                            v-if="booking.conversation_id"
                            :to="{ name: 'member-messages', params: { conversation: booking.conversation_id }, query: { booking: booking.id } }"
                            class="btn border_btn"
                            :class="{ msz_host_btn: isCurrent }"
                        >
                            Message host
                        </router-link>
                    </div>

                    <div class="stay_information_block">
                        <h6>Stay Information</h6>
                        <p>CHECK-IN<strong>{{ formatDay(booking.check_in) }}</strong>{{ isCurrent || isPast ? 'from' : 'at' }} {{ booking.check_in_time }}</p>
                        <p>CHECK-OUT<strong>{{ formatDay(booking.check_out) }}</strong>by {{ booking.check_out_time }}</p>
                        <p>GUESTS<strong>{{ guestsLabel(booking.adults, booking.children) }}</strong></p>
                    </div>

                    <div class="total_block">
                        <h6>Price Breakdown</h6>
                        <ul>
                            <li v-for="(line, index) in booking.price.lines" :key="index" :class="{ discount_text: line.amount < 0 }">
                                <span>{{ line.label }}</span><span>{{ line.amount < 0 ? '−' : '' }}{{ money(Math.abs(line.amount)) }}</span>
                            </li>
                            <li><span>{{ booking.price.total_label }}</span><span>{{ money(booking.price.total) }}</span></li>
                        </ul>
                    </div>

                    <div v-if="isCurrent && (booking.support.security_phone || booking.support.support_phone)" class="emergecy_block">
                        <h6><img :src="asset('/member/images/alert-triangle.svg')" alt="" />Emergency &amp; Support</h6>
                        <ul>
                            <li v-if="booking.support.security_phone">
                                Building Security<a :href="`tel:${tel(booking.support.security_phone)}`">{{ booking.support.security_phone }}</a>
                            </li>
                            <li v-if="booking.support.support_phone">
                                Vietstays Support<a :href="`tel:${tel(booking.support.support_phone)}`">{{ booking.support.support_phone }}</a>
                            </li>
                        </ul>
                    </div>

                    <p v-if="isPast && booking.review.eligible && !showReview && !booking.review.submitted" class="member-side-link">
                        <a href="#" @click.prevent="showReview = true">Review this stay</a>
                    </p>
                </div>
            </div>
        </div>

        <template v-if="booking">
            <PassportsModal
                :open="passportsOpen"
                :booking-id="booking.id"
                :summary="booking.passports"
                :start-with="passportStart"
                @close="passportsOpen = false"
                @saved="onPassportsSaved"
                @privacy="privacyOpen = true"
            />
            <ExtraCleaningModal
                :open="cleaningOpen"
                :booking-id="booking.id"
                :options="extraCleaning"
                :currency="booking.currency"
                @close="cleaningOpen = false"
                @ordered="onCleaningOrdered"
            />
            <CancelReservationModal
                :open="cancelOpen"
                :booking-id="booking.id"
                :quote="booking.cancellation"
                :currency="booking.currency"
                @close="cancelOpen = false"
                @cancelled="onCancelled"
            />
            <MemberModal :open="amenitiesOpen" title="Apartment amenities" @close="amenitiesOpen = false">
                <ul class="member-plain-list">
                    <li v-for="item in detail.amenities" :key="item">{{ item }}</li>
                </ul>
            </MemberModal>
            <MemberModal :open="rulesOpen" title="House rules" @close="rulesOpen = false">
                <ul class="member-plain-list">
                    <li v-for="rule in detail.house_rules" :key="rule">{{ rule }}</li>
                </ul>
            </MemberModal>
            <MemberModal :open="privacyOpen" title="How your information is used" @close="privacyOpen = false">
                <p>
                    Vietnamese law requires hosts to register the temporary stay of foreign guests with the local authorities. Your host uses the
                    passport details you add here only for that registration.
                </p>
                <p>
                    Passport numbers and dates of birth are stored encrypted. Only you, your host and the Vietstays staff who manage your booking can
                    see them. You can update the details until check-out.
                </p>
                <p><router-link :to="{ name: 'terms', hash: '#privacy' }" target="_blank">Read our privacy terms</router-link></p>
            </MemberModal>
            <MemberModal :open="galleryIndex !== null" :title="booking.apartment.name" panel-class="member-gallery" @close="galleryIndex = null">
                <div v-if="galleryIndex !== null" class="member-gallery__stage">
                    <img :src="gallery[galleryIndex].full" :alt="`Photo ${galleryIndex + 1}`" />
                    <div class="member-gallery__nav">
                        <button type="button" class="btn border_btn" :disabled="galleryIndex === 0" @click="galleryIndex--">Previous</button>
                        <span>{{ galleryIndex + 1 }} / {{ gallery.length }}</span>
                        <button type="button" class="btn border_btn" :disabled="galleryIndex === gallery.length - 1" @click="galleryIndex++">Next</button>
                    </div>
                </div>
            </MemberModal>
        </template>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import apiClient, { downloadFile } from '@/api/client';
import CancelReservationModal from '@/components/member/CancelReservationModal.vue';
import ExtraCleaningModal from '@/components/member/ExtraCleaningModal.vue';
import MemberModal from '@/components/member/MemberModal.vue';
import PassportsModal from '@/components/member/PassportsModal.vue';
import StayReviewCard from '@/components/member/StayReviewCard.vue';
import { useMemberAreaStore } from '@/stores/memberArea';
import { withAppBase } from '@/utils/app-base';
import { copyText, formatDay, formatMoney, guestsLabel } from '@/utils/member-format';

const route = useRoute();
const area = useMemberAreaStore();

const loading = ref(true);
const error = ref('');
const booking = ref(null);
const downloading = ref(null);

const passportsOpen = ref(false);
const passportStart = ref(null);
const cleaningOpen = ref(false);
const cancelOpen = ref(false);
const amenitiesOpen = ref(false);
const rulesOpen = ref(false);
const privacyOpen = ref(false);
const galleryIndex = ref(null);
const showReview = ref(true);

const asset = (path) => withAppBase(path);

const stage = computed(() => booking.value?.stage);
const isUpcoming = computed(() => stage.value === 'upcoming');
const isCurrent = computed(() => stage.value === 'current');
const isPast = computed(() => stage.value === 'past');
const isCancelled = computed(() => stage.value === 'cancelled');

const detail = computed(() => booking.value?.apartment_detail ?? {});
const gallery = computed(() => detail.value.gallery ?? []);
const access = computed(() => booking.value?.access ?? {});
const revealHours = computed(() => access.value.reveal_hours ?? 24);
const arrival = computed(() => booking.value?.arrival ?? {});
const housekeeping = computed(() => booking.value?.housekeeping ?? {});
const extraCleaning = computed(() => housekeeping.value.extra_cleaning ?? {});
const openRequests = computed(() => (housekeeping.value.requests ?? []).filter((item) => ['requested', 'confirmed'].includes(item.status)));
const passportsComplete = computed(() => booking.value && booking.value.passports.registered >= booking.value.passports.expected);
const showHousekeeping = computed(
    () => housekeeping.value.cleanings_per_week > 0 || extraCleaning.value.available || openRequests.value.length > 0,
);

const pageClass = computed(() => (isCurrent.value ? 'booking_details_stay' : 'booking_list booking_details'));

const statusClass = computed(() => {
    if (isCurrent.value) return 'progress_text';
    if (isPast.value) return 'completed_text';
    if (isCancelled.value) return 'cancelled_text';
    return booking.value?.status === 'pending' ? 'pending_text' : '';
});

const apartmentRoute = computed(() => {
    const apartment = booking.value?.apartment;

    if (apartment?.slug) return { name: 'public-apartment', params: { slug: apartment.slug } };
    if (apartment?.id) return { name: 'public-apartment-id', params: { id: apartment.id } };
    return null;
});

const countdownTitle = computed(() => {
    const days = booking.value?.days_until_check_in ?? 0;

    if (booking.value?.status === 'pending') return 'Waiting for host confirmation';
    if (days === 0) return 'Check-in is today';
    return days === 1 ? '1 day until check-in' : `${days} days until check-in`;
});

const countdownText = computed(() => {
    if (booking.value?.status === 'pending') {
        return 'We’ll email you as soon as your host confirms. You can already add passport details.';
    }

    return detail.value.city ? `Prepare for your upcoming trip to beautiful ${detail.value.city}.` : 'Prepare for your upcoming trip.';
});

const mapEmbedUrl = computed(() => {
    const { latitude: lat, longitude: lng } = detail.value;

    if (lat == null || lng == null) {
        return null;
    }

    const d = 0.006;
    const bbox = [lng - d, lat - d, lng + d, lat + d].join(',');

    return `https://www.openstreetmap.org/export/embed.html?bbox=${bbox}&layer=mapnik&marker=${lat},${lng}`;
});

function money(amount) {
    return formatMoney(amount, booking.value?.currency);
}

function tel(phone) {
    return String(phone).replace(/[^\d+]/g, '');
}

function openGallery(index) {
    galleryIndex.value = gallery.value.length ? index : null;
}

function openPassports(position = null) {
    passportStart.value = position;
    passportsOpen.value = true;
}

async function copy(text, message) {
    area.notify((await copyText(text)) ? message : 'Could not copy — please select and copy it manually.', 'success');
}

async function download(type) {
    if (downloading.value) return;
    downloading.value = type;

    try {
        await downloadFile(`/member/reservations/${booking.value.id}/documents/${type}`, `vietstays-${type}.pdf`);
    } catch (err) {
        area.notify(err.message || 'Could not download the document.', 'error');
    } finally {
        downloading.value = null;
    }
}

function onPassportsSaved(summary, message) {
    booking.value.passports = summary;
    area.notify(message || 'Passport details saved.');
}

async function onCleaningOrdered(message) {
    cleaningOpen.value = false;
    area.notify(message || 'Extra cleaning requested.');
    await load(false);
}

async function withdraw(service) {
    try {
        const res = await apiClient.delete(`/member/reservations/${booking.value.id}/services/${service.id}`);
        area.notify(res.message || 'Request withdrawn.');
        await load(false);
    } catch (err) {
        area.notify(err.message || 'Could not withdraw the request.', 'error');
    }
}

function onCancelled(data, message) {
    cancelOpen.value = false;
    booking.value = { ...booking.value, ...data };
    area.notify(message || 'Your reservation has been cancelled.');
}

function onReviewDone(message, skipped = false) {
    if (skipped) {
        showReview.value = false;
        return;
    }

    booking.value.review.submitted = true;
    area.notify(message || 'Thank you for your review!');
}

async function load(showSpinner = true) {
    if (showSpinner) loading.value = true;
    error.value = '';

    try {
        const res = await apiClient.get(`/member/reservations/${route.params.id}`);
        booking.value = res.data;
        showReview.value = !res.data.review?.skipped;
    } catch (err) {
        error.value = err.status === 404 ? 'We could not find this reservation in your account.' : err.message || 'Could not load the reservation.';
    } finally {
        loading.value = false;
    }
}

watch(() => route.params.id, (id) => id && load());
onMounted(load);
</script>
