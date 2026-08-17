<template>
  <div class="export-container">
    <div class="export-actions">
      <button @click="exportToImage" class="btn-export image-btn" :disabled="isExporting">
        <span v-if="isExporting"><Icon name="PhSpinner" class="spin" /> Génération...</span>
        <span v-else><Icon name="PhCamera" weight="duotone" /> Exporter en Image</span>
      </button>
      
      <button @click="exportToPdf" class="btn-export pdf-btn">
        <Icon name="PhFilePdf" weight="duotone" /> Télécharger PDF
      </button>
    </div>

    <!-- Zone cachée utilisée uniquement pour la capture HTML2Canvas si besoin 
         ou affichée comme un vrai récapitulatif -->
    <div ref="exportZone" class="export-zone">
      <div class="export-header">
        <h2>Bilan du mois de {{ formattedMonth }}</h2>
      </div>

      <div class="books-grid">
        <div v-for="reading in readings" :key="reading.id" class="book-card">
          <!-- useCORS est crucial ici pour que html2canvas puisse lire l'image de Google Books -->
          <img 
            :src="reading.book.cover_url" 
            v-if="reading.book.cover_url" 
            crossorigin="anonymous" 
            alt="Couverture" 
            class="cover-image"
          />
          <div v-else class="cover-fallback" :style="{ backgroundColor: reading.book.spine_color || 'var(--color-primary)' }">
            {{ reading.book.title }}
          </div>
          
          <div class="info">
            <h3>{{ reading.book.title }}</h3>
            <p class="author">{{ reading.book.author }}</p>
            <div class="rating">
              <Icon v-for="i in 5" :key="i" name="PhStar" :weight="i <= (reading.rating / 4) ? 'fill' : 'regular'" class="star-icon" />
            </div>
          </div>
        </div>
      </div>
      
      <div v-if="readings.length === 0" class="empty-state">
        Aucun livre terminé ce mois-ci.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import html2canvas from 'html2canvas';
import Icon from '../Shared/Icon.vue';

const props = defineProps({
  readings: {
    type: Array,
    required: true,
    default: () => []
  },
  year: {
    type: Number,
    required: true
  },
  month: {
    type: Number,
    required: true
  }
});

const exportZone = ref(null);
const isExporting = ref(false);

const formattedMonth = computed(() => {
  return `${props.month.toString().padStart(2, '0')}/${props.year}`;
});

const exportToImage = async () => {
  if (!exportZone.value) return;
  isExporting.value = true;
  
  try {
    const canvas = await html2canvas(exportZone.value, {
      useCORS: true, // Requis pour les images externes (Google Books)
      allowTaint: false,
      backgroundColor: null, // Transparent ou prend le fond du layout
      scale: 2 // Améliore la résolution de l'image
    });
    
    const link = document.createElement('a');
    link.download = `carnet_lecture_${props.year}_${props.month}.png`;
    link.href = canvas.toDataURL('image/png');
    link.click();
  } catch (error) {
    console.error("Erreur lors de l'export PNG", error);
    alert("Une erreur est survenue lors de la création de l'image.");
  } finally {
    isExporting.value = false;
  }
};

const exportToPdf = () => {
  // Appelle la route backend générant le PDF avec Browsershot
  window.open(`/api/export/month/${props.year}/${props.month}`, '_blank');
};
</script>

<style scoped>
.export-container {
  margin: 2rem 0;
  font-family: inherit;
}

.export-actions {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.btn-export {
  padding: 0.75rem 1.5rem;
  color: var(--color-bg);
  border: none;
  border-radius: var(--border-radius, 8px);
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
  transition: opacity 0.2s, transform 0.2s;
  box-shadow: var(--shadow-md);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-family: inherit;
}

.btn-export:hover:not(:disabled) {
  opacity: 0.9;
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.btn-export:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.image-btn {
  background-color: var(--color-primary);
}

.pdf-btn {
  background-color: var(--color-primary-light);
}

/* Zone exportée en PNG */
.export-zone {
  padding: 2.5rem;
  /* On force un background agréable en cas de fond transparent */
  background: var(--color-surface);
  border-radius: var(--border-radius, 16px);
  border: 1px solid var(--color-border);
}

.export-header {
  text-align: center;
  margin-bottom: 2rem;
  border-bottom: 2px solid var(--color-border);
  padding-bottom: 1rem;
}

.export-header h2 {
  margin: 0;
  font-size: 2rem;
  color: var(--color-text);
  font-family: inherit;
}

.books-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 2rem;
}

.book-card {
  display: flex;
  flex-direction: column;
  background: var(--color-bg);
  border-radius: var(--border-radius, 12px);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--color-border);
}

.cover-image {
  width: 100%;
  height: 300px;
  object-fit: cover;
}

.cover-fallback {
  width: 100%;
  height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.2rem;
  font-weight: bold;
  text-align: center;
  padding: 1rem;
  box-sizing: border-box;
}

.info {
  padding: 1rem;
}

.info h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.1rem;
  color: var(--color-text);
}

.author {
  margin: 0 0 0.5rem 0;
  color: var(--color-text-muted);
  font-size: 0.9rem;
}

.rating {
  color: var(--color-accent);
  display: flex;
  gap: 2px;
}

.star-icon {
  font-size: 1.1rem;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: var(--color-text-muted);
  font-style: italic;
}

.spin {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  100% { transform: rotate(360deg); }
}
</style>
