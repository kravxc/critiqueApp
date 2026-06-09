<template>
  <div class="admin-contents">
    <div class="admin-container">
      <header class="admin-header">
        <div class="admin-header__left">
          <h1 class="admin-title">Управление релизами</h1>
          <span class="admin-subtitle">Всего релизов: {{ totalFound }}</span>
        </div>
        <div class="admin-header__right">
          <button class="btn btn--primary" @click="goToCreate">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Добавить релиз
          </button>
          <button class="admin-logout" @click="goToDashboard" title="На главную админки">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
          </button>
        </div>
      </header>

      <!-- Filters -->
      <div class="filters-section">
        <div class="search-bar">
          <svg class="search-bar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            v-model="filters.search"
            @input="debouncedSearch"
            placeholder="Поиск по названию или исполнителю..."
            class="search-bar__input"
          />
          <button
            v-if="filters.search"
            class="search-bar__clear"
            @click="clearSearch"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="filters-row">
          <div class="filter-group">
            <label class="filter-label">Жанр</label>
            <select v-model="filters.genre" @change="applyFilters" class="filter-select">
              <option value="all">Все жанры</option>
              <option v-for="genre in uniqueGenres" :key="genre" :value="genre">{{ genre }}</option>
            </select>
          </div>

          <div class="filter-group">
            <label class="filter-label">Тип</label>
            <select v-model="filters.type" @change="applyFilters" class="filter-select">
              <option value="all">Все типы</option>
              <option value="album">Альбомы</option>
              <option value="single">Синглы</option>
              <option value="EP">EP</option>
            </select>
          </div>

          <div class="filter-group">
            <label class="filter-label">Статус</label>
            <select v-model="filters.trashed" @change="applyFilters" class="filter-select">
              <option value="without">Только активные</option>
              <option value="with">Все (включая удаленные)</option>
              <option value="only">Только удаленные</option>
            </select>
          </div>

          <div class="filter-group">
            <label class="filter-label">Сортировка</label>
            <select v-model="filters.sort" @change="applyFilters" class="filter-select">
              <option value="newest">Сначала новые</option>
              <option value="oldest">Сначала старые</option>
              <option value="popular">По популярности</option>
              <option value="title">По названию</option>
            </select>
          </div>

          <button
            class="btn btn--outline reset-btn"
            @click="resetFilters"
            :disabled="isFiltersDefault"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Сбросить
          </button>
        </div>

        <!-- Applied filters -->
        <div v-if="appliedFilters.length > 0" class="applied-filters">
          <span
            v-for="filter in appliedFilters"
            :key="filter.key"
            class="applied-filter"
          >
            {{ filter.label }}: {{ filter.value }}
            <button @click.stop="removeFilter(filter.key)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </span>
        </div>
      </div>

      <!-- Table -->
      <div class="table-section">
        <div v-if="isLoading" class="table-skeleton">
          <div v-for="i in 10" :key="i" class="skeleton-row"></div>
        </div>

        <div v-else-if="contents.length > 0" class="table-responsive">
          <table class="admin-table">
            <thead>
            <tr>
              <th>Обложка</th>
              <th>Название</th>
              <th>Исполнитель</th>
              <th>Тип</th>
              <th>Жанр</th>
              <th>Год</th>
              <th>Статистика</th>
              <th>Статус</th>
              <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <tr
              v-for="content in paginatedContents"
              :key="content.id"
              :class="{ 'trashed-row': content.deleted_at }"
            >
              <td class="cover-cell" @click="viewContent(content.id)">
                <div class="table-cover clickable">
                  <img
                    v-if="content.cover_image_url"
                    :src="content.cover_image_url"
                    :alt="content.title"
                  />
                  <div v-else class="table-cover-placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z" />
                    </svg>
                  </div>
                </div>
              </td>
              <td class="title-cell" @click="viewContent(content.id)">
                <div class="title-info clickable">
                  <span class="title">{{ content.title }}</span>
                  <span v-if="content.label" class="label">{{ content.label }}</span>
                </div>
              </td>
              <td class="artist-cell" @click="viewContent(content.id)">
                <span class="clickable">{{ content.artist }}</span>
              </td>
              <td class="type-cell">{{ getMusicTypeLabel(content.music_type) }}</td>
              <td class="genre-cell">{{ content.genre }}</td>
              <td class="year-cell">{{ formatYear(content.release_date) }}</td>
              <td class="stats-cell">
                <div class="stats-badges">
                    <span class="stat-badge" :title="`${content.favorites_count} в избранном`">
                      <svg viewBox="0 0 20 20" fill="currentColor">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                      </svg>
                      {{ content.favorites_count }}
                    </span>
                  <span class="stat-badge" :title="`${content.reviews_count} рецензий`">
                      <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                      </svg>
                      {{ content.reviews_count }}
                    </span>
                </div>
              </td>
              <td class="status-cell">
                  <span class="status-badge" :class="{
                    'status-active': !content.deleted_at,
                    'status-deleted': content.deleted_at
                  }">
                    {{ content.deleted_at ? 'Удален' : 'Активен' }}
                  </span>
                <span v-if="content.deleted_at" class="delete-date" :title="formatDate(content.deleted_at)">
                    {{ formatRelativeDate(content.deleted_at) }}
                  </span>
              </td>
              <td class="actions-cell">
                <div class="action-buttons">
                  <button
                    class="action-btn edit"
                    @click="goToEdit(content.id)"
                    title="Редактировать"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    v-if="!content.deleted_at"
                    class="action-btn delete"
                    @click="confirmDelete(content)"
                    title="Мягкое удаление"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                  <button
                    v-if="content.deleted_at"
                    class="action-btn restore"
                    @click="confirmRestore(content)"
                    title="Восстановить"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                  </button>
                  <button
                    v-if="content.deleted_at"
                    class="action-btn force-delete"
                    @click="confirmForceDelete(content)"
                    title="Полное удаление"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="empty-state">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z" />
          </svg>
          <h3>Релизы не найдены</h3>
          <p>Попробуйте изменить параметры поиска</p>
          <button class="btn btn--primary" @click="resetFilters">Сбросить все фильтры</button>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="contents.length > pageSize" class="pagination-section">
        <div class="pagination-info">
          Показано {{ (currentPage - 1) * pageSize + 1 }} -
          {{ Math.min(currentPage * pageSize, contents.length) }}
          из {{ contents.length }}
        </div>
        <div class="pagination-controls">
          <button
            class="pagination-btn"
            :disabled="currentPage === 1"
            @click="currentPage--"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
          </button>

          <div class="pagination-pages">
            <button
              v-for="page in visiblePages"
              :key="page"
              class="pagination-page"
              :class="{ 'pagination-page--active': page === currentPage }"
              @click="currentPage = page"
              :disabled="page === '...'"
            >
              {{ page }}
            </button>
          </div>

          <button
            class="pagination-btn"
            :disabled="currentPage === totalPages"
            @click="currentPage++"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
        <div class="pagination-per-page">
          <select v-model="pageSize" @change="currentPage = 1">
            <option :value="10">10 на странице</option>
            <option :value="25">25 на странице</option>
            <option :value="50">50 на странице</option>
            <option :value="100">100 на странице</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <Teleport to="body">
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-container modal-container--small">
        <div class="modal-header" :class="{
          'modal-header--danger': modalAction === 'delete' || modalAction === 'force-delete',
          'modal-header--warning': modalAction === 'restore'
        }">
          <h2 class="modal-title">{{ modalTitle }}</h2>
          <button class="modal-close" @click="closeModal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="modal-body">
          <div class="modal-icon" :class="{
            'modal-icon--danger': modalAction === 'delete' || modalAction === 'force-delete',
            'modal-icon--warning': modalAction === 'restore'
          }">
            <svg v-if="modalAction === 'delete'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            <svg v-else-if="modalAction === 'restore'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <svg v-else-if="modalAction === 'force-delete'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </div>

          <p class="modal-text">{{ modalMessage }}</p>

          <div v-if="selectedContent" class="modal-preview">
            <div class="preview-cover">
              <img
                v-if="selectedContent.cover_image_url"
                :src="selectedContent.cover_image_url"
                :alt="selectedContent.title"
              />
              <div v-else class="preview-cover-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z" />
                </svg>
              </div>
            </div>
            <div class="preview-info">
              <h4 class="preview-title">{{ selectedContent.title }}</h4>
              <p class="preview-artist">{{ selectedContent.artist }}</p>
              <p class="preview-meta">
                {{ getMusicTypeLabel(selectedContent.music_type) }} • {{ selectedContent.genre }} • {{ formatYear(selectedContent.release_date) }}
              </p>
            </div>
          </div>
        </div>

        <div class="modal-actions" :class="{
          'modal-actions--danger': modalAction === 'delete' || modalAction === 'force-delete',
          'modal-actions--warning': modalAction === 'restore'
        }">
          <button
            type="button"
            class="btn btn--outline"
            @click="closeModal"
            :disabled="modalLoading"
          >
            Отмена
          </button>
          <button
            type="button"
            class="btn"
            :class="{
              'btn--danger': modalAction === 'delete' || modalAction === 'force-delete',
              'btn--warning': modalAction === 'restore'
            }"
            @click="executeAction"
            :disabled="modalLoading"
          >
            <svg v-if="modalLoading" class="spinner" viewBox="0 0 50 50">
              <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4"></circle>
            </svg>
            <span v-else>{{ modalButtonText }}</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
  <ThemeToggle></ThemeToggle>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch, inject } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import Cookies from 'js-cookie'
