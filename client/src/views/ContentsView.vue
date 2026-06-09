<template>
  <div class="contents">
    <div class="container">

      <div class="contents__header">
        <h1 class="contents__title">Музыкальные релизы</h1>
        <p class="contents__subtitle">Все альбомы, синглы и EP</p>
      </div>


      <div class="contents__filters">
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
            <label class="filter-label">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
              </svg>
              Жанр
            </label>
            <select v-model="filters.genre" @change="applyFilters" class="filter-select">
              <option value="all">Все жанры</option>
              <option v-for="genre in uniqueGenres" :key="genre" :value="genre">{{ genre }}</option>
            </select>
          </div>

          <div class="filter-group">
            <label class="filter-label">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
              </svg>
              Тип
            </label>
            <select v-model="filters.type" @change="applyFilters" class="filter-select">
              <option value="all">Все типы</option>
              <option value="album">Альбомы</option>
              <option value="single">Синглы</option>
              <option value="ep">EP</option>
            </select>
          </div>

          <div class="filter-group">
            <label class="filter-label">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
              </svg>
              Сортировка
            </label>
            <select v-model="filters.sort" @change="applyFilters" class="filter-select">
              <option value="newest">Сначала новые</option>
              <option value="oldest">Сначала старые</option>
              <option value="popular">Популярные</option>
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


        <div v-if="!isLoading" class="results-info">
          <div class="results-count">
            <span class="results-number">{{ totalFound }}</span>
            {{ pluralize(totalFound, ['найден', 'найдено', 'найдено']) }}
            {{ pluralize(totalFound, ['релиз', 'релиза', 'релизов']) }}
          </div>
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
      </div>

      <div v-if="isLoading" class="contents__grid">
        <div v-for="i in 12" :key="i" class="content-card content-card--skeleton">
          <div class="content-card__cover skeleton"></div>
          <div class="content-card__info">
            <div class="skeleton skeleton--title"></div>
            <div class="skeleton skeleton--artist"></div>
            <div class="skeleton skeleton--meta"></div>
          </div>
        </div>
      </div>

      <div v-else-if="contents.length > 0" class="contents__grid">
        <div
          v-for="content in paginatedContents"
          :key="content.id"
          class="content-card"
          @click="goToContent(content.id)"
        >
          <div class="content-card__cover">
            <img
              v-if="content.cover_image_url"
              :src="content.cover_image_url"
              :alt="content.title"
              loading="lazy"
              @error="handleImageError"
            />
            <div v-else class="content-card__cover-placeholder">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
              </svg>
            </div>
          </div>

          <div class="content-card__info">
            <h3 class="content-card__title">{{ content.title }}</h3>
            <p class="content-card__artist">{{ content.artist }}</p>

            <div class="content-card__meta">
              <span class="content-card__type">{{ getMusicTypeLabel(content.music_type) }}</span>
              <span class="content-card__genre">{{ content.genre }}</span>
              <span class="content-card__year">{{ formatYear(content.release_date) }}</span>
            </div>

            <div class="content-card__stats">
              <div class="content-card__stat" :title="`${content.favorites_count} лайков`">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
                </svg>
                {{ content.favorites_count || 0 }}
              </div>
              <div class="content-card__stat" :title="`${content.reviews_count || content.reviews_actual_count || 0} рецензий`">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                </svg>
                {{ content.reviews_actual_count || content.reviews_count || 0 }}
              </div>
            </div>
          </div>
        </div>
      </div>


      <div v-else class="contents__empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
        </svg>
        <h3>Релизы не найдены</h3>
        <p>Попробуйте изменить параметры поиска</p>
        <button class="btn btn--primary" @click="resetFilters">Сбросить все фильтры</button>
      </div>

      <div v-if="contents.length > pageSize" class="contents__pagination">
        <button
          class="pagination-btn"
          :disabled="currentPage === 1"
          @click="handlePageChange(currentPage - 1)"
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
            @click="handlePageChange(page)"
            :disabled="page === '...'"
          >
            {{ page }}
          </button>
        </div>

        <button
          class="pagination-btn"
          :disabled="currentPage === totalPages"
          @click="handlePageChange(currentPage + 1)"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'

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
  reviews_actual_count?: number // Добавляем поле для актуального количества рецензий
  added_by: number
  created_at: string
  updated_at: string
  deleted_at: string | null
}

interface Filters {
  search: string
  genre: string
  type: string
  sort: string
}

const router = useRouter()
const isLoading = ref(false)
const contents = ref<Content[]>([])
const allContents = ref<Content[]>([])
const totalFound = ref(0)

const filters = reactive<Filters>({
  search: '',
  genre: 'all',
  type: 'all',
  sort: 'newest'
})

const currentPage = ref(1)
const pageSize = 12

const totalPages = computed(() => Math.ceil(contents.value.length / pageSize))

const paginatedContents = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  const end = start + pageSize
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
  return applied
})

const isFiltersDefault = computed(() => {
  return !filters.search && filters.genre === 'all' && filters.type === 'all' && filters.sort === 'newest'
})

