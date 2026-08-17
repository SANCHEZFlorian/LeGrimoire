<template>
  <div class="landing-container">
    <div class="theme-timer">
      <span class="timer-text">Thème: {{ formatThemeName(currentTheme) }}</span>
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: progress + '%' }"></div>
      </div>
    </div>

    <div class="content-wrapper">
      <header class="hero-section">
        <h1 class="hero-title">Carnet de Lecture</h1>
        <p class="hero-subtitle">
          Votre espace privé et élégant pour encrer la mémoire de toutes vos lectures.
        </p>
        
        <div class="cta-buttons">
          <router-link to="/register" class="ink-btn">Créer mon compte</router-link>
          <router-link to="/login" class="ink-btn secondary">Me connecter</router-link>
        </div>
      </header>

      <div class="ornament-divider">
        <Icon name="PhBookOpen" weight="duotone" />
      </div>

      <section class="features-section">
        <div class="feature-column">
          <h3 class="feature-title">Statistiques Avancées</h3>
          <p class="feature-text">
            Suivez le décompte de vos pages lues, visualisez vos bilans annuels et 
            contemplez la progression de vos connaissances au fil des saisons.
          </p>
        </div>
        
        <div class="feature-column border-column">
          <h3 class="feature-title">Bibliothèque Hybride</h3>
          <p class="feature-text">
            Recherchez des ouvrages via les vastes archives du monde. Les couleurs 
            des tranches de votre étagère virtuelle s'adaptent à vos goûts.
          </p>
        </div>
        
        <div class="feature-column border-column">
          <h3 class="feature-title">Thèmes Dynamiques</h3>
          <p class="feature-text">
            Découvrez une interface qui évolue selon les saisons et vos envies, 
            avec de multiples thèmes et ambiances à explorer.
          </p>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Icon from './Shared/Icon.vue';

const themes = ['grimoire', 'moderne', 'romantique', 'cottagecore', 'dark_academia', 'florale', 'nature', 'animaux'];
const currentThemeIndex = ref(0);
const progress = ref(0);
const currentTheme = ref(themes[0]);

let timer = null;
let progressTimer = null;

const formatThemeName = (theme) => {
  return theme.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const changeTheme = () => {
  currentThemeIndex.value = (currentThemeIndex.value + 1) % themes.length;
  currentTheme.value = themes[currentThemeIndex.value];
  document.documentElement.setAttribute('data-theme', currentTheme.value);
  progress.value = 0;
};

onMounted(() => {
  // Choisir un thème aléatoire au démarrage
  currentThemeIndex.value = Math.floor(Math.random() * themes.length);
  currentTheme.value = themes[currentThemeIndex.value];
  document.documentElement.setAttribute('data-theme', currentTheme.value);

  // Mettre à jour la barre de progression (toutes les 100ms)
  progressTimer = setInterval(() => {
    progress.value += 1; // 1% toutes les 100ms = 100% en 10s
    if (progress.value >= 100) {
      changeTheme();
    }
  }, 100);
});

onUnmounted(() => {
  clearInterval(progressTimer);
  // Optionnel: On pourrait réinitialiser au thème par défaut s'ils naviguent ailleurs,
  // mais app.js ou useTheme le fera.
});
</script>

<style scoped>
.landing-container {
  position: relative;
  min-height: calc(100vh - 2rem);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 2rem;
  box-sizing: border-box;
  color: var(--color-text);
  overflow-x: hidden;
  background: transparent;
}

.theme-timer {
  position: absolute;
  top: 1rem;
  right: 2rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  z-index: 10;
  background: var(--color-surface);
  padding: 0.5rem 1rem;
  border-radius: 20px;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--color-border);
}

.timer-text {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--color-text-muted);
}

.progress-bar {
  width: 100px;
  height: 6px;
  background: var(--color-border);
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: var(--color-primary);
  width: 0%;
  transition: width 0.1s linear;
}

.content-wrapper {
  position: relative;
  z-index: 3;
  max-width: 1000px;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 3vh;
}

.hero-section {
  text-align: center;
}

.hero-title {
  font-size: clamp(2.5rem, 6vw, 4.5rem);
  font-weight: 800;
  margin: 0;
  color: var(--color-primary);
  letter-spacing: 1px;
}

.hero-subtitle {
  font-size: clamp(1.2rem, 3vw, 1.6rem);
  color: var(--color-text);
  margin: 0.5rem 0 3rem 0;
  line-height: 1.4;
}

.cta-buttons {
  display: flex;
  gap: 2rem;
  justify-content: center;
}

.ink-btn {
  font-size: 1.1rem;
  font-weight: 600;
  text-decoration: none;
  padding: 0.8rem 2.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  border-radius: var(--border-radius, 8px);
}

.ink-btn:not(.secondary) {
  background-color: var(--color-primary);
  color: var(--color-bg);
  border: 2px solid var(--color-primary);
  box-shadow: var(--shadow-md);
}

.ink-btn:not(.secondary):hover {
  background-color: var(--color-primary-light);
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.ink-btn.secondary {
  background-color: transparent;
  color: var(--color-text);
  border: 2px solid var(--color-primary);
}

.ink-btn.secondary:hover {
  background-color: var(--color-surface);
  transform: translateY(-2px);
}

.ornament-divider {
  font-size: 2rem;
  color: var(--color-primary);
  margin: 0;
  opacity: 0.7;
}

.features-section {
  display: flex;
  justify-content: space-between;
  width: 100%;
  gap: 1.5rem;
}

.feature-column {
  flex: 1;
  padding: 0 1rem;
  text-align: center;
}

.border-column {
  border-left: 1px solid var(--color-border);
}

.feature-title {
  font-size: clamp(1.2rem, 2vw, 1.4rem);
  font-weight: 700;
  color: var(--color-text);
  margin: 0 0 1rem 0;
  position: relative;
  display: inline-block;
}

.feature-title::after {
  content: '';
  position: absolute;
  bottom: -8px;
  left: 20%;
  width: 60%;
  height: 2px;
  background: var(--color-primary);
  opacity: 0.5;
}

.feature-text {
  font-size: clamp(1rem, 1.5vw, 1.1rem);
  line-height: 1.5;
  margin: 0;
  color: var(--color-text-muted);
}

@media (max-width: 900px) {
  .features-section {
    flex-direction: column;
    gap: 3rem;
  }
  
  .border-column {
    border-left: none;
    border-top: 1px solid var(--color-border);
    padding-top: 3rem;
  }
  
  .cta-buttons {
    flex-direction: column;
    gap: 1rem;
  }
  
  .theme-timer {
    top: auto;
    bottom: 1rem;
    right: 50%;
    transform: translateX(50%);
  }
}
</style>
