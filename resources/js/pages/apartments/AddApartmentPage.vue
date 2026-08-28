<template>
    <div class="vv-apt2026-overlay host-wizard-embed">
        <div class="vv-apt2026-modal" role="main" aria-label="Add new apartment">
            <header class="vv-apt2026-modal__header">
                <h2>Add new apartment</h2>
                <router-link
                    :to="{ name: 'apartments' }"
                    class="vv-apt2026-modal__close"
                    title="Close"
                >
                    ×
                </router-link>
            </header>

                <nav class="vv-apt2026-step-tabs" aria-label="Wizard steps">
                    <button
                        v-for="step in steps"
                        :key="step.id"
                        type="button"
                        class="vv-apt2026-step-tabs__btn"
                        :class="stepTabClass(step.id)"
                        :disabled="step.id > maxTab"
                        @click="goToTab(step.id)"
                    >
                        <span class="num">{{ step.num }}</span>{{ step.label }}
                    </button>
                </nav>

                <div class="vv-apt2026-body">
                    <p v-if="error" class="host-form-error">{{ error }}</p>

                    <!-- Step 1: Building & type -->
                    <div v-show="tab === 0">
                        <div class="vv-apt2026-field-group vv-apt2026-location-cascade">
                            <div class="vv-apt2026-form-row">
                                <div class="col-md-6">
                                    <label class="vv-apt2026-label" for="apt-country">Country</label>
                                    <select id="apt-country" class="vv-apt2026-field is-filled" disabled>
                                        <option value="VN" selected>Vietnam</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="vv-apt2026-label" for="apt-city">City</label>
                                    <select
                                        id="apt-city"
                                        v-model="form.city_id"
                                        class="vv-apt2026-field"
                                        :class="{ 'is-filled': form.city_id }"
                                        @change="onCityChange"
                                    >
                                        <option value="">Select city…</option>
                                        <option v-for="city in cities" :key="city.city_id" :value="city.city_id">
                                            {{ city.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="vv-apt2026-form-row">
                                <div class="col-md-6">
                                    <label class="vv-apt2026-label" for="apt-district">District / neighbourhood</label>
                                    <select
                                        id="apt-district"
                                        v-model="form.district_id"
                                        class="vv-apt2026-field"
                                        :class="{ 'is-filled': form.district_id }"
                                        :disabled="!form.city_id"
                                        @change="onDistrictChange"
                                    >
                                        <option value="">Select district…</option>
                                        <option v-for="d in districts" :key="d.district_id" :value="d.district_id">
                                            {{ d.name }}
                                        </option>
                                    </select>
                                    <p class="vv-apt2026-hint mb-0">Selected after city</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="vv-apt2026-label" for="apt-building">Building</label>
                                    <select
                                        id="apt-building"
                                        v-model="form.building_id"
                                        class="vv-apt2026-field"
                                        :class="{ 'is-filled': form.building_id }"
                                        :disabled="!form.district_id"
                                        @change="onBuildingChange"
                                    >
                                        <option value="">Select building…</option>
                                        <option v-for="b in buildings" :key="b.id" :value="b.id">{{ b.name }}</option>
                                    </select>
                                    <p class="vv-apt2026-hint mb-0">Selected after district</p>
                                </div>
                            </div>
                            <p v-if="buildingLocationHint" class="vv-apt2026-hint mb-1">{{ buildingLocationHint }}</p>
                            <p class="mb-0">
                                <button
                                    type="button"
                                    class="vv-apt2026-link-orange"
                                    style="border:none;background:none;padding:0;cursor:pointer;font-size:13px;font-weight:600"
                                    @click="showBuildingRequest = !showBuildingRequest"
                                >
                                    {{ showBuildingRequest ? '− Hide building request' : '+ Suggest new building' }}
                                </button>
                            </p>
                        </div>

                        <div v-show="showBuildingRequest" class="vv-apt2026-building-request">
                            <h6>Suggest a new building</h6>
                            <p class="vv-apt2026-hint">
                                Vietstays will review your request and add the building so you can finish listing setup.
                            </p>
                            <div class="vv-apt2026-form-row">
                                <div class="col-md-6">
                                    <label class="vv-apt2026-label">Building name</label>
                                    <input
                                        v-model="buildingRequest.name"
                                        type="text"
                                        class="vv-apt2026-field"
                                        maxlength="200"
                                    />
                                </div>
                                <div class="col-md-6">
                                    <label class="vv-apt2026-label">Street address</label>
                                    <input v-model="buildingRequest.address" type="text" class="vv-apt2026-field" />
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="vv-apt2026-label">Additional notes</label>
                                <textarea v-model="buildingRequest.notes" class="vv-apt2026-field" rows="2" />
                            </div>
                            <button type="button" class="vv-apt2026-btn-pick vv-apt2026-btn-pick--sm" @click="submitBuildingRequest">
                                Send request
                            </button>
                        </div>

                        <div class="vv-apt2026-field-group">
                            <label class="vv-apt2026-label">Apartment type</label>
                            <div class="vv-apt2026-type-pills vv-apt2026-type-pills--spaced">
                                <label v-for="t in apartmentTypes" :key="t">
                                    <input v-model="form.apartment_type" type="radio" :value="t" />
                                    <span>{{ t }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="vv-apt2026-field-group">
                            <div class="vv-apt2026-label-row">
                                <label class="vv-apt2026-label mb-0" for="apt-feature">Distinctive feature</label>
                                <span class="vv-apt2026-label-note">Used in the apartment name</span>
                            </div>
                            <input
                                id="apt-feature"
                                v-model="form.distinguishing_feature"
                                type="text"
                                class="vv-apt2026-field"
                                :class="{ 'is-filled': form.distinguishing_feature.trim() }"
                                maxlength="120"
                                placeholder="e.g. 'City View &amp; Pool'…"
                            />
                            <p class="vv-apt2026-hint">Apartment name is generated automatically as you fill in the fields.</p>
                            <p class="vv-apt2026-name-preview">
                                {{ namePreview ? `Apartment name: ${namePreview}` : '' }}
                            </p>
                        </div>

                        <div class="vv-apt2026-form-row">
                            <div class="col-md-6">
                                <div class="vv-apt2026-field-group mb-0">
                                    <div class="vv-apt2026-label-row">
                                        <label class="vv-apt2026-label mb-0" for="apt-room">Floor / unit ID</label>
                                        <span class="vv-apt2026-label-note">optional</span>
                                    </div>
                                    <input
                                        id="apt-room"
                                        v-model="form.room_number"
                                        type="text"
                                        class="vv-apt2026-field"
                                        placeholder="e.g. 8A, floor 12…"
                                    />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="vv-apt2026-field-group mb-0">
                                    <label class="vv-apt2026-label">Apartment standard</label>
                                    <div class="vv-apt2026-standard-pills">
                                        <label v-for="s in qualityStandards" :key="s.value">
                                            <input v-model="form.quality_standard" type="radio" :value="s.value" />
                                            <span>
                                                <strong>{{ s.label }}</strong>
                                                <small>{{ s.sublabel }}</small>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="vv-apt2026-field-group">
                            <div class="vv-apt2026-label-row">
                                <label class="vv-apt2026-label mb-0">Short description</label>
                                <span class="vv-apt2026-label-note">max 300 characters</span>
                            </div>
                            <textarea
                                v-model="form.about_this_short"
                                class="vv-apt2026-field"
                                rows="5"
                                maxlength="300"
                                placeholder="Describe the apartment for guests…"
                            />
                            <div class="vv-apt2026-char-count">{{ form.about_this_short.length }} / 300</div>
                        </div>
                    </div>

                    <!-- Step 2: Photos -->
                    <div v-show="tab === 1">
                        <div class="vv-apt2026-photo-toolbar">
                            <div class="vv-apt2026-photo-tabs">
                                <button
                                    type="button"
                                    class="nav-link"
                                    :class="{ active: photoTab === 'upload' }"
                                    @click="photoTab = 'upload'"
                                >
                                    Add images
                                </button>
                                <button
                                    v-if="form.building_id"
                                    type="button"
                                    class="nav-link"
                                    :class="{ active: photoTab === 'building' }"
                                    @click="photoTab = 'building'"
                                >
                                    Select from building images
                                </button>
                            </div>
                            <span class="vv-apt2026-photo-counter">{{ listingPhotoCount }} / {{ publishMinPhotos }}</span>
                        </div>

                        <div v-show="photoTab === 'upload'">
                            <input
                                ref="fileInput"
                                type="file"
                                accept="image/jpeg,image/png"
                                multiple
                                class="d-none"
                                @change="onFilesPicked"
                            />

                            <div
                                class="vv-apt2026-dropzone"
                                :class="{ 'is-dragover': dragOver }"
                                @click="fileInput?.click()"
                                @dragover.prevent="dragOver = true"
                                @dragleave.prevent="dragOver = false"
                                @drop.prevent="onDrop"
                            >
                                <p class="vv-apt2026-dropzone__icon mb-2">📷</p>
                                <p class="mb-1"><strong>Drag and drop images here</strong></p>
                                <p class="mb-2 text-muted small">or</p>
                                <button type="button" class="vv-apt2026-btn-pick" @click.stop="fileInput?.click()">
                                    Select images from computer
                                </button>
                                <p class="mt-3 mb-0 small text-muted">
                                    Minimum {{ publishMinPhotos }} images · JPG or PNG · Max 10 MB per image
                                </p>
                                <p class="mb-0 small text-muted vv-apt2026-dropzone-tip">
                                    💡 The first image you upload becomes the cover image automatically — you can change this later.
                                </p>
                            </div>

                            <div v-if="uploadPhotos.length" class="vv-apt2026-images-grid vv-apt2026-images-grid--5col mt-3">
                                <div
                                    v-for="(photo, index) in uploadPhotos"
                                    :key="photo.uid"
                                    class="vv-apt2026-image-card"
                                    :class="{
                                        'is-cover': index === 0,
                                        'is-selected': selectedPhotoIndex === index,
                                    }"
                                    @click="selectedPhotoIndex = index"
                                >
                                    <button type="button" class="vv-apt2026-image-card__menu" @click.stop>⋯</button>
                                    <button
                                        type="button"
                                        class="vv-apt2026-image-card__remove"
                                        aria-label="Remove"
                                        @click.stop="removeUploadPhoto(index)"
                                    >
                                        ×
                                    </button>
                                    <div class="vv-apt2026-image-card__thumb">
                                        <img :src="photo.preview" alt="" />
                                    </div>
                                    <span v-if="index === 0" class="vv-apt2026-image-card__cover">★ Cover photo</span>
                                    <input
                                        v-model="photo.caption"
                                        type="text"
                                        class="vv-apt2026-field vv-apt2026-field--caption"
                                        placeholder="Click to name…"
                                        @click.stop
                                    />
                                    <div class="vv-apt2026-image-card__actions">
                                        <button type="button" :disabled="index === 0" @click.stop="movePhoto(index, -1)">←</button>
                                        <button
                                            type="button"
                                            :disabled="index === uploadPhotos.length - 1"
                                            @click.stop="movePhoto(index, 1)"
                                        >
                                            →
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-show="photoTab === 'building'">
                            <p class="vv-apt2026-hint">
                                Select up to {{ maxBuildingGallery }} images from the building gallery. Selected images
                                shown first. Remove a selected image to select another.
                            </p>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>{{ selectedBuilding?.name ?? 'Building gallery' }}</strong>
                                <span class="badge badge-warning">
                                    {{ buildingGallerySelected.length }} / {{ maxBuildingGallery }} selected
                                </span>
                            </div>
                            <div v-if="!buildingGalleryItems.length" class="vv-apt2026-hint">
                                No building gallery images registered for this building yet.
                            </div>
                            <div v-else class="vv-apt2026-building-grid">
                                <label
                                    v-for="item in buildingGalleryItems"
                                    :key="item.id"
                                    :class="{ 'is-selected': buildingGallerySelected.includes(item.id) }"
                                    @click.prevent="toggleBuildingGallery(item.id)"
                                >
                                    <div class="vv-apt2026-image-card__thumb">
                                        <span>{{ item.label || `#${item.id}` }}</span>
                                    </div>
                                    <small>{{ item.label || `Image #${item.id}` }}</small>
                                </label>
                            </div>
                        </div>

                        <div v-if="listingPhotoCount >= publishMinPhotos" class="vv-apt2026-upload-success mt-3">
                            ✓ {{ listingPhotoCount }} images uploaded — ready to proceed!
                        </div>
                        <p v-else class="alert alert-warning mt-3 mb-0 small">
                            {{ listingPhotoCount }} of {{ publishMinPhotos }} listing photos — apartment uploads and
                            building photos both count. Full amount required before publishing.
                        </p>
                        <p v-if="unnamedPhotoCount" class="vv-apt2026-hint mt-2">
                            ⚠ {{ unnamedPhotoCount }} image{{ unnamedPhotoCount === 1 ? '' : 's' }} missing names — you
                            can name them now or later. You can proceed.
                        </p>
                    </div>

                    <!-- Step 3: Facilities -->
                    <div v-show="tab === 2">
                        <div class="vv-apt2026-facility-toolbar">
                            <div class="vv-apt2026-facility-tabs">
                                <button
                                    type="button"
                                    class="nav-link"
                                    :class="{ active: facilityTab === 'apartment' }"
                                    @click="facilityTab = 'apartment'"
                                >
                                    Apartment facilities
                                </button>
                                <button
                                    type="button"
                                    class="nav-link"
                                    :class="{ active: facilityTab === 'building' }"
                                    @click="facilityTab = 'building'"
                                >
                                    Building amenities
                                </button>
                            </div>
                            <span v-if="facilityTab === 'apartment'" class="vv-apt2026-facility-counter">
                                {{ selectedApartmentFacilityCount }} / {{ selectableApartmentFacilities.length }} facilities
                            </span>
                            <span v-else class="vv-apt2026-facility-counter">
                                {{ confirmedBuildingFacilityCount }} / {{ buildingLevelFacilities.length }} confirmed
                            </span>
                        </div>

                        <div v-show="facilityTab === 'apartment'">
                            <h6 class="vv-apt2026-section-title">Apartment facilities</h6>
                            <p class="vv-apt2026-hint mb-3">
                                Check the amenities available in your specific apartment. You can add your own beyond
                                what is pre-filled.
                            </p>
                            <div class="vv-apt2026-facility-grid vv-apt2026-facility-grid--4col">
                                <label
                                    v-for="f in selectableApartmentFacilities"
                                    :key="f.facility_id"
                                    class="vv-apt2026-facility-item vv-apt2026-facility-item--apt"
                                    :class="{ 'is-checked': form.facilities.includes(f.facility_id) }"
                                >
                                    <input v-model="form.facilities" type="checkbox" :value="f.facility_id" />
                                    <span class="vv-apt2026-facility-box" aria-hidden="true" />
                                    <span class="vv-apt2026-facility-icon">{{ facilityIcon(f.name) }}</span>
                                    <span class="vv-apt2026-facility-label">{{ f.name }}</span>
                                </label>
                            </div>
                        </div>

                        <div v-show="facilityTab === 'building'">
                            <template v-if="!form.building_id">
                                <p class="vv-apt2026-hint">Select a building in step 1 first.</p>
                            </template>
                            <template v-else>
                                <h6 class="vv-apt2026-section-title">Building amenities</h6>
                                <p class="vv-apt2026-hint mb-3">
                                    These are pre-registered by Vietstays and apply to all of
                                    {{ selectedBuilding?.name }}. They are already included in your apartment listing
                                    and shown to guests.
                                </p>
                                <div class="vv-apt2026-facility-grid vv-apt2026-facility-grid--4col vv-apt2026-facility-grid--building">
                                    <div
                                        v-for="f in buildingLevelFacilities"
                                        :key="f.facility_id"
                                        class="vv-apt2026-facility-item vv-apt2026-facility-item--building"
                                        :class="buildingFacilityClass(f.facility_id)"
                                    >
                                        <span class="vv-apt2026-facility-box" aria-hidden="true">
                                            {{ isBuildingFacilityConfirmed(f.facility_id) ? '✓' : '' }}
                                        </span>
                                        <span class="vv-apt2026-facility-icon">{{ facilityIcon(f.name) }}</span>
                                        <span class="vv-apt2026-facility-label">{{ f.name }}</span>
                                    </div>
                                </div>
                                <div class="vv-apt2026-info-box mt-3">
                                    <span class="vv-apt2026-info-box__icon">🔒</span>
                                    <p>
                                        Green amenities are confirmed by Vietstays. Grey amenities are not yet confirmed
                                        for this building. Contact Vietstays if anything is missing or incorrect.
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Step 4: Confirm -->
                    <div v-show="tab === 3">
                        <p class="vv-apt2026-preview-intro">
                            Preview your listing. Price and practical details are configured on the next page after
                            creation.
                        </p>

                        <div class="vv-apt2026-preview-card">
                            <div class="vv-apt2026-preview-card__layout">
                                <div class="vv-apt2026-preview-card__media">
                                    <div
                                        class="vv-apt2026-preview-card__hero"
                                        :class="{ 'is-empty': !coverPreview }"
                                        :style="coverPreview ? { backgroundImage: `url(${coverPreview})` } : undefined"
                                    >
                                        <span v-if="!coverPreview">🖼</span>
                                        <span v-if="coverPreview" class="vv-apt2026-preview-card__cover-badge">
                                            ★ Cover photo
                                        </span>
                                    </div>
                                    <div v-if="previewThumbs.length" class="vv-apt2026-preview-card__thumbs">
                                        <div
                                            v-for="(thumb, idx) in previewThumbs"
                                            :key="idx"
                                            class="vv-apt2026-preview-card__thumb"
                                            :style="{ backgroundImage: `url(${thumb})` }"
                                        />
                                    </div>
                                </div>
                                <div class="vv-apt2026-preview-card__body">
                                    <h3 class="vv-apt2026-preview-card__title">{{ namePreview || 'Apartment name' }}</h3>
                                    <p v-if="buildingLocationHint" class="vv-apt2026-preview-card__location">
                                        {{ buildingLocationHint }}
                                    </p>
                                    <div class="vv-apt2026-preview-card__price">
                                        <strong>VND — / night</strong>
                                        <small>Configured after creation</small>
                                    </div>
                                    <div v-if="previewFacilityTags.length" class="vv-apt2026-preview-tags">
                                        <span v-for="f in previewFacilityTags" :key="f.facility_id">
                                            {{ facilityIcon(f.name) }} {{ f.name }}
                                        </span>
                                    </div>
                                    <p v-if="listingPhotoCount > 4" class="vv-apt2026-preview-card__more-photos">
                                        + {{ listingPhotoCount - 4 }} more images
                                    </p>
                                    <div class="vv-apt2026-preview-card__rules">
                                        House rules and practical information filled in after creation.
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.about_this_short.trim()" class="vv-apt2026-preview-card__about">
                                <h6>About the apartment</h6>
                                <p>{{ form.about_this_short }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <footer class="vv-apt2026-footer" :class="{ 'vv-apt2026-footer--first': tab === 0 }">
                    <div class="vv-apt2026-footer-left">
                        <button v-if="tab > 0" type="button" class="vv-apt2026-btn-back" @click="prevTab">
                            ← {{ steps[tab - 1].label }}
                        </button>
                    </div>
                    <button type="button" class="vv-apt2026-cancel" @click="goCancel">Cancel</button>
                    <div class="vv-apt2026-footer-right">
                        <p v-if="footerHint" class="vv-apt2026-footer-hint">{{ footerHint }}</p>
                        <button
                            v-if="tab < steps.length - 1"
                            type="button"
                            class="vv-apt2026-btn-next"
                            :disabled="!stepValid"
                            @click="nextTab"
                        >
                            Next: {{ steps[tab + 1].label }} →
                        </button>
                        <button
                            v-else
                            type="button"
                            class="vv-apt2026-btn-next is-success"
                            :disabled="saving"
                            @click="save"
                        >
                            {{ saving ? 'Creating…' : '✓ Create apartment and go to price management' }}
                        </button>
                    </div>
                </footer>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import apiClient, { uploadFiles } from '@/api/client';
import { useToast } from '@/composables/useToast';
import { facilityIcon } from '@/utils/facilityIcons';
import {
    APARTMENT_TYPES,
    QUALITY_STANDARDS,
    suggestDailyPrice,
} from '@/utils/pricing';

const router = useRouter();
const toast = useToast();

const wizardMinPhotos = 1;
const publishMinPhotos = 10;
const maxBuildingGallery = 3;

const steps = [
    { id: 0, num: 1, label: 'Building & type' },
    { id: 1, num: 2, label: 'Photos' },
    { id: 2, num: 3, label: 'Facilities' },
    { id: 3, num: 4, label: 'Confirm' },
];

const tab = ref(0);
const maxTab = ref(0);
const saving = ref(false);
const error = ref('');
const facilityTab = ref('apartment');
const photoTab = ref('upload');
const showBuildingRequest = ref(false);
const dragOver = ref(false);
const selectedPhotoIndex = ref(null);
const fileInput = ref(null);

const cities = ref([]);
const districts = ref([]);
const buildings = ref([]);
const allFacilities = ref([]);

const uploadPhotos = ref([]);
const buildingGallerySelected = ref([]);

const buildingRequest = reactive({
    name: '',
    address: '',
    notes: '',
});

const apartmentTypes = APARTMENT_TYPES;
const qualityStandards = QUALITY_STANDARDS;

const form = reactive({
    city_id: '',
    district_id: '',
    building_id: '',
    apartment_type: '',
    quality_standard: 'above_average',
    distinguishing_feature: '',
    room_number: '',
    facilities: [],
    about_this_short: '',
});

const selectedBuilding = ref(null);

const buildingLevelFacilities = computed(() =>
    allFacilities.value.filter((f) => f.type === 'location'),
);

const buildingFacilityIds = computed(() => {
    const ids = selectedBuilding.value?.facilities;
    return Array.isArray(ids) ? ids.map(Number) : [];
});

const selectableApartmentFacilities = computed(() =>
    allFacilities.value.filter((f) => {
        if (f.type !== 'location') {
            return true;
        }
        return !buildingFacilityIds.value.includes(Number(f.facility_id));
    }),
);

const selectedApartmentFacilityCount = computed(() =>
    form.facilities.filter((id) =>
        selectableApartmentFacilities.value.some((f) => f.facility_id === id),
    ).length,
);

const confirmedBuildingFacilityCount = computed(() =>
    buildingLevelFacilities.value.filter((f) =>
        buildingFacilityIds.value.includes(Number(f.facility_id)),
    ).length,
);

const buildingGalleryItems = computed(() => {
    const gallery = selectedBuilding.value?.building_gallery;
    return Array.isArray(gallery) ? gallery.filter((item) => item?.id) : [];
});

const listingPhotoCount = computed(
    () => uploadPhotos.value.length + buildingGallerySelected.value.length,
);

const unnamedPhotoCount = computed(
    () => uploadPhotos.value.filter((p) => !p.caption.trim()).length,
);

const suggestedPrice = computed(() =>
    suggestDailyPrice(form.apartment_type, form.quality_standard),
);

const namePreview = computed(() => {
    const building = buildings.value.find((b) => b.id === Number(form.building_id))?.name;
    const district = districts.value.find((d) => d.district_id === Number(form.district_id));
    const parts = [building, form.distinguishing_feature].filter(Boolean);
    let name = parts.join('–');
    if (district?.name) {
        name += (name ? ' · ' : '') + district.name;
    }
    if (form.apartment_type) {
        name += (name ? ' · ' : '') + form.apartment_type;
    }
    return name.length > 80 ? name.slice(0, 80) : name;
});

const buildingLocationHint = computed(() => {
    if (!selectedBuilding.value) return '';
    const district = districts.value.find((d) => d.district_id === Number(form.district_id));
    const city = cities.value.find((c) => c.city_id === Number(form.city_id));
    return [selectedBuilding.value.name, district?.name, city?.name].filter(Boolean).join(', ');
});

const coverPreview = computed(() => uploadPhotos.value[0]?.preview ?? null);

const previewThumbs = computed(() =>
    uploadPhotos.value.slice(1, 4).map((p) => p.preview),
);

const previewFacilityTags = computed(() =>
    allFacilities.value
        .filter((f) => form.facilities.includes(f.facility_id))
        .slice(0, 6),
);

const stepValid = computed(() => {
    if (tab.value === 0) {
        return (
            form.district_id &&
            form.building_id &&
            form.apartment_type &&
            form.distinguishing_feature.trim() &&
            namePreview.value
        );
    }
    if (tab.value === 1) {
        return listingPhotoCount.value >= wizardMinPhotos;
    }
    return true;
});

const footerHint = computed(() => {
    if (tab.value === 0 && !stepValid.value) {
        return 'Fill in required fields to proceed.';
    }
    if (tab.value === 1 && listingPhotoCount.value < wizardMinPhotos) {
        return 'Upload at least 1 image to proceed.';
    }
    return '';
});

watch(
    () => form.building_id,
    (id) => {
        selectedBuilding.value = id ? buildings.value.find((b) => b.id === Number(id)) ?? null : null;
        buildingGallerySelected.value = [];
    },
);

onMounted(() => {
    loadInitialData();
});

function goCancel() {
    router.push({ name: 'apartments' });
}

function stepTabClass(index) {
    if (tab.value === index) return 'is-active';
    if (index < maxTab.value || index < tab.value) return 'is-done';
    if (index > maxTab.value) return 'is-future';
    return '';
}

function isBuildingFacilityConfirmed(facilityId) {
    return buildingFacilityIds.value.includes(Number(facilityId));
}

function buildingFacilityClass(facilityId) {
    return isBuildingFacilityConfirmed(facilityId) ? 'is-confirmed' : 'is-unconfirmed';
}

function toggleBuildingGallery(id) {
    const numericId = Number(id);
    const idx = buildingGallerySelected.value.indexOf(numericId);
    if (idx >= 0) {
        buildingGallerySelected.value.splice(idx, 1);
        return;
    }
    if (buildingGallerySelected.value.length >= maxBuildingGallery) {
        toast.show(`Maximum ${maxBuildingGallery} building gallery images.`);
        return;
    }
    buildingGallerySelected.value.push(numericId);
}

async function loadInitialData() {
    error.value = '';
    try {
        const [citiesRes, facilitiesRes] = await Promise.all([
            apiClient.get('/locations/cities'),
            apiClient.get('/facilities'),
        ]);
        cities.value = citiesRes?.data ?? [];
        allFacilities.value = facilitiesRes?.data ?? [];
    } catch (err) {
        error.value = err.payload?.message ?? err.message ?? 'Could not load form data.';
    }
}

async function onCityChange() {
    form.district_id = '';
    form.building_id = '';
    districts.value = [];
    buildings.value = [];
    selectedBuilding.value = null;
    if (!form.city_id) return;
    const res = await apiClient.get(`/locations/districts?city_id=${form.city_id}`);
    districts.value = res?.data ?? [];
}

async function onDistrictChange() {
    form.building_id = '';
    buildings.value = [];
    selectedBuilding.value = null;
    if (!form.district_id) return;
    const res = await apiClient.get(`/locations/buildings?district_id=${form.district_id}`);
    buildings.value = res?.data ?? [];
}

function onBuildingChange() {
    selectedBuilding.value = buildings.value.find((b) => b.id === Number(form.building_id)) ?? null;
}

function addPhotoFiles(files) {
    Array.from(files).forEach((file) => {
        uploadPhotos.value.push({
            uid: `${Date.now()}-${Math.random().toString(36).slice(2)}`,
            file,
            preview: URL.createObjectURL(file),
            caption: '',
        });
    });
}

function onFilesPicked(event) {
    addPhotoFiles(event.target.files ?? []);
    event.target.value = '';
}

function onDrop(event) {
    dragOver.value = false;
    addPhotoFiles(event.dataTransfer?.files ?? []);
}

function removeUploadPhoto(index) {
    URL.revokeObjectURL(uploadPhotos.value[index].preview);
    uploadPhotos.value.splice(index, 1);
    if (selectedPhotoIndex.value === index) {
        selectedPhotoIndex.value = null;
    }
}

function movePhoto(index, direction) {
    const target = index + direction;
    if (target < 0 || target >= uploadPhotos.value.length) return;
    const items = [...uploadPhotos.value];
    [items[index], items[target]] = [items[target], items[index]];
    uploadPhotos.value = items;
    selectedPhotoIndex.value = target;
}

function submitBuildingRequest() {
    if (!buildingRequest.name.trim()) {
        toast.show('Please enter a building name.');
        return;
    }
    toast.show('Building request submitted for review.');
    Object.assign(buildingRequest, { name: '', address: '', notes: '' });
    showBuildingRequest.value = false;
}

function goToTab(index) {
    if (index <= maxTab.value) {
        tab.value = index;
    }
}

function nextTab() {
    if (!stepValid.value) return;
    maxTab.value = Math.max(maxTab.value, tab.value + 1);
    tab.value += 1;
}

function prevTab() {
    tab.value = Math.max(0, tab.value - 1);
}

function resetForm() {
    tab.value = 0;
    maxTab.value = 0;
    error.value = '';
    saving.value = false;
    facilityTab.value = 'apartment';
    photoTab.value = 'upload';
    selectedPhotoIndex.value = null;
    buildingGallerySelected.value = [];
    uploadPhotos.value.forEach((p) => URL.revokeObjectURL(p.preview));
    uploadPhotos.value = [];
    Object.assign(buildingRequest, { name: '', address: '', notes: '' });
    Object.assign(form, {
        city_id: '',
        district_id: '',
        building_id: '',
        apartment_type: '',
        quality_standard: 'above_average',
        distinguishing_feature: '',
        room_number: '',
        facilities: [],
        about_this_short: '',
    });
}

async function save() {
    saving.value = true;
    error.value = '';

    try {
        const payload = {
            building_id: Number(form.building_id),
            district_id: Number(form.district_id),
            apartment_type: form.apartment_type,
            quality_standard: form.quality_standard,
            distinguishing_feature: form.distinguishing_feature,
            room_number: form.room_number || null,
            about_this_short: form.about_this_short,
            facilities: form.facilities,
            price_daily: suggestedPrice.value,
            status: 'draft',
            building_gallery_json: buildingGallerySelected.value.length
                ? buildingGallerySelected.value
                : null,
        };

        const res = await apiClient.post('/apartments', payload);
        const apartment = res?.data;

        if (!apartment?.id) {
            throw new Error('Apartment was saved but the server did not return an ID.');
        }

        if (uploadPhotos.value.length) {
            try {
                for (const photo of uploadPhotos.value) {
                    const fd = new FormData();
                    fd.append('photos[]', photo.file);
                    await uploadFiles(`/apartments/${apartment.id}/photos`, fd);
                }
            } catch (photoErr) {
                toast.show(
                    photoErr.message ?? 'Apartment created, but some photos could not be uploaded.',
                );
                router.push({ name: 'apartment-detail', params: { id: apartment.id } });
                return;
            }
        }

        toast.show('Apartment created.');
        router.push({ name: 'apartment-detail', params: { id: apartment.id } });
    } catch (err) {
        error.value =
            err.payload?.message ??
            (typeof err.payload?.errors === 'object'
                ? Object.values(err.payload.errors).flat().join(' ')
                : null) ??
            err.message ??
            'Could not create apartment.';
    } finally {
        saving.value = false;
    }
}
</script>

<style scoped>
.d-none {
    display: none;
}

.d-flex {
    display: flex;
}

.justify-content-between {
    justify-content: space-between;
}

.align-items-center {
    align-items: center;
}

.mb-0 { margin-bottom: 0; }
.mb-1 { margin-bottom: 0.25rem; }
.mb-2 { margin-bottom: 0.5rem; }
.mb-3 { margin-bottom: 1rem; }
.mt-2 { margin-top: 0.5rem; }
.mt-3 { margin-top: 1rem; }

.text-muted {
    color: var(--vv2026-muted);
}

.small {
    font-size: 12px;
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #f0d78c;
}

.alert {
    padding: 10px 14px;
    border-radius: 4px;
}

.alert-warning {
    background: #fff3cd;
    border: 1px solid #f0d78c;
    color: #856404;
}
</style>
