<template>
  <component
    :is="phosphorIconComponent"
    :size="size"
    :color="color"
    :weight="weight"
    :class="customClass"
  />
</template>

<script setup>
import { computed } from 'vue';
import * as PhosphorIcons from '@phosphor-icons/vue';

const props = defineProps({
  name: {
    type: String,
    required: true,
  },
  size: {
    type: [Number, String],
    default: 24,
  },
  color: {
    type: String,
    default: 'currentColor',
  },
  weight: {
    type: String,
    default: 'regular',
    validator: (value) => ['thin', 'light', 'regular', 'bold', 'fill', 'duotone'].includes(value),
  },
  customClass: {
    type: String,
    default: '',
  },
});

const phosphorIconComponent = computed(() => {
  const iconName = props.name.startsWith('Ph') ? props.name : `Ph${props.name.charAt(0).toUpperCase() + props.name.slice(1)}`;
  return PhosphorIcons[iconName] || null;
});
</script>
