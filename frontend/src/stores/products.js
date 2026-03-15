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
    // Step 1: update product data as JSON
    const jsonData = {
      title:       formData.get('title'),
      description: formData.get('description'),
      sale_price:  formData.get('sale_price'),
      cost_price:  formData.get('cost_price'),
      category:    formData.get('category'),
    }
    await api.put(`/products/${id}/data`, jsonData)

    // Step 2: upload new images if any
    const images = formData.getAll('images[]')
    if (images.length > 0 && images[0].size > 0) {
      const imgFormData = new FormData()
      images.forEach(img => imgFormData.append('images[]', img))
      await api.post(`/products/${id}/images`, imgFormData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    }

    const { data } = await api.get(`/products/${id}`)
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