let searchTimeout: ReturnType<typeof setTimeout>

const API_URL = import.meta.env.VITE_API_URL + '/api'|| 'http://localhost:8000/api'


const fetchReviewsCountForContent = async (contentId: number): Promise<number> => {
  try {
    const response = await fetch(`${API_URL}/contents/${contentId}`, {
      headers: {
        'Accept': 'application/json'
      }
    })
    
    const data = await response.json()
    if (response.ok && data.data?.reviews) {
      return data.data.reviews.length
    }
    return 0
  } catch (error) {
    console.error(`Error fetching reviews count for content ${contentId}:`, error)
    return 0
  }
}

// Функция для обогащения данных о релизах актуальным количеством рецензий
const enrichContentsWithReviewsCount = async (contentsList: Content[]): Promise<Content[]> => {
  const enrichedContents = await Promise.all(
    contentsList.map(async (content) => {
      const actualReviewsCount = await fetchReviewsCountForContent(content.id)
      return {
        ...content,
        reviews_actual_count: actualReviewsCount
      }
    })
  )
  return enrichedContents
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

const formatYear = (dateString: string) => {
  return new Date(dateString).getFullYear()
}

const pluralize = (count: number, words: [string, string, string]) => {
  const cases = [2, 0, 1, 1, 1, 2]
  return words[(count % 100 > 4 && count % 100 < 20) ? 2 : cases[(count % 10 < 5) ? count % 10 : 5]]
}

const handleImageError = (e: Event) => {
  const img = e.target as HTMLImageElement
  img.style.display = 'none'
  const parent = img.parentElement
  if (parent) {
    parent.classList.add('content-card__cover--error')
    const placeholder = document.createElement('div')
    placeholder.className = 'content-card__cover-placeholder'
    placeholder.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" /></svg>`
    parent.appendChild(placeholder)
  }
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
  }
  applyFilters()
}

const searchContents = async () => {
  isLoading.value = true
  currentPage.value = 1

  try {
    const params: Record<string, string> = {}

    if (filters.search) {
      params.search = filters.search
    }

    if (filters.genre !== 'all') {
      params.genre = filters.genre
    }

    if (filters.type !== 'all') {
      params.music_type = filters.type
    }

    params.sort = filters.sort

    const url = `${API_URL}/contents/search`
    console.log('Searching contents with params:', params)

    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(params)
    })

    const data = await response.json()
    console.log('Search response:', data)

    if (response.ok) {
      let contentsList: Content[] = []
      if (data.data && Array.isArray(data.data)) {
        contentsList = data.data
        totalFound.value = data.meta?.total_found || contentsList.length
      } else {
        contentsList = []
        totalFound.value = 0
      }
      
      // Обогащаем данные актуальным количеством рецензий
      if (contentsList.length > 0) {
        contents.value = await enrichContentsWithReviewsCount(contentsList)
      } else {
        contents.value = []
      }
    } else {
      console.error('Error searching contents:', data)
      contents.value = []
      totalFound.value = 0
    }
  } catch (error) {
    console.error('Error searching contents:', error)
    contents.value = []
    totalFound.value = 0
  } finally {
    isLoading.value = false
  }
}

