<template>
    <div class="member-page terms-page">
        <header class="terms-page__header">
            <h1>Terms, privacy and contact</h1>
            <p>Last updated {{ TERMS_UPDATED }}</p>
            <nav class="terms-page__toc" aria-label="On this page">
                <a v-for="section in TERMS_SECTIONS" :key="section.id" :href="`#${section.id}`" class="public-link">
                    {{ section.title }}
                </a>
                <a href="#contact" class="public-link">Contact us</a>
            </nav>
        </header>

        <section v-for="section in TERMS_SECTIONS" :id="section.id" :key="section.id" class="host-app-card terms-page__section">
            <h2>{{ section.title }}</h2>
            <p v-for="(paragraph, index) in section.paragraphs" :key="index">{{ paragraph }}</p>
        </section>

        <section id="contact" class="host-app-card terms-page__section">
            <h2>Contact us</h2>
            <p>Questions about a booking, a change or your personal data? Get in touch and include your booking reference if you have one.</p>
            <dl v-if="contactEmail || contactPhone" class="terms-page__contact">
                <template v-if="contactEmail">
                    <dt>Email</dt>
                    <dd><a :href="`mailto:${contactEmail}`" class="public-link">{{ contactEmail }}</a></dd>
                </template>
                <template v-if="contactPhone">
                    <dt>Phone</dt>
                    <dd><a :href="`tel:${contactPhone.replace(/\s+/g, '')}`" class="public-link">{{ contactPhone }}</a></dd>
                </template>
            </dl>
            <p v-else>You can reply to any email we have sent you about your booking.</p>
        </section>
    </div>
</template>

<script setup>
import { TERMS_SECTIONS, TERMS_UPDATED } from '@/data/terms-content';

const meta = (name) => document.querySelector(`meta[name="${name}"]`)?.content?.trim() ?? '';
const contactEmail = meta('contact-email');
const contactPhone = meta('contact-phone');
</script>
