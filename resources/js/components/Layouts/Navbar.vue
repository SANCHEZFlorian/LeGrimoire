<template>
  <header class="navbar">
    <div class="navbar-container">
      <div class="navbar-left">
        <router-link to="/dashboard" class="logo">
          <Icon name="PhBookOpenText" weight="duotone" />
          <span>Le Grimoire</span>
        </router-link>
      </div>

      <nav class="navbar-center desktop-only">
        <router-link to="/dashboard" class="nav-item">
          <Icon name="PhGauge" weight="duotone" />
          <span>Dashboard</span>
        </router-link>



        <div class="nav-item dropdown-trigger" @mouseenter="showLibrary = true" @mouseleave="showLibrary = false">
          <div class="dropdown-label">
            <Icon name="PhBooks" weight="duotone" />
            <span>Ma Bibliothèque</span>
            <Icon name="PhCaretDown" weight="bold" size="14" class="caret" />
          </div>
          <div class="dropdown-menu" v-show="showLibrary">
            <router-link to="/tracker" class="dropdown-item">
              <Icon name="PhBooks" weight="duotone" /> Tous mes livres
            </router-link>
            <router-link to="/wishlist" class="dropdown-item">
              <Icon name="PhShoppingCart" weight="duotone" /> Wishlist
            </router-link>
            <router-link to="/tbr" class="dropdown-item">
              <Icon name="PhBookmarks" weight="duotone" /> Pile à lire
            </router-link>
            <router-link to="/favorites" class="dropdown-item">
              <Icon name="PhHeart" weight="fill" /> Favoris
            </router-link>
            <router-link to="/reviews" class="dropdown-item">
              <Icon name="PhStar" weight="fill" /> Reviews
            </router-link>
          </div>
        </div>

        <router-link to="/reading" class="nav-item">
          <Icon name="PhBookOpen" weight="duotone" />
          <span>Lectures en cours</span>
        </router-link>

        <router-link to="/chronicle" class="nav-item">
          <Icon name="PhCalendarDots" weight="duotone" />
          <span>Chronique</span>
        </router-link>
      </nav>

      <div class="navbar-right desktop-only">
        <div class="nav-item dropdown-trigger" @mouseenter="showAdd = true" @mouseleave="showAdd = false">
          <div class="add-btn">
            <Icon name="PhPlusCircle" weight="fill" size="20" />
            <span>Ajouter</span>
          </div>
          <div class="dropdown-menu align-right" v-show="showAdd">
            <router-link to="/catalog" class="dropdown-item">
              <Icon name="PhMagnifyingGlass" weight="bold" /> Chercher un livre (Catalogue)
            </router-link>
            <router-link to="/add-manual" class="dropdown-item">
              <Icon name="PhPencilLine" weight="bold" /> Saisie manuelle d'œuvre
            </router-link>
          </div>
        </div>

        <div class="nav-item dropdown-trigger profile-menu" @mouseenter="showProfile = true" @mouseleave="showProfile = false">
          <div class="profile-icon">
            <Icon name="PhUserCircle" weight="duotone" size="28" />
          </div>
          <div class="dropdown-menu align-right" v-show="showProfile">
            <router-link 
              v-if="user && user.role !== 'user'" 
              to="/admin" 
              class="dropdown-item"
            >
              <Icon name="PhShieldCheck" weight="duotone" /> Administration
            </router-link>
            <router-link to="/settings" class="dropdown-item">
              <Icon name="PhGearSix" weight="duotone" /> Paramètres
            </router-link>
            <button @click="logout" class="dropdown-item text-danger">
              <Icon name="PhSignOut" weight="bold" /> Déconnexion
            </button>
          </div>
        </div>
      </div>

      <div class="mobile-toggle">
        <button class="hamburger-btn" @click="$emit('toggle-menu')">
          <Icon name="PhList" weight="bold" size="28" />
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Icon from '../Shared/Icon.vue';

const router = useRouter();

const showLibrary = ref(false);
const showAdd = ref(false);
const showProfile = ref(false);
const user = ref(null);

