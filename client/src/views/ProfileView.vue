<template>
  <div class="profile">
    <div class="container">
      <div class="profile__header">
        <h1 class="profile__title">Мой профиль</h1>
        <button
          v-if="!isEditing"
          class="btn btn--primary"
          @click="startEditing"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Редактировать профиль
        </button>
      </div>

      <div v-if="isLoading" class="profile__skeleton">
        <div class="profile__skeleton-avatar"></div>
        <div class="profile__skeleton-info">
          <div class="skeleton skeleton--title"></div>
          <div class="skeleton skeleton--text"></div>
          <div class="skeleton skeleton--text"></div>
          <div class="skeleton skeleton--text"></div>
        </div>
      </div>

      <div v-else class="profile__content">
        <div class="profile__main">
          <div class="profile__avatar-section">
            <div class="profile__avatar">
              <img
                v-if="userData.avatar_url || userData.avatar"
                :src="getAvatarUrl(userData.avatar_url || userData.avatar)"
                :alt="userData.name"
              />
              <div v-else class="profile__avatar-placeholder">
                {{ getUserInitials }}
              </div>
            </div>

            <div class="profile__avatar-upload">
              <label for="avatar-upload" class="btn btn--outline btn--small" :class="{ 'btn--loading': isAvatarUploading }">
                <span v-if="isAvatarUploading" class="spinner spinner--small"></span>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>{{ isAvatarUploading ? 'Загрузка...' : 'Загрузить фото' }}</span>
              </label>
              <input
                type="file"
                id="avatar-upload"
                accept="image/*"
                class="profile__avatar-input"
                @change="handleAvatarUpload"
                :disabled="isAvatarUploading"
              />
            </div>
          </div>

          <div class="profile__info">
            <div v-if="!isEditing" class="profile__info-display">
              <h2 class="profile__name">{{ userData.name }}</h2>
              <p class="profile__email">{{ userData.email }}</p>
              <p v-if="userData.bio" class="profile__bio">{{ userData.bio }}</p>
              <p v-else class="profile__bio profile__bio--empty">Добавьте информацию о себе</p>

              <div class="profile__meta">
                <div class="profile__meta-item">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span>Зарегистрирован: {{ formatDate(userData.created_at) }}</span>
                </div>

                <div class="profile__meta-item">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  <span>Роль: {{ getRoleName(userData.role_id) }}</span>
                </div>
              </div>
            </div>

            <div v-else class="profile__info-edit">
              <div class="form-group">
                <label for="name" class="form-label">
                  Имя
                  <span class="form-label__optional">(необязательно)</span>
                </label>
                <input
                  type="text"
                  id="name"
                  v-model="editForm.name"
                  class="form-input"
                  :class="{ 'form-input--error': editErrors.name }"
                  placeholder="Ваше имя"
                />
                <span v-if="editErrors.name" class="form-error">{{ editErrors.name }}</span>
              </div>

              <div class="form-group">
                <label for="email" class="form-label">
                  Email
                  <span class="form-label__optional">(необязательно)</span>
                </label>
                <input
                  type="email"
                  id="email"
                  v-model="editForm.email"
                  class="form-input"
                  :class="{ 'form-input--error': editErrors.email }"
                  placeholder="your@email.com"
                />
                <span v-if="editErrors.email" class="form-error">{{ editErrors.email }}</span>
              </div>

              <div class="form-group">
                <label for="bio" class="form-label">
                  О себе
                  <span class="form-label__optional">(необязательно)</span>
                </label>
                <textarea
                  id="bio"
                  v-model="editForm.bio"
                  class="form-textarea"
                  rows="4"
                  placeholder="Расскажите о себе..."
                ></textarea>
              </div>

              <div class="profile__edit-actions">
                <button class="btn btn--primary" @click="saveProfile" :disabled="isSaving">
                  <span v-if="isSaving" class="spinner"></span>
                  <span v-else>Сохранить</span>
                </button>
                <button class="btn btn--outline" @click="cancelEditing">Отмена</button>
              </div>
            </div>
          </div>
        </div>
        <div class="profile__section">
          <h3 class="profile__section-title">Статистика</h3>
          <div class="profile__stats">
            <div class="profile__stat-card">
              <div class="profile__stat-value">{{ userStats.reviews || 0 }}</div>
              <div class="profile__stat-label">Рецензий</div>
            </div>
            <div class="profile__stat-card">
              <div class="profile__stat-value">{{ userStats.likes || 0 }}</div>
              <div class="profile__stat-label">Лайков получено</div>
            </div>
            <div class="profile__stat-card">
              <div class="profile__stat-value">{{ userStats.comments || 0 }}</div>
              <div class="profile__stat-label">Комментариев</div>
            </div>
          </div>
        </div>
        <div v-if="favoriteContents.length > 0" class="profile__section">
          <h3 class="profile__section-title">Избранные релизы</h3>
          <div class="profile__favorites">
            <div v-for="content in displayedFavorites" :key="content.id" class="profile__favorite-card">
              <div class="profile__favorite-cover">
                <img
                  v-if="content.cover_image"
                  :src="getCoverUrl(content.cover_image)"
                  :alt="content.title"
                />
                <div v-else class="profile__favorite-cover-placeholder">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                  </svg>
                </div>
              </div>

              <div class="profile__favorite-content">
                <h4 class="profile__favorite-title">{{ content.title }}</h4>
                <p class="profile__favorite-artist">{{ content.artist }}</p>

                <div class="profile__favorite-meta">
                  <span class="profile__favorite-type">
                    {{ getMusicTypeLabel(content.music_type) }}
                  </span>
                  <span class="profile__favorite-date">
                    {{ formatReleaseDate(content.release_date) }}
                  </span>
                </div>

                <div class="profile__favorite-stats">
                  <span class="profile__favorite-stat">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                      <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
                    </svg>
                    {{ content.favorites_count || 0 }}
                  </span>
                  <span class="profile__favorite-stat">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                    </svg>
                    {{ content.reviews_count || 0 }}
                  </span>
                </div>
              </div>

              <router-link :to="`/contents/${content.id}`" class="profile__favorite-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
              </router-link>
            </div>
          </div>

          <button v-if="favoriteContents.length > 5" class="profile__show-more" @click="showAllFavorites = !showAllFavorites">
            {{ showAllFavorites ? 'Показать меньше' : 'Показать все' }}
          </button>
        </div>

        <div v-else-if="!isLoadingFavorites" class="profile__section profile__section--empty">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
          </svg>
          <h3>Нет избранных релизов</h3>
          <p>Добавляйте релизы в избранное, чтобы они появились здесь</p>
          <router-link to="/" class="btn btn--outline">На главную</router-link>
        </div>



        <div class="profile__section profile__section--danger">
          <h3 class="profile__section-title"></h3>
          <div class="profile__danger-actions">
            <button class="btn btn--outline btn--danger" @click="showChangePassword = true">

              Сменить пароль
            </button>
            <button class="btn btn--outline btn--danger" @click="showDeleteAccount = true">
              Удалить аккаунт
            </button>
          </div>
        </div>
      </div>
    </div>

    <teleport to="body">
      <div v-if="showChangePassword" class="modal" @click.self="showChangePassword = false">
        <div class="modal__content">
          <h2 class="modal__title">Сменить пароль</h2>

          <div class="modal__form">
            <div class="form-group">
              <label for="current-password" class="form-label">Текущий пароль</label>
              <input
                type="password"
                id="current-password"
                v-model="passwordForm.current"
                @input="clearPasswordError('current')"
                class="form-input"
                :class="{ 'form-input--error': passwordErrors.current }"
                placeholder="Введите текущий пароль"
              />
              <span v-if="passwordErrors.current" class="form-error">{{ passwordErrors.current }}</span>
            </div>

            <div class="form-group">
              <label for="new-password" class="form-label">Новый пароль</label>
              <input
                type="password"
                id="new-password"
                v-model="passwordForm.new"
                @input="validateNewPassword"
                class="form-input"
                :class="{ 'form-input--error': passwordErrors.new }"
                placeholder="Минимум 8 символов"
              />
              <span v-if="passwordErrors.new" class="form-error">{{ passwordErrors.new }}</span>

              <div v-if="passwordForm.new" class="password-strength">
                <div class="strength-bars">
                  <div
                    v-for="i in 4"
                    :key="i"
                    class="strength-bar"
                    :class="getStrengthClass(i)"
                  ></div>
                </div>
                <span class="strength-text">{{ passwordStrengthText }}</span>
              </div>
            </div>

            <div class="form-group">
              <label for="confirm-password" class="form-label">Подтверждение</label>
              <input
                type="password"
                id="confirm-password"
                v-model="passwordForm.confirm"
                @input="validateConfirmPassword"
                class="form-input"
                :class="{ 'form-input--error': passwordErrors.confirm }"
                placeholder="Повторите новый пароль"
              />
              <span v-if="passwordErrors.confirm" class="form-error">{{ passwordErrors.confirm }}</span>
            </div>

            <div v-if="passwordError" class="modal__error">{{ passwordError }}</div>
          </div>

          <div class="modal__actions">
            <button class="btn btn--primary" @click="changePassword" :disabled="isPasswordLoading || !isPasswordValid">
              <span v-if="isPasswordLoading" class="spinner"></span>
              <span v-else>Сменить</span>
            </button>
            <button class="btn btn--outline" @click="closePasswordModal">Отмена</button>
          </div>
        </div>
      </div>
    </teleport>

    <teleport to="body">
      <div v-if="showDeleteAccount" class="modal" @click.self="showDeleteAccount = false">
        <div class="modal__content modal__content--danger">
          <h2 class="modal__title">Удаление аккаунта</h2>

          <div class="modal__message">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p>Вы уверены, что хотите удалить свой аккаунт?</p>
            <p class="modal__warning">Это действие необратимо. Все ваши данные будут потеряны.</p>
          </div>

          <div class="modal__actions">
            <button class="btn btn--danger" @click="deleteAccount" :disabled="isDeleteLoading">
              <span v-if="isDeleteLoading" class="spinner"></span>
              <span v-else>Удалить навсегда</span>
            </button>
            <button class="btn btn--outline" @click="showDeleteAccount = false">Отмена</button>
          </div>
        </div>
      </div>
    </teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, inject, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import Cookies from 'js-cookie'

