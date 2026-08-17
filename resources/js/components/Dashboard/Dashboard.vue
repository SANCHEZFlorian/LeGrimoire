<template>
  <div class="dashboard-container">
    <div class="dashboard-header">
      <h1 class="page-title">Tableau de Bord</h1>
      <div class="year-selector">
        <CustomSelect v-model="selectedYear" :options="yearOptions" @change="fetchDashboard" />
      </div>
    </div>

    <div v-if="isLoading" class="loading-state">
      Chargement de vos statistiques...
    </div>

    <div v-else-if="data" class="dashboard-content">

      <!-- Livres en cours -->
      <section class="current-reading-section" v-if="data.current_readings && data.current_readings.length > 0">
        <div class="section-header">
          <h2 class="section-title"><Icon name="PhBookOpen" weight="duotone" /> Lectures en cours</h2>
          <router-link to="/reading" class="link-all">Gérer les lectures →</router-link>
        </div>
        <div class="current-books-grid">
          <div
            v-for="reading in data.current_readings"
            :key="reading.id"
            class="current-book-card"
          >
            <img :src="reading.book.cover_url || placeholder" alt="Couverture" class="book-cover" />
            <div class="book-details">
              <h3>{{ reading.book.title }}</h3>
              <p class="author">{{ reading.book.author }}</p>
              <div class="progress-wrapper">
                <div class="progress-bar-bg">
                  <div class="progress-bar-fill" :style="{ width: getProgressPct(reading) + '%' }"></div>
                </div>
                <div class="progress-text">
                  <span>{{ reading.current_page }} / {{ reading.book?.pages_count || '?' }}p</span>
                  <span class="progress-pct">{{ getProgressPct(reading) }}%</span>
                </div>
              </div>
            </div>
            <button class="btn-voir" @click="openDetail(reading)">Voir →</button>
          </div>
        </div>
      </section>

      <!-- Widgets Stats -->
      <section class="stats-grid">
        <div class="stat-card">
          <Icon name="PhBookOpenText" weight="duotone" class="stat-icon" />
          <div class="stat-value">{{ data.total_finished }}</div>
          <div class="stat-label">Livres terminés</div>
        </div>
        <div class="stat-card">
          <Icon name="PhFiles" weight="duotone" class="stat-icon" />
          <div class="stat-value">{{ data.total_pages_read.toLocaleString() }}</div>
          <div class="stat-label">Pages lues</div>
        </div>
        <div class="stat-card">
          <Icon name="PhHeadphones" weight="duotone" class="stat-icon" />
          <div class="stat-value">{{ data.heures_ecoutees }}</div>
          <div class="stat-label">Heures écoutées</div>
        </div>
        <div class="stat-card">
          <Icon name="PhChartBar" weight="duotone" class="stat-icon" />
          <div class="stat-value">{{ data.progression_totale }}%</div>
          <div class="stat-label">Progression totale</div>
        </div>
      </section>

      <!-- Stats détaillées -->
      <section class="details-grid">
        <div class="detail-card">
          <span class="detail-label">Genre principal</span>
          <span class="detail-value">{{ data.favorite_genre || '—' }}</span>
        </div>
        <div class="detail-card">
          <span class="detail-label">Auteur principal</span>
          <span class="detail-value">{{ data.main_author || '—' }}</span>
        </div>
        <div class="detail-card">
          <span class="detail-label">Format principal</span>
          <span class="detail-value">{{ data.main_format || '—' }}</span>
        </div>
        <div class="detail-card">
          <span class="detail-label">Meilleur mois</span>
          <span class="detail-value">{{ data.best_month || '—' }} <small v-if="data.best_month">({{ data.best_month_count }})</small></span>
        </div>
        <div class="detail-card" v-if="data.longest_book">
          <span class="detail-label">Livre le plus long</span>
          <span class="detail-value">{{ data.longest_book.title }} <small>({{ data.longest_book.pages_count }}p)</small></span>
        </div>
        <div class="detail-card" v-if="data.reading_goal">
          <span class="detail-label">Objectif annuel</span>
          <span class="detail-value">{{ data.total_finished }} / {{ data.reading_goal }} <small>({{ data.reading_goal_progress }}%)</small></span>
        </div>
      </section>

      <!-- Graphique annuel -->
      <section class="chart-section">
        <h2 class="section-title"><Icon name="PhTrendUp" weight="duotone" /> Bilan Annuel ({{ data.current_year }})</h2>
        <AnnualChart :monthlyStats="data.monthly_stats" :year="data.current_year" />
      </section>

      <!-- Dernier livre terminé -->
      <section class="last-finished-section" v-if="data.last_finished">
        <h2 class="section-title"><Icon name="PhBookBookmark" weight="duotone" /> Dernier livre terminé</h2>
        <div class="last-finished-card" @click="openDetail(data.last_finished)">
          <img :src="data.last_finished.book.cover_url || placeholder" alt="Couverture" class="small-cover" />
          <div>
            <h3>{{ data.last_finished.book.title }}</h3>
            <p class="author">{{ data.last_finished.book.author }}</p>
            <p class="meta" v-if="data.last_finished.book.genre">{{ data.last_finished.book.genre }}</p>
            <p class="meta">{{ formatDate(data.last_finished.start_date) }} → {{ formatDate(data.last_finished.end_date) }}</p>
            <p class="rating-display" v-if="data.last_finished.rating != null">
              {{ formatRating(data.last_finished.rating) }}
            </p>
          </div>
        </div>
      </section>

      <!-- Livres terminés par mois -->
      <section class="monthly-views-section" v-if="data.monthly_reads">
        <h2 class="section-title"><Icon name="PhCalendarDots" weight="duotone" /> Livres terminés par mois</h2>
        <div class="months-grid">
          <div v-for="(reads, month) in data.monthly_reads" :key="month" class="month-card" v-show="reads.length > 0">
            <h3>{{ getMonthName(month) }} <span class="month-count">({{ reads.length }})</span></h3>
            <div class="monthly-books">
              <div
                v-for="read in reads"
                :key="read.id"
                class="monthly-book"
                :title="read.book.title"
                @click="openDetail(read)"
              >
                <img :src="read.book.cover_url || placeholder" alt="Cover" />
                <Icon v-if="read.is_favorite" name="PhHeart" weight="fill" class="fav-badge" />
                <div class="book-rating-badge" v-if="read.rating != null">{{ formatRating(read.rating) }}</div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

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
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import AnnualChart from './AnnualChart.vue';
import BookDetailModal from '../Shared/BookDetailModal.vue';
import Icon from '../Shared/Icon.vue';
import CustomSelect from '../Shared/CustomSelect.vue';

