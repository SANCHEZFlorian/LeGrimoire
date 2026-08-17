<template>
  <div class="reading-container">
    <h1 class="page-title"><Icon name="PhBookmarkSimple" weight="duotone" /> Lectures en cours</h1>

    <div v-if="isLoading" class="loading-state">Chargement...</div>

    <div v-else-if="readings.length === 0" class="empty-state">
      Aucune lecture en cours. Commencez un livre depuis la
      <router-link to="/tbr">pile à lire</router-link> ou depuis <router-link to="/catalog">THE Bibliothèque</router-link> !
    </div>

    <div v-else class="readings-list">
      <div v-for="reading in readings" :key="reading.id" class="reading-card">

        <!-- ══ Bandeau principal (toujours visible) ══ -->
        <div class="card-main">
          <img
            :src="getReadingCover(reading)"
            alt="Couverture"
            class="card-cover"
          />

          <div class="card-center">
            <div class="card-titles">
              <h2>{{ getReadingTitle(reading) }}</h2>
              <p class="author">{{ getReadingAuthor(reading) }}</p>
            </div>
            <div class="progress-row">
              <div class="progress-bar-bg">
                <div
                  class="progress-bar-fill"
                  :style="{ width: getProgressPct(reading) + '%' }"
                ></div>
              </div>
              <span class="pct-label">{{ getProgressPct(reading) }}%</span>
            </div>
          </div>

          <div class="card-right">
            <div class="page-input-group" v-if="reading.format !== 'Audio'">
              <input
                type="number"
                v-model.number="localData[reading.id].current_page"
                :max="getTotalPages(reading) || 9999"
                min="0"
                class="page-input"
                @input="debouncedSaveReading(reading)"
              />
              <span class="pages-total">/ {{ getTotalPages(reading) || '?' }}</span>
            </div>
            <div class="audio-input-group" v-else>
              <input
                type="text"
                v-model="localData[reading.id].listening_hours"
                placeholder="02:30:00"
                class="audio-input"
                @input="debouncedSaveReading(reading)"
              />
            </div>
            <div class="card-actions-row">
              <button
                class="btn-expand"
                :class="{ open: expanded[reading.id] }"
                @click="toggleExpand(reading.id)"
              >
                <Icon v-if="expanded[reading.id]" name="PhCaretUp" weight="bold" />
                <Icon v-else name="PhCaretDown" weight="bold" />
                {{ expanded[reading.id] ? 'Fermer' : 'Détails' }}
              </button>
              <button class="btn-finish" @click="finishReading(reading)" title="Terminer la lecture">
                <Icon name="PhCheckCircle" weight="duotone" />
              </button>
            </div>
            <span v-if="saved[reading.id]" class="saved-msg"><Icon name="PhCheck" weight="bold" /> Sauvegardé</span>
          </div>
        </div>

        <!-- ══ Section dépliable ══ -->
        <Transition name="slide">
          <div v-if="expanded[reading.id]" class="card-detail">

            <!-- Citations -->
            <div class="detail-section">
              <div class="detail-header">
                <h3><Icon name="PhQuotes" weight="duotone" /> Citations</h3>
                <button class="btn-add" @click="addQuote(reading.id)"><Icon name="PhPlus" weight="bold" /> Ajouter</button>
              </div>
              <div
                v-for="(quote, qi) in localData[reading.id].quotes"
                :key="qi"
                class="entry-row"
              >
                <textarea
                  v-model="quote.text"
                  rows="2"
                  placeholder="« La citation... »"
                  class="entry-textarea"
                ></textarea>
                <input
                  v-model="quote.attribution"
                  type="text"
                  placeholder="Perso / chapitre / contexte"
                  class="entry-attribution"
                />
                <button class="btn-remove" @click="removeQuote(reading.id, qi)" title="Supprimer"><Icon name="PhX" weight="bold" /></button>
              </div>
              <p v-if="localData[reading.id].quotes.length === 0" class="empty-hint">
                Aucune citation pour l'instant.
              </p>
            </div>

            <!-- Moments favoris -->
            <div class="detail-section">
              <div class="detail-header">
                <h3><Icon name="PhStar" weight="duotone" /> Moments favoris</h3>
                <button class="btn-add" @click="addMoment(reading.id)"><Icon name="PhPlus" weight="bold" /> Ajouter</button>
              </div>
              <div
                v-for="(moment, mi) in localData[reading.id].favorite_moments"
                :key="mi"
                class="entry-row"
              >
                <textarea
                  v-model="moment.text"
                  rows="2"
                  placeholder="Décrivez le moment..."
                  class="entry-textarea"
                ></textarea>
                <input
                  v-model="moment.attribution"
                  type="text"
                  placeholder="Perso / chapitre / contexte"
                  class="entry-attribution"
                />
                <button class="btn-remove" @click="removeMoment(reading.id, mi)" title="Supprimer"><Icon name="PhX" weight="bold" /></button>
              </div>
              <p v-if="localData[reading.id].favorite_moments.length === 0" class="empty-hint">
                Aucun moment noté pour l'instant.
              </p>
            </div>

            <!-- Notes de lecture -->
            <div class="detail-section">
              <h3><Icon name="PhPencilSimple" weight="duotone" /> Notes de lecture</h3>
              <textarea
                v-model="localData[reading.id].review"
                rows="3"
                placeholder="Vos impressions au fil de la lecture..."
                class="full-textarea"
              ></textarea>
            </div>

            <div class="detail-save">
              <button
                class="btn-save-full"
                :disabled="saving[reading.id]"
                @click="saveReading(reading)"
              >
                <Icon name="PhFloppyDisk" weight="duotone" /> {{ saving[reading.id] ? 'Sauvegarde...' : 'Sauvegarder tout' }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </div>

    <!-- Modale de finalisation -->
    <BookDetailModal
      :visible="modalOpen"
      :reading="selectedReading"
      :ratingSystem="ratingSystem"
      @close="modalOpen = false"
      @saved="onFinished"
      @deleted="onDeleted"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import BookDetailModal from '../Shared/BookDetailModal.vue';
import Icon from '../Shared/Icon.vue';

const placeholder = 'https://via.placeholder.com/128x192.png?text=Pas+de+couverture';
const isLoading = ref(true);
const readings = ref([]);
const ratingSystem = ref('20_points');
const modalOpen = ref(false);
const selectedReading = ref(null);

const localData = reactive({});
const saving = reactive({});
const saved = reactive({});
const expanded = reactive({});

onMounted(async () => {
  try {
    const [readingsRes, prefsRes] = await Promise.all([
      axios.get('/api/readings'),
      axios.get('/api/user/preferences'),
    ]);
    const allReadings = readingsRes.data;
    readings.value = allReadings.filter(r => r.status === 'reading');
    ratingSystem.value = prefsRes.data.rating_system || '20_points';

    readings.value.forEach(r => {
      const rawQuotes = Array.isArray(r.quotes) ? r.quotes : [];
      const quotes = rawQuotes.map(q =>
        typeof q === 'string' ? { text: q, attribution: '' } : { text: q.text || '', attribution: q.attribution || '' }
      );

      const rawMoments = Array.isArray(r.favorite_moments) ? r.favorite_moments : [];
      const moments = rawMoments.map(m =>
        typeof m === 'string' ? { text: m, attribution: '' } : { text: m.text || '', attribution: m.attribution || '' }
      );

      localData[r.id] = {
        current_page: r.current_page || 0,
        listening_hours: r.listening_hours || '',
        review: r.review || '',
        quotes,
        favorite_moments: moments,
      };
      saving[r.id] = false;
      saved[r.id] = false;
      expanded[r.id] = false;
    });
  } catch (e) {
    console.error(e);
  } finally {
    isLoading.value = false;
  }
});

const getReadingCover = (r) => r.edition?.cover_url || r.work?.cover_url || r.book?.cover_url || placeholder;
const getReadingTitle = (r) => r.work?.title || r.book?.title || 'Titre inconnu';
const getReadingAuthor = (r) => r.work?.author || r.book?.author || 'Auteur inconnu';
const getTotalPages = (r) => r.pages_count || r.edition?.default_pages_count || r.book?.pages_count || 0;

const getProgressPct = (reading) => {
  const cur = localData[reading.id]?.current_page || 0;
  const total = getTotalPages(reading);
  if (total === 0) return 0;
  return Math.min(100, Math.round((cur / total) * 100));
};

const toggleExpand = (id) => {
  expanded[id] = !expanded[id];
};

const addQuote = (id) => {
  localData[id].quotes.push({ text: '', attribution: '' });
};
const removeQuote = (id, index) => {
  localData[id].quotes.splice(index, 1);
};

const addMoment = (id) => {
  localData[id].favorite_moments.push({ text: '', attribution: '' });
};
const removeMoment = (id, index) => {
  localData[id].favorite_moments.splice(index, 1);
};

const saveReading = async (reading) => {
  saving[reading.id] = true;
  saved[reading.id] = false;
  try {
    await axios.put(`/api/readings/${reading.id}`, {
      current_page: localData[reading.id].current_page,
      listening_hours: localData[reading.id].listening_hours,
      review: localData[reading.id].review,
      quotes: localData[reading.id].quotes.filter(q => q.text.trim() !== ''),
      favorite_moments: localData[reading.id].favorite_moments.filter(m => m.text.trim() !== ''),
    });
    reading.current_page = localData[reading.id].current_page;
    saved[reading.id] = true;
    setTimeout(() => { saved[reading.id] = false; }, 2500);
  } catch (e) {
    console.error(e);
    alert('Erreur lors de la sauvegarde.');
  } finally {
    saving[reading.id] = false;
  }
};

const debounceTimers = {};
const debouncedSaveReading = (reading) => {
  clearTimeout(debounceTimers[reading.id]);
  debounceTimers[reading.id] = setTimeout(() => {
    saveReading(reading);
  }, 800);
};

const finishReading = (reading) => {
  const clone = JSON.parse(JSON.stringify(reading));
  clone.status = 'finished';
  if (!clone.end_date) clone.end_date = new Date().toISOString().substring(0, 10);
  clone.current_page = localData[reading.id].current_page;
  clone.review = localData[reading.id].review;
  selectedReading.value = clone;
  modalOpen.value = true;
};

const onFinished = (updated) => {
  readings.value = readings.value.filter(r => r.id !== updated.id);
  delete localData[updated.id];
};

const onDeleted = (deletedId) => {
  readings.value = readings.value.filter(r => r.id !== deletedId);
  delete localData[deletedId];
};
</script>

<style scoped>
.reading-container { width: 100%; }
.page-title { font-size: 2rem; margin-bottom: 1.5rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.5rem; }

.loading-state,
.empty-state {
  text-align: center; padding: 3rem; color: var(--color-text-muted); font-size: 1.05rem;
  border: 1px dashed var(--color-border); border-radius: 12px;
}
.empty-state a { color: var(--color-primary); text-decoration: none; font-weight: 600; }

.readings-list { 
  display: grid; 
  grid-template-columns: 1fr; 
  gap: 1rem; 
  align-items: start; 
}

@media (min-width: 1100px) {
  .readings-list {
    grid-template-columns: 1fr 1fr;
  }
}

.reading-card {
  border: 1px solid var(--color-border);
  border-radius: var(--border-radius, 12px);
  overflow: hidden;
  background: var(--color-bg);
}

.card-main {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.85rem 1.2rem;
}

.card-cover {
  width: 52px;
  height: 78px;
  object-fit: cover;
  border-radius: 5px;
  box-shadow: var(--shadow-card);
  flex-shrink: 0;
  border: 1px solid var(--color-border);
}

.card-center {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.card-titles h2 {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: var(--color-text);
}

.author {
  margin: 0;
  font-size: 0.8rem;
  color: var(--color-text-muted);
  font-style: italic;
}

.progress-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.progress-bar-bg {
  flex: 1;
  background: var(--color-border);
  height: 6px;
  border-radius: 3px;
  overflow: hidden;
}

.progress-bar-fill {
  background: var(--color-primary);
  height: 100%;
  transition: width 0.3s;
  border-radius: 3px;
}

.pct-label {
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--color-primary);
  width: 34px;
  text-align: right;
  flex-shrink: 0;
}

.card-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.4rem;
  flex-shrink: 0;
}

