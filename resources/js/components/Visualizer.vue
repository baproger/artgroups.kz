<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Визуализация</h2>
        <p class="text-gray-500 mt-0.5 text-sm">Перемещайте, масштабируйте и вращайте объекты</p>
      </div>
      <button @click="emit('back')" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
        ← Назад
      </button>
    </div>

    <div class="flex flex-col xl:flex-row gap-6">
      <!-- Canvas -->
      <div class="flex-1 min-w-0">
        <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
          <div ref="canvasWrapper" class="relative flex items-center justify-center" style="min-height: 400px;">
            <canvas ref="canvasEl" />
          </div>
        </div>

        <!-- Active object indicator -->
        <div v-if="activeObject" class="mt-3 flex items-center gap-2 text-sm text-gray-500">
          <div class="w-2 h-2 rounded-full bg-[#eef2f5]0 animate-pulse"/>
          Выбран: <strong class="text-gray-700">{{ activeObject === 'gate' ? gate.name : wicket?.name }}</strong>
          — кликните на другой объект для выбора
        </div>
      </div>

      <!-- Controls panel -->
      <div class="xl:w-72 space-y-3">

        <!-- GATE CONTROLS -->
        <ObjectControls
          label="Ворота"
          :item="gate"
          :opacity="gateOpacity"
          :active-color="gateColor"
          :is-active="activeObject === 'gate'"
          @activate="activateObject('gate')"
          @opacity="v => { gateOpacity = v; applyOpacity('gate', v); }"
          @color="v => applyColor('gate', v)"
          @flip="flipObject('gate')"
          @reset="resetObject('gate')"
        />

        <!-- WICKET CONTROLS -->
        <ObjectControls
          v-if="wicket"
          label="Калитка"
          :item="wicket"
          :opacity="wicketOpacity"
          :active-color="wicketColor"
          :is-active="activeObject === 'wicket'"
          @activate="activateObject('wicket')"
          @opacity="v => { wicketOpacity = v; applyOpacity('wicket', v); }"
          @color="v => applyColor('wicket', v)"
          @flip="flipObject('wicket')"
          @reset="resetObject('wicket')"
        />

        <!-- Actions -->
        <button
          @click="saveResult"
          :disabled="saving"
          class="w-full py-4 bg-[#2e5f99] hover:bg-[#265285] disabled:bg-[#8aafd1] text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-2"
        >
          <svg v-if="saving" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          {{ saving ? 'Сохраняем...' : 'Сохранить и продолжить →' }}
        </button>

        <button
          @click="downloadImage"
          class="w-full py-3 border-2 border-gray-200 text-gray-600 hover:border-[#2e5f99] hover:text-[#2e5f99] font-medium rounded-xl transition-all text-sm"
        >
          Скачать изображение
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Canvas, FabricImage, filters as fabricFilters } from 'fabric';
import axios from 'axios';
import ObjectControls from './ObjectControls.vue';

const props = defineProps({ project: Object, gate: Object, wicket: Object });
const emit = defineEmits(['saved', 'back']);

const canvasEl = ref(null);
const canvasWrapper = ref(null);
let fabricCanvas = null;
let gateObj = null;
let wicketObj = null;

const activeObject = ref('gate');
const gateOpacity = ref(100);
const wicketOpacity = ref(100);
const gateColor = ref(null);
const wicketColor = ref(null);
const saving = ref(false);

onMounted(async () => {
  await initCanvas();
});

onUnmounted(() => {
  fabricCanvas?.dispose();
});

async function initCanvas() {
  const wrapper = canvasWrapper.value;
  const maxWidth = Math.min(wrapper.clientWidth || 800, 900);

  const img = await FabricImage.fromURL(props.project.source_image_url, { crossOrigin: 'anonymous' });

  const aspectRatio = img.height / img.width;
  const canvasWidth = maxWidth;
  const canvasHeight = Math.min(Math.round(maxWidth * aspectRatio), 620);

  fabricCanvas = new Canvas(canvasEl.value, {
    width: canvasWidth,
    height: canvasHeight,
    selection: false,
  });

  img.set({
    scaleX: canvasWidth / img.width,
    scaleY: canvasHeight / img.height,
    left: 0,
    top: 0,
    selectable: false,
    evented: false,
    originX: 'left',
    originY: 'top',
  });
  fabricCanvas.add(img);

  await loadGateObject();
  if (props.wicket?.image_url) await loadWicketObject();

  fabricCanvas.on('selection:created', onSelectionChange);
  fabricCanvas.on('selection:updated', onSelectionChange);

  fabricCanvas.renderAll();
}

