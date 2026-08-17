<template>
  <div class="custom-select-wrapper" ref="selectRef">
    <div 
      class="custom-select-trigger" 
      :class="{ 'is-open': isOpen }"
      @click="toggle"
    >
      <span>{{ selectedLabel }}</span>
      <Icon name="PhCaretDown" weight="bold" size="14" class="caret" />
    </div>

    <div class="custom-select-dropdown" v-if="isOpen">
      <div 
        class="custom-select-option" 
        v-for="opt in normalizedOptions" 
        :key="opt.value"
        :class="{ 'is-selected': opt.value === modelValue }"
        @click="selectOption(opt.value)"
      >
        {{ opt.label }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import Icon from './Icon.vue';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  options: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const selectRef = ref(null);

const toggle = () => {
  isOpen.value = !isOpen.value;
};

const selectOption = (val) => {
  emit('update:modelValue', val);
  emit('change', val);
  isOpen.value = false;
};

// Normalize options to handle both array of strings and array of objects
const normalizedOptions = computed(() => {
  return props.options.map(opt => {
    if (typeof opt === 'object' && opt !== null) {
      return { value: opt.value, label: opt.label || opt.value };
    }
    return { value: opt, label: opt };
  });
});

const selectedLabel = computed(() => {
  const selected = normalizedOptions.value.find(o => o.value === props.modelValue);
  return selected ? selected.label : (props.modelValue || 'Sélectionner');
});

const handleClickOutside = (e) => {
  if (selectRef.value && !selectRef.value.contains(e.target)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.custom-select-wrapper {
  position: relative;
  width: 100%;
}

.custom-select-trigger {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 1rem;
  background-color: var(--color-surface, rgba(255, 255, 255, 0.8));
  border: 1px solid var(--color-border, rgba(44, 30, 22, 0.2));
  border-radius: 8px;
  color: var(--color-text);
  font-family: var(--font-body, 'Cormorant Garamond', serif);
  font-size: 1rem;
  cursor: pointer;
  box-shadow: var(--shadow-sm, 0 1px 2px rgba(0, 0, 0, 0.05));
  transition: all var(--transition-fast, 0.2s ease);
  min-height: 40px;
}

.custom-select-trigger:hover, .custom-select-trigger.is-open {
  border-color: var(--color-primary, #721c24);
}

.custom-select-trigger.is-open {
  box-shadow: 0 0 0 3px var(--color-primary-light, rgba(114, 28, 36, 0.15));
}

.caret {
  transition: transform 0.2s ease;
  color: var(--color-text);
}

.custom-select-trigger.is-open .caret {
  transform: rotate(180deg);
}

.custom-select-dropdown {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  min-width: 100%;
  width: max-content;
  background-color: var(--color-bg, #fff7ed);
  border: 1px solid var(--color-border, rgba(44, 30, 22, 0.2));
  border-radius: 8px;
  box-shadow: var(--shadow-card, 0 4px 12px rgba(0,0,0,0.1));
  z-index: 100;
  max-height: 250px;
  overflow-y: auto;
  padding: 0.25rem 0;
}

/* Scrollbar styles for the dropdown */
.custom-select-dropdown::-webkit-scrollbar {
  width: 6px;
}
.custom-select-dropdown::-webkit-scrollbar-track {
  background: transparent;
}
.custom-select-dropdown::-webkit-scrollbar-thumb {
  background: var(--color-primary-light, rgba(114, 28, 36, 0.2));
  border-radius: 4px;
}
.custom-select-dropdown::-webkit-scrollbar-thumb:hover {
  background: var(--color-primary, #721c24);
}

.custom-select-option {
  padding: 0.5rem 1rem;
  font-family: var(--font-body, 'Cormorant Garamond', serif);
  font-size: 1rem;
  color: var(--color-text);
  cursor: pointer;
  transition: background-color 0.2s, color 0.2s;
  white-space: nowrap;
}

.custom-select-option:hover {
  background-color: var(--color-primary-light, rgba(114, 28, 36, 0.05));
  color: var(--color-primary, #721c24);
}

.custom-select-option.is-selected {
  background-color: var(--color-primary-light, rgba(114, 28, 36, 0.1));
  color: var(--color-primary, #721c24);
  font-weight: bold;
}
</style>