import ThemeToggle from '@/components/ThemeToggle.vue'

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
  deleted_at: string | null
}

interface Filters {
  search: string
  genre: string
  type: string
  trashed: 'with' | 'without' | 'only'
  sort: string
}

const router = useRouter()
const userStore = useUserStore()
const toast = inject('toast') as {
  success: (message: string, timeout?: number) => void
  error: (message: string, timeout?: number) => void
  warning: (message: string, timeout?: number) => void
  info: (message: string, timeout?: number) => void
}

const isLoading = ref(false)
const contents = ref<Content[]>([])
const allContents = ref<Content[]>([])
const totalFound = ref(0)

const filters = reactive<Filters>({
  search: '',
  genre: 'all',
  type: 'all',
  trashed: 'without',
  sort: 'newest'
})

const currentPage = ref(1)
const pageSize = ref(25)

const totalPages = computed(() => Math.ceil(contents.value.length / pageSize.value))

const paginatedContents = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  const end = start + pageSize.value
  return contents.value.slice(start, end)
})

const visiblePages = computed(() => {
  const delta = 2
  const range: number[] = []
  const rangeWithDots: (number | string)[] = []
  let l: number | undefined

  for (let i = 1; i <= totalPages.value; i++) {
    if (i === 1 || i === totalPages.value || (i >= currentPage.value - delta && i <= currentPage.value + delta)) {
      range.push(i)
    }
  }

  range.forEach((i) => {
    if (l) {
      if (i - l === 2) {
        rangeWithDots.push(l + 1)
      } else if (i - l !== 1) {
        rangeWithDots.push('...')
      }
    }
    rangeWithDots.push(i)
    l = i
  })

  return rangeWithDots
})

