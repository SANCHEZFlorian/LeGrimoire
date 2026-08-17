<template>
  <Teleport to="body">
    <div v-if="visible" class="modal-overlay" @click.self="$emit('close')">
      <div class="modal-card">
        <button class="close-btn" @click="$emit('close')"><Icon name="PhX" weight="bold" /></button>

        <div class="modal-header">
          <h2><Icon name="PhPencilSimple" weight="duotone" /> Modifier l'Édition</h2>
          <p class="subtitle" v-if="work">Édition de : <strong>{{ work.title }}</strong> par {{ work.author }}</p>
        </div>

        <form @submit.prevent="submit" class="edition-form">
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
              <input type="text" v-model="form.publisher" placeholder="Ex: Bayard Jeunesse, Pocket..." />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Date de parution</label>
              <input type="date" v-model="form.publication_date" />
            </div>

            <div class="form-group">
              <label>ISBN / Identifiant</label>
              <input type="text" v-model="form.isbn" placeholder="9782..." />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group" v-if="form.format !== 'Audio'">
              <label>Nombre de pages indicatif</label>
              <input type="number" v-model.number="form.default_pages_count" placeholder="Ex: 680" min="1" />
            </div>
            <div class="form-group" v-else>
              <label>Durée audio indicative</label>
              <input type="text" v-model="form.default_audio_duration" placeholder="Ex: 16:30:00" />
            </div>

            <div class="form-group">
              <label>Langue</label>
              <CustomSelect v-model="form.language" :options="[
                { value: 'fr', label: 'Français' },
                { value: 'en', label: 'Anglais' },
                { value: 'es', label: 'Espagnol' },
                { value: 'de', label: 'Allemand' },
                { value: 'it', label: 'Italien' }
              ]" />
            </div>
          </div>

          <!-- Couverture -->
          <div class="form-group">
            <label>Image de couverture</label>
            <div class="cover-input-tabs">
              <input type="url" v-model="form.cover_url" placeholder="Lien direct vers l'image (https://...)" v-if="!coverPreview" />
              
              <div class="upload-inline">
                <input type="file" ref="fileInput" accept="image/*" @change="onFileChange" style="display: none;" />
                <button type="button" class="btn-secondary" @click="triggerFileInput">
                  <Icon name="PhUploadSimple" weight="bold" /> {{ coverPreview ? 'Remplacer l\'image' : 'Téléverser un nouveau fichier' }}
                </button>
                <button type="button" class="btn-remove" v-if="coverPreview" @click="removeFile">
                  Retirer
                </button>
              </div>

              <div v-if="coverPreview || form.cover_url" class="cover-preview-box">
                <img :src="coverPreview || form.cover_url" alt="Aperçu couverture" class="cover-thumb" />
              </div>
            </div>
          </div>

          <!-- Résumé propre à cette édition -->
          <div class="form-group">
            <label>Résumé / 4ème de couverture de cette édition</label>
            <textarea v-model="form.summary" rows="4" placeholder="Résumé spécifique à cette publication..."></textarea>
          </div>

          <div class="modal-actions">
            <button type="button" class="btn-delete" @click="deleteEdition">
              <Icon name="PhTrash" weight="bold" /> Supprimer l'édition
            </button>
            <div class="action-right">
              <button type="button" class="btn-cancel" @click="$emit('close')">Annuler</button>
              <button type="submit" class="btn-submit" :disabled="isSubmitting">
                <Icon name="PhFloppyDisk" weight="duotone" />
                {{ isSubmitting ? 'Enregistrement...' : 'Sauvegarder l\'édition' }}
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
  edition: { type: Object, default: null },
});

const emit = defineEmits(['close', 'updated', 'deleted']);

const isSubmitting = ref(false);
const fileInput = ref(null);
const coverFile = ref(null);
const coverPreview = ref(null);

const form = reactive({
  format: 'Broché',
  publisher: '',
  publication_date: '',
  isbn: '',
  cover_url: '',
  summary: '',
  language: 'fr',
  default_pages_count: null,
  default_audio_duration: '',
});

watch(() => props.edition, (ed) => {
  if (ed) {
    form.format = ed.format || 'Broché';
    form.publisher = ed.publisher || '';
    form.publication_date = ed.publication_date ? ed.publication_date.substring(0, 10) : '';
    form.isbn = ed.isbn || '';
    form.cover_url = ed.cover_url || '';
    form.summary = ed.summary || '';
    form.language = ed.language || 'fr';
    form.default_pages_count = ed.default_pages_count || null;
    form.default_audio_duration = ed.default_audio_duration || '';
    coverFile.value = null;
    coverPreview.value = null;
  }
}, { immediate: true });

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
  if (!props.edition || isSubmitting.value) return;
  isSubmitting.value = true;

  try {
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('format', form.format);
    if (form.publisher) formData.append('publisher', form.publisher.trim());
    if (form.publication_date) formData.append('publication_date', form.publication_date);
    if (form.isbn) formData.append('isbn', form.isbn.trim());
    if (form.cover_url && !coverFile.value) formData.append('cover_url', form.cover_url.trim());
    if (coverFile.value) formData.append('cover_file', coverFile.value);
    if (form.summary) formData.append('summary', form.summary);
    if (form.language) formData.append('language', form.language);
    if (form.default_pages_count) formData.append('default_pages_count', parseInt(form.default_pages_count));
    if (form.default_audio_duration) formData.append('default_audio_duration', form.default_audio_duration);

    const response = await axios.post(`/api/catalog/editions/${props.edition.id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    emit('updated', response.data);
    emit('close');
  } catch (error) {
    console.error("Erreur lors de la modification de l'édition", error);
    alert("Une erreur est survenue lors de la mise à jour de l'édition.");
  } finally {
    isSubmitting.value = false;
  }
};

const deleteEdition = async () => {
  if (!props.edition) return;
  if (!confirm(`Voulez-vous vraiment supprimer cette édition (${props.edition.format} - ${props.edition.publisher || 'Standard'}) ?`)) return;

  try {
    await axios.delete(`/api/catalog/editions/${props.edition.id}`);
    emit('deleted', props.edition.id);
    emit('close');
  } catch (error) {
    console.error("Erreur suppression édition", error);
    alert("Erreur lors de la suppression de l'édition.");
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

.edition-form {
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

.form-group label {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--color-text);
}

input[type="text"],
input[type="url"],
input[type="number"],
input[type="date"],
textarea {
  padding: 0.6rem 0.8rem;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  font-size: 1rem;
  background: var(--color-bg);
  color: var(--color-text);
  font-family: inherit;
}

input:focus, textarea:focus {
  outline: none;
  border-color: var(--color-primary);
}

.cover-input-tabs {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.upload-inline {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.btn-secondary {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  color: var(--color-text);
  font-family: inherit;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.btn-remove {
  background: none;
  border: none;
  color: var(--color-accent, #e11d48);
  font-size: 0.9rem;
  cursor: pointer;
  text-decoration: underline;
}

.cover-preview-box {
  margin-top: 0.4rem;
}

.cover-thumb {
  width: 70px;
  height: 105px;
  object-fit: cover;
  border-radius: 4px;
  border: 1px solid var(--color-border);
  box-shadow: var(--shadow-card);
}

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
