<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click.self="$emit('close')">
    <div class="w-full max-w-2xl max-h-[90vh] flex flex-col card-alien neon-border modal-enter shadow-2xl">

      <!-- Header -->
      <div class="flex items-center justify-between p-5 border-b border-alien-border">
        <div class="flex items-center gap-3">
          <span class="text-xl">👽</span>
          <h2 class="font-orbitron text-sm text-alien-green tracking-widest">
            {{ isEditing ? 'EDITAR ENTIDADE' : 'CADASTRAR ENTIDADE' }}
          </h2>
        </div>
        <button class="text-alien-muted hover:text-alien-green transition-colors" @click="$emit('close')">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Body -->
      <div class="overflow-y-auto flex-1 p-5 space-y-4">

        <!-- Title -->
        <div>
          <label class="label-alien">Título *</label>
          <input v-model="form.title" class="input-alien" placeholder="Nome da entidade alienígena" />
          <p v-if="errors.title" class="error-text">{{ errors.title[0] }}</p>
        </div>

        <!-- Category -->
        <div>
          <label class="label-alien">Espécie / Categoria</label>
          <input v-model="form.category" class="input-alien" placeholder="Ex: Alien Aquático, Robô, Parasita..." list="categories-list" />
          <datalist id="categories-list">
            <option v-for="cat in store.categories" :key="cat" :value="cat" />
          </datalist>
        </div>

        <!-- Prices -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label-alien">Custo (R$) *</label>
            <input v-model="form.cost_price" type="number" step="0.01" min="0" class="input-alien" placeholder="0,00" />
            <p v-if="errors.cost_price" class="error-text">{{ errors.cost_price[0] }}</p>
          </div>
          <div>
            <label class="label-alien">Preço de Venda (R$) *</label>
            <input v-model="form.sale_price" type="number" step="0.01" min="0" class="input-alien" placeholder="0,00" />
            <p v-if="errors.sale_price" class="error-text">{{ errors.sale_price[0] }}</p>
          </div>
        </div>

        <!-- Margin indicator -->
        <div v-if="form.cost_price && form.sale_price" class="flex items-center gap-2 text-xs font-rajdhani p-2 rounded border"
          :class="marginOk ? 'border-alien-green/30 bg-alien-green/5' : 'border-red-500/30 bg-red-500/5'">
          <span class="text-alien-muted">Margem:</span>
          <span :class="marginOk ? 'text-alien-green' : 'text-red-400'" class="font-orbitron">{{ marginPercent }}%</span>
          <span class="text-alien-muted">(mínimo 10%)</span>
          <span v-if="!marginOk" class="text-red-400 ml-1">— mínimo: {{ formatCurrency(minPrice) }}</span>
        </div>

        <!-- Description -->
        <div>
          <label class="label-alien">Descrição *</label>
          <p class="text-xs text-alien-muted mb-1.5 font-rajdhani">
            Tags permitidas:
            <code class="bg-alien-border px-1 rounded text-alien-blue">&lt;p&gt;</code>
            <code class="bg-alien-border px-1 rounded text-alien-blue">&lt;br&gt;</code>
            <code class="bg-alien-border px-1 rounded text-alien-blue">&lt;b&gt;</code>
            <code class="bg-alien-border px-1 rounded text-alien-blue">&lt;strong&gt;</code>
          </p>
          <textarea v-model="form.description" class="input-alien resize-none" rows="4" placeholder="<p>Descrição da entidade...</p>"></textarea>
          <p v-if="errors.description" class="error-text">{{ errors.description[0] }}</p>
        </div>

        <!-- Images -->
        <div>
          <label class="label-alien">Imagens (JPG / PNG)</label>

          <!-- Existing images -->
          <div v-if="existingImages.length" class="flex flex-wrap gap-2 mb-3">
            <div v-for="img in existingImages" :key="img.id"
              class="relative group w-20 h-20 rounded border border-alien-border overflow-hidden">
              <img :src="img.url" class="w-full h-full object-cover" />
              <button type="button"
                class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-red-400"
                @click="removeExistingImage(img)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Dropzone -->
          <div
            class="border-2 border-dashed rounded p-6 text-center cursor-pointer transition-all duration-200"
            :class="isDragging ? 'border-alien-green bg-alien-green/10' : 'border-alien-border hover:border-alien-green/50 hover:bg-alien-green/5'"
            @dragover.prevent="isDragging = true"
            @dragleave="isDragging = false"
            @drop.prevent="onDrop"
            @click="$refs.fileInput.click()"
          >
            <input ref="fileInput" type="file" accept=".jpg,.jpeg,.png" multiple class="hidden" @change="onFilesSelected" />
            <div class="text-3xl mb-2">🛸</div>
            <p class="text-sm text-alien-muted font-rajdhani">Arraste imagens ou <span class="text-alien-green">clique para selecionar</span></p>
            <p class="text-xs text-alien-muted mt-1">JPG, PNG — máx. 5MB</p>
          </div>

          <!-- New previews -->
          <div v-if="newImagePreviews.length" class="flex flex-wrap gap-2 mt-3">
            <div v-for="(preview, i) in newImagePreviews" :key="i"
              class="relative group w-20 h-20 rounded border border-alien-border overflow-hidden">
              <img :src="preview" class="w-full h-full object-cover" />
              <button type="button"
                class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-red-400"
                @click="removeNewImage(i)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>

          <p v-if="imageError" class="error-text">{{ imageError }}</p>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex justify-end gap-3 p-5 border-t border-alien-border">
        <button class="btn-ghost" @click="$emit('close')">Cancelar</button>
        <button class="btn-alien" :disabled="saving" @click="handleSubmit">
          <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          {{ saving ? 'Transmitindo...' : isEditing ? 'Salvar Alterações' : 'Cadastrar Entidade' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { useProductStore } from '@/stores/products'

const props = defineProps({ product: { type: Object, default: null } })
const emit  = defineEmits(['close', 'saved'])

const toast          = useToast()
const store          = useProductStore()
const isEditing      = computed(() => !!props.product)
const saving         = ref(false)
const isDragging     = ref(false)
const imageError     = ref('')
const errors         = ref({})
const existingImages = ref([])
const newFiles       = ref([])
const newImagePreviews = ref([])

const form = reactive({ title: '', description: '', sale_price: '', cost_price: '', category: '' })

const marginPercent = computed(() => {
  const cost = parseFloat(form.cost_price)
  const sale = parseFloat(form.sale_price)
  if (!cost || !sale) return 0
  return (((sale - cost) / cost) * 100).toFixed(1)
})
const marginOk = computed(() => parseFloat(marginPercent.value) >= 10)
const minPrice = computed(() => parseFloat(form.cost_price) * 1.1)

function formatCurrency(val) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val)
}

