<template>
  <div class="admin-dashboard">
    <h1 class="page-title">Dashboard</h1>
    
    <div class="stats-grid">
      <!-- Admin Stats -->
      <div class="stat-card" v-if="user?.role === 'admin'">
        <div class="stat-icon users"><Icon name="PhUsers" weight="duotone" /></div>
        <div class="stat-info">
          <h3>Utilisateurs</h3>
          <p class="stat-value">Gestion Globale</p>
        </div>
      </div>

      <!-- Modérateur Stats -->
      <div class="stat-card" v-if="['admin', 'moderator'].includes(user?.role)">
        <div class="stat-icon flags"><Icon name="PhFlag" weight="duotone" /></div>
        <div class="stat-info">
          <h3>Signalements</h3>
          <p class="stat-value">À traiter</p>
        </div>
      </div>

      <!-- Bibliothécaire Stats -->
      <div class="stat-card" v-if="['admin', 'librarian'].includes(user?.role)">
        <div class="stat-icon books"><Icon name="PhBooks" weight="duotone" /></div>
        <div class="stat-info">
          <h3>Suggestions</h3>
          <p class="stat-value">Modifications en attente</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Icon from '../Shared/Icon.vue';

const user = ref(null);

onMounted(async () => {
  try {
    const response = await axios.get('/api/user');
    user.value = response.data;
  } catch (e) {
    console.error("Erreur user dashboard", e);
  }
});
</script>

<style scoped>
.page-title {
  font-size: 1.8rem;
  color: #0f172a;
  margin-bottom: 2rem;
  font-family: 'Segoe UI', sans-serif;
  font-weight: bold;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.stat-icon.users { background: #dbeafe; color: #2563eb; }
.stat-icon.flags { background: #fee2e2; color: #dc2626; }
.stat-icon.books { background: #fef3c7; color: #d97706; }

.stat-info h3 {
  font-size: 0.9rem;
  color: #64748b;
  margin-bottom: 0.25rem;
}

.stat-value {
  font-size: 1.1rem;
  font-weight: bold;
  color: #0f172a;
}
</style>