const loadAllContents = async () => {
  isLoading.value = true
  currentPage.value = 1

  try {
    const response = await fetch(`${API_URL}/contents`, {
      headers: {
        'Accept': 'application/json'
      }
    })

    const data = await response.json()
    console.log('All contents response:', data)

    if (response.ok) {
      let contentsList: Content[] = []
      if (data.data && Array.isArray(data.data)) {
        if (data.data.length > 0 && Array.isArray(data.data[0])) {
          contentsList = data.data.flat()
        } else {
          contentsList = data.data
        }
      }
      
      // Обогащаем данные актуальным количеством рецензий
      if (contentsList.length > 0) {
        allContents.value = await enrichContentsWithReviewsCount(contentsList)
      } else {
        allContents.value = []
      }
      
      contents.value = allContents.value
      totalFound.value = allContents.value.length
    } else {
      console.error('Error fetching all contents:', data)
      allContents.value = []
      contents.value = []
      totalFound.value = 0
    }
  } catch (error) {
    console.error('Error fetching all contents:', error)
    allContents.value = []
    contents.value = []
    totalFound.value = 0
  } finally {
    isLoading.value = false
  }
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const applyFilters = () => {
  if (filters.search || filters.genre !== 'all' || filters.type !== 'all' || filters.sort !== 'newest') {
    searchContents()
  } else {
    contents.value = allContents.value
    totalFound.value = allContents.value.length
    currentPage.value = 1
  }
}

const resetFilters = () => {
  filters.search = ''
  filters.genre = 'all'
  filters.type = 'all'
  filters.sort = 'newest'
  contents.value = allContents.value
  totalFound.value = allContents.value.length
  currentPage.value = 1
}

const goToContent = (id: number) => {
  router.push(`/contents/${id}`)
}

// Обработчик изменения страницы с прокруткой вверх
const handlePageChange = (page: number | string) => {
  if (page === '...') return
  
  const newPage = typeof page === 'number' ? page : parseInt(page)
  if (newPage === currentPage.value) return
  
  currentPage.value = newPage
  
  // Прокручиваем страницу вверх
  nextTick(() => {
    window.scrollTo({ 
      top: 0, 
      behavior: 'smooth' 
    })
  })
}

// Следим за изменением currentPage для прокрутки (на случай, если страница меняется из других мест)
watch(currentPage, (newPage, oldPage) => {
  if (newPage !== oldPage) {
    nextTick(() => {
      window.scrollTo({ 
        top: 0, 
        behavior: 'smooth' 
      })
    })
  }
})

onMounted(() => {
  loadAllContents()
})
</script>

<style lang="scss" scoped>
/* Стили остаются без изменений */
.contents {
  padding: var(--space-3xl) 0;

  &__header {
    text-align: center;
    margin-bottom: var(--space-2xl);
  }

  &__title {
    font-size: var(--font-size-3xl);
    font-weight: 800;
    color: var(--color-text-primary);
    margin-bottom: var(--space-xs);
  }

  &__subtitle {
    color: var(--color-text-secondary);
    font-size: var(--font-size-lg);
  }

  &__filters {
    margin-bottom: var(--space-xl);
    background: var(--color-bg-card);
    border-radius: var(--radius-xl);
    padding: var(--space-xl);
    border: 1px solid var(--border-color);
  }

  &__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--space-xl);
    margin-bottom: var(--space-xl);
  }

  &__pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-md);
    margin-top: var(--space-xl);
    flex-wrap: wrap;
  }

  &__empty {
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
  grid-template-columns: 1fr 1fr 1fr auto;
  gap: var(--space-md);
  margin-bottom: var(--space-lg);
  align-items: flex-end;

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
  display: flex;
  align-items: center;
  gap: var(--space-xs);
  font-size: var(--font-size-sm);
  font-weight: 600;
  color: var(--color-text-primary);

  svg {
    width: 16px;
    height: 16px;
    color: var(--color-accent);
  }
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

  @media (max-width: 768px) {
    width: 100%;
    justify-content: center;
  }

  svg {
    width: 18px;
    height: 18px;
  }
}

.results-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: var(--space-md);
  padding-top: var(--space-md);
  border-top: 1px solid var(--border-color);
}

.results-count {
  font-size: var(--font-size-sm);
  color: var(--color-text-secondary);

  .results-number {
    font-weight: 700;
    color: var(--color-accent);
    font-size: var(--font-size-md);
    margin-right: 4px;
  }
}

.applied-filters {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-xs);
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

.content-card {
  background: var(--color-bg-card);
  border-radius: var(--radius-xl);
  overflow: hidden;
  border: 1px solid var(--border-color);
  transition: var(--transition-smooth);
  cursor: pointer;
  box-shadow: var(--shadow-md);

  &:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: var(--shadow-accent);
    border-color: var(--color-accent-light);

    .content-card__cover img {
      transform: scale(1.1);
    }

    .content-card__title {
      color: var(--color-accent);
    }
  }

  &--skeleton {
    pointer-events: none;
  }

  &__cover {
    aspect-ratio: 1;
    overflow: hidden;
    background: var(--color-bg-secondary);
    position: relative;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }

    &--error {
      background: var(--color-bg-secondary);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    &-placeholder {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, var(--color-bg-secondary), var(--color-bg-card));
      color: var(--color-text-tertiary);

      svg {
        width: 48px;
        height: 48px;
      }
    }
  }

  &__info {
    padding: var(--space-lg);
  }

  &__title {
    font-size: var(--font-size-lg);
    font-weight: 700;
    margin-bottom: var(--space-xs);
    color: var(--color-text-primary);
    transition: color 0.3s ease;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  &__artist {
    font-size: var(--font-size-sm);
    color: var(--color-text-secondary);
    margin-bottom: var(--space-sm);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  &__meta {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-xs);
    margin-bottom: var(--space-md);
    font-size: var(--font-size-xs);
  }

  &__type {
    color: var(--color-accent);
    background: rgba(var(--color-accent-rgb), 0.1);
    padding: 2px 6px;
    border-radius: var(--radius-full);
  }

  &__genre {
    color: var(--color-text-secondary);
    background: var(--color-bg-secondary);
    padding: 2px 6px;
    border-radius: var(--radius-full);
  }

  &__year {
    color: var(--color-text-tertiary);
    background: var(--color-bg-secondary);
    padding: 2px 6px;
    border-radius: var(--radius-full);
  }

  &__stats {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    border-top: 1px solid var(--border-color);
    padding-top: var(--space-md);
    margin-top: var(--space-md);
  }

  &__stat {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: var(--font-size-xs);
    color: var(--color-text-tertiary);
    cursor: help;

    svg {
      width: 16px;
      height: 16px;
      color: var(--color-accent);
    }
  }
}

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
    height: 24px;
    margin-bottom: var(--space-xs);
  }

  &--artist {
    width: 60%;
    height: 20px;
    margin-bottom: var(--space-sm);
  }

  &--meta {
    width: 40%;
    height: 18px;
  }
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>