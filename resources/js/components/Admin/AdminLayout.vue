<template>
  <div class="admin-layout">
    <!-- Navbar Admin Supérieure -->
    <header class="admin-navbar">
      <div class="brand">
        <router-link to="/admin" class="logo">
          <Icon name="PhShieldCheck" weight="duotone" />
          <span>Administration Grimoire</span>
        </router-link>
      </div>
      <div class="user-menu">
        <span class="role-badge" v-if="user">{{ user.role }}</span>
        <router-link to="/dashboard" class="back-link">
          <Icon name="PhArrowLeft" weight="bold" /> Retour au site
        </router-link>
      </div>
    </header>

    <div class="admin-body">
      <!-- Sidebar de Navigation -->
      <aside class="admin-sidebar">
        <nav class="sidebar-nav">
          <router-link to="/admin" class="nav-item" exact-active-class="active">
            <Icon name="PhGauge" weight="duotone" /> Dashboard
          </router-link>

          <!-- Section Admin -->
          <template v-if="isAdmin">
            <div class="nav-section">Global</div>
            <router-link to="/admin/users" class="nav-item" active-class="active">
              <Icon name="PhUsers" weight="duotone" /> Utilisateurs
            </router-link>
            <router-link to="/admin/settings" class="nav-item" active-class="active">
              <Icon name="PhGearSix" weight="duotone" /> Paramètres
            </router-link>
          </template>

          <!-- Section Modérateur -->
          <template v-if="isAdmin || isModerator">
            <div class="nav-section">Modération</div>
            <router-link to="/admin/moderation/reviews" class="nav-item" active-class="active">
              <Icon name="PhFlag" weight="duotone" /> Signalements
            </router-link>
            <router-link to="/admin/moderation/comments" class="nav-item" active-class="active">
              <Icon name="PhChatsCircle" weight="duotone" /> Commentaires
            </router-link>
          </template>

          <!-- Section Bibliothécaire -->
          <template v-if="isAdmin || isLibrarian">
            <div class="nav-section">Catalogue</div>
            <router-link to="/admin/catalog/suggestions" class="nav-item" active-class="active">
              <Icon name="PhLightbulb" weight="duotone" /> Suggestions
            </router-link>
            <router-link to="/admin/catalog/works" class="nav-item" active-class="active">
              <Icon name="PhBooks" weight="duotone" /> Œuvres
            </router-link>
            <router-link to="/admin/catalog/editions" class="nav-item" active-class="active">
              <Icon name="PhBookBookmark" weight="duotone" /> Éditions
            </router-link>
          </template>

          <!-- Section Support (Modérateurs & Admin) -->
          <template v-if="isAdmin || isModerator">
            <div class="nav-section">Support & Aide</div>
            <router-link to="/admin/support-tickets" class="nav-item" active-class="active">
              <Icon name="PhEnvelopeSimple" weight="duotone" /> Tickets Support
            </router-link>
            <router-link to="/admin/feature-suggestions" class="nav-item" active-class="active">
              <Icon name="PhLightbulb" weight="duotone" /> Boîte à idées
            </router-link>
            <router-link to="/admin/faq" class="nav-item" active-class="active" v-if="isAdmin">
              <Icon name="PhInfo" weight="duotone" /> Gérer FAQ
            </router-link>
          </template>
        </nav>
      </aside>

      <!-- Contenu Principal Admin -->
      <main class="admin-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import Icon from '../Shared/Icon.vue';

const user = ref(null);

onMounted(async () => {
  try {
    const response = await axios.get('/api/user');
    user.value = response.data;
  } catch (e) {
    console.error("Erreur de récupération de l'utilisateur", e);
  }
});

const isAdmin = computed(() => user.value && user.value.role === 'admin');
const isModerator = computed(() => user.value && user.value.role === 'moderator');
const isLibrarian = computed(() => user.value && user.value.role === 'librarian');
</script>

<style scoped>
.admin-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: #f8fafc; /* Un peu plus neutre que le site principal pour l'admin */
  color: #1e293b;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Police plus propre pour l'admin */
}

/* Navbar Admin */
.admin-navbar {
  height: 60px;
  background-color: #1e293b; /* Bleu très sombre */
  color: #f8fafc;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 1.5rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  z-index: 10;
}

.brand .logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-family: var(--font-heading, 'MedievalSharp', cursive);
  font-size: 1.25rem;
  color: #f8fafc;
  text-decoration: none;
}

.user-menu {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.role-badge {
  background-color: #3b82f6;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.8rem;
  text-transform: uppercase;
  font-weight: bold;
  letter-spacing: 0.05em;
}

.back-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #cbd5e1;
  text-decoration: none;
  font-size: 0.95rem;
  transition: color 0.2s;
}

.back-link:hover {
  color: white;
}

/* Corps */
.admin-body {
  display: flex;
  flex: 1;
  overflow: hidden;
}

/* Sidebar */
.admin-sidebar {
  width: 250px;
  background-color: white;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.sidebar-nav {
  padding: 1.5rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.nav-section {
  font-size: 0.75rem;
  text-transform: uppercase;
  color: #94a3b8;
  font-weight: bold;
  letter-spacing: 0.05em;
  margin-top: 1.5rem;
  margin-bottom: 0.5rem;
  padding-left: 0.75rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border-radius: 0.5rem;
  color: #475569;
  text-decoration: none;
  font-size: 0.95rem;
  transition: all 0.2s;
}

.nav-item:hover {
  background-color: #f1f5f9;
  color: #0f172a;
}

.nav-item.active {
  background-color: #eff6ff;
  color: #2563eb;
  font-weight: 500;
}

/* Contenu Principal */
.admin-content {
  flex: 1;
  padding: 2rem;
  overflow-y: auto;
  background-color: #f8fafc;
}
</style>
