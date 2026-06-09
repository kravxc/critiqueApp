
<template>
  <div class="content-detail">
    <div class="container">
      <button class="back-btn" @click="goBack">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span>Назад к релизам</span>
      </button>

      <div v-if="isLoading" class="content-detail__skeleton">
        <div class="skeleton-cover"></div>
        <div class="skeleton-info">
          <div class="skeleton skeleton--title"></div>
          <div class="skeleton skeleton--artist"></div>
          <div class="skeleton skeleton--meta"></div>
          <div class="skeleton skeleton--stats"></div>
        </div>
      </div>

      <div v-else-if="content" class="content-detail__content">
        <div class="content-header">
          <div class="content-cover">
            <img
              v-if="content.cover_image_url"
              :src="content.cover_image_url"
              :alt="content.title"
            />
            <div v-else class="content-cover-placeholder">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
              </svg>
            </div>

            <button
              v-if="user"
              class="favorite-btn"
              :class="{ 'favorite-btn--active': isFavorite }"
              @click="toggleFavorite"
              :disabled="favoriteLoading"
              :title="isFavorite ? 'Убрать из избранного' : 'Добавить в избранное'"
            >
              <svg v-if="favoriteLoading" class="spinner" viewBox="0 0 50 50">
                <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4"></circle>
              </svg>
              <svg v-else viewBox="0 0 24 24" fill="currentColor" stroke="none">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
              </svg>
            </button>
          </div>

          <div class="content-info">
            <h1 class="content-title">{{ content.title }}</h1>
            <p class="content-artist">{{ content.artist }}</p>

            <div class="content-meta">
              <span class="content-type">{{ getMusicTypeLabel(content.music_type) }}</span>
              <span class="content-genre">{{ content.genre }}</span>
              <span class="content-date">{{ formatDate(content.release_date) }}</span>
              <span v-if="content.label" class="content-label">{{ content.label }}</span>
            </div>

            <div class="content-stats">
              <div class="stat" :title="`${content.favorites_count} в избранном`">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                </svg>
                <span class="stat-value">{{ content.favorites_count || 0 }}</span>
              </div>
              <div class="stat" :title="`${actualReviewsCount} рецензий`">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                </svg>
                <span class="stat-value">{{ actualReviewsCount }}</span>
              </div>
            </div>

            <div v-if="user && !hasUserReviewed" class="create-review-btn-container">
              <button class="btn btn--primary btn--large" @click="openReviewModal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Написать рецензию
              </button>
            </div>
            <div v-else-if="user && hasUserReviewed" class="review-already-exists">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>Вы уже оставили рецензию на этот релиз</span>
            </div>
          </div>
        </div>

        <div class="reviews-section">
          <h2 class="reviews-title">
            Рецензии
            <span class="reviews-count">({{actualReviewsCount}})</span>
          </h2>

          <div v-if="authorReviews.length > 0" class="author-reviews">
            <h3 class="author-reviews-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              Авторские рецензии
            </h3>
            <div class="reviews-grid">
              <div
                v-for="review in authorReviews"
                :key="review.id"
                class="review-card review-card--author"
              >
                <div class="review-card__header">
                  <div class="review-card__author">
                    <div class="review-card__avatar">
                      <img
                        v-if="review.user.avatar"
                        :src="getAvatarUrl(review.user.avatar)"
                        :alt="review.user.name"
                      />
                      <div v-else class="review-card__avatar-placeholder">
                        {{ review.user.name.charAt(0).toUpperCase() }}
                      </div>
                    </div>
                    <div>
                      <div class="review-card__author-name">{{ review.user.name }}</div>
                      <div class="review-card__author-role">Автор</div>
                    </div>
                  </div>
                  <div class="review-card__date">{{ formatReviewDate(review.created_at) }}</div>

                  <button
                    v-if="canDeleteReview(review)"
                    class="review-card__delete"
                    @click.stop="confirmDeleteReview(review)"
                    :title="user?.role_id === 1 ? 'Удалить рецензию (админ)' : 'Удалить свою рецензию'"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>

                <h3 class="review-card__title">{{ review.title }}</h3>
                <p class="review-card__text">{{ review.text }}</p>

                <ReviewLikes
                  :review="review"
                  :content="content"
                  :user="user"
                  :like-loading="likeLoading"
                  @toggle-like="toggleLike"
                />
              </div>
            </div>
          </div>

          <div v-if="regularReviews.length > 0" class="regular-reviews">
            <h3 class="regular-reviews-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
              Рецензии пользователей
            </h3>
            <div class="reviews-grid">
              <div
                v-for="review in regularReviews"
                :key="review.id"
                class="review-card"
              >
                <div class="review-card__header">
                  <div class="review-card__author">
                    <div class="review-card__avatar">
                      <img
                        v-if="review.user.avatar"
                        :src="getAvatarUrl(review.user.avatar)"
                        :alt="review.user.name"
                      />
                      <div v-else class="review-card__avatar-placeholder">
                        {{ review.user.name.charAt(0).toUpperCase() }}
                      </div>
                    </div>
                    <div>
                      <div class="review-card__author-name">{{ review.user.name }}</div>
                      <div class="review-card__author-role">Пользователь</div>
                    </div>
                  </div>
                  <div class="review-card__date">{{ formatReviewDate(review.created_at) }}</div>

                  <button
                    v-if="canDeleteReview(review)"
                    class="review-card__delete"
                    @click.stop="confirmDeleteReview(review)"
                    :title="user?.role_id === 1 ? 'Удалить рецензию (админ)' : 'Удалить свою рецензию'"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>

                <h3 class="review-card__title">{{ review.title }}</h3>
                <p class="review-card__text">{{ review.text }}</p>

                <ReviewLikes
                  :review="review"
                  :content="content"
                  :user="user"
                  :like-loading="likeLoading"
                  @toggle-like="toggleLike"
                />
              </div>
            </div>
          </div>

          <div v-if="!authorReviews.length && !regularReviews.length" class="no-reviews">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <h3>Пока нет рецензий</h3>
            <p>Будьте первым, кто оставит рецензию на этот релиз</p>
          </div>
        </div>
      </div>

      <div v-else class="content-detail__error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h2>Релиз не найден</h2>
        <p>Возможно, он был удален или никогда не существовал</p>
        <button class="btn btn--primary" @click="goBack">Вернуться к релизам</button>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showReviewModal" class="modal-overlay" @click.self="closeReviewModal">
        <div class="modal-container">
          <div class="modal-header">
            <h2 class="modal-title">Написать рецензию</h2>
            <button class="modal-close" @click="closeReviewModal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="submitReview" class="review-form">
            <div class="form-group">
              <label for="review-title" class="form-label">Заголовок</label>
              <input
                id="review-title"
                v-model="reviewForm.title"
                type="text"
                class="form-input"
                :class="{ 'form-input--error': formErrors.title }"
                placeholder="Введите заголовок рецензии"
                maxlength="255"
              />
              <div class="form-hint">
                Минимум 5 символов, максимум 255
              </div>
              <div v-if="formErrors.title" class="form-error">
                {{ formErrors.title }}
              </div>
            </div>

            <div class="form-group">
              <label for="review-text" class="form-label">Текст рецензии</label>
              <textarea
                id="review-text"
                v-model="reviewForm.text"
                class="form-textarea"
                :class="{ 'form-textarea--error': formErrors.text }"
                placeholder="Напишите ваше мнение о релизе..."
                rows="8"
              ></textarea>
              <div class="form-hint">
                Минимум 50 символов. Сейчас: {{ reviewForm.text.length }} символов
              </div>
              <div v-if="formErrors.text" class="form-error">
                {{ formErrors.text }}
              </div>
            </div>

            <div class="modal-actions">
              <button type="button" class="btn btn--outline" @click="closeReviewModal">
                Отмена
              </button>
              <button
                type="submit"
                class="btn btn--primary"
                :disabled="reviewSubmitting"
              >
                <svg v-if="reviewSubmitting" class="spinner" viewBox="0 0 50 50">
                  <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4"></circle>
                </svg>
                <span v-else>Опубликовать</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDeleteModal">
        <div class="modal-container modal-container--small">
          <div class="modal-header modal-header--danger">
            <h2 class="modal-title">Удаление рецензии</h2>
            <button class="modal-close" @click="closeDeleteModal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="modal-body">
            <div class="delete-warning">
              <div class="delete-warning__icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <p class="delete-warning__text">
                Вы уверены, что хотите удалить эту рецензию?
              </p>
              <p class="delete-warning__subtext">
                Это действие нельзя будет отменить.
              </p>
            </div>

            <div v-if="reviewToDelete" class="delete-preview">
              <h4 class="delete-preview__title">{{ reviewToDelete.title }}</h4>
              <p class="delete-preview__excerpt">
                {{ reviewToDelete.text.substring(0, 100) }}...
              </p>
            </div>
          </div>

          <div class="modal-actions modal-actions--danger">
            <button
              type="button"
              class="btn btn--outline"
              @click="closeDeleteModal"
              :disabled="deleteLoading"
            >
              Отмена
            </button>
            <button
              type="button"
              class="btn btn--danger"
              @click="deleteReview"
              :disabled="deleteLoading"
            >
              <svg v-if="deleteLoading" class="spinner" viewBox="0 0 50 50">
                <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4"></circle>
              </svg>
              <span v-else>Удалить</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, inject, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import Cookies from 'js-cookie'
