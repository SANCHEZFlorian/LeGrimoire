<template>
  <div class="wishlist-container">
    <h1 class="page-title"><Icon name="PhShoppingCart" weight="duotone" /> Liste d'envies</h1>

    <div v-if="isLoading" class="loading-state">Chargement...</div>

    <div v-else>
      <!-- Filtres -->
      <div class="filters-bar">
        <div class="filter-group">
          <label>Format</label>
          <CustomSelect v-model="filterFormat" :options="[
            { value: 'all', label: 'Tous' },
            { value: 'Broché', label: 'Broché' },
            { value: 'Relié', label: 'Relié' },
            { value: 'Poche', label: 'Poche' },
            { value: 'Numérique', label: 'Numérique' },
            { value: 'Audio', label: 'Audio' }
          ]" />
        </div>
        <div class="filter-group">
          <label>Priorité</label>
          <CustomSelect v-model="filterPriority" :options="[
            { value: 'all', label: 'Toutes' },
            { value: 'high', label: 'Haute' },
            { value: 'medium', label: 'Moyenne' },
            { value: 'low', label: 'Basse' }
          ]" />
        </div>
      </div>

      <!-- Liste -->
      <div class="wishlist-grid" v-if="filteredBooks.length > 0">
        <div v-for="reading in filteredBooks" :key="reading.id" class="wishlist-card">
          <img :src="getCover(reading)" alt="Couverture" class="card-cover" @click="openDetail(reading)" />
          <div class="card-info">
            <h3 @click="openDetail(reading)">{{ getTitle(reading) }}</h3>
            <p class="author">{{ getAuthor(reading) }}</p>
            <p class="format" v-if="reading.format || reading.edition?.format">{{ reading.format || reading.edition?.format }}</p>
            <p class="priority" v-if="reading.priority">
              <Icon v-if="reading.priority.includes('🔥')" name="PhFire" weight="fill" class="priority-icon" />
              {{ reading.priority.replace('🔥 ', '') }}
            </p>
            <div class="card-actions">
              <label class="bought-toggle" :class="{ checked: reading.is_bought }">
                <input type="checkbox" v-model="reading.is_bought" @change="markBought(reading)" />
                <Icon v-if="reading.is_bought" name="PhCheckCircle" weight="fill" />
                <span>{{ reading.is_bought ? 'Acheté' : 'Marquer acheté' }}</span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="empty-state">
        Votre liste d'envies est vide. Ajoutez des livres via <router-link to="/catalog">THE Bibliothèque</router-link> ou la recherche !
      </div>
    </div>

    <BookDetailModal
      :visible="modalOpen"
      :reading="selectedReading"
      :ratingSystem="ratingSystem"
      @close="modalOpen = false"
      @saved="onSaved"
      @deleted="onDeleted"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import BookDetailModal from '../Shared/BookDetailModal.vue';
import Icon from '../Shared/Icon.vue';
import CustomSelect from '../Shared/CustomSelect.vue';

const placeholder = 'https://via.placeholder.com/128x192.png?text=Pas+de+couverture';
const isLoading = ref(true);
const readings = ref([]);
const filterFormat = ref('all');
const filterPriority = ref('all');
const modalOpen = ref(false);
const selectedReading = ref(null);
const ratingSystem = ref('20_points');

onMounted(async () => {
  try {
    const [readingsRes, prefsRes] = await Promise.all([
      axios.get('/api/readings'),
      axios.get('/api/user/preferences'),
    ]);
    readings.value = readingsRes.data;
    ratingSystem.value = prefsRes.data.rating_system || '20_points';
  } catch (e) {
    console.error(e);
  } finally {
    isLoading.value = false;
  }
});

const getCover = (r) => r.edition?.cover_url || r.work?.cover_url || r.book?.cover_url || placeholder;
const getTitle = (r) => r.work?.title || r.book?.title || 'Titre inconnu';
const getAuthor = (r) => r.work?.author || r.book?.author || 'Auteur inconnu';

const wishlistBooks = computed(() => readings.value.filter(r => r.status === 'wishlist'));

const filteredBooks = computed(() => {
  let books = wishlistBooks.value;
  if (filterFormat.value !== 'all') {
    books = books.filter(r => (r.format || r.edition?.format) === filterFormat.value);
  }
  if (filterPriority.value === 'high') {
    books = books.filter(r => r.priority && r.priority.includes('🔥'));
  } else if (filterPriority.value === 'normal') {
    books = books.filter(r => !r.priority || !r.priority.includes('🔥'));
  }
  return books;
});

const markBought = async (reading) => {
  try {
    const newStatus = reading.is_bought ? 'to_read' : 'wishlist';
    await axios.put(`/api/readings/${reading.id}`, {
      is_bought: reading.is_bought,
      status: reading.is_bought ? 'to_read' : 'wishlist',
    });
    if (reading.is_bought) {
      reading.status = 'to_read';
    }
  } catch (e) {
    console.error(e);
    reading.is_bought = !reading.is_bought;
  }
};

const openDetail = (reading) => {
  selectedReading.value = reading;
  modalOpen.value = true;
};

const onSaved = (updated) => {
  const idx = readings.value.findIndex(r => r.id === updated.id);
  if (idx !== -1) readings.value[idx] = updated;
};

const onDeleted = (deletedId) => {
  readings.value = readings.value.filter(r => r.id !== deletedId);
};
</script>

<style scoped>
.wishlist-container { width: 100%; }
.page-title { font-size: 2.2rem; margin-bottom: 1.5rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.5rem; }
.loading-state { text-align: center; padding: 4rem; color: var(--color-text-muted); font-style: italic; }

.filters-bar { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
.filter-group { display: flex; flex-direction: column; gap: 0.3rem; }
.filter-group label { font-size: 0.8rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px; }

.wishlist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem; }
.wishlist-card {
  display: flex; gap: 1rem; padding: 1.2rem;
  border: 1px solid var(--color-border); border-radius: var(--border-radius, 12px); transition: box-shadow 0.2s;
  background: var(--color-surface);
}
.wishlist-card:hover { box-shadow: var(--shadow-card); }
.card-cover { width: 80px; height: 120px; object-fit: cover; border-radius: 6px; cursor: pointer; border: 1px solid var(--color-border); }
.card-info { flex: 1; display: flex; flex-direction: column; }
.card-info h3 { margin: 0 0 0.2rem; font-size: 1.1rem; cursor: pointer; color: var(--color-text); }
.card-info h3:hover { text-decoration: underline; color: var(--color-primary); }
.author { margin: 0 0 0.3rem; color: var(--color-text-muted); font-style: italic; font-size: 0.95rem; }
.format { margin: 0; font-size: 0.85rem; color: var(--color-text-muted); }
.priority { margin: 0.3rem 0 0; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 0.3rem; color: var(--color-text); }
.priority-icon { color: var(--color-accent, #e11d48); }
.card-actions { margin-top: auto; padding-top: 0.5rem; }
.bought-toggle {
  display: inline-flex; align-items: center; gap: 0.4rem;
  cursor: pointer; font-size: 0.9rem; padding: 0.3rem 0.8rem;
  border: 1px solid var(--color-border); border-radius: 20px; transition: all 0.2s;
  color: var(--color-text);
}
.bought-toggle.checked { background: var(--color-primary-light); border-color: var(--color-primary); color: var(--color-primary); }
.bought-toggle input { display: none; }

.empty-state {
  text-align: center; padding: 3rem; color: var(--color-text-muted); font-style: italic;
  border: 1px dashed var(--color-border); border-radius: 12px;
}
</style>
