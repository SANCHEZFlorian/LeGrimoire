<template>
  <Teleport to="body">
    <div v-if="visible" class="modal-overlay" @click.self="$emit('close')">
      <div class="modal-card">
        <button class="close-btn" @click="$emit('close')"><Icon name="PhX" weight="bold" /></button>

        <div class="modal-header">
          <h2><Icon name="PhPencilSimple" weight="duotone" /> Modifier l'Œuvre</h2>
          <p class="subtitle">Modifiez les informations universelles partagées pour cette œuvre.</p>
        </div>

        <form @submit.prevent="submit" class="edit-form">
          <div class="form-row">
            <div class="form-group flex-2">
              <label>Titre de l'œuvre *</label>
              <input type="text" v-model="form.title" required />
            </div>
            <div class="form-group flex-2">
              <label>Auteur(s) *</label>
              <input type="text" v-model="form.author" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Titre original</label>
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
            <label>Genres & Thèmes multiples</label>
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
                  placeholder="Ajouter un genre (appuyez sur Entrée)..." 
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

          <div class="modal-actions">
            <button type="button" class="btn-delete" @click="deleteWork">
              <Icon name="PhTrash" weight="bold" /> Supprimer l'œuvre
            </button>
            <div class="action-right">
              <button type="button" class="btn-cancel" @click="$emit('close')">Annuler</button>
              <button type="submit" class="btn-submit" :disabled="isSubmitting">
                <Icon name="PhFloppyDisk" weight="duotone" />
                {{ isSubmitting ? 'Sauvegarde...' : 'Sauvegarder les modifications' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import axios from 'axios';
import Icon from '../Shared/Icon.vue';
import CustomSelect from '../Shared/CustomSelect.vue';

const props = defineProps({
  visible: { type: Boolean, default: false },
  work: { type: Object, default: null },
});

const emit = defineEmits(['close', 'updated', 'deleted']);

const isSubmitting = ref(false);
const newGenreInput = ref('');
const quickGenreSuggestions = [
  'Fantasy', 'Romance', 'Science-Fiction', 'Thriller', 'Policier', 
  'Jeunesse', 'Aventure', 'Historique', 'Manga', 'Contemporain', 'Dystopie'
];

const form = reactive({
  title: '',
  author: '',
  original_title: '',
  audience: '',
  series_name: '',
  series_number: '',
  genres: [],
});

watch(() => props.work, (w) => {
  if (w) {
    form.title = w.title || '';
    form.author = w.author || '';
    form.original_title = w.original_title || '';
    form.audience = w.audience || '';
    form.series_name = w.series_name || '';
    form.series_number = w.series_number || '';
    form.genres = Array.isArray(w.genres) ? [...w.genres] : (w.genres ? [w.genres] : []);
  }
}, { immediate: true });

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

const submit = async () => {
  if (!props.work || isSubmitting.value) return;
  isSubmitting.value = true;

  try {
    const payload = {
      title: form.title.trim(),
      author: form.author.trim(),
      original_title: form.original_title ? form.original_title.trim() : null,
      audience: form.audience || null,
      series_name: form.series_name ? form.series_name.trim() : null,
      series_number: form.series_number ? form.series_number.trim() : null,
      genres: form.genres,
    };

    const response = await axios.put(`/api/catalog/works/${props.work.id}`, payload);
    emit('updated', response.data);
    emit('close');
  } catch (error) {
    console.error("Erreur mise à jour œuvre", error);
    alert("Une erreur est survenue lors de la mise à jour de l'œuvre.");
  } finally {
    isSubmitting.value = false;
  }
};

const deleteWork = async () => {
  if (!props.work) return;
  if (!confirm(`Voulez-vous vraiment supprimer l'œuvre "${props.work.title}" et toutes ses éditions du catalogue ?`)) return;

  try {
    await axios.delete(`/api/catalog/works/${props.work.id}`);
    emit('deleted', props.work.id);
    emit('close');
  } catch (error) {
    console.error("Erreur suppression œuvre", error);
    alert("Erreur lors de la suppression de l'œuvre.");
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
  z-index: 1100;
  backdrop-filter: blur(5px);
}

.modal-card {
  background: var(--color-bg);
  border-radius: var(--border-radius, 12px);
  width: 650px;
  max-width: 95vw;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  box-shadow: var(--shadow-xl, 0 25px 60px rgba(0, 0, 0, 0.35));
  padding: 2rem;
  border: 1px solid var(--color-border);
}

.close-btn {
  position: absolute;
  top: 14px;
  right: 14px;
  background: none;
  border: none;
  font-size: 1.3rem;
  cursor: pointer;
  color: var(--color-text-muted);
  padding: 0.3rem;
  border-radius: 4px;
}
.close-btn:hover { color: var(--color-accent, #e11d48); background: var(--color-surface); }

.modal-header h2 {
  font-size: 1.6rem;
  margin: 0 0 0.2rem;
  color: var(--color-primary);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.subtitle {
  color: var(--color-text-muted);
  font-style: italic;
  font-size: 0.95rem;
  margin: 0 0 1.2rem;
}

.edit-form {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

.form-row {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.form-group {
  flex: 1;
  min-width: 200px;
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.form-group.flex-2 { flex: 2; }
.form-group.flex-1 { flex: 1; }

.form-group label {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--color-text);
}

input[type="text"] {
  padding: 0.6rem 0.8rem;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  font-size: 1rem;
  background: var(--color-bg);
  color: var(--color-text);
  font-family: inherit;
}

input:focus {
  outline: none;
  border-color: var(--color-primary);
}

/* Genres Tags */
.genres-input-container {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.active-genres-list {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
}

.genre-tag-active {
  background: var(--color-primary);
  color: var(--color-bg);
  padding: 0.2rem 0.6rem;
  border-radius: 10px;
  font-size: 0.85rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.btn-remove-tag {
  background: none;
  border: none;
  color: var(--color-bg);
  cursor: pointer;
  font-size: 1rem;
  line-height: 1;
  padding: 0;
}

.genre-entry-row {
  display: flex;
  gap: 0.6rem;
}

.genre-entry-row input {
  flex: 1;
}

.btn-add-tag {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 6px;
  padding: 0.5rem 1rem;
  font-family: inherit;
  font-size: 0.9rem;
  cursor: pointer;
  color: var(--color-text);
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.genre-suggestions {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  flex-wrap: wrap;
  font-size: 0.8rem;
}

.sugg-label {
  color: var(--color-text-muted);
  font-style: italic;
}

.sugg-chip {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 0.15rem 0.45rem;
  font-size: 0.78rem;
  color: var(--color-text-muted);
  cursor: pointer;
  font-family: inherit;
}
.sugg-chip:disabled { opacity: 0.4; cursor: not-allowed; }

.modal-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid var(--color-border);
}

.action-right {
  display: flex;
  gap: 1rem;
}

.btn-delete {
  background: none;
  border: 1px solid var(--color-border);
  color: var(--color-accent, #e11d48);
  padding: 0.6rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  font-family: inherit;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}
.btn-delete:hover {
  background: rgba(225, 29, 72, 0.1);
  border-color: var(--color-accent, #e11d48);
}

.btn-cancel {
  background: none;
  border: 1px solid var(--color-border);
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  cursor: pointer;
  color: var(--color-text-muted);
  font-family: inherit;
}

.btn-submit {
  background: var(--color-primary);
  color: var(--color-bg);
  border: none;
  padding: 0.6rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.2s;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-family: inherit;
}

.btn-submit:hover:not(:disabled) {
  background: var(--color-primary-light);
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
