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

                    <!-- Guest arrival (door code, Wi-Fi, cleaning) -->
                    <ApartmentGuestInfoTab v-if="activeTab === 'arrival'" :apartment-id="apartmentId" />

                    <!-- Price & terms -->
                    <div
                        v-show="activeTab === 'price'"
                        class="host-apt-detail__section host-apt-form-section"
                        data-checklist="price"
                    >
                        <h2 class="host-apt-detail__section-title">Details &amp; price</h2>

                        <div class="host-price-card">
                            <label class="host-field__label" for="price-daily">Nightly rate</label>
                            <div class="host-price-card__rate">
                                <input
                                    id="price-daily"
                                    v-model.number="editForm.price_daily"
                                    type="number"
                                    min="0"
                                    step="10000"
                                    class="host-price-card__input"
                                    :class="{ 'host-price-card__input--struck': activeSeasonPrice }"
                                />
                                <span>VND / night</span>
                            </div>
                            <p v-if="activeSeasonPrice" class="host-price-card__season" :style="{ color: activeSeasonPrice.color }">
                                {{ activeSeasonPrice.label }}
                                <span>{{ activeSeasonPrice.sub }}</span>
                            </p>
                            <p class="host-suggested-price host-suggested-price--inline">
                                <span class="host-suggested-price__label">
                                    Suggested for {{ apartment.type || 'this type' }} · {{ standardLabel }}
                                </span>
                                <span class="host-suggested-price__value">{{ formatVnd(suggestedPrice) }}</span>
                            </p>

                            <div class="host-price-card__controls">
                                <button
                                    type="button"
                                    class="host-price-toggle"
                                    :class="{ 'host-price-toggle--fixed': editForm.pricing_model === 'fixed' }"
                                    @click="editForm.pricing_model = 'fixed'"
                                >
                                    Fixed price
                                </button>
                                <button
                                    type="button"
                                    class="host-price-toggle"
                                    :class="{ 'host-price-toggle--season': editForm.pricing_model === 'seasonal' }"
                                    @click="editForm.pricing_model = 'seasonal'"
                                >
                                    Seasonal
                                </button>
                                <span class="host-price-card__divider"></span>
                                <label class="host-price-card__min" for="min-nights">Min. stay</label>
                                <select id="min-nights" v-model.number="editForm.min_nights" class="host-select host-price-card__select">
                                    <option :value="1">1 night</option>
                                    <option :value="2">2 nights</option>
                                    <option :value="3">3 nights</option>
                                    <option :value="5">5 nights</option>
                                    <option :value="7">7 nights</option>
                                </select>
                            </div>

                            <div v-if="editForm.pricing_model === 'seasonal'" class="host-season-chart">
                                <button
                                    v-for="bar in seasonalBars"
                                    :key="bar.id"
                                    type="button"
                                    class="host-season-chart__month"
                                    @click="monthEdit = monthEdit === bar.id ? null : bar.id"
                                >
                                    <span class="host-season-chart__price">{{ bar.priceLabel }}</span>
                                    <span
                                        class="host-season-chart__bar"
                                        :style="{ height: bar.barHeight, background: bar.barColor }"
                                    ></span>
                                    <span class="host-season-chart__label" :class="{ 'host-season-chart__label--now': bar.current }">
                                        {{ bar.label }}
                                    </span>
                                </button>
                            </div>
                            <div v-if="editingMonth" class="host-season-edit">
                                <span>{{ editingMonth.label }} adjustment</span>
                                <input
                                    v-model.number="editForm.seasonalPct[editingMonth.id]"
                                    type="number"
                                    min="-90"
                                    max="300"
                                    step="1"
                                    class="host-input host-season-edit__input"
                                />
                                <span>%</span>
                            </div>
                        </div>

                        <div class="host-price-card">
                            <h3 class="host-price-card__title">Long-stay discount</h3>
                            <div class="host-longstay__head">
                                <span>Threshold</span>
                                <span>Rate</span>
                                <span>Suggested</span>
                                <span>Discounted</span>
                                <span>Saving</span>
                            </div>
                            <div v-for="row in longStayPreview" :key="row.nights" class="host-longstay__row">
                                <button type="button" class="host-longstay__toggle" @click="editForm.longStay[row.nights] = !editForm.longStay[row.nights]">
                                    <span class="host-switch" :class="{ 'host-switch--on': row.on }"></span>
                                    <span>{{ row.nights }} nights</span>
                                </button>
                                <input
                                    v-model.number="editForm[row.key]"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="1"
                                    class="host-input host-longstay__rate"
                                    :disabled="!row.on"
                                />
                                <span class="host-longstay__muted">{{ row.gross }}</span>
                                <strong>{{ row.net }}</strong>
                                <span class="host-longstay__save">−{{ row.save }}</span>
                            </div>
                        </div>

                        <div class="host-price-card">
                            <h3 class="host-price-card__title">Cleaning fee</h3>
                            <p class="host-field-hint">Added to the guest total.</p>
                            <div class="host-form-grid">
                                <div class="host-field">
                                    <label class="host-field__label" for="cleaning-fee">Cleaning fee (VND)</label>
                                    <input
                                        id="cleaning-fee"
                                        v-model.number="editForm.cleaning_fee"
                                        type="number"
                                        min="0"
                                        step="10000"
                                        class="host-input"
                                        :disabled="!editForm.cleaning_fee_enabled"
                                    />
                                </div>
                                <div class="host-field">
                                    <label class="host-field__label" for="extra-cleaning">Extra cleaning fee (VND)</label>
                                    <input
                                        id="extra-cleaning"
                                        v-model.number="editForm.extra_cleaning_fee"
                                        type="number"
                                        min="0"
                                        step="10000"
                                        class="host-input"
                                    />
                                </div>
                            </div>
                            <label class="host-apt-pricing__check">
                                <input v-model="editForm.cleaning_fee_enabled" type="checkbox" />
                                Charge the cleaning fee on each booking
                            </label>
                        </div>

                        <div class="host-price-card">
                            <h3 class="host-price-card__title">Weekend add-on &amp; campaigns</h3>
                            <p class="host-field-hint">
                                Friday and Saturday use the add-on. A campaign replaces the long-stay discount on overlapping nights.
                            </p>
                            <div class="host-form-grid">
                                <div class="host-field">
                                    <label class="host-field__label" for="addon-weekend">Friday &amp; Saturday add-on (%)</label>
                                    <input
                                        id="addon-weekend"
                                        v-model.number="editForm.addon_days2"
                                        type="number"
                                        min="0"
                                        max="500"
                                        step="1"
                                        class="host-input"
                                    />
                                </div>
                                <div class="host-field">
                                    <label class="host-field__label" for="promo-discount">Promo code discount (%)</label>
                                    <input
                                        id="promo-discount"
                                        v-model.number="editForm.promocode_discount"
                                        type="number"
                                        min="0"
                                        max="100"
                                        step="1"
                                        class="host-input"
                                    />
                                </div>
                            </div>
                            <div v-for="(row, index) in editForm.campaigns" :key="index" class="host-apt-pricing__campaign">
                                <div class="host-field">
                                    <label class="host-field__label">Start</label>
                                    <input v-model="row.start" type="date" class="host-input" />
                                </div>
                                <div class="host-field">
                                    <label class="host-field__label">End</label>
                                    <input v-model="row.end" type="date" class="host-input" />
                                </div>
                                <div class="host-field">
                                    <label class="host-field__label">Discount %</label>
                                    <input v-model.number="row.discount" type="number" min="0" max="100" step="1" class="host-input" />
                                </div>
                                <button type="button" class="host-btn host-btn--ghost" @click="editForm.campaigns.splice(index, 1)">
                                    Remove
                                </button>
                            </div>
                            <button type="button" class="host-btn host-btn--ghost" @click="addCampaign">Add campaign</button>
                        </div>
                    </div>

                    <!-- Presentation -->
                    <div v-show="activeTab === 'presentation'" class="host-pres">
                        <div>
                            <h2 class="host-pres__title">Presentation</h2>
                            <p class="host-pres__lead">
                                This is how the guest sees the apartment. Same steps as registration — jump freely between them.
                            </p>
                        </div>

                        <div class="host-pres__steps">
                            <template v-for="(step, index) in presSteps" :key="step.id">
                                <button type="button" class="host-pres__step" @click="presStep = step.id">
                                    <span
                                        class="host-pres__dot"
                                        :class="{
                                            'host-pres__dot--active': presStep === step.id,
                                            'host-pres__dot--done': presStep !== step.id && step.done,
                                        }"
                                    ></span>
                                    <span
                                        class="host-pres__step-label"
                                        :class="{ 'host-pres__step-label--on': presStep === step.id || step.done }"
                                    >
                                        {{ step.label }}
                                    </span>
                                </button>
                                <span
                                    v-if="index < presSteps.length - 1"
                                    class="host-pres__line"
                                    :class="{ 'host-pres__line--done': step.done }"
                                ></span>
                            </template>
                        </div>

                        <div v-if="presStep === 'location'" class="host-pres__card">
                            <div class="host-pres__split">
                                <div>
                                    <label class="host-pres__label" for="pres-district">District</label>
                                    <select id="pres-district" v-model="editForm.district_id" class="host-pres__control" @change="onDistrictChange">
                                        <option value="">Select district</option>
                                        <option v-for="district in districts" :key="district.district_id" :value="district.district_id">
                                            {{ district.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="host-pres__label" for="pres-building">Building</label>
                                    <select id="pres-building" v-model="editForm.building_id" class="host-pres__control">
                                        <option value="">Select building</option>
                                        <option v-for="building in buildings" :key="building.id" :value="building.id">
                                            {{ building.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="host-pres__label" for="pres-address">Address</label>
                                <input id="pres-address" v-model="editForm.address" class="host-pres__control" placeholder="Street, number, district" />
                            </div>
                            <div class="host-pres__split">
                                <div>
                                    <label class="host-pres__label" for="pres-floor">Floor</label>
                                    <input id="pres-floor" v-model="editForm.floor_number" class="host-pres__control" placeholder="e.g. 18" />
                                </div>
                                <div>
                                    <label class="host-pres__label" for="pres-unit">Unit ID</label>
                                    <input id="pres-unit" v-model="editForm.room_number" class="host-pres__control" placeholder="e.g. 18.05" />
                                </div>
                            </div>
                            <p class="host-pres__note">
                                Guests see the district and building. The full address is shared only after a confirmed booking.
                            </p>
                        </div>

                        <div v-else-if="presStep === 'type'" class="host-pres__card">
                            <div>
                                <div class="host-pres__label">Apartment type</div>
                                <div class="host-pres__choices">
                                    <button
                                        v-for="type in apartmentTypes"
                                        :key="type"
                                        type="button"
                                        class="host-pres__choice"
                                        :class="{ 'host-pres__choice--on': editForm.apartment_type === type }"
                                        @click="editForm.apartment_type = type"
                                    >
                                        {{ type }}
                                    </button>
                                </div>
                            </div>
                            <div>
                                <div class="host-pres__label">Apartment standard</div>
                                <div class="host-pres__choices host-pres__choices--short">
                                    <button
                                        v-for="standard in qualityStandards"
                                        :key="standard.value"
                                        type="button"
                                        class="host-pres__choice"
                                        :class="{ 'host-pres__choice--on': editForm.quality_standard === standard.value }"
                                        @click="editForm.quality_standard = standard.value"
                                    >
                                        {{ standard.label }}
                                    </button>
                                </div>
                            </div>
                            <div class="host-pres__split host-pres__split--feature">
                                <div>
                                    <label class="host-pres__label" for="pres-feature">Distinctive feature</label>
                                    <input
                                        id="pres-feature"
                                        v-model="editForm.distinguishing_feature"
                                        maxlength="20"
                                        class="host-pres__control"
                                        placeholder="e.g. City View & Pool"
                                    />
                                </div>
                                <div>
                                    <label class="host-pres__label" for="pres-name">Apartment name</label>
                                    <input id="pres-name" v-model="editForm.display_name" class="host-pres__control host-pres__control--strong" />
                                </div>
                            </div>
                        </div>

                        <div v-else-if="presStep === 'pictures'" class="host-pres__card" data-checklist="photos">
                            <div class="host-pres__card-head">
                                <div class="host-pres__label">Pictures</div>
                                <span class="host-pres__count" :class="{ 'host-pres__count--ok': validImages.length >= 3 }">
                                    {{ validImages.length }} photos
                                </span>
                            </div>
                            <p class="host-pres__tip">
                                Drag a photo to swap places. The first photo is the cover photo the guest sees first.
                            </p>
                            <div v-if="photoUploading" class="host-field-hint">Uploading photos…</div>
                            <div class="host-pres__photos">
                                <div
                                    v-for="(img, idx) in validImages"
                                    :key="`${img.image_id || img.thumb}-${idx}`"
                                    class="host-pres__photo"
                                    draggable="true"
                                    @dragstart="onPhotoDragStart(idx, $event)"
                                    @dragover.prevent
                                    @drop.prevent="onPhotoCardDrop(idx)"
                                >
                                    <div
                                        class="host-pres__thumb"
                                        :style="{ backgroundImage: `url(${resolveApartmentImageUrl(img.thumb || img.url)})` }"
                                    >
                                        <span v-if="idx === 0" class="host-pres__cover">★ Cover photo</span>
                                        <button type="button" class="host-pres__remove" aria-label="Remove photo" @click.stop="removePhoto(idx)">
                                            ✕
                                        </button>
                                    </div>
                                    <select
                                        class="host-pres__photo-type"
                                        :class="{ 'host-pres__photo-type--set': img.caption }"
                                        :value="img.caption || ''"
                                        @change="setPhotoCaption(idx, $event.target.value)"
                                    >
                                        <option value="">Choose photo type</option>
                                        <option v-if="img.caption && !photoTypeOptions.includes(img.caption)" :value="img.caption">
                                            {{ img.caption }}
                                        </option>
                                        <option v-for="name in photoTypeOptions" :key="name" :value="name">{{ name }}</option>
                                    </select>
                                </div>
                                <button
                                    type="button"
                                    class="host-pres__upload"
                                    :class="{ 'host-pres__upload--active': dragOver }"
                                    @click="fileInput?.click()"
                                    @dragover.prevent="dragOver = true"
                                    @dragleave.prevent="dragOver = false"
                                    @drop.prevent="onDropzonePhotoDrop"
                                >
                                    <span>+</span>
                                    <span>Upload</span>
                                </button>
                            </div>
                            <input
                                ref="fileInput"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                multiple
                                class="host-pres__file"
                                @change="onPhotoSelect"
                            />
                        </div>

                        <div v-else-if="presStep === 'facilities'" class="host-pres__stack" data-checklist="facilities">
                            <div class="host-pres__card">
                                <div class="host-pres__card-head">
                                    <div class="host-pres__label">Apartment facilities</div>
                                    <span class="host-pres__count host-pres__count--apt">{{ selectedApartmentFacilityCount }} selected</span>
                                </div>
                                <div class="host-pres__facilities">
                                    <button
                                        v-for="facility in selectableApartmentFacilities"
                                        :key="facility.facility_id"
                                        type="button"
                                        class="host-pres__facility"
                                        :class="{ 'host-pres__facility--apt': editForm.facilities.includes(facility.facility_id) }"
                                        @click="toggleFacility(facility.facility_id)"
                                    >
                                        <span
                                            class="host-pres__check"
                                            :class="{ 'host-pres__check--apt': editForm.facilities.includes(facility.facility_id) }"
                                        >
                                            <span v-if="editForm.facilities.includes(facility.facility_id)">✓</span>
                                        </span>
                                        {{ facility.name }}
                                    </button>
                                </div>
                            </div>
                            <div class="host-pres__card">
                                <div class="host-pres__card-head">
                                    <div class="host-pres__label">Building facilities</div>
                                    <span class="host-pres__count host-pres__count--building">{{ confirmedBuildingFacilityCount }} selected</span>
                                </div>
                                <div class="host-pres__facilities">
                                    <div
                                        v-for="facility in buildingLevelFacilities"
                                        :key="facility.facility_id"
                                        class="host-pres__facility"
                                        :class="{ 'host-pres__facility--building': buildingFacilityIds.includes(Number(facility.facility_id)) }"
                                    >
                                        <span
                                            class="host-pres__check"
                                            :class="{ 'host-pres__check--building': buildingFacilityIds.includes(Number(facility.facility_id)) }"
                                        >
                                            <span v-if="buildingFacilityIds.includes(Number(facility.facility_id))">✓</span>
                                        </span>
                                        {{ facility.name }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="host-pres__card" data-checklist="short">
                            <div>
                                <div class="host-pres__label-row">
                                    <label class="host-pres__label" for="pres-short">Short description</label>
                                    <span>Max 300 characters</span>
                                </div>
                                <textarea
                                    id="pres-short"
                                    v-model="editForm.about_this_short"
                                    maxlength="300"
                                    class="host-pres__area"
                                    placeholder="Describe the apartment briefly — the view, the location, what makes it special."
                                ></textarea>
                            </div>
                            <div data-checklist="desc">
                                <label class="host-pres__label" for="pres-rules">House rules</label>
                                <textarea
                                    id="pres-rules"
                                    v-model="editForm.house_rules"
                                    class="host-pres__area host-pres__area--rules"
                                    :class="{ 'host-pres__area--missing': !editForm.house_rules.trim() }"
                                    placeholder="For example: no smoking indoors, no parties, quiet after 10:00 pm."
                                ></textarea>
                                <p v-if="!editForm.house_rules.trim()" class="host-pres__tip host-pres__tip--gap">
                                    House rules are missing — this is the last piece of a complete listing.
                                </p>
                            </div>
                            <div>
                                <label class="host-pres__label" for="pres-practical">Practical info for the guest</label>
                                <textarea
                                    id="pres-practical"
                                    v-model="editForm.description"
                                    class="host-pres__area host-pres__area--short"
                                    placeholder="Check-in, key pickup, wifi name, trash disposal…"
                                ></textarea>
                            </div>
                        </div>

                        <div class="host-pres__nav">
                            <button v-if="presStepIndex > 0" type="button" class="host-pres__prev" @click="presStep = presSteps[presStepIndex - 1].id">
                                ← {{ presSteps[presStepIndex - 1].label }}
                            </button>
                            <span class="host-pres__nav-spacer"></span>
                            <button
                                v-if="presStepIndex < presSteps.length - 1"
                                type="button"
                                class="host-pres__next"
                                @click="presStep = presSteps[presStepIndex + 1].id"
                            >
                                {{ presSteps[presStepIndex + 1].label }} →
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    v-show="activeTab !== 'arrival'"
                    class="host-apt-sticky-bar host-apt-sticky-bar--always"
                    :class="{ 'host-apt-sticky-bar--dimmed': !dirty }"
                >
                    <div class="host-apt-sticky-bar__left">
                        <router-link :to="{ name: 'apartments' }" class="host-apt-sticky-bar__back">
                            ← All apartments
                        </router-link>
                        <span class="host-apt-sticky-bar__status">
                            {{
                                dirty
                                    ? `${pendingChangeCount} change${pendingChangeCount === 1 ? '' : 's'} pending — click Save to apply`
                                    : 'All saved'
                            }}
                        </span>
                    </div>
                    <div class="host-apt-sticky-bar__actions">
                        <button
                            v-if="dirty"
                            type="button"
                            class="host-btn host-btn--ghost host-apt-sticky-bar__reset"
                            @click="resetChanges"
                        >
                            Reset
                        </button>
                        <button
                            type="button"
                            class="host-btn host-btn--sand"
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
import ApartmentGuestInfoTab from '@/components/apartments/ApartmentGuestInfoTab.vue';
import { usePageTitle } from '@/composables/usePageTitle';
import { useToast } from '@/composables/useToast';
import {
    isValidApartmentImage,
    resolveApartmentImageUrl,
    stripHtml,
} from '@/utils/apartment-images';
import { formatVnd } from '@/utils/format';
import { APARTMENT_TYPES, QUALITY_STANDARDS } from '@/utils/pricing';

const route = useRoute();
const router = useRouter();
const toast = useToast();
const { setPageTitle, clearPageTitle } = usePageTitle();

const loading = ref(true);
const saving = ref(false);
const photoUploading = ref(false);
const dragOver = ref(false);
const activeTab = ref('presentation');
const fileInput = ref(null);
const dragFromIndex = ref(null);
const scrollEl = ref(null);
const imagesDirty = ref(false);
const liveStatus = ref({ state: 'vacant', label: 'Vacant — no active booking' });

const tabs = [
    { id: 'availability', label: 'Availability' },
    { id: 'price', label: 'Price & terms' },
    { id: 'presentation', label: 'Presentation' },
    { id: 'arrival', label: 'Guest arrival' },
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

const months = [
    { id: 1, label: 'Jan' },
    { id: 2, label: 'Feb' },
    { id: 3, label: 'Mar' },
    { id: 4, label: 'Apr' },
    { id: 5, label: 'May' },
    { id: 6, label: 'Jun' },
    { id: 7, label: 'Jul' },
    { id: 8, label: 'Aug' },
    { id: 9, label: 'Sep' },
    { id: 10, label: 'Oct' },
    { id: 11, label: 'Nov' },
    { id: 12, label: 'Dec' },
];

const longStayTiers = [
    { nights: 3, key: 'discount_3days' },
    { nights: 5, key: 'discount_5days' },
    { nights: 7, key: 'discount_7days' },
    { nights: 14, key: 'discount_14days' },
    { nights: 30, key: 'discount_30days' },
];

function emptySeasonalPct() {
    return Object.fromEntries(months.map((month) => [month.id, 0]));
}

function emptyLongStay() {
    return Object.fromEntries(longStayTiers.map((tier) => [tier.nights, false]));
}

function emptyPricingForm() {
    return {
        price_daily: 0,
        cleaning_fee: 0,
        extra_cleaning_fee: 0,
        cleaning_fee_enabled: true,
        promocode_discount: 0,
        pricing_model: 'fixed',
        min_nights: 1,
        addon_days2: 0,
        discount_3days: 0,
        discount_5days: 0,
        discount_7days: 0,
        discount_14days: 0,
        discount_30days: 0,
        longStay: emptyLongStay(),
        seasonalPct: emptySeasonalPct(),
        campaigns: [],
        about_this_short: '',
        description: '',
        distinguishing_feature: '',
        display_name: '',
        district_id: '',
        building_id: '',
        address: '',
        floor_number: '',
        room_number: '',
        apartment_type: '',
        quality_standard: 'standard',
        house_rules: '',
        facilities: [],
    };
}

const editForm = reactive(emptyPricingForm());
const originalForm = reactive(emptyPricingForm());

const originalImagesJson = ref('[]');

const standardLabel = computed(() => {
    const match = QUALITY_STANDARDS.find((s) => s.value === apartment.value.standard);
    return match?.label ?? apartment.value.standard ?? 'Standard';
});

const suggestedPrice = ref(0);
const monthEdit = ref(null);
const currentMonth = new Date().getMonth() + 1;
const presStep = ref('pictures');
const districts = ref([]);
const buildings = ref([]);
const allFacilities = ref([]);
const apartmentTypes = APARTMENT_TYPES;
const qualityStandards = QUALITY_STANDARDS;

const presStepDefs = [
    { id: 'location', label: 'Location' },
    { id: 'type', label: 'Type' },
    { id: 'pictures', label: 'Pictures' },
    { id: 'facilities', label: 'Facilities' },
    { id: 'rules', label: 'House rules & info' },
];

const validImages = computed(() =>
    (apartment.value.images ?? []).filter(isValidApartmentImage),
);

const selectedBuilding = computed(
    () => buildings.value.find((building) => Number(building.id) === Number(editForm.building_id)) ?? null,
);

const buildingLevelFacilities = computed(() => allFacilities.value.filter((facility) => facility.type === 'location'));

const buildingFacilityIds = computed(() => {
    const ids = selectedBuilding.value?.facilities;
    return Array.isArray(ids) ? ids.map(Number) : [];
});

const selectableApartmentFacilities = computed(() =>
    allFacilities.value.filter((facility) => {
        if (facility.type !== 'location') {
            return true;
        }
        return !buildingFacilityIds.value.includes(Number(facility.facility_id));
    }),
);

const selectedApartmentFacilityCount = computed(
    () => editForm.facilities.filter((id) => selectableApartmentFacilities.value.some((facility) => facility.facility_id === id)).length,
);

const confirmedBuildingFacilityCount = computed(
    () => buildingLevelFacilities.value.filter((facility) => buildingFacilityIds.value.includes(Number(facility.facility_id))).length,
);

const photoTypeOptions = computed(() => {
    const type = editForm.apartment_type || '2BR';
    const bedrooms =
        type === 'Studio' ? [] : type === '1BR' ? ['Bedroom'] : Array.from({ length: Number.parseInt(type, 10) || 1 }, (_, index) => `Bedroom ${index + 1}`);
    return ['Living room', ...bedrooms, 'Kitchen', 'Bath', 'Toilet', 'Balcony', 'Entrance', 'Laundry', 'Dining room', 'Facade', 'View', 'Pool', 'Lobby'];
});

const presSteps = computed(() =>
    presStepDefs.map((step) => ({
        ...step,
        done:
            (step.id === 'location' && editForm.district_id && editForm.building_id) ||
            (step.id === 'type' && editForm.apartment_type && editForm.quality_standard) ||
            (step.id === 'pictures' && validImages.value.length >= 3) ||
            (step.id === 'facilities' && selectedApartmentFacilityCount.value > 0 && confirmedBuildingFacilityCount.value > 0) ||
            (step.id === 'rules' && editForm.house_rules.trim() && stripHtml(editForm.description)),
    })),
);

const presStepIndex = computed(() => Math.max(0, presSteps.value.findIndex((step) => step.id === presStep.value)));

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

function pricingSnapshot(form) {
    return JSON.stringify({
        price_daily: Number(form.price_daily) || 0,
        cleaning_fee: Number(form.cleaning_fee) || 0,
        extra_cleaning_fee: Number(form.extra_cleaning_fee) || 0,
        cleaning_fee_enabled: Boolean(form.cleaning_fee_enabled),
        promocode_discount: Number(form.promocode_discount) || 0,
        pricing_model: form.pricing_model || 'fixed',
        min_nights: Number(form.min_nights) || 1,
        addon_days2: Number(form.addon_days2) || 0,
        discount_3days: Number(form.discount_3days) || 0,
        discount_5days: Number(form.discount_5days) || 0,
        discount_7days: Number(form.discount_7days) || 0,
        discount_14days: Number(form.discount_14days) || 0,
        discount_30days: Number(form.discount_30days) || 0,
        longStay: form.longStay,
        seasonalPct: form.seasonalPct,
        campaigns: form.campaigns,
        display_name: form.display_name || '',
        district_id: Number(form.district_id) || 0,
        building_id: Number(form.building_id) || 0,
        address: form.address || '',
        floor_number: form.floor_number || '',
        room_number: form.room_number || '',
        apartment_type: form.apartment_type || '',
        quality_standard: form.quality_standard || '',
        house_rules: form.house_rules || '',
        facilities: [...(form.facilities || [])].map(Number).sort((a, b) => a - b),
        about_this_short: form.about_this_short || '',
        description: form.description || '',
        distinguishing_feature: form.distinguishing_feature || '',
    });
}

const pricingDirty = computed(() => pricingSnapshot(editForm) !== pricingSnapshot(originalForm));

const dirty = computed(() => pricingDirty.value || imagesDirty.value);

const pendingChangeCount = computed(() => {
    let count = 0;
    if (pricingDirty.value) count += 1;
    if (imagesDirty.value) count += 1;
    return count;
});

function compactPrice(amount) {
    const value = Math.round(Number(amount) || 0);
    if (Math.abs(value) >= 1000000) {
        const millions = value / 1000000;
        return `${Number.isInteger(millions) ? millions : millions.toFixed(1)}m`;
    }
    if (Math.abs(value) >= 1000) {
        return `${Math.round(value / 1000)}k`;
    }
    return String(value);
}

const seasonalBars = computed(() => {
    const nightly = Number(editForm.price_daily) || 0;
    const percents = months.map((month) => Number(editForm.seasonalPct[month.id]) || 0);
    const maxAbs = Math.max(20, ...percents.map((percent) => Math.abs(percent)));

    return months.map((month, index) => {
        const pct = percents[index];
        const price = Math.round(nightly * (1 + pct / 100));
        return {
            ...month,
            pct,
            priceLabel: compactPrice(price),
            barHeight: `${Math.round(30 + (Math.abs(pct) / maxAbs) * 50)}px`,
            barColor: pct > 0 ? '#7a9d4f' : pct < 0 ? '#e0793a' : '#ddd5bd',
            current: month.id === currentMonth,
        };
    });
});

const editingMonth = computed(() => months.find((month) => month.id === monthEdit.value) ?? null);

const activeSeasonPrice = computed(() => {
    if (editForm.pricing_model !== 'seasonal') {
        return null;
    }
    const pct = Number(editForm.seasonalPct[currentMonth]) || 0;
    if (!pct) {
        return null;
    }
    const nightly = Number(editForm.price_daily) || 0;
    return {
        label: formatVnd(Math.round(nightly * (1 + pct / 100))),
        sub: pct < 0 ? 'Low-season rate' : 'High-season rate',
        color: pct < 0 ? '#d33a3a' : '#1f7a44',
    };
});

const longStayPreview = computed(() => {
    const nightly = Number(editForm.price_daily) || 0;
    return longStayTiers.map((tier) => {
        const on = Boolean(editForm.longStay[tier.nights]);
        const pct = on ? Number(editForm[tier.key]) || 0 : 0;
        const gross = nightly * tier.nights;
        const net = Math.round(gross * (1 - pct / 100));
        return {
            ...tier,
            on,
            gross: formatVnd(gross),
            net: formatVnd(net),
            save: formatVnd(Math.max(0, gross - net)),
        };
    });
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
        done: (editForm.facilities?.length ?? 0) > 0,
        tab: 'presentation',
        anchor: 'facilities',
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

function copyPricingForm(target, source) {
    target.price_daily = source.price_daily;
    target.cleaning_fee = source.cleaning_fee;
    target.extra_cleaning_fee = source.extra_cleaning_fee;
    target.cleaning_fee_enabled = source.cleaning_fee_enabled;
    target.promocode_discount = source.promocode_discount;
    target.pricing_model = source.pricing_model;
    target.min_nights = source.min_nights;
    target.addon_days2 = source.addon_days2;
    target.discount_3days = source.discount_3days;
    target.discount_5days = source.discount_5days;
    target.discount_7days = source.discount_7days;
    target.discount_14days = source.discount_14days;
    target.discount_30days = source.discount_30days;
    target.longStay = { ...source.longStay };
    target.seasonalPct = { ...source.seasonalPct };
    target.campaigns = source.campaigns.map((row) => ({ ...row }));
    target.about_this_short = source.about_this_short;
    target.description = source.description;
    target.distinguishing_feature = source.distinguishing_feature;
    target.display_name = source.display_name;
    target.district_id = source.district_id;
    target.building_id = source.building_id;
    target.address = source.address;
    target.floor_number = source.floor_number;
    target.room_number = source.room_number;
    target.apartment_type = source.apartment_type;
    target.quality_standard = source.quality_standard;
    target.house_rules = source.house_rules;
    target.facilities = [...source.facilities];
}

function applyFormFromApartment(data) {
    const pricing = data.pricing && typeof data.pricing === 'object' ? data.pricing : {};
    const seasonal = data.seasonal_pricing && typeof data.seasonal_pricing === 'object' ? data.seasonal_pricing : {};
    const next = emptyPricingForm();

    next.price_daily = data.price_daily ?? 0;
    next.cleaning_fee = data.cleaning_fee ?? 0;
    next.extra_cleaning_fee = data.extra_cleaning_fee ?? 0;
    next.cleaning_fee_enabled = data.cleaning_fee_enabled !== false;
    next.promocode_discount = data.promocode_discount ?? 0;
    next.pricing_model = data.pricing_model === 'seasonal' ? 'seasonal' : 'fixed';
    next.min_nights = [1, 2, 3, 5, 7].includes(Number(pricing.min_nights)) ? Number(pricing.min_nights) : 1;
    next.addon_days2 = Number(pricing.addon_days2) || 0;
    next.discount_3days = Number(pricing.discount_3days) || 0;
    next.discount_5days = Number(pricing.discount_5days) || 0;
    next.discount_7days = Number(pricing.discount_7days) || 0;
    next.discount_14days = Number(pricing.discount_14days) || 0;
    next.discount_30days = Number(pricing.discount_30days) || 0;
    const storedStay = pricing.long_stay && typeof pricing.long_stay === 'object' ? pricing.long_stay : null;
    longStayTiers.forEach((tier) => {
        if (storedStay) {
            const flag = storedStay[tier.nights] ?? storedStay[String(tier.nights)];
            next.longStay[tier.nights] = flag === true || flag === 1 || flag === '1';
        } else {
            next.longStay[tier.nights] = Number(pricing[tier.key]) > 0;
        }
    });
    const storedPct = pricing.seasonal_pct && typeof pricing.seasonal_pct === 'object' ? pricing.seasonal_pct : null;
    const base = Number(data.price_daily) || 0;
    months.forEach((month) => {
        if (storedPct && (storedPct[month.id] !== undefined || storedPct[String(month.id)] !== undefined)) {
            next.seasonalPct[month.id] = Number(storedPct[month.id] ?? storedPct[String(month.id)]) || 0;
            return;
        }
        const absolute = seasonal[month.id] ?? seasonal[String(month.id)];
        if (base > 0 && absolute !== undefined && absolute !== null && absolute !== '') {
            next.seasonalPct[month.id] = Math.round((Number(absolute) / base - 1) * 100);
        }
    });
    next.campaigns = (data.campaign_discounts ?? []).map((row) => ({
        start: row.start ?? '',
        end: row.end ?? '',
        discount: Number(row.discount) || 0,
    }));
    next.about_this_short = data.about_this_short ?? '';
    next.description = data.description ?? '';
    next.distinguishing_feature = data.distinguishing_feature ?? '';
    next.display_name = data.name ?? '';
    next.district_id = data.district_id || '';
    next.building_id = data.building_id || '';
    next.address = data.address ?? '';
    next.floor_number = data.floor_number ?? '';
    next.room_number = data.room_number ?? '';
    next.apartment_type = data.type ?? '';
    next.quality_standard = data.standard ?? 'standard';
    next.house_rules = data.house_rules ?? '';
    next.facilities = (data.facilities ?? []).map(Number);

    copyPricingForm(editForm, next);
    copyPricingForm(originalForm, next);
}

function addCampaign() {
    editForm.campaigns.push({ start: '', end: '', discount: 0 });
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
    const stepByAnchor = { photos: 'pictures', short: 'rules', desc: 'rules', facilities: 'facilities' };
    if (stepByAnchor[item.anchor]) {
        presStep.value = stepByAnchor[item.anchor];
    }
}

function replaceValidImages(nextImages) {
    apartment.value.images = nextImages;
    markImagesDirty();
}

function removePhoto(idx) {
    const next = [...validImages.value];
    next.splice(idx, 1);
    replaceValidImages(next);
}

function setPhotoCaption(idx, caption) {
    const next = validImages.value.map((img, index) => (index === idx ? { ...img, caption } : img));
    replaceValidImages(next);
}

function toggleFacility(facilityId) {
    const id = Number(facilityId);
    if (editForm.facilities.includes(id)) {
        editForm.facilities = editForm.facilities.filter((value) => value !== id);
        return;
    }
    editForm.facilities = [...editForm.facilities, id];
}

async function loadDistricts(cityId) {
    const query = cityId ? `?city_id=${cityId}` : '';
        const res = await apiClient.get(`/locations/districts${query}`);
        districts.value = (res?.data ?? []).map((district) => ({
            ...district,
            district_id: Number(district.district_id),
        }));
}

async function loadBuildings() {
    if (!editForm.district_id) {
        buildings.value = [];
        return;
    }
    const res = await apiClient.get(`/locations/buildings?district_id=${editForm.district_id}`);
    buildings.value = (res?.data ?? []).map((building) => ({
        ...building,
        id: Number(building.id),
    }));
}

async function onDistrictChange() {
    editForm.building_id = '';
    await loadBuildings();
}

async function loadPresentationOptions(cityId) {
    try {
        const facilitiesRes = await apiClient.get('/facilities');
        allFacilities.value = (facilitiesRes?.data ?? []).map((facility) => ({
            ...facility,
            facility_id: Number(facility.facility_id),
        }));
        await loadDistricts(cityId);
        await loadBuildings();
    } catch {
        districts.value = [];
        buildings.value = [];
        allFacilities.value = [];
    }
}

function setCoverPhoto(idx) {
    if (idx <= 0) {
        return;
    }

    const next = [...validImages.value];
    const [moved] = next.splice(idx, 1);
    next.unshift(moved);
    replaceValidImages(next);
}

function reorderPhotos(fromIdx, toIdx) {
    if (fromIdx === toIdx) {
        return;
    }

    const next = [...validImages.value];
    const [moved] = next.splice(fromIdx, 1);
    next.splice(toIdx, 0, moved);
    replaceValidImages(next);
}

function onPhotoDragStart(idx, event) {
    dragFromIndex.value = idx;
    event.dataTransfer.effectAllowed = 'move';
}

function onPhotoDragOver(event) {
    event.dataTransfer.dropEffect = 'move';
}

function onPhotoCardDrop(targetIdx) {
    const fromIdx = dragFromIndex.value;

    if (fromIdx === null) {
        return;
    }

    reorderPhotos(fromIdx, targetIdx);
    dragFromIndex.value = null;
}

function resetChanges() {
    copyPricingForm(editForm, originalForm);
    apartment.value.images = JSON.parse(originalImagesJson.value || '[]');
    imagesDirty.value = false;
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

function onDropzonePhotoDrop(event) {
    dragOver.value = false;
    uploadPhotoFiles(event.dataTransfer?.files);
}

async function loadApartment() {
    loading.value = true;

    try {
        const res = await apiClient.get(`/apartments/${apartmentId.value}`);
        applyApartmentData(res?.data ?? {});
        await loadPresentationOptions(res?.data?.city_id);
        loadSuggestedPrice();
    } catch {
        toast.show('Could not load apartment.');
    } finally {
        loading.value = false;
    }
}

async function loadSuggestedPrice() {
    try {
        const res = await apiClient.get(`/apartments/${apartmentId.value}/suggested-price`);
        suggestedPrice.value = res?.data?.suggested_price ?? 0;
    } catch {
        suggestedPrice.value = 0;
    }
}

async function save() {
    saving.value = true;

    try {
        const nightly = Number(editForm.price_daily) || 0;
        const seasonalPct = {};
        const seasonal = {};
        months.forEach((month) => {
            const pct = Number(editForm.seasonalPct[month.id]) || 0;
            seasonalPct[month.id] = pct;
            if (pct !== 0) {
                seasonal[month.id] = Math.max(0, Math.round(nightly * (1 + pct / 100)));
            }
        });

        const payload = {
            price_daily: nightly,
            cleaning_fee: Number(editForm.cleaning_fee) || 0,
            extra_cleaning_fee: Number(editForm.extra_cleaning_fee) || 0,
            cleaning_fee_enabled: Boolean(editForm.cleaning_fee_enabled),
            promocode_discount: Number(editForm.promocode_discount) || 0,
            pricing_model: editForm.pricing_model,
            pricing: {
                min_nights: Number(editForm.min_nights) || 1,
                addon_days2: Number(editForm.addon_days2) || 0,
                discount_3days: Number(editForm.discount_3days) || 0,
                discount_5days: Number(editForm.discount_5days) || 0,
                discount_7days: Number(editForm.discount_7days) || 0,
                discount_14days: Number(editForm.discount_14days) || 0,
                discount_30days: Number(editForm.discount_30days) || 0,
                long_stay: { ...editForm.longStay },
                seasonal_pct: seasonalPct,
            },
            seasonal_pricing: seasonal,
            campaign_discounts: editForm.campaigns
                .filter((row) => row.start && row.end && Number(row.discount) > 0)
                .map((row) => ({
                    start: row.start,
                    end: row.end,
                    discount: Number(row.discount) || 0,
                })),
            about_this_short: editForm.about_this_short ?? '',
            description: editForm.description ?? '',
            distinguishing_feature: editForm.distinguishing_feature ?? '',
            display_name: editForm.display_name ?? '',
            name: editForm.display_name ?? '',
            address: editForm.address ?? '',
            floor_number: editForm.floor_number ?? '',
            room_number: editForm.room_number ?? '',
            house_rules: editForm.house_rules ?? '',
            facilities: editForm.facilities.map(Number),
        };

        if (editForm.district_id) {
            payload.district_id = Number(editForm.district_id);
        }
        if (editForm.building_id) {
            payload.building_id = Number(editForm.building_id);
        }
        if (editForm.apartment_type) {
            payload.apartment_type = editForm.apartment_type;
        }
        if (editForm.quality_standard) {
            payload.quality_standard = editForm.quality_standard;
        }

        if (imagesDirty.value) {
            payload.images = (apartment.value.images ?? [])
                .filter(isValidApartmentImage)
                .map((img) => ({
                    order: img.order ?? 0,
                    thumb: img.thumb || img.url || '',
                    image_id: img.image_id ?? '',
                    caption: img.caption ?? '',
                    ...(img.url ? { url: img.url } : {}),
                }));
        }

        const res = await apiClient.put(`/apartments/${apartmentId.value}`, payload);
        applyApartmentData({ ...apartment.value, ...(res?.data ?? {}) });
        await loadBuildings();
        toast.show('Apartment saved.');
    } catch (err) {
        toast.show(err.payload?.detail ?? err.message ?? 'Could not save apartment.');
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    loadApartment();
});

onUnmounted(() => {
    clearPageTitle();
});

watch(apartmentId, loadApartment);
</script>

<style scoped>
.host-price-card {
    border: 1px solid #eee6d0;
    border-radius: 14px;
    background: #fff;
    padding: 22px 24px;
    margin-bottom: 16px;
}

.host-price-card__rate {
    display: flex;
    align-items: baseline;
    gap: 10px;
    margin-top: 8px;
}

.host-price-card__input {
    width: 280px;
    max-width: 100%;
    border: 0;
    padding: 0;
    background: transparent;
    font-size: 38px;
    font-weight: 700;
    color: #1c2b23;
    line-height: 1.1;
}

.host-price-card__input--struck {
    text-decoration: line-through;
    color: #8a9187;
}

.host-price-card__rate span,
.host-price-card__season span {
    font-size: 15px;
    font-weight: 600;
    color: #8a9187;
}

.host-price-card__season {
    margin: 4px 0 0;
    font-size: 26px;
    font-weight: 700;
}

.host-suggested-price--inline {
    margin-top: 8px;
}

.host-price-card__controls {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 20px;
}

.host-price-toggle {
    padding: 10px 20px;
    border-radius: 8px;
    border: 1px solid #ddd5bd;
    background: #fff;
    color: #8a9187;
    font-size: 13.5px;
    font-weight: 700;
    cursor: pointer;
}

.host-price-toggle--fixed {
    background: #e0793a;
    border-color: #e0793a;
    color: #fff;
}

.host-price-toggle--season {
    background: #12352b;
    border-color: #12352b;
    color: #fff;
}

.host-price-card__divider {
    width: 1px;
    height: 20px;
    background: #eee6d0;
}

.host-price-card__min {
    font-size: 13px;
    color: #8a9187;
}

.host-price-card__select {
    width: auto;
    min-width: 120px;
}

.host-price-card__title {
    margin: 0 0 14px;
    font-size: 15px;
    font-weight: 700;
    color: #1c2b23;
}

.host-season-chart {
    display: flex;
    align-items: flex-end;
    gap: 6px;
    margin-top: 24px;
    height: 110px;
}

.host-season-chart__month {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
    height: 100%;
    padding: 0;
    border: 0;
    background: transparent;
    cursor: pointer;
}

.host-season-chart__price {
    font-size: 10px;
    font-weight: 700;
    color: #8a9187;
}

.host-season-chart__bar {
    width: 100%;
    border-radius: 8px;
}

.host-season-chart__label {
    font-size: 11px;
    color: #9a9484;
}

.host-season-chart__label--now {
    color: #12352b;
    font-weight: 700;
}

.host-season-edit {
    margin-top: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f7f2e6;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 13px;
    color: #5e6b62;
    font-weight: 600;
}

.host-season-edit__input {
    width: 90px;
}

.host-longstay__head,
.host-longstay__row {
    display: grid;
    grid-template-columns: 160px 90px minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr);
    gap: 12px;
    align-items: center;
}

.host-longstay__head {
    margin-bottom: 8px;
    font-size: 12px;
    font-weight: 700;
    color: #8a9187;
}

.host-longstay__row {
    padding: 6px 0;
    font-size: 13px;
}

.host-longstay__toggle {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0;
    border: 0;
    background: transparent;
    font-size: 13.5px;
    font-weight: 600;
    color: #1c2b23;
    cursor: pointer;
}

.host-switch {
    width: 34px;
    height: 19px;
    border-radius: 10px;
    background: #ddd5bd;
    position: relative;
    flex: none;
}

.host-switch::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background: #fff;
}

.host-switch--on {
    background: #12352b;
}

.host-switch--on::after {
    left: 17px;
}

.host-longstay__rate {
    width: 70px;
    padding: 6px 8px;
}

.host-longstay__muted {
    color: #8a9187;
}

.host-longstay__save {
    color: #1f7a44;
    font-weight: 600;
}

.host-apt-pricing__campaign {
    display: grid;
    grid-template-columns: 1fr 1fr 140px auto;
    gap: 12px;
    align-items: end;
    margin-bottom: 12px;
}

.host-apt-pricing__check {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 12px;
    font-size: 14px;
}

@media (max-width: 900px) {
    .host-longstay__head {
        display: none;
    }

    .host-longstay__row,
    .host-apt-pricing__campaign {
        grid-template-columns: 1fr 1fr;
    }
}

.host-pres {
    max-width: 940px;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.host-pres__title {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #1c2b23;
}

.host-pres__lead {
    margin: 4px 0 0;
    font-size: 13.5px;
    color: #8a9187;
}

.host-pres__steps {
    display: flex;
    align-items: flex-start;
    padding: 2px 4px 4px;
}

.host-pres__step {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: none;
    padding: 0;
    border: 0;
    background: transparent;
    cursor: pointer;
}

.host-pres__dot {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    box-sizing: border-box;
    background: #fff;
    border: 3px solid #ddd5bd;
}

.host-pres__dot--active {
    background: #fff;
    border: 7px solid #e0793a;
}

.host-pres__dot--done {
    background: #12352b;
    border: 3px solid #12352b;
}

.host-pres__step-label {
    margin-top: 9px;
    font-size: 14px;
    font-weight: 700;
    color: #9a9484;
    white-space: nowrap;
}

.host-pres__step-label--on {
    color: #12352b;
}

.host-pres__line {
    flex: 1;
    height: 3px;
    margin-top: 13px;
    min-width: 20px;
    background: #e5decb;
}

.host-pres__line--done {
    background: #12352b;
}

.host-pres__card {
    border: 1px solid #eee6d0;
    border-radius: 14px;
    background: #fff;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.host-pres__stack {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.host-pres__split {
    display: flex;
    gap: 20px;
}

.host-pres__split > div {
    flex: 1;
    min-width: 0;
}

.host-pres__split--feature > div:last-child {
    flex: 2;
}

.host-pres__label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 700;
    color: #8a9187;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.host-pres__label-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 12px;
}

.host-pres__label-row span {
    font-size: 12.5px;
    color: #8a9187;
}

.host-pres__control,
.host-pres__area {
    width: 100%;
    padding: 14px 16px;
    border-radius: 10px;
    border: 1px solid #ddd5bd;
    font-size: 16px;
    color: #1c2b23;
    background: #fff;
    box-sizing: border-box;
    font-family: inherit;
}

.host-pres__control--strong {
    font-weight: 600;
}

.host-pres__area {
    min-height: 130px;
    resize: vertical;
    font-size: 15px;
}

.host-pres__area--rules {
    min-height: 110px;
}

.host-pres__area--short {
    min-height: 90px;
}

.host-pres__area--missing {
    border-color: #e0a458;
}

.host-pres__note {
    margin: 0;
    font-size: 12.5px;
    color: #5e6b62;
    background: #f7f2e6;
    border-radius: 8px;
    padding: 10px 14px;
}

.host-pres__choices {
    display: flex;
    gap: 8px;
}

.host-pres__choices--short {
    max-width: 520px;
}

.host-pres__choice {
    flex: 1;
    text-align: center;
    padding: 15px 8px;
    border-radius: 10px;
    border: 1px solid #ddd5bd;
    background: #fff;
    color: #8a9187;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
}

.host-pres__choice--on {
    background: #f7f2e6;
    border-color: #e0793a;
    color: #1c2b23;
}

.host-pres__card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.host-pres__card-head .host-pres__label {
    margin: 0;
}

.host-pres__count {
    padding: 6px 14px;
    border-radius: 20px;
    background: #fbf3e0;
    border: 1px solid #f0dfa9;
    color: #8a6a3d;
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
}

.host-pres__count--ok {
    background: #e5f3ea;
    border-color: #9fd6b2;
    color: #1f7a44;
}

.host-pres__count--apt {
    background: #fdf1e6;
    border-color: #e0793a;
    color: #b5651d;
}

.host-pres__count--building {
    background: #e5f3ea;
    border-color: #9fd6b2;
    color: #1f7a44;
}

.host-pres__tip {
    margin: 0;
    font-size: 12.5px;
    color: #8a6a3d;
    background: #fbf3e0;
    border: 1px solid #f0dfa9;
    border-radius: 8px;
    padding: 8px 12px;
}

.host-pres__tip--gap {
    margin-top: 8px;
}

.host-pres__photos {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 8px;
}

.host-pres__photo {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}

.host-pres__thumb {
    position: relative;
    aspect-ratio: 1;
    border-radius: 8px;
    overflow: hidden;
    background: #eee6d0 center / cover no-repeat;
    cursor: grab;
}

.host-pres__cover {
    position: absolute;
    bottom: 5px;
    left: 5px;
    right: 5px;
    background: #e0793a;
    color: #fff;
    font-size: 9.5px;
    font-weight: 700;
    text-align: center;
    padding: 3px 4px;
    border-radius: 5px;
}

.host-pres__remove {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 18px;
    height: 18px;
    border: 0;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.55);
    color: #fff;
    font-size: 11px;
    line-height: 18px;
    cursor: pointer;
    z-index: 2;
    padding: 0;
}

.host-pres__photo-type {
    width: 100%;
    padding: 6px;
    border-radius: 6px;
    border: 1px solid #ddd5bd;
    font-size: 11.5px;
    color: #1c2b23;
    background: #fff;
    box-sizing: border-box;
}

.host-pres__photo-type--set {
    border-color: #e0793a;
    background: #f7f2e6;
}

.host-pres__upload {
    aspect-ratio: 1;
    border-radius: 8px;
    border: 2px dashed #ddd5bd;
    background: transparent;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    cursor: pointer;
    color: #8a9187;
    font-size: 11px;
}

.host-pres__upload span:first-child {
    font-size: 18px;
}

.host-pres__upload--active {
    border-color: #e0793a;
    color: #1c2b23;
}

.host-pres__file {
    display: none;
}

.host-pres__facilities {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.host-pres__facility {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 8px 10px;
    border: 0;
    border-radius: 7px;
    background: transparent;
    text-align: left;
    font-size: 13.5px;
    color: #3a453d;
    cursor: pointer;
}

.host-pres__facility--apt {
    background: #fdf1e6;
    cursor: pointer;
}

.host-pres__facility--building {
    background: #e5f3ea;
    cursor: default;
}

.host-pres__check {
    width: 16px;
    height: 16px;
    border-radius: 4px;
    border: 2px solid #ddd5bd;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: none;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
}

.host-pres__check--apt {
    background: #e0793a;
    border-color: #e0793a;
}

.host-pres__check--building {
    background: #12352b;
    border-color: #12352b;
}

.host-pres__nav {
    display: flex;
    align-items: center;
    gap: 12px;
}

.host-pres__nav-spacer {
    flex: 1;
}

.host-pres__prev,
.host-pres__next {
    padding: 11px 22px;
    border-radius: 8px;
    font-size: 14.5px;
    cursor: pointer;
}

.host-pres__prev {
    background: #fff;
    border: 1px solid #ddd5bd;
    color: #3a453d;
    font-weight: 600;
}

.host-pres__next {
    padding-inline: 26px;
    background: #12352b;
    border: 0;
    color: #fff;
    font-weight: 700;
}

@media (max-width: 900px) {
    .host-pres__split,
    .host-pres__choices,
    .host-pres__facilities {
        flex-direction: column;
        display: flex;
    }

    .host-pres__photos {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .host-pres__step-label {
        font-size: 12px;
    }
}
</style>
