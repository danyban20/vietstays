<template>
    <div class="host-apt-detail">
        <div v-if="loading" class="host-apt-detail__loading">Loading apartment…</div>

        <div v-else class="host-apt-detail__body">
            <div class="host-apt-detail__main-col">
                <div class="host-tabs host-apt-detail__tabs">
                    <button
                        v-for="t in tabs"
                        :key="t.id"
                        type="button"
                        class="host-tab"
                        :class="{ 'host-tab--active': activeTab === t.id }"
                        @click="activeTab = t.id"
                    >
                        {{ t.label }}
                    </button>
                </div>

                <div ref="scrollEl" class="host-apt-detail__scroll">
                    <!-- Availability -->
                    <div v-show="activeTab === 'availability'" class="host-apt-detail__section">
                        <ApartmentAvailabilityTab
                            :apartment-id="apartmentId"
                            @live-status="onLiveStatus"
                            @add-period="onAddPeriod"
                        />
                    </div>

                    <!-- Price & terms -->
                    <div
                        v-show="activeTab === 'price'"
                        class="host-apt-detail__section host-apt-form-section"
                        data-checklist="price"
                    >
                        <h2 class="host-apt-detail__section-title">Pricing</h2>
                        <div class="host-form-grid">
                            <div class="host-field">
                                <label class="host-field__label" for="price-daily">Nightly rate (VND)</label>
                                <input
                                    id="price-daily"
                                    v-model.number="editForm.price_daily"
                                    type="number"
                                    min="0"
                                    step="10000"
                                    class="host-input"
                                />
                            </div>
                            <div class="host-field">
                                <label class="host-field__label" for="cleaning-fee">Cleaning fee (VND)</label>
                                <input
                                    id="cleaning-fee"
                                    v-model.number="editForm.cleaning_fee"
                                    type="number"
                                    min="0"
                                    step="10000"
                                    class="host-input"
                                />
                            </div>
                        </div>
                        <div class="host-suggested-price">
                            <span class="host-suggested-price__label">
                                Suggested rate for {{ apartment.type || 'this type' }} ·
                                {{ standardLabel }}
                            </span>
                            <span class="host-suggested-price__value">{{ formatVnd(suggestedPrice) }}</span>
                        </div>
                        <div class="host-panel host-panel--compact">
                            <p class="host-placeholder__title">Seasonal rates &amp; min stay</p>
                            <p class="host-field-hint">Advanced pricing rules will be configured here.</p>
                        </div>
                    </div>

                    <!-- Presentation -->
                    <div v-show="activeTab === 'presentation'" class="host-apt-detail__section host-apt-form-section">
                        <h2 class="host-apt-detail__section-title">Photos &amp; description</h2>

                        <div class="host-field-group" data-checklist="photos">
                            <div class="host-label-row">
                                <span class="host-field__label">Photos</span>
                                <span class="host-label-note">{{ validImages.length }} uploaded</span>
                            </div>

                            <div
                                class="host-apt-photo-dropzone"
                                :class="{ 'host-apt-photo-dropzone--active': dragOver }"
                                @click="fileInput?.click()"
                                @dragover.prevent="dragOver = true"
                                @dragleave.prevent="dragOver = false"
                                @drop.prevent="onPhotoDrop"
                            >
                                <p class="host-apt-photo-dropzone__icon">📷</p>
                                <p class="host-apt-photo-dropzone__title">
                                    <strong>Drag and drop images here</strong>
                                </p>
                                <p class="host-apt-photo-dropzone__hint">or select from your computer</p>
                                <button type="button" class="host-btn host-btn--ghost" @click.stop="fileInput?.click()">
                                    Select images
                                </button>
                                <p class="host-apt-photo-dropzone__note">JPG or PNG · Max 8 MB per image</p>
                                <input
                                    ref="fileInput"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    multiple
                                    class="host-apt-photo-dropzone__input"
                                    @change="onPhotoSelect"
                                />
                            </div>

                            <div v-if="photoUploading" class="host-field-hint">Uploading photos…</div>

                            <div v-if="validImages.length" class="host-apt-photo-grid">
                                <article
                                    v-for="(img, idx) in validImages"
                                    :key="`${img.image_id || img.thumb}-${idx}`"
                                    class="host-apt-photo-card"
                                    :class="{ 'host-apt-photo-card--cover': idx === 0 }"
                                >
                                    <img
                                        :src="resolveApartmentImageUrl(img.thumb || img.url)"
                                        alt=""
                                        class="host-apt-photo-card__img"
                                    />
                                    <span v-if="idx === 0" class="host-apt-photo-card__badge">Cover photo</span>
                                    <input
                                        v-model="img.caption"
                                        type="text"
                                        class="host-apt-photo-card__caption"
                                        placeholder="Caption (optional)"
                                        @input="markImagesDirty"
                                    />
                                </article>
                            </div>
                        </div>

                        <div class="host-field-group" data-checklist="short">
                            <div class="host-label-row">
                                <label class="host-field__label" for="edit-short">Short description</label>
                                <span class="host-label-note">max 300 characters</span>
                            </div>
                            <textarea
                                id="edit-short"
                                v-model="editForm.about_this_short"
                                class="host-textarea"
                                rows="4"
                                maxlength="300"
                                placeholder="A brief summary shown in search results and listing cards"
                            />
                            <div class="host-char-count">{{ editForm.about_this_short.length }} / 300</div>
                        </div>

                        <div class="host-field-group" data-checklist="desc">
                            <label class="host-field__label">Full description</label>
                            <TrumbowygEditor v-model="editForm.description" />
                            <p class="host-field-hint">
                                Describe the apartment layout, amenities, and neighbourhood for guests.
                            </p>
                        </div>

                        <div class="host-field">
                            <div class="host-label-row">
                                <label class="host-field__label" for="edit-feature">Distinctive feature</label>
                                <span class="host-label-note">Used in the apartment name</span>
                            </div>
                            <input
                                id="edit-feature"
                                v-model="editForm.distinguishing_feature"
                                type="text"
                                class="host-input"
                                placeholder="e.g. city view, rooftop pool"
                            />
                        </div>
                    </div>
                </div>

                <div
                    class="host-apt-sticky-bar"
                    :class="{ 'host-apt-sticky-bar--visible': stickyBarVisible || dirty }"
                >
                    <div class="host-apt-sticky-bar__left">
                        <router-link :to="{ name: 'apartments' }" class="host-apt-sticky-bar__back">
                            ← All apartments
                        </router-link>
                        <span class="host-apt-sticky-bar__status">
                            {{
                                dirty
                                    ? `${pendingChangeCount} change${pendingChangeCount === 1 ? '' : 's'} pending`
                                    : 'All saved'
                            }}
                        </span>
                    </div>
                    <div class="host-apt-sticky-bar__actions">
                        <button
                            type="button"
                            class="host-btn"
                            :class="dirty ? 'host-btn--accent' : 'host-btn--sand'"
                            :disabled="!dirty || saving"
                            @click="save"
                        >
                            {{ saving ? 'Saving…' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>

            <aside class="host-apt-detail__rail">
                <div class="host-apt-live-status" :class="liveStatusClass">
                    <p class="host-apt-live-status__label">Live status</p>
                    <p class="host-apt-live-status__value">{{ liveStatusLabel }}</p>
                </div>

                <div class="host-apt-rail-block">
                    <h2 class="host-apt-rail-card__title">Identity</h2>
                    <p class="host-apt-rail-card__name">{{ apartment.name }}</p>
                    <p class="host-apt-rail-card__meta">{{ apartment.district }} · {{ apartment.building }}</p>
                    <p class="host-apt-rail-card__meta">ID: {{ apartmentId }}</p>
                    <span class="host-pill" :class="statusPillClass">{{ apartment.status }}</span>

                    <div class="host-apt-progress">
                        <span class="host-apt-progress__label">Completion {{ liveCompletion }}%</span>
                        <div class="host-apt-progress__bar">
                            <div class="host-apt-progress__fill" :style="{ width: `${liveCompletion}%` }" />
                        </div>
                    </div>
                </div>

                <div class="host-apt-rail-block">
                    <h2 class="host-apt-rail-card__title">Registration checklist</h2>
                    <ul class="host-apt-checklist">
                        <li v-for="item in checklist" :key="item.id">
                            <button
                                type="button"
                                class="host-apt-checklist__item"
                                :class="{ 'host-apt-checklist__item--done': item.done }"
                                @click="jumpToChecklistItem(item)"
                            >
                                <span class="host-apt-checklist__icon">{{ item.done ? '✓' : '' }}</span>
                                {{ item.label }}
                            </button>
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import apiClient, { uploadFiles } from '@/api/client';
import ApartmentAvailabilityTab from '@/components/apartments/ApartmentAvailabilityTab.vue';
import TrumbowygEditor from '@/components/TrumbowygEditor.vue';
import { usePageTitle } from '@/composables/usePageTitle';
import { useToast } from '@/composables/useToast';
import {
    isValidApartmentImage,
    resolveApartmentImageUrl,
    stripHtml,
} from '@/utils/apartment-images';
import { formatVnd } from '@/utils/format';
import { QUALITY_STANDARDS, suggestDailyPrice } from '@/utils/pricing';

const route = useRoute();
const router = useRouter();
const toast = useToast();
const { setPageTitle, clearPageTitle } = usePageTitle();

const loading = ref(true);
const saving = ref(false);
const photoUploading = ref(false);
const dragOver = ref(false);
const activeTab = ref('presentation');
const stickyBarVisible = ref(false);
const fileInput = ref(null);
const scrollEl = ref(null);
const imagesDirty = ref(false);
const liveStatus = ref({ state: 'vacant', label: 'Vacant — no active booking' });

const tabs = [
    { id: 'availability', label: 'Availability' },
    { id: 'price', label: 'Price & terms' },
    { id: 'presentation', label: 'Presentation' },
];

const apartmentId = computed(() => route.params.id);

const apartment = ref({
    name: 'Apartment',
    district: '—',
    building: '—',
    type: '',
    standard: 'standard',
    status: 'draft',
    completion: 0,
    images: [],
    facilities: [],
});

const editForm = reactive({
    price_daily: 0,
    cleaning_fee: 0,
    about_this_short: '',
    description: '',
    distinguishing_feature: '',
});

const originalForm = reactive({
    price_daily: 0,
    cleaning_fee: 0,
    about_this_short: '',
    description: '',
    distinguishing_feature: '',
});

const originalImagesJson = ref('[]');

const standardLabel = computed(() => {
    const match = QUALITY_STANDARDS.find((s) => s.value === apartment.value.standard);
    return match?.label ?? apartment.value.standard ?? 'Standard';
});

const suggestedPrice = computed(() =>
    suggestDailyPrice(apartment.value.type, apartment.value.standard),
);

const validImages = computed(() =>
    (apartment.value.images ?? []).filter(isValidApartmentImage),
);

const liveStatusClass = computed(() => {
    const state = liveStatus.value.state ?? 'vacant';
    return `host-apt-live-status--${state}`;
});

const liveStatusLabel = computed(() => liveStatus.value.label ?? 'Vacant — no active booking');

const statusPillClass = computed(() => {
    const status = (apartment.value.status ?? '').toLowerCase();
    if (status === 'active') return 'host-pill--active';
    if (status === 'pending') return 'host-pill--pending';
    return 'host-pill--draft';
});

const dirty = computed(
    () =>
        editForm.price_daily !== originalForm.price_daily ||
        editForm.cleaning_fee !== originalForm.cleaning_fee ||
        editForm.about_this_short !== originalForm.about_this_short ||
        editForm.description !== originalForm.description ||
        editForm.distinguishing_feature !== originalForm.distinguishing_feature ||
        imagesDirty.value,
);

const pendingChangeCount = computed(() => {
    let count = 0;
    if (editForm.price_daily !== originalForm.price_daily) count += 1;
    if (editForm.cleaning_fee !== originalForm.cleaning_fee) count += 1;
    if (editForm.about_this_short !== originalForm.about_this_short) count += 1;
    if (editForm.description !== originalForm.description) count += 1;
    if (editForm.distinguishing_feature !== originalForm.distinguishing_feature) count += 1;
    if (imagesDirty.value) count += 1;
    return count;
});

const checklist = computed(() => [
    {
        id: 'photos',
        label: 'Photos uploaded',
        done: validImages.value.length > 0,
        tab: 'presentation',
        anchor: 'photos',
    },
    {
        id: 'short',
        label: 'Short description',
        done: Boolean(editForm.about_this_short.trim()),
        tab: 'presentation',
        anchor: 'short',
    },
    {
        id: 'desc',
        label: 'Full description',
        done: Boolean(stripHtml(editForm.description)),
        tab: 'presentation',
        anchor: 'desc',
    },
    {
        id: 'price',
        label: 'Nightly rate set',
        done: (editForm.price_daily ?? 0) > 0,
        tab: 'price',
        anchor: 'price',
    },
    {
        id: 'facilities',
        label: 'Facilities selected',
        done: (apartment.value.facilities?.length ?? 0) > 0,
        tab: 'presentation',
        anchor: null,
    },
]);

const liveCompletion = computed(() => {
    const done = checklist.value.filter((item) => item.done).length;
    return Math.round((done / checklist.value.length) * 100);
});

function snapshotImages() {
    originalImagesJson.value = JSON.stringify(apartment.value.images ?? []);
    imagesDirty.value = false;
}

function markImagesDirty() {
    imagesDirty.value = JSON.stringify(apartment.value.images ?? []) !== originalImagesJson.value;
}

function applyFormFromApartment(data) {
    editForm.price_daily = data.price_daily ?? 0;
    editForm.cleaning_fee = data.cleaning_fee ?? 0;
    editForm.about_this_short = data.about_this_short ?? '';
    editForm.description = data.description ?? '';
    editForm.distinguishing_feature = data.distinguishing_feature ?? '';
    originalForm.price_daily = editForm.price_daily;
    originalForm.cleaning_fee = editForm.cleaning_fee;
    originalForm.about_this_short = editForm.about_this_short;
    originalForm.description = editForm.description;
    originalForm.distinguishing_feature = editForm.distinguishing_feature;
}

function applyApartmentData(data) {
    apartment.value = {
        name: data.name ?? 'Apartment',
        district: data.district ?? '—',
        building: data.building ?? '—',
        type: data.type ?? '',
        standard: data.standard ?? 'standard',
        status: data.status ?? 'draft',
        completion: data.completion ?? 0,
        images: Array.isArray(data.images) ? data.images : [],
        facilities: data.facilities ?? [],
        about_this_short: data.about_this_short ?? '',
        description: data.description ?? '',
        price_daily: data.price_daily ?? 0,
    };
    applyFormFromApartment(data);
    setPageTitle(apartment.value.name);
    snapshotImages();
}

function onLiveStatus(status) {
    liveStatus.value = status ?? { state: 'vacant', label: 'Vacant — no active booking' };
}

function onAddPeriod() {
    router.push({ name: 'bookings', query: { add: 'manual' } });
}

function jumpToChecklistItem(item) {
    activeTab.value = item.tab;
    if (item.anchor) {
        requestAnimationFrame(() => {
            const target = scrollEl.value?.querySelector(`[data-checklist="${item.anchor}"]`);
            target?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
}

function onScroll() {
    const scrollTop = scrollEl.value?.scrollTop ?? 0;
    stickyBarVisible.value = scrollTop > 40;
}

async function uploadPhotoFiles(files) {
    const list = Array.from(files ?? []).filter((file) => file.type.startsWith('image/'));

    if (!list.length) {
        return;
    }

    photoUploading.value = true;

    try {
        for (const file of list) {
            const fd = new FormData();
            fd.append('photos[]', file);
            const res = await uploadFiles(`/apartments/${apartmentId.value}/photos`, fd);
            if (res?.data) {
                applyApartmentData(res.data);
            }
        }
        toast.show('Photos uploaded.');
    } catch (err) {
        toast.show(err.message ?? 'Could not upload photos.');
    } finally {
        photoUploading.value = false;
    }
}

function onPhotoSelect(event) {
    uploadPhotoFiles(event.target.files);
    event.target.value = '';
}

function onPhotoDrop(event) {
    dragOver.value = false;
    uploadPhotoFiles(event.dataTransfer?.files);
}

async function loadApartment() {
    loading.value = true;

    try {
        const res = await apiClient.get(`/apartments/${apartmentId.value}`);
        applyApartmentData(res?.data ?? {});
    } catch {
        toast.show('Could not load apartment.');
    } finally {
        loading.value = false;
    }
}

async function save() {
    saving.value = true;

    try {
        const payload = {
            price_daily: editForm.price_daily,
            cleaning_fee: editForm.cleaning_fee,
            about_this_short: editForm.about_this_short,
            description: editForm.description,
            distinguishing_feature: editForm.distinguishing_feature,
        };

        if (imagesDirty.value) {
            payload.images = (apartment.value.images ?? []).filter(isValidApartmentImage);
        }

        const res = await apiClient.put(`/apartments/${apartmentId.value}`, payload);
        applyApartmentData({ ...apartment.value, ...(res?.data ?? {}) });
        toast.show('Apartment saved.');
    } catch (err) {
        toast.show(err.message ?? 'Could not save apartment.');
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    loadApartment();
    scrollEl.value?.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
});

onUnmounted(() => {
    scrollEl.value?.removeEventListener('scroll', onScroll);
    clearPageTitle();
});

watch(apartmentId, loadApartment);
</script>
