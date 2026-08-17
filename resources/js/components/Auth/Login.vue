<template>
  <div class="login-container">
    <div class="login-card">
      <h2>Connexion</h2>
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label>Email</label>
          <input type="email" v-model="form.email" required placeholder="test@test.com" />
        </div>
        <div class="form-group">
          <label>Mot de passe</label>
          <input type="password" v-model="form.password" required placeholder="password" />
        </div>
        
        <div v-if="errorMessage" class="error-msg">{{ errorMessage }}</div>
        
        <button type="submit" :disabled="isLoading">
          {{ isLoading ? 'Connexion en cours...' : 'Se connecter' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const form = ref({ email: '', password: '' });
const isLoading = ref(false);
const errorMessage = ref('');

const handleLogin = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  
  try {
    // Initialisation du cookie CSRF pour Sanctum
    await axios.get('/sanctum/csrf-cookie');
    
    // Appel à notre API de connexion
    const response = await axios.post('/api/login', form.value);
    
    // Stockage du token (l'API renvoie un plainTextToken)
    if (response.data.access_token) {
      const token = response.data.access_token;
      localStorage.setItem('auth_token', token);
      
      // Injection du token dans les requêtes futures
      window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      
      // Redirection
      router.push('/dashboard');
    }
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errorMessage.value = "Identifiants incorrects.";
    } else {
      errorMessage.value = "Une erreur est survenue lors de la connexion.";
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: calc(100vh - 2rem);
}

.login-card {
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
</style>
