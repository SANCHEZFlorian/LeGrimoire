<template>
  <div class="season-particles-container">
    <div v-for="n in particleCount" :key="n" class="particle" :class="particleClass" :style="getParticleStyle(n)"></div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  season: {
    type: String,
    required: true
  }
});

const particleCount = computed(() => {
  if (props.season === 'printemps') return 15;
  if (props.season === 'hiver') return 30;
  if (props.season === 'automne') return 12;
  return 0; // Pas de particules l'été, ou juste des rayons lumineux (difficile avec de simples divs)
});

const particleClass = computed(() => {
  return `particle-${props.season}`;
});

const getParticleStyle = (n) => {
  // Générer des positions et des durées aléatoires pour chaque particule
  // En production, il vaut mieux utiliser une librairie ou un canvas pour la perf
  const left = Math.random() * 100;
  const animationDuration = 5 + Math.random() * 10;
  const animationDelay = Math.random() * -10; // -10 pour qu'elles soient déjà en cours
  const size = 0.5 + Math.random() * 1.5;

  return {
    left: `${left}vw`,
    animationDuration: `${animationDuration}s`,
    animationDelay: `${animationDelay}s`,
    transform: `scale(${size})`
  };
};
</script>

<style scoped>
.season-particles-container {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
}

.particle {
  position: absolute;
  top: -10vh;
  opacity: 0.6;
}

/* Printemps : Pétales (cercles allongés roses) */
.particle-printemps {
  width: 15px;
  height: 10px;
  background-color: #fca5a5;
  border-radius: 50% 0 50% 0;
  animation: fall-sway linear infinite;
}

/* Automne : Feuilles (cercles ambrés) */
.particle-automne {
  width: 12px;
  height: 12px;
  background-color: #d97706;
  border-radius: 2px 10px 2px 10px;
  animation: fall-sway linear infinite;
}

/* Hiver : Flocons (cercles blancs floutés) */
.particle-hiver {
  width: 8px;
  height: 8px;
  background-color: #fff;
  border-radius: 50%;
  box-shadow: 0 0 4px #fff;
  animation: fall-straight linear infinite;
}

@keyframes fall-sway {
  0% { transform: translateY(0) rotate(0deg) translateX(0); }
  50% { transform: translateY(55vh) rotate(180deg) translateX(20px); opacity: 1; }
  100% { transform: translateY(110vh) rotate(360deg) translateX(-20px); opacity: 0; }
}

@keyframes fall-straight {
  0% { transform: translateY(0); }
  100% { transform: translateY(110vh); opacity: 0; }
}
</style>
