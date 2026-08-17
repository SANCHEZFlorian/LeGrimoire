<template>
  <Teleport to="body">
    <div v-if="visible" class="modal-overlay" @click.self="$emit('close')">
      <div class="modal-card">
        <button class="close-btn" @click="$emit('close')"><Icon name="PhX" weight="bold" /></button>

        <div class="modal-header">
          <h2><Icon name="PhBookmarkSimple" weight="duotone" /> Ajouter à ma bibliothèque</h2>
          <p class="subtitle">Personnalisez les détails de votre exemplaire pour votre suivi de lecture.</p>
        </div>

        <div class="book-summary-box" v-if="work && edition">
          <img :src="edition.cover_url || work.cover_url || placeholderCover" alt="Couverture" class="summary-cover" />
          <div class="summary-info">
            <h3 class="summary-title">{{ work.title }}</h3>
            <p class="summary-author">Par {{ work.author }}</p>
            <div class="summary-tags">
              <span class="badge format-badge">{{ edition.format || 'Broché' }}</span>
              <span class="badge" v-if="edition.publisher">{{ edition.publisher }}</span>
              <span class="badge" v-if="work.series_name">{{ work.series_name }} <span v-if="work.series_number">#{{ work.series_number }}</span></span>
            </div>
          </div>
        </div>

        <form @submit.prevent="submit" class="personal-form">
          <!-- Format & Nombre de pages / Durée -->
          <div class="form-row">
            <div class="form-group">
              <label>Format de mon exemplaire</label>
              <CustomSelect v-model="form.format" :options="[
                { value: 'Broché', label: 'Broché (Collé)' },
                { value: 'Relié', label: 'Relié (Hardcover)' },
                { value: 'Poche', label: 'Poche' },
                { value: 'Ebook', label: 'E-book (Liseuse / Numérique)' },
                { value: 'Audio', label: 'Livre audio' }
              ]" />
            </div>

            <div class="form-group" v-if="form.format !== 'Audio'">
              <label>
                Nombre de pages <span class="label-hint">(de votre exemplaire / liseuse)</span>
              </label>
              <input 
                type="number" 
                v-model.number="form.pages_count" 
                placeholder="Ex: 520" 
                min="1" 
                required 
              />
            </div>

            <div class="form-group" v-else>
              <label>Durée audio <span class="label-hint">(HH:MM:SS)</span></label>
              <input 
                type="text" 
                v-model="form.listening_hours" 
                placeholder="Ex: 14:30:00" 
              />
            </div>
          </div>

          <!-- Statut initial & Dates -->
          <div class="form-row">
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

            <div class="form-group" v-if="form.status === 'reading' || form.status === 'finished'">
              <label>Date de début</label>
              <input type="date" v-model="form.start_date" />
            </div>

            <div class="form-group" v-if="form.status === 'finished'">
              <label>Date de fin</label>
              <input type="date" v-model="form.end_date" />
            </div>
          </div>

          <!-- Wishlist Priority -->
          <div class="form-group" v-if="form.status === 'wishlist'">
            <label>Priorité d'achat</label>
            <CustomSelect v-model="form.priority" :options="[
              { value: 'low', label: 'Basse' },
              { value: 'medium', label: 'Moyenne' },
              { value: '🔥 high', label: '🔥 Haute (Prioritaire)' }
            ]" />
          </div>

          <!-- Options -->
          <div class="form-row toggles-row">
            <label class="checkbox-label">
              <input type="checkbox" v-model="form.is_bought" />
              <span>Exemplaire déjà acheté</span>
            </label>
            <label class="checkbox-label">
              <input type="checkbox" v-model="form.is_favorite" />
              <span>Marquer comme favori</span>
            </label>
          </div>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="$emit('close')">Annuler</button>
            <button type="submit" class="btn-submit" :disabled="isSubmitting">
              <Icon name="PhCheckCircle" weight="bold" />
              {{ isSubmitting ? 'Ajout en cours...' : 'Ajouter à ma bibliothèque' }}
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

const placeholderCover = 'https://via.placeholder.com/128x192.png?text=Pas+de+couverture';

const props = defineProps({
  visible: { type: Boolean, default: false },
  work: { type: Object, default: null },
  edition: { type: Object, default: null },
});

const emit = defineEmits(['close', 'added']);

const isSubmitting = ref(false);

const form = reactive({
  format: 'Broché',
  pages_count: null,
  listening_hours: '',
  status: 'to_read',
  start_date: null,
  end_date: null,
  priority: 'medium',
  is_bought: true,
  is_favorite: false,
});

watch([() => props.work, () => props.edition], ([w, ed]) => {
  if (ed) {
    form.format = ed.format || 'Broché';
    form.pages_count = null;
    form.listening_hours = '';
    form.status = 'to_read';
    form.start_date = null;
    form.end_date = null;
    form.is_bought = true;
    form.is_favorite = false;
  }
}, { immediate: true });

const submit = async () => {
  if (isSubmitting.value) return;
  isSubmitting.value = true;

  try {
    const payload = {
      work_id: props.work?.id,
      edition_id: props.edition?.id,
      format: form.format,
      pages_count: form.pages_count ? parseInt(form.pages_count) : null,
      listening_hours: form.listening_hours || null,
      audio_duration: form.listening_hours || null,
      status: form.status,
      start_date: form.start_date || (form.status === 'reading' ? new Date().toISOString().substring(0, 10) : null),
      end_date: form.end_date,
      priority: form.status === 'wishlist' ? form.priority : null,
      is_bought: form.is_bought,
      is_favorite: form.is_favorite,
    };

    const response = await axios.post('/api/readings', payload);
    emit('added', response.data);
    emit('close');
  } catch (error) {
    if (error.response && error.response.status === 409) {
      alert("Ce livre est déjà dans votre bibliothèque !");
    } else {
      console.error("Erreur lors de l'ajout", error);
      alert("Une erreur est survenue lors de l'ajout à votre bibliothèque.");
    }
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
  width: 600px;
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

.book-summary-box {
  display: flex;
  gap: 1.2rem;
  background: var(--color-surface);
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid var(--color-border);
  margin-bottom: 1.5rem;
  align-items: center;
}

.summary-cover {
  width: 60px;
  height: 90px;
  object-fit: cover;
  border-radius: 4px;
  box-shadow: var(--shadow-card);
}

.summary-info {
  flex: 1;
}

.summary-title {
  margin: 0 0 0.2rem;
  font-size: 1.2rem;
  color: var(--color-text);
}

.summary-author {
  margin: 0 0 0.5rem;
  font-size: 0.95rem;
  color: var(--color-text-muted);
  font-style: italic;
}

.summary-tags {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
}

.badge {
  font-size: 0.75rem;
  padding: 0.2rem 0.5rem;
  border-radius: 10px;
  background: var(--color-bg);
  color: var(--color-text-muted);
  border: 1px solid var(--color-border);
}

.format-badge {
  background: var(--color-primary);
  color: var(--color-bg);
  border: none;
  font-weight: 600;
}

.personal-form {
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

.label-hint {
  font-weight: normal;
  font-size: 0.8rem;
  color: var(--color-text-muted);
  font-style: italic;
}

input[type="text"],
input[type="number"],
input[type="date"] {
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

.toggles-row {
  display: flex;
  gap: 1.5rem;
  margin-top: 0.5rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  cursor: pointer;
  font-size: 0.95rem;
  color: var(--color-text);
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
