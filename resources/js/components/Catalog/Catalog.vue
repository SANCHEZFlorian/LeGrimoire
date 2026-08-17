<template>
  <div class="catalog-page">
    <div class="catalog-header">
      <div>
        <h1 class="page-title"><Icon name="PhMagnifyingGlass" weight="duotone" /> Chercher un livre</h1>
        <p class="page-subtitle">
          Recherchez dans le catalogue ou importez de nouvelles œuvres depuis Internet pour les ajouter à votre carnet.
        </p>
      </div>
      <div class="header-actions">
        <router-link to="/add-manual" class="btn-create-work">
          <Icon name="PhPlusCircle" weight="bold" /> Proposer une œuvre
        </router-link>
      </div>
    </div>

    <!-- Filtres et Recherche -->
    <div class="catalog-filters">
      <div class="search-bar">
        <Icon name="PhMagnifyingGlass" weight="bold" class="search-icon" />
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Rechercher par titre, auteur, saga ou ISBN..." 
          @input="debouncedFetch"
        />
        <button v-if="searchQuery" class="clear-btn" @click="clearSearch"><Icon name="PhX" weight="bold" /></button>
      </div>

      <!-- Barre de Genres Multiples -->
      <div class="genres-filter-bar" v-if="availableGenres.length > 0">
        <span class="genres-label"><Icon name="PhTag" weight="duotone" /> Genres :</span>
        <div class="genres-tags">
          <button 
            class="genre-chip" 
            :class="{ active: selectedGenre === 'all' }"
            @click="setGenre('all')"
          >
            Tous ({{ works.length }})
          </button>
          <button 
            v-for="genre in availableGenres" 
            :key="genre" 
            class="genre-chip" 
            :class="{ active: selectedGenre === genre }"
            @click="setGenre(genre)"
          >
            {{ genre }}
          </button>
        </div>
      </div>
    </div>

    <!-- Chargement -->
    <div v-if="isLoading" class="loading-state">
      <div class="loading-pulse">Consultation des grands registres...</div>
    </div>

    <!-- Liste des Œuvres -->
    <div v-else-if="filteredWorks.length > 0" class="works-grid">
      <div v-for="work in filteredWorks" :key="work.id" class="work-card">
        
        <!-- Colonne Gauche : Couverture de l'édition sélectionnée -->
        <div class="work-cover-col">
          <img 
            :src="getSelectedEdition(work)?.cover_url || work.cover_url || placeholderCover" 
            alt="Couverture" 
            class="work-cover" 
          />
          <span class="format-pill">
            {{ getSelectedEdition(work)?.format || 'Broché' }}
          </span>
        </div>

        <!-- Colonne Droite : Infos Œuvre & Sélecteur d'Éditions -->
        <div class="work-content-col">
          <div class="work-header-info">
            <div class="title-row">
              <h2 class="work-title">{{ work.title }}</h2>
              <span v-if="work.in_user_library" class="in-library-badge" title="Dans votre bibliothèque">
                <Icon name="PhBookmarkSimple" weight="fill" /> Dans ma bibliothèque
              </span>
              <button class="btn-edit-work" @click="openEditWork(work)" title="Modifier les informations de l'œuvre">
                <Icon name="PhPencilSimple" weight="bold" /> Modifier l'œuvre
              </button>
            </div>
            <p class="work-author">Par {{ work.author }}</p>
            <p class="work-series" v-if="work.series_name">
              <Icon name="PhBooks" weight="duotone" /> Saga {{ work.series_name }}
              <span v-if="work.series_number"> — Tome {{ work.series_number }}</span>
            </p>
          </div>

          <!-- Tags Genres Multiples -->
          <div class="work-genres" v-if="work.genres && work.genres.length > 0">
            <span v-for="g in work.genres" :key="g" class="genre-tag">
              {{ g }}
            </span>
          </div>

          <!-- Sélecteur d'Éditions -->
          <div class="editions-section">
            <div class="editions-header">
              <span class="editions-title">
                Éditions disponibles ({{ work.editions ? work.editions.length : 0 }}) :
              </span>
              <button class="btn-add-edition-inline" @click="openAddEdition(work)">
                <Icon name="PhPlus" weight="bold" /> Nouvelle édition
              </button>
            </div>

            <div class="editions-selector-list" v-if="work.editions && work.editions.length > 0">
              <button 
                v-for="ed in work.editions" 
                :key="ed.id" 
                class="edition-tab-btn" 
                :class="{ active: selectedEditionIds[work.id] === ed.id }"
                @click="selectedEditionIds[work.id] = ed.id"
              >
                <span class="tab-format">{{ ed.format || 'Édition' }}</span>
                <span class="tab-pub" v-if="ed.publisher">• {{ ed.publisher }}</span>
                <span class="tab-pages" v-if="ed.default_pages_count">({{ ed.default_pages_count }} p.)</span>
              </button>
            </div>

            <!-- Détails de l'édition sélectionnée -->
            <div class="selected-edition-details" v-if="getSelectedEdition(work)">
              <div class="edition-meta-row">
                <p class="edition-meta">
                  <span v-if="getSelectedEdition(work).publisher"><strong>Éditeur :</strong> {{ getSelectedEdition(work).publisher }}</span>
                  <span v-if="getSelectedEdition(work).publication_date"> • <strong>Parution :</strong> {{ getSelectedEdition(work).publication_date }}</span>
                  <span v-if="getSelectedEdition(work).isbn"> • <strong>ISBN :</strong> {{ getSelectedEdition(work).isbn }}</span>
                  <span v-if="getSelectedEdition(work).default_pages_count"> • <strong>Pages :</strong> {{ getSelectedEdition(work).default_pages_count }}</span>
                  <span v-if="getSelectedEdition(work).default_audio_duration"> • <strong>Durée :</strong> {{ getSelectedEdition(work).default_audio_duration }}</span>
                </p>
                <button class="btn-edit-edition" @click="openEditEdition(work, getSelectedEdition(work))" title="Modifier cette édition">
                  <Icon name="PhPencilSimple" weight="bold" /> Modifier cette édition
                </button>
              </div>

              <p class="edition-summary" v-if="getSelectedEdition(work).summary">
                {{ getSelectedEdition(work).summary }}
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="work-card-actions">
            <button 
              class="btn-add-to-lib" 
              @click="openAddToLibrary(work, getSelectedEdition(work))"
            >
              <Icon name="PhPlus" weight="bold" />
              Ajouter cette édition à ma bibliothèque
            </button>
          </div>

        </div>

      </div>
    </div>

    <!-- Liste des Œuvres Google -->
    <div v-if="googleWorks.length > 0" class="google-works-section">
      <h3 class="section-title"><Icon name="PhGlobe" weight="duotone" /> Résultats depuis Internet (Google Books)</h3>
      <div class="works-grid">
        <div v-for="gWork in googleWorks" :key="gWork.google_id" class="work-card google-card">
          <div class="work-cover-col">
            <img :src="gWork.thumbnail || placeholderCover" alt="Couverture" class="work-cover" />
            <span class="format-pill api-pill">API Externe</span>
          </div>
          <div class="work-content-col">
            <div class="work-header-info">
              <div class="title-row">
                <h2 class="work-title">{{ gWork.title }}</h2>
              </div>
              <p class="work-author">Par {{ gWork.author || 'Inconnu' }}</p>
              <p class="edition-meta" v-if="gWork.publisher || gWork.publishedDate">
                <span v-if="gWork.publisher">Éditeur: {{ gWork.publisher }}</span>
                <span v-if="gWork.publishedDate"> • Parution: {{ gWork.publishedDate }}</span>
              </p>
            </div>
            <p class="edition-summary" v-if="gWork.description">{{ gWork.description }}</p>
            <div class="work-card-actions">
              <button class="btn-add-to-lib btn-import" @click="importGoogleWorkAndOpenLibrary(gWork)" :disabled="importingWork">
                <Icon name="PhDownloadSimple" weight="bold" />
                {{ importingWork ? 'Importation...' : 'Importer et Ajouter' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Aucun résultat -->
    <div v-else-if="searchQuery && !isLoading" class="empty-state">
      <p>Aucune œuvre trouvée correspondant à vos critères.</p>
      <router-link to="/add-manual" class="btn-create-work">
        <Icon name="PhPlusCircle" weight="bold" /> Ajouter cette œuvre à THE bibliothèque
      </router-link>
    </div>

    <!-- Modale Ajout à ma Bibliothèque -->
    <AddToLibraryModal 
      :visible="addToLibModalOpen" 
      :work="selectedWorkForAction" 
      :edition="selectedEditionForAction" 
      @close="addToLibModalOpen = false" 
      @added="onReadingAdded" 
    />

    <!-- Modale Ajout Nouvelle Édition -->
    <AddEditionModal 
      :visible="addEditionModalOpen" 
      :work="selectedWorkForAction" 
      @close="addEditionModalOpen = false" 
      @created="onEditionCreated" 
    />

    <!-- Modale Modification Œuvre -->
    <EditWorkModal 
      :visible="editWorkModalOpen" 
      :work="selectedWorkForAction" 
      @close="editWorkModalOpen = false" 
      @updated="onWorkUpdated" 
      @deleted="onWorkDeleted" 
    />

    <!-- Modale Modification Édition -->
    <EditEditionModal 
      :visible="editEditionModalOpen" 
      :work="selectedWorkForAction" 
      :edition="selectedEditionForAction" 
      @close="editEditionModalOpen = false" 
      @updated="onEditionUpdated" 
      @deleted="onEditionDeleted" 
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import Icon from '../Shared/Icon.vue';
import AddToLibraryModal from './AddToLibraryModal.vue';
import AddEditionModal from './AddEditionModal.vue';
import EditWorkModal from './EditWorkModal.vue';
import EditEditionModal from './EditEditionModal.vue';

const placeholderCover = 'https://via.placeholder.com/128x192.png?text=Pas+de+couverture';

const isLoading = ref(true);
const works = ref([]);
const googleWorks = ref([]);
const availableGenres = ref([]);
const searchQuery = ref('');
const selectedGenre = ref('all');

const selectedEditionIds = reactive({});

const addToLibModalOpen = ref(false);
const addEditionModalOpen = ref(false);
const editWorkModalOpen = ref(false);
const editEditionModalOpen = ref(false);

const selectedWorkForAction = ref(null);
const selectedEditionForAction = ref(null);
const importingWork = ref(false);

let debounceTimeout = null;

const fetchCatalog = async () => {
  isLoading.value = true;
  try {
    const [worksRes, genresRes] = await Promise.all([
      axios.get('/api/catalog/works', {
        params: {
          q: searchQuery.value || undefined,
          genre: selectedGenre.value !== 'all' ? selectedGenre.value : undefined,
        }
      }),
      axios.get('/api/catalog/genres')
    ]);

    works.value = worksRes.data.catalog_results || [];
    googleWorks.value = worksRes.data.google_results || [];
    availableGenres.value = genresRes.data;

    works.value.forEach(w => {
      if (w.editions && w.editions.length > 0 && !selectedEditionIds[w.id]) {
        selectedEditionIds[w.id] = w.editions[0].id;
      }
    });
  } catch (error) {
    console.error("Erreur chargement catalogue", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchCatalog);

const debouncedFetch = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    fetchCatalog();
  }, 400);
};

const clearSearch = () => {
  searchQuery.value = '';
  fetchCatalog();
};

const setGenre = (g) => {
  selectedGenre.value = g;
  fetchCatalog();
};

const filteredWorks = computed(() => works.value);

const getSelectedEdition = (work) => {
  if (!work.editions || work.editions.length === 0) return null;
  const id = selectedEditionIds[work.id];
  return work.editions.find(e => e.id === id) || work.editions[0];
};

const openAddToLibrary = (work, edition) => {
  selectedWorkForAction.value = work;
  selectedEditionForAction.value = edition || (work.editions ? work.editions[0] : null);
  addToLibModalOpen.value = true;
};

const openAddEdition = (work) => {
  selectedWorkForAction.value = work;
  addEditionModalOpen.value = true;
};

const importGoogleWorkAndOpenLibrary = async (gWork) => {
  if (importingWork.value) return;
  importingWork.value = true;
  try {
    const payload = {
      title: gWork.title,
      author: gWork.author || 'Inconnu',
      original_title: null,
      genres: gWork.categories || [],
      edition: {
        format: 'Broché',
        publisher: gWork.publisher || null,
        publication_date: gWork.publishedDate || null,
        isbn: gWork.isbn || null,
        cover_url: gWork.thumbnail || null,
        summary: gWork.description || null,
        language: gWork.language || 'fr'
      }
    };
    const response = await axios.post('/api/catalog/works', payload);
    const newWork = response.data;
    works.value.unshift(newWork);
    googleWorks.value = googleWorks.value.filter(gw => gw.google_id !== gWork.google_id);
    openAddToLibrary(newWork, newWork.editions && newWork.editions.length > 0 ? newWork.editions[0] : null);
  } catch(e) {
    console.error(e);
    alert("Erreur lors de l'import");
  } finally {
    importingWork.value = false;
  }
};

const openEditWork = (work) => {
  selectedWorkForAction.value = work;
  editWorkModalOpen.value = true;
};

const openEditEdition = (work, edition) => {
  selectedWorkForAction.value = work;
  selectedEditionForAction.value = edition;
  editEditionModalOpen.value = true;
};

const onReadingAdded = () => {
  fetchCatalog();
};

const onEditionCreated = (newEdition) => {
  fetchCatalog();
};

const onWorkUpdated = (updatedWork) => {
  const idx = works.value.findIndex(w => w.id === updatedWork.id);
  if (idx !== -1) {
    works.value[idx] = updatedWork;
  } else {
    fetchCatalog();
  }
};

const onWorkDeleted = (deletedWorkId) => {
  works.value = works.value.filter(w => w.id !== deletedWorkId);
};

const onEditionUpdated = (updatedEdition) => {
  fetchCatalog();
};

const onEditionDeleted = (deletedEditionId) => {
  fetchCatalog();
};
</script>

<style scoped>
.catalog-page {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding-bottom: 4rem;
}

.catalog-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 2rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.page-title {
  font-size: 2.3rem;
  margin: 0 0 0.4rem;
  color: var(--color-primary);
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.page-subtitle {
  color: var(--color-text-muted);
  font-style: italic;
  font-size: 1.15rem;
  margin: 0;
  max-width: 800px;
}

.btn-create-work {
  background: transparent;
  color: var(--color-primary);
  border: 1px solid var(--color-primary);
  text-decoration: none;
  padding: 0.5rem 1.2rem;
  border-radius: var(--border-radius, 8px);
  font-weight: 600;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
}
.btn-create-work:hover {
  background: var(--color-primary-light);
}

/* Filtres */
.catalog-filters {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
  margin-bottom: 2.5rem;
}

.search-bar {
  position: relative;
  display: flex;
  align-items: center;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 0.5rem 1rem;
  box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.05));
}

