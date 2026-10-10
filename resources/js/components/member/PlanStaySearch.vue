<template>
    <form class="dash_book_block" @submit.prevent="submit">
        <div class="input_wrap dash_choose_city_sel" :class="{ open: openDropdown === 'city' }">
            <label>Where</label>
            <a href="#" class="room_btn city_label" @click.prevent="toggle('city')">{{ cityLabel }}</a>
            <div class="dash_city_dropdown dash_book_dropdown">
                <ul>
                    <li v-for="city in cities" :key="city.city_id">
                        <a href="#" @click.prevent="selectCity(city)"><strong>{{ city.name }}</strong></a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="input_wrap dash_room_sel" :class="{ open: openDropdown === 'guests' }">
            <label>Rooms &amp; Guests</label>
            <a href="#" class="room_btn rooms_guests_label" @click.prevent="toggle('guests')">{{ guestsLabel }}</a>
            <div class="dash_rooom_dropdown dash_book_dropdown">
                <ul>
                    <li v-for="field in counters" :key="field.key">
                        <span class="lbltxt">{{ field.label }}<i>{{ field.hint }}</i></span>
                        <div class="number">
                            <span class="minus" role="button" :aria-label="`Fewer ${field.label.toLowerCase()}`" @click="adjust(field.key, -1)" />
                            <input :value="search[field.key]" type="text" :name="field.key" readonly />
                            <span class="plus" role="button" :aria-label="`More ${field.label.toLowerCase()}`" @click="adjust(field.key, 1)" />
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div class="input_wrap dash_date_sel">
            <label>Check in &amp; out</label>
            <input ref="dateInput" type="text" name="datefilter" placeholder="Select dates" readonly />
        </div>
        <div class="input_submit">
            <input type="submit" value="Search" />
        </div>
    </form>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import apiClient from '@/api/client';
import { useHomeSearchBar } from '@/composables/useHomeSearchBar';

const router = useRouter();
const { initDateRangePicker, hideDatePicker, destroyDateRangePicker } = useHomeSearchBar();

const dateInput = ref(null);
const cities = ref([]);
const openDropdown = ref(null);
const touchedGuests = ref(false);

const search = reactive({
    cityId: '',
    cityName: '',
    rooms: 1,
    adults: 2,
    children: 0,
    from: '',
    to: '',
});

const counters = [
    { key: 'rooms', label: 'Rooms', hint: 'Choose amount of rooms', min: 1 },
    { key: 'adults', label: 'Adults', hint: '13 years old or older', min: 1 },
    { key: 'children', label: 'Children', hint: 'below 13 years old', min: 0 },
];

const cityLabel = computed(() => search.cityName || 'Where are you going?');
const guestsLabel = computed(() => {
    if (!touchedGuests.value) {
        return 'Add rooms & guests';
    }

    const guests = search.adults + search.children;

    return `${search.rooms} ${search.rooms === 1 ? 'room' : 'rooms'} & ${guests} ${guests === 1 ? 'guest' : 'guests'}`;
});

function close() {
    openDropdown.value = null;
    document.body.classList.remove('book_overlay_open');
}

function toggle(name) {
    hideDatePicker();

    if (openDropdown.value === name) {
        close();
        return;
    }

    openDropdown.value = name;
    document.body.classList.add('book_overlay_open');
}

function selectCity(city) {
    search.cityId = String(city.city_id);
    search.cityName = city.name;
    close();
}

function adjust(key, delta) {
    const field = counters.find((item) => item.key === key);
    search[key] = Math.max(field.min, Number(search[key] || 0) + delta);
    touchedGuests.value = true;
}

function submit() {
    close();
    const query = {};

    if (search.cityId) query.city = search.cityId;
    if (touchedGuests.value) {
        query.rooms = String(search.rooms);
        query.adults = String(search.adults);
        if (search.children) query.children = String(search.children);
    }
    if (search.from) query.from = search.from;
    if (search.to) query.to = search.to;

    router.push({ name: 'public-apartments', query });
}

function onDocumentClick(event) {
    if (!event.target.closest('.dash_book_block')) {
        close();
    }
}

onMounted(async () => {
    document.addEventListener('click', onDocumentClick);

    try {
        const res = await apiClient.get('/public/cities');
        cities.value = Array.isArray(res?.data) ? res.data : [];
    } catch {
        cities.value = [];
    }

    await nextTick();
    await initDateRangePicker(dateInput.value, {
        onApply: (from, to) => {
            search.from = from;
            search.to = to;
        },
        onClear: () => {
            search.from = '';
            search.to = '';
        },
        onShow: () => {
            openDropdown.value = null;
        },
    });
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onDocumentClick);
    document.body.classList.remove('book_overlay_open');
    destroyDateRangePicker();
});
</script>
