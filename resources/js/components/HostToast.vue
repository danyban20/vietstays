<template>
    <Transition name="host-toast">
        <div v-if="visible" class="host-toast" role="status">{{ message }}</div>
    </Transition>
</template>

<script setup>
import { useToast } from '@/composables/useToast';

const { message, visible } = useToast();
</script>
