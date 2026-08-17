<template>
  <div class="reviews-container">
    <h1 class="page-title"><Icon name="PhStar" weight="duotone" /> Reviews & Galerie</h1>

    <div v-if="isLoading" class="loading-state">Chargement...</div>

    <div v-else>
      <div class="reviews-grid" v-if="reviewedBooks.length > 0">
        <div v-for="reading in reviewedBooks" :key="reading.id" class="review-card" @click="openDetail(reading)">
          <img :src="getCover(reading)" alt="Couverture" class="review-cover" />
          <div class="review-details">
            <h3>{{ getTitle(reading) }}</h3>
            <p class="author">Par {{ getAuthor(reading) }}</p>

            <div class="review-meta">
              <span class="rating" v-if="reading.rating != null">{{ formatRating(reading.rating) }}</span>
              <span v-if="reading.is_favorite" class="fav-tag"><Icon name="PhHeart" weight="fill" /> Favori</span>
              <SpiceRating v-if="reading.spice_rating" :modelValue="reading.spice_rating" :editable="false" />
            </div>

            <blockquote v-if="reading.favorite_quote" class="quote">
              « {{ reading.favorite_quote }} »
            </blockquote>

            <div v-if="reading.favorite_moment" class="moment">
              <strong>Moment préféré :</strong> {{ reading.favorite_moment }}
            </div>

            <p v-if="reading.review" class="review-text">{{ reading.review }}</p>
          </div>
        </div>
      </div>

      <div v-else class="empty-state">
        Aucune review pour le moment. Terminez des livres et donnez votre avis !
      </div>
    </div>

    <BookDetailModal
      :visible="modalOpen"
      :reading="selectedReading"
      :ratingSystem="ratingSystem"
      @close="modalOpen = false"
      @saved="onSaved"
      @deleted="onDeleted"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import BookDetailModal from '../Shared/BookDetailModal.vue';
import SpiceRating from '../Shared/SpiceRating.vue';
import Icon from '../Shared/Icon.vue';

const placeholder = 'https://via.placeholder.com/128x192.png?text=Pas+de+couverture';
const isLoading = ref(true);
const readings = ref([]);
const modalOpen = ref(false);
const selectedReading = ref(null);
const ratingSystem = ref('20_points');

onMounted(async () => {
  try {
    const [readingsRes, prefsRes] = await Promise.all([
      axios.get('/api/readings'),
      axios.get('/api/user/preferences'),
    ]);
    readings.value = readingsRes.data;
    ratingSystem.value = prefsRes.data.rating_system || '20_points';
  } catch (e) {
    console.error(e);
  } finally {
    isLoading.value = false;
  }
});

const getCover = (r) => r.edition?.cover_url || r.work?.cover_url || r.book?.cover_url || placeholder;
const getTitle = (r) => r.work?.title || r.book?.title || 'Titre inconnu';
const getAuthor = (r) => r.work?.author || r.book?.author || 'Auteur inconnu';

const reviewedBooks = computed(() => {
  return readings.value.filter(r => ['finished', 'past_read'].includes(r.status));
});

const formatRating = (r) => {
  if (r === null) return '';
  const sys = ratingSystem.value;
  if (sys === '5_stars') return r + '⭐';
  if (sys === '10_points') return r + '/10';
  return r + '/20';
};

const openDetail = (reading) => {
  selectedReading.value = reading;
  modalOpen.value = true;
};

const onSaved = (updated) => {
  const idx = readings.value.findIndex(r => r.id === updated.id);
  if (idx !== -1) readings.value[idx] = updated;
};

const onDeleted = (deletedId) => {
  readings.value = readings.value.filter(r => r.id !== deletedId);
};
</script>

<style scoped>
.reviews-container { width: 100%; }
.page-title { font-size: 2.2rem; margin-bottom: 1.5rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.5rem; }
.loading-state { text-align: center; padding: 4rem; color: var(--color-text-muted); font-style: italic; }

.reviews-grid { display: flex; flex-direction: column; gap: 1.5rem; }

.review-card {
  display: flex; gap: 1.5rem; padding: 1.5rem;
  border: 1px solid var(--color-border); border-radius: var(--border-radius, 12px);
  cursor: pointer; transition: box-shadow 0.2s;
  background: var(--color-surface);
}
.review-card:hover { box-shadow: var(--shadow-card); }

.review-cover { width: 100px; height: 150px; object-fit: cover; border-radius: 8px; flex-shrink: 0; border: 1px solid var(--color-border); }

.review-details { flex: 1; display: flex; flex-direction: column; }
.review-details h3 { margin: 0 0 0.2rem; font-size: 1.2rem; color: var(--color-text); }
.author { margin: 0 0 0.5rem; color: var(--color-text-muted); font-style: italic; }

.review-meta { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; flex-wrap: wrap; }
.rating { font-weight: 700; font-size: 1.1rem; color: var(--color-primary); }
.fav-tag { display: flex; align-items: center; gap: 0.3rem; font-size: 0.85rem; background: var(--color-bg); color: var(--color-accent, #e11d48); padding: 0.2rem 0.6rem; border-radius: 12px; font-weight: 600; border: 1px solid var(--color-border); }

.quote {
  font-style: italic; background: var(--color-bg); padding: 0.8rem 1rem;
  border-left: 3px solid var(--color-primary); margin: 0.5rem 0; border-radius: 0 6px 6px 0;
  font-size: 0.95rem; color: var(--color-text);
}

.moment { margin-bottom: 0.5rem; font-size: 0.9rem; color: var(--color-text-muted); }

.review-text { font-size: 0.95rem; color: var(--color-text); line-height: 1.5; margin: 0; }

.empty-state {
  text-align: center; padding: 3rem; color: var(--color-text-muted); font-style: italic;
  border: 1px dashed var(--color-border); border-radius: 12px;
}

@media (max-width: 600px) {
  .review-card { flex-direction: column; align-items: center; text-align: center; }
  .review-cover { width: 120px; height: 180px; }
}
</style>