onMounted(async () => {
  try {
    const response = await axios.get('/api/user');
    user.value = response.data;
  } catch (e) {
    console.error('Erreur lors du chargement de l\'utilisateur', e);
  }
});

const logout = async () => {
  try {
    await axios.post('/api/logout');
    localStorage.removeItem('auth_token');
    router.push('/login');
  } catch (error) {
    console.error("Erreur lors de la déconnexion", error);
  }
};
</script>

<style scoped>
.navbar {
  position: sticky;
  top: 0;
  z-index: 100;
  background-color: var(--color-surface, rgba(255, 255, 255, 0.8));
  backdrop-filter: blur(10px);
  border-bottom: 1px solid var(--color-border, rgba(0, 0, 0, 0.1));
  padding: 0 1.5rem;
  height: 70px;
  display: flex;
  align-items: center;
}

.navbar-container {
  max-width: 1400px;
  width: 100%;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.navbar-left .logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-family: var(--font-heading, 'MedievalSharp', cursive);
  font-size: 1.5rem;
  color: var(--color-primary, #721c24);
  text-decoration: none;
  font-weight: bold;
}

.navbar-center {
  display: flex;
  gap: 1.2rem;
  align-items: center;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--color-text, #2c1e16);
  text-decoration: none;
  font-family: var(--font-body, 'Cormorant Garamond', serif);
  font-size: 1.1rem;
  font-weight: 500;
  padding: 0.5rem;
  border-radius: 8px;
  transition: all 0.2s;
  cursor: pointer;
  position: relative;
}

.nav-item:hover, .router-link-active {
  color: var(--color-primary, #721c24);
  background-color: var(--color-primary-light, rgba(114, 28, 36, 0.05));
}

.dropdown-trigger {
  position: relative;
}

.dropdown-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.caret {
  margin-left: -2px;
  transition: transform 0.2s;
}
.dropdown-trigger:hover .caret {
  transform: rotate(180deg);
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  background-color: var(--color-bg, #fff7ed);
  border: 1px solid var(--color-border, rgba(44, 30, 22, 0.2));
  border-radius: 8px;
  box-shadow: var(--shadow-card, 0 4px 12px rgba(0,0,0,0.1));
  min-width: 210px;
  padding: 0.5rem 0;
  display: flex;
  flex-direction: column;
  z-index: 101;
}

.dropdown-menu.align-right {
  left: auto;
  right: 0;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.25rem;
  color: var(--color-text, #2c1e16);
  text-decoration: none;
  font-family: var(--font-body, 'Cormorant Garamond', serif);
  font-size: 1rem;
  transition: background-color 0.2s, color 0.2s;
  cursor: pointer;
  border: none;
  background: none;
  text-align: left;
  width: 100%;
}

.dropdown-item:hover {
  background-color: var(--color-primary-light, rgba(114, 28, 36, 0.05));
  color: var(--color-primary, #721c24);
}

.text-danger {
  color: #dc2626 !important;
}
.text-danger:hover {
  background-color: rgba(220, 38, 38, 0.1) !important;
}

.navbar-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.add-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background-color: transparent;
  color: var(--color-primary, #721c24);
  border: 1px solid var(--color-primary, #721c24);
  padding: 0.35rem 0.85rem;
  border-radius: var(--border-radius, 20px);
  font-weight: 600;
  transition: all 0.2s;
}

.nav-item:hover .add-btn {
  background-color: var(--color-primary-light, rgba(114, 28, 36, 0.1));
}

.profile-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: var(--color-primary-light, rgba(114, 28, 36, 0.1));
  color: var(--color-primary, #721c24);
}

.mobile-toggle {
  display: none;
}
.hamburger-btn {
  background: none;
  border: none;
  color: var(--color-text, #2c1e16);
  cursor: pointer;
  padding: 0.5rem;
}

@media (max-width: 1023px) {
  .desktop-only {
    display: none !important;
  }
  .mobile-toggle {
    display: block;
  }
}
</style>
