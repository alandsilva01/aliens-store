<template>
  <div class="min-h-screen flex items-center justify-center bg-alien-bg px-4 relative overflow-hidden">

    <!-- Background effects -->
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-alien-purple/10 rounded-full blur-3xl"></div>
      <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-alien-green/5 rounded-full blur-3xl"></div>
      <!-- Grid lines -->
      <div class="absolute inset-0 opacity-5"
        style="background-image: linear-gradient(#00FF88 1px, transparent 1px), linear-gradient(90deg, #00FF88 1px, transparent 1px); background-size: 50px 50px;">
      </div>
    </div>

    <div class="w-full max-w-sm relative z-10">
      <!-- Logo -->
      <div class="text-center mb-8">
        <div class="text-6xl mb-3">👽</div>
        <h1 class="font-orbitron text-2xl font-bold neon-text tracking-widest">ALIENS STORE</h1>
        <p class="text-alien-muted text-sm font-rajdhani mt-1 tracking-widest uppercase">Sistema de Produtos Intergaláctico</p>
      </div>

      <!-- Card -->
      <div class="card-alien p-6 neon-border">
        <div class="scan-line"></div>

        <h2 class="font-orbitron text-sm text-alien-muted uppercase tracking-widest mb-6 text-center">
          — Acesso Autorizado —
        </h2>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <label class="label-alien">Identificação</label>
            <input
              v-model="form.email"
              type="email"
              class="input-alien"
              placeholder="admin@aliensstore.com"
              autocomplete="email"
            />
            <p v-if="errors.email" class="error-text">{{ errors.email[0] }}</p>
          </div>

          <div>
            <label class="label-alien">Código de Acesso</label>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPass ? 'text' : 'password'"
                class="input-alien pr-10"
                placeholder="••••••••"
                autocomplete="current-password"
              />
              <button
                type="button"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-alien-muted hover:text-alien-green transition-colors"
                @click="showPass = !showPass"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="showPass" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
              </button>
            </div>
            <p v-if="errors.password" class="error-text">{{ errors.password[0] }}</p>
          </div>

          <p v-if="generalError" class="text-red-400 text-sm text-center bg-red-400/10 rounded py-2 px-3 font-rajdhani border border-red-400/20">
            ⚠ {{ generalError }}
          </p>

          <button type="submit" class="btn-alien w-full justify-center py-3 mt-2" :disabled="loading">
            <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ loading ? 'Autenticando...' : 'Iniciar Transmissão' }}
          </button>
        </form>
      </div>

      <p class="text-center text-xs text-alien-muted mt-4 font-rajdhani">
        Acesso: <span class="text-alien-green font-mono">admin@aliensstore.com</span> / <span class="text-alien-green font-mono">Admin@123</span>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router       = useRouter()
const auth         = useAuthStore()
const loading      = ref(false)
const showPass     = ref(false)
const generalError = ref('')
const errors       = ref({})
const form         = reactive({ email: '', password: '' })

async function handleLogin() {
  loading.value      = true
  generalError.value = ''
  errors.value       = {}
  try {
    await auth.login(form.email, form.password)
    router.push('/')
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
    } else {
      generalError.value = err.response?.data?.message || 'Falha na autenticação.'
    }
  } finally {
    loading.value = false
  }
}
</script>
