<template>
  <div class="tracker-container">
    <h1 class="page-title">Ma Bibliothèque</h1>

    <div v-if="isLoading" class="loading-state">Chargement...</div>

    <div v-else class="tracker-content">
      <!-- Recherche et Filtres -->
      <div class="search-and-filters">
        <div class="search-bar">
          <Icon name="PhMagnifyingGlass" weight="bold" class="search-icon" />
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Rechercher par titre, auteur..." 
          />
          <button v-if="searchQuery" class="clear-btn" @click="searchQuery = ''" type="button" title="Effacer la recherche">
            <Icon name="PhX" weight="bold" />
          </button>
        </div>

        <div class="filters-bar">
          <div class="filter-group">
            <label>Statut</label>
            <CustomSelect v-model="filterStatus" :options="[
              { value: 'all', label: 'Tous' },
              { value: 'finished', label: 'Lus' },
              { value: 'reading', label: 'En cours' },
              { value: 'to_read', label: 'À lire' },
              { value: 'wishlist', label: 'Wishlist' },
              { value: 'dnf', label: 'DNF' }
            ]" />
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
      </div>

      <!-- Tops du mois -->
      <section v-if="topBooks.length > 0" class="tops-section">
        <h2 class="section-title"><Icon name="PhTrophy" weight="duotone" /> Tops du mois</h2>
        <div class="tops-grid">
          <div v-for="reading in topBooks" :key="reading.id" class="top-book-card" @click="openDetail(reading)">
            <div class="cover-wrapper">
              <img :src="getCover(reading)" alt="Couverture" class="book-cover" />
              <Icon name="PhStar" weight="fill" class="award-badge" />
            </div>
            <div class="book-info">
              <h3>{{ getTitle(reading) }}</h3>
              <p>{{ getAuthor(reading) }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Étagère -->
      <section class="shelf-section">
        <h2 class="section-title"><Icon name="PhBookshelf" weight="duotone" /> Étagère <span class="shelf-count">({{ filteredBooks.length }})</span></h2>
        <div class="wood-shelf">
          <div class="books-container">
            <div
              v-for="reading in filteredBooks"
              :key="reading.id"
              class="book-spine"
              @click="openDetail(reading)"
              :style="{ backgroundColor: getSpineColor(reading) }"
              :title="getTitle(reading) + ' par ' + getAuthor(reading)"
            >
              <span class="book-title">{{ getTitle(reading) }}</span>
            </div>
          </div>
          <div class="shelf-plank"></div>
        </div>
      </section>

      <div v-if="filteredBooks.length === 0 && !isLoading" class="empty-state">
        <span v-if="searchQuery">Aucun livre ne correspond à votre recherche "{{ searchQuery }}".</span>
        <span v-else>Aucun livre ne correspond à vos filtres.</span>
      </div>
    </div>

    <!-- Modale -->
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
const searchQuery = ref('');
const filterStatus = ref('all');
const filterFormat = ref('all');
const filterGenre = ref('all');
const modalOpen = ref(false);
const selectedReading = ref(null);
const ratingSystem = ref('20_points');

const fetchData = async () => {
  isLoading.value = true;
  try {
    const [readingsRes, prefsRes] = await Promise.all([
      axios.get('/api/readings'),
      axios.get('/api/user/preferences'),
    ]);
    readings.value = readingsRes.data;
    ratingSystem.value = prefsRes.data.rating_system || '20_points';
  } catch (e) {
    console.error("Erreur", e);
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchData);

const getCover = (r) => r.edition?.cover_url || r.work?.cover_url || r.book?.cover_url || placeholder;
const getTitle = (r) => r.work?.title || r.book?.title || 'Titre inconnu';
const getAuthor = (r) => r.work?.author || r.book?.author || 'Auteur inconnu';
const getSpineColor = (r) => r.edition?.spine_color || r.work?.spine_color || r.book?.spine_color || '#4A3525';

const getGenresList = (r) => {
  const g = r.work?.genres || r.book?.genres;
  if (Array.isArray(g)) return g;
  if (typeof g === 'string') return [g];
  if (r.book?.genre) return [r.book.genre];
  return [];
};

const genres = computed(() => {
  const set = new Set();
  readings.value.forEach(r => {
    getGenresList(r).forEach(g => set.add(g));
  });
  return [...set].sort();
});

const genreOptions = computed(() => {
  return [
    { value: 'all', label: 'Tous' },
    ...genres.value.map(g => ({ value: g, label: g }))
  ];
});

const topBooks = computed(() => {
  return readings.value.filter(r => {
    const awards = r.work?.custom_awards || r.book?.custom_awards;
    if (!awards || awards.length === 0) return false;
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.trim().toLowerCase();
      const title = (getTitle(r) || '').toLowerCase();
      const author = (getAuthor(r) || '').toLowerCase();
      const saga = (r.work?.series_name || '').toLowerCase();
      return title.includes(q) || author.includes(q) || saga.includes(q);
    }
    return true;
  });
});

const filteredBooks = computed(() => {
  let books = readings.value.filter(r => {
    const awards = r.work?.custom_awards || r.book?.custom_awards;
    return !awards || awards.length === 0;
  });

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.trim().toLowerCase();
    books = books.filter(r => {
      const title = (getTitle(r) || '').toLowerCase();
      const author = (getAuthor(r) || '').toLowerCase();
      const saga = (r.work?.series_name || '').toLowerCase();
      return title.includes(q) || author.includes(q) || saga.includes(q);
    });
  }

  if (filterStatus.value !== 'all') {
    books = books.filter(r => r.status === filterStatus.value);
  }
  if (filterFormat.value !== 'all') {
    books = books.filter(r => (r.format || r.edition?.format) === filterFormat.value);
  }
  if (filterGenre.value !== 'all') {
    books = books.filter(r => getGenresList(r).includes(filterGenre.value));
  }
  return books;
});

