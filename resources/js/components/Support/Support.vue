<template>
  <div class="support-page">
    <div class="header-banner">
      <h1>Aide & Support</h1>
      <p>Comment pouvons-nous vous aider aujourd'hui ?</p>
    </div>

    <div class="support-container">
      <div class="tabs">
        <button 
          @click="activeTab = 'faq'" 
          :class="['tab-btn', { active: activeTab === 'faq' }]"
        >
          <Icon name="PhInfo" weight="duotone" /> FAQ
        </button>
        <button 
          @click="activeTab = 'suggestions'" 
          :class="['tab-btn', { active: activeTab === 'suggestions' }]"
        >
          <Icon name="PhLightbulb" weight="duotone" /> Boîte à idées
        </button>
        <button 
          @click="activeTab = 'contact'" 
          :class="['tab-btn', { active: activeTab === 'contact' }]"
        >
          <Icon name="PhEnvelopeSimple" weight="duotone" /> Contacter le Support
        </button>
      </div>

      <div class="tab-content">
        <FaqTab v-if="activeTab === 'faq'" />
        <SuggestionsTab v-if="activeTab === 'suggestions'" />
        <ContactTab v-if="activeTab === 'contact'" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import Icon from '../Shared/Icon.vue';
import FaqTab from './FaqTab.vue';
import SuggestionsTab from './SuggestionsTab.vue';
import ContactTab from './ContactTab.vue';

const activeTab = ref('faq');
</script>

<style scoped>
.support-page {
  max-width: 1000px;
  margin: 0 auto;
  padding: 2rem;
}

.header-banner {
  text-align: center;
  margin-bottom: 3rem;
}

.header-banner h1 {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
}

.header-banner p {
  color: var(--color-text-light, #5c4d42);
  font-size: 1.2rem;
}

.support-container {
  background: var(--color-surface, rgba(255, 255, 255, 0.8));
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  border: 1px solid var(--color-border, rgba(0, 0, 0, 0.1));
  overflow: hidden;
}

.tabs {
  display: flex;
  border-bottom: 1px solid var(--color-border, rgba(0, 0, 0, 0.1));
  background: var(--color-surface-dim, rgba(0, 0, 0, 0.02));
}

.tab-btn {
  flex: 1;
  padding: 1.25rem 1rem;
  background: none;
  border: none;
  border-bottom: 3px solid transparent;
  cursor: pointer;
  font-family: var(--font-body, 'Cormorant Garamond', serif);
  font-size: 1.2rem;
  font-weight: 600;
  color: var(--color-text-light, #5c4d42);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.2s;
}

.tab-btn:hover {
  background: var(--color-primary-light, rgba(114, 28, 36, 0.05));
  color: var(--color-primary, #721c24);
}

.tab-btn.active {
  color: var(--color-primary, #721c24);
  border-bottom-color: var(--color-primary, #721c24);
  background: white;
}

.tab-content {
  padding: 2rem;
}
</style>