interface Content {
  id: number
  title: string
  music_type: string
  artist: string
  release_date: string
  cover_image: string | null
  label: string | null
  favorites_count: number
  reviews_count: number
}

const router = useRouter()
const userStore = useUserStore()
const toast = inject('toast') as any

const isLoading = ref(true)
const isLoadingFavorites = ref(false)
const isEditing = ref(false)
const isSaving = ref(false)
const isAvatarUploading = ref(false)
const showChangePassword = ref(false)
const showDeleteAccount = ref(false)
const showAllFavorites = ref(false)
const isPasswordLoading = ref(false)
const isDeleteLoading = ref(false)
const passwordError = ref('')

const isBinding = ref(false)
const isUnbinding = ref(false)
const bindingService = ref<'github' | 'google' | null>(null)
const unbindingService = ref<'github' | 'google' | null>(null)

const userData = ref<any>(null)

const favoriteContents = ref<Content[]>([])

const editForm = reactive({
  name: '',
  email: '',
  bio: ''
})

const editErrors = reactive({
  name: '',
  email: ''
})

const passwordForm = reactive({
  current: '',
  new: '',
  confirm: ''
})

const passwordErrors = reactive({
  current: '',
  new: '',
  confirm: ''
})

const userStats = ref({
  reviews: 5,
  likes: 12,
  comments: 3
})