const uniqueGenres = computed(() => {
  const genres = allContents.value.map(c => c.genre).filter(Boolean)
  return [...new Set(genres)].sort()
})

const appliedFilters = computed(() => {
  const applied: { key: string; label: string; value: string }[] = []
  if (filters.search) {
    applied.push({ key: 'search', label: 'Поиск', value: filters.search })
  }
  if (filters.genre !== 'all') {
    applied.push({ key: 'genre', label: 'Жанр', value: filters.genre })
  }
  if (filters.type !== 'all') {
    applied.push({ key: 'type', label: 'Тип', value: getMusicTypeLabel(filters.type) })
  }
  if (filters.trashed !== 'without') {
    applied.push({
      key: 'trashed',
      label: 'Статус',
      value: filters.trashed === 'with' ? 'Все' : 'Только удаленные'
    })
  }
  return applied
})

const isFiltersDefault = computed(() => {
  return !filters.search &&
    filters.genre === 'all' &&
    filters.type === 'all' &&
    filters.trashed === 'without' &&
    filters.sort === 'newest'
})

// Modal state
const showDeleteModal = ref(false)
const modalLoading = ref(false)
const modalAction = ref<'delete' | 'restore' | 'force-delete' | null>(null)
const selectedContent = ref<Content | null>(null)