import ReviewLikes from '@/components/ReviewLikes.vue'

interface User {
  id: number
  name: string
  email: string
  avatar: string | null
  role_id: number
  bio?: string
  preferences?: {
    favorites: number[]
  }
  avatar_url?: string
}

interface LikeDetail {
  id: number
  user_id: number
  type: string
  user: User
  created_at: string
}

interface ReviewLikes {
  total: number
  breakdown: {
    like: number
    author_like: number
  }
  details: {
    regular_likes: LikeDetail[]
    author_likes: LikeDetail[]
  }
}

interface Review {
  id: number
  title: string
  text: string
  likes: ReviewLikes
  created_at: string
  updated_at: string
  user: User
}

interface Content {
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
}

interface ContentResponse {
  data: {
    content: Content
    reviews: Review[]
  }
}

interface ToggleLikeResponse {
  success: boolean
  message: string
  data?: any
}

interface ToggleFavoriteResponse {
  data: {
    message: string
  }
}

interface CreateReviewResponse {
  data?: Review[]
  error?: {
    message: string
  }
}

interface DeleteReviewResponse {
  message?: string
}

const toast = inject('toast') as {
  success: (message: string, timeout?: number) => void
  error: (message: string, timeout?: number) => void
  warning: (message: string, timeout?: number) => void
  info: (message: string, timeout?: number) => void
}

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()
const isLoading = ref(true)
const content = ref<Content | null>(null)
const reviews = ref<Review[]>([])

