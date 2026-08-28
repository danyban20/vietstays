<template>
    <div class="host-app-page">
        <div v-if="submitted" class="host-app-success">
            <h1>Application submitted</h1>
            <p>Thank you for applying to become a Vietstays host.</p>
            <p v-if="submittedRef" class="host-app-success__ref">
                Your application ID: <strong>{{ submittedRef }}</strong>
            </p>
            <p class="host-app-success__note">
                Our team normally reviews applications within 2–3 business days. We will email you at
                {{ form.email }} with updates.
            </p>
            <router-link :to="{ name: 'home' }" class="public-btn public-btn--primary">Back to home</router-link>
        </div>

        <template v-else>
            <header class="host-app-page__header">
                <h1>Become a Vietstays host</h1>
                <p>List your apartment or apply as a portfolio manager. Vietstays is for furnished apartments only.</p>
            </header>

            <div v-if="errors.length" class="host-app-alert host-app-alert--error">
                <ul>
                    <li v-for="(message, index) in errors" :key="index">{{ message }}</li>
                </ul>
            </div>

            <div v-if="form.applicant_type" class="host-app-progress">
                <span
                    v-for="step in totalSteps"
                    :key="step"
                    class="host-app-progress__item"
                    :class="{
                        'host-app-progress__item--active': step === currentStep,
                        'host-app-progress__item--done': step < currentStep,
                    }"
                >
                    {{ stepLabel(step) }}
                </span>
            </div>

            <section class="host-app-card">
                <!-- Step: type -->
                <form v-if="stepKind === 'type'" @submit.prevent="nextFromType">
                    <h2>How many apartments are you listing today?</h2>
                    <p class="host-app-step-desc">Register one apartment now, or apply as a portfolio manager with multiple units.</p>
                    <div class="host-app-choice-grid">
                        <label class="host-app-choice">
                            <input v-model="form.applicant_type" type="radio" value="single_property" required />
                            <span class="host-app-choice__box">
                                <strong>One apartment</strong>
                                <small>Set up your listing — space type, location, amenities, price, and more</small>
                            </span>
                        </label>
                        <label class="host-app-choice">
                            <input v-model="form.applicant_type" type="radio" value="multi_property" required />
                            <span class="host-app-choice__box">
                                <strong>Multiple apartments</strong>
                                <small>Apply as a portfolio manager — add listings after approval</small>
                            </span>
                        </label>
                    </div>
                    <div class="host-app-actions">
                        <button type="submit" class="public-btn public-btn--primary">Continue</button>
                    </div>
                </form>

                <!-- Step: space type -->
                <form v-else-if="stepKind === 'space_type'" @submit.prevent="goNext">
                    <h2>What type of apartment will guests have?</h2>
                    <div class="host-app-choice-grid">
                        <label v-for="option in options.space_types" :key="option.value" class="host-app-choice">
                            <input v-model="form.property_space_type" type="radio" :value="option.value" required />
                            <span class="host-app-choice__box"><strong>{{ option.label }}</strong></span>
                        </label>
                    </div>
                    <div class="host-app-actions">
                        <button type="button" class="public-btn public-btn--ghost" @click="goBack">Back</button>
                        <button type="submit" class="public-btn public-btn--primary">Continue</button>
                    </div>
                </form>

                <!-- Step: location -->
                <form v-else-if="stepKind === 'location'" @submit.prevent="goNext">
                    <h2>Where is your apartment located?</h2>
                    <div class="host-app-field">
                        <label>Street address *</label>
                        <textarea v-model="form.property_address" rows="2" required placeholder="Building, street, ward" />
                    </div>
                    <div class="host-app-field-row">
                        <div class="host-app-field">
                            <label>City *</label>
                            <select v-model.number="form.property_city_id" required @change="onPropertyCityChange">
                                <option value="">Select city</option>
                                <option v-for="city in options.cities" :key="city.city_id" :value="city.city_id">
                                    {{ city.name }}
                                </option>
                            </select>
                        </div>
                        <div class="host-app-field">
                            <label>District / neighbourhood *</label>
                            <select v-model.number="form.property_district_id" required>
                                <option value="">Select district</option>
                                <option
                                    v-for="district in propertyDistricts"
                                    :key="district.district_id"
                                    :value="district.district_id"
                                >
                                    {{ district.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="host-app-actions">
                        <button type="button" class="public-btn public-btn--ghost" @click="goBack">Back</button>
                        <button type="submit" class="public-btn public-btn--primary">Continue</button>
                    </div>
                </form>

                <!-- Step: basics -->
                <form v-else-if="stepKind === 'basics'" @submit.prevent="goNext">
                    <h2>Apartment basics</h2>
                    <div class="host-app-field-row">
                        <div class="host-app-field">
                            <label>Bedrooms / beds *</label>
                            <input v-model.number="form.property_beds" type="number" min="0" required />
                        </div>
                        <div class="host-app-field">
                            <label>Bathrooms *</label>
                            <input v-model.number="form.property_bathrooms" type="number" min="0" required />
                        </div>
                    </div>
                    <div class="host-app-actions">
                        <button type="button" class="public-btn public-btn--ghost" @click="goBack">Back</button>
                        <button type="submit" class="public-btn public-btn--primary">Continue</button>
                    </div>
                </form>

                <!-- Step: amenities -->
                <form v-else-if="stepKind === 'amenities'" @submit.prevent="goNext">
                    <h2>Amenities</h2>
                    <p class="host-app-step-desc">Select everything that applies to your apartment.</p>
                    <div class="host-app-check-grid">
                        <label v-for="facility in options.facilities" :key="facility.facility_id" class="host-app-check">
                            <input v-model="form.property_amenities" type="checkbox" :value="facility.facility_id" />
                            <span>{{ facility.name }}</span>
                        </label>
                    </div>
                    <div class="host-app-actions">
                        <button type="button" class="public-btn public-btn--ghost" @click="goBack">Back</button>
                        <button type="submit" class="public-btn public-btn--primary">Continue</button>
                    </div>
                </form>

                <!-- Step: listing -->
                <form v-else-if="stepKind === 'listing'" @submit.prevent="goNext">
                    <h2>Listing details &amp; price</h2>
                    <div class="host-app-field">
                        <label>Listing title *</label>
                        <input v-model="form.property_name" type="text" required placeholder="e.g. Bright 2BR in District 1" />
                    </div>
                    <div class="host-app-field">
                        <label>Description *</label>
                        <textarea v-model="form.property_description" rows="4" required />
                    </div>
                    <div class="host-app-field">
                        <label>Nightly price (VND) *</label>
                        <input v-model.number="form.property_price_daily" type="number" min="1" required />
                    </div>
                    <div class="host-app-actions">
                        <button type="button" class="public-btn public-btn--ghost" @click="goBack">Back</button>
                        <button type="submit" class="public-btn public-btn--primary">Continue</button>
                    </div>
                </form>

                <!-- Step: contact -->
                <form v-else-if="stepKind === 'contact'" @submit.prevent="goNext">
                    <h2>About you</h2>
                    <div class="host-app-field-row">
                        <div class="host-app-field">
                            <label>Full name *</label>
                            <input v-model="form.full_name" type="text" required />
                        </div>
                        <div class="host-app-field">
                            <label>Email *</label>
                            <input v-model="form.email" type="email" required />
                        </div>
                    </div>
                    <div class="host-app-field-row">
                        <div class="host-app-field">
                            <label>Phone *</label>
                            <input v-model="form.phone" type="tel" required />
                        </div>
                        <div v-if="form.applicant_type === 'multi_property'" class="host-app-field">
                            <label>Number of apartments *</label>
                            <input v-model.number="form.num_properties" type="number" min="1" required />
                        </div>
                    </div>
                    <template v-if="form.applicant_type === 'multi_property'">
                        <div class="host-app-field-row">
                            <div class="host-app-field">
                                <label>Primary city *</label>
                                <select v-model.number="form.primary_city_id" required @change="onPrimaryCityChange">
                                    <option value="">Select city</option>
                                    <option v-for="city in options.cities" :key="city.city_id" :value="city.city_id">
                                        {{ city.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="host-app-field">
                            <label>Districts you operate in *</label>
                            <div class="host-app-check-grid host-app-check-grid--scroll">
                                <label
                                    v-for="district in primaryDistricts"
                                    :key="district.district_id"
                                    class="host-app-check"
                                >
                                    <input v-model="form.districts" type="checkbox" :value="district.district_id" />
                                    <span>{{ district.name }}</span>
                                </label>
                            </div>
                        </div>
                    </template>
                    <div class="host-app-field">
                        <label>Company / brand</label>
                        <input v-model="form.company_name" type="text" />
                    </div>
                    <div class="host-app-field">
                        <label>Portfolio link</label>
                        <input v-model="form.portfolio_url" type="url" placeholder="https://" />
                    </div>
                    <div class="host-app-field">
                        <label>Manager / portfolio description *</label>
                        <textarea v-model="form.description" rows="4" required />
                    </div>
                    <div class="host-app-actions">
                        <button type="button" class="public-btn public-btn--ghost" @click="goBack">Back</button>
                        <button type="submit" class="public-btn public-btn--primary">Continue</button>
                    </div>
                </form>

                <!-- Step: experience -->
                <form v-else-if="stepKind === 'experience'" @submit.prevent="goNext">
                    <h2>Experience &amp; fit</h2>
                    <p class="host-app-step-desc">Optional — helps our team understand your hosting background.</p>
                    <div class="host-app-field-row">
                        <div class="host-app-field">
                            <label>Years managing</label>
                            <select v-model="form.years_managing">
                                <option v-for="option in options.years_managing" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                        <div class="host-app-field">
                            <label>Typical guests</label>
                            <select v-model="form.guest_profile">
                                <option v-for="option in options.guest_profiles" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="host-app-field">
                        <label>Platforms used</label>
                        <div class="host-app-check-grid">
                            <label v-for="option in options.platforms" :key="option.value" class="host-app-check">
                                <input v-model="form.platforms_used" type="checkbox" :value="option.value" />
                                <span>{{ option.label }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="host-app-actions">
                        <button type="button" class="public-btn public-btn--ghost" @click="goBack">Back</button>
                        <button type="submit" class="public-btn public-btn--primary">Continue</button>
                    </div>
                </form>

                <!-- Step: review -->
                <form v-else-if="stepKind === 'review'" @submit.prevent="submitApplication">
                    <h2>Review &amp; submit</h2>
                    <dl class="host-app-review">
                        <div><dt>Type</dt><dd>{{ form.applicant_type === 'single_property' ? 'One apartment' : 'Multiple apartments' }}</dd></div>
                        <div><dt>Name</dt><dd>{{ form.full_name }}</dd></div>
                        <div><dt>Email</dt><dd>{{ form.email }}</dd></div>
                        <div><dt>Phone</dt><dd>{{ form.phone }}</dd></div>
                        <div v-if="form.applicant_type === 'multi_property'"><dt>Apartments</dt><dd>{{ form.num_properties }}</dd></div>
                        <div v-if="form.applicant_type === 'single_property'"><dt>Address</dt><dd>{{ form.property_address }}</dd></div>
                        <div v-if="form.applicant_type === 'single_property'"><dt>Price / night</dt><dd>{{ formatVnd(form.property_price_daily) }}</dd></div>
                    </dl>
                    <label class="host-app-check host-app-check--confirm">
                        <input v-model="form.confirm_application" type="checkbox" :true-value="true" :false-value="false" />
                        <span>
                            {{
                                form.applicant_type === 'single_property'
                                    ? 'I understand my apartment stays hidden until Vietstays approves my host application.'
                                    : 'I understand this is an application and apartment listings are created only after approval.'
                            }}
                        </span>
                    </label>
                    <div class="host-app-actions">
                        <button type="button" class="public-btn public-btn--ghost" @click="goBack">Back</button>
                        <button type="submit" class="public-btn public-btn--primary" :disabled="submitting">
                            {{ submitting ? 'Submitting…' : 'Submit application' }}
                        </button>
                    </div>
                </form>
            </section>
        </template>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import apiClient from '@/api/client';
import { formatVnd } from '@/utils/format';

const loading = ref(true);
const submitting = ref(false);
const submitted = ref(false);
const submittedRef = ref('');
const errors = ref([]);
const currentStep = ref(1);

const options = reactive({
    cities: [],
    districts: [],
    facilities: [],
    space_types: [],
    years_managing: [],
    guest_profiles: [],
    platforms: [],
});

const form = reactive({
    applicant_type: '',
    property_space_type: '',
    property_address: '',
    property_city_id: '',
    property_district_id: '',
    property_beds: 1,
    property_bathrooms: 1,
    property_amenities: [],
    property_name: '',
    property_description: '',
    property_price_daily: null,
    full_name: '',
    email: '',
    phone: '',
    num_properties: 1,
    primary_city_id: '',
    districts: [],
    company_name: '',
    portfolio_url: '',
    description: '',
    years_managing: '',
    guest_profile: '',
    platforms_used: [],
    confirm_application: false,
});

const singleSteps = ['type', 'space_type', 'location', 'basics', 'amenities', 'listing', 'contact', 'experience', 'review'];
const multiSteps = ['type', 'contact', 'experience', 'review'];

const stepSequence = computed(() => (form.applicant_type === 'single_property' ? singleSteps : multiSteps));
const totalSteps = computed(() => stepSequence.value.length);
const stepKind = computed(() => stepSequence.value[currentStep.value - 1] ?? 'type');

const propertyDistricts = computed(() =>
    options.districts.filter((district) => district.city_id === form.property_city_id),
);

const primaryDistricts = computed(() =>
    options.districts.filter((district) => district.city_id === form.primary_city_id),
);

function stepLabel(step) {
    const labels = {
        type: 'Start',
        space_type: 'Space',
        location: 'Location',
        basics: 'Basics',
        amenities: 'Amenities',
        listing: 'Listing',
        contact: 'About you',
        experience: 'Experience',
        review: 'Review',
    };

    return labels[stepSequence.value[step - 1]] ?? step;
}

function nextFromType() {
    if (! form.applicant_type) {
        return;
    }
    currentStep.value = 2;
}

function goNext() {
    errors.value = [];

    if (stepKind.value === 'listing' && form.property_description && ! form.description) {
        form.description = form.property_description;
    }

    currentStep.value = Math.min(currentStep.value + 1, totalSteps.value);
}

function goBack() {
    errors.value = [];
    currentStep.value = Math.max(currentStep.value - 1, 1);
}

function onPropertyCityChange() {
    form.property_district_id = '';
}

function onPrimaryCityChange() {
    form.districts = [];
}

function buildPayload() {
    const payload = {
        applicant_type: form.applicant_type,
        full_name: form.full_name,
        email: form.email,
        phone: form.phone,
        company_name: form.company_name || null,
        portfolio_url: form.portfolio_url || null,
        description: form.description || form.property_description,
        years_managing: form.years_managing || null,
        guest_profile: form.guest_profile || null,
        platforms_used: form.platforms_used,
        confirm_application: form.confirm_application,
    };

    if (form.applicant_type === 'multi_property') {
        payload.num_properties = form.num_properties;
        payload.primary_city_id = form.primary_city_id;
        payload.districts = form.districts;
    } else {
        payload.property_address = form.property_address;
        payload.property_space_type = form.property_space_type;
        payload.property_city_id = form.property_city_id;
        payload.property_district_id = form.property_district_id;
        payload.primary_city_id = form.property_city_id;
        payload.property_beds = form.property_beds;
        payload.property_bathrooms = form.property_bathrooms;
        payload.property_amenities = form.property_amenities;
        payload.property_name = form.property_name;
        payload.property_description = form.property_description;
        payload.property_price_daily = form.property_price_daily;
        payload.districts = form.property_district_id ? [form.property_district_id] : [];
        payload.primary_city_id = form.property_city_id;
    }

    return payload;
}

async function submitApplication() {
    submitting.value = true;
    errors.value = [];

    try {
        const payload = await apiClient.post('/public/host-applications', buildPayload());
        submitted.value = true;
        submittedRef.value = payload?.data?.application_ref ?? '';
    } catch (err) {
        const messages = err.payload?.errors?.application;
        if (Array.isArray(messages)) {
            errors.value = messages;
        } else if (err.payload?.errors) {
            errors.value = Object.values(err.payload.errors).flat();
        } else {
            errors.value = [err.message || 'Could not submit application.'];
        }
    } finally {
        submitting.value = false;
    }
}

onMounted(async () => {
    try {
        const res = await apiClient.get('/public/host-applications/options');
        Object.assign(options, res?.data ?? {});
    } catch {
        errors.value = ['Could not load application form options.'];
    } finally {
        loading.value = false;
    }
});
</script>