const modalTitle = computed(() => {
  switch (modalAction.value) {
    case 'delete': return 'Подтверждение удаления'
    case 'restore': return 'Подтверждение восстановления'
    case 'force-delete': return 'Подтверждение полного удаления'
    default: return ''
  }
})

const modalMessage = computed(() => {
  switch (modalAction.value) {
    case 'delete':
      return 'Вы уверены, что хотите мягко удалить этот релиз? Он будет скрыт с сайта, но его можно будет восстановить.'
    case 'restore':
      return 'Вы уверены, что хотите восстановить этот релиз? Он снова станет доступен на сайте.'
    case 'force-delete':
      return 'Вы уверены, что хотите полностью удалить этот релиз? Это действие необратимо! Все связанные данные (рецензии, лайки, избранное) также будут удалены.'
    default: return ''
  }
})

const modalButtonText = computed(() => {
  switch (modalAction.value) {
    case 'delete': return 'Удалить'
    case 'restore': return 'Восстановить'
    case 'force-delete': return 'Удалить навсегда'
    default: return ''
  }
})

let searchTimeout: ReturnType<typeof setTimeout>

const API_URL = import.meta.env.VITE_API_URL + '/api' || 'http://localhost:8000/api'

const getToken = (): string | null => {
  return Cookies.get('auth_token') || null
}

const getMusicTypeLabel = (type: string) => {
  const types: Record<string, string> = {
    album: 'Альбом',
    single: 'Сингл',
    ep: 'EP',
    EP: 'EP',
    compilation: 'Сборник'
  }
  return types[type] || type
}

const formatYear = (dateString: string) => {
  return new Date(dateString).getFullYear()
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatRelativeDate = (dateString: string) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffTime = Math.abs(now.getTime() - date.getTime())
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

  if (diffDays === 1) return 'вчера'
  if (diffDays < 7) return `${diffDays} дня назад`
  if (diffDays < 30) return `${Math.floor(diffDays / 7)} недель назад`
  return formatDate(dateString)
}

const clearSearch = () => {
  filters.search = ''
  applyFilters()
}

const removeFilter = (key: string) => {
  switch (key) {
    case 'search':
      filters.search = ''
      break
    case 'genre':
      filters.genre = 'all'
      break
    case 'type':
      filters.type = 'all'
      break
    case 'trashed':
      filters.trashed = 'without'
      break
  }
  applyFilters()
}

const searchContents = async () => {
  const token = getToken()
  if (!token) {
    router.push('/login')
    return
  }

  isLoading.value = true
  currentPage.value = 1

  try {
    const params: any = {
      sort: filters.sort,
      show_trashed_filter: filters.trashed
    }

    if (filters.search) {
      params.search = filters.search
    }

    if (filters.genre !== 'all') {
      params.genre = filters.genre
    }

    if (filters.type !== 'all') {
      params.music_type = filters.type
    }

    console.log(' Searching contents with params:', params)

    const url = `${API_URL}/contents/search`

    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(params)
    })

    if (response.status === 401) {
      handleUnauthorized()
      return
    }

    const data = await response.json()
    console.log('📦 Full search response:', JSON.stringify(data, null, 2))

    if (data.data && data.data.length > 0) {
      console.log('🔍 Sample content with fields:', {
        id: data.data[0].id,
        title: data.data[0].title,
        has_deleted_at: 'deleted_at' in data.data[0],
        deleted_at_value: data.data[0].deleted_at
      })
    }

    if (response.ok) {
      if (data.data && Array.isArray(data.data)) {
        const hasDeleted = data.data.some((c: Content) => c.deleted_at !== null)
        console.log(`Has deleted in response: ${hasDeleted}`)

        let filteredData = data.data

        if (filters.trashed === 'only') {
          filteredData = data.data.filter((c: Content) => c.deleted_at !== null)
          console.log(`After 'only' filter: ${filteredData.length} items`)
        } else if (filters.trashed === 'without') {
          filteredData = data.data.filter((c: Content) => c.deleted_at === null)
          console.log(`After 'without' filter: ${filteredData.length} items`)
        }

        contents.value = filteredData
        totalFound.value = filteredData.length
        allContents.value = filteredData
      } else {
        contents.value = []
        totalFound.value = 0
        allContents.value = []
      }
    } else {
      console.error('Error searching contents:', data)
      toast.error('Ошибка при поиске релизов')
      contents.value = []
      totalFound.value = 0
    }
  } catch (error) {
    console.error('Error searching contents:', error)
    toast.error('Произошла ошибка при поиске')
    contents.value = []
    totalFound.value = 0
  } finally {
    isLoading.value = false
  }
}

