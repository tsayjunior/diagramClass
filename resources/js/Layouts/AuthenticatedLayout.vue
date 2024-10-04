<template>
  <div>
    <div class="flex h-screen bg-gray-50">
      <Navigation v-if="show_navigation" />
      <NavigationMobile />

      <div class="flex flex-col flex-1 w-full">
        <TopMenu :show_navigation="show_back"/>

        <main class="h-full overflow-y-auto">
          <div class="container px-6 mx-auto grid">
            <h2 class="my-6 text-2xl font-semibold text-gray-700">
              <slot name="header" />
            </h2>

            <slot />
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'; // Asegúrate de importar defineProps

// Definición del prop con tipo y valor predeterminado
const props = defineProps({
  show_navigation: {
    type: Boolean,
    default: true, // Puedes establecer un valor predeterminado si lo deseas
  },
  show_back: {
    type: Boolean,
    default: false, // Puedes establecer un valor predeterminado si lo deseas
  },
});
import Navigation from './Navigation.vue';
import TopMenu from "./TopMenu.vue";
import NavigationMobile from './NavigationMobile.vue';
</script>
