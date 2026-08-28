import { ref } from 'vue';

export const pageTitle = ref('');

export function usePageTitle() {
    function setPageTitle(title) {
        pageTitle.value = title ?? '';
    }

    function clearPageTitle() {
        pageTitle.value = '';
    }

    return { pageTitle, setPageTitle, clearPageTitle };
}
