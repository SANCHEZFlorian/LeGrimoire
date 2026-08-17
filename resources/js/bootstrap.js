import axios from 'axios';

// Configuration globale d'Axios pour Laravel Sanctum
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
