<template>
  <div class="tbr-container">
    <h1 class="page-title"><Icon name="PhBooks" weight="duotone" /> Pile À Lire (TBR)</h1>

    <div v-if="isLoading" class="loading-state">Chargement...</div>

    <div v-else>
      <!-- Filtres -->
      <div class="filters-bar">
        <div class="filter-group">
          <label>Genre</label>
          <CustomSelect v-model="filterGenre" :options="genreOptions" />
        </div>
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
      </div>

      <!-- Liste -->
      <div class="tbr-grid" v-if="filteredBooks.length > 0">
        <div v-for="reading in filteredBooks" :key="reading.id" class="tbr-card">
          <img :src="getCover(reading)" alt="Couverture" class="card-cover" @click="openDetail(reading)" />
          <div class="card-info">
            <h3 @click="openDetail(reading)">{{ getTitle(reading) }}</h3>
            <p class="author">{{ getAuthor(reading) }}</p>
            <div class="genres-row" v-if="getGenres(reading).length > 0">
              <span v-for="g in getGenres(reading)" :key="g" class="mini-genre">{{ g }}</span>
            </div>
            <p class="format" v-if="reading.format || reading.edition?.format">{{ reading.format || reading.edition?.format }}</p>
            <p class="pages" v-if="getPages(reading)">{{ getPages(reading) }} pages</p>
            <button class="btn-start" @click="startReading(reading)">
              <Icon name="PhBookOpen" weight="duotone" /> Commencer la lecture
            </button>
          </div>
        </div>
      </div>

      <div v-else class="empty-state">
        Votre pile à lire est vide. Ajoutez des livres depuis <router-link to="/catalog">THE Bibliothèque</router-link> ou la <router-link to="/book-search">recherche</router-link> !
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
const filterGenre = ref('all');
const filterFormat = ref('all');
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
const getPages = (r) => r.pages_count || r.edition?.default_pages_count || r.book?.pages_count;

const getGenres = (r) => {
  const g = r.work?.genres || r.book?.genres;
  if (Array.isArray(g)) return g;
  if (typeof g === 'string') return [g];
  if (r.book?.genre) return [r.book.genre];
  return [];
};

const tbrBooks = computed(() => readings.value.filter(r => r.status === 'to_read'));

const genres = computed(() => {
  const set = new Set();
  tbrBooks.value.forEach(r => {
    getGenres(r).forEach(g => set.add(g));
  });
  return [...set].sort();
});

const genreOptions = computed(() => {
  return [
    { value: 'all', label: 'Tous' },
    ...genres.value.map(g => ({ value: g, label: g }))
  ];
});

const filteredBooks = computed(() => {
  let books = tbrBooks.value;
  if (filterGenre.value !== 'all') {
    books = books.filter(r => getGenres(r).includes(filterGenre.value));
  }
  if (filterFormat.value !== 'all') {
    books = books.filter(r => (r.format || r.edition?.format) === filterFormat.value);
  }
  return books;
});

const startReading = async (reading) => {
  try {
    const today = new Date().toISOString().substring(0, 10);
    await axios.put(`/api/readings/${reading.id}`, {
      status: 'reading',
      start_date: today,
    });
    reading.status = 'reading';
    reading.start_date = today;
  } catch (e) {
    console.error(e);
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
.tbr-container { width: 100%; }
.page-title { font-size: 2.2rem; margin-bottom: 1.5rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.5rem; }
.loading-state { text-align: center; padding: 4rem; color: var(--color-text-muted); font-style: italic; }

.filters-bar { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
.filter-group { display: flex; flex-direction: column; gap: 0.3rem; }
.filter-group label { font-size: 0.8rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px; }

.tbr-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem; }
.tbr-card {
  display: flex; gap: 1rem; padding: 1.2rem;
  border: 1px solid var(--color-border); border-radius: var(--border-radius, 12px); transition: box-shadow 0.2s;
  background: var(--color-surface);
}
.tbr-card:hover { box-shadow: var(--shadow-card); }
.card-cover { width: 80px; height: 120px; object-fit: cover; border-radius: 6px; cursor: pointer; border: 1px solid var(--color-border); }
.card-info { flex: 1; display: flex; flex-direction: column; }
.card-info h3 { margin: 0 0 0.2rem; font-size: 1.1rem; cursor: pointer; color: var(--color-text); }
.card-info h3:hover { text-decoration: underline; color: var(--color-primary); }
.author { margin: 0 0 0.3rem; color: var(--color-text-muted); font-style: italic; font-size: 0.95rem; }
.genres-row { display: flex; gap: 0.3rem; flex-wrap: wrap; margin-bottom: 0.3rem; }
.mini-genre { font-size: 0.75rem; background: var(--color-bg); border: 1px solid var(--color-border); padding: 0.1rem 0.4rem; border-radius: 6px; color: var(--color-text-muted); }
.format { margin: 0; font-size: 0.85rem; color: var(--color-text-muted); }
.pages { margin: 0.2rem 0 0; font-size: 0.85rem; color: var(--color-text-muted); }

.btn-start {
  margin-top: auto; padding-top: 0.5rem; align-self: flex-start;
  padding: 0.5rem 1rem; background: var(--color-primary); color: var(--color-bg);
  border: none; border-radius: 8px; font-size: 0.9rem;
  cursor: pointer; transition: background 0.2s; font-weight: 600;
  display: flex; align-items: center; gap: 0.4rem;
}
.btn-start:hover { background: var(--color-primary-light); }

.empty-state {
  text-align: center; padding: 3rem; color: var(--color-text-muted); font-style: italic;
  border: 1px dashed var(--color-border); border-radius: 12px;
}
</style>
