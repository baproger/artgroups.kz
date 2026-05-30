<template>
  <div>
    <!-- Category tabs -->
    <div class="flex gap-2 flex-wrap mb-4">
      <button
        @click="activeCategory = null"
        :class="[
          'px-4 py-1.5 rounded-full text-sm font-medium transition-all',
          activeCategory === null ? 'bg-[#2e5f99] text-white' : 'bg-white text-gray-600 border border-gray-200 hover:border-[#2e5f99]'
        ]"
      >
        Все
      </button>
      <button
        v-for="cat in categories"
        :key="cat.id"
        @click="activeCategory = cat.id"
        :class="[
          'px-4 py-1.5 rounded-full text-sm font-medium transition-all',
          activeCategory === cat.id ? 'bg-[#2e5f99] text-white' : 'bg-white text-gray-600 border border-gray-200 hover:border-[#2e5f99]'
        ]"
      >
        {{ cat.name }}
      </button>
    </div>

    <!-- Search -->
    <div class="relative mb-4">
      <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
      </svg>
      <input
        v-model="search"
        type="text"
        :placeholder="placeholder"
        class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-[#2e5f99] text-sm"
      />
    </div>

    <!-- Grid -->
    <div v-if="filtered.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
      <button
        v-for="item in filtered"
        :key="item.id"
        @click="emit('select', item)"
        class="group bg-white border-2 rounded-xl overflow-hidden text-left transition-all hover:border-[#2e5f99] hover:shadow-md"
        :class="selected?.id === item.id ? 'border-[#2e5f99] shadow-md' : 'border-gray-100'"
      >
        <div class="aspect-square bg-gray-50 relative overflow-hidden">
          <img
            v-if="item.image_url"
            :src="item.image_url"
            :alt="item.name"
            class="w-full h-full object-contain p-3 transition-transform group-hover:scale-105"
          />
          <div v-else class="w-full h-full flex items-center justify-center text-gray-200">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <div v-if="selected?.id === item.id" class="absolute top-1.5 right-1.5 w-6 h-6 bg-[#2e5f99] rounded-full flex items-center justify-center">
            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
        </div>
        <div class="px-2 py-2">
          <p class="font-medium text-gray-900 text-xs truncate">{{ item.name }}</p>
          <div v-if="item.colors && Object.keys(item.colors).length" class="flex gap-1 mt-1">
            <div
              v-for="(hex, name) in item.colors"
              :key="name"
              :title="name"
              :style="{ backgroundColor: hex }"
              class="w-3 h-3 rounded-full border border-gray-200"
            />
          </div>
        </div>
      </button>
    </div>

    <div v-else class="text-center py-10 text-gray-400 text-sm">
      Ничего не найдено
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  items: Array,
  categories: Array,
  selected: Object,
  placeholder: { type: String, default: 'Поиск...' },
});

const emit = defineEmits(['select']);

const activeCategory = ref(null);
const search = ref('');

const filtered = computed(() =>
  props.items.filter(item => {
    const matchCat = activeCategory.value === null || item.category_id === activeCategory.value;
    const matchSearch = !search.value || item.name.toLowerCase().includes(search.value.toLowerCase());
    return matchCat && matchSearch;
  })
);
</script>
