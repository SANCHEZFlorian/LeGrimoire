<template>
  <div class="app-layout">
    <ThemeDecorations :theme="activeTheme" :season="activeSeason" />
    
    <Navbar v-if="showNavbar" @toggle-menu="mobileMenuOpen = true" />
    <MobileMenu v-if="showNavbar" :is-open="mobileMenuOpen" @close="mobileMenuOpen = false" />
    
    <!-- Contenu Principal -->
    <main class="main-content">
      <!-- Le routeur va injecter les vues ici (Dashboard, Library) -->
      <router-view />
    </main>
  </div>
</template>
<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import Navbar from './Navbar.vue';
import MobileMenu from './MobileMenu.vue';
import ThemeDecorations from '../Shared/ThemeDecorations.vue';
import { useTheme } from '../../composables/useTheme';

const router = useRouter();
const route = useRoute();
const { activeTheme, activeSeason, fetchTheme } = useTheme();

const mobileMenuOpen = ref(false);

// Ne pas afficher la navbar sur la Landing, le Login, le Register et les routes d'admin
const showNavbar = computed(() => {
  return !['Landing', 'Login', 'Register'].includes(route.name) && !route.path.startsWith('/admin');
});

onMounted(async () => {
  await fetchTheme();
});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=MedievalSharp&display=swap');

/* Variables CSS globales (Fallback par défaut : Automne) */
:root {
  --bg-color: #fff7ed;
  --text-color: #2c1e16; /* Couleur encre d'écriture */
  --primary-color: #721c24; /* Le rouge profond de notre thème */
  --paper-grain-opacity: 0.5;
}

body {
  /* Le bg-color est initialement défini par la variable du thème, mais on peut le surcharger pour le parchemin global */
  background-color: var(--bg-color, #f4ebd8);
  
  /* Texture de papier et vignette globales */
  background-image: 
      url('https://www.transparenttextures.com/patterns/aged-paper.png'),
      radial-gradient(circle at center, transparent 40%, rgba(139, 69, 19, 0.4) 120%);
  background-blend-mode: multiply; 
  background-repeat: repeat;
  background-attachment: fixed; /* Pour éviter que le fond bouge au scroll */
  
  /* Transition douce lors du changement de thème */
  transition: background-color 0.5s ease, color 0.5s ease;
  margin: 0;
}

body, p, span, a, li, input, textarea, button {
  font-family: 'Cormorant Garamond', serif;
  color: #2c1e16; /* Couleur encre d'écriture */
  font-size: 1.1rem;
}

h1, h2, h3, h4, h5, h6 {
  font-family: 'MedievalSharp', cursive; /* Aspect Enluminure */
  color: #721c24; /* Le rouge profond de notre thème */
  font-weight: normal;
  margin-bottom: 1rem;
  margin-top: 0;
}

.app-layout {
  position: relative;
  min-height: 100vh;
  display: flex;
  flex-direction: column; /* Changed from row for sidebar to column for navbar */
}

.main-content {
  flex: 1; /* Prend l'espace restant */
  position: relative;
  z-index: 1;
  padding: 2rem;
  width: 100%;
  max-width: 1600px; /* Limit max width for readability */
  margin: 0 auto;
}
</style>
