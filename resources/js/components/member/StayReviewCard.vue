<template>
    <div class="stay_experince">
        <template v-if="review.submitted || submitted">
            <h4>Thank you for your review</h4>
            <div class="stars member-stars--readonly" :aria-label="`${shownRating} out of 5 stars`">
                <span v-for="n in 5" :key="n" class="star_icon" :class="{ is_on: n <= shownRating }" />
                <p>{{ ratingWords[shownRating] }}</p>
            </div>
            <p v-if="shownComment" class="member-review-comment">“{{ shownComment }}”</p>
        </template>
        <template v-else>
            <h4>How was your stay?</h4>
            <p>Share your experience at {{ apartmentName }} to help {{ hostName }} improve and guide future travelers.</p>
            <div class="stars" role="radiogroup" aria-label="Overall rating">
                <a
                    v-for="n in 5"
                    :key="n"
                    href="#"
                    class="star_icon"
                    role="radio"
                    :aria-checked="rating === n"
                    :aria-label="`${n} ${n === 1 ? 'star' : 'stars'}`"
                    :class="{ is_on: n <= (hover || rating) }"
                    @mouseenter="hover = n"
                    @mouseleave="hover = 0"
                    @click.prevent="rating = n"
                />
                <p>{{ rating ? ratingWords[rating] : 'Select a rating' }}</p>
            </div>
            <h6>Optional category ratings</h6>
            <ul>
                <li v-for="category in review.categories" :key="category.key" :class="{ is_rated: categories[category.key] }">
                    <a href="#" @click.prevent="toggleCategory(category.key)">{{ category.label }}</a>
                    <span v-if="openCategory === category.key || categories[category.key]" class="member-mini-stars">
                        <button
                            v-for="n in 5"
                            :key="n"
                            type="button"
                            :class="{ is_on: n <= (categories[category.key] || 0) }"
                            :aria-label="`${category.label}: ${n} of 5`"
                            @click="setCategory(category.key, n)"
                        />
                    </span>
                </li>
            </ul>
            <div class="opt_comment">
                <label for="review-comment">Optional comment</label>
                <textarea id="review-comment" v-model="comment" maxlength="2000" placeholder="Share anything that would help future guests." />
            </div>
            <p v-if="error" class="member-form-error">{{ error }}</p>
            <div class="btn_wrap">
                <button type="button" class="btn" :disabled="!rating || saving" @click="submit">{{ saving ? 'Sending…' : 'Submit Review' }}</button>
                <a href="#" class="skip_link" @click.prevent="skip">Skip for now</a>
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import apiClient from '@/api/client';

const props = defineProps({
    bookingId: { type: [Number, String], required: true },
    review: { type: Object, required: true },
    apartmentName: { type: String, default: 'the apartment' },
    hostName: { type: String, default: 'your host' },
});

const emit = defineEmits(['done']);

const ratingWords = ['', 'Poor', 'Fair', 'Good', 'Very good', 'Excellent'];

const rating = ref(0);
const hover = ref(0);
const comment = ref('');
const categories = reactive({});
const openCategory = ref(null);
const saving = ref(false);
const error = ref('');
const submitted = ref(false);

const shownRating = computed(() => (submitted.value ? rating.value : props.review.rating) || 0);
const shownComment = computed(() => (submitted.value ? comment.value.trim() : props.review.comment));

function toggleCategory(key) {
    openCategory.value = openCategory.value === key ? null : key;
}

function setCategory(key, value) {
    categories[key] = categories[key] === value ? 0 : value;
}

async function submit() {
    saving.value = true;
    error.value = '';

    const ratedCategories = Object.fromEntries(Object.entries(categories).filter(([, value]) => value > 0));

    try {
        const res = await apiClient.post(`/member/reservations/${props.bookingId}/review`, {
            rating: rating.value,
            category_ratings: ratedCategories,
            comment: comment.value.trim() || null,
        });
        submitted.value = true;
        emit('done', res.message);
    } catch (err) {
        error.value = err.message || 'Could not send your review.';
    } finally {
        saving.value = false;
    }
}

async function skip() {
    try {
        await apiClient.post(`/member/reservations/${props.bookingId}/review/skip`);
    } catch {
        // Skipping only hides the card; nothing to recover.
    }

    emit('done', null, true);
}
</script>
