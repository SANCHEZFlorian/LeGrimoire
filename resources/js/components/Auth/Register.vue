<template>
  <div class="auth-container">
    <div class="auth-card">
      <h2>Créer mon compte</h2>
      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label>Nom complet</label>
          <input type="text" v-model="form.name" required placeholder="John Doe" />
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" v-model="form.email" required placeholder="test@test.com" />
        </div>
        <div class="form-group">
          <label>Mot de passe</label>
          <input type="password" v-model="form.password" required />
        </div>
        <div class="form-group">
          <label>Confirmer le mot de passe</label>
          <input type="password" v-model="form.password_confirmation" required />
        </div>
        
        <div v-if="errorMessage" class="error-msg">{{ errorMessage }}</div>
        
        <button type="submit" :disabled="isLoading">
          {{ isLoading ? 'Création en cours...' : 'S\'inscrire' }}
        </button>
      </form>
      <div class="auth-links">
        <router-link to="/login">Déjà un compte ? Se connecter.</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const form = ref({ 
  name: '', 
  email: '', 
  password: '', 
  password_confirmation: '' 
});
const isLoading = ref(false);
const errorMessage = ref('');

const handleRegister = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  
  try {
    // Initialisation du cookie CSRF pour Sanctum
    await axios.get('/sanctum/csrf-cookie');
    
    // Appel à notre API d'inscription
    const response = await axios.post('/api/register', form.value);
    
    // Stockage du token (l'API renvoie un plainTextToken)
    if (response.data.access_token) {
      const token = response.data.access_token;
      localStorage.setItem('auth_token', token);
      
      // Injection du token dans les requêtes futures
      window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      
      // Redirection vers le dashboard
      router.push('/dashboard');
    }
  } catch (error) {
    if (error.response && error.response.status === 422) {
      // Afficher les erreurs de validation
      const errors = error.response.data.errors;
      errorMessage.value = Object.values(errors).flat().join(' ');
    } else {
      errorMessage.value = "Une erreur est survenue lors de l'inscription.";
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: calc(100vh - 2rem);
}

.auth-card {
  background: var(--color-surface);
  padding: 3rem;
  border: 1px solid var(--color-border);
  border-radius: var(--border-radius, 12px);
  width: 100%;
  max-width: 450px;
  box-shadow: var(--shadow-xl);
}

h2 {
  text-align: center;
  margin-bottom: 2.5rem;
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--color-primary);
}

.form-group {
  margin-bottom: 2rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--color-text);
}

input {
    background: transparent;
    border: none;
    border-bottom: 2px solid var(--color-border);
    border-radius: 0;
    color: var(--color-text);
    font-size: 1.1rem;
    padding: 8px 0;
    width: 100%;
    box-sizing: border-box;
    font-family: inherit;
}

input:focus {
    outline: none;
    border-bottom-color: var(--color-primary);
}

/* Hack Autofill Chrome */
input:-webkit-autofill,
input:-webkit-autofill:hover, 
input:-webkit-autofill:focus, 
input:-webkit-autofill:active {
    -webkit-box-shadow: 0 0 0 30px var(--color-surface) inset !important;
    -webkit-text-fill-color: var(--color-text) !important;
    transition: background-color 5000s ease-in-out 0s;
}

button {
  font-size: 1.2rem;
  font-weight: 600;
  padding: 0.8rem 2.5rem;
  width: 100%;
  cursor: pointer;
  transition: all 0.2s ease;
  
  background-color: var(--color-primary);
  color: var(--color-bg);
  border: none;
  border-radius: 8px;
  margin-top: 1rem;
  font-family: inherit;
}

button:hover:not(:disabled) {
  background-color: var(--color-primary-light);
}

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.error-msg {
  color: var(--color-accent, #ef4444);
  margin-top: -1rem;
  margin-bottom: 1rem;
  text-align: center;
  font-size: 1.1rem;
  font-weight: bold;
}

.auth-links {
  text-align: center;
  margin-top: 2rem;
}

.auth-links a {
  font-family: inherit;
  font-size: 1.1rem;
  color: var(--color-primary);
  text-decoration: none;
  font-weight: 600;
}

.auth-links a:hover {
  text-decoration: underline;
  color: var(--color-primary-light);
}
</style>
