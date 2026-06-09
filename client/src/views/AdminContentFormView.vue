<template>
  <div class="admin-content-form">
    <div class="admin-container">
      <header class="admin-header">
        <h1 class="admin-title">{{ isEdit ? 'Редактирование релиза' : 'Добавление нового релиза' }}</h1>
        <button class="btn btn--outline" @click="goBack">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Назад
        </button>
      </header>

      <div v-if="pageLoading" class="loading-state">
        <div class="spinner"></div>
        <p>Загрузка...</p>
      </div>

      <form v-else @submit.prevent="submitForm" class="form-container">
        <div class="form-section">
          <h2 class="section-title">Основная информация</h2>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">
                Название релиза <span class="required">*</span>
              </label>
              <input
                v-model="form.title"
                type="text"
                class="form-input"
                :class="{ 'form-input--error': errors.title }"
                placeholder="Введите название"
                maxlength="255"
              />
              <div v-if="errors.title" class="form-error">{{ errors.title }}</div>
            </div>

            <div class="form-group">
              <label class="form-label">
                Исполнитель <span class="required">*</span>
              </label>
              <input
                v-model="form.artist"
                type="text"
                class="form-input"
                :class="{ 'form-input--error': errors.artist }"
                placeholder="Введите имя исполнителя"
                maxlength="255"
              />
              <div v-if="errors.artist" class="form-error">{{ errors.artist }}</div>
            </div>

            <div class="form-group">
              <label class="form-label">
                Тип релиза <span class="required">*</span>
              </label>
              <select
                v-model="form.music_type"
                class="form-select"
                :class="{ 'form-select--error': errors.music_type }"
              >
                <option value="">Выберите тип</option>
                <option value="album">Альбом</option>
                <option value="single">Сингл</option>
                <option value="EP">EP</option>
                <option value="compilation">Сборник</option>
              </select>
              <div v-if="errors.music_type" class="form-error">{{ errors.music_type }}</div>
            </div>

            <div class="form-group">
              <label class="form-label">
                Жанр <span class="required">*</span>
              </label>
              <input
                v-model="form.genre"
                type="text"
                class="form-input"
                :class="{ 'form-input--error': errors.genre }"
                placeholder="Введите жанр"
                maxlength="100"
              />
              <div v-if="errors.genre" class="form-error">{{ errors.genre }}</div>
            </div>

            <div class="form-group">
              <label class="form-label">
                Дата релиза <span class="required">*</span>
              </label>
              <input
                v-model="form.release_date"
                type="date"
                class="form-input"
                :class="{ 'form-input--error': errors.release_date }"
              />
              <div v-if="errors.release_date" class="form-error">{{ errors.release_date }}</div>
            </div>

            <div class="form-group">
              <label class="form-label">Лейбл</label>
              <input
                v-model="form.label"
                type="text"
                class="form-input"
                :class="{ 'form-input--error': errors.label }"
                placeholder="Введите название лейбла"
                maxlength="255"
              />
              <div v-if="errors.label" class="form-error">{{ errors.label }}</div>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h2 class="section-title">Обложка релиза</h2>

          <div class="cover-upload">
            <div class="cover-preview">
              <img
                v-if="coverPreview"
                :src="coverPreview"
                alt="Обложка"
                class="cover-image"
              />
              <div v-else class="cover-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Предпросмотр обложки</span>
              </div>

              <div v-if="uploadProgress > 0" class="upload-progress">
                <div class="progress-bar" :style="{ width: uploadProgress + '%' }"></div>
                <span class="progress-text">{{ uploadProgress }}%</span>
              </div>
            </div>

            <div class="cover-actions">
              <input
                ref="fileInput"
                type="file"
                accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml"
                class="file-input"
                @change="handleFileSelect"
              />

              <button
                type="button"
                class="btn btn--outline"
                @click="triggerFileInput"
                :disabled="coverUploading"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Выбрать файл
              </button>

              <button
                v-if="form.cover_image || coverFile"
                type="button"
                class="btn btn--danger"
                @click="confirmDeleteCover"
                :disabled="coverUploading || coverDeleting"
              >
                <svg v-if="coverDeleting" class="spinner" viewBox="0 0 50 50">
                  <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4"></circle>
                </svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Удалить обложку
              </button>
            </div>

            <div v-if="coverError" class="cover-error">
              {{ coverError }}
            </div>

            <div class="cover-hint">
              <p>Допустимые форматы: JPEG, PNG, JPG, GIF, SVG</p>
              <p>Максимальный размер: 2 МБ</p>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button
            type="button"
            class="btn btn--outline btn--large"
            @click="goBack"
            :disabled="submitting"
          >
            Отмена
          </button>
          <button
            type="submit"
            class="btn btn--primary btn--large"
            :disabled="submitting"
          >
            <svg v-if="submitting" class="spinner" viewBox="0 0 50 50">
              <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4"></circle>
            </svg>
            <span v-else>{{ isEdit ? 'Сохранить изменения' : 'Создать релиз' }}</span>
          </button>
        </div>
      </form>
    </div>

    <Teleport to="body">
      <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDeleteModal">
        <div class="modal-container modal-container--small">
          <div class="modal-header modal-header--danger">
            <h2 class="modal-title">Удаление обложки</h2>
            <button class="modal-close" @click="closeDeleteModal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="modal-body">
            <div class="modal-icon modal-icon--danger">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </div>
            <p class="modal-text">Вы уверены, что хотите удалить обложку?</p>
            <p class="modal-subtext">Это действие нельзя будет отменить.</p>
          </div>

          <div class="modal-actions modal-actions--danger">
            <button
              type="button"
              class="btn btn--outline"
              @click="closeDeleteModal"
              :disabled="coverDeleting"
            >
              Отмена
            </button>
            <button
              type="button"
              class="btn btn--danger"
              @click="deleteCover"
              :disabled="coverDeleting"
            >
              <svg v-if="coverDeleting" class="spinner" viewBox="0 0 50 50">
                <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4"></circle>
              </svg>
              <span v-else>Удалить</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
  <ThemeToggle></ThemeToggle>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, inject } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Cookies from 'js-cookie'