const handleUnauthorized = () => {
  console.log('Session expired')
  Cookies.remove('auth_token')
  Cookies.remove('user_data')
  router.push('/login')
  toast.error('Сессия истекла. Пожалуйста, войдите снова.')
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const applyFilters = () => {
  searchContents()
}

const resetFilters = () => {
  filters.search = ''
  filters.genre = 'all'
  filters.type = 'all'
  filters.trashed = 'without'
  filters.sort = 'newest'
  searchContents()
}

const viewContent = (id: number) => {
  window.open(`/contents/${id}`, '_blank')
}

const goToDashboard = () => {
  router.push('/admin')
}

const goToCreate = () => {
  router.push({ name: 'admin-contents-create' })
}

const goToEdit = (id: number) => {
  router.push({ name: 'admin-contents-edit', params: { id: id.toString() } })
}

const confirmDelete = (content: Content) => {
  selectedContent.value = content
  modalAction.value = 'delete'
  showDeleteModal.value = true
  document.body.style.overflow = 'hidden'
}

const confirmRestore = (content: Content) => {
  selectedContent.value = content
  modalAction.value = 'restore'
  showDeleteModal.value = true
  document.body.style.overflow = 'hidden'
}

const confirmForceDelete = (content: Content) => {
  selectedContent.value = content
  modalAction.value = 'force-delete'
  showDeleteModal.value = true
  document.body.style.overflow = 'hidden'
}

const closeModal = () => {
  showDeleteModal.value = false
  selectedContent.value = null
  modalAction.value = null
  document.body.style.overflow = ''
}

const executeAction = async () => {
  if (!selectedContent.value || !modalAction.value) return

  const token = getToken()
  if (!token) {
    router.push('/login')
    return
  }

  modalLoading.value = true

  try {
    let url = `${API_URL}/contents/${selectedContent.value.id}`
    let method = 'DELETE'
    let successMessage = ''

    if (modalAction.value === 'delete') {
      method = 'DELETE'
      successMessage = 'Релиз успешно удален'
    } else if (modalAction.value === 'restore') {
      url = `${API_URL}/contents/${selectedContent.value.id}/restore`
      method = 'POST'
      successMessage = 'Релиз успешно восстановлен'
    } else if (modalAction.value === 'force-delete') {
      url = `${API_URL}/contents/${selectedContent.value.id}/force`
      method = 'DELETE'
      successMessage = 'Релиз полностью удален из системы'
    }

    const response = await fetch(url, {
      method,
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    })

    if (response.status === 401) {
      handleUnauthorized()
      return
    }

    if (response.status === 403) {
      toast.error('У вас нет прав для выполнения этого действия')
      closeModal()
      return
    }

    if (response.status === 404) {
      toast.error('Релиз не найден')
      closeModal()
      return
    }

    if (response.ok) {
      toast.success(successMessage)
      closeModal()
      await searchContents()
    } else {
      const data = await response.json()
      toast.error(data.error?.message || 'Ошибка при выполнении операции')
      closeModal()
    }
  } catch (error) {
    console.error('Error executing action:', error)
    toast.error('Произошла ошибка при выполнении операции')
    closeModal()
  } finally {
    modalLoading.value = false
  }
}

watch([filters], () => {
  applyFilters()
}, { deep: true })

watch(currentPage, () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
})

onMounted(() => {
  searchContents()
})
</script>

<style lang="scss" scoped>
.admin-contents {
  min-height: 100vh;
  background: var(--color-bg-secondary);
  padding: var(--space-xl) 0;
}

