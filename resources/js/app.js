import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';

// Layout Global
import AppLayout from './components/Layouts/AppLayout.vue';

// Pages
import Landing from './components/Landing.vue';
import Dashboard from './components/Dashboard/Dashboard.vue';
import Catalog from './components/Catalog/Catalog.vue';
import Tracker from './components/Tracker/Tracker.vue';
import TBR from './components/TBR/TBR.vue';
import Wishlist from './components/Wishlist/Wishlist.vue';
import Reviews from './components/Reviews/Reviews.vue';
import Favorites from './components/Favorites/Favorites.vue';
import Settings from './components/Settings/Settings.vue';
import CurrentReading from './components/Reading/CurrentReading.vue';
import Login from './components/Auth/Login.vue';
import Register from './components/Auth/Register.vue';
import AddManual from './components/AddManual.vue';
import Chronicle from './components/Chronicle/Chronicle.vue';

// Admin Pages
import AdminLayout from './components/Admin/AdminLayout.vue';
import AdminDashboard from './components/Admin/AdminDashboard.vue';
import AdminUsers from './components/Admin/AdminUsers.vue';
import AdminSettings from './components/Admin/AdminSettings.vue';
import ModerationReviews from './components/Admin/ModerationReviews.vue';
import ModerationComments from './components/Admin/ModerationComments.vue';
import CatalogWorks from './components/Admin/CatalogWorks.vue';
import CatalogEditions from './components/Admin/CatalogEditions.vue';
import CatalogSuggestions from './components/Admin/CatalogSuggestions.vue';

// Restauration du token si présent
const token = localStorage.getItem('auth_token');
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Configuration des routes
const routes = [
    { path: '/', name: 'Landing', component: Landing },
    { path: '/login', name: 'Login', component: Login },
    { path: '/register', name: 'Register', component: Register },
    { path: '/dashboard', name: 'Dashboard', component: Dashboard, meta: { requiresAuth: true } },
    { path: '/catalog', name: 'Catalog', component: Catalog, meta: { requiresAuth: true } },
    { path: '/tracker', name: 'Tracker', component: Tracker, meta: { requiresAuth: true } },
    { path: '/reviews', name: 'Reviews', component: Reviews, meta: { requiresAuth: true } },
    { path: '/favorites', name: 'Favorites', component: Favorites, meta: { requiresAuth: true } },
    { path: '/wishlist', name: 'Wishlist', component: Wishlist, meta: { requiresAuth: true } },
    { path: '/tbr', name: 'TBR', component: TBR, meta: { requiresAuth: true } },
    { path: '/add-manual', name: 'AddManual', component: AddManual, meta: { requiresAuth: true } },
    { path: '/settings', name: 'Settings', component: Settings, meta: { requiresAuth: true } },
    { path: '/chronicle', name: 'Chronicle', component: Chronicle, meta: { requiresAuth: true } },
    { path: '/reading', name: 'CurrentReading', component: CurrentReading, meta: { requiresAuth: true } },
    
    // Administration Routes
    { 
        path: '/admin', 
        component: AdminLayout, 
        meta: { requiresAuth: true, requiresAdminRole: true },
        children: [
            { path: '', name: 'AdminDashboard', component: AdminDashboard },
            { path: 'users', name: 'AdminUsers', component: AdminUsers },
            { path: 'settings', name: 'AdminSettings', component: AdminSettings },
            { path: 'moderation/reviews', name: 'ModerationReviews', component: ModerationReviews },
            { path: 'moderation/comments', name: 'ModerationComments', component: ModerationComments },
            { path: 'catalog/works', name: 'AdminCatalogWorks', component: CatalogWorks },
            { path: 'catalog/editions', name: 'AdminCatalogEditions', component: CatalogEditions },
            { path: 'catalog/suggestions', name: 'AdminCatalogSuggestions', component: CatalogSuggestions }
        ]
    }
];

// Création de l'instance du Routeur
const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    if (to.meta.requiresAuth) {
        if (!localStorage.getItem('auth_token')) {
            return next('/login');
        }
    }
    
    if (to.meta.requiresAdminRole) {
        try {
            const { data: user } = await window.axios.get('/api/user');
            if (!user || user.role === 'user') {
                return next('/dashboard'); // Redirection si simple user
            }
        } catch (e) {
            return next('/login');
        }
    }
    next();
});

// Création de l'application Vue
const app = createApp(AppLayout);
app.use(router);
app.mount('#app');
