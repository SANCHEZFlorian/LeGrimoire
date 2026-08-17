<template>
  <div class="error-page-container">
    <div class="error-card">
      <div class="error-badge">Erreur 404</div>
      
      <div class="icon-wrapper">
        <Icon name="PhCompass" size="64" weight="duotone" customClass="error-icon" />
      </div>

      <h1 class="error-title">Page Introuvable</h1>
      
      <p class="error-message">
        Il semblerait que cette page ait disparu des archives ou n'ait jamais été écrite dans ce grimoire.
      </p>

      <div class="error-actions">
        <router-link to="/dashboard" class="action-btn primary">
          <Icon name="PhHouse" size="20" weight="bold" />
          <span>Retour au Grimoire</span>
        </router-link>

        <button @click="goBack" class="action-btn secondary">
          <Icon name="PhArrowLeft" size="20" weight="bold" />
          <span>Page précédente</span>
        </button>
      </div>

      <div class="quick-links">
        <span class="quick-title">Raccourcis utiles :</span>
        <div class="links-list">
          <router-link to="/catalog" class="quick-link">
            <Icon name="PhBookOpen" size="16" /> Catalogue
          </router-link>
          <router-link to="/tracker" class="quick-link">
            <Icon name="PhBookmarkSimple" size="16" /> Tracker
          </router-link>
          <router-link to="/tbr" class="quick-link">
            <Icon name="PhStack" size="16" /> Pile à lire
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import Icon from '../Shared/Icon.vue';

const router = useRouter();

const goBack = () => {
  if (window.history.length > 2) {
    router.back();
  } else {
    router.push('/dashboard');
  }
};
</script>

<style scoped>
.error-page-container {
  min-height: calc(100vh - 120px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  box-sizing: border-box;
}

.error-card {
  max-width: 580px;
  width: 100%;
  background: var(--color-surface, #ffffff);
  border: 1px solid var(--color-border, #e5e7eb);
  border-radius: var(--border-radius-lg, 16px);
  padding: 3rem 2rem;
  text-align: center;
  box-shadow: var(--shadow-lg, 0 10px 25px -5px rgba(0, 0, 0, 0.1));
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  overflow: hidden;
}

.error-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--color-primary, #4f46e5), var(--color-primary-light, #818cf8));
}

.error-badge {
  display: inline-block;
  padding: 0.35rem 1rem;
  border-radius: 9999px;
  background: var(--color-primary-subtle, rgba(79, 70, 229, 0.1));
  color: var(--color-primary, #4f46e5);
  font-weight: 700;
  font-size: 0.875rem;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  margin-bottom: 1.5rem;
}

.icon-wrapper {
  color: var(--color-primary, #4f46e5);
  margin-bottom: 1.25rem;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-8px);
  }
}

.error-title {
  font-size: clamp(1.75rem, 4vw, 2.25rem);
  font-weight: 800;
  color: var(--color-text, #111827);
  margin: 0 0 0.75rem 0;
  letter-spacing: -0.025em;
}

.error-message {
  font-size: 1.05rem;
  line-height: 1.6;
  color: var(--color-text-muted, #6b7280);
  margin: 0 0 2rem 0;
  max-width: 440px;
}

.error-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: center;
  width: 100%;
  margin-bottom: 2rem;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  font-size: 0.95rem;
  font-weight: 600;
  border-radius: var(--border-radius, 8px);
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1px solid transparent;
}

.action-btn.primary {
  background: var(--color-primary, #4f46e5);
  color: var(--color-bg, #ffffff);
  box-shadow: var(--shadow-sm, 0 1px 2px rgba(0, 0, 0, 0.05));
}

.action-btn.primary:hover {
  background: var(--color-primary-light, #6366f1);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md, 0 4px 6px -1px rgba(0, 0, 0, 0.1));
}

.action-btn.secondary {
  background: transparent;
  color: var(--color-text, #374151);
  border-color: var(--color-border, #d1d5db);
}

.action-btn.secondary:hover {
  background: var(--color-surface-hover, #f3f4f6);
  border-color: var(--color-text-muted, #9ca3af);
  transform: translateY(-1px);
}

.quick-links {
  border-top: 1px solid var(--color-border, #e5e7eb);
  padding-top: 1.5rem;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}

.quick-title {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-text-muted, #9ca3af);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.links-list {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 1.25rem;
}

.quick-link {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  color: var(--color-primary, #4f46e5);
  font-size: 0.9rem;
  font-weight: 500;
  text-decoration: none;
  transition: opacity 0.2s ease;
}

.quick-link:hover {
  text-decoration: underline;
  opacity: 0.8;
}

@media (max-width: 640px) {
  .error-card {
    padding: 2rem 1.25rem;
  }
  
  .error-actions {
    flex-direction: column;
  }
  
  .action-btn {
    width: 100%;
  }
}
</style>
