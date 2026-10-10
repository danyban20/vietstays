<template>
    <div class="host-apt-detail__section host-apt-form-section host-guest-info">
        <h2 class="host-apt-detail__section-title">Guest arrival</h2>
        <p class="host-field-hint">
            Guests see this in their booking on vietstays.com. The door code shows {{ form.access_reveal_hours }} hours before check-in
            and Wi-Fi from the day of arrival — only on confirmed bookings.
        </p>

        <div v-if="loading" class="host-apt-detail__loading">Loading…</div>
        <form v-else @submit.prevent="save">
            <div class="host-price-card">
                <h3 class="host-price-card__title">Check-in &amp; check-out</h3>
                <div class="host-form-grid">
                    <div class="host-field">
                        <label class="host-field__label" for="gi-checkin">Check-in from</label>
                        <input id="gi-checkin" v-model="form.check_in_time" type="time" class="host-input" />
                    </div>
                    <div class="host-field">
                        <label class="host-field__label" for="gi-checkout">Check-out by</label>
                        <input id="gi-checkout" v-model="form.check_out_time" type="time" class="host-input" />
                    </div>
                </div>
                <p class="host-field-hint">Empty = 2:00 PM check-in and 12:00 PM check-out.</p>
            </div>

            <div class="host-price-card">
                <h3 class="host-price-card__title">Door &amp; Wi-Fi</h3>
                <div class="host-form-grid">
                    <div class="host-field">
                        <label class="host-field__label" for="gi-code">Door access code</label>
                        <input id="gi-code" v-model="form.door_code" type="text" maxlength="50" class="host-input" autocomplete="off" />
                    </div>
                    <div class="host-field">
                        <label class="host-field__label" for="gi-code-note">How to use it</label>
                        <input
                            id="gi-code-note"
                            v-model="form.door_code_note"
                            type="text"
                            maxlength="255"
                            class="host-input"
                            placeholder="Use the keypad on the door lock."
                        />
                    </div>
                    <div class="host-field">
                        <label class="host-field__label" for="gi-wifi">Wi-Fi network</label>
                        <input id="gi-wifi" v-model="form.wifi_network" type="text" maxlength="100" class="host-input" autocomplete="off" />
                    </div>
                    <div class="host-field">
                        <label class="host-field__label" for="gi-wifi-pass">Wi-Fi password</label>
                        <input id="gi-wifi-pass" v-model="form.wifi_password" type="text" maxlength="100" class="host-input" autocomplete="off" />
                    </div>
                </div>
                <p class="host-field-hint">Codes are stored encrypted.</p>
            </div>

            <div class="host-price-card">
                <h3 class="host-price-card__title">Arrival</h3>
                <div class="host-field">
                    <label class="host-field__label" for="gi-arrival">Arrival instructions</label>
                    <textarea
                        id="gi-arrival"
                        v-model="form.arrival_instructions"
                        maxlength="3000"
                        rows="4"
                        class="host-textarea"
                        placeholder="How to find the building, which entrance to use, self check-in or meeting the host…"
                    />
                </div>
                <div class="host-field">
                    <label class="host-field__label" for="gi-parking">Parking</label>
                    <textarea id="gi-parking" v-model="form.parking_info" maxlength="1000" rows="2" class="host-textarea" />
                </div>
                <div class="host-form-grid">
                    <div class="host-field">
                        <label class="host-field__label" for="gi-contact-label">Arrival help — who</label>
                        <input
                            id="gi-contact-label"
                            v-model="form.arrival_contact_label"
                            type="text"
                            maxlength="100"
                            class="host-input"
                            placeholder="Building reception"
                        />
                    </div>
                    <div class="host-field">
                        <label class="host-field__label" for="gi-contact-phone">Arrival help — phone</label>
                        <input id="gi-contact-phone" v-model="form.arrival_contact_phone" type="tel" maxlength="30" class="host-input" />
                    </div>
                    <div class="host-field">
                        <label class="host-field__label" for="gi-security">Building security phone</label>
                        <input id="gi-security" v-model="form.security_phone" type="tel" maxlength="30" class="host-input" />
                    </div>
                </div>
            </div>

            <div class="host-price-card">
                <h3 class="host-price-card__title">Included cleaning</h3>
                <div class="host-field">
                    <label class="host-field__label" for="gi-cleanings">Cleanings included per week of stay</label>
                    <select id="gi-cleanings" v-model.number="form.cleanings_per_week" class="host-select">
                        <option :value="0">None</option>
                        <option :value="1">Once a week</option>
                        <option :value="2">Twice a week</option>
                        <option :value="3">3 times a week</option>
                        <option :value="7">Every day</option>
                    </select>
                </div>
                <p class="host-field-hint">
                    Guests see the cleaning days on their booking. They can also order extra cleaning at the extra cleaning fee from
                    “Price &amp; terms”; you confirm each request on the booking.
                </p>
            </div>

            <div class="host-guest-info__actions">
                <button type="submit" class="host-btn host-btn--primary" :disabled="saving">{{ saving ? 'Saving…' : 'Save guest arrival' }}</button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import apiClient from '@/api/client';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    apartmentId: { type: [String, Number], required: true },
});

const toast = useToast();
const loading = ref(true);
const saving = ref(false);

const form = reactive({
    check_in_time: '',
    check_out_time: '',
    door_code: '',
    door_code_note: '',
    wifi_network: '',
    wifi_password: '',
    arrival_instructions: '',
    parking_info: '',
    arrival_contact_label: '',
    arrival_contact_phone: '',
    security_phone: '',
    cleanings_per_week: 0,
    access_reveal_hours: 24,
});

async function load() {
    loading.value = true;

    try {
        const res = await apiClient.get(`/apartments/${props.apartmentId}/guest-info`);
        Object.assign(form, res.data);
    } catch (err) {
        toast.show(err.message ?? 'Could not load guest arrival details.');
    } finally {
        loading.value = false;
    }
}

async function save() {
    saving.value = true;

    try {
        const { access_reveal_hours: _ignored, ...payload } = form;
        const res = await apiClient.put(`/apartments/${props.apartmentId}/guest-info`, {
            ...payload,
            check_in_time: payload.check_in_time || null,
            check_out_time: payload.check_out_time || null,
        });
        Object.assign(form, res.data);
        toast.show(res.message ?? 'Saved.');
    } catch (err) {
        toast.show(err.message ?? 'Could not save.');
    } finally {
        saving.value = false;
    }
}

onMounted(load);
</script>

<style scoped>
/* Same card look as the "Price & terms" tab (its styles are scoped to the page). */
.host-price-card {
    border: 1px solid #eee6d0;
    border-radius: 14px;
    background: #fff;
    padding: 22px 24px;
    margin-bottom: 16px;
}

.host-price-card__title {
    margin: 0 0 14px;
    font-size: 15px;
    font-weight: 700;
    color: #1c2b23;
}

.host-form-grid {
    align-items: end;
}

.host-field-hint {
    margin: 10px 0 0;
}

.host-guest-info > .host-field-hint {
    margin: 0 0 16px;
}

@media (max-width: 760px) {
    .host-form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
