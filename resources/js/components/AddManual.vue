<template>
  <div class="add-manual-page">
    <h1 class="page-title"><Icon name="PhFeather" weight="duotone" /> Enluminer une Nouvelle Œuvre</h1>
    <p class="intro-text">
      Inscrivez une œuvre et son édition dans les archives de <strong>THE Bibliothèque</strong>, puis ajoutez votre exemplaire personnalisé à votre carnet.
    </p>

    <form @submit.prevent="submit" class="manual-form">
      
      <!-- ══ BLOC 1 : L'Œuvre Universelle ══ -->
      <div class="form-card-section">
        <h2 class="section-title"><Icon name="PhBookOpen" weight="duotone" /> 1. L'Œuvre Universelle</h2>
        <p class="section-desc">Informations partagées de l'œuvre (communes à toutes les éditions).</p>

        <div class="form-row">
          <div class="form-group flex-2">
            <label>Titre de l'œuvre *</label>
            <input type="text" v-model="form.title" placeholder="Ex: Eragon" required />
          </div>
          <div class="form-group flex-2">
            <label>Auteur(s) *</label>
            <input type="text" v-model="form.author" placeholder="Ex: Christopher Paolini" required />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Titre original <span class="hint">(optionnel)</span></label>
            <input type="text" v-model="form.original_title" placeholder="Ex: Eragon" />
          </div>
          <div class="form-group">
            <label>Public cible</label>
            <CustomSelect v-model="form.audience" :options="[
              { value: '', label: 'Non spécifié' },
              { value: 'Jeunesse', label: 'Jeunesse' },
              { value: 'Young Adult', label: 'Young Adult (YA)' },
              { value: 'New Adult', label: 'New Adult' },
              { value: 'Adulte', label: 'Adulte' }
            ]" />
          </div>
        </div>

        <!-- Saga / Série -->
        <div class="form-row">
          <div class="form-group flex-2">
            <label>Nom de la saga / série</label>
            <input type="text" v-model="form.series_name" placeholder="Ex: L'Héritage" />
          </div>
          <div class="form-group flex-1">
            <label>Numéro de tome</label>
            <input type="text" v-model="form.series_number" placeholder="Ex: 1" />
          </div>
        </div>

        <!-- Genres multiples interactifs -->
        <div class="form-group">
          <label>Genres & Thèmes multiples <span class="hint">(appuyez sur Entrée pour ajouter un genre)</span></label>
          <div class="genres-input-container">
            <div class="active-genres-list" v-if="form.genres.length > 0">
              <span v-for="(g, gi) in form.genres" :key="gi" class="genre-tag-active">
                {{ g }}
                <button type="button" class="btn-remove-tag" @click="removeGenre(gi)">×</button>
              </span>
            </div>
            <div class="genre-entry-row">
              <input 
                type="text" 
                v-model="newGenreInput" 
                placeholder="Ajouter un genre (ex: Fantasy, Aventure, Dragon...)" 
                @keydown.enter.prevent="addGenre"
              />
              <button type="button" class="btn-add-tag" @click="addGenre">
                <Icon name="PhPlus" weight="bold" /> Ajouter
              </button>
            </div>
            
            <!-- Suggestions rapides -->
            <div class="genre-suggestions">
              <span class="sugg-label">Suggestions :</span>
              <button 
                type="button" 
                v-for="sugg in quickGenreSuggestions" 
                :key="sugg" 
                class="sugg-chip"
                @click="addSuggestedGenre(sugg)"
                :disabled="form.genres.includes(sugg)"
              >
                + {{ sugg }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ══ BLOC 2 : L'Édition Spécifique ══ -->
      <div class="form-card-section">
        <h2 class="section-title"><Icon name="PhBookBookmark" weight="duotone" /> 2. L'Édition</h2>
        <p class="section-desc">Détails de la publication (éditeur, format, couverture et résumé propres à cette version).</p>

        <div class="form-row">
          <div class="form-group">
            <label>Format de l'édition</label>
            <CustomSelect v-model="form.format" :options="[
              { value: 'Broché', label: 'Broché (Collé)' },
              { value: 'Relié', label: 'Relié (Hardcover / Collector)' },
              { value: 'Poche', label: 'Poche' },
              { value: 'Ebook', label: 'E-book / Numérique' },
              { value: 'Audio', label: 'Livre audio' }
            ]" />
          </div>

          <div class="form-group">
            <label>Maison d'édition / Éditeur</label>
            <input type="text" v-model="form.publisher" placeholder="Ex: Bayard Jeunesse, Pocket, Audiolib..." />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Date de parution de l'édition</label>
            <input type="date" v-model="form.publication_date" />
          </div>

          <div class="form-group">
            <label>ISBN / Identifiant</label>
            <input type="text" v-model="form.isbn" placeholder="Ex: 9782747014403" />
          </div>
        </div>

        <!-- Couverture -->
        <div class="form-group">
          <label>Couverture de cette édition</label>
          <div class="cover-row">
            <input 
              type="file" 
              ref="fileInput" 
              accept="image/*" 
              @change="onFileChange" 
              style="display: none;" 
            />
            
            <div v-if="coverPreview" class="cover-preview-wrapper">
              <img :src="coverPreview" alt="Aperçu couverture" class="cover-preview" />
              <button type="button" class="btn-remove-file" @click="removeFile">Retirer l'image</button>
            </div>
            
            <div v-else class="cover-buttons-group">
              <input type="url" v-model="form.cover_url" placeholder="Lien URL de l'image (https://...)" class="cover-url-input" />
              <button type="button" class="btn-upload" @click="triggerFileInput">
                <Icon name="PhUploadSimple" weight="bold" /> Téléverser un fichier
              </button>
            </div>
          </div>
        </div>

        <!-- Résumé propre à l'édition -->
        <div class="form-group">
          <label>Résumé / 4ème de couverture de cette édition</label>
          <textarea v-model="form.summary" rows="4" placeholder="Résumé tel qu'il figure sur cette édition..."></textarea>
        </div>
      </div>

      <!-- ══ BLOC 3 : Mon Exemplaire (Lecteur) ══ -->
      <div class="form-card-section reader-section">
        <h2 class="section-title"><Icon name="PhBookmarkSimple" weight="duotone" /> 3. Mon Exemplaire & Ma Lecture</h2>
        <p class="section-desc">Vos informations personnelles de lecteur pour le suivi dans votre carnet.</p>

        <div class="form-row">
          <div class="form-group" v-if="form.format !== 'Audio'">
            <label>
              Nombre de pages de mon livre <span class="hint">(exact sur mon livre/liseuse) *</span>
            </label>
            <input type="number" v-model.number="form.pages_count" placeholder="Ex: 680" min="1" required />
          </div>
          <div class="form-group" v-else>
            <label>Durée audio de mon livre <span class="hint">(HH:MM:SS)</span></label>
            <input type="text" v-model="form.listening_hours" placeholder="Ex: 16:30:00" />
          </div>

          <div class="form-group">
            <label>Statut dans ma bibliothèque</label>
            <CustomSelect v-model="form.status" :options="[
              { value: 'to_read', label: 'À lire (Dans ma PAL)' },
              { value: 'reading', label: 'En cours de lecture' },
              { value: 'finished', label: 'Déjà lu (Terminé)' },
              { value: 'wishlist', label: 'Wishlist (Envie d\'achat)' },
              { value: 'dnf', label: 'DNF (Abandonné)' }
            ]" />
          </div>
        </div>

        <div class="form-row" v-if="form.status === 'reading' || form.status === 'finished'">
          <div class="form-group">
            <label>Date de début</label>
            <input type="date" v-model="form.start_date" />
          </div>
          <div class="form-group" v-if="form.status === 'finished'">
            <label>Date de fin</label>
            <input type="date" v-model="form.end_date" />
          </div>
        </div>
      </div>

      <!-- Bouton de validation -->
      <div class="form-submit-row">
        <button type="submit" class="btn-stamp" :disabled="!isFormValid || isSaving">
          <Icon name="PhFeather" weight="duotone" />
          {{ isSaving ? 'Gravure en cours...' : 'Graver dans les registres & Ajouter à mon carnet' }}
        </button>
      </div>

    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import Icon from './Shared/Icon.vue';