const showReviewModal = ref(false)
const reviewSubmitting = ref(false)
const reviewForm = reactive({
  title: '',
  text: ''
})
const formErrors = reactive({
  title: '',
  text: ''
})

const showDeleteModal = ref(false)
const deleteLoading = ref(false)
const reviewToDelete = ref<Review | null>(null)

const likeLoading = ref<Record<string, boolean>>({})
const favoriteLoading = ref(false)

const API_URL = import.meta.env.VITE_API_URL + '/api' || 'http://localhost:8000/api'
const ASSETS_URL = 'https://critiqueassets.storage.yandexcloud.net/'

const user = computed(() => userStore.user)

const hasUserReviewed = computed(() => {
  if (!user.value || !reviews.value.length) return false
  return reviews.value.some(review => review.user.id === user.value?.id)
})

const isFavorite = computed(() => {
  if (!user.value || !user.value.preferences || !content.value) return false
  return user.value.preferences.favorites?.includes(content.value.id) || false
})

const actualReviewsCount = computed(() => {
  return reviews.value.length
})

const authorReviews = computed(() => {
  return reviews.value.filter(review => review.user.role_id === 3)
})

const regularReviews = computed(() => {
  return reviews.value.filter(review => review.user.role_id !== 3)
})

const canDeleteReview = (review: Review) => {
  if (!user.value) return false

  if (user.value.role_id === 1) return true

  return review.user.id === user.value.id
}

