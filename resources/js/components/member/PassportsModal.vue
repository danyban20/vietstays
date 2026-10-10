<template>
    <MemberModal :open="open" :title="view === 'list' ? 'Guest Passports' : 'Add Passport Details'" @close="close">
        <template v-if="view === 'list'">
            <p>Add passport details for every guest staying at the apartment. Each guest’s information is saved separately.</p>
            <div class="registerd_text">
                <strong>{{ summary.registered }} of {{ summary.expected }} guests registered</strong>
                {{ missing > 0 ? `Complete the remaining ${missing === 1 ? 'guest' : 'guests'} before arrival.` : 'Everyone is registered. Thank you!' }}
            </div>
            <div class="guest_list">
                <div v-for="guest in summary.guests" :key="guest.position" class="block">
                    <div class="block_left">
                        <div class="num">{{ guest.position }}</div>
                        <div class="name">
                            <strong>{{ guest.complete ? guest.full_name : `Guest ${guest.position}` }}</strong>
                            {{ guest.complete ? `Passport ending in ${guest.passport_last4}` : 'Passport details required' }}
                        </div>
                    </div>
                    <div class="block_right">
                        <div>
                            <span :class="guest.complete ? 'completed' : 'required_text'">{{ guest.complete ? 'Completed' : 'Required' }}</span>
                        </div>
                        <a v-if="summary.editable" href="#" class="adit" @click.prevent="edit(guest)">
                            {{ guest.complete ? 'Edit' : 'Add passport details' }}
                        </a>
                    </div>
                </div>
            </div>
            <p>Passport details are stored per guest and can be updated until check-out.</p>
            <div class="btn_wrap">
                <a href="#" class="btn border_btn" @click.prevent="close">Close</a>
                <a v-if="summary.editable && firstMissing" href="#" class="btn" @click.prevent="edit(firstMissing)">Add missing passport</a>
            </div>
        </template>

        <form v-else novalidate @submit.prevent="save">
            <p>
                Guest {{ form.position }} of {{ summary.expected }} — add the passport details requested for this stay.
                Review the <a href="#" class="member-inline-link" @click.prevent="$emit('privacy')">Privacy Notice</a> to learn how the information is handled.
            </p>
            <div v-if="loadingGuest" class="member-loading">Loading…</div>
            <template v-else>
                <div v-if="formError" class="member-form-error">{{ formError }}</div>
                <label for="pp-name">Full Name (as on passport)</label>
                <input id="pp-name" v-model="form.full_name" type="text" placeholder="Enter full name" autocomplete="name" maxlength="150" />
                <p v-if="errors.full_name" class="member-field-error">{{ errors.full_name }}</p>
                <div class="row">
                    <div class="col-sm-6">
                        <label for="pp-nat">Nationality</label>
                        <select id="pp-nat" v-model="form.nationality">
                            <option value="">Select nationality</option>
                            <option v-for="country in countries" :key="country.code" :value="country.code">{{ country.name }}</option>
                        </select>
                        <p v-if="errors.nationality" class="member-field-error">{{ errors.nationality }}</p>
                    </div>
                    <div class="col-sm-6">
                        <label for="pp-num">Passport Number</label>
                        <input id="pp-num" v-model="form.passport_number" type="text" placeholder="Enter passport number" maxlength="20" autocomplete="off" />
                        <p v-if="errors.passport_number" class="member-field-error">{{ errors.passport_number }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <label for="pp-dob">Date of Birth</label>
                        <input id="pp-dob" v-model="form.date_of_birth" type="date" :max="today" />
                        <p v-if="errors.date_of_birth" class="member-field-error">{{ errors.date_of_birth }}</p>
                    </div>
                    <div class="col-sm-6">
                        <label for="pp-exp">Expiry Date</label>
                        <input id="pp-exp" v-model="form.passport_expiry" type="date" :min="today" />
                        <p v-if="errors.passport_expiry" class="member-field-error">{{ errors.passport_expiry }}</p>
                    </div>
                </div>
                <label for="pp-photo">Upload Passport Photo</label>
                <label class="member-upload" for="pp-photo">
                    <img :src="uploadImage" alt="" />
                    <span>{{ photoLabel }}</span>
                </label>
                <input id="pp-photo" ref="fileInput" class="member-visually-hidden" type="file" accept="image/jpeg,image/png,image/webp,application/pdf" @change="onFile" />
                <p v-if="errors.photo" class="member-field-error">{{ errors.photo }}</p>
            </template>
            <div class="btn_wrap">
                <a href="#" class="btn border_btn" @click.prevent="view = 'list'">Back to guests</a>
                <button type="submit" class="btn" :disabled="saving || loadingGuest">{{ saving ? 'Saving…' : 'Save guest passport' }}</button>
            </div>
        </form>
    </MemberModal>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import apiClient, { uploadFiles } from '@/api/client';
import MemberModal from '@/components/member/MemberModal.vue';
import { countryOptions } from '@/data/countries';
import { withAppBase } from '@/utils/app-base';

const props = defineProps({
    open: { type: Boolean, default: false },
    bookingId: { type: [Number, String], required: true },
    summary: { type: Object, required: true },
    startWith: { type: Number, default: null },
});

const emit = defineEmits(['close', 'saved', 'privacy']);

const countries = countryOptions();
const uploadImage = withAppBase('/member/images/upload.png');
const today = new Date().toISOString().slice(0, 10);

const view = ref('list');
const saving = ref(false);
const loadingGuest = ref(false);
const formError = ref('');
const errors = reactive({});
const fileInput = ref(null);
const photo = ref(null);
const hasPhoto = ref(false);

const form = reactive({
    position: 1,
    full_name: '',
    nationality: '',
    passport_number: '',
    date_of_birth: '',
    passport_expiry: '',
});

const missing = computed(() => Math.max(0, props.summary.expected - props.summary.registered));
const firstMissing = computed(() => props.summary.guests.find((guest) => !guest.complete) ?? null);
const photoLabel = computed(() => {
    if (photo.value) {
        return photo.value.name;
    }

    return hasPhoto.value ? 'Photo on file — choose a file to replace it' : 'JPG, PNG or PDF, up to 8 MB';
});

function resetErrors() {
    formError.value = '';
    Object.keys(errors).forEach((key) => delete errors[key]);
}

async function edit(guest) {
    resetErrors();
    photo.value = null;
    hasPhoto.value = false;
    if (fileInput.value) fileInput.value.value = '';

    Object.assign(form, {
        position: guest.position,
        full_name: '',
        nationality: '',
        passport_number: '',
        date_of_birth: '',
        passport_expiry: '',
    });
    view.value = 'form';

    if (!guest.complete) {
        return;
    }

    loadingGuest.value = true;

    try {
        const res = await apiClient.get(`/member/reservations/${props.bookingId}/guests/${guest.position}`);
        Object.assign(form, {
            full_name: res.data.full_name ?? '',
            nationality: res.data.nationality ?? '',
            passport_number: res.data.passport_number ?? '',
            date_of_birth: res.data.date_of_birth ?? '',
            passport_expiry: res.data.passport_expiry ?? '',
        });
        hasPhoto.value = Boolean(res.data.has_photo);
    } catch (err) {
        formError.value = err.message || 'Could not load this guest.';
    } finally {
        loadingGuest.value = false;
    }
}

function onFile(event) {
    photo.value = event.target.files?.[0] ?? null;
}

function validate() {
    resetErrors();

    if (!form.full_name.trim()) errors.full_name = 'Enter the name as printed on the passport.';
    if (!form.nationality) errors.nationality = 'Choose a nationality.';
    if (!/^[A-Za-z0-9]{5,20}$/.test(form.passport_number.trim())) errors.passport_number = 'Use letters and numbers only, as printed on the passport.';
    if (!form.date_of_birth) errors.date_of_birth = 'Enter the date of birth.';
    if (!form.passport_expiry) errors.passport_expiry = 'Enter the expiry date.';
    if (photo.value && photo.value.size > 8 * 1024 * 1024) errors.photo = 'The file is larger than 8 MB.';

    return Object.keys(errors).length === 0;
}

async function save() {
    if (!validate()) {
        return;
    }

    saving.value = true;
    const data = new FormData();
    Object.entries(form).forEach(([key, value]) => data.append(key, typeof value === 'string' ? value.trim() : value));
    if (photo.value) data.append('photo', photo.value);

    try {
        const res = await uploadFiles(`/member/reservations/${props.bookingId}/guests`, data);
        emit('saved', res.data, res.message);
        view.value = 'list';
    } catch (err) {
        const fieldErrors = err.payload?.errors ?? {};
        Object.entries(fieldErrors).forEach(([key, messages]) => {
            errors[key] = Array.isArray(messages) ? messages[0] : messages;
        });
        formError.value = Object.keys(fieldErrors).length ? 'Please check the highlighted fields.' : err.message || 'Could not save.';
    } finally {
        saving.value = false;
    }
}

function close() {
    emit('close');
}

watch(
    () => props.open,
    (open) => {
        if (!open) {
            return;
        }

        view.value = 'list';

        if (props.startWith) {
            const guest = props.summary.guests.find((item) => item.position === props.startWith);
            if (guest) edit(guest);
        }
    },
);
</script>