const openDetail = (reading) => {
  selectedReading.value = reading;
  modalOpen.value = true;
};

const onSaved = (updated) => {
  const idx = readings.value.findIndex(r => r.id === updated.id);
  if (idx !== -1) readings.value[idx] = updated;
  else fetchData();
};

const onDeleted = (deletedId) => {
  readings.value = readings.value.filter(r => r.id !== deletedId);
};
</script>

<style scoped>
.tracker-container { width: 100%; }
.page-title { font-size: 2.2rem; margin-bottom: 1.5rem; color: var(--color-primary); }
.section-title { display: flex; align-items: center; gap: 0.5rem; color: var(--color-primary); }
.loading-state { text-align: center; padding: 4rem; color: var(--color-text-muted); font-style: italic; }

.search-and-filters {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
  margin-bottom: 2rem;
}

.search-bar {
  position: relative;
  display: flex;
  align-items: center;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 0.6rem 1rem;
  box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.05));
  max-width: 500px;
}

.search-icon {
  color: var(--color-text-muted);
  font-size: 1.2rem;
  margin-right: 0.75rem;
}

.search-bar input {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 1rem;
  color: var(--color-text);
  font-family: inherit;
  outline: none;
}

.clear-btn {
  background: none;
  border: none;
  color: var(--color-text-muted);
  font-size: 1.1rem;
  cursor: pointer;
  padding: 0.2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s;
}

.clear-btn:hover {
  color: var(--color-text);
}

.filters-bar { display: flex; gap: 1rem; flex-wrap: wrap; }
.filter-group { display: flex; flex-direction: column; gap: 0.3rem; }
.filter-group label { font-size: 0.8rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px; }

.tops-section { margin-bottom: 3rem; }
.tops-grid { display: flex; flex-wrap: wrap; gap: 1.5rem; }
.top-book-card { width: 130px; cursor: pointer; transition: transform 0.2s; }
.top-book-card:hover { transform: translateY(-6px); }
.cover-wrapper { position: relative; border-radius: 8px; overflow: hidden; box-shadow: var(--shadow-card); }
.book-cover { width: 100%; height: 195px; object-fit: cover; display: block; border: 1px solid var(--color-border); }
.award-badge {
  position: absolute; bottom: -8px; right: -8px; background: var(--color-accent, #fbbf24);
  color: white; border-radius: 50%; width: 30px; height: 30px; display: flex;
  justify-content: center; align-items: center; font-size: 1rem;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2); border: 2px solid var(--color-surface);
}
.book-info h3 { margin: 0.5rem 0 0.1rem; font-size: 0.9rem; font-weight: 600; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; color: var(--color-text); }
.book-info p { margin: 0; font-size: 0.8rem; color: var(--color-text-muted); }

.shelf-section { margin-bottom: 3rem; }
.shelf-section h2 { margin-bottom: 1rem; }
.shelf-count { font-weight: 400; color: var(--color-text-muted); font-size: 1rem; }
.wood-shelf { padding: 2rem 2rem 0; }
.books-container {
  display: flex; align-items: flex-end; justify-content: flex-start;
  gap: 2px; padding: 0 20px; min-height: 220px; flex-wrap: wrap; position: relative; z-index: 1;
}
.shelf-plank {
  width: 100%; height: 15px; background: linear-gradient(to bottom, #6b4423, #3e2714);
  box-shadow: 0 5px 10px rgba(0,0,0,0.3); border-radius: 2px; margin-top: -2px; z-index: 0; position: relative;
}
.book-spine {
  width: 42px; height: 180px; border-radius: 3px 3px 0 0;
  box-shadow: inset 4px 0 10px rgba(0,0,0,0.2), inset -2px 0 5px rgba(255,255,255,0.1);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: transform 0.2s ease; z-index: 1;
}
.book-spine:hover { transform: translateY(-10px); z-index: 10; }
.book-title {
  writing-mode: vertical-rl; text-orientation: mixed; transform: rotate(180deg);
  color: #fff; font-size: 0.8rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-height: 90%;
}

.empty-state {
  text-align: center; padding: 3rem; color: var(--color-text-muted); font-style: italic; font-size: 1.1rem;
  border: 1px dashed var(--color-border); border-radius: 12px;
}

@media (max-width: 700px) {
  .filters-bar { flex-direction: column; }
}
</style>