const router = useRouter();
const placeholder = 'https://via.placeholder.com/128x192.png?text=Pas+de+couverture';
const isLoading = ref(true);
const data = ref(null);
const selectedYear = ref(new Date().getFullYear());
const availableYears = ref([new Date().getFullYear()]);
const modalOpen = ref(false);
const selectedReading = ref(null);
const ratingSystem = ref('20_points');

const yearOptions = computed(() => availableYears.value.map(y => ({ value: y, label: y.toString() })));

const fetchDashboard = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/dashboard', { params: { year: selectedYear.value } });
    data.value = response.data;
    availableYears.value = response.data.available_years || [new Date().getFullYear()];
    ratingSystem.value = response.data.rating_system || '20_points';
  } catch (error) {
    if (error.response && error.response.status === 401) {
      router.push('/login');
    } else {
      console.error("Erreur dashboard", error);
    }
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchDashboard);

const getProgressPct = (reading) => {
  const cur = reading.current_page || 0;
  const total = reading.book?.pages_count || 0;
  if (total === 0) return 0;
  return Math.min(100, Math.round((cur / total) * 100));
};

const getMonthName = (m) => {
  const d = new Date();
  d.setMonth(m - 1);
  return d.toLocaleString('fr-FR', { month: 'long' });
};

const formatDate = (d) => {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: '2-digit' });
};

const formatRating = (r) => {
  if (r === null || r === undefined) return '';
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
  fetchDashboard();
};
</script>

<style scoped>
.dashboard-container { width: 100%; }

.dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.page-title { font-size: 2.2rem; margin: 0; color: var(--color-primary); }
.year-selector select {
  padding: 0.5rem 1rem; border: 1px solid var(--color-border); border-radius: 8px;
  font-size: 1rem; background: var(--color-bg); color: var(--color-text); cursor: pointer;
}

.loading-state { text-align: center; padding: 4rem; font-size: 1.3rem; font-style: italic; color: var(--color-text-muted); }

.section-title { display: flex; align-items: center; gap: 0.5rem; color: var(--color-primary); }