const getAvatarUrl = (avatarPath: string | null) => {
  if (!avatarPath) return ''
  if (avatarPath.startsWith('http')) {
    return avatarPath
  }
  const cleanPath = avatarPath.replace(/^storage\//, '')
  return `${ASSETS_URL}${cleanPath}`
}

const getMusicTypeLabel = (type: string) => {
  const types: Record<string, string> = {
    album: 'Альбом',
    single: 'Сингл',
    ep: 'EP',
    compilation: 'Сборник'
  }
  return types[type] || type
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatReviewDate = (dateString: string) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffTime = Math.abs(now.getTime() - date.getTime())
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

  if (diffDays === 1) return 'сегодня'
  if (diffDays < 7) return `${diffDays} дня назад`

  return date.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const hasUserLiked = (review: Review, likeType: string) => {
  if (!user.value || !user.value.id) return false

  if (likeType === 'like') {
    return review.likes?.details?.regular_likes?.some(
      like => like.user_id === user.value?.id
    ) || false
  } else {
    return review.likes?.details?.author_likes?.some(
      like => like.user_id === user.value?.id
    ) || false
  }
}

const isLikeLoading = (reviewId: number, likeType: string) => {
  return likeLoading.value[`${reviewId}-${likeType}`] || false
}

const getToken = (): string | null => {
  return Cookies.get('auth_token') || null
}

const checkAuthAndGetToken = (): string | null => {
  const token = getToken()

  if (!token) {
    console.error('No token found')
    router.push('/login')
    return null
  }

  if (!user.value) {
    console.error('No user in store')
    router.push('/login')
    return null
  }

  return token
}

const handleUnauthorized = () => {
  console.log('Session expired')

  Cookies.remove('auth_token')
  Cookies.remove('user_data')
  userStore.clearUser()

  router.push('/login')

  toast.error('Сессия истекла. Пожалуйста, войдите снова.')
}

const openReviewModal = () => {
  reviewForm.title = ''
  reviewForm.text = ''
  formErrors.title = ''
  formErrors.text = ''
  showReviewModal.value = true
  document.body.style.overflow = 'hidden'
}

const closeReviewModal = () => {
  showReviewModal.value = false
  document.body.style.overflow = ''
}

const validateForm = (): boolean => {
  let isValid = true
  formErrors.title = ''
  formErrors.text = ''

  if (!reviewForm.title.trim()) {
    formErrors.title = 'Заголовок не может быть пустым'
    isValid = false
  } else if (reviewForm.title.length < 5) {
    formErrors.title = 'Заголовок должен содержать минимум 5 символов'
    isValid = false
  } else if (reviewForm.title.length > 255) {
    formErrors.title = 'Заголовок не может превышать 255 символов'
    isValid = false
  }

  if (!reviewForm.text.trim()) {
    formErrors.text = 'Текст рецензии не может быть пустым'
    isValid = false
  } else if (reviewForm.text.length < 50) {
    formErrors.text = `Текст должен содержать минимум 50 символов. Сейчас: ${reviewForm.text.length}`
    isValid = false
  }

  return isValid
}

const submitReview = async () => {
  if (!validateForm()) return

  const token = checkAuthAndGetToken()
  if (!token) return

  if (!content.value) return

  reviewSubmitting.value = true

  try {
    const response = await fetch(`${API_URL}/reviews`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        title: reviewForm.title.trim(),
        text: reviewForm.text.trim(),
        content_id: content.value.id
      })
    })

    const data: CreateReviewResponse = await response.json()
    console.log('Create review response:', { status: response.status, data })

    if (response.status === 401) {
      handleUnauthorized()
      return
    } else if (response.status === 422) {
      const errors = data.error as any
      if (errors.title) {
        formErrors.title = errors.title[0]
      }
      if (errors.text) {
        formErrors.text = errors.text[0]
      }
      toast.error('Проверьте правильность заполнения полей')
    } else if (data.error?.message === 'Review already exists') {
      toast.error('Вы уже оставили рецензию на этот релиз')
    } else if (response.ok && data.data) {
      await fetchContent(true)
      closeReviewModal()
      toast.success('Рецензия успешно опубликована')
    } else {
      console.error('Error creating review:', data)
      toast.error(data.error?.message || 'Ошибка при создании рецензии')
    }
  } catch (error) {
    console.error('Error creating review:', error)
    toast.error('Произошла ошибка при создании рецензии')
  } finally {
    reviewSubmitting.value = false
  }
}

const confirmDeleteReview = (review: Review) => {
  reviewToDelete.value = review
  showDeleteModal.value = true
  document.body.style.overflow = 'hidden'
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  reviewToDelete.value = null
  document.body.style.overflow = ''
}

