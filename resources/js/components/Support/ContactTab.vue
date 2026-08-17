<template>
  <div class="contact-tab">
    <h2>Contacter le Support</h2>
    <p class="subtitle">Un problème technique ? Une question spécifique ? Envoyez-nous un message !</p>

    <div v-if="success" class="success-alert">
      Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.
    </div>

    <form @submit.prevent="submit" class="contact-form" v-else>
      <div class="form-group">
        <label>Sujet</label>
        <input type="text" v-model="form.subject" required placeholder="Le sujet de votre demande..." class="input-field" />
      </div>
      <div class="form-group">
        <label>Message</label>
        <textarea v-model="form.message" required rows="6" placeholder="Décrivez votre problème en détail..." class="input-field"></textarea>
      </div>
      <button type="submit" class="btn btn-primary" :disabled="loading">
        {{ loading ? 'Envoi en cours...' : 'Envoyer le message' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const form = ref({ subject: '', message: '' });
const loading = ref(false);
const success = ref(false);

const submit = async () => {
  loading.value = true;
  try {
    await axios.post('/api/support/tickets', form.value);
    success.value = true;
  } catch(e) {
    console.error(e);
    alert('Une erreur est survenue lors de l\'envoi.');
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.contact-tab h2 { font-size: 1.8rem; margin: 0 0 0.5rem 0; }
.subtitle { color: #64748b; margin-bottom: 2rem; }
.contact-form { max-width: 600px; }
.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
.input-field { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; font-size: 1rem; }
.btn { padding: 0.75rem 1.5rem; border-radius: 6px; cursor: pointer; border: none; font-weight: bold; font-size: 1.1rem; }
.btn-primary { background: var(--color-primary, #721c24); color: white; }
.btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }
.success-alert { background: #dcfce7; color: #16a34a; padding: 1.5rem; border-radius: 8px; border: 1px solid #bbf7d0; font-weight: bold; text-align: center; }
</style>