.admin-container {
  max-width: 1600px;
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

  &__left {
    display: flex;
    align-items: baseline;
    gap: var(--space-md);
  }

  &__right {
    display: flex;
    align-items: center;
    gap: var(--space-md);
  }
}

.admin-title {
  font-size: var(--font-size-3xl);
  font-weight: 800;
  color: var(--color-text-primary);
  margin: 0;
}

.admin-subtitle {
  color: var(--color-text-secondary);
  font-size: var(--font-size-md);
}

.admin-logout {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-full);
  background: var(--color-bg-card);
  border: 1px solid var(--border-color);
  color: var(--color-text-secondary);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: var(--transition-base);

  &:hover {
    color: var(--color-accent);
    border-color: var(--color-accent);
    transform: scale(1.1);
  }

  svg {
    width: 20px;
    height: 20px;
  }
}

.filters-section {
  background: var(--color-bg-card);
  border-radius: var(--radius-xl);
  padding: var(--space-xl);
  border: 1px solid var(--border-color);
  margin-bottom: var(--space-xl);
}

.search-bar {
  position: relative;
  margin-bottom: var(--space-lg);

  &__icon {
    position: absolute;
    left: var(--space-md);
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    color: var(--color-text-tertiary);
  }

  &__input {
    width: 100%;
    padding: var(--space-md) var(--space-xl) var(--space-md) calc(var(--space-md) * 2 + 20px);
    background: var(--color-bg-secondary);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-full);
    font-size: var(--font-size-md);
    color: var(--color-text-primary);
    transition: var(--transition-base);

    &:focus {
      outline: none;
      border-color: var(--color-accent);
      box-shadow: 0 0 0 3px rgba(var(--color-accent-rgb), 0.1);
    }

    &::placeholder {
      color: var(--color-text-tertiary);
    }
  }

  &__clear {
    position: absolute;
    right: var(--space-sm);
    top: 50%;
    transform: translateY(-50%);
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: none;
    border: none;
    color: var(--color-text-tertiary);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition-base);

    &:hover {
      background: var(--color-bg-secondary);
      color: var(--color-error);
    }

    svg {
      width: 16px;
      height: 16px;
    }
  }
}

.filters-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr auto;
  gap: var(--space-md);
  margin-bottom: var(--space-lg);
  align-items: flex-end;

  @media (max-width: 1024px) {
    grid-template-columns: repeat(2, 1fr);
  }

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
    gap: var(--space-sm);
  }
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: var(--space-xs);
}

.filter-label {
  font-size: var(--font-size-sm);
  font-weight: 600;
  color: var(--color-text-primary);
}

.filter-select {
  width: 100%;
  padding: var(--space-sm) var(--space-md);
  background: var(--color-bg-secondary);
  border: 2px solid var(--border-color);
  border-radius: var(--radius-md);
  font-size: var(--font-size-md);
  color: var(--color-text-primary);
  cursor: pointer;
  transition: var(--transition-base);
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M6 9l6 6 6-6'%3E%3C/path%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right var(--space-md) center;

  &:focus {
    outline: none;
    border-color: var(--color-accent);
    box-shadow: 0 0 0 3px rgba(var(--color-accent-rgb), 0.1);
  }
}

.reset-btn {
  height: 42px;
  display: flex;
  align-items: center;
  gap: var(--space-xs);
  white-space: nowrap;

  svg {
    width: 18px;
    height: 18px;
  }
}

.applied-filters {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-xs);
  margin-top: var(--space-md);
  padding-top: var(--space-md);
  border-top: 1px solid var(--border-color);
}

.applied-filter {
  display: inline-flex;
  align-items: center;
  gap: var(--space-xs);
  padding: var(--space-xs) var(--space-sm);
  background: rgba(var(--color-accent-rgb), 0.1);
  border: 1px solid rgba(var(--color-accent-rgb), 0.2);
  border-radius: var(--radius-full);
  font-size: var(--font-size-xs);
  color: var(--color-accent);

  button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: none;
    border: none;
    color: currentColor;
    cursor: pointer;
    padding: 0;
    transition: var(--transition-base);

    &:hover {
      background: rgba(var(--color-accent-rgb), 0.2);
      transform: scale(1.1);
    }

    svg {
      width: 12px;
      height: 12px;
    }
  }
}

