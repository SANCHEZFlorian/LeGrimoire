<template>
  <div class="settings-container">
    <h1 class="page-title"><Icon name="PhGearSix" weight="duotone" /> Paramètres</h1>

    <div v-if="isLoading" class="loading-state">Chargement...</div>

    <div v-else class="settings-content">
      
      <!-- Thème et Design -->
      <section class="setting-section">
        <h2>Apparence & Thème</h2>
        <p class="setting-description">Personnalisez l'esthétique de votre carnet.</p>
        
        <div class="theme-options">
          <div class="form-group">
            <label>Mode du thème</label>
            <CustomSelect v-model="form.theme_mode" :options="[
              { value: 'auto', label: 'Automatique (Saisons auto)' },
              { value: 'fixed', label: 'Fixé (Toujours le même)' },
              { value: 'chronicle_auto', label: 'Fixé, Saisons auto sur Chronique' }
            ]" />
          </div>

          <div class="form-group">
            <label>Thème principal</label>
            <CustomSelect v-model="form.theme_name" :options="[
              { value: 'grimoire', label: '🏰 Grimoire' },
              { value: 'moderne', label: '⚡ Moderne' },
              { value: 'romantique', label: '🌹 Romantique' },
              { value: 'cottagecore', label: '🏡 Cottagecore' },
              { value: 'dark-academia', label: '📜 Dark Academia' },
              { value: 'florale', label: '🌸 Florale' },
              { value: 'nature', label: '🌲 Nature' },
              { value: 'animaux', label: '🦊 Animaux' }
            ]" />
          </div>

          <div class="form-group" v-if="form.theme_mode !== 'auto'">
            <label>Saison (Variations)</label>
            <CustomSelect v-model="form.fixed_season" :options="[
              { value: 'printemps', label: '🌱 Printemps' },
              { value: 'ete', label: '☀️ Été' },
              { value: 'automne', label: '🍂 Automne' },
              { value: 'hiver', label: '❄️ Hiver' }
            ]" />
          </div>
        </div>
      </section>

      <!-- Système de notation -->
      <section class="setting-section">
        <h2>Système de notation</h2>
        <p class="setting-description">Choisissez comment vous souhaitez noter vos livres.</p>
        <div class="rating-options">
          <label
            v-for="opt in ratingOptions"
            :key="opt.value"
            class="rating-option"
            :class="{ selected: form.rating_system === opt.value }"
          >
            <input type="radio" v-model="form.rating_system" :value="opt.value" />
            <div class="option-content">
              <span class="option-icon"><Icon :name="opt.icon" weight="duotone" /></span>
              <span class="option-label">{{ opt.label }}</span>
              <span class="option-desc">{{ opt.description }}</span>
            </div>
          </label>
        </div>
      </section>

      <!-- Objectif annuel -->
      <section class="setting-section">
        <h2>Objectif annuel</h2>
        <p class="setting-description">Combien de livres souhaitez-vous lire cette année ?</p>
        <div class="goal-input-wrapper">
          <input
            type="number"
            v-model.number="form.reading_goal_annual"
            min="1"
            max="999"
            placeholder="Ex: 24"
            class="goal-input"
          />
          <span class="goal-suffix">livres</span>
        </div>
      </section>

      <!-- Bouton sauvegarder -->
      <div class="save-section">
        <button @click="save" class="btn-save" :disabled="isSaving">
          {{ isSaving ? 'Sauvegarde...' : 'Sauvegarder les préférences' }}
        </button>
        <span v-if="savedMsg" class="saved-msg">{{ savedMsg }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import Icon from '../Shared/Icon.vue';
import CustomSelect from '../Shared/CustomSelect.vue';
import { useTheme } from '../../composables/useTheme';

const { updateTheme } = useTheme();

const isLoading = ref(true);
const isSaving = ref(false);
const savedMsg = ref('');

const form = reactive({
  rating_system: '20_points',
  reading_goal_annual: null,
  theme_mode: 'auto',
  theme_name: 'grimoire',
  fixed_season: 'printemps'
});

const ratingOptions = [
  { value: '5_stars', icon: 'PhStar', label: '5 étoiles', description: 'Pas de 0.5 — Ex: 4 étoiles' },
  { value: '10_points', icon: 'PhNumberCircleOne', label: 'Note sur 10', description: 'Pas de 0.5 — Ex: 8.5/10' },
  { value: '20_points', icon: 'PhPencilLine', label: 'Note sur 20', description: 'Pas de 1 — Ex: 18/20' },
];

onMounted(async () => {
  try {
    const res = await axios.get('/api/user/preferences');
    form.rating_system = res.data.rating_system || '20_points';
    form.reading_goal_annual = res.data.reading_goal_annual;
    
    // On récupère aussi les infos de thème si elles y sont, ou on refait un appel spécifique
    const themeRes = await axios.get('/api/theme');
    form.theme_mode = themeRes.data.theme_mode || 'auto';
    form.theme_name = themeRes.data.theme_name || 'grimoire';
    form.fixed_season = themeRes.data.active_season || 'printemps';

  } catch (e) {
    console.error(e);
  } finally {
    isLoading.value = false;
  }
});

const save = async () => {
  isSaving.value = true;
  savedMsg.value = '';
  try {
    await axios.put('/api/user/preferences', {
      rating_system: form.rating_system,
      reading_goal_annual: form.reading_goal_annual,
    });
    
    await updateTheme(form.theme_mode, form.theme_name, form.fixed_season);

    savedMsg.value = '✅ Préférences sauvegardées !';
    setTimeout(() => { savedMsg.value = ''; }, 3000);
  } catch (e) {
    console.error(e);
    savedMsg.value = '❌ Erreur lors de la sauvegarde.';
  } finally {
    isSaving.value = false;
  }
};
</script>

<style scoped>
.settings-container { width: 100%; max-width: 800px; margin: 0 auto; }
.page-title { font-size: 2.2rem; margin-bottom: 2rem; color: var(--color-primary); font-family: var(--font-heading); }
.loading-state { text-align: center; padding: 4rem; color: var(--color-text-muted); font-style: italic; }

.setting-section { margin-bottom: 2.5rem; background: var(--color-surface); padding: 1.5rem; border-radius: var(--border-radius, 12px); border: 1px solid var(--color-border); }
.setting-section h2 { font-size: 1.3rem; margin: 0 0 0.3rem; color: var(--color-primary); }
.setting-description { color: var(--color-text-muted); margin: 0 0 1.5rem; font-size: 0.95rem; }

/* Theme options */
.theme-options { display: flex; flex-direction: column; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.4rem; }
.form-group label { font-weight: 600; font-size: 0.95rem; color: var(--color-text); }


/* Rating options */
.rating-options { display: flex; flex-direction: column; gap: 0.75rem; }
.rating-option {
  display: flex; align-items: center; gap: 0.75rem;
  padding: 1rem 1.2rem; border: 2px solid var(--color-border); border-radius: 12px;
  cursor: pointer; transition: all 0.2s;
}
.rating-option:hover { border-color: var(--color-primary-light); }
.rating-option.selected { border-color: var(--color-primary); background: var(--color-primary-light); }
.rating-option input { display: none; }

.option-content { display: flex; flex-direction: column; }
.option-icon { font-size: 1.4rem; margin-bottom: 0.2rem; }
.option-label { font-weight: 700; font-size: 1.05rem; color: var(--color-text); }
.option-desc { font-size: 0.85rem; color: var(--color-text-muted); }

/* Goal */
.goal-input-wrapper { display: flex; align-items: center; gap: 0.5rem; }
.goal-input {
  width: 100px; padding: 0.6rem 0.75rem; border: 1px solid var(--color-border);
  border-radius: 8px; font-size: 1.1rem; text-align: center; background: transparent;
  color: var(--color-text);
}
.goal-input:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 2px var(--color-primary-light); }
.goal-suffix { font-size: 1rem; color: var(--color-text-muted); }

/* Save */
.save-section { display: flex; align-items: center; gap: 1rem; margin-top: 1rem; }
.btn-save {
  padding: 0.7rem 2rem; background: var(--color-primary); color: #fff;
  border: none; border-radius: 8px; font-size: 1rem; font-weight: 600;
  cursor: pointer; transition: background 0.2s;
}
.btn-save:hover:not(:disabled) { filter: brightness(0.9); }
.btn-save:disabled { opacity: 0.5; cursor: not-allowed; }
.saved-msg { font-size: 0.95rem; font-weight: 600; color: var(--color-primary); }
</style>
