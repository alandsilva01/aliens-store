<template>
  <div class="min-h-screen bg-alien-bg">

    <!-- Header -->
    <header class="bg-alien-card border-b border-alien-border sticky top-0 z-30 backdrop-blur-sm">
      <div class="max-w-4xl mx-auto px-4 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <button class="text-alien-muted hover:text-alien-green transition-colors" @click="$router.push('/')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
          <span class="text-2xl">👽</span>
          <span class="font-orbitron text-alien-green text-sm tracking-widest neon-text">GESTÃO DE TRIPULAÇÃO</span>
        </div>
        <button class="btn-alien text-xs" @click="openForm()">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Novo Usuário
        </button>
      </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8">

      <!-- Loading -->
      <div v-if="loading" class="flex items-center justify-center py-20">
        <div class="text-center">
          <div class="text-5xl mb-4 animate-pulse">👽</div>
          <p class="font-orbitron text-alien-green text-sm tracking-widest">Carregando tripulação...</p>
        </div>
      </div>

      <!-- Table -->
      <div v-else class="card-alien overflow-hidden">
        <div class="p-4 border-b border-alien-border">
          <h2 class="font-orbitron text-xs text-alien-muted tracking-widest uppercase">
            {{ users.length }} membro(s) registrado(s)
          </h2>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-alien-border">
                <th class="text-left px-4 py-3 label-alien text-xs">#</th>
                <th class="text-left px-4 py-3 label-alien text-xs">Nome</th>
                <th class="text-left px-4 py-3 label-alien text-xs">E-mail</th>
                <th class="text-left px-4 py-3 label-alien text-xs">Criado em</th>
                <th class="text-right px-4 py-3 label-alien text-xs">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id"
                class="border-b border-alien-border/30 hover:bg-alien-green/5 transition-colors">
                <td class="px-4 py-3 font-mono text-xs text-alien-muted">{{ user.id }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-alien-green/10 border border-alien-green/30 flex items-center justify-center font-orbitron text-xs text-alien-green">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <span class="font-rajdhani text-sm text-alien-text">{{ user.name }}</span>
                    <span v-if="user.id === auth.user?.id"
                      class="text-xs font-orbitron px-1.5 py-0.5 rounded bg-alien-green/10 text-alien-green border border-alien-green/30">
                      VOCÊ
                    </span>
                  </div>
                </td>
                <td class="px-4 py-3 font-rajdhani text-sm text-alien-muted">{{ user.email }}</td>
                <td class="px-4 py-3 font-rajdhani text-xs text-alien-muted">{{ formatDate(user.created_at) }}</td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button class="btn-ghost text-xs py-1 px-2" @click="openForm(user)">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                      Editar
                    </button>
                    <button
                      v-if="user.id !== auth.user?.id"
                      class="btn-danger text-xs py-1 px-2"
                      @click="confirmDelete(user)">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                      Excluir
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- Form Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click.self="closeForm">
      <div class="w-full max-w-md card-alien neon-border modal-enter shadow-2xl">
        <div class="flex items-center justify-between p-5 border-b border-alien-border">
          <h2 class="font-orbitron text-sm text-alien-green tracking-widest">
            {{ isEditing ? 'EDITAR MEMBRO' : 'NOVO MEMBRO' }}
          </h2>
          <button class="text-alien-muted hover:text-alien-green" @click="closeForm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="p-5 space-y-4">
          <div>
            <label class="label-alien">Nome *</label>
            <input v-model="form.name" class="input-alien" placeholder="Nome do membro" />
            <p v-if="errors.name" class="error-text">{{ errors.name[0] }}</p>
          </div>
          <div>
            <label class="label-alien">E-mail *</label>
            <input v-model="form.email" type="email" class="input-alien" placeholder="email@aliensstore.com" />
            <p v-if="errors.email" class="error-text">{{ errors.email[0] }}</p>
          </div>
          <div>
            <label class="label-alien">{{ isEditing ? 'Nova Senha (deixe vazio para manter)' : 'Senha *' }}</label>
            <input v-model="form.password" type="password" class="input-alien" placeholder="Mínimo 8 caracteres" />
            <p v-if="errors.password" class="error-text">{{ errors.password[0] }}</p>
          </div>
          <div v-if="form.password">
            <label class="label-alien">Confirmar Senha *</label>
            <input v-model="form.password_confirmation" type="password" class="input-alien" placeholder="Repita a senha" />
          </div>
        </div>

        <div class="flex justify-end gap-3 p-5 border-t border-alien-border">
          <button class="btn-ghost" @click="closeForm">Cancelar</button>
          <button class="btn-alien" :disabled="saving" @click="handleSubmit">
            <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ saving ? 'Salvando...' : isEditing ? 'Salvar' : 'Cadastrar' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
      <div class="w-full max-w-sm card-alien border-red-500/40 modal-enter shadow-2xl p-6 text-center">
        <div class="text-4xl mb-3">⚠️</div>
        <h3 class="font-orbitron text-sm text-red-400 tracking-widest mb-2">CONFIRMAR EXCLUSÃO</h3>
        <p class="text-alien-muted font-rajdhani text-sm mb-6">
          Deseja remover <span class="text-alien-text font-semibold">{{ deleteTarget.name }}</span>?
        </p>
        <div class="flex gap-3 justify-center">
          <button class="btn-ghost" @click="deleteTarget = null">Cancelar</button>
          <button class="btn-danger" @click="handleDelete">Confirmar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const toast       = useToast()
const auth        = useAuthStore()
const users       = ref([])
const loading     = ref(false)
const showModal   = ref(false)
const saving      = ref(false)
const errors      = ref({})
const deleteTarget = ref(null)
const editingUser  = ref(null)

const isEditing = computed(() => !!editingUser.value)

const form = reactive({ name: '', email: '', password: '', password_confirmation: '' })

onMounted(fetchUsers)

async function fetchUsers() {
  loading.value = true
  try {
    const { data } = await api.get('/users')
    users.value = data
  } finally {
    loading.value = false
  }
}

function openForm(user = null) {
  editingUser.value = user
  errors.value = {}
  if (user) {
    form.name     = user.name
    form.email    = user.email
    form.password = ''
    form.password_confirmation = ''
  } else {
    form.name     = ''
    form.email    = ''
    form.password = ''
    form.password_confirmation = ''
  }
  showModal.value = true
}

function closeForm() {
  showModal.value   = false
  editingUser.value = null
  errors.value      = {}
}

async function handleSubmit() {
  errors.value = {}
  saving.value = true
  try {
    if (isEditing.value) {
      await api.put(`/users/${editingUser.value.id}`, form)
      toast.success('Usuario atualizado!')
    } else {
      await api.post('/users', form)
      toast.success('Usuario cadastrado!')
    }
    closeForm()
    fetchUsers()
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

function confirmDelete(user) {
  deleteTarget.value = user
}

async function handleDelete() {
  try {
    await api.delete(`/users/${deleteTarget.value.id}`)
    toast.success('Usuario removido.')
    deleteTarget.value = null
    fetchUsers()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Erro ao excluir.')
    deleteTarget.value = null
  }
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('pt-BR')
}
</script>
