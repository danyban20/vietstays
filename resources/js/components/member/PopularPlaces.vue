<template>
    <div v-if="places.length" class="popular_stay">
        <div class="subtitle_top">
            <h4>Popular places to stay</h4>
        </div>
        <div class="block_wrap">
            <router-link
                v-for="(place, index) in places"
                :key="place.district_id"
                :to="{ name: 'public-apartments', query: { district: place.district_id } }"
                class="block"
            >
                <div class="img">
                    <img :src="place.image || fallbackImage(index)" :alt="place.name" loading="lazy" @error="onImageError($event, index)" />
                </div>
                <div class="desc">
                    <p>
                        <strong>{{ place.name }} </strong>
                        {{ place.city }} · {{ place.apartments }} {{ place.apartments === 1 ? 'apartment' : 'apartments' }}
                    </p>
                </div>
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import apiClient from '@/api/client';
import { withAppBase } from '@/utils/app-base';

const places = ref([]);

function fallbackImage(index) {
    return withAppBase(`/member/images/place_image_${(index % 4) + 1}.png`);
}

function onImageError(event, index) {
    const fallback = fallbackImage(index);

    if (!event.target.src.endsWith(fallback)) {
        event.target.src = fallback;
    }
}

onMounted(async () => {
    try {
        const res = await apiClient.get('/public/popular-places');
        places.value = Array.isArray(res?.data) ? res.data : [];
    } catch {
        places.value = [];
    }
});
</script>
