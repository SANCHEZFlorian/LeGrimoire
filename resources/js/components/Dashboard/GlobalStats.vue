<template>
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon"><Icon name="PhBooks" weight="duotone" /></div>
      <div class="stat-info">
        <h3>Livres Terminés</h3>
        <p class="stat-value">{{ totalFinished }}</p>
      </div>
    </div>
    
    <div class="stat-card">
      <div class="stat-icon"><Icon name="PhBookOpen" weight="duotone" /></div>
      <div class="stat-info">
        <h3>Pages Lues</h3>
        <p class="stat-value">{{ totalPages }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  totalFinished: {
    type: Number,
    required: true
  },
  totalPages: {
    type: Number,
    required: true
  }
})
</script>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
}
.stat-card {
  /* Suppression de la carte blanche */
  background: transparent;
  padding: 1.5rem 0;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  border-bottom: 1px solid rgba(44, 30, 22, 0.4);
}
.stat-icon {
  font-size: 2.5rem;
  /* Plus de rond gris, juste un trait d'encre */
  background: transparent;
  border: 1px solid rgba(44, 30, 22, 0.4);
  width: 60px;
  height: 60px;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 50%;
}
.stat-info h3 {
  margin: 0;
  font-family: 'MedievalSharp', cursive;
  font-size: 1.3rem;
  color: #2c1e16;
}
.stat-value {
  margin: 0.2rem 0 0 0;
  font-size: 1.8rem;
  font-weight: 700;
  color: #721c24;
}
</style>