import CustomSelect from './Shared/CustomSelect.vue';

const router = useRouter();

const isSaving = ref(false);
const fileInput = ref(null);
const coverFile = ref(null);
const coverPreview = ref(null);

const newGenreInput = ref('');
const quickGenreSuggestions = [
  'Fantasy', 'Romance', 'Science-Fiction', 'Thriller', 'Policier', 
  'Jeunesse', 'Aventure', 'Historique', 'Manga', 'Contemporain', 'Dystopie'
];

const form = reactive({
  // Œuvre
  title: '',
  author: '',
  original_title: '',
  audience: '',
  series_name: '',
  series_number: '',
  genres: ['Fantasy'],

  // Édition
  format: 'Broché',
  publisher: '',
  publication_date: '',
  isbn: '',
  cover_url: '',
  summary: '',
  language: 'fr',

  // Mon Exemplaire
  pages_count: null,
  listening_hours: '',
  status: 'to_read',
  start_date: null,
  end_date: null,
});

const isFormValid = computed(() => {
  return form.title.trim().length > 0 && 
         form.author.trim().length > 0 && 
         (form.format === 'Audio' || (form.pages_count && form.pages_count > 0));
});

const addGenre = () => {
  const g = newGenreInput.value.trim();
  if (g && !form.genres.includes(g)) {
    form.genres.push(g);
  }
  newGenreInput.value = '';
};