import ThemeToggle from '@/components/ThemeToggle.vue'

interface ContentForm {
  title: string
  artist: string
  music_type: string
  genre: string
  release_date: string
  label: string
  cover_image: string | null
}

interface ContentResponse {
  data: {
    id: number
    title: string
    music_type: string
    artist: string
    genre: string
    release_date: string
    cover_image: string | null
    cover_image_url: string | null
    label: string | null
    favorites_count: number
    reviews_count: number
    added_by: number
    created_at: string
    updated_at: string
    deleted_at: string | null
  }
}

interface CreateContentResponse {
  data: {
    id: number
    title: string
    music_type: string
    artist: string
    genre: string
    release_date: string
    cover_image: string | null
    label: string
    added_by: number
  }
}

interface UploadCoverResponse {
  data: {
    message: string
    cover_url: string
    content: any
  }
}

interface ErrorResponse {
  errors?: Record<string, string[]>
  error?: string
  message?: string
}

const route = useRoute()
const router = useRouter()
const toast = inject('toast') as {
  success: (message: string, timeout?: number) => void
  error: (message: string, timeout?: number) => void
  warning: (message: string, timeout?: number) => void
  info: (message: string, timeout?: number) => void
}

const pageLoading = ref(true)
const isEdit = computed(() => route.params.id && route.params.id !== 'create')

const form = reactive<ContentForm>({
  title: '',
  artist: '',
  music_type: '',
  genre: '',
  release_date: '',
  label: '',
  cover_image: null
})

const errors = reactive<Record<string, string>>({})
const submitting = ref(false)


const fileInput = ref<HTMLInputElement | null>(null)
const coverFile = ref<File | null>(null)
const coverPreview = ref<string | null>(null)
const coverUploading = ref(false)
const coverDeleting = ref(false)
const uploadProgress = ref(0)
const coverError = ref('')


const showDeleteModal = ref(false)

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const getToken = (): string | null => {
  return Cookies.get('auth_token') || null
}