const deleteReview = async () => {
  if (!reviewToDelete.value) return

  const token = checkAuthAndGetToken()
  if (!token) return

  deleteLoading.value = true

  try {
    const response = await fetch(`${API_URL}/delete/review/${reviewToDelete.value.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    const data: DeleteReviewResponse = await response.json()
    console.log('Delete review response:', { status: response.status, data })

    if (response.status === 401) {
      handleUnauthorized()
      return
    } else if (response.status === 403) {
      toast.error('У вас нет прав для удаления этой рецензии')
      closeDeleteModal()
    } else if (response.status === 404) {
      toast.error('Рецензия не найдена')
      closeDeleteModal()
    } else if (response.ok && data.message === 'Review deleted successfully') {
      await fetchContent(true)
      closeDeleteModal()
      toast.success('Рецензия успешно удалена')
    } else {
      console.error('Error deleting review:', data)
      toast.error(data.message || 'Ошибка при удалении рецензии')
      closeDeleteModal()
    }
  } catch (error) {
    console.error('Error deleting review:', error)
    toast.error('Произошла ошибка при удалении рецензии')
    closeDeleteModal()
  } finally {
    deleteLoading.value = false
  }
}

const toggleLike = async (reviewId: number, likeType: string) => {
  const token = checkAuthAndGetToken()
  if (!token) return

  const review = reviews.value.find(r => r.id === reviewId)
  if (!review) return

  const loadingKey = `${reviewId}-${likeType}`
  likeLoading.value[loadingKey] = true

  try {
    console.log(`Toggling like for review ${reviewId} with type ${likeType}`)

    const response = await fetch(`${API_URL}/toggleLike/review/${reviewId}`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        like_type: likeType
      })
    })

    const data: ToggleLikeResponse = await response.json()
    console.log('Toggle like response:', { status: response.status, data })

    if (response.status === 401) {
      handleUnauthorized()
      return
    } else if (response.ok) {
      await fetchContent(true)

       if (data.message.includes('удален') || data.message.includes('removed')) {
        toast.info(data.message)
    } else {
        toast.success(data.message)
    }
    } else {
      console.error('Error toggling like:', data)
      toast.error(data.message || 'Ошибка при постановке лайка')
    }
  } catch (error) {
    console.error('Error toggling like:', error)
    toast.error('Произошла ошибка при постановке лайка')
  } finally {
    likeLoading.value[loadingKey] = false
  }
}

const toggleFavorite = async () => {
  const token = checkAuthAndGetToken()
  if (!token) return

  if (!content.value) return

  favoriteLoading.value = true

  try {
    console.log(`Toggling favorite for content ${content.value.id}`)

    const response = await fetch(`${API_URL}/toggleFavorite/content/${content.value.id}`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })

    const data: ToggleFavoriteResponse = await response.json()
    console.log('Toggle favorite response:', { status: response.status, data })

    if (response.status === 401) {
      handleUnauthorized()
      return
    } else if (response.ok) {
      await fetchContent(true)
      await userStore.fetchUser()

      if (data.data.message.includes('added')) {
        toast.success('Добавлено в избранное')
      } else if (data.data.message.includes('removed')) {
        toast.info('Удалено из избранного')
      } else {
        toast.success(data.data.message)
      }
    } else {
      console.error('Error toggling favorite:', data)
      toast.error(data.data?.message || 'Ошибка при добавлении в избранное')
    }
  } catch (error) {
    console.error('Error toggling favorite:', error)
    toast.error('Произошла ошибка при добавлении в избранное')
  } finally {
    favoriteLoading.value = false
  }
}

const fetchContent = async (silent: boolean = false) => {
  const contentId = route.params.id

  if (!contentId) {
    router.push('/contents')
    return
  }

  if (!silent) {
    isLoading.value = true
  }

  try {
    const response = await fetch(`${API_URL}/contents/${contentId}`, {
      headers: {
        'Accept': 'application/json'
      }
    })

    const data: ContentResponse = await response.json()
    console.log('Content detail response:', data)

    if (response.ok && data.data) {
      content.value = data.data.content
      reviews.value = data.data.reviews
    } else {
      content.value = null
      reviews.value = []
    }
  } catch (error) {
    console.error('Error fetching content:', error)
    content.value = null
    reviews.value = []
  } finally {
    if (!silent) {
      isLoading.value = false
    }
  }
}

const goBack = () => {
  router.push('/contents')
}

onMounted(async () => {
  console.log('Component mounted')
  console.log('User from store:', user.value)
  console.log('Token exists:', !!Cookies.get('auth_token'))

  if (Cookies.get('auth_token') && !user.value) {
    await userStore.fetchUser()
  }

  await fetchContent()
})
</script>

<style lang="scss" scoped>
.content-detail {
  padding: var(--space-3xl) 0;
  min-height: 100vh;

  .container {
    max-width: 900px;
  }
}
.back-btn {
  display: inline-flex;
  align-items: center;
  gap: var(--space-xs);
  background: none;
  border: none;
  color: var(--color-text-secondary);
  font-size: var(--font-size-sm);
  cursor: pointer;
  padding: var(--space-xs) 0;
  margin-bottom: var(--space-xl);
  transition: var(--transition-base);

  svg {
    width: 18px;
    height: 18px;
  }

  &:hover {
    color: var(--color-accent);
    transform: translateX(-5px);
  }
}

.content-detail__skeleton {
  display: flex;
  gap: var(--space-2xl);

  @media (max-width: 768px) {
    flex-direction: column;
  }

  .skeleton-cover {
    width: 300px;
    height: 300px;
    border-radius: var(--radius-xl);
    background: linear-gradient(
        90deg,
        var(--color-bg-secondary) 25%,
        var(--color-bg-elevated) 50%,
        var(--color-bg-secondary) 75%
    );
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;

    @media (max-width: 768px) {
      width: 100%;
      height: auto;
      aspect-ratio: 1;
    }
  }

  .skeleton-info {
    flex: 1;

    .skeleton {
      background: linear-gradient(
          90deg,
          var(--color-bg-secondary) 25%,
          var(--color-bg-elevated) 50%,
          var(--color-bg-secondary) 75%
      );
      background-size: 200% 100%;
      animation: shimmer 1.5s infinite;
      border-radius: var(--radius-md);

      &--title {
        width: 80%;
        height: 48px;
        margin-bottom: var(--space-md);
      }

      &--artist {
        width: 50%;
        height: 32px;
        margin-bottom: var(--space-lg);
      }

      &--meta {
        width: 60%;
        height: 24px;
        margin-bottom: var(--space-lg);
      }

      &--stats {
        width: 40%;
        height: 32px;
      }
    }
  }
}

.content-header {
  display: flex;
  gap: var(--space-2xl);
  margin-bottom: var(--space-3xl);

  @media (max-width: 768px) {
    flex-direction: column;
    gap: var(--space-xl);
  }
}

.content-cover {
  position: relative;
  width: 300px;
  height: 300px;
  border-radius: var(--radius-xl);
  overflow: hidden;
  box-shadow: var(--shadow-xl);
  flex-shrink: 0;

  @media (max-width: 768px) {
    width: 100%;
    height: auto;
    aspect-ratio: 1;
  }

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  &-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--color-bg-secondary), var(--color-bg-card));
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-tertiary);

    svg {
      width: 80px;
      height: 80px;
    }
  }
}

.favorite-btn {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.9);
  border: 2px solid var(--border-color);
  color: #9ca3af;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  backdrop-filter: blur(4px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);

  svg {
    width: 24px;
    height: 24px;
  }

  &:hover:not(:disabled) {
    transform: scale(1.1);
    background: white;
    border-color: var(--color-accent);
    color: var(--color-accent);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
  }

  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  &--active {
    color: #ef4444;
    border-color: #ef4444;

    svg {
      fill: #ef4444;
    }

    &:hover:not(:disabled) {
      color: #dc2626;
      border-color: #dc2626;

      svg {
        fill: #dc2626;
      }
    }
  }
}

.content-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: var(--space-lg);
}

.content-title {
  font-size: var(--font-size-4xl);
  font-weight: 800;
  line-height: 1.2;
  color: var(--color-text-primary);
  margin: 0;

  @media (max-width: 768px) {
    font-size: var(--font-size-2xl);
  }
}

.content-artist {
  font-size: var(--font-size-xl);
  color: var(--color-accent);
  font-weight: 600;
  margin: 0;
}

.content-meta {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-xs);
  margin: 0;

  span {
    padding: var(--space-xs) var(--space-sm);
    border-radius: var(--radius-full);
    font-size: var(--font-size-sm);
  }

  .content-type {
    background: var(--color-accent);
    color: white;
  }

  .content-genre {
    background: var(--color-bg-secondary);
    color: var(--color-text-secondary);
  }

  .content-date {
    background: var(--color-bg-secondary);
    color: var(--color-text-secondary);
  }

  .content-label {
    background: var(--color-bg-secondary);
    color: var(--color-text-secondary);
  }
}

.content-stats {
  display: flex;
  gap: var(--space-xl);
  margin-top: var(--space-md);

  .stat {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    color: var(--color-text-secondary);


    svg {
      width: 24px;
      height: 24px;
      color: var(--color-accent);
    }

    .stat-value {
      font-size: var(--font-size-lg);
      font-weight: 600;
      color: var(--color-text-primary);
    }
  }
}

.create-review-btn-container {
  margin-top: var(--space-lg);

  .btn {
    display: inline-flex;
    align-items: center;
    gap: var(--space-sm);

    svg {
      width: 20px;
      height: 20px;
    }
  }
}

.review-already-exists {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
  margin-top: var(--space-lg);
  padding: var(--space-md);
  background-color: rgba(var(--color-success-rgb), 0.1);
  border: 1px solid var(--color-success);
  border-radius: var(--radius-lg);
  color: var(--color-success);

  svg {
    width: 24px;
    height: 24px;
    flex-shrink: 0;
  }

  span {
    font-size: var(--font-size-sm);
  }
}

.reviews-section {
  border-top: 1px solid var(--border-color);
  padding-top: var(--space-2xl);
}

.reviews-title {
  font-size: var(--font-size-2xl);
  font-weight: 700;
  margin-bottom: var(--space-xl);
  color: var(--color-text-primary);

  .reviews-count {
    color: var(--color-text-tertiary);
    font-size: var(--font-size-lg);
    font-weight: 400;
    margin-left: var(--space-xs);
  }
}

.author-reviews-title,
.regular-reviews-title {
  display: flex;
  align-items: center;
  gap: var(--space-xs);
  font-size: var(--font-size-xl);
  font-weight: 600;
  margin-bottom: var(--space-lg);
  color: var(--color-text-primary);

  svg {
    width: 24px;
    height: 24px;
    color: var(--color-accent);
  }
}

.author-reviews {
  margin-bottom: var(--space-2xl);
}

.reviews-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-xl);
}

.review-card {
  position: relative;
  background: var(--color-bg-card);
  border-radius: var(--radius-xl);
  padding: var(--space-xl);
  border: 1px solid var(--border-color);
  transition: var(--transition-smooth);

  &--author {
    border-color: var(--color-accent);
    background: linear-gradient(to bottom right, var(--color-bg-card), rgba(var(--color-accent-rgb), 0.05));

    .review-card__author-role {
      color: var(--color-accent);
      font-weight: 600;
    }
  }

  &:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);

    .review-card__delete {
      opacity: 1;
    }
  }

  &__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--space-lg);
    flex-wrap: wrap;
    gap: var(--space-sm);
    padding-right: 40px;
  }

  &__author {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
  }

  &__avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    background: linear-gradient(135deg, var(--color-accent), var(--color-accent-light));
    flex-shrink: 0;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    &-placeholder {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 600;
      font-size: var(--font-size-lg);
    }
  }

  &__author-name {
    font-weight: 600;
    color: var(--color-text-primary);
  }

  &__author-role {
    font-size: var(--font-size-xs);
    color: var(--color-text-tertiary);
  }

  &__date {
    font-size: var(--font-size-xs);
    color: var(--color-text-tertiary);
  }

  &__delete {
    position: absolute;
    top: var(--space-lg);
    right: var(--space-lg);
    width: 32px;
    height: 32px;
    border-radius: var(--radius-full);
    background: var(--color-bg-secondary);
    border: 1px solid var(--border-color);
    color: var(--color-text-tertiary);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    opacity: 0.7;

    &:hover {
      background: var(--color-error);
      border-color: var(--color-error);
      color: white;
      transform: scale(1.1);
      opacity: 1;
    }

    svg {
      width: 16px;
      height: 16px;
    }
  }

  &__title {
    font-size: var(--font-size-xl);
    font-weight: 700;
    margin-bottom: var(--space-md);
    color: var(--color-text-primary);
  }

  &__text {
    color: var(--color-text-secondary);
    line-height: 1.8;
    margin-bottom: var(--space-xl);
    white-space: pre-wrap;
  }

  &__footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    border-top: 1px solid var(--border-color);
    padding-top: var(--space-md);
  }

  &__likes {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: var(--space-xs);
  }
}

.likes-breakdown {
  display: flex;
  align-items: center;
  gap: var(--space-md);
  flex-wrap: wrap;
}

.like-wrapper {
  display: inline-flex;
  align-items: center;
}

.like-btn {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: var(--space-xs) var(--space-sm);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-full);
  background: var(--color-bg-card);
  color: var(--color-text-secondary);
  font-size: var(--font-size-sm);
  cursor: pointer;
  transition: var(--transition-base);

  svg {
    width: 18px;
    height: 18px;
  }

  &:hover:not(:disabled) {
    border-color: var(--color-accent);
    color: var(--color-accent);
    transform: translateY(-2px);
  }

  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  &--active {
    background: var(--color-accent);
    border-color: var(--color-accent);
    color: white;

    &:hover:not(:disabled) {
      background: var(--color-accent-dark);
      color: white;
    }
  }

  &--author {
    svg {
      color: var(--color-accent);
    }

    &--active {
      background: var(--color-accent);
      svg {
        color: white;
      }
    }
  }
}

.like-stat {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: var(--space-xs) var(--space-sm);
  font-size: var(--font-size-sm);
  color: var(--color-text-secondary);
  background: var(--color-bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-full);

  svg {
    width: 18px;
    height: 18px;
  }

  &--author svg {
    color: var(--color-accent);
  }
}

.likes-total {
  font-size: var(--font-size-xs);
  color: var(--color-text-tertiary);
}

.spinner {
  display: inline-block;
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: currentColor;
  animation: spin 0.8s linear infinite;

  &--small {
    width: 16px;
    height: 16px;
  }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.no-reviews {
  text-align: center;
  padding: var(--space-3xl) 0;

  svg {
    width: 64px;
    height: 64px;
    color: var(--color-text-tertiary);
    margin-bottom: var(--space-lg);
  }

  h3 {
    font-size: var(--font-size-xl);
    font-weight: 600;
    margin-bottom: var(--space-sm);
    color: var(--color-text-primary);
  }

  p {
    color: var(--color-text-secondary);
    margin-bottom: var(--space-lg);
  }
}

.content-detail__error {
  text-align: center;
  padding: var(--space-3xl) 0;

  svg {
    width: 64px;
    height: 64px;
    color: var(--color-error);
    margin-bottom: var(--space-lg);
  }

  h2 {
    font-size: var(--font-size-2xl);
    font-weight: 700;
    margin-bottom: var(--space-sm);
    color: var(--color-text-primary);
  }

  p {
    color: var(--color-text-secondary);
    margin-bottom: var(--space-lg);
  }
}

.modal-overlay {
  position: fixed;
  top: 100;
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
  max-width: 600px;
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
    background: linear-gradient(to right, rgba(var(--color-error-rgb), 0.05), transparent);
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
}

.delete-warning {
  text-align: center;
  margin-bottom: var(--space-lg);

  &__icon {
    width: 64px;
    height: 64px;
    margin: 0 auto var(--space-md);
    color: var(--color-error);
    animation: warning-pulse 2s infinite;

    svg {
      width: 100%;
      height: 100%;
    }
  }

  &__text {
    font-size: var(--font-size-lg);
    font-weight: 600;
    color: var(--color-text-primary);
    margin-bottom: var(--space-xs);
  }

  &__subtext {
    font-size: var(--font-size-sm);
    color: var(--color-text-tertiary);
  }
}

@keyframes warning-pulse {
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

.delete-preview {
  background: var(--color-bg-secondary);
  border-radius: var(--radius-lg);
  padding: var(--space-md);
  margin-top: var(--space-md);
  border: 1px solid var(--border-color);

  &__title {
    font-size: var(--font-size-md);
    font-weight: 600;
    color: var(--color-text-primary);
    margin-bottom: var(--space-xs);
  }

  &__excerpt {
    font-size: var(--font-size-sm);
    color: var(--color-text-secondary);
    line-height: 1.5;
    font-style: italic;
  }
}

.review-form {
  padding: var(--space-lg);
}

.form-group {
  margin-bottom: var(--space-lg);
}

.form-label {
  display: block;
  margin-bottom: var(--space-xs);
  font-weight: 600;
  color: var(--color-text-primary);
  font-size: var(--font-size-sm);
}

.form-input,
.form-textarea {
  width: 100%;
  padding: var(--space-sm) var(--space-md);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  background: var(--color-bg-primary);
  color: var(--color-text-primary);
  font-size: var(--font-size-sm);
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

.form-textarea {
  resize: vertical;
  min-height: 120px;
  font-family: inherit;
}

.form-hint {
  font-size: var(--font-size-xs);
  color: var(--color-text-tertiary);
  margin-top: var(--space-xs);
}

.form-error {
  font-size: var(--font-size-xs);
  color: var(--color-error);
  margin-top: var(--space-xs);
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: var(--space-sm);
  margin-top: var(--space-xl);
  border-top: 1px solid var(--border-color);
  padding: var(--space-lg);

  &--danger {
    border-top-color: var(--color-error);
    background: linear-gradient(to bottom, transparent, rgba(var(--color-error-rgb), 0.05));
  }
}

.btn--danger {
  background: var(--color-error);
  border-color: var(--color-error);
  color: white;
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
  }

  &:hover:not(:disabled) {
    //background: var(--color-error-dark);
    border-color: var(--color-error-dark);
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(var(--color-error-rgb), 0.3);

    &::before {
      left: 100%;
    }
  }

  &:active:not(:disabled) {
    transform: translateY(0);
    box-shadow: 0 4px 8px rgba(var(--color-error-rgb), 0.2);
  }

  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    filter: grayscale(0.5);
  }

  .spinner {
    border-color: rgba(255, 255, 255, 0.3);
    border-top-color: white;
  }
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

@media (max-width: 768px) {
  .content-detail {
    padding: var(--space-2xl) 0;
  }

  .content-stats {
    flex-direction: column;
    gap: var(--space-sm);
  }

  .reviews-title {
    font-size: var(--font-size-xl);
  }

  .review-card {
    padding: var(--space-lg);

    &__title {
      font-size: var(--font-size-lg);
    }

    &__header {
      padding-right: 32px;
    }

    &__delete {
      top: var(--space-md);
      right: var(--space-md);
      width: 28px;
      height: 28px;

      svg {
        width: 14px;
        height: 14px;
      }
    }
  }

  .likes-breakdown {
    flex-direction: column;
    align-items: flex-end;
  }

  .modal-container {
    max-height: 95vh;
  }

  .modal-actions {
    flex-direction: column;

    .btn {
      width: 100%;
    }
  }

  .delete-warning {
    &__icon {
      width: 48px;
      height: 48px;
    }

    &__text {
      font-size: var(--font-size-md);
    }
  }

  .delete-preview {
    padding: var(--space-sm);

    &__title {
      font-size: var(--font-size-sm);
    }

    &__excerpt {
      font-size: var(--font-size-xs);
    }
  }
}


</style>