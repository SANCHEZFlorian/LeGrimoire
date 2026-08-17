<template>
  <div class="spice-rating" :class="{ readonly: !editable }">
    <span
      v-for="n in 5"
      :key="n"
      class="spice-icon"
      :class="{ active: modelValue >= n, hovered: editable && hoverVal >= n }"
      @click="editable && toggle(n)"
      @mouseenter="editable && (hoverVal = n)"
      @mouseleave="editable && (hoverVal = 0)"
    >
      <Icon name="PhFlame" weight="fill" />
    </span>
    <span v-if="modelValue && modelValue > 0" class="spice-label">{{ modelValue }}/5</span>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import Icon from './Icon.vue';

const props = defineProps({
  modelValue: { type: Number, default: 0 },
  editable: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue']);
const hoverVal = ref(0);

const toggle = (n) => {
  emit('update:modelValue', props.modelValue === n ? 0 : n);
};
</script>

<style scoped>
.spice-rating {
  display: inline-flex;
  align-items: center;
  gap: 0.15rem;
}

.spice-icon {
  font-size: 1.3rem;
  opacity: 0.25;
  transition: opacity 0.15s ease, transform 0.15s ease;
  cursor: pointer;
  color: var(--color-accent, #ef4444);
  display: inline-flex;
}

.spice-rating.readonly .spice-icon {
  cursor: default;
}

.spice-icon.active {
  opacity: 1;
}

.spice-icon.hovered {
  opacity: 0.7;
  transform: scale(1.15);
}

.spice-label {
  margin-left: 0.5rem;
  font-size: 0.85rem;
  color: var(--color-text-muted);
  font-weight: 600;
}
</style>
