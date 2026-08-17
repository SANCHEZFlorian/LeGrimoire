<template>
  <Teleport to="body">
    <div v-if="visible" class="modal-overlay" @click.self="$emit('close')">
      <div class="modal-card">
        <button class="close-btn" @click="$emit('close')"><Icon name="PhX" weight="bold" /></button>

        <div class="modal-header">
          <h2><Icon name="PhBookBookmark" weight="duotone" /> Ajouter une Édition</h2>
          <p class="subtitle" v-if="work">Pour l'œuvre : <strong>{{ work.title }}</strong> par {{ work.author }}</p>
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

          <!-- Couverture : URL ou fichier -->
          <div class="form-group">
            <label>Image de couverture</label>
            <div class="cover-input-tabs">
              <input type="url" v-model="form.cover_url" placeholder="Lien direct vers l'image (https://...)" v-if="!coverPreview" />
              
              <div class="upload-inline">
                <input type="file" ref="fileInput" accept="image/*" @change="onFileChange" style="display: none;" />
                <button type="button" class="btn-secondary" @click="triggerFileInput">
                  <Icon name="PhUploadSimple" weight="bold" /> {{ coverPreview ? 'Changer l\'image' : 'Téléverser un fichier' }}
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
            <button type="button" class="btn-cancel" @click="$emit('close')">Annuler</button>
            <button type="submit" class="btn-submit" :disabled="isSubmitting">
              <Icon name="PhPlus" weight="bold" />
              {{ isSubmitting ? 'Enregistrement...' : 'Enregistrer cette édition' }}
            </button>
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

const emit = defineEmits(['close', 'created']);

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
});

watch(() => props.visible, (v) => {
  if (v) {
    form.format = 'Broché';
    form.publisher = '';
    form.publication_date = '';
    form.isbn = '';
    form.cover_url = '';
    form.summary = '';
    form.language = 'fr';
    coverFile.value = null;
    coverPreview.value = null;
  }
});

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
  if (!props.work || isSubmitting.value) return;
  isSubmitting.value = true;

  try {
    const formData = new FormData();
    formData.append('format', form.format);
    if (form.publisher) formData.append('publisher', form.publisher);
    if (form.publication_date) formData.append('publication_date', form.publication_date);
    if (form.isbn) formData.append('isbn', form.isbn);
    if (form.cover_url && !coverFile.value) formData.append('cover_url', form.cover_url);
    if (coverFile.value) formData.append('cover_file', coverFile.value);
    if (form.summary) formData.append('summary', form.summary);
    if (form.language) formData.append('language', form.language);

    const response = await axios.post(`/api/catalog/works/${props.work.id}/editions`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    emit('created', response.data);
    emit('close');
  } catch (error) {
    console.error("Erreur lors de l'ajout d'édition", error);
    alert("Une erreur est survenue lors de l'ajout de l'édition.");
  } finally {
    isSubmitting.value = false;
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
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid var(--color-border);
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
  background: var(--color-surface);
  color: var(--color-text);
  border: 1px solid var(--color-border);
  padding: 0.5rem 1.2rem;
  border-radius: var(--border-radius, 8px);
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-family: inherit;
}

.btn-submit:hover:not(:disabled) {
  background: var(--color-primary-light);
  color: var(--color-primary);
  border-color: var(--color-primary);
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
