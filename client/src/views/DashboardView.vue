
<template>
  <div class="admin-dashboard">
    <div class="admin-container">

      <header class="admin-header">
        <h1 class="admin-title">Панель управления</h1>
        <div class="admin-header__right">
          <span class="admin-welcome">С возвращением, {{ user?.name }}</span>
          <button class="admin-logout" @click="handleLogout" title="Выйти">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </div>
      </header>

      <section class="stats-section">
        <h2 class="section-title">Общая статистика</h2>
        <div v-if="statsLoading" class="stats-skeleton">
          <div v-for="i in 3" :key="i" class="stat-card skeleton"></div>
        </div>
        <div v-else-if="stats" class="stats-grid">
          <div class="stat-card" @click="router.push('/admin/contents')">
            <div class="stat-card__icon stat-card__icon--content">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
              </svg>
            </div>
            <div class="stat-card__info">
              <div class="stat-card__value">{{ stats.total_content }}</div>
              <div class="stat-card__label">Всего релизов</div>
            </div>
          </div>

          <div class="stat-card" @click="router.push('/admin/users')">
            <div class="stat-card__icon stat-card__icon--users">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
            <div class="stat-card__info">
              <div class="stat-card__value">{{ stats.total_users }}</div>
              <div class="stat-card__label">Пользователей</div>
            </div>
          </div>

          <div class="stat-card" @click="router.push('/admin/reviews')">
            <div class="stat-card__icon stat-card__icon--reviews">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
            </div>
            <div class="stat-card__info">
              <div class="stat-card__value">{{ stats.total_reviews }}</div>
              <div class="stat-card__label">Рецензий</div>
            </div>
          </div>
        </div>
      </section>

      <section class="contents-section">
        <div class="section-header">
          <h2 class="section-title">Последние релизы</h2>
          <router-link to="/admin/contents" class="view-all-link">
            Все релизы
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </router-link>
        </div>

        <div v-if="contentsLoading" class="contents-skeleton">
          <div v-for="i in 5" :key="i" class="content-card skeleton"></div>
        </div>

        <div v-else-if="lastContents.length" class="contents-list">
          <div v-for="content in lastContents" :key="content.id" class="content-card">
            <div class="content-card__cover">
              <img
                v-if="content.cover_image_url"
                :src="content.cover_image_url"
                :alt="content.title"
              />
              <div v-else class="content-card__cover-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z" />
                </svg>
              </div>
            </div>

            <div class="content-card__info">
              <h3 class="content-card__title">{{ content.title }}</h3>
              <p class="content-card__artist">{{ content.artist }}</p>
              <div class="content-card__meta">
                <span class="content-type">{{ getMusicTypeLabel(content.music_type) }}</span>
                <span class="content-genre">{{ content.genre }}</span>
              </div>
            </div>

            <div class="content-card__stats">
              <div class="stat-item" :title="`${content.favorites_count} в избранном`">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                </svg>
                <span>{{ content.favorites_count }}</span>
              </div>
              <div class="stat-item" :title="`${content.reviews_count} рецензий`">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                </svg>
                <span>{{ content.reviews_count }}</span>
              </div>
              <div class="content-status" :class="{ 'status-deleted': content.deleted_at }">
                {{ content.deleted_at ? 'Удален' : 'Активен' }}
              </div>
            </div>

            <div class="content-card__actions">
              <router-link :to="`/admin/contents/edit/${content.id}`" class="action-btn edit" title="Редактировать">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </router-link>
              <button class="action-btn view" :title="`Просмотр релиза`" @click="viewContent(content.id)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div v-else class="empty-state">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z" />
          </svg>
          <h3>Нет релизов</h3>
          <p>На платформе пока нет добавленных релизов</p>
        </div>
      </section>

      <section class="quick-actions">
        <h2 class="section-title">Быстрые действия</h2>
        <div class="actions-grid">
          <router-link to="/admin/contents/create" class="action-card">
            <div class="action-card__icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
              </svg>
            </div>
            <h3 class="action-card__title">Добавить релиз</h3>
            <p class="action-card__text">Создать новый музыкальный релиз</p>
          </router-link>

          <router-link to="/admin/users" class="action-card">
            <div class="action-card__icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
            </div>
            <h3 class="action-card__title">Управление пользователями</h3>
            <p class="action-card__text">Просмотр и редактирование профилей</p>
          </router-link>

          <router-link to="/admin/reviews" class="action-card">
            <div class="action-card__icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
            </div>
            <h3 class="action-card__title">Модерация рецензий</h3>
            <p class="action-card__text">Проверка и управление рецензиями</p>
          </router-link>

          <router-link to="/admin/settings" class="action-card">
            <div class="action-card__icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <h3 class="action-card__title">Настройки платформы</h3>
            <p class="action-card__text">Общие настройки и конфигурация</p>
          </router-link>
        </div>
      </section>
    </div>
    <ThemeToggle></ThemeToggle>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, inject } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import Cookies from 'js-cookie'
