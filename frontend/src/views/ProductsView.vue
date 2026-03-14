<template>
  <div class="min-h-screen bg-alien-bg">

    <!-- Header -->
    <header class="bg-alien-card border-b border-alien-border sticky top-0 z-30 backdrop-blur-sm">
      <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <span class="text-2xl">👽</span>
          <span class="font-orbitron text-alien-green text-sm tracking-widest neon-text">ALIENS STORE</span>
        </div>
        <div class="flex items-center gap-3">
          <span class="text-xs text-alien-muted font-rajdhani hidden sm:block tracking-widest uppercase">
            {{ auth.user?.name }}
          </span>
          <button class="btn-ghost text-xs" @click="$router.push('/users')">
            👥 Usuários
          </button>
          <button class="btn-ghost text-xs" @click="handleLogout">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Sair
          </button>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8">

      <!-- Toolbar -->
      <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <div class="relative flex-1">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-alien-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input v-model="search" class="input-alien pl-9" placeholder="Rastrear espécie..." @input="debouncedFetch" />
        </div>

        <select v-model="filterCategory" class="input-alien w-auto" @change="fetchProducts()">
          <option value="">Todas as Espécies</option>
          <option v-for="cat in store.categories" :key="cat" :value="cat">{{ cat }}</option>
        </select>

        <select v-model="filterActive" class="input-alien w-auto" @change="fetchProducts()">
          <option value="">Todos</option>
          <option value="true">Ativos</option>
          <option value="false">Inativos</option>
        </select>

        <button class="btn-alien" @click="openForm()">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Cadastrar
        </button>
      </div>

      <!-- Loading -->
      <div v-if="store.loading" class="flex items-center justify-center py-20">
        <div class="text-center">
          <div class="text-5xl mb-4 animate-pulse">👽</div>
          <p class="font-orbitron text-alien-green text-sm tracking-widest">Carregando transmissão...</p>
        </div>
      </div>

      <!-- Empty -->
      <div v-else-if="store.products.length === 0" class="text-center py-20">
        <div class="text-6xl mb-4">🛸</div>
        <p class="font-orbitron text-alien-muted text-sm tracking-widest">Nenhum ser encontrado</p>
        <p class="text-alien-muted text-sm mt-2 font-rajdhani">Cadastre o primeiro produto intergaláctico.</p>
      </div>

      <!-- Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <ProductCard
          v-for="(product, i) in store.products"
          :key="product.id"
          :product="product"
          :style="{ animationDelay: `${i * 60}ms` }"
          class="alien-card-enter"
          @view="openDetail(product)"
          @edit="openForm(product)"
          @toggle="handleToggle(product)"
        />
      </div>

      <!-- Pagination -->
      <div v-if="store.pagination.last_page > 1" class="flex justify-center gap-2 mt-8">
        <button
          v-for="page in store.pagination.last_page"
          :key="page"
          class="w-9 h-9 rounded font-orbitron text-xs transition-all border"
          :class="page === store.pagination.current_page
            ? 'bg-alien-green text-alien-bg border-alien-green'
            : 'bg-transparent text-alien-muted border-alien-border hover:border-alien-green hover:text-alien-green'"
          @click="goToPage(page)"
        >{{ page }}</button>
      </div>
    </main>

    <!-- Detail Modal -->
    <ProductDetailModal
      v-if="showDetail"
      :product="detailProduct"
      @close="showDetail = false"
      @edit="p => { showDetail = false; openForm(p) }"
    />

    <!-- Form Modal -->
    <ProductFormModal
      v-if="showModal"
      :product="editingProduct"
      @close="closeForm"
      @saved="onSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '@/stores/auth'
import { useProductStore } from '@/stores/products'
import { useRouter } from 'vue-router'
import ProductCard from '@/components/product/ProductCard.vue'
import ProductDetailModal from '@/components/product/ProductDetailModal.vue'
import ProductFormModal from '@/components/product/ProductFormModal.vue'

const toast          = useToast()
const auth           = useAuthStore()
const store          = useProductStore()
const router         = useRouter()
const showModal      = ref(false)
const editingProduct = ref(null)
const showDetail     = ref(false)
const detailProduct  = ref(null)
const search         = ref('')
const filterCategory = ref('')
const filterActive   = ref('')
let   debounceTimer  = null

onMounted(() => {
  fetchProducts()
  store.fetchCategories()
})

function fetchProducts(page = 1) {
  const params = { page }
  if (search.value)        params.search   = search.value
  if (filterCategory.value) params.category = filterCategory.value
  if (filterActive.value !== '') params.active = filterActive.value
  store.fetchProducts(params)
}

function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => fetchProducts(), 400)
}

function goToPage(page) {
  fetchProducts(page)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function openDetail(product) {
  detailProduct.value = product
  showDetail.value    = true
}

function openForm(product = null) {
  editingProduct.value = product
  showModal.value      = true
}

function closeForm() {
  showModal.value      = false
  editingProduct.value = null
}

async function onSaved() {
  closeForm()
  fetchProducts()
  store.fetchCategories()
  toast.success('Produto salvo com sucesso!')
}

async function handleToggle(product) {
  try {
    await store.toggleActive(product.id)
    toast.success(product.is_active ? 'Produto inativado.' : 'Produto ativado.')
  } catch {
    toast.error('Erro ao atualizar produto.')
  }
}

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>
