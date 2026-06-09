<template>
  <div class="review-card__footer">
    <div class="review-card__likes">
      <div class="likes-breakdown">
        <div class="like-wrapper">
          <button
            v-if="user && user.role_id !== 3"
            class="like-btn like-btn--regular"
            :class="{ 'like-btn--active': hasUserLiked('like') }"
            @click.stop="$emit('toggle-like', review.id, 'like')"
            :disabled="isLikeLoading('like')"
            title="Обычный лайк"
          >
            <svg
              v-if="isLikeLoading('like')"
              class="spinner spinner--small"
              viewBox="0 0 50 50"
            >
              <circle
                cx="25"
                cy="25"
                r="20"
                fill="none"
                stroke="currentColor"
                stroke-width="4"
              ></circle>
            </svg>
            <svg v-else viewBox="0 0 20 20" fill="currentColor">
              <path
                d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"
              />
            </svg>
            <span>{{ review.likes?.breakdown?.like || 0 }}</span>
          </button>
          <div v-else class="like-stat like-stat--regular">
            <svg viewBox="0 0 20 20" fill="currentColor">
              <path
                d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"
              />
            </svg>
            <span>{{ review.likes?.breakdown?.like || 0 }}</span>
          </div>
        </div>

        <div class="like-wrapper">
          <button
            v-if="user && user.role_id === 3"
            class="like-btn like-btn--author"
            :class="{ 'like-btn--active': hasUserLiked('author_like') }"
            @click.stop="$emit('toggle-like', review.id, 'author_like')"
            :disabled="isLikeLoading('author_like')"
            title="Авторский лайк"
          >
            <svg
              v-if="isLikeLoading('author_like')"
              class="spinner spinner--small"
              viewBox="0 0 50 50"
            >
              <circle
                cx="25"
                cy="25"
                r="20"
                fill="none"
                stroke="currentColor"
                stroke-width="4"
              ></circle>
            </svg>
            <svg v-else viewBox="0 0 20 20" fill="currentColor">
              <path
                d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"
              />
            </svg>
            <span>{{ review.likes?.breakdown?.author_like || 0 }}</span>
          </button>
          <div v-else class="like-stat like-stat--author">
            <svg viewBox="0 0 20 20" fill="currentColor">
              <path
                d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"
              />
            </svg>
            <span>{{ review.likes?.breakdown?.author_like || 0 }}</span>
          </div>
        </div>
      </div>
      <span class="likes-total">Всего: {{ review.likes?.total || 0 }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
const props = defineProps<{
  review: any;
  content: any;
  user: any;
  likeLoading: Record<string, boolean>;
}>();

defineEmits<{
  (e: "toggle-like", reviewId: number, likeType: string): void;
}>();

const hasUserLiked = (likeType: string) => {
  if (!props.user || !props.user.id) return false;

  if (likeType === "like") {
    return (
      props.review.likes?.details?.regular_likes?.some(
        (like: any) => like.user_id === props.user.id,
      ) || false
    );
  } else {
    return (
      props.review.likes?.details?.author_likes?.some(
        (like: any) => like.user_id === props.user.id,
      ) || false
    );
  }
};

const isLikeLoading = (likeType: string) => {
  return props.likeLoading[`${props.review.id}-${likeType}`] || false;
};
</script>

<style scoped>
.review-card__footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  border-top: 1px solid #e5e7eb;
  padding-top: 1rem;
  margin-top: 1rem;
}

.review-card__likes {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.5rem;
}

.likes-breakdown {
  display: flex;
  align-items: center;
  gap: 1rem;
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
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 9999px;
  background: #ffffff;
  color: #4b5563;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
  line-height: 1;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.like-btn svg {
  width: 18px;
  height: 18px;
}

.like-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.like-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.like-btn--regular {
  border-color: #3b82f6;
  color: #3b82f6;
}

.like-btn--regular:hover:not(:disabled) {
  border-color: #2563eb;
  color: #2563eb;
}

.like-btn--regular.like-btn--active {
  background: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

.like-btn--regular.like-btn--active:hover:not(:disabled) {
  background: #2563eb;
  border-color: #2563eb;
}

.like-btn--author {
  border-color: #f59e0b;
  color: #f59e0b;
}

.like-btn--author:hover:not(:disabled) {
  border-color: #d97706;
  color: #d97706;
}

.like-btn--author.like-btn--active {
  background: #f59e0b;
  border-color: #f59e0b;
  color: white;
}

.like-btn--author.like-btn--active:hover:not(:disabled) {
  background: #d97706;
  border-color: #d97706;
}

.like-stat {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 9999px;
  line-height: 1;
  color: #6b7280;
}

.like-stat svg {
  width: 18px;
  height: 18px;
}

.like-stat--regular svg {
  color: #3b82f6;
}

.like-stat--regular {
  color: #6b7280;
}

.like-stat--author svg {
  color: #f59e0b;
}

.like-stat--author {
  color: #6b7280;
}

.likes-total {
  font-size: 0.75rem;
  color: #9ca3af;
}

.spinner {
  display: inline-block;
  width: 18px;
  height: 18px;
  border: 2px solid rgba(59, 130, 246, 0.2);
  border-radius: 50%;
  border-top-color: #3b82f6;
  animation: spin 0.8s linear infinite;
}

.spinner--small {
  width: 16px;
  height: 16px;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (prefers-color-scheme: dark) {
  .review-card__footer {
    border-top-color: #374151;
  }

  .like-btn {
    background: #1f2937;
    border-color: #4b5563;
    color: #d1d5db;
  }

  .like-btn--regular {
    border-color: #60a5fa;
    color: #60a5fa;
  }

  .like-btn--regular:hover:not(:disabled) {
    border-color: #93c5fd;
    color: #93c5fd;
  }

  .like-btn--regular.like-btn--active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
  }

  .like-btn--author {
    border-color: #fbbf24;
    color: #fbbf24;
  }

  .like-btn--author:hover:not(:disabled) {
    border-color: #fcd34d;
    color: #fcd34d;
  }

  .like-btn--author.like-btn--active {
    background: #f59e0b;
    border-color: #f59e0b;
    color: white;
  }

  .like-stat {
    background: #1f2937;
    border-color: #374151;
    color: #9ca3af;
  }

  .like-stat--regular svg {
    color: #60a5fa;
  }

  .like-stat--author svg {
    color: #fbbf24;
  }

  .likes-total {
    color: #6b7280;
  }

  .spinner {
    border-color: rgba(96, 165, 250, 0.2);
    border-top-color: #60a5fa;
  }
}

@media (max-width: 768px) {
  .likes-breakdown {
    flex-direction: column;
    align-items: flex-end;
    gap: 0.5rem;
  }

  .like-btn,
  .like-stat {
    padding: 0.375rem 0.625rem;
    font-size: 0.75rem;
  }

  .like-btn svg,
  .like-stat svg {
    width: 16px;
    height: 16px;
  }
}

.light-theme .like-stat {
  background: #f3f4f6;
  border-color: #e5e7eb;
  color: #4b5563;
}

.light-theme .like-stat--regular {
  background: #fef2f2;
  border-color: #3b82f6;

  svg {
    color: #3b82f6;
  }
}

.light-theme .like-stat--author {
  background: #fffbeb;
  border-color: #fde68a;

  svg {
    color: #f59e0b;
  }
}

.light-theme .like-btn {
  background: #ffffff;
  border-color: #e5e7eb;
  color: #374151;
}

.light-theme .like-btn--active {
  background: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

.light-theme .like-btn--author--active {
  background: #f59e0b;
  border-color: #f59e0b;
}
</style>