import ThemeToggle from '@/components/ThemeToggle.vue'

interface Stats {
  total_content: number
  total_users: number
  total_reviews: number
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
  deleted_at: string | null
}

const router = useRouter()
const userStore = useUserStore()
const toast = inject('toast') as {
  success: (message: string, timeout?: number) => void
  error: (message: string, timeout?: number) => void
}

const user = computed(() => userStore.user)

const statsLoading = ref(true)
const stats = ref<Stats | null>(null)

const contentsLoading = ref(true)
const lastContents = ref<Content[]>([])

const API_URL = 'http://127.0.0.1:8000/api'

const getToken = (): string | null => {
  return Cookies.get('auth_token') || null
}

const getMusicTypeLabel = (type: string) => {
  const types: Record<string, string> = {
    album: 'Альбом',
    single: 'Сингл',
    ep: 'EP',
  }
  return types[type] || type
}

const fetchStats = async () => {
  const token = getToken()
  if (!token) return

  statsLoading.value = true

  try {
    const response = await fetch(`${API_URL}/dashboard-stats`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    const data = await response.json()
    console.log('Stats response:', data)

    if (response.ok && data.data) {
      stats.value = data.data.stats
    }
  } catch (error) {
    console.error('Error fetching stats:', error)
    toast.error('Ошибка при загрузке статистики')
  } finally {
    statsLoading.value = false
  }
}

const fetchLastContents = async () => {
  const token = getToken()
  if (!token) return

  contentsLoading.value = true

  try {
    const response = await fetch(`${API_URL}/last-contents`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    const data = await response.json()
    console.log('Last contents response:', data)

    if (response.ok && data.data) {
      lastContents.value = data.data.flat()
    }
  } catch (error) {
    console.error('Error fetching last contents:', error)
    toast.error('Ошибка при загрузке последних релизов')
  } finally {
    contentsLoading.value = false
  }
}

const viewContent = (id: number) => {
  window.open(`/content/${id}`, '_blank')
}

const handleLogout = async () => {
  await userStore.logout()
  router.push('/login')
  toast.success('Вы вышли из системы')
}

onMounted(async () => {
  await fetchStats()
  await fetchLastContents()
})
</script>

<style lang="scss" scoped>
.admin-dashboard {
  min-height: 100vh;
  background: var(--color-bg-secondary);
  padding: var(--space-xl) 0;
}

.admin-container {
  max-width: 1400px;
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

.admin-welcome {
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
    color: var(--color-error);
    border-color: var(--color-error);
    transform: scale(1.1);
  }

  svg {
    width: 20px;
    height: 20px;
  }
}

.section-title {
  font-size: var(--font-size-2xl);
  font-weight: 700;
  color: var(--color-text-primary);
  margin-bottom: var(--space-xl);
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: var(--space-xl);
}

.view-all-link {
  display: flex;
  align-items: center;
  gap: var(--space-xs);
  color: var(--color-accent);
  text-decoration: none;
  font-weight: 500;
  transition: var(--transition-base);

  &:hover {
    gap: var(--space-md);
  }

  svg {
    width: 20px;
    height: 20px;
  }
}


.stats-section {
  margin-bottom: var(--space-2xl);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--space-lg);
}

.stat-card {
  display: flex;
  align-items: center;
  gap: var(--space-lg);
  padding: var(--space-xl);
  background: var(--color-bg-card);
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-color);
  transition: var(--transition-smooth);

  &:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--color-accent);
    cursor: pointer;
  }

  &__icon {
    width: 60px;
    height: 60px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;

    svg {
      width: 30px;
      height: 30px;
    }

    &--content {
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(59, 130, 246, 0.05));
      color: #3b82f6;
    }

    &--users {
      background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0.05));
      color: #10b981;
    }

    &--reviews {
      background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0.05));
      color: #f59e0b;
    }
  }

  &__info {
    flex: 1;
  }

  &__value {
    font-size: var(--font-size-3xl);
    font-weight: 800;
    color: var(--color-text-primary);
    line-height: 1.2;
    margin-bottom: var(--space-xs);
  }

  &__label {
    color: var(--color-text-secondary);
    font-size: var(--font-size-sm);
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
}