.table-section {
  background: var(--color-bg-card);
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-color);
  overflow: hidden;
  margin-bottom: var(--space-xl);
}

.table-responsive {
  overflow-x: auto;
}

.admin-table {
  width: 100%;
  border-collapse: collapse;

  th {
    text-align: left;
    padding: var(--space-md) var(--space-lg);
    background: var(--color-bg-secondary);
    color: var(--color-text-secondary);
    font-weight: 600;
    font-size: var(--font-size-sm);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    white-space: nowrap;
  }

  td {
    padding: var(--space-md) var(--space-lg);
    border-bottom: 1px solid var(--border-color);
    color: var(--color-text-primary);
    font-size: var(--font-size-sm);
  }

  tr:last-child td {
    border-bottom: none;
  }

  tr:hover td {
    background: var(--color-bg-hover);
  }

  .trashed-row {
    background: rgba(239, 68, 68, 0.05);

    &:hover td {
      background: rgba(239, 68, 68, 0.1);
    }
  }
}

.cover-cell {
  width: 50px;
}

.table-cover {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-md);
  overflow: hidden;
  background: var(--color-bg-secondary);

  &.clickable {
    cursor: pointer;
    transition: var(--transition-base);

    &:hover {
      transform: scale(1.1);
      box-shadow: var(--shadow-md);
    }
  }

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
    color: var(--color-text-tertiary);

    svg {
      width: 20px;
      height: 20px;
    }
  }
}

.title-cell {
  min-width: 200px;
}

.title-info {
  display: flex;
  flex-direction: column;
  gap: 4px;

  &.clickable {
    cursor: pointer;
    transition: var(--transition-base);

    &:hover .title {
      color: var(--color-accent);
    }
  }

  .title {
    font-weight: 600;
    color: var(--color-text-primary);
    transition: color 0.2s ease;
  }

  .label {
    font-size: var(--font-size-xs);
    color: var(--color-text-tertiary);
  }
}

.artist-cell {
  min-width: 150px;

  .clickable {
    cursor: pointer;
    transition: var(--transition-base);

    &:hover {
      color: var(--color-accent);
    }
  }
}

.stats-cell {
  min-width: 100px;
}

.stats-badges {
  display: flex;
  gap: var(--space-sm);
}

.stat-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  background: var(--color-bg-secondary);
  border-radius: var(--radius-full);
  font-size: var(--font-size-xs);
  color: var(--color-text-secondary);

  svg {
    width: 14px;
    height: 14px;
    color: var(--color-accent);
  }
}

.status-cell {
  min-width: 120px;
}

.status-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: var(--radius-full);
  font-size: var(--font-size-xs);
  font-weight: 600;

  &.status-active {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
  }

  &.status-deleted {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
  }
}

.delete-date {
  display: block;
  font-size: var(--font-size-xs);
  color: var(--color-text-tertiary);
  margin-top: 2px;
}

.actions-cell {
  min-width: 100px;
}

.action-buttons {
  display: flex;
  gap: var(--space-xs);
  flex-wrap: wrap;
}

.action-btn {
  width: 32px;
  height: 32px;
  border-radius: var(--radius-full);
  background: var(--color-bg-secondary);
  border: 1px solid var(--border-color);
  color: var(--color-text-secondary);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;

  &:hover {
    transform: scale(1.1);
  }

  svg {
    width: 16px;
    height: 16px;
  }

  &.edit:hover {
    background: var(--color-accent);
    border-color: var(--color-accent);
    color: white;
  }

  &.delete:hover {
    background: #ef4444;
    border-color: #ef4444;
    color: white;
  }

  &.restore:hover {
    background: #f59e0b;
    border-color: #f59e0b;
    color: white;
  }

  &.force-delete:hover {
    background: #7f1d1d;
    border-color: #7f1d1d;
    color: white;
  }
}