const API_URL = import.meta.env.VITE_API_URL + '/api'|| 'http://localhost:8000/api'

const displayedFavorites = computed(() => {
  if (showAllFavorites.value) {
    return favoriteContents.value
  }
  return favoriteContents.value.slice(0, 5)
})

const getUserInitials = computed(() => {
  if (!userData.value?.name) return '?'
  return userData.value.name
    .split(' ')
    .map((word: string) => word[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const getRoleName = (roleId: number) => {
  const roles: Record<number, string> = {
    1: 'Администратор',
    2: 'Пользователь',
    3: 'Автор'
  }
  return roles[roleId] || 'Пользователь'
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

const formatReleaseDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getAvatarUrl = (avatar: string) => {
  if (!avatar) return ''
  if (avatar.startsWith('http')) {
    return avatar
  }
  if (avatar.startsWith('storage/') || avatar.startsWith('avatars/')) {
    return `https://critiqueassets.storage.yandexcloud.net/${avatar}`
  }
  return avatar
}

const getCoverUrl = (coverImage: string) => {
  if (!coverImage) return ''
  if (coverImage.startsWith('http')) {
    return coverImage
  }
  return `https://critiqueassets.storage.yandexcloud.net/${coverImage}`
}

const passwordStrength = computed(() => {
  const password = passwordForm.new
  if (!password) return 0

  let strength = 0
  if (password.length >= 8) strength++
  if (/[a-z]/.test(password)) strength++
  if (/[A-Z]/.test(password)) strength++
  if (/[0-9]/.test(password)) strength++
  if (/[^a-zA-Z0-9]/.test(password)) strength++

  return Math.min(strength, 4)
})

const passwordStrengthText = computed(() => {
  const strength = passwordStrength.value
  if (strength === 0) return 'Введите пароль'
  if (strength === 1) return 'Очень слабый'
  if (strength === 2) return 'Слабый'
  if (strength === 3) return 'Средний'
  if (strength === 4) return 'Сильный'
  return ''
})

const getStrengthClass = (index: number) => {
  const strength = passwordStrength.value
  if (index <= strength) {
    if (strength <= 2) return 'strength-bar--weak'
    if (strength === 3) return 'strength-bar--medium'
    if (strength === 4) return 'strength-bar--strong'
  }
  return ''
}

const isPasswordValid = computed(() => {
  return passwordForm.current &&
    passwordForm.new &&
    passwordForm.confirm &&
    !passwordErrors.new &&
    !passwordErrors.confirm &&
    passwordForm.new === passwordForm.confirm &&
    passwordForm.new.length >= 8
})

const validateNewPassword = () => {
  if (!passwordForm.new) {
    passwordErrors.new = 'Новый пароль обязателен'
  } else if (passwordForm.new.length < 8) {
    passwordErrors.new = 'Пароль должен содержать минимум 8 символов'
  } else {
    passwordErrors.new = ''
  }

  if (passwordForm.confirm) {
    validateConfirmPassword()
  }
}

const validateConfirmPassword = () => {
  if (!passwordForm.confirm) {
    passwordErrors.confirm = 'Подтверждение пароля обязательно'
  } else if (passwordForm.new !== passwordForm.confirm) {
    passwordErrors.confirm = 'Пароли не совпадают'
  } else {
    passwordErrors.confirm = ''
  }
}

const clearPasswordError = (field: string) => {
  if (field === 'current') {
    passwordErrors.current = ''
    passwordError.value = ''
  }
}

const closePasswordModal = () => {
  showChangePassword.value = false
  passwordForm.current = ''
  passwordForm.new = ''
  passwordForm.confirm = ''
  passwordErrors.current = ''
  passwordErrors.new = ''
  passwordErrors.confirm = ''
  passwordError.value = ''
}

const fetchFavoriteContents = async (favoriteIds: number[]) => {
  if (!favoriteIds.length) return

  isLoadingFavorites.value = true

  try {
    const promises = favoriteIds.map(async (id) => {
      try {
        const response = await fetch(`${API_URL}/contents/${id}`, {
          headers: {
            'Accept': 'application/json'
          }
        })
        const data = await response.json()
        return response.ok ? data.data.content : null
      } catch {
        return null
      }
    })

    const results = await Promise.all(promises)
    favoriteContents.value = results.filter(Boolean)
  } catch (error) {
    console.error('Error fetching favorite contents:', error)
    toast.error('Ошибка при загрузке избранных релизов')
  } finally {
    isLoadingFavorites.value = false
  }
}

const fetchProfile = async () => {
  isLoading.value = true

  try {
    if (userStore.user) {
      userData.value = userStore.user
      editForm.name = userStore.user.name || ''
      editForm.email = userStore.user.email || ''
      editForm.bio = userStore.user.bio || ''

      if (userStore.user.preferences?.favorites?.length) {
        await fetchFavoriteContents(userStore.user.preferences.favorites)
      }
    } else {
      await userStore.fetchUser()
      if (userStore.user) {
        userData.value = userStore.user
        editForm.name = userStore.user.name || ''
        editForm.email = userStore.user.email || ''
        editForm.bio = userStore.user.bio || ''

        if (userStore.user.preferences?.favorites?.length) {
          await fetchFavoriteContents(userStore.user.preferences.favorites)
        }
      } else {
        router.push('/login')
      }
    }
  } catch (error) {
    console.error('Error fetching profile:', error)
    toast.error('Ошибка при загрузке профиля')
    router.push('/login')
  } finally {
    isLoading.value = false
  }
}

const startEditing = () => {
  editForm.name = userData.value.name || ''
  editForm.email = userData.value.email || ''
  editForm.bio = userData.value.bio || ''
  isEditing.value = true
  editErrors.name = ''
  editErrors.email = ''
}

const cancelEditing = () => {
  isEditing.value = false
  editForm.name = userData.value.name || ''
  editForm.email = userData.value.email || ''
  editForm.bio = userData.value.bio || ''
  editErrors.name = ''
  editErrors.email = ''
}

const saveProfile = async () => {
  isSaving.value = true
  editErrors.name = ''
  editErrors.email = ''

  try {
    const token = Cookies.get('auth_token')

    if (!token) {
      throw new Error('Не найден токен авторизации')
    }

    const response = await fetch(`${API_URL}/edit-profile`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        name: editForm.name || undefined,
        email: editForm.email || undefined,
        bio: editForm.bio || undefined
      })
    })

    const data = await response.json()
    console.log('Profile update response:', data)

    if (!response.ok) {
      if (data.errors) {
        if (data.errors.name) editErrors.name = data.errors.name[0]
        if (data.errors.email) editErrors.email = data.errors.email[0]
        throw new Error('Ошибка валидации')
      } else if (data.message) {
        throw new Error(data.message)
      } else {
        throw new Error('Ошибка при сохранении профиля')
      }
    }

    if (data.data) {
      userStore.user = { ...userStore.user, ...data.data }
      userData.value = userStore.user

      Cookies.set('user_data', JSON.stringify(userStore.user), {
        expires: 7,
        secure: false,
        sameSite: 'lax',
        path: '/'
      })
    }

    isEditing.value = false
    toast.success('Профиль успешно обновлен')

  } catch (error: any) {
    console.error('Error saving profile:', error)
    toast.error(error.message || 'Ошибка при сохранении профиля')
  } finally {
    isSaving.value = false
  }
}

const handleAvatarUpload = async (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return

  if (!file.type.startsWith('image/')) {
    toast.warning('Пожалуйста, выберите изображение')
    return
  }

  if (file.size > 5 * 1024 * 1024) {
    toast.warning('Размер файла не должен превышать 5MB')
    return
  }

  const formData = new FormData()
  formData.append('avatar', file)

  isAvatarUploading.value = true

  try {
    const token = Cookies.get('auth_token')

    if (!token) {
      throw new Error('Не найден токен авторизации')
    }

    const response = await fetch(`${API_URL}/upload-avatar`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`
      },
      body: formData
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Ошибка при загрузке аватара')
    }

    if (data.data?.avatar_url) {
      userStore.user = { ...userStore.user, avatar_url: data.data.avatar_url }
    }
    if (data.data?.user) {
      userStore.user = { ...userStore.user, ...data.data.user }
    }
    userData.value = userStore.user

    Cookies.set('user_data', JSON.stringify(userStore.user), {
      expires: 7,
      secure: false,
      sameSite: 'lax',
      path: '/'
    })

    toast.success('Аватар успешно загружен')
    ;(event.target as HTMLInputElement).value = ''

  } catch (error: any) {
    console.error('Error uploading avatar:', error)
    toast.error(error.message || 'Ошибка при загрузке аватара')
  } finally {
    isAvatarUploading.value = false
  }
}

const messageHandler = async (event: MessageEvent) => {
  if (event.origin !== window.location.origin) return

  const { type, data } = event.data
  console.log('Received message:', type, data)

  if (type === 'github-auth-success' || type === 'google-auth-success') {
    if (data.is_bind) {
      // Это была привязка, просто обновляем данные
      await userStore.fetchUser()
      toast.success(data.message || 'Аккаунт успешно привязан')
    } else {
      // Это был вход/регистрация
      if (data.token) {
        userStore.setUser(data, data.token)
        toast.success('Успешный вход')
        router.push('/')
      }
    }

    // Очищаем данные из localStorage
    localStorage.removeItem('bind_token')
    localStorage.removeItem('bind_service')
  } else if (type === 'github-auth-error' || type === 'google-auth-error') {
    toast.error(data.message || 'Ошибка при авторизации')
    localStorage.removeItem('bind_token')
    localStorage.removeItem('bind_service')
  }

  isBinding.value = false
  bindingService.value = null
}
const bindGitHub = async () => {
  try {
    isBinding.value = true
    bindingService.value = 'github'

    const token = Cookies.get('auth_token')

    if (!token) {
      toast.error('Не найден токен авторизации')
      return
    }

    const response = await fetch(`${API_URL}/auth/github`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    })

    const data = await response.json()

    if (data.redirect_url) {
      localStorage.setItem('bind_token', token)
      localStorage.setItem('bind_service', 'github')

      const width = 600
      const height = 700
      const left = window.screen.width / 2 - width / 2
      const top = window.screen.height / 2 - height / 2

      window.open(
        data.redirect_url,
        'github-bind',
        `width=${width},height=${height},left=${left},top=${top}`
      )
    } else {
      throw new Error('Не получен URL для перенаправления')
    }
  } catch (error: any) {
    console.error('GitHub bind error:', error)
    toast.error(error.message || 'Ошибка при привязке GitHub')
    isBinding.value = false
    bindingService.value = null
  }
}

const bindGoogle = async () => {
  try {
    isBinding.value = true
    bindingService.value = 'google'

    const token = Cookies.get('auth_token')

    if (!token) {
      toast.error('Не найден токен авторизации')
      return
    }

    const response = await fetch(`${API_URL}/auth/google`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    })

    const data = await response.json()

    if (data.redirect_url) {
      localStorage.setItem('bind_token', token)
      localStorage.setItem('bind_service', 'google')

      const width = 600
      const height = 700
      const left = window.screen.width / 2 - width / 2
      const top = window.screen.height / 2 - height / 2

      window.open(
        data.redirect_url,
        'google-bind',
        `width=${width},height=${height},left=${left},top=${top}`
      )
    } else {
      throw new Error('Не получен URL для перенаправления')
    }
  } catch (error: any) {
    console.error('Google bind error:', error)
    toast.error(error.message || 'Ошибка при привязке Google')
    isBinding.value = false
    bindingService.value = null
  }
}
const unbindGitHub = async () => {
  if (!confirm('Вы уверены, что хотите отвязать GitHub аккаунт?')) {
    return
  }

  isUnbinding.value = true
  unbindingService.value = 'github'

  try {
    const token = Cookies.get('auth_token')

    if (!token) {
      throw new Error('Не найден токен авторизации')
    }

    const response = await fetch(`${API_URL}/profile/unbind/github`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    const data = await response.json()

    if (response.ok) {
      userStore.user = { ...userStore.user, github_id: null }
      userData.value = userStore.user

      Cookies.set('user_data', JSON.stringify(userStore.user), {
        expires: 7,
        secure: false,
        sameSite: 'lax',
        path: '/'
      })

      toast.success('GitHub аккаунт отвязан')
    } else {
      throw new Error(data.message || 'Ошибка при отвязке GitHub')
    }
  } catch (error: any) {
    console.error('GitHub unbind error:', error)
    toast.error(error.message || 'Ошибка при отвязке GitHub')
  } finally {
    isUnbinding.value = false
    unbindingService.value = null
  }
}

const unbindGoogle = async () => {
  if (!confirm('Вы уверены, что хотите отвязать Google аккаунт?')) {
    return
  }

  isUnbinding.value = true
  unbindingService.value = 'google'

  try {
    const token = Cookies.get('auth_token')

    if (!token) {
      throw new Error('Не найден токен авторизации')
    }

    const response = await fetch(`${API_URL}/profile/unbind/google`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    const data = await response.json()

    if (response.ok) {
      userStore.user = { ...userStore.user, google_id: null }
      userData.value = userStore.user

      Cookies.set('user_data', JSON.stringify(userStore.user), {
        expires: 7,
        secure: false,
        sameSite: 'lax',
        path: '/'
      })

      toast.success('Google аккаунт отвязан')
    } else {
      throw new Error(data.message || 'Ошибка при отвязке Google')
    }
  } catch (error: any) {
    console.error('Google unbind error:', error)
    toast.error(error.message || 'Ошибка при отвязке Google')
  } finally {
    isUnbinding.value = false
    unbindingService.value = null
  }
}

const changePassword = async () => {
  if (!passwordForm.current) {
    passwordErrors.current = 'Введите текущий пароль'
    return
  }

  validateNewPassword()
  validateConfirmPassword()

  if (passwordErrors.new || passwordErrors.confirm) {
    return
  }

  isPasswordLoading.value = true
  passwordError.value = ''

  try {
    const token = Cookies.get('auth_token')

    if (!token) {
      throw new Error('Не найден токен авторизации')
    }

    const response = await fetch(`${API_URL}/profile/password`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        current_password: passwordForm.current,
        new_password: passwordForm.new,
        new_password_confirmation: passwordForm.confirm
      })
    })

    const data = await response.json()
    console.log('Password change response:', data)

    if (response.ok) {
      closePasswordModal()
      toast.success('Пароль успешно изменен')
    } else {
      if (data.message) {
        if (data.message.includes('текущий пароль') || data.message.includes('current')) {
          passwordErrors.current = data.message
        } else {
          passwordError.value = data.message
        }
      } else {
        passwordError.value = 'Ошибка при смене пароля'
      }
      toast.error(passwordError.value || 'Ошибка при смене пароля')
    }
  } catch (error: any) {
    console.error('Error changing password:', error)
    passwordError.value = 'Произошла ошибка'
    toast.error('Произошла ошибка при смене пароля')
  } finally {
    isPasswordLoading.value = false
  }
}

const deleteAccount = async () => {
  isDeleteLoading.value = true

  try {
    const token = Cookies.get('auth_token')

    if (!token) {
      throw new Error('Не найден токен авторизации')
    }

    const response = await fetch(`${API_URL}/profile`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    if (response.ok) {
      toast.success('Аккаунт успешно удален')
      await userStore.logout()
      router.push('/')
    } else {
      throw new Error('Ошибка при удалении аккаунта')
    }
  } catch (error: any) {
    console.error('Error deleting account:', error)
    toast.error(error.message || 'Ошибка при удалении аккаунта')
  } finally {
    isDeleteLoading.value = false
    showDeleteAccount.value = false
  }
}

onMounted(() => {
  fetchProfile()
  window.addEventListener('message', messageHandler)
})

onUnmounted(() => {
  window.removeEventListener('message', messageHandler)
})
</script>

<style lang="scss" src="@/assets/styles/profile.scss" scoped>
</style>