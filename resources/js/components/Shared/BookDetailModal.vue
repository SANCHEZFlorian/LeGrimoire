<template>
  <Teleport to="body">
    <div v-if="visible" class="modal-overlay" @click.self="$emit('close')">
      <div class="modal-container">
        <button class="close-btn" @click="$emit('close')"><Icon name="PhX" weight="bold" /></button>

        <div class="modal-body" v-if="localReading">
          <!-- Partie gauche : couverture + badges + piment -->
          <div class="modal-left">
            <img
              :src="getCoverUrl()"
              alt="Couverture"
              class="modal-cover"
            />
            <div class="badges">
              <span v-if="localReading.is_favorite" class="badge favorite"><Icon name="PhHeart" weight="fill" /> Favori</span>
              <span v-if="localReading.is_reread" class="badge reread"><Icon name="PhArrowsClockwise" weight="bold" /> Relecture</span>
              <span v-if="localReading.is_dnf" class="badge dnf"><Icon name="PhXCircle" weight="bold" /> DNF</span>
            </div>
            <div class="cover-meta">
              <SpiceRating
                v-model="localReading.spice_rating"
                :editable="true"
              />
            </div>

            <!-- Infos Édition rapides -->
            <div class="edition-summary-card" v-if="getEdition()">
              <div class="ed-card-header">
                <span class="ed-card-title">Édition</span>
                <button type="button" class="btn-quick-edit" @click="openEditEdition" title="Modifier cette édition dans le catalogue">
                  <Icon name="PhPencilSimple" weight="bold" /> Modifier
                </button>
              </div>
              <p class="ed-line"><strong>Format :</strong> {{ localReading.format || getEdition().format || 'Broché' }}</p>
              <p class="ed-line" v-if="getEdition().publisher"><strong>Éditeur :</strong> {{ getEdition().publisher }}</p>
              <p class="ed-line" v-if="getEdition().publication_date"><strong>Parution :</strong> {{ getEdition().publication_date }}</p>
              <p class="ed-line" v-if="getEdition().isbn"><strong>ISBN :</strong> {{ getEdition().isbn }}</p>
            </div>
          </div>

          <!-- Partie droite : détails et formulaire -->
          <div class="modal-right">
            <div class="work-title-header">
              <div>
                <h2 class="book-title">{{ getWorkTitle() }}</h2>
                <p class="book-author">Par {{ getWorkAuthor() }}</p>
              </div>
              <button type="button" class="btn-edit-work-quick" @click="openEditWork" title="Modifier l'œuvre dans THE Bibliothèque">
                <Icon name="PhPencilSimple" weight="bold" /> Modifier l'œuvre
              </button>
            </div>

            <p class="book-series" v-if="getSeriesName()">
              <Icon name="PhBooks" weight="duotone" /> Saga {{ getSeriesName() }}
              <span v-if="getSeriesNumber()"> — Tome {{ getSeriesNumber() }}</span>
            </p>

            <!-- Genres multiples -->
            <div class="genres-tags-row" v-if="getGenres().length > 0">
              <span v-for="g in getGenres()" :key="g" class="genre-pill">{{ g }}</span>
            </div>

            <form @submit.prevent="save" class="detail-form">
              <!-- Statut & Format -->
              <div class="form-row">
                <div class="form-group">
                  <label>Statut</label>
                  <CustomSelect v-model="localReading.status" :options="[
                    { value: 'to_read', label: 'À lire (PAL)' },
                    { value: 'reading', label: 'En cours de lecture' },
                    { value: 'finished', label: 'Terminé' },
                    { value: 'dnf', label: 'DNF (Abandonné)' },
                    { value: 'wishlist', label: 'Wishlist' }
                  ]" />
                </div>

                <div class="form-group">
                  <label>Format de mon exemplaire</label>
                  <CustomSelect v-model="localReading.format" :options="[
                    { value: 'Broché', label: 'Livre broché (Collé)' },
                    { value: 'Relié', label: 'Livre relié (Hardcover)' },
                    { value: 'Poche', label: 'Poche' },
                    { value: 'Numérique', label: 'Numérique (E-book / Liseuse)' },
                    { value: 'Ebook', label: 'E-book' },
                    { value: 'Audio', label: 'Livre audio' }
                  ]" />
                </div>
              </div>

              <!-- Pages du lecteur -->
              <div class="form-row" v-if="localReading.status !== 'wishlist'">
                <div class="form-group" v-if="localReading.format !== 'Audio'">
                  <label>Progression (Pages lues / Total de mon livre)</label>
                  <div class="pages-input-row">
                    <input type="number" v-model.number="localReading.current_page" min="0" placeholder="Page actuelle" />
                    <span class="pages-sep">sur</span>
                    <input type="number" v-model.number="localReading.pages_count" min="1" placeholder="Total pages de mon livre" />
                    <span class="pages-total-lbl">pages</span>
                  </div>
                </div>

                <div class="form-group" v-else>
                  <label>Durée d'écoute (HH:MM:SS)</label>
                  <input type="text" v-model="localReading.listening_hours" placeholder="Ex: 14:30:00" />
                </div>
              </div>

              <!-- Dates de lecture -->
              <div class="form-row">
                <div class="form-group">
                  <label>Date de début</label>
                  <input type="date" v-model="localReading.start_date" />
                </div>
                <div class="form-group">
                  <label>Date de fin</label>
                  <input type="date" v-model="localReading.end_date" />
                </div>
              </div>

              <!-- Note -->
              <div class="form-group">
                <label>Note globale</label>
                <RatingInput
                  v-model="localReading.rating"
                  :system="ratingSystem"
                />
              </div>

              <!-- Priorité (wishlist) -->
              <div class="form-group" v-if="localReading.status === 'wishlist'">
                <label>Priorité d'achat</label>
                <input type="text" v-model="localReading.priority" placeholder="Ex: 🔥 À acheter absolument" />
              </div>

              <!-- Avis -->
              <div class="form-group">
                <label>Avis / Chronique</label>
                <textarea v-model="localReading.review" rows="3" placeholder="Votre avis sur cette lecture..."></textarea>
              </div>

              <!-- Citations multiples -->
              <div class="form-group">
                <div class="multi-header">
                  <label><Icon name="PhQuotes" weight="duotone" /> Citations marquantes</label>
                  <button type="button" class="btn-add-entry" @click="addQuote"><Icon name="PhPlus" weight="bold" /> Ajouter</button>
                </div>
                <div v-for="(q, qi) in localQuotes" :key="qi" class="multi-entry">
                  <textarea v-model="q.text" rows="2" placeholder="« La citation... »"></textarea>
                  <input v-model="q.attribution" type="text" placeholder="Perso / page / chapitre" />
                  <button type="button" class="btn-rm-entry" @click="localQuotes.splice(qi, 1)"><Icon name="PhX" weight="bold" /></button>
                </div>
              </div>

              <!-- Moments favoris multiples -->
              <div class="form-group">
                <div class="multi-header">
                  <label><Icon name="PhStar" weight="duotone" /> Moments favoris</label>
                  <button type="button" class="btn-add-entry" @click="addMoment"><Icon name="PhPlus" weight="bold" /> Ajouter</button>
                </div>
                <div v-for="(m, mi) in localMoments" :key="mi" class="multi-entry">
                  <textarea v-model="m.text" rows="2" placeholder="Décrivez le moment marquant..."></textarea>
                  <input v-model="m.attribution" type="text" placeholder="Perso / chapitre" />
                  <button type="button" class="btn-rm-entry" @click="localMoments.splice(mi, 1)"><Icon name="PhX" weight="bold" /></button>
                </div>
              </div>

              <!-- Résumé de l'édition -->
              <div class="form-group" v-if="getEditionSummary()">
                <label class="summary-label">Résumé de l'édition (4ème de couverture) :</label>
                <div class="edition-summary-text">{{ getEditionSummary() }}</div>
              </div>

              <!-- Toggles -->
              <div class="form-row toggles-row">
                <label class="toggle-label">
                  <input type="checkbox" v-model="localReading.is_favorite" />
                  <Icon name="PhHeart" weight="fill" style="color: var(--color-accent, #ef4444)" /> Coup de cœur (Favori)
                </label>
                <label class="toggle-label" v-if="localReading.status === 'wishlist'">
                  <input type="checkbox" v-model="localReading.is_bought" />
                  <Icon name="PhCheckCircle" weight="bold" style="color: var(--color-primary)" /> Acheté
                </label>
                <label class="toggle-label">
                  <input type="checkbox" v-model="localReading.is_reread" />
                  <Icon name="PhArrowsClockwise" weight="bold" style="color: var(--color-primary)" /> Relecture
                </label>
              </div>

              <div class="form-actions">
                <button type="button" class="btn-delete" @click="deleteReading">
                  <Icon name="PhTrash" weight="bold" /> Supprimer de mon carnet
                </button>
                <button type="submit" class="btn-save" :disabled="isSaving">
                  <Icon name="PhFloppyDisk" weight="duotone" /> {{ isSaving ? 'Sauvegarde...' : 'Sauvegarder' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modale Édition d'Œuvre -->
    <EditWorkModal
      :visible="editWorkOpen"
      :work="localReading?.work || localReading?.book"
      @close="editWorkOpen = false"
      @updated="onWorkUpdated"
    />

    <!-- Modale Édition d'Édition -->
    <EditEditionModal
      :visible="editEditionOpen"
      :work="localReading?.work || localReading?.book"
      :edition="localReading?.edition || localReading?.book"
      @close="editEditionOpen = false"
      @updated="onEditionUpdated"
    />
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import RatingInput from './RatingInput.vue';
import SpiceRating from './SpiceRating.vue';
import Icon from './Icon.vue';
import CustomSelect from './CustomSelect.vue';
import EditWorkModal from '../Catalog/EditWorkModal.vue';
import EditEditionModal from '../Catalog/EditEditionModal.vue';

const placeholderCover = 'https://via.placeholder.com/128x192.png?text=Pas+de+couverture';

const props = defineProps({
  visible: { type: Boolean, default: false },
  reading: { type: Object, default: null },
  ratingSystem: { type: String, default: '20_points' },
});

const emit = defineEmits(['close', 'saved', 'deleted']);

const localReading = ref(null);
const localQuotes = ref([]);
const localMoments = ref([]);
const isSaving = ref(false);

const editWorkOpen = ref(false);
const editEditionOpen = ref(false);

watch(() => props.reading, (newVal) => {
  if (newVal) {
    localReading.value = JSON.parse(JSON.stringify(newVal));
    if (localReading.value.start_date) {
      localReading.value.start_date = localReading.value.start_date.substring(0, 10);
    }
    if (localReading.value.end_date) {
      localReading.value.end_date = localReading.value.end_date.substring(0, 10);
    }
    if (!localReading.value.pages_count) {
      localReading.value.pages_count = localReading.value.edition?.default_pages_count || localReading.value.book?.pages_count || null;
    }
    
    const rawQ = Array.isArray(newVal.quotes) ? newVal.quotes : [];
    localQuotes.value = rawQ.map(q =>
      typeof q === 'string' ? { text: q, attribution: '' } : { text: q.text || '', attribution: q.attribution || '' }
    );
    const rawM = Array.isArray(newVal.favorite_moments) ? newVal.favorite_moments : [];
    localMoments.value = rawM.map(m =>
      typeof m === 'string' ? { text: m, attribution: '' } : { text: m.text || '', attribution: m.attribution || '' }
    );
  } else {
    localReading.value = null;
    localQuotes.value = [];
    localMoments.value = [];
  }
}, { immediate: true });

const getWorkTitle = () => localReading.value?.work?.title || localReading.value?.book?.title || 'Titre inconnu';
const getWorkAuthor = () => localReading.value?.work?.author || localReading.value?.book?.author || 'Auteur inconnu';
const getSeriesName = () => localReading.value?.work?.series_name || localReading.value?.book?.series_name;
const getSeriesNumber = () => localReading.value?.work?.series_number || localReading.value?.book?.series_number;
const getCoverUrl = () => localReading.value?.edition?.cover_url || localReading.value?.work?.cover_url || localReading.value?.book?.cover_url || placeholderCover;
const getEdition = () => localReading.value?.edition || localReading.value?.book;
const getEditionSummary = () => localReading.value?.edition?.summary || localReading.value?.book?.summary;

const getGenres = () => {
  const g = localReading.value?.work?.genres || localReading.value?.book?.genres;
  if (Array.isArray(g)) return g;
  if (typeof g === 'string') return [g];
  if (localReading.value?.book?.genre) return [localReading.value.book.genre];
  return [];
};

const addQuote = () => localQuotes.value.push({ text: '', attribution: '' });
const addMoment = () => localMoments.value.push({ text: '', attribution: '' });

const openEditWork = () => {
  editWorkOpen.value = true;
};

const openEditEdition = () => {
  editEditionOpen.value = true;
};

const onWorkUpdated = (updatedWork) => {
  if (localReading.value) {
    localReading.value.work = updatedWork;
    if (localReading.value.book) {
      localReading.value.book.title = updatedWork.title;
      localReading.value.book.author = updatedWork.author;
      localReading.value.book.genres = updatedWork.genres;
      localReading.value.book.series_name = updatedWork.series_name;
      localReading.value.book.series_number = updatedWork.series_number;
    }
  }
  emit('saved', localReading.value);
};

const onEditionUpdated = (updatedEdition) => {
  if (localReading.value) {
    localReading.value.edition = updatedEdition;
    if (localReading.value.book) {
      localReading.value.book.cover_url = updatedEdition.cover_url;
      localReading.value.book.publisher = updatedEdition.publisher;
      localReading.value.book.summary = updatedEdition.summary;
      localReading.value.book.format = updatedEdition.format;
      localReading.value.book.isbn = updatedEdition.isbn;
    }
  }
  emit('saved', localReading.value);
};

const save = async () => {
  if (!localReading.value || isSaving.value) return;
  isSaving.value = true;

  try {
    const payload = {
      status: localReading.value.status,
      current_page: localReading.value.current_page,
      pages_count: localReading.value.pages_count ? parseInt(localReading.value.pages_count) : null,
      start_date: localReading.value.start_date,
      end_date: localReading.value.end_date,
      rating: localReading.value.rating,
      spice_rating: localReading.value.spice_rating,
      review: localReading.value.review,
      quotes: localQuotes.value.filter(q => q.text.trim() !== ''),
      favorite_moments: localMoments.value.filter(m => m.text.trim() !== ''),
      format: localReading.value.format,
      language: localReading.value.language,
      reading_time_minutes: localReading.value.reading_time_minutes,
      listening_hours: localReading.value.listening_hours,
      audio_duration: localReading.value.listening_hours,
      priority: localReading.value.priority,
      is_favorite: localReading.value.is_favorite,
      is_bought: localReading.value.is_bought,
      is_reread: localReading.value.is_reread,
      is_dnf: localReading.value.is_dnf,
      story_type: localReading.value.story_type,
    };

    const response = await axios.put(`/api/readings/${localReading.value.id}`, payload);
    emit('saved', response.data);
    emit('close');
  } catch (error) {
    console.error("Erreur lors de la sauvegarde", error);
    alert("Une erreur est survenue lors de la sauvegarde.");
  } finally {
    isSaving.value = false;
  }
};

const deleteReading = async () => {
  if (!confirm("Voulez-vous vraiment retirer cet ouvrage de votre bibliothèque ?")) return;
  try {
    await axios.delete(`/api/readings/${localReading.value.id}`);
    emit('deleted', localReading.value.id);
    emit('close');
  } catch (error) {
    console.error("Erreur suppression", error);
    alert("Erreur lors de la suppression.");
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: var(--overlay-bg, rgba(0, 0, 0, 0.65));
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(5px);
}

.modal-container {
  background: var(--color-bg);
  border-radius: var(--border-radius, 12px);
  width: 960px;
  max-width: 95vw;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  box-shadow: var(--shadow-xl, 0 25px 60px rgba(0, 0, 0, 0.35));
  border: 1px solid var(--color-border);
}

.close-btn {
  position: absolute;
  top: 14px;
  right: 16px;
  background: none;
  border: none;
  font-size: 1.4rem;
  cursor: pointer;
  color: var(--color-text-muted);
  z-index: 10;
  padding: 0.4rem;
  border-radius: 4px;
}
.close-btn:hover {
  color: var(--color-accent, #e11d48);
  background: var(--color-surface);
}

.modal-body {
  display: flex;
  padding: 2.2rem;
  gap: 2.2rem;
}

.modal-left {
  flex: 0 0 200px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.modal-cover {
  width: 100%;
  border-radius: 8px;
  box-shadow: var(--shadow-card);
  border: 1px solid var(--color-border);
}

.badges {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
  justify-content: center;
}

.badge {
  font-size: 0.75rem;
  padding: 0.2rem 0.6rem;
  border-radius: 12px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.2rem;
}
.badge.favorite { background: var(--color-surface); color: var(--color-primary); }
.badge.reread { background: var(--color-surface); color: var(--color-primary); }
.badge.dnf { background: var(--color-surface); color: var(--color-accent, #e11d48); }

.edition-summary-card {
  width: 100%;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 0.8rem;
  font-size: 0.82rem;
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.ed-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.3rem;
  border-bottom: 1px solid var(--color-border);
  padding-bottom: 0.2rem;
}

.ed-card-title {
  font-weight: 700;
  text-transform: uppercase;
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

.btn-quick-edit {
  background: none;
  border: none;
  color: var(--color-primary);
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.2rem;
  padding: 0;
}
.btn-quick-edit:hover {
  text-decoration: underline;
}

.ed-line {
  margin: 0;
  color: var(--color-text-muted);
}
.ed-line strong {
  color: var(--color-text);
}

.modal-right {
  flex: 1;
  min-width: 0;
}

.work-title-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 0.2rem;
}

.btn-edit-work-quick {
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
  white-space: nowrap;
}
.btn-edit-work-quick:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
  background: var(--color-surface);
}

.book-title {
  font-size: 1.8rem;
  margin: 0 0 0.2rem;
  font-weight: 700;
  color: var(--color-text);
}

.book-author {
  font-size: 1.2rem;
  color: var(--color-text-muted);
  font-style: italic;
  margin: 0 0 0.3rem;
}

.book-series {
  font-size: 0.95rem;
  color: var(--color-primary);
  margin: 0 0 0.8rem;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.genres-tags-row {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
  margin-bottom: 1.2rem;
}

.genre-pill {
  font-size: 0.78rem;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  padding: 0.15rem 0.55rem;
  border-radius: 10px;
  color: var(--color-text-muted);
}

.detail-form {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

.form-row {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.form-row > .form-group {
  flex: 1;
  min-width: 200px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.form-group label {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--color-text);
}

.pages-input-row {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.pages-input-row input {
  width: 100px;
  text-align: center;
}

.pages-sep, .pages-total-lbl {
  font-size: 0.95rem;
  color: var(--color-text-muted);
  font-style: italic;
}

.form-group input,
.form-group textarea {
  padding: 0.55rem 0.8rem;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  font-size: 1rem;
  background: var(--color-bg);
  color: var(--color-text);
  font-family: inherit;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--color-primary);
}

.summary-label {
  font-size: 0.85rem;
  color: var(--color-text-muted);
}

.edition-summary-text {
  font-size: 0.9rem;
  line-height: 1.45;
  color: var(--color-text);
  background: var(--color-surface);
  padding: 0.8rem;
  border-radius: 6px;
  border: 1px solid var(--color-border);
}

.toggles-row {
  flex-wrap: wrap;
  gap: 1.5rem;
}

.toggle-label {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 500;
  color: var(--color-text);
}

.form-actions {
  margin-top: 0.8rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid var(--color-border);
}

.btn-delete {
  background: none;
  border: 1px solid var(--color-border);
  color: var(--color-accent, #e11d48);
  padding: 0.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-family: inherit;
}
.btn-delete:hover {
  background: rgba(225, 29, 72, 0.1);
  border-color: var(--color-accent, #e11d48);
}

.btn-save {
  padding: 0.65rem 2rem;
  background: var(--color-primary);
  color: var(--color-bg);
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-family: inherit;
}
.btn-save:hover:not(:disabled) {
  background: var(--color-primary-light);
}

.multi-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.4rem;
}

.btn-add-entry {
  padding: 0.2rem 0.7rem;
  background: none;
  border: 1px dashed var(--color-primary);
  border-radius: 20px;
  color: var(--color-primary);
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.2rem;
}

.multi-entry {
  display: grid;
  grid-template-columns: 1fr 180px auto;
  gap: 0.5rem;
  align-items: start;
  margin-bottom: 0.5rem;
}

.btn-rm-entry {
  background: none;
  border: none;
  color: var(--color-accent, #e11d48);
  font-size: 1rem;
  cursor: pointer;
  padding: 0.4rem;
}

@media (max-width: 768px) {
  .modal-body {
    flex-direction: column;
    align-items: center;
  }
  .modal-left {
    width: 150px;
  }
}
</style>
