import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useProductStore = defineStore('products', () => {
  const products   = ref([])
  const pagination = ref({})
  const categories = ref([])
  const loading    = ref(false)

  async function fetchProducts(params = {}) {
    loading.value = true
    try {
      const { data } = await api.get('/products', { params })
      products.value   = data.data
      pagination.value = {
        current_page: data.current_page,
        last_page:    data.last_page,
        total:        data.total,
      }
    } finally {
      loading.value = false
    }
  }

  async function fetchCategories() {
    const { data } = await api.get('/products/categories')
    categories.value = data
  }

  async function createProduct(formData) {
    const { data } = await api.post('/products', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return data
  }

  async function updateProduct(id, formData) {
    // Laravel 11 nao suporta _method em multipart
    // Enviamos como POST com X-HTTP-Method-Override header
    const { data } = await api.post(`/products/${id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'X-HTTP-Method-Override': 'PUT',
      },
    })
    return data
  }

  async function toggleActive(id) {
    const { data } = await api.patch(`/products/${id}/toggle-active`)
    const idx = products.value.findIndex(p => p.id === id)
    if (idx !== -1) products.value[idx] = data
    return data
  }

  async function removeImage(productId, imageId) {
    await api.delete(`/products/${productId}/images/${imageId}`)
  }

  async function getProduct(id) {
    const { data } = await api.get(`/products/${id}`)
    return data
  }

  return {
    products, pagination, categories, loading,
    fetchProducts, fetchCategories,
    createProduct, updateProduct,
    toggleActive, removeImage, getProduct,
  }
})
