<template>
  <div>
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
          <div class="review-card__author-role">{{ getUserRoleLabel(review.user.role_id) }}</div>
        </div>
      </div>
      <div class="review-card__date">{{ formatReviewDate(review.created_at) }}</div>
    </div>

    <h3 class="review-card__title">{{ review.title }}</h3>
    <p class="review-card__text">{{ review.text }}</p>

    <div class="review-card__footer">
      <div class="review-card__likes">
        <div class="likes-breakdown">
          <template v-if="user">
            <template v-if="user.role_id === 3">
              <button
                v-if="canGiveAuthorLike"
                class="like-btn like-btn--author"
                :class="{ 'like-btn--active': hasUserLiked('author_like') }"
                @click.stop="toggleLike('author_like')"
                :disabled="isLikeLoading('author_like')"
                title="Авторский лайк"
              >
                <svg v-if="isLikeLoading('author_like')" class="spinner spinner--small" viewBox="0 0 50 50">
                  <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4"></circle>
                </svg>
                <svg v-else viewBox="0 0 20 20" fill="currentColor">
                  <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                </svg>
                <span>{{ review.likes?.breakdown?.author_like || 0 }}</span>
              </button>
              <div v-else class="likes-stats">
                <span v-if="review.likes?.breakdown?.author_like > 0" class="like-stat like-stat--author">
                  <svg viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                  </svg>
                  <span>{{ review.likes.breakdown.author_like }}</span>
                </span>
              </div>
            </template>

            <template v-else>
              <button
                class="like-btn"
                :class="{ 'like-btn--active': hasUserLiked('like') }"
                @click.stop="toggleLike('like')"
                :disabled="isLikeLoading('like')"
                title="Обычный лайк"
              >
                <svg v-if="isLikeLoading('like')" class="spinner spinner--small" viewBox="0 0 50 50">
                  <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4"></circle>
                </svg>
                <svg v-else viewBox="0 0 20 20" fill="currentColor">
                  <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
                </svg>
                <span>{{ review.likes?.breakdown?.like || 0 }}</span>
              </button>
            </template>
          </template>

          <div v-else class="likes-stats">
            <span v-if="review.likes?.breakdown?.like > 0" class="like-stat">
              <svg viewBox="0 0 20 20" fill="currentColor">
                <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
              </svg>
              <span>{{ review.likes.breakdown.like }}</span>
            </span>
            <span v-if="review.likes?.breakdown?.author_like > 0" class="like-stat like-stat--author">
              <svg viewBox="0 0 20 20" fill="currentColor">
                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
              </svg>
              <span>{{ review.likes.breakdown.author_like }}</span>
            </span>
          </div>
        </div>
        <span class="likes-total">Всего: {{ review.likes?.total || 0 }}</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  review: any
  content: any
  user: any
  likeLoading: Record<string, boolean>
}>()

const emit = defineEmits<{
  (e: 'toggleLike', payload: { reviewId: number, likeType: string }): void
}>()

const ASSETS_URL = 'https://critiqueassets.storage.yandexcloud.net/'

const getAvatarUrl = (avatarPath: string | null) => {
  if (!avatarPath) return ''
  if (avatarPath.startsWith('http')) {
    return avatarPath
  }
  const cleanPath = avatarPath.replace(/^storage\//, '')
  return `${ASSETS_URL}${cleanPath}`
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

const getUserRoleLabel = (roleId: number) => {
  return roleId === 3 ? 'Автор' : 'Слушатель'
}

const canGiveAuthorLike = computed(() => {
  return props.user?.id === props.content?.added_by
})

const hasUserLiked = (likeType: string) => {
  if (!props.user || !props.user.id) return false

  if (likeType === 'like') {
    return props.review.likes?.details?.regular_likes?.some(
      (like: any) => like.user_id === props.user.id
    ) || false
  } else {
    return props.review.likes?.details?.author_likes?.some(
      (like: any) => like.user_id === props.user.id
    ) || false
  }
}

const isLikeLoading = (likeType: string) => {
  return props.likeLoading[`${props.review.id}-${likeType}`] || false
}

const toggleLike = (likeType: string) => {
  emit('toggleLike', { reviewId: props.review.id, likeType })
}
</script>