const validateForm = (): boolean => {
  let isValid = true
  errors.title = ''
  errors.artist = ''
  errors.music_type = ''
  errors.genre = ''
  errors.release_date = ''
  errors.label = ''

  if (isEdit.value) {
    return true
  }

  if (!form.title.trim()) {
    errors.title = 'Название обязательно'
    isValid = false
  } else if (form.title.length > 255) {
    errors.title = 'Название не может превышать 255 символов'
    isValid = false
  }

  if (!form.artist.trim()) {
    errors.artist = 'Исполнитель обязателен'
    isValid = false
  } else if (form.artist.length > 255) {
    errors.artist = 'Имя исполнителя не может превышать 255 символов'
    isValid = false
  }

  if (!form.music_type) {
    errors.music_type = 'Тип релиза обязателен'
    isValid = false
  }

  if (!form.genre.trim()) {
    errors.genre = 'Жанр обязателен'
    isValid = false
  } else if (form.genre.length > 100) {
    errors.genre = 'Жанр не может превышать 100 символов'
    isValid = false
  }

  if (!form.release_date) {
    errors.release_date = 'Дата релиза обязательна'
    isValid = false
  }

  if (form.label && form.label.length > 255) {
    errors.label = 'Название лейбла не может превышать 255 символов'
    isValid = false
  }

  return isValid
}

const submitForm = async () => {
  if (!validateForm()) {
    if (!isEdit.value) {
      toast.warning('Пожалуйста, заполните все обязательные поля')
    }
    return
  }

  const token = getToken()
  if (!token) {
    router.push('/login')
    return
  }

  submitting.value = true

  try {
    let url: string
    let method: string
    let bodyData: any = {}

    if (isEdit.value) {
      url = `${API_URL}/contents/${route.params.id}`
      method = 'POST'

      if (form.title) bodyData.title = form.title
      if (form.artist) bodyData.artist = form.artist
      if (form.music_type) bodyData.music_type = form.music_type
      if (form.genre) bodyData.genre = form.genre
      if (form.release_date) bodyData.release_date = form.release_date
      if (form.label) bodyData.label = form.label
    } else {
      url = `${API_URL}/contents`
      method = 'POST'
      bodyData = {
        title: form.title.trim(),
        artist: form.artist.trim(),
        music_type: form.music_type,
        genre: form.genre.trim(),
        release_date: form.release_date,
        label: form.label.trim() || undefined
      }
    }

    console.log(`${isEdit.value ? 'Updating' : 'Creating'} content:`, bodyData)

    const response = await fetch(url, {
      method,
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(bodyData)
    })

    if (response.status === 401) {
      handleUnauthorized()
      return
    }

    if (response.status === 404) {
      const data = await response.json()
      toast.error(data.error || 'Релиз не найден')
      return
    }

    if (response.status === 422) {
      const data: ErrorResponse = await response.json()
      if (data.errors) {
        Object.entries(data.errors).forEach(([field, messages]) => {
          errors[field] = messages[0]
        })
      }
      toast.error('Проверьте правильность заполнения полей')
      return
    }

    const data = await response.json()
    console.log('Content response:', data)

    if (response.ok && data.data) {
      const successMessage = isEdit.value
        ? 'Релиз успешно обновлен'
        : 'Релиз успешно создан'
      toast.success(successMessage)

      if (coverFile.value) {
        await uploadCover(data.data.id)
      } else {
        router.push('/admin/contents')
      }
    } else {
      toast.error(data.error || `Ошибка при ${isEdit.value ? 'обновлении' : 'создании'} релиза`)
    }
  } catch (error) {
    console.error('Error submitting form:', error)
    toast.error(`Произошла ошибка при ${isEdit.value ? 'обновлении' : 'создании'} релиза`)
  } finally {
    submitting.value = false
  }
}