.table-skeleton {
  padding: var(--space-xl);

  .skeleton-row {
    height: 60px;
    background: linear-gradient(
        90deg,
        var(--color-bg-secondary) 25%,
        var(--color-bg-elevated) 50%,
        var(--color-bg-secondary) 75%
    );
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: var(--radius-md);
    margin-bottom: var(--space-sm);

    &:last-child {
      margin-bottom: 0;
    }
  }
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.pagination-section {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: var(--space-md);
  margin-top: var(--space-xl);
  padding: var(--space-lg);
  background: var(--color-bg-card);
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-color);
}

.pagination-info {
  color: var(--color-text-secondary);
  font-size: var(--font-size-sm);
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
}

.pagination-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--color-bg-card);
  border: 1px solid var(--border-color);
  color: var(--color-text-primary);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: var(--transition-base);

  &:hover:not(:disabled) {
    background: var(--color-accent);
    color: white;
    border-color: var(--color-accent);
    transform: scale(1.1);
  }

  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  svg {
    width: 20px;
    height: 20px;
  }
}

.pagination-pages {
  display: flex;
  gap: var(--space-xs);
  flex-wrap: wrap;
  justify-content: center;
}

.pagination-page {
  min-width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--color-bg-card);
  border: 1px solid var(--border-color);
  color: var(--color-text-primary);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: var(--transition-base);
  font-size: var(--font-size-sm);

  &:hover:not(.pagination-page--active):not(:disabled) {
    background: var(--color-bg-secondary);
    border-color: var(--color-accent);
  }

  &--active {
    background: var(--color-accent);
    color: white;
    border-color: var(--color-accent);
    cursor: default;
  }

  &:disabled {
    cursor: default;
    opacity: 0.5;
  }
}

.pagination-per-page {
  select {
    padding: var(--space-xs) var(--space-md);
    background: var(--color-bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    color: var(--color-text-primary);
    font-size: var(--font-size-sm);
    cursor: pointer;

    &:focus {
      outline: none;
      border-color: var(--color-accent);
    }
  }
}

.empty-state {
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

// Modal styles
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
  max-width: 500px;
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

  &--warning {
    border-bottom-color: #f59e0b;
    background: linear-gradient(to right, rgba(245, 158, 11, 0.05), transparent);
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

.modal-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto var(--space-md);
  display: flex;
  align-items: center;
  justify-content: center;

  svg {
    width: 100%;
    height: 100%;
  }

  &--danger {
    color: var(--color-error);
    animation: pulse 2s infinite;
  }

  &--warning {
    color: #f59e0b;
    animation: pulse 2s infinite;
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
  text-align: center;
  font-size: var(--font-size-md);
  color: var(--color-text-primary);
  margin-bottom: var(--space-lg);
  line-height: 1.6;
}

.modal-preview {
  display: flex;
  gap: var(--space-md);
  padding: var(--space-md);
  background: var(--color-bg-secondary);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-color);
  margin-top: var(--space-md);
}

.preview-cover {
  width: 60px;
  height: 60px;
  border-radius: var(--radius-md);
  overflow: hidden;
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
    background: var(--color-bg-card);
    color: var(--color-text-tertiary);

    svg {
      width: 30px;
      height: 30px;
    }
  }
}

.preview-info {
  flex: 1;
  min-width: 0;
}

.preview-title {
  font-size: var(--font-size-md);
  font-weight: 600;
  color: var(--color-text-primary);
  margin-bottom: 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.preview-artist {
  font-size: var(--font-size-sm);
  color: var(--color-accent);
  margin-bottom: 4px;
}

.preview-meta {
  font-size: var(--font-size-xs);
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

  &--warning {
    border-top-color: #f59e0b;
    background: linear-gradient(to bottom, transparent, rgba(245, 158, 11, 0.05));
  }
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

  &--warning {
    background: #f59e0b;
    color: white;

    &:hover:not(:disabled) {
      background: #d97706;
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(245, 158, 11, 0.3);
    }
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

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .admin-header {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--space-md);
  }

  .pagination-section {
    flex-direction: column;
    align-items: stretch;
  }

  .pagination-controls {
    justify-content: center;
  }

  .pagination-per-page {
    text-align: center;
  }

  .modal-container {
    margin: var(--space-sm);
  }

  .modal-preview {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .preview-cover {
    width: 80px;
    height: 80px;
  }
}
</style>