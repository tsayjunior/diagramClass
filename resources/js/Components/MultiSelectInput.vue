<template>
  <div class="relative text-gray-500 focus-within:text-purple-600 dark:focus-within:text-purple-400">
    <select 
      class="rounded block w-full pl-10 mt-1 text-sm text-black focus:outline-none focus:shadow-outline-purple form-input" 
      :value="modelValue" 
      @change="handleSelect" 
      ref="input" 
      multiple
      :required="required"  
      :placeholder="text"
    >
      <option disabled value=""> {{ text }}</option>
      <option 
        v-for="op in options" 
        :key="op.id" 
        :value="op.id"
      >
        {{ op.country }}
      </option>
    </select>
    
    <div class="absolute inset-y-0 flex items-center ml-3 pointer-events-none">
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

defineProps(['modelValue', 'text', 'options', 'required']);
defineEmits(['update:modelValue']);

const input = ref(null);

const handleSelect = (event) => {
  // Obtener los valores seleccionados como un array
  const selectedOptions = Array.from(event.target.selectedOptions, option => option.value);
  
  // Emitir los valores seleccionados
  emit('update:modelValue', selectedOptions);
};
</script>
