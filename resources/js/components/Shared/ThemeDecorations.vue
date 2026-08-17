<template>
  <div class="theme-decorations" aria-hidden="true">
    <!-- Overlay de fond si image de fond définie -->
    <div class="bg-overlay" v-if="bgImage" :style="{ backgroundImage: `url(${bgImage})` }"></div>

    <!-- Coins décoratifs (top-left, top-right, bottom-left, bottom-right) -->
    <div v-if="corners" class="decor-corner top-left" v-html="corners.tl"></div>
    <div v-if="corners" class="decor-corner top-right" v-html="corners.tr"></div>
    <div v-if="corners" class="decor-corner bottom-left" v-html="corners.bl"></div>
    <div v-if="corners" class="decor-corner bottom-right" v-html="corners.br"></div>

    <!-- Particules saisonnières -->
    <SeasonParticles :season="season" />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import SeasonParticles from './SeasonParticles.vue';

const props = defineProps({
  theme: {
    type: String,
    required: true
  },
  season: {
    type: String,
    required: true
  }
});

// Calcul de l'image de fond (placeholder pour l'instant, sera remplacé par les vraies images générées)
const bgImage = computed(() => {
  return `/images/themes/${props.theme}/bg-${props.season}.webp`;
});

// Définition des SVG par thème (simplifié pour l'exemple)
const themeCorners = {
  grimoire: {
    tl: '<svg width="50" height="50" viewBox="0 0 100 100"><path d="M0 0 L100 0 C50 0 0 50 0 100 Z" fill="var(--color-primary-light)"/></svg>',
    tr: '<svg width="50" height="50" viewBox="0 0 100 100"><path d="M100 0 L0 0 C50 0 100 50 100 100 Z" fill="var(--color-primary-light)"/></svg>',
    bl: '<svg width="50" height="50" viewBox="0 0 100 100"><path d="M0 100 L100 100 C50 100 0 50 0 0 Z" fill="var(--color-primary-light)"/></svg>',
    br: '<svg width="50" height="50" viewBox="0 0 100 100"><path d="M100 100 L0 100 C50 100 100 50 100 0 Z" fill="var(--color-primary-light)"/></svg>'
  },
  romantique: {
    tl: '<svg width="40" height="40" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="var(--color-primary-light)"/></svg>',
    tr: '<svg width="40" height="40" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="var(--color-primary-light)"/></svg>',
    bl: '<svg width="40" height="40" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="var(--color-primary-light)"/></svg>',
    br: '<svg width="40" height="40" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="var(--color-primary-light)"/></svg>'
  }
};

const corners = computed(() => {
  return themeCorners[props.theme] || null;
});
</script>

<style scoped>
.theme-decorations {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  pointer-events: none;
  z-index: 0; /* Derrière le contenu */
}

.bg-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  opacity: 0.15; /* Subtil */
  transition: background-image 1s ease-in-out;
  mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 0%, rgba(0,0,0,0) 100%);
  -webkit-mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 0%, rgba(0,0,0,0) 100%);
}

.decor-corner {
  position: absolute;
  opacity: 0.8;
}
.decor-corner.top-left { top: 0; left: 0; }
.decor-corner.top-right { top: 0; right: 0; }
.decor-corner.bottom-left { bottom: 0; left: 0; }
.decor-corner.bottom-right { bottom: 0; right: 0; }
</style>
