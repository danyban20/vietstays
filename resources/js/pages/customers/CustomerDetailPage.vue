<template>
    <div>
        <div class="host-page-header">
            <div>
                <router-link :to="{ name: 'customers' }" class="host-back-link">← Back to customers</router-link>
                <h1 class="host-page-title">{{ customer?.name || 'Customer' }}</h1>
            </div>
        </div>

        <div v-if="loading" class="host-loading">Loading customer…</div>
        <div v-else-if="accessDenied" class="host-form-error">You don't have access to this customer.</div>
        <div v-else-if="loadError" class="host-form-error">{{ loadError }}</div>

        <div v-else-if="customer" class="host-detail-grid">
            <div class="host-detail-main">
                <section class="host-settings-card host-detail-card">
                    <h2 class="host-detail-card__title">Contact details</h2>
                    <dl class="host-detail-list">
                        <div><dt>Name</dt><dd>{{ customer.name }}</dd></div>
                        <div><dt>Email</dt><dd>{{ customer.email || '—' }}</dd></div>
                        <div><dt>Phone</dt><dd>{{ customer.phone || '—' }}</dd></div>
                        <div><dt>Country</dt><dd>{{ customer.country || '—' }}</dd></div>
                        <div><dt>Customer since</dt><dd>{{ formatDate(customer.created_at) }}</dd></div>
                    </dl>
                </section>

                <section class="host-settings-card host-detail-card">
                    <h2 class="host-detail-card__title">Booking history</h2>
                    <table v-if="customer.bookings?.length" class="host-table host-table--compact">
                        <thead>
                            <tr>
                                <th>Apartment</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Status</th>
                                <th style="text-align: right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="booking in customer.bookings"
                                :key="booking.id"
                                @click="goToBooking(booking.id)"
                            >
                                <td>{{ booking.apartment || '—' }}</td>
                                <td>{{ formatDate(booking.check_in) }}</td>
                                <td>{{ formatDate(booking.check_out) }}</td>
                                <td><span class="host-pill" :class="pillClass(booking.status)">{{ formatStatus(booking.status) }}</span></td>
                                <td class="host-table__amount">{{ formatVnd(booking.total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="host-detail-description">No bookings yet.</p>
                </section>
            </div>

            <aside class="host-detail-side">
                <section class="host-settings-card host-detail-card">
                    <h2 class="host-detail-card__title">Summary</h2>
                    <dl class="host-detail-list">
                        <div><dt>Total bookings</dt><dd>{{ customer.bookings_count }}</dd></div>
                        <div><dt>Total revenue</dt><dd>{{ formatVnd(customer.total_revenue) }}</dd></div>
                        <div><dt>Last stay</dt><dd>{{ formatDate(customer.last_stay) }}</dd></div>
                    </dl>
                </section>
            </aside>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import apiClient from '@/api/client';
import { formatDate, formatStatus, formatVnd } from '@/utils/format';

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const accessDenied = ref(false);
const loadError = ref('');
const customer = ref(null);

function pillClass(status) {
    return {
        confirmed: 'host-pill--confirmed',
        pending: 'host-pill--pending',
        cancelled: 'host-pill--cancelled',
    }[status] ?? 'host-pill--draft';
}

function goToBooking(id) {
    router.push({ name: 'booking-detail', params: { id } });
}

async function loadCustomer() {
    loading.value = true;
    accessDenied.value = false;
    loadError.value = '';

    try {
        const res = await apiClient.get(`/customers/${route.params.id}`);
        customer.value = res?.data ?? null;
    } catch (err) {
        if (err.status === 403) {
            accessDenied.value = true;
        } else {
            loadError.value = err.payload?.message ?? err.message ?? 'Could not load customer.';
        }
    } finally {
        loading.value = false;
    }
}

onMounted(loadCustomer);
</script>
