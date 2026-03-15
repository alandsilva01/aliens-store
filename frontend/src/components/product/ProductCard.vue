<template>
  <div
    class="card-alien flex flex-col group cursor-pointer transition-all duration-300 hover:border-alien-green"
    style="transition: box-shadow 0.3s, border-color 0.3s;"
    :style="{ boxShadow: hovered ? '0 0 20px rgba(0,255,136,0.15)' : 'none' }"
    @click="$emit('view', product)"
    @mouseenter="hovered = true"
    @mouseleave="hovered = false"
  >
    <!-- Scan line on hover -->
    <div v-if="hovered" class="scan-line"></div>

    <!-- Image -->
    <div class="relative h-44 bg-alien-bg overflow-hidden">
     <img
  v-if="product.images && product.images.length"
  :src="product.images[0].url"
  :alt="product.title"
  class="w-full h-full object-cover object-position-top transition-transform duration-700 group-hover:scale-110"
  style="object-position: top !important"
/>
      <div v-else class="w-full h-full flex items-center justify-center">
        <span class="text-6xl opacity-30">👽</span>
      </div>

      <!-- Image count -->
      <div v-if="product.images && product.images.length > 1"
        class="absolute bottom-2 right-2 bg-black/70 text-alien-green text-xs px-2 py-0.5 rounded font-orbitron border border-alien-green/30">
        +{{ product.images.length - 1 }}
      </div>

      <!-- Status -->
      <div class="absolute top-2 left-2">
        <span class="text-xs font-orbitron px-2 py-0.5 rounded border tracking-widest"
          :class="product.is_active
            ? 'bg-alien-green/10 text-alien-green border-alien-green/40'
            : 'bg-gray-800/80 text-gray-500 border-gray-600/40'">
          {{ product.is_active ? 'ATIVO' : 'INATIVO' }}
        </span>
      </div>

      <!-- Category badge -->
      <div v-if="product.category" class="absolute top-2 right-2">
        <span class="text-xs font-rajdhani px-2 py-0.5 rounded bg-black/60 text-white border border-white/30">
          {{ product.category }}
        </span>
      </div>
    </div>

    <!-- Content -->
    <div class="p-4 flex flex-col flex-1">
      <h3 class="font-orbitron text-sm text-alien-text truncate mb-2 group-hover:text-alien-green transition-colors">
        {{ product.title }}
      </h3>

      <div class="flex items-baseline gap-2 mb-1">
        <span class="font-orbitron text-lg font-bold text-alien-green price-pulse">
          {{ formatCurrency(product.sale_price) }}
        </span>
      </div>
      <div class="text-xs text-alien-muted font-rajdhani mb-3">
        Custo: {{ formatCurrency(product.cost_price) }}
        <span class="ml-2 text-alien-blue">+{{ margin }}%</span>
      </div>

      <div class="text-xs text-alien-muted font-rajdhani mb-4">
        Por: <span class="text-alien-text">{{ product.creator?.name || '—' }}</span>
      </div>

      <!-- Actions -->
      <div class="flex gap-2 mt-auto">
        <button class="btn-ghost flex-1 justify-center text-xs py-1.5" @click.stop="$emit('edit', product)">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
          Editar
        </button>

        <button
          class="flex-1 justify-center text-xs py-1.5 inline-flex items-center gap-1 rounded border font-rajdhani font-semibold uppercase tracking-widest transition-all duration-200"
          :class="product.is_active
            ? 'border-red-500/40 text-red-400 hover:bg-red-500/10'
            : 'border-alien-green/40 text-alien-green hover:bg-alien-green/10'"
          @click.stop="$emit('toggle', product)"
        >
          {{ product.is_active ? 'Inativar' : 'Ativar' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({ product: { type: Object, required: true } })
defineEmits(['edit', 'toggle', 'view'])

const hovered = ref(false)

const margin = computed(() => {
  const cost = parseFloat(props.product.cost_price)
  const sale = parseFloat(props.product.sale_price)
  if (!cost) return 0
  return (((sale - cost) / cost) * 100).toFixed(0)
})

function formatCurrency(val) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val)
}
</script>