.page-input-group {
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.page-input, .audio-input {
  width: 72px;
  padding: 0.3rem 0.4rem;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  font-size: 0.95rem;
  text-align: center;
  background: var(--color-bg);
  color: var(--color-text);
}

.audio-input {
  width: 90px;
}

.page-input:focus, .audio-input:focus {
  outline: none;
  border-color: var(--color-primary);
}

.pages-total { font-size: 0.85rem; color: var(--color-text-muted); }

.card-actions-row {
  display: flex;
  gap: 0.4rem;
  align-items: center;
}

.btn-expand {
  padding: 0.3rem 0.7rem;
  background: none;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  font-size: 0.8rem;
  cursor: pointer;
  color: var(--color-text-muted);
  transition: all 0.2s;
  white-space: nowrap;
  display: flex; align-items: center; gap: 0.3rem;
}
.btn-expand:hover, .btn-expand.open {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

.btn-finish {
  padding: 0.3rem 0.5rem;
  background: none;
  border: 1px solid var(--color-primary);
  border-radius: 6px;
  font-size: 1rem;
  color: var(--color-primary);
  cursor: pointer;
  transition: background 0.2s;
  display: flex; align-items: center; justify-content: center;
}
.btn-finish:hover { background: var(--color-primary-light); color: var(--color-bg); }

.saved-msg {
  font-size: 0.75rem;
  color: var(--color-primary);
  font-weight: 600;
  text-align: right;
  display: flex; align-items: center; gap: 0.2rem;
}

/* Section dépliable */
.card-detail {
  border-top: 1px solid var(--color-border);
  padding: 1.25rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  background: var(--color-surface);
}

.detail-section { display: flex; flex-direction: column; gap: 0.6rem; }

.detail-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.detail-header h3,
.detail-section > h3 {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 700;
  display: flex; align-items: center; gap: 0.4rem; color: var(--color-text);
}

.btn-add {
  padding: 0.25rem 0.8rem;
  background: none;
  border: 1px dashed var(--color-primary);
  border-radius: 20px;
  color: var(--color-primary);
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex; align-items: center; gap: 0.2rem;
}
.btn-add:hover { background: var(--color-bg-light); }

.entry-row {
  display: grid;
  grid-template-columns: 1fr 200px auto;
  gap: 0.5rem;
  align-items: start;
}

.entry-textarea {
  padding: 0.5rem 0.6rem;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  font-size: 0.9rem;
  resize: vertical;
  background: var(--color-bg);
  color: var(--color-text);
  font-family: inherit;
}
.entry-textarea:focus { outline: none; border-color: var(--color-primary); }

.entry-attribution {
  padding: 0.5rem 0.6rem;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  font-size: 0.85rem;
  background: var(--color-bg);
  color: var(--color-text);
}
.entry-attribution:focus { outline: none; border-color: var(--color-primary); }

.btn-remove {
  background: none;
  border: none;
  color: var(--color-accent, #e11d48);
  font-size: 1rem;
  cursor: pointer;
  padding: 0.4rem;
  line-height: 1;
  border-radius: 4px;
  align-self: center;
}
.btn-remove:hover { background: var(--color-surface); }

.empty-hint { font-size: 0.85rem; color: var(--color-text-muted); font-style: italic; margin: 0; }

.full-textarea {
  width: 100%;
  box-sizing: border-box;
  padding: 0.6rem 0.75rem;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  font-size: 0.95rem;
  resize: vertical;
  background: var(--color-bg);
  color: var(--color-text);
  font-family: inherit;
}
.full-textarea:focus { outline: none; border-color: var(--color-primary); }

.detail-save { display: flex; justify-content: flex-end; }
.btn-save-full {
  padding: 0.5rem 1.5rem;
  background: var(--color-primary);
  color: var(--color-bg);
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  display: flex; align-items: center; gap: 0.4rem;
}
.btn-save-full:hover:not(:disabled) { background: var(--color-primary-light); }
.btn-save-full:disabled { opacity: 0.5; cursor: not-allowed; }

.slide-enter-active,
.slide-leave-active { transition: all 0.25s ease; max-height: 1000px; overflow: hidden; }
.slide-enter-from,
.slide-leave-to { max-height: 0; opacity: 0; padding-top: 0; padding-bottom: 0; }

@media (max-width: 600px) {
  .card-main { flex-wrap: wrap; }
  .card-right { flex-direction: row; align-items: center; width: 100%; justify-content: flex-end; }
  .entry-row { grid-template-columns: 1fr auto; }
  .entry-attribution { grid-column: 1; }
}
</style>
