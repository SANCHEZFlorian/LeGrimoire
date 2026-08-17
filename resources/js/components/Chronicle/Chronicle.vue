<template>
  <div class="chronicle-container">

    <!-- Header -->
    <div class="chronicle-header">
      <h1 class="page-title"><Icon name="PhCalendarDots" weight="duotone" /> Chronique de Lecture</h1>
      <div class="year-selector">
        <button class="year-nav-btn" @click="prevYear" :disabled="!canGoPrev">‹</button>
        <span class="year-label">{{ selectedYear }}</span>
        <button class="year-nav-btn" @click="nextYear" :disabled="!canGoNext">›</button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="loading-state">
      <div class="loading-ornament"><Icon name="PhSparkle" weight="duotone" /></div>
      Chargement de votre chronique...
    </div>

    <template v-else-if="data">

      <!-- Stats annuelles -->
      <section class="annual-stats">
        <div class="stats-row">
          <div class="stat-pill">
            <span class="stat-pill-icon"><Icon name="PhBookOpen" weight="duotone" /></span>
            <div>
              <div class="stat-pill-value">{{ data.total_books }}</div>
              <div class="stat-pill-label">livres lus</div>
            </div>
          </div>
          <div class="stat-pill">
            <span class="stat-pill-icon"><Icon name="PhFileText" weight="duotone" /></span>
            <div>
              <div class="stat-pill-value">{{ data.total_pages.toLocaleString('fr-FR') }}</div>
              <div class="stat-pill-label">pages</div>
            </div>
          </div>
          <div class="stat-pill" v-if="hasListening">
            <span class="stat-pill-icon"><Icon name="PhHeadphones" weight="duotone" /></span>
            <div>
              <div class="stat-pill-value">{{ data.total_listening }}</div>
              <div class="stat-pill-label">heures écoutées</div>
            </div>
          </div>
          <div class="stat-pill" v-if="data.top_genre">
            <span class="stat-pill-icon"><Icon name="PhTag" weight="duotone" /></span>
            <div>
              <div class="stat-pill-value">{{ data.top_genre }}</div>
              <div class="stat-pill-label">genre principal</div>
            </div>
          </div>
          <div class="stat-pill" v-if="data.top_author">
            <span class="stat-pill-icon"><Icon name="PhPenNib" weight="duotone" /></span>
            <div>
              <div class="stat-pill-value">{{ data.top_author }}</div>
              <div class="stat-pill-label">auteur principal</div>
            </div>
          </div>
          <div class="stat-pill" v-if="data.avg_rating !== null">
            <span class="stat-pill-icon"><Icon name="PhStar" weight="duotone" /></span>
            <div>
              <div class="stat-pill-value">{{ formatRating(data.avg_rating) }}</div>
              <div class="stat-pill-label">note moyenne</div>
            </div>
          </div>
        </div>
      </section>

      <!-- Coup de cœur de l'année -->
      <section class="year-favorite-section" v-if="data.year_favorite">
        <div class="section-ornament">✦ ✦ ✦</div>
        <h2>Coup de cœur {{ selectedYear }}</h2>
        <div class="year-favorite-card" @click="openDetail(data.year_favorite)">
          <div class="fav-cover-wrapper">
            <img
              :src="data.year_favorite.book.cover_url || placeholder"
              :alt="data.year_favorite.book.title"
              class="fav-cover"
            />
            <div class="fav-crown"><Icon name="PhCrown" weight="fill" /></div>
          </div>
          <div class="fav-info">
            <div class="fav-badge-label">Coup de cœur de l'année</div>
            <h3>{{ data.year_favorite.book.title }}</h3>
            <p class="fav-author">{{ data.year_favorite.book.author }}</p>
            <p class="fav-genre" v-if="data.year_favorite.book.genre">{{ data.year_favorite.book.genre }}</p>
            <p class="fav-rating" v-if="data.year_favorite.rating !== null">
              {{ formatRating(data.year_favorite.rating) }}
            </p>
            <p class="fav-date">
              Terminé le {{ formatDate(data.year_favorite.end_date) }}
            </p>
            <p class="fav-review" v-if="data.year_favorite.review">
              « {{ truncate(data.year_favorite.review, 200) }} »
            </p>
          </div>
        </div>
        <div class="section-ornament">✦ ✦ ✦</div>
      </section>

      <!-- Message si aucune lecture -->
      <div v-if="data.total_books === 0" class="empty-year">
        <div class="empty-icon"><Icon name="PhMailbox" weight="duotone" /></div>
        <p>Aucune lecture terminée en {{ selectedYear }}.</p>
      </div>

      <!-- Timeline des mois -->
      <section class="timeline-section" v-if="data.total_books > 0">
        <h2>Mois par mois</h2>
        <div class="months-timeline">
          <div
            v-for="month in activeMonths"
            :key="month.month_number"
            class="month-block"
            :data-season="getSeason(month.month_number)"
          >
            <!-- En-tête du mois -->
            <div class="month-header">
              <div class="month-connector"></div>
              <div class="month-badge">
                <span class="month-number">{{ padMonth(month.month_number) }}</span>
              </div>
              <div class="month-title-area">
                <h3 class="month-name">{{ capitalize(month.month_name) }}</h3>
                <div class="month-meta">
                  <span class="month-count">{{ month.total_books }} livre{{ month.total_books > 1 ? 's' : '' }}</span>
                  <span class="month-sep" v-if="month.total_pages > 0"> · </span>
                  <span class="month-pages" v-if="month.total_pages > 0">{{ month.total_pages.toLocaleString('fr-FR') }} pages</span>
                  <span class="month-sep" v-if="month.top_genre"> · </span>
                  <span class="month-genre" v-if="month.top_genre">{{ month.top_genre }}</span>
                  <span class="month-sep" v-if="month.avg_rating !== null"> · </span>
                  <span class="month-avg" v-if="month.avg_rating !== null">moy. {{ formatRating(month.avg_rating) }}</span>
                </div>
              </div>
            </div>

            <!-- Grille des couvertures -->
            <div class="month-books-grid">
              <div
                v-for="reading in month.books"
                :key="reading.id"
                class="book-thumb"
                :class="{ 'is-fav': isFavorite(reading, month) }"
                @click="openDetail(reading)"
                :title="reading.book.title + ' — ' + reading.book.author"
              >
                <img
                  :src="reading.book.cover_url || placeholder"
                  :alt="reading.book.title"
                  class="book-thumb-img"
                />
                <!-- Badge favori du mois -->
                <div class="thumb-crown" v-if="isFavorite(reading, month)" title="Coup de cœur du mois"><Icon name="PhCrown" weight="fill" /></div>
                <!-- Badge favori perso -->
                <div class="thumb-heart" v-else-if="reading.is_favorite" title="Coup de cœur"><Icon name="PhHeart" weight="fill" /></div>
                <!-- Note -->
                <div class="thumb-rating" v-if="reading.rating !== null">{{ formatRating(reading.rating) }}</div>

                <!-- Tooltip hover -->
                <div class="book-tooltip">
                  <strong>{{ reading.book.title }}</strong>
                  <span>{{ reading.book.author }}</span>
                  <span v-if="reading.book.genre">{{ reading.book.genre }}</span>
                  <span v-if="reading.rating !== null">{{ formatRating(reading.rating) }}</span>
                  <span>{{ formatDate(reading.end_date) }}</span>
                </div>
              </div>
            </div>

            <!-- Coup de cœur du mois (si différent de l'annuel) -->
            <div
              class="month-favorite-strip"
              v-if="month.favorite && month.total_books > 1"
              @click="openDetail(month.favorite)"
            >
              <span class="mf-label"><Icon name="PhCrown" weight="fill" /> Coup de cœur :</span>
              <img
                :src="month.favorite.book.cover_url || placeholder"
                :alt="month.favorite.book.title"
                class="mf-cover"
              />
              <span class="mf-title">{{ month.favorite.book.title }}</span>
              <span class="mf-author">— {{ month.favorite.book.author }}</span>
              <span class="mf-rating" v-if="month.favorite.rating !== null">
                {{ formatRating(month.favorite.rating) }}
              </span>
            </div>
          </div>
        </div>
      </section>

    </template>

    <!-- Modale -->
    <BookDetailModal
      :visible="modalOpen"
      :reading="selectedReading"
      :ratingSystem="ratingSystem"
      @close="modalOpen = false"
      @saved="onSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import BookDetailModal from '../Shared/BookDetailModal.vue';
import Icon from '../Shared/Icon.vue';

const router = useRouter();
const placeholder = 'https://via.placeholder.com/100x150.png?text=?';

const isLoading   = ref(true);
const data        = ref(null);
const selectedYear = ref(new Date().getFullYear());
const modalOpen   = ref(false);
const selectedReading = ref(null);
const ratingSystem = ref('20_points');

// ───── Fetch ─────
const fetchChronicle = async () => {
  isLoading.value = true;
  try {
    const res = await axios.get('/api/chronicle', { params: { year: selectedYear.value } });
    data.value = res.data;
    ratingSystem.value = res.data.rating_system || '20_points';
  } catch (err) {
    if (err.response?.status === 401) router.push('/login');
    else console.error('Erreur chronicle', err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchChronicle);

// ───── Navigation années ─────
const availableYears = computed(() => data.value?.available_years ?? [new Date().getFullYear()]);
const canGoPrev = computed(() => {
  const idx = availableYears.value.indexOf(selectedYear.value);
  return idx > 0;
});
const canGoNext = computed(() => {
  const idx = availableYears.value.indexOf(selectedYear.value);
  return idx < availableYears.value.length - 1;
});

const prevYear = () => {
  const idx = availableYears.value.indexOf(selectedYear.value);
  if (idx > 0) { selectedYear.value = availableYears.value[idx - 1]; }
};
const nextYear = () => {
  const idx = availableYears.value.indexOf(selectedYear.value);
  if (idx < availableYears.value.length - 1) { selectedYear.value = availableYears.value[idx + 1]; }
};

watch(selectedYear, fetchChronicle);

// ───── Computed ─────
const activeMonths = computed(() =>
  (data.value?.months ?? []).filter(m => m.total_books > 0)
);

const hasListening = computed(() => {
  const t = data.value?.total_listening;
  return t && t !== '0:00:00';
});

// ───── Helpers ─────
const isFavorite = (reading, month) => {
  return month.favorite && reading.id === month.favorite.id;
};

const formatRating = (r) => {
  if (r === null || r === undefined) return '';
  if (ratingSystem.value === '5_stars') return r + ' ⭐';
  if (ratingSystem.value === '10_points') return r + '/10';
  return r + '/20';
};

const formatDate = (d) => {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
};

const capitalize = (s) => s ? s.charAt(0).toUpperCase() + s.slice(1) : s;

const padMonth = (m) => String(m).padStart(2, '0');

const truncate = (s, n) => s && s.length > n ? s.slice(0, n).trimEnd() + '…' : s;

const openDetail = (reading) => {
  selectedReading.value = reading;
  modalOpen.value = true;
};

const getSeason = (monthNumber) => {
  const m = parseInt(monthNumber);
  if (m >= 3 && m <= 5) return 'printemps';
  if (m >= 6 && m <= 8) return 'ete';
  if (m >= 9 && m <= 11) return 'automne';
  return 'hiver';
};

const onSaved = () => { fetchChronicle(); };
</script>

<style scoped>
/* ── Container ── */
.chronicle-container {
  width: 100%;
}

/* ── Header ── */
.chronicle-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2.5rem;
  border-bottom: 2px solid var(--color-border);
  padding-bottom: 1.5rem;
}
.page-title {
  font-size: 2.2rem;
  margin: 0;
  color: var(--color-primary);
}
.year-selector {
  display: flex;
  align-items: center;
  gap: 1rem;
}
.year-nav-btn {
  background: none;
  border: 1.5px solid var(--color-border);
  color: var(--color-primary);
  width: 36px; height: 36px;
  border-radius: 50%;
  font-size: 1.4rem;
  line-height: 1;
  cursor: pointer;
  transition: all 0.2s;
  display: flex; align-items: center; justify-content: center;
}
.year-nav-btn:hover:not(:disabled) {
  background: var(--color-primary-light);
}
.year-nav-btn:disabled { opacity: 0.3; cursor: default; }
.year-label {
  font-size: 1.8rem;
  font-family: var(--font-heading);
  color: var(--color-primary);
  min-width: 80px;
  text-align: center;
}

/* ── Loading ── */
.loading-state {
  text-align: center;
  padding: 5rem;
  font-size: 1.2rem;
  color: var(--color-text-muted);
  font-style: italic;
}
.loading-ornament {
  font-size: 3rem;
  color: var(--color-primary);
  margin-bottom: 1rem;
  animation: spin 3s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Stats annuelles ── */
.annual-stats {
  margin-bottom: 2.5rem;
}
.stats-row {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}
.stat-pill {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 50px;
  padding: 0.6rem 1.2rem;
  transition: box-shadow 0.2s;
}
.stat-pill:hover { box-shadow: var(--shadow-card); }
.stat-pill-icon { font-size: 1.3rem; color: var(--color-primary); }
.stat-pill-value { font-size: 1.1rem; font-weight: 700; color: var(--color-primary); line-height: 1.2; }
.stat-pill-label { font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.4px; }

/* ── Coup de cœur de l'année ── */
.year-favorite-section {
  margin-bottom: 3rem;
  text-align: center;
}
.section-ornament {
  color: var(--color-border);
  letter-spacing: 0.8rem;
  font-size: 0.9rem;
  margin: 0.5rem 0;
}
.year-favorite-section h2 { margin: 0.5rem 0 1.2rem; color: var(--color-primary); }
.year-favorite-card {
  display: inline-flex;
  gap: 2rem;
  align-items: flex-start;
  background: var(--color-surface);
  border: 1.5px solid var(--color-border);
  border-radius: var(--border-radius);
  padding: 1.5rem 2rem;
  cursor: pointer;
  transition: box-shadow 0.25s, transform 0.25s;
  text-align: left;
  max-width: 680px;
  width: 100%;
}
.year-favorite-card:hover {
  box-shadow: var(--shadow-card);
  transform: translateY(-3px);
}
.fav-cover-wrapper {
  position: relative;
  flex-shrink: 0;
}
.fav-cover {
  width: 110px;
  height: 165px;
  object-fit: cover;
  border-radius: 8px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}
.fav-crown {
  position: absolute;
  top: -14px; left: 50%;
  transform: translateX(-50%);
  font-size: 1.6rem;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
}
.fav-info { flex: 1; min-width: 0; }
.fav-badge-label {
  display: inline-block;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--color-primary);
  border: 1px solid var(--color-border);
  border-radius: 20px;
  padding: 0.15rem 0.6rem;
  margin-bottom: 0.6rem;
}
.fav-info h3 { margin: 0 0 0.2rem; font-size: 1.4rem; color: var(--color-primary); }
.fav-author { margin: 0 0 0.2rem; color: var(--color-text-muted); font-style: italic; }
.fav-genre { margin: 0 0 0.3rem; font-size: 0.9rem; color: var(--color-text-muted); }
.fav-rating { font-size: 1.1rem; font-weight: 700; color: var(--color-primary); margin: 0 0 0.3rem; }
.fav-date { font-size: 0.85rem; color: var(--color-text-muted); margin: 0 0 0.6rem; }
.fav-review { font-style: italic; color: var(--color-text); font-size: 0.95rem; line-height: 1.5; margin: 0; }

/* ── Empty year ── */
.empty-year {
  text-align: center;
  padding: 4rem;
  color: var(--color-text-muted);
}
.empty-icon { font-size: 3rem; margin-bottom: 1rem; }

/* ── Timeline ── */
.timeline-section h2 { margin-bottom: 2rem; color: var(--color-primary); }
.months-timeline {
  display: flex;
  flex-direction: column;
  gap: 2.5rem;
  position: relative;
}
.months-timeline::before {
  content: '';
  position: absolute;
  left: 22px;
  top: 24px; bottom: 24px;
  width: 2px;
  background: var(--color-border);
  border-radius: 2px;
}

/* ── Month block ── */
.month-block { position: relative; padding-left: 60px; }
.month-connector {
  position: absolute;
  left: -38px;
  top: 22px;
  width: 38px;
  height: 2px;
  background: var(--color-border);
}
.month-badge {
  position: absolute;
  left: -60px;
  top: 10px;
  width: 44px; height: 44px;
  background: var(--color-primary);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
/* override couleur badge si la saison est active localement */
.month-block[data-season="printemps"] .month-badge { background: #4ade80; }
.month-block[data-season="ete"] .month-badge { background: #fbbf24; }
.month-block[data-season="automne"] .month-badge { background: #ea580c; }
.month-block[data-season="hiver"] .month-badge { background: #38bdf8; }

.month-number {
  font-family: var(--font-body);
  font-weight: 700;
  font-size: 1rem;
  color: #fff;
}

/* ── Month header ── */
.month-header {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1rem;
}
.month-title-area { flex: 1; }
.month-name {
  margin: 0 0 0.2rem;
  font-size: 1.3rem;
  color: var(--color-primary);
}
.month-meta {
  font-size: 0.85rem;
  color: var(--color-text-muted);
}
.month-sep { margin: 0 0.3rem; }
.month-count { font-weight: 600; color: var(--color-text); }
.month-genre { font-style: italic; }
.month-avg { font-weight: 600; }

/* ── Books grid ── */
.month-books-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

/* ── Book thumbnail ── */
.book-thumb {
  position: relative;
  width: 70px; height: 105px;
  cursor: pointer;
  border-radius: 5px;
  overflow: visible;
  transition: transform 0.2s;
  flex-shrink: 0;
}
.book-thumb:hover { transform: translateY(-6px) scale(1.05); z-index: 10; }
.book-thumb.is-fav .book-thumb-img {
  box-shadow: 0 0 0 2.5px var(--color-accent), 0 6px 20px rgba(0,0,0,0.2);
}
.book-thumb-img {
  width: 100%; height: 100%;
  object-fit: cover;
  border-radius: 5px;
  border: 1px solid var(--color-border);
  display: block;
}
.thumb-crown {
  position: absolute;
  top: -12px; left: 50%;
  transform: translateX(-50%);
  font-size: 1.1rem;
  filter: drop-shadow(0 1px 3px rgba(0,0,0,0.3));
}
.thumb-heart {
  position: absolute;
  top: -8px; right: -6px;
  font-size: 0.9rem;
}
.thumb-rating {
  position: absolute;
  bottom: -8px; left: 50%;
  transform: translateX(-50%);
  background: rgba(0,0,0, 0.8);
  color: #fff;
  font-size: 0.62rem;
  padding: 1px 5px;
  border-radius: 8px;
  font-weight: 700;
  white-space: nowrap;
  pointer-events: none;
}

/* ── Tooltip ── */
.book-tooltip {
  display: none;
  position: absolute;
  bottom: calc(100% + 16px);
  left: 50%;
  transform: translateX(-50%);
  background: var(--color-surface);
  backdrop-filter: blur(10px);
  border: 1px solid var(--color-border);
  color: var(--color-text);
  border-radius: 8px;
  padding: 0.6rem 0.9rem;
  min-width: 160px;
  max-width: 220px;
  z-index: 50;
  pointer-events: none;
  flex-direction: column;
  gap: 0.15rem;
  box-shadow: var(--shadow-card);
}
.book-tooltip strong { font-size: 0.9rem; font-family: var(--font-heading); color: var(--color-primary); }
.book-tooltip span { font-size: 0.78rem; color: var(--color-text-muted); }
.book-thumb:hover .book-tooltip { display: flex; }

/* ── Month favorite strip ── */
.month-favorite-strip {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  background: var(--color-surface);
  border: 1px solid var(--color-accent);
  border-radius: 50px;
  padding: 0.35rem 1rem 0.35rem 0.6rem;
  cursor: pointer;
  transition: background 0.2s;
  margin-top: 0.5rem;
}
.month-favorite-strip:hover { background: var(--color-primary-light); }
.mf-label { font-size: 0.8rem; color: var(--color-text); font-weight: 600; white-space: nowrap; }
.mf-cover { width: 28px; height: 42px; object-fit: cover; border-radius: 3px; flex-shrink: 0; }
.mf-title { font-size: 0.85rem; font-weight: 700; color: var(--color-text); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.mf-author { font-size: 0.78rem; color: var(--color-text-muted); font-style: italic; white-space: nowrap; }
.mf-rating { font-size: 0.8rem; font-weight: 700; color: var(--color-primary); white-space: nowrap; }

/* ── Responsive ── */
@media (max-width: 700px) {
  .chronicle-header { flex-direction: column; gap: 1rem; align-items: flex-start; }
  .year-favorite-card { flex-direction: column; align-items: center; text-align: center; }
  .months-timeline::before { left: 18px; }
  .month-block { padding-left: 50px; }
  .month-badge { left: -52px; width: 38px; height: 38px; }
  .book-thumb { width: 58px; height: 87px; }
}
</style>