/* Livres en cours */
.current-reading-section { margin-bottom: 2.5rem; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.section-header h2 { margin: 0; }
.link-all { font-size: 0.9rem; color: var(--color-primary); text-decoration: none; font-weight: 600; }
.link-all:hover { text-decoration: underline; }
.current-books-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; }
.current-book-card {
  display: flex; gap: 1rem; padding: 1rem; align-items: center;
  border: 1px solid var(--color-border); border-radius: var(--border-radius, 12px);
  background: var(--color-surface);
}
.book-cover { width: 60px; height: 90px; object-fit: cover; border-radius: 6px; flex-shrink: 0; }
.book-details { flex: 1; min-width: 0; }
.book-details h3 { margin: 0 0 0.2rem; font-size: 1rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--color-text); }
.author { margin: 0 0 0.6rem; color: var(--color-text-muted); font-style: italic; font-size: 0.85rem; }
.progress-bar-bg { background: var(--color-border); height: 8px; border-radius: 5px; overflow: hidden; }
.progress-bar-fill { background: var(--color-primary); height: 100%; transition: width 0.4s; border-radius: 5px; }
.progress-text { display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; margin-top: 0.5rem; font-size: 0.8rem; color: var(--color-text-muted); }
.progress-pct { font-weight: 600; }
.btn-voir {
  flex-shrink: 0; padding: 0.4rem 0.8rem; background: none;
  border: 1px solid var(--color-primary); color: var(--color-primary); border-radius: 8px;
  font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;
  white-space: nowrap;
}
.btn-voir:hover { background: var(--color-primary); color: var(--color-bg); }

/* Stats Grid */
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.stat-card {
  text-align: center; padding: 1.5rem 1rem;
  border: 1px solid var(--color-border); border-radius: var(--border-radius, 12px);
  background: var(--color-surface);
}
.stat-icon { font-size: 2rem; margin-bottom: 0.5rem; color: var(--color-primary); }
.stat-value { font-size: 1.8rem; font-weight: 700; color: var(--color-text); }
.stat-label { font-size: 0.85rem; color: var(--color-text-muted); margin-top: 0.3rem; }

/* Details Grid */
.details-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem; margin-bottom: 2.5rem; }
.detail-card {
  display: flex; flex-direction: column; padding: 1rem;
  border: 1px solid var(--color-border); border-radius: 10px;
  background: var(--color-surface);
}
.detail-label { font-size: 0.8rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
.detail-value { font-size: 1.1rem; font-weight: 600; margin-top: 0.3rem; color: var(--color-text); }
.detail-value small { font-weight: 400; color: var(--color-text-muted); }

/* Chart */
.chart-section { margin-bottom: 2.5rem; }
.chart-section h2 { margin-bottom: 1rem; }

/* Dernier livre terminé */
.last-finished-section { margin-bottom: 2.5rem; }
.last-finished-card {
  display: flex; gap: 1.5rem; padding: 1.2rem; cursor: pointer;
  border: 1px solid var(--color-border); border-radius: var(--border-radius, 12px); transition: box-shadow 0.2s;
  background: var(--color-surface);
}
.last-finished-card:hover { box-shadow: var(--shadow-card); }
.small-cover { width: 80px; height: 120px; object-fit: cover; border-radius: 6px; }
.last-finished-card h3 { margin: 0 0 0.2rem; font-size: 1.2rem; color: var(--color-text); }
.meta { color: var(--color-text-muted); font-size: 0.9rem; margin: 0.2rem 0; }
.rating-display { font-weight: 700; font-size: 1.1rem; margin-top: 0.3rem; color: var(--color-primary); }

/* Mois */
.monthly-views-section { margin-top: 2rem; }
.months-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
.month-card { border: 1px solid var(--color-border); border-radius: 10px; padding: 1rem; background: var(--color-surface); }
.month-card h3 { text-transform: capitalize; margin: 0 0 0.75rem; font-size: 1.1rem; color: var(--color-primary); }
.month-count { font-weight: 400; color: var(--color-text-muted); }
.monthly-books { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.monthly-book { position: relative; width: 55px; height: 82px; cursor: pointer; transition: transform 0.15s; }
.monthly-book:hover { transform: translateY(-4px); }
.monthly-book img { width: 100%; height: 100%; object-fit: cover; border-radius: 4px; border: 1px solid var(--color-border); }
.fav-badge { position: absolute; top: -5px; right: -5px; font-size: 1.2rem; color: var(--color-accent, #e11d48); filter: drop-shadow(0 2px 2px rgba(0,0,0,0.2)); }
.book-rating-badge {
  position: absolute; bottom: -4px; left: 50%; transform: translateX(-50%);
  background: rgba(0,0,0,0.7); color: white; font-size: 0.65rem;
  padding: 1px 5px; border-radius: 8px; font-weight: 600; white-space: nowrap;
}

@media (max-width: 700px) {
  .dashboard-header { flex-direction: column; gap: 1rem; align-items: flex-start; }
  .current-book-card { flex-direction: column; align-items: center; text-align: center; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
