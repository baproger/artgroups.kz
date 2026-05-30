<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Выберите ворота и калитку</h2>
        <p class="text-gray-500 mt-1">Выберите модель ворот (обязательно) и калитку (по желанию)</p>
      </div>
      <button @click="emit('back')" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
        ← Назад
      </button>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <svg class="w-10 h-10 animate-spin text-[#2e5f99]" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
      </svg>
    </div>

    <div v-else class="space-y-10">

      <!-- BLOCK 1: ВОРОТА -->
      <div>
        <div class="flex items-center gap-3 mb-4">
          <div class="w-8 h-8 rounded-full bg-[#eef2f5] flex items-center justify-center text-[#2e5f99] font-bold text-sm">1</div>
          <h3 class="text-lg font-semibold text-gray-900">Ворота <span class="text-red-500">*</span></h3>
          <span v-if="selectedGate" class="text-sm text-green-600 font-medium flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            {{ selectedGate.name }}
          </span>
        </div>

        <CatalogGrid
          :items="gates"
          :categories="categoriesWithGates"
          :selected="selectedGate"
          placeholder="Поиск ворот..."
          @select="selectedGate = selectedGate?.id === $event.id ? null : $event"
        />
      </div>

      <!-- DIVIDER -->
      <div class="border-t border-dashed border-gray-200"/>

      <!-- BLOCK 2: КАЛИТКА -->
      <div>
        <div class="flex items-center gap-3 mb-4">
          <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold text-sm">2</div>
          <h3 class="text-lg font-semibold text-gray-900">Калитка <span class="text-gray-400 font-normal text-sm">(необязательно)</span></h3>
          <span v-if="selectedWicket" class="text-sm text-green-600 font-medium flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            {{ selectedWicket.name }}
          </span>
          <button v-if="selectedWicket" @click="selectedWicket = null" class="text-xs text-gray-400 hover:text-red-500 ml-auto">
            Убрать
          </button>
        </div>

        <div v-if="wickets.length === 0" class="py-8 text-center text-gray-400 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
          <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <p class="text-sm">Калитки ещё не добавлены в каталог</p>
          <p class="text-xs mt-1 text-gray-300">Добавьте через админ-панель с типом «Калитка»</p>
        </div>

        <CatalogGrid
          v-else
          :items="wickets"
          :categories="categoriesWithWickets"
          :selected="selectedWicket"
          placeholder="Поиск калиток..."
          @select="selectedWicket = selectedWicket?.id === $event.id ? null : $event"
        />
      </div>

      <!-- CTA -->
      <button
        :disabled="!selectedGate"
        @click="proceed"
        class="w-full py-4 bg-[#2e5f99] hover:bg-[#265285] disabled:bg-gray-200 disabled:text-gray-400 text-white font-semibold rounded-xl transition-all"
      >
        {{ selectedGate ? 'Наложить на фото →' : 'Выберите ворота, чтобы продолжить' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import CatalogGrid from './CatalogGrid.vue';

const emit = defineEmits(['selected', 'back']);

const loading = ref(true);
const categories = ref([]);
const gates = ref([]);
const wickets = ref([]);
const selectedGate = ref(null);
const selectedWicket = ref(null);

onMounted(async () => {
  try {
    const [catRes, gateRes, wicketRes] = await Promise.all([
      axios.get('/api/categories'),
      axios.get('/api/gates', { params: { type: 'gate' } }),
      axios.get('/api/gates', { params: { type: 'wicket' } }),
    ]);
    categories.value = catRes.data;
    gates.value = gateRes.data;
    wickets.value = wicketRes.data;
  } finally {
    loading.value = false;
  }
});

const categoriesWithGates = computed(() =>
  categories.value.filter(c => gates.value.some(g => g.category_id === c.id))
);

const categoriesWithWickets = computed(() =>
  categories.value.filter(c => wickets.value.some(g => g.category_id === c.id))
);

function proceed() {
  if (!selectedGate.value) return;
  emit('selected', { gate: selectedGate.value, wicket: selectedWicket.value });
}
</script>