onMounted(async () => {
  if (props.product) {
    const detail = await store.getProduct(props.product.id)
    form.title       = detail.title
    form.description = detail.description
    form.sale_price  = detail.sale_price
    form.cost_price  = detail.cost_price
    form.category    = detail.category || ''
    existingImages.value = detail.images || []
  }
})

function onFilesSelected(e) {
  addFiles(Array.from(e.target.files))
  e.target.value = ''
}

function onDrop(e) {
  isDragging.value = false
  addFiles(Array.from(e.dataTransfer.files))
}

function addFiles(files) {
  imageError.value = ''
  for (const file of files) {
    if (!['image/jpeg', 'image/png'].includes(file.type)) {
      imageError.value = 'Apenas JPG e PNG são permitidos.'
      continue
    }
    if (file.size > 5 * 1024 * 1024) {
      imageError.value = 'Máximo 5MB por imagem.'
      continue
    }
    newFiles.value.push(file)
    const reader = new FileReader()
    reader.onload = (e) => newImagePreviews.value.push(e.target.result)
    reader.readAsDataURL(file)
  }
}

function removeNewImage(i) {
  newFiles.value.splice(i, 1)
  newImagePreviews.value.splice(i, 1)
}

async function removeExistingImage(img) {
  try {
    await store.removeImage(props.product.id, img.id)
    existingImages.value = existingImages.value.filter(i => i.id !== img.id)
    toast.success('Imagem removida.')
  } catch {
    toast.error('Erro ao remover imagem.')
  }
}

async function handleSubmit() {
  errors.value = {}
  saving.value = true
  const formData = new FormData()
  formData.append('title',       form.title)
  formData.append('description', form.description)
  formData.append('sale_price',  form.sale_price)
  formData.append('cost_price',  form.cost_price)
  formData.append('category',    form.category)
  newFiles.value.forEach(f => formData.append('images[]', f))

  try {
    if (isEditing.value) {
      await store.updateProduct(props.product.id, formData)
    } else {
      await store.createProduct(formData)
    }
    emit('saved')
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
    } else {
      toast.error(err.response?.data?.message || 'Erro ao salvar.')
    }
  } finally {
    saving.value = false
  }
}
</script>
