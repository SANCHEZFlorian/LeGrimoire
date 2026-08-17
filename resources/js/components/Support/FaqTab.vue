<template>
  <div class="faq-tab">
    <h2>Foire aux questions</h2>
    <div v-if="faqs.length === 0" class="empty-state">
      Aucune question trouvée pour le moment.
    </div>
    <div class="faq-list" v-else>
      <div v-for="faq in faqs" :key="faq.id" class="faq-item">
        <h3 class="faq-question">{{ faq.question }}</h3>
        <p class="faq-answer">{{ faq.answer }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const faqs = ref([]);

onMounted(async () => {
  try {
    const res = await axios.get('/api/support/faq');
    faqs.value = res.data;
  } catch(e) {
    console.error(e);
  }
});
</script>

<style scoped>
.faq-tab h2 {
  font-size: 1.8rem;
  margin-bottom: 1.5rem;
}
.faq-item {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  border: 1px solid var(--color-border, rgba(0,0,0,0.1));
}
.faq-question {
  font-size: 1.3rem;
  color: var(--color-primary, #721c24);
  margin-bottom: 0.5rem;
}
.faq-answer {
  font-size: 1.1rem;
  color: var(--color-text, #2c1e16);
  white-space: pre-wrap;
}
</style>