.search-icon {
  color: var(--color-text-muted);
  font-size: 1.3rem;
  margin-right: 0.8rem;
}

.search-bar input {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 1.15rem;
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
}

/* Multi-genres chips */
.genres-filter-bar {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  flex-wrap: wrap;
}

.genres-label {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--color-text-muted);
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.genres-tags {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.genre-chip {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 20px;
  padding: 0.3rem 0.8rem;
  font-size: 0.9rem;
  color: var(--color-text);
  font-family: inherit;
  cursor: pointer;
  transition: all 0.2s;
}

.genre-chip:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

.genre-chip.active {
  background: var(--color-primary);
  color: var(--color-bg);
  border-color: var(--color-primary);
  font-weight: 600;
}

/* Works Grid / Cards */
.works-grid {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.work-card {
  display: flex;
  gap: 2rem;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--border-radius, 12px);
  padding: 1.8rem;
  box-shadow: var(--shadow-card);
  transition: transform 0.2s, box-shadow 0.2s;
}

.work-cover-col {
  flex: 0 0 160px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.8rem;
}

.work-cover {
  width: 100%;
  height: 240px;
  object-fit: cover;
  border-radius: 8px;
  box-shadow: var(--shadow-card);
  border: 1px solid var(--color-border);
}

.format-pill {
  background: var(--color-primary);
  color: var(--color-bg);
  font-size: 0.8rem;
  font-weight: 600;
  padding: 0.2rem 0.6rem;
  border-radius: 12px;
}

.work-content-col {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.work-header-info {
  margin-bottom: 0.8rem;
}

.title-row {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  flex-wrap: wrap;
  margin-bottom: 0.2rem;
}

.work-title {
  margin: 0;
  font-size: 1.7rem;
  color: var(--color-text);
}

.in-library-badge {
  font-size: 0.8rem;
  background: rgba(46, 125, 50, 0.15);
  color: #2e7d32;
  padding: 0.2rem 0.6rem;
  border-radius: 12px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.btn-edit-work {
  background: none;
  border: 1px solid var(--color-border);
  color: var(--color-text-muted);
  font-size: 0.8rem;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.3rem;
  transition: all 0.2s;
  font-family: inherit;
}
.btn-edit-work:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
  background: var(--color-bg);
}

.work-author {
  margin: 0 0 0.3rem;
  font-size: 1.15rem;
  color: var(--color-text-muted);
  font-style: italic;
}

.work-series {
  margin: 0;
  font-size: 0.95rem;
  color: var(--color-primary);
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

/* Genres */
.work-genres {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
  margin-bottom: 1.2rem;
}

.genre-tag {
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  padding: 0.15rem 0.55rem;
  border-radius: 10px;
  font-size: 0.8rem;
  color: var(--color-text-muted);
}

/* Editions Section */
.editions-section {
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 1.2rem;
  margin-bottom: 1.5rem;
}

.editions-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.8rem;
}

.editions-title {
  font-size: 0.9rem;
  font-weight: 700;
  color: var(--color-text);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn-add-edition-inline {
  background: none;
  border: 1px dashed var(--color-primary);
  color: var(--color-primary);
  font-size: 0.8rem;
  font-weight: 600;
  padding: 0.2rem 0.6rem;
  border-radius: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.2rem;
  transition: background 0.2s;
}
.btn-add-edition-inline:hover {
  background: var(--color-primary-light);
}

.editions-selector-list {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}

.edition-tab-btn {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 6px;
  padding: 0.4rem 0.7rem;
  font-size: 0.88rem;
  color: var(--color-text);
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-family: inherit;
}

.edition-tab-btn:hover {
  border-color: var(--color-primary);
}

.edition-tab-btn.active {
  background: var(--color-primary);
  color: var(--color-bg);
  border-color: var(--color-primary);
  font-weight: 600;
}

.edition-meta-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.6rem;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.edition-meta {
  font-size: 0.9rem;
  color: var(--color-text-muted);
  margin: 0;
}

.btn-edit-edition {
  background: none;
  border: 1px solid var(--color-border);
  color: var(--color-text-muted);
  font-size: 0.78rem;
  padding: 0.15rem 0.5rem;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-family: inherit;
}
.btn-edit-edition:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
  background: var(--color-surface);
}

.edition-summary {
  font-size: 0.95rem;
  line-height: 1.5;
  color: var(--color-text);
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Actions */
.work-card-actions {
  margin-top: auto;
  display: flex;
  justify-content: flex-end;
}

.btn-add-to-lib {
  background: var(--color-surface);
  color: var(--color-text);
  border: 1px solid var(--color-border);
  padding: 0.5rem 1.2rem;
  border-radius: var(--border-radius, 8px);
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
  font-family: inherit;
}
.btn-add-to-lib:hover {
  background: var(--color-primary-light);
  color: var(--color-primary);
  border-color: var(--color-primary);
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  border: 1px dashed var(--color-border);
  border-radius: 12px;
  color: var(--color-text-muted);
  font-style: italic;
  font-size: 1.2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
}

.loading-state {
  text-align: center;
  padding: 5rem 0;
  color: var(--color-text-muted);
  font-size: 1.3rem;
  font-style: italic;
}

.loading-pulse {
  animation: pulse 1.5s infinite ease-in-out;
}

@keyframes pulse {
  0% { opacity: 0.5; }
  50% { opacity: 1; }
  100% { opacity: 0.5; }
}

.google-works-section {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 2px dashed var(--color-border);
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--color-text-muted);
  font-size: 1.4rem;
  margin-bottom: 1.5rem;
}

.api-pill {
  background-color: var(--color-text-muted);
}

.btn-import {
  background-color: var(--color-accent);
}
.btn-import:hover:not(:disabled) {
  background-color: #c91940;
}
.btn-import:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .work-card {
    flex-direction: column;
    align-items: center;
  }
  .work-cover-col {
    width: 140px;
  }
  .work-card-actions {
    justify-content: center;
    width: 100%;
  }
  .btn-add-to-lib {
    width: 100%;
    justify-content: center;
  }
}
</style>
