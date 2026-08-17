<template>
  <div class="mobile-menu-overlay" :class="{ 'is-open': isOpen }" @click.self="$emit('close')">
    <div class="mobile-menu-panel" :class="{ 'is-open': isOpen }">
      <div class="mobile-menu-header">
        <div class="logo">
          <Icon name="PhBookOpenText" weight="duotone" />
          <span>Le Grimoire</span>
        </div>
        <button class="close-btn" @click="$emit('close')">
          <Icon name="PhX" weight="bold" size="24" />
        </button>
      </div>

      <nav class="mobile-nav">
        <router-link to="/dashboard" class="mobile-nav-item" @click="$emit('close')">
          <Icon name="PhGauge" weight="duotone" /> Dashboard
        </router-link>



        <div class="mobile-nav-section">
          <h4>Ma Bibliothèque</h4>
          <router-link to="/tracker" class="mobile-nav-item" @click="$emit('close')">
            <Icon name="PhBooks" weight="duotone" /> Tous mes livres
          </router-link>
          <router-link to="/wishlist" class="mobile-nav-item" @click="$emit('close')">
            <Icon name="PhShoppingCart" weight="duotone" /> Wishlist
          </router-link>
          <router-link to="/tbr" class="mobile-nav-item" @click="$emit('close')">
            <Icon name="PhBookmarks" weight="duotone" /> Pile à lire
          </router-link>
          <router-link to="/favorites" class="mobile-nav-item" @click="$emit('close')">
            <Icon name="PhHeart" weight="fill" /> Favoris
          </router-link>
          <router-link to="/reviews" class="mobile-nav-item" @click="$emit('close')">
            <Icon name="PhStar" weight="fill" /> Reviews
          </router-link>
        </div>

        <router-link to="/reading" class="mobile-nav-item" @click="$emit('close')">
          <Icon name="PhBookOpen" weight="duotone" /> Lectures en cours
        </router-link>

        <router-link to="/chronicle" class="mobile-nav-item" @click="$emit('close')">
          <Icon name="PhCalendarDots" weight="duotone" /> Chronique
        </router-link>

        <div class="mobile-nav-section">
          <router-link to="/catalog" class="mobile-nav-item" @click="$emit('close')">
            <Icon name="PhPlusCircle" weight="bold" /> Ajouter
          </router-link>
        </div>
      </nav>

      <div class="mobile-menu-footer">
        <router-link to="/settings" class="mobile-nav-item" @click="$emit('close')">
          <Icon name="PhGearSix" weight="duotone" /> Paramètres
        </router-link>
        <button class="mobile-nav-item text-danger" @click="logout">
          <Icon name="PhSignOut" weight="bold" /> Déconnexion
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import axios from 'axios';
import Icon from '../Shared/Icon.vue';

defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close']);
const router = useRouter();

const logout = async () => {
  emit('close');
  try {
    await axios.post('/api/logout');
    router.push('/login');
  } catch (error) {
    console.error("Erreur lors de la déconnexion", error);
  }
};
</script>

<style scoped>
.mobile-menu-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1000;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s ease;
}
.mobile-menu-overlay.is-open {
  opacity: 1;
  pointer-events: auto;
}

.mobile-menu-panel {
  position: absolute;
  top: 0;
  right: -300px;
  width: 300px;
  height: 100vh;
  background-color: var(--color-bg, #fff7ed);
  box-shadow: -4px 0 15px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  transition: right 0.3s ease;
  overflow-y: auto;
}
.mobile-menu-panel.is-open {
  right: 0;
}

.mobile-menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid var(--color-border, rgba(44, 30, 22, 0.1));
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-family: var(--font-heading, 'MedievalSharp', cursive);
  font-size: 1.25rem;
  color: var(--color-primary, #721c24);
  font-weight: bold;
}

.close-btn {
  background: none;
  border: none;
  color: var(--color-text, #2c1e16);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.25rem;
}

.mobile-nav {
  flex: 1;
  padding: 1rem 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.mobile-nav-section {
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
}

.mobile-nav-section h4 {
  font-family: var(--font-heading, 'MedievalSharp', cursive);
  color: var(--color-text-muted, #7a6040);
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  padding: 0 1.5rem;
  margin: 0.5rem 0;
}

.mobile-nav-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 1.5rem;
  color: var(--color-text, #2c1e16);
  text-decoration: none;
  font-family: var(--font-body, 'Cormorant Garamond', serif);
  font-size: 1.1rem;
  background: none;
  border: none;
  text-align: left;
  width: 100%;
  cursor: pointer;
}

.mobile-nav-item:hover, .router-link-active {
  background-color: var(--color-primary-light, rgba(114, 28, 36, 0.05));
  color: var(--color-primary, #721c24);
}

.mobile-menu-footer {
  padding: 1rem 0;
  border-top: 1px solid var(--color-border, rgba(44, 30, 22, 0.1));
}

.text-danger {
  color: #dc2626 !important;
}
</style>
