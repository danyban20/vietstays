import { createApp } from 'vue';
import { createPinia } from 'pinia';
import PublicApp from './PublicApp.vue';
import router from './router/public';
import { i18n, bootstrapLocale } from './i18n';
import './styles/tokens.css';
import './styles/public.css';

const app = createApp(PublicApp);
const pinia = createPinia();

app.use(pinia);
app.use(i18n);
app.use(router);

bootstrapLocale().finally(() => {
    app.mount('#app');
});
