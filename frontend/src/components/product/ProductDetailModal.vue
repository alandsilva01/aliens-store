<template>
  <!-- Backdrop -->
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-sm" @click.self="$emit('close')">
    <div class="w-full max-w-3xl max-h-[90vh] flex flex-col card-alien neon-border modal-enter shadow-2xl">

      <!-- Header -->
      <div class="flex items-center justify-between p-5 border-b border-alien-border">
        <div class="flex items-center gap-3">
          <span class="text-xl">👽</span>
          <div>
            <h2 class="font-orbitron text-sm text-alien-green tracking-widest">{{ product.title }}</h2>
            <span v-if="product.category" class="text-xs text-alien-blue font-rajdhani">{{ product.category }}</span>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-xs font-orbitron px-2 py-0.5 rounded border tracking-widest"
            :class="product.is_active
              ? 'bg-alien-green/10 text-alien-green border-alien-green/40'
              : 'bg-gray-800 text-gray-500 border-gray-600/40'">
            {{ product.is_active ? 'ATIVO' : 'INATIVO' }}
          </span>
          <button class="text-alien-muted hover:text-alien-green transition-colors ml-2" @click="$emit('close')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Body -->
      <div class="overflow-y-auto flex-1 p-5 space-y-5">

        <!-- Image gallery -->
        <div v-if="detail && detail.images && detail.images.length">
          <!-- Main image -->
          <div class="relative h-64 bg-alien-bg rounded-lg overflow-hidden border border-alien-border cursor-pointer mb-2"
            @click="lightboxIndex = activeImage">
            <img :src="detail.images[activeImage].url" :alt="detail.title"
              class="w-full h-full object-contain transition-all duration-300" />
            <div class="absolute inset-0 flex items-center justify-center opacity-0 hover:opacity-100 bg-black/40 transition-opacity">
              <svg class="w-10 h-10 text-alien-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
              </svg>
            </div>
          </div>
          <!-- Thumbnails -->
          <div v-if="detail.images.length > 1" class="flex gap-2 overflow-x-auto pb-1">
            <div v-for="(img, i) in detail.images" :key="img.id"
              class="flex-shrink-0 w-16 h-16 rounded border cursor-pointer overflow-hidden transition-all duration-200"
              :class="i === activeImage ? 'border-alien-green' : 'border-alien-border hover:border-alien-muted'"
              @click="activeImage = i">
              <img :src="img.url" class="w-full h-full object-cover" />
            </div>
          </div>
        </div>
        <div v-else class="h-32 bg-alien-bg rounded-lg border border-alien-border flex items-center justify-center">
          <span class="text-5xl opacity-20">👽</span>
        </div>

        <!-- Prices -->
        <div class="grid grid-cols-3 gap-3">
          <div class="bg-alien-bg rounded-lg p-3 border border-alien-border text-center">
            <p class="label-alien text-xs mb-1">Preço de Venda</p>
            <p class="font-orbitron text-lg text-alien-green price-pulse">{{ formatCurrency(product.sale_price) }}</p>
          </div>
          <div class="bg-alien-bg rounded-lg p-3 border border-alien-border text-center">
            <p class="label-alien text-xs mb-1">Custo</p>
            <p class="font-orbitron text-base text-alien-muted">{{ formatCurrency(product.cost_price) }}</p>
          </div>
          <div class="bg-alien-bg rounded-lg p-3 border border-alien-border text-center">
            <p class="label-alien text-xs mb-1">Margem</p>
            <p class="font-orbitron text-base text-alien-blue">{{ margin }}%</p>
          </div>
        </div>

        <!-- Description -->
        <div v-if="detail">
          <p class="label-alien mb-2">Descrição</p>
          <div class="bg-alien-bg rounded-lg p-4 border border-alien-border text-alien-text font-rajdhani text-sm leading-relaxed prose-alien"
            v-html="detail.description">
          </div>
        </div>

        <!-- Audit logs -->
        <div v-if="detail && detail.logs && detail.logs.length">
          <p class="label-alien mb-2">📋 Histórico de Alterações</p>
          <div class="space-y-2 max-h-40 overflow-y-auto">
            <div v-for="log in detail.logs" :key="log.id"
              class="flex items-start gap-3 bg-alien-bg rounded-lg p-3 border border-alien-border/50 text-xs font-rajdhani">
              <div class="mt-0.5">
                <span class="inline-block w-2 h-2 rounded-full"
                  :class="{
                    'bg-alien-green': log.action === 'created',
                    'bg-alien-blue':  log.action === 'updated',
                    'bg-yellow-400':  log.action === 'activated' || log.action === 'inactivated',
                    'bg-red-400':     log.action === 'deleted',
                  }">
                </span>
              </div>
              <div class="flex-1">
                <span class="text-alien-text capitalize font-semibold">{{ translateAction(log.action) }}</span>
                <span class="text-alien-muted ml-1">por {{ log.user?.name || 'Sistema' }}</span>
                <span class="text-alien-muted ml-1">— {{ formatDate(log.logged_at) }}</span>
                <div v-if="log.changes && Object.keys(log.changes).length" class="mt-1 text-alien-muted">
                  Campos: {{ Object.keys(log.changes).join(', ') }}
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Footer -->
      <div class="flex justify-between gap-3 p-5 border-t border-alien-border">
        <div class="text-xs text-alien-muted font-rajdhani">
          Criado por <span class="text-alien-text">{{ product.creator?.name || '—' }}</span>
        </div>
        <div class="flex gap-2">
          <button class="btn-ghost text-xs" @click="$emit('edit', product)">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Editar
          </button>
          <button class="btn-alien text-xs" @click="$emit('close')">Fechar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Lightbox -->
  <div v-if="lightboxIndex !== null"
    class="fixed inset-0 z-[60] bg-black/95 flex items-center justify-center p-4"
    @click="lightboxIndex = null">
    <img v-if="detail" :src="detail.images[lightboxIndex].url"
      class="max-w-full max-h-full object-contain rounded-lg"
      style="max-height: 90vh;" />
    <button class="absolute top-4 right-4 text-white/70 hover:text-white" @click="lightboxIndex = null">
      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useProductStore } from '@/stores/products'

const props = defineProps({ product: { type: Object, required: true } })
const emit  = defineEmits(['close', 'edit'])

const store        = useProductStore()
const detail       = ref(null)
const activeImage  = ref(0)
const lightboxIndex = ref(null)

onMounted(async () => {
  detail.value = await store.getProduct(props.product.id)
})

const margin = computed(() => {
  const cost = parseFloat(props.product.cost_price)
  const sale = parseFloat(props.product.sale_price)
  if (!cost) return 0
  return (((sale - cost) / cost) * 100).toFixed(1)
})

function formatCurrency(val) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val)
}

function formatDate(date) {
  return new Date(date).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' })
}

function translateAction(action) {
  const map = { created: 'Criado', updated: 'Editado', activated: 'Ativado', inactivated: 'Inativado', deleted: 'Removido' }
  return map[action] || action
}
</script>
