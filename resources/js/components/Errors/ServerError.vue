<template>
  <div class="error-page-container">
    <div class="error-card">
      <div class="error-badge server-error">Erreur 500</div>
      
      <div class="icon-wrapper">
        <Icon name="PhWarningCircle" size="64" weight="duotone" customClass="error-icon" />
      </div>

      <h1 class="error-title">Tourbillon dans le Grimoire</h1>
      
      <p class="error-message">
        Une anomalie magique ou une erreur inattendue est survenue côté serveur. Nos archivistes ont été notifiés et réparent le parchemin.
      </p>

      <div class="error-actions">
        <button @click="reloadPage" class="action-btn primary">
          <Icon name="PhArrowClockwise" size="20" weight="bold" />
          <span>Réessayer</span>
        </button>

        <router-link to="/dashboard" class="action-btn secondary">
          <Icon name="PhHouse" size="20" weight="bold" />
          <span>Retour au Grimoire</span>
        </router-link>
      </div>

      <div v-if="errorDetails" class="error-details">
        <details>
          <summary>Détails techniques</summary>
          <pre>{{ errorDetails }}</pre>
        </details>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import Icon from '../Shared/Icon.vue';

const props = defineProps({
  errorDetails: {
    type: String,
    default: null,
  },
});

const reloadPage = () => {
  window.location.reload();
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
  background: linear-gradient(90deg, #ef4444, #f97316);
}

.error-badge.server-error {
  display: inline-block;
  padding: 0.35rem 1rem;
  border-radius: 9999px;
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  font-weight: 700;
  font-size: 0.875rem;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  margin-bottom: 1.5rem;
}

.icon-wrapper {
  color: #ef4444;
  margin-bottom: 1.25rem;
  animation: pulse-error 2s ease-in-out infinite;
}

@keyframes pulse-error {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.08);
    opacity: 0.85;
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

.error-details {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--color-border, #e5e7eb);
  width: 100%;
  text-align: left;
}

.error-details summary {
  cursor: pointer;
  font-size: 0.85rem;
  color: var(--color-text-muted, #9ca3af);
  font-weight: 600;
}

.error-details pre {
  margin-top: 0.5rem;
  padding: 0.75rem;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 6px;
  font-size: 0.8rem;
  overflow-x: auto;
  color: #ef4444;
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