const addSuggestedGenre = (sugg) => {
  if (!form.genres.includes(sugg)) {
    form.genres.push(sugg);
  }
};

const removeGenre = (index) => {
  form.genres.splice(index, 1);
};

const triggerFileInput = () => fileInput.value?.click();

const onFileChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  coverFile.value = file;
  const reader = new FileReader();
  reader.onload = (ev) => { coverPreview.value = ev.target.result; };
  reader.readAsDataURL(file);
};

const removeFile = () => {
  coverFile.value = null;
  coverPreview.value = null;
  if (fileInput.value) fileInput.value.value = '';
};

const submit = async () => {
  if (!isFormValid.value || isSaving.value) return;
  isSaving.value = true;

  try {
    const formData = new FormData();
    
    // Œuvre
    formData.append('title', form.title.trim());
    formData.append('author', form.author.trim());
    if (form.original_title) formData.append('original_title', form.original_title.trim());
    if (form.audience) formData.append('audience', form.audience);
    if (form.series_name) formData.append('series_name', form.series_name.trim());
    if (form.series_number) formData.append('series_number', form.series_number.trim());
    form.genres.forEach((g, idx) => {
      formData.append(`genres[${idx}]`, g);
    });

    // Édition
    formData.append('format', form.format);
    if (form.publisher) formData.append('publisher', form.publisher.trim());
    if (form.publication_date) formData.append('publication_date', form.publication_date);
    if (form.isbn) formData.append('isbn', form.isbn.trim());
    if (form.summary) formData.append('summary', form.summary);
    if (form.language) formData.append('language', form.language);
    if (form.cover_url && !coverFile.value) formData.append('cover_url', form.cover_url);
    if (coverFile.value) formData.append('cover_file', coverFile.value);

    // Mon Exemplaire
    if (form.pages_count) formData.append('pages_count', parseInt(form.pages_count));
    if (form.listening_hours) formData.append('listening_hours', form.listening_hours);
    formData.append('status', form.status);
    if (form.start_date) formData.append('start_date', form.start_date);
    if (form.end_date) formData.append('end_date', form.end_date);

    await axios.post('/api/readings', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    router.push('/tracker');
  } catch (error) {
    if (error.response && error.response.status === 409) {
      alert("Ce livre est déjà dans votre bibliothèque.");
      router.push('/tracker');
    } else {
      console.error("Erreur lors de la création", error);
      alert("Une erreur est survenue lors de l'enregistrement de l'ouvrage.");
    }
  } finally {
    isSaving.value = false;
  }
};
</script>

<style scoped>
.add-manual-page {
  max-width: 850px;
  margin: 0 auto;
  padding: 1rem 0 4rem;
}

.page-title {
  font-size: 2.3rem;
  margin-bottom: 0.5rem;
  color: var(--color-primary);
  display: flex;
  align-items: center;
  gap: 0.6rem;
  justify-content: center;
  text-align: center;
}

.intro-text {
  text-align: center;
  font-size: 1.2rem;
  color: var(--color-text-muted);
  font-style: italic;
  margin-bottom: 2.5rem;
}

.manual-form {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.form-card-section {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--border-radius, 12px);
  padding: 2rem;
  box-shadow: var(--shadow-card);
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

.reader-section {
  border-left: 4px solid var(--color-primary);
}

.section-title {
  font-size: 1.4rem;
  margin: 0 0 0.2rem;
  color: var(--color-primary);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.section-desc {
  margin: 0 0 0.8rem;
  font-size: 0.95rem;
  color: var(--color-text-muted);
  font-style: italic;
}

.form-row {
  display: flex;
  gap: 1.2rem;
  flex-wrap: wrap;
}

.form-group {
  flex: 1;
  min-width: 220px;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.form-group.flex-2 { flex: 2; }
.form-group.flex-1 { flex: 1; }

.form-group label {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--color-text);
}

.hint {
  font-weight: normal;
  font-size: 0.8rem;
  color: var(--color-text-muted);
  font-style: italic;
}

input[type="text"],
input[type="url"],
input[type="number"],
input[type="date"],
textarea {
  padding: 0.7rem 0.9rem;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  font-size: 1.05rem;
  background: var(--color-bg);
  color: var(--color-text);
  font-family: inherit;
}

input:focus, textarea:focus {
  outline: none;
  border-color: var(--color-primary);
}

/* Genres Tags */
.genres-input-container {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.active-genres-list {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.genre-tag-active {
  background: var(--color-primary);
  color: var(--color-bg);
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.btn-remove-tag {
  background: none;
  border: none;
  color: var(--color-bg);
  cursor: pointer;
  font-size: 1.1rem;
  line-height: 1;
  padding: 0;
}

.genre-entry-row {
  display: flex;
  gap: 0.8rem;
}

.genre-entry-row input {
  flex: 1;
}

.btn-add-tag {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 0.6rem 1.2rem;
  font-family: inherit;
  font-size: 0.95rem;
  cursor: pointer;
  color: var(--color-text);
  display: flex;
  align-items: center;
  gap: 0.3rem;
  transition: all 0.2s;
}

.btn-add-tag:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

.genre-suggestions {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  flex-wrap: wrap;
  font-size: 0.85rem;
}

.sugg-label {
  color: var(--color-text-muted);
  font-style: italic;
}

.sugg-chip {
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: 10px;
  padding: 0.2rem 0.5rem;
  font-size: 0.8rem;
  color: var(--color-text-muted);
  cursor: pointer;
  font-family: inherit;
  transition: all 0.2s;
}

.sugg-chip:hover:not(:disabled) {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

.sugg-chip:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Couverture */
.cover-row {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.cover-buttons-group {
  display: flex;
  gap: 0.8rem;
  flex-wrap: wrap;
}

.cover-url-input {
  flex: 1;
  min-width: 250px;
}

.btn-upload {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  font-family: inherit;
  font-size: 0.95rem;
  cursor: pointer;
  color: var(--color-text);
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.cover-preview-wrapper {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.cover-preview {
  width: 70px;
  height: 105px;
  object-fit: cover;
  border-radius: 4px;
  box-shadow: var(--shadow-card);
  border: 1px solid var(--color-border);
}

.btn-remove-file {
  background: none;
  border: none;
  color: var(--color-accent, #e11d48);
  font-size: 0.95rem;
  cursor: pointer;
  text-decoration: underline;
}

/* Submit */
.form-submit-row {
  display: flex;
  justify-content: center;
  margin-top: 1rem;
}

.btn-stamp {
  background: var(--color-surface);
  color: var(--color-text);
  border: 1px solid var(--color-border);
  padding: 0.75rem 1.8rem;
  border-radius: var(--border-radius, 10px);
  font-weight: 600;
  font-size: 1.05rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-family: inherit;
}

.btn-stamp:hover:not(:disabled) {
  background: var(--color-primary-light);
  color: var(--color-primary);
  border-color: var(--color-primary);
  transform: translateY(-2px);
}

.btn-stamp:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}
</style>