function onSelectionChange(e) {
  if (!e.selected?.[0]) return;
  if (e.selected[0] === gateObj) activeObject.value = 'gate';
  else if (e.selected[0] === wicketObj) activeObject.value = 'wicket';
}

async function loadGateObject() {
  if (!props.gate?.image_url) return;
  gateObj = await FabricImage.fromURL(props.gate.image_url, { crossOrigin: 'anonymous' });

  const maxW = fabricCanvas.width * 0.55;
  const maxH = fabricCanvas.height * 0.7;
  const scale = Math.min(maxW / gateObj.width, maxH / gateObj.height);

  gateObj.set({
    scaleX: scale, scaleY: scale,
    left: fabricCanvas.width * 0.1,
    top: fabricCanvas.height * 0.2,
    cornerColor: '#f59e0b', cornerStyle: 'circle',
    borderColor: '#f59e0b', transparentCorners: false,
  });

  fabricCanvas.add(gateObj);
  fabricCanvas.setActiveObject(gateObj);
}

async function loadWicketObject() {
  wicketObj = await FabricImage.fromURL(props.wicket.image_url, { crossOrigin: 'anonymous' });

  const maxW = fabricCanvas.width * 0.20;
  const maxH = fabricCanvas.height * 0.55;
  const scale = Math.min(maxW / wicketObj.width, maxH / wicketObj.height);

  wicketObj.set({
    scaleX: scale, scaleY: scale,
    left: fabricCanvas.width * 0.68,
    top: fabricCanvas.height * 0.3,
    cornerColor: '#6366f1', cornerStyle: 'circle',
    borderColor: '#6366f1', transparentCorners: false,
  });

  fabricCanvas.add(wicketObj);
}

function activateObject(type) {
  activeObject.value = type;
  const obj = type === 'gate' ? gateObj : wicketObj;
  if (obj) fabricCanvas.setActiveObject(obj);
  fabricCanvas.renderAll();
}

function applyOpacity(type, value) {
  const obj = type === 'gate' ? gateObj : wicketObj;
  if (!obj) return;
  obj.set('opacity', value / 100);
  fabricCanvas.renderAll();
}

function applyColor(type, hex) {
  const obj = type === 'gate' ? gateObj : wicketObj;
  if (!obj) return;
  if (type === 'gate') gateColor.value = hex;
  else wicketColor.value = hex;

  if (hex === null) {
    obj.set('filters', []);
  } else {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    obj.set('filters', [new fabricFilters.ColorMatrix({
      matrix: [
        0, 0, 0, 0, r / 255,
        0, 0, 0, 0, g / 255,
        0, 0, 0, 0, b / 255,
        0, 0, 0, 1, 0,
      ],
    })]);
  }
  obj.applyFilters();
  fabricCanvas.renderAll();
}

function flipObject(type) {
  const obj = type === 'gate' ? gateObj : wicketObj;
  if (!obj) return;
  obj.set('flipX', !obj.flipX);
  fabricCanvas.renderAll();
}

function resetObject(type) {
  const obj = type === 'gate' ? gateObj : wicketObj;
  if (!obj) return;

  const isGate = type === 'gate';
  const maxW = fabricCanvas.width * (isGate ? 0.55 : 0.20);
  const maxH = fabricCanvas.height * (isGate ? 0.7 : 0.55);
  const scale = Math.min(maxW / obj.width, maxH / obj.height);

  obj.set({
    scaleX: scale, scaleY: scale,
    left: fabricCanvas.width * (isGate ? 0.1 : 0.68),
    top: fabricCanvas.height * (isGate ? 0.2 : 0.3),
    angle: 0, flipX: false, opacity: 1, filters: [],
  });
  obj.applyFilters();

  if (isGate) { gateOpacity.value = 100; gateColor.value = null; }
  else { wicketOpacity.value = 100; wicketColor.value = null; }

  fabricCanvas.renderAll();
}

async function saveResult() {
  saving.value = true;
  try {
    fabricCanvas.discardActiveObject();
    fabricCanvas.renderAll();
    const dataUrl = fabricCanvas.toDataURL({ format: 'png', quality: 0.95 });
    const { data } = await axios.post(`/api/projects/${props.project.id}/result`, { image: dataUrl });
    emit('saved', data);
  } catch {
    alert('Ошибка сохранения. Попробуйте снова.');
  } finally {
    saving.value = false;
  }
}

function downloadImage() {
  fabricCanvas.discardActiveObject();
  fabricCanvas.renderAll();
  const dataUrl = fabricCanvas.toDataURL({ format: 'png', quality: 0.95 });
  const link = document.createElement('a');
  link.download = `artgroup-gates-${Date.now()}.png`;
  link.href = dataUrl;
  link.click();
}
</script>
