<template>
  <div class="suggestions-tab">
    <div class="header">
      <h2>Boîte à idées</h2>
      <button class="btn btn-primary" @click="showForm = !showForm">
        {{ showForm ? 'Annuler' : 'Proposer une idée' }}
      </button>
    </div>

    <!-- Formulaire d'ajout -->
    <div v-if="showForm" class="suggestion-form">
      <input type="text" v-model="form.title" placeholder="Titre de votre idée..." class="input-field" />
      <textarea v-model="form.description" placeholder="Décrivez votre idée en détail..." rows="4" class="input-field"></textarea>
      <button class="btn btn-success" @click="submitSuggestion" :disabled="!form.title || !form.description">Soumettre</button>
    </div>

    <!-- Liste des idées -->
    <div class="suggestions-list">
      <div v-for="suggestion in suggestions" :key="suggestion.id" class="suggestion-card">
        <div class="vote-section">
          <button @click="toggleVote(suggestion)" :class="['vote-btn', { active: suggestion.has_voted }]">
            <Icon name="PhCaretUp" weight="bold" />
          </button>
          <span class="vote-count">{{ suggestion.upvotes_count }}</span>
        </div>
        <div class="suggestion-content">
          <h3>{{ suggestion.title }}</h3>
          <p>{{ suggestion.description }}</p>
          <div class="meta">
            Proposé par {{ suggestion.user?.name || 'Inconnu' }} - Statut : <span :class="'status ' + suggestion.status">{{ suggestion.status }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Icon from '../Shared/Icon.vue';

const suggestions = ref([]);
const showForm = ref(false);
const form = ref({ title: '', description: '' });

const fetchSuggestions = async () => {
  try {
    const res = await axios.get('/api/support/suggestions');
    suggestions.value = res.data;
  } catch(e) { console.error(e); }
};

onMounted(fetchSuggestions);

const submitSuggestion = async () => {
  try {
    await axios.post('/api/support/suggestions', form.value);
    form.value = { title: '', description: '' };
    showForm.value = false;
    fetchSuggestions();
  } catch(e) { console.error(e); }
};

const toggleVote = async (suggestion) => {
  try {
    const res = await axios.post(`/api/support/suggestions/${suggestion.id}/vote`);
    suggestion.has_voted = res.data.voted;
    if (res.data.voted) suggestion.upvotes_count++;
    else suggestion.upvotes_count--;
  } catch(e) { console.error(e); }
};
</script>

<style scoped>
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.header h2 { font-size: 1.8rem; margin: 0; }
.btn { padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; border: none; font-weight: bold; }
.btn-primary { background: var(--color-primary, #721c24); color: white; }
.btn-success { background: #16a34a; color: white; margin-top: 1rem; }
.suggestion-form { background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #e2e8f0; }
.input-field { width: 100%; margin-bottom: 1rem; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; }

.suggestion-card { display: flex; gap: 1.5rem; background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #e2e8f0; }
.vote-section { display: flex; flex-direction: column; align-items: center; }
.vote-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #94a3b8; transition: color 0.2s; }
.vote-btn:hover { color: #64748b; }
.vote-btn.active { color: #2563eb; }
.vote-count { font-size: 1.2rem; font-weight: bold; }
.suggestion-content h3 { margin: 0 0 0.5rem 0; font-size: 1.3rem; }
.meta { font-size: 0.9rem; color: #64748b; margin-top: 1rem; }
.status { padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; text-transform: uppercase; }
.status.pending { background: #fef3c7; color: #d97706; }
.status.planned { background: #dbeafe; color: #2563eb; }
.status.implemented { background: #dcfce7; color: #16a34a; }
.status.rejected { background: #fee2e2; color: #dc2626; }
</style>