.stats-skeleton,
.contents-skeleton {
  display: grid;
  gap: var(--space-lg);
}

.stats-skeleton {
  grid-template-columns: repeat(3, 1fr);
}

.contents-skeleton {
  grid-template-columns: 1fr;
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
  border-radius: var(--radius-xl);
  height: 120px;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}


.contents-section {
  margin-bottom: var(--space-2xl);
}

.contents-list {
  display: flex;
  flex-direction: column;
  gap: var(--space-md);
}

.content-card {
  display: flex;
  align-items: center;
  gap: var(--space-lg);
  padding: var(--space-md);
  background: var(--color-bg-card);
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-color);
  transition: var(--transition-smooth);

  &:hover {
    transform: translateX(4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--color-accent);

    .content-card__actions {
      opacity: 1;
    }
  }

  &__cover {
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
      background: linear-gradient(135deg, var(--color-bg-secondary), var(--color-bg-card));
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--color-text-tertiary);

      svg {
        width: 30px;
        height: 30px;
      }
    }
  }

  &__info {
    flex: 1;
    min-width: 0;
  }

  &__title {
    font-size: var(--font-size-lg);
    font-weight: 600;
    color: var(--color-text-primary);
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  &__artist {
    color: var(--color-accent);
    font-size: var(--font-size-sm);
    margin-bottom: var(--space-xs);
  }

  &__meta {
    display: flex;
    gap: var(--space-xs);
    flex-wrap: wrap;

    span {
      padding: 2px 8px;
      border-radius: var(--radius-full);
      font-size: var(--font-size-xs);
    }

    .content-type {
      background: var(--color-accent);
      color: white;
    }

    .content-genre {
      background: var(--color-bg-secondary);
      color: var(--color-text-secondary);
    }
  }

  &__stats {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    margin-left: auto;
    padding: 0 var(--space-lg);
  }

  .stat-item {
    display: flex;
    align-items: center;
    gap: 4px;
    color: var(--color-text-secondary);
    font-size: var(--font-size-sm);

    svg {
      width: 18px;
      height: 18px;
      color: var(--color-accent);
    }
  }

  .content-status {
    padding: 4px 8px;
    border-radius: var(--radius-full);
    font-size: var(--font-size-xs);
    font-weight: 600;
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;

    &.status-deleted {
      background: rgba(239, 68, 68, 0.1);
      color: #ef4444;
    }
  }

  &__actions {
    display: flex;
    gap: var(--space-xs);
    opacity: 0.7;
    transition: opacity 0.2s ease;
  }
}

.action-btn {
  width: 36px;
  height: 36px;
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

  &.edit:hover {
    background: var(--color-accent);
    border-color: var(--color-accent);
    color: white;
  }

  &.view:hover {
    background: #10b981;
    border-color: #10b981;
    color: white;
  }

  svg {
    width: 18px;
    height: 18px;
  }
}


.quick-actions {
  margin-top: var(--space-2xl);
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: var(--space-lg);

  @media (max-width: 1024px) {
    grid-template-columns: repeat(2, 1fr);
  }

  @media (max-width: 480px) {
    grid-template-columns: 1fr;
  }
}

.action-card {
  display: block;
  padding: var(--space-xl);
  background: var(--color-bg-card);
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-color);
  text-decoration: none;
  transition: var(--transition-smooth);

  &:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--color-accent);

    .action-card__icon {
      transform: scale(1.1);
      color: var(--color-accent);
    }
  }

  &__icon {
    width: 48px;
    height: 48px;
    margin-bottom: var(--space-md);
    color: var(--color-text-secondary);
    transition: var(--transition-smooth);

    svg {
      width: 100%;
      height: 100%;
    }
  }

  &__title {
    font-size: var(--font-size-lg);
    font-weight: 600;
    color: var(--color-text-primary);
    margin-bottom: var(--space-xs);
  }

  &__text {
    color: var(--color-text-secondary);
    font-size: var(--font-size-sm);
    line-height: 1.6;
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


@media (max-width: 768px) {
  .admin-header {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--space-md);
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .stats-skeleton {
    grid-template-columns: 1fr;
  }

  .content-card {
    flex-wrap: wrap;

    &__stats {
      margin-left: 0;
      padding: var(--space-sm) 0;
      width: 100%;
      justify-content: space-around;
    }

    &__actions {
      width: 100%;
      justify-content: flex-end;
    }
  }

  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--space-sm);
  }
}
</style>