const uploadCover = async (contentId: number) => {
  if (!coverFile.value) return

  const token = getToken()
  if (!token) return

  coverUploading.value = true
  uploadProgress.value = 0

  const formData = new FormData()
  formData.append('cover_image', coverFile.value)

  try {
    const xhr = new XMLHttpRequest()

    xhr.upload.addEventListener('progress', (event) => {
      if (event.lengthComputable) {
        uploadProgress.value = Math.round((event.loaded * 100) / event.total)
      }
    })

    const promise = new Promise((resolve, reject) => {
      xhr.onload = () => {
        if (xhr.status >= 200 && xhr.status < 300) {
          resolve(JSON.parse(xhr.response))
        } else {
          reject(xhr)
        }
      }
      xhr.onerror = () => reject(xhr)
    })

    xhr.open('POST', `${API_URL}/cover/${contentId}`)
    xhr.setRequestHeader('Authorization', `Bearer ${token}`)
    xhr.send(formData)

    const data = await promise as UploadCoverResponse
    console.log('Upload cover response:', data)

    toast.success('Обложка успешно загружена')
    router.push('/admin/contents')
  } catch (error) {
    console.error('Error uploading cover:', error)
    toast.error('Ошибка при загрузке обложки')
  } finally {
    coverUploading.value = false
    uploadProgress.value = 0
  }
}

const deleteCover = async () => {
  if (!isEdit.value || !route.params.id) {
    closeDeleteModal()
    coverFile.value = null
    coverPreview.value = null
    form.cover_image = null
    if (fileInput.value) fileInput.value.value = ''
    toast.success('Обложка удалена')
    return
  }

  const token = getToken()
  if (!token) {
    router.push('/login')
    return
  }

  coverDeleting.value = true

  try {
    const response = await fetch(`${API_URL}/cover/${route.params.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    if (response.status === 401) {
      handleUnauthorized()
      return
    }

    if (response.status === 403) {
      toast.error('Доступ запрещен')
      closeDeleteModal()
      return
    }

    if (response.status === 404) {
      const data = await response.json()
      toast.error(data.error || 'Релиз не найден')
      closeDeleteModal()
      return
    }

    const data = await response.json()
    console.log('Delete cover response:', data)

    if (response.ok && data.data) {
      form.cover_image = null
      coverFile.value = null
      coverPreview.value = null
      if (fileInput.value) fileInput.value.value = ''
      toast.success(data.data.message || 'Обложка успешно удалена')
      closeDeleteModal()
    } else {
      toast.error(data.error || 'Ошибка при удалении обложки')
      closeDeleteModal()
    }
  } catch (error) {
    console.error('Error deleting cover:', error)
    toast.error('Произошла ошибка при удалении обложки')
    closeDeleteModal()
  } finally {
    coverDeleting.value = false
  }
}

const handleFileSelect = (event: Event) => {
  const input = event.target as HTMLInputElement
  if (!input.files?.length) return

  const file = input.files[0]
  coverError.value = ''

  const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml']
  if (!allowedTypes.includes(file.type)) {
    coverError.value = 'Неподдерживаемый формат файла'
    input.value = ''
    return
  }

  if (file.size > 2 * 1024 * 1024) {
    coverError.value = 'Файл слишком большой. Максимальный размер 2MB'
    input.value = ''
    return
  }

  coverFile.value = file

  const reader = new FileReader()
  reader.onload = (e) => {
    coverPreview.value = e.target?.result as string
  }
  reader.readAsDataURL(file)
}

const triggerFileInput = () => {
  fileInput.value?.click()
}

const confirmDeleteCover = () => {
  showDeleteModal.value = true
  document.body.style.overflow = 'hidden'
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  document.body.style.overflow = ''
}

const handleUnauthorized = () => {
  Cookies.remove('auth_token')
  Cookies.remove('user_data')
  router.push('/login')
  toast.error('Сессия истекла. Пожалуйста, войдите снова.')
}

const goBack = () => {
  router.push('/admin/contents')
}

const loadContent = async () => {
  const token = getToken()
  if (!token) {
    router.push('/login')
    return
  }

  try {
    const response = await fetch(`${API_URL}/contents/${route.params.id}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    if (response.status === 401) {
      handleUnauthorized()
      return
    }

    if (response.status === 404) {
      const data = await response.json()
      toast.error(data.error || 'Релиз не найден')
      router.push('/admin/contents')
      return
    }

    const data = await response.json()
    console.log('Load content response:', data)

    if (response.ok && data.data) {
      const content = data.data.content || data.data
      form.title = content.title || ''
      form.artist = content.artist || ''
      form.music_type = content.music_type || ''
      form.genre = content.genre || ''
      form.release_date = content.release_date ? content.release_date.split('T')[0] : ''
      form.label = content.label || ''
      form.cover_image = content.cover_image || null

      if (content.cover_image_url) {
        coverPreview.value = content.cover_image_url
      }
    }
  } catch (error) {
    console.error('Error loading content:', error)
    toast.error('Ошибка при загрузке данных релиза')
  }
}

onMounted(async () => {
  if (isEdit.value) {
    await loadContent()
  }

  setTimeout(() => {
    pageLoading.value = false
  }, 300)
})
</script>

<style lang="scss" scoped>
.admin-content-form {
  min-height: 100vh;
  background: var(--color-bg-secondary);
  padding: var(--space-xl) 0;
}

.admin-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 0 var(--space-lg);
}

