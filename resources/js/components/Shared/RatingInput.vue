<template>
  <div class="rating-input" :class="'mode-' + system">
    <!-- Mode 5 étoiles -->
    <div v-if="system === '5_stars'" class="stars-container">
      <span
        v-for="star in 10"
        :key="star"
        class="star-half"
        :class="{
          filled: modelValue >= star * 0.5,
          'is-right': star % 2 === 0
        }"
        @click="setRating(star * 0.5)"
        @mouseenter="hoverValue = star * 0.5"
        @mouseleave="hoverValue = null"
      >
        <span class="star-icon" :class="{ highlighted: hoverValue && hoverValue >= star * 0.5 }">
          <Icon v-if="star % 2 === 1" name="PhStar" weight="fill" />
        </span>
      </span>
      <span class="rating-label" v-if="displayValue !== null">{{ displayValue }}/5</span>
    </div>

    <!-- Mode note sur 10 -->
    <div v-else-if="system === '10_points'" class="numeric-container">
      <input
        type="number"
        :value="modelValue"
        @input="$emit('update:modelValue', parseFloat($event.target.value) || null)"
        min="0"
        max="10"
        step="0.5"
        class="rating-number-input"
      />
      <span class="rating-suffix">/10</span>
    </div>

    <!-- Mode note sur 20 (défaut) -->
    <div v-else class="numeric-container">
      <input
        type="number"
        :value="modelValue"
        @input="$emit('update:modelValue', parseFloat($event.target.value) || null)"
        min="0"
        max="20"
        step="1"
        class="rating-number-input"
      />
      <span class="rating-suffix">/20</span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import Icon from './Icon.vue';

const props = defineProps({
  modelValue: { type: Number, default: null },
  system: { type: String, default: '20_points' }, // '5_stars', '10_points', '20_points'
});

const emit = defineEmits(['update:modelValue']);
const hoverValue = ref(null);

const displayValue = computed(() => {
  if (props.modelValue === null || props.modelValue === undefined) return null;
  return props.modelValue;
});

const setRating = (value) => {
  // Si on clique sur la même note, on la retire
  if (props.modelValue === value) {
    emit('update:modelValue', null);
  } else {
    emit('update:modelValue', value);
  }
};
</script>

<style scoped>
.rating-input {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

/* === Étoiles === */
.stars-container {
  display: inline-flex;
  align-items: center;
  gap: 0;
  cursor: pointer;
  user-select: none;
}

.star-half {
  display: inline-block;
  font-size: 1.6rem;
  color: var(--color-border);
  transition: color 0.15s ease;
  line-height: 1;
}

.star-half.filled .star-icon,
.star-half .star-icon.highlighted {
  color: var(--color-primary);
}

.star-icon {
  pointer-events: none;
  display: inline-block;
}

.rating-label {
  margin-left: 0.75rem;
  font-size: 1rem;
  color: var(--color-text-muted);
  font-weight: 600;
}

/* === Numérique === */
.numeric-container {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.rating-number-input {
  width: 70px;
  padding: 0.4rem 0.5rem;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  font-size: 1.1rem;
  text-align: center;
  background: var(--color-bg);
  color: var(--color-text);
}

.rating-number-input:focus {
  outline: none;
  border-color: var(--color-primary);
}

.rating-suffix {
  font-size: 1.1rem;
  color: var(--color-text-muted);
  font-weight: 600;
}
</style>
