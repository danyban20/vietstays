import { createApp } from 'vue';
import { createPinia } from 'pinia';
import AdminApp from './AdminApp.vue';
import router from './router/admin';
import { i18n } from './i18n';
import { useLocaleStore } from './stores/locale';
import { normalizeLegacyAppUrl } from '@/utils/app-base';
import './styles/host-dashboard.css';
import './styles/apt-wizard.css';

normalizeLegacyAppUrl('/admin');

const app = createApp(AdminApp);
const pinia = createPinia();

app.use(pinia);
app.use(i18n);
app.use(router);

const localeStore = useLocaleStore(pinia);
localeStore.bootstrap().finally(() => {
    app.mount('#app');
});