.admin-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: var(--space-2xl);
  padding-bottom: var(--space-lg);
  border-bottom: 1px solid var(--border-color);
}

.admin-title {
  font-size: var(--font-size-3xl);
  font-weight: 800;
  color: var(--color-text-primary);
  margin: 0;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--space-3xl);
  background: var(--color-bg-card);
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-color);

  .spinner {
    width: 40px;
    height: 40px;
    border: 3px solid var(--color-bg-secondary);
    border-top-color: var(--color-accent);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: var(--space-md);
  }

  p {
    color: var(--color-text-secondary);
    font-size: var(--font-size-md);
  }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.form-container {
  background: var(--color-bg-card);
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-color);
  overflow: hidden;
}

.form-section {
  padding: var(--space-xl);
  border-bottom: 1px solid var(--border-color);

  &:last-child {
    border-bottom: none;
  }
}

.section-title {
  font-size: var(--font-size-xl);
  font-weight: 700;
  color: var(--color-text-primary);
  margin-bottom: var(--space-lg);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--space-lg);

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
    gap: var(--space-md);
  }
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: var(--space-xs);
}

.form-label {
  font-size: var(--font-size-sm);
  font-weight: 600;
  color: var(--color-text-primary);
  display: flex;
  align-items: center;
  gap: 4px;

  .required {
    color: var(--color-error);
    font-size: var(--font-size-md);
  }
}

.form-input,
.form-select {
  width: 100%;
  padding: var(--space-sm) var(--space-md);
  background: var(--color-bg-secondary);
  border: 2px solid var(--border-color);
  border-radius: var(--radius-md);
  font-size: var(--font-size-md);
  color: var(--color-text-primary);
  transition: var(--transition-base);

  &:focus {
    outline: none;
    border-color: var(--color-accent);
    box-shadow: 0 0 0 3px rgba(var(--color-accent-rgb), 0.1);
  }

  &--error {
    border-color: var(--color-error);

    &:focus {
      border-color: var(--color-error);
      box-shadow: 0 0 0 3px rgba(var(--color-error-rgb), 0.1);
    }
  }
}

.form-select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M6 9l6 6 6-6'%3E%3C/path%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right var(--space-md) center;
}

.form-error {
  font-size: var(--font-size-xs);
  color: var(--color-error);
}

.cover-upload {
  display: flex;
  flex-direction: column;
  gap: var(--space-lg);
}

.cover-preview {
  position: relative;
  width: 300px;
  height: 300px;
  border-radius: var(--radius-xl);
  overflow: hidden;
  border: 2px solid var(--border-color);
  background: var(--color-bg-secondary);
  margin: 0 auto;

  @media (max-width: 768px) {
    width: 100%;
    height: auto;
    aspect-ratio: 1;
  }
}

.cover-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.cover-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: var(--space-md);
  color: var(--color-text-tertiary);

  svg {
    width: 64px;
    height: 64px;
  }

  span {
    font-size: var(--font-size-sm);
  }
}

.upload-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: var(--color-bg-secondary);
}

.progress-bar {
  height: 100%;
  background: var(--color-accent);
  transition: width 0.3s ease;
}

.progress-text {
  position: absolute;
  top: -20px;
  right: 0;
  font-size: var(--font-size-xs);
  color: var(--color-accent);
}

