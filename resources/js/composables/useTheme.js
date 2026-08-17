import { ref, onMounted } from 'vue';
import axios from 'axios';

// État partagé globalement
const activeTheme = ref('grimoire');
const activeSeason = ref('printemps');
const themeMode = ref('auto'); // auto, fixed, chronicle_auto

export function useTheme() {
  const fetchTheme = async () => {
    try {
      const { data } = await axios.get('/api/theme');
      activeTheme.value = data.theme_name || 'grimoire';
      activeSeason.value = data.active_season || getCurrentSeason();
      themeMode.value = data.theme_mode || 'auto';
      
      applyTheme();
    } catch (e) {
      console.error("Erreur récupération thème", e);
      // Fallback
      activeTheme.value = 'grimoire';
      activeSeason.value = getCurrentSeason();
      applyTheme();
    }
  };

  const applyTheme = () => {
    document.documentElement.setAttribute('data-theme', activeTheme.value);
    document.documentElement.setAttribute('data-season', activeSeason.value);
  };

  const getCurrentSeason = () => {
    const month = new Date().getMonth() + 1; // 1-12
    if (month >= 3 && month <= 5) return 'printemps';
    if (month >= 6 && month <= 8) return 'ete';
    if (month >= 9 && month <= 11) return 'automne';
    return 'hiver';
  };

  const updateTheme = async (mode, theme, season) => {
    try {
      await axios.post('/api/theme', {
        theme_mode: mode,
        theme_name: theme,
        fixed_season: season
      });
      activeTheme.value = theme;
      activeSeason.value = mode === 'auto' ? getCurrentSeason() : season;
      themeMode.value = mode;
      applyTheme();
    } catch (e) {
      console.error("Erreur maj thème", e);
    }
  };

  return {
    activeTheme,
    activeSeason,
    themeMode,
    fetchTheme,
    applyTheme,
    updateTheme,
    getCurrentSeason
  };
}
