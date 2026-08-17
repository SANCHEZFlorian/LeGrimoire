<template>
  <div class="favorites-container">
    <h1 class="page-title"><Icon name="PhHeart" weight="fill" /> Mes Livres Préférés</h1>

    <div v-if="isLoading" class="loading-state">Chargement...</div>

    <div v-else>
      <!-- Filtres -->
      <div class="filters-bar">
        <div class="filter-group">
          <label>Mois</label>
          <CustomSelect v-model="filterMonth" :options="monthOptions" />
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
        <div class="filter-group">
          <label>Genre</label>
          <CustomSelect v-model="filterGenre" :options="genreOptions" />
        </div>
      </div>

      <!-- Grille -->
      <div class="favorites-grid" v-if="filteredBooks.length > 0">
        <div v-for="reading in filteredBooks" :key="reading.id" class="fav-card" @click="openDetail(reading)">
          <div class="cover-wrapper">
            <img :src="getCover(reading)" alt="Couverture" />
            <div class="fav-badge"><Icon name="PhHeart" weight="fill" class="fav-badge-icon" /></div>
          </div>
          <h3>{{ getTitle(reading) }}</h3>
          <p class="author">{{ getAuthor(reading) }}</p>
          <p class="rating" v-if="reading.rating != null">{{ formatRating(reading.rating) }}</p>
        </div>
      </div>

      <div v-else class="empty-state">
        Aucun favori trouvé. Marquez des livres comme favoris !
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
const filterMonth = ref('all');
const filterFormat = ref('all');
const filterGenre = ref('all');
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

const getGenres = (r) => {
  const g = r.work?.genres || r.book?.genres;
  if (Array.isArray(g)) return g;
  if (typeof g === 'string') return [g];
  if (r.book?.genre) return [r.book.genre];
  return [];
};

const favoriteBooks = computed(() => {
  return readings.value.filter(r => r.is_favorite || isMaxRating(r.rating));
});

const isMaxRating = (rating) => {
  if (rating === null || rating === undefined) return false;
  const sys = ratingSystem.value;
  if (sys === '5_stars') return rating >= 5;
  if (sys === '10_points') return rating >= 10;
  return rating >= 20;
};

const genres = computed(() => {
  const set = new Set();
  favoriteBooks.value.forEach(r => {
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

const monthOptions = computed(() => {
  const options = [{ value: 'all', label: 'Tous' }];
  for (let m = 1; m <= 12; m++) {
    options.push({ value: m, label: getMonthName(m) });
  }
  return options;
});

const filteredBooks = computed(() => {
  let books = favoriteBooks.value;
  if (filterMonth.value !== 'all') {
    books = books.filter(r => {
      if (!r.end_date) return false;
      return new Date(r.end_date).getMonth() + 1 === filterMonth.value;
    });
  }
  if (filterFormat.value !== 'all') {
    books = books.filter(r => (r.format || r.edition?.format) === filterFormat.value);
  }
  if (filterGenre.value !== 'all') {
    books = books.filter(r => getGenres(r).includes(filterGenre.value));
  }
  return books;
});

const getMonthName = (m) => {
  const d = new Date(); d.setMonth(m - 1);
  return d.toLocaleString('fr-FR', { month: 'long' });
};

const formatRating = (r) => {
  if (r === null) return '';
  const sys = ratingSystem.value;
  if (sys === '5_stars') return r + '⭐';
  if (sys === '10_points') return r + '/10';
  return r + '/20';
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
.favorites-container { width: 100%; }
.page-title { font-size: 2.2rem; margin-bottom: 1.5rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.5rem; }
.page-title :deep(svg) { color: var(--color-accent, #e11d48); }
.loading-state { text-align: center; padding: 4rem; color: var(--color-text-muted); font-style: italic; }

.filters-bar { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
.filter-group { display: flex; flex-direction: column; gap: 0.3rem; }
.filter-group label { font-size: 0.8rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px; }

.favorites-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1.5rem; }
.fav-card {
  cursor: pointer; text-align: center; transition: transform 0.2s;
}
.fav-card:hover { transform: translateY(-5px); }
.cover-wrapper {
  position: relative; border-radius: 8px; overflow: hidden;
  box-shadow: var(--shadow-card); margin-bottom: 0.5rem;
}
.cover-wrapper img { width: 100%; height: 220px; object-fit: cover; display: block; border: 1px solid var(--color-border); border-radius: 8px; }
.fav-badge {
  position: absolute; top: 8px; right: 8px; font-size: 1.1rem;
  background: var(--color-surface); border-radius: 50%; width: 28px; height: 28px;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}
.fav-badge-icon { color: var(--color-accent, #e11d48); }
.fav-card h3 { margin: 0 0 0.1rem; font-size: 0.95rem; font-weight: 600; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; color: var(--color-text); }
.fav-card .author { margin: 0; font-size: 0.8rem; color: var(--color-text-muted); }
.fav-card .rating { margin: 0.3rem 0 0; font-weight: 700; font-size: 0.9rem; color: var(--color-primary); }

.empty-state {
  text-align: center; padding: 3rem; color: var(--color-text-muted); font-style: italic;
  border: 1px dashed var(--color-border); border-radius: 12px;
}
</style>