.cover-actions {
  display: flex;
  justify-content: center;
  gap: var(--space-md);
  flex-wrap: wrap;
}

.file-input {
  display: none;
}

.cover-error {
  text-align: center;
  color: var(--color-error);
  font-size: var(--font-size-sm);
  padding: var(--space-sm);
  background: rgba(var(--color-error-rgb), 0.1);
  border-radius: var(--radius-md);
}

.cover-hint {
  text-align: center;
  color: var(--color-text-tertiary);
  font-size: var(--font-size-xs);

  p {
    margin: 4px 0;
  }
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: var(--space-md);
  padding: var(--space-xl);
  background: var(--color-bg-secondary);
  border-top: 1px solid var(--border-color);
}

.btn {
  padding: var(--space-sm) var(--space-lg);
  border-radius: var(--radius-md);
  font-size: var(--font-size-sm);
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition-base);
  border: 1px solid transparent;
  display: inline-flex;
  align-items: center;
  gap: var(--space-xs);

  svg {
    width: 18px;
    height: 18px;
  }

  &--primary {
    background: var(--color-accent);
    color: white;

    &:hover:not(:disabled) {
      background: var(--color-accent-dark);
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }
  }

  &--outline {
    background: transparent;
    border-color: var(--border-color);
    color: var(--color-text-primary);

    &:hover:not(:disabled) {
      background: var(--color-bg-secondary);
      border-color: var(--color-accent);
    }
  }

  &--danger {
    background: var(--color-error);
    color: white;

    &:hover:not(:disabled) {
      background: #dc2626;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(239, 68, 68, 0.3);
    }
  }

  &--large {
    padding: var(--space-md) var(--space-xl);
    font-size: var(--font-size-md);
  }

  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}

.spinner {
  display: inline-block;
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: currentColor;
  animation: spin 0.8s linear infinite;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: var(--space-md);
  backdrop-filter: blur(4px);
}

.modal-container {
  background: var(--color-bg-card);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-xl);
  width: 100%;
  max-width: 450px;
  max-height: 90vh;
  overflow-y: auto;
  animation: modal-appear 0.3s ease;

  &--small {
    max-width: 450px;
  }
}

@keyframes modal-appear {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--space-lg);
  border-bottom: 1px solid var(--border-color);

  &--danger {
    border-bottom-color: var(--color-error);
    background: linear-gradient(to right, rgba(239, 68, 68, 0.05), transparent);
  }
}

.modal-title {
  font-size: var(--font-size-xl);
  font-weight: 700;
  color: var(--color-text-primary);
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  color: var(--color-text-secondary);
  cursor: pointer;
  padding: var(--space-xs);
  border-radius: var(--radius-full);
  transition: var(--transition-base);
  display: flex;
  align-items: center;
  justify-content: center;

  svg {
    width: 24px;
    height: 24px;
  }

  &:hover {
    background: var(--color-bg-secondary);
    color: var(--color-error);
  }
}

.modal-body {
  padding: var(--space-lg);
  text-align: center;
}

.modal-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto var(--space-md);
  color: var(--color-error);
  animation: pulse 2s infinite;

  svg {
    width: 100%;
    height: 100%;
  }
}

@keyframes pulse {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.1);
    opacity: 0.8;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.modal-text {
  font-size: var(--font-size-md);
  color: var(--color-text-primary);
  margin-bottom: var(--space-xs);
}

.modal-subtext {
  font-size: var(--font-size-sm);
  color: var(--color-text-tertiary);
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: var(--space-sm);
  padding: var(--space-lg);
  border-top: 1px solid var(--border-color);

  &--danger {
    border-top-color: var(--color-error);
    background: linear-gradient(to bottom, transparent, rgba(239, 68, 68, 0.05));
  }
}

@media (max-width: 768px) {
  .admin-header {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--space-md);
  }

  .form-actions {
    flex-direction: column;

    .btn {
      width: 100%;
      justify-content: center;
    }
  }

  .cover-actions {
    flex-direction: column;

    .btn {
      width: 100%;
      justify-content: center;
    }
  }
}
</style>