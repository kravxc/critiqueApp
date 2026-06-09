<template>
  <div class="admin-reviews">
    <div class="admin-container">
      <header class="admin-header">
        <div class="admin-header__left">
          <h1 class="admin-title">Управление рецензиями</h1>
          <span class="admin-subtitle">Всего рецензий: {{ totalFound }}</span>
        </div>
        <div class="admin-header__right">
          <button
            class="admin-logout"
            @click="goToDashboard"
            title="На главную админки"
          >
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
              />
            </svg>
          </button>
        </div>
      </header>

      <div class="filters-section">
        <div class="search-bar">
          <svg
            class="search-bar__icon"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>
          <input
            type="text"
            v-model="filters.search"
            @input="debouncedSearch"
            placeholder="Поиск по заголовку или тексту рецензии..."
            class="search-bar__input"
          />
          <button
            v-if="filters.search"
            class="search-bar__clear"
            @click="clearSearch"
          >
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>

        <div class="filters-row">
          <div class="filter-group">
            <label class="filter-label">Поиск по автору</label>
            <input
              type="text"
              v-model="filters.userName"
              @input="debouncedSearch"
              placeholder="Имя автора..."
              class="filter-input"
            />
          </div>

          <div class="filter-group">
            <label class="filter-label">Статус</label>
            <select
              v-model="filters.trashed"
              @change="applyFilters"
              class="filter-select"
            >
              <option value="without">Только активные</option>
              <option value="with">Все (включая удаленные)</option>
              <option value="only">Только удаленные</option>
            </select>
          </div>

          <div class="filter-group">
            <label class="filter-label">Сортировка</label>
            <select
              v-model="filters.sortBy"
              @change="applyFilters"
              class="filter-select"
            >
              <option value="created_at">По дате создания</option>
              <option value="title">По заголовку</option>
              <option value="likes_count">По количеству лайков</option>
              <option value="updated_at">По дате обновления</option>
            </select>
          </div>

          <div class="filter-group">
            <label class="filter-label">Порядок</label>
            <select
              v-model="filters.sortOrder"
              @change="applyFilters"
              class="filter-select"
            >
              <option value="desc">По убыванию</option>
              <option value="asc">По возрастанию</option>
            </select>
          </div>

          <button
            class="btn btn--outline reset-btn"
            @click="resetFilters"
            :disabled="isFiltersDefault"
          >
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
              />
            </svg>
            Сбросить
          </button>
        </div>

        <div v-if="appliedFilters.length > 0" class="applied-filters">
          <span
            v-for="filter in appliedFilters"
            :key="filter.key"
            class="applied-filter"
          >
            {{ filter.label }}: {{ filter.value }}
            <button @click.stop="removeFilter(filter.key)">
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </span>
        </div>
      </div>

      <div v-if="stats" class="stats-summary">
        <div class="stat-chip">
          <span class="stat-chip__label">Найдено рецензий:</span>
          <span class="stat-chip__value">{{ stats.total_found }}</span>
        </div>
        <div class="stat-chip" v-if="stats.total_with_trashed !== null">
          <span class="stat-chip__label">Всего с удаленными:</span>
          <span class="stat-chip__value">{{ stats.total_with_trashed }}</span>
        </div>
        <div class="stat-chip" v-if="stats.total_only_trashed !== null">
          <span class="stat-chip__label">В корзине:</span>
          <span class="stat-chip__value stat-chip__value--danger">{{
            stats.total_only_trashed
          }}</span>
        </div>
      </div>

      <div class="table-section">
        <div v-if="isLoading" class="table-skeleton">
          <div v-for="i in perPage" :key="i" class="skeleton-row"></div>
        </div>

        <div v-else-if="reviews.length > 0" class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Заголовок</th>
                <th>Текст</th>
                <th>Автор</th>
                <th>Контент</th>
                <th>Дата создания</th>
                <th>Статус</th>
                <th>Действия</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="review in reviews"
                :key="review.id"
                :class="{ 'trashed-row': review.is_trashed }"
              >
                <td class="title-cell">
                  <div class="review-title">
                    {{ truncateText(review.title, 40) }}
                  </div>
                </td>
                <td class="text-cell">
                  <div class="review-text-preview">
                    {{ truncateText(review.text, 100) }}
                    <button
                      v-if="review.text && review.text.length > 100"
                      class="view-full-link"
                      @click="openReviewModal(review)"
                    >
                      посмотреть полностью
                    </button>
                  </div>
                </td>
                <td class="user-cell">
                  <div class="user-info">
                    <div class="user-avatar">
                      <img
                        v-if="review.user?.avatar_url"
                        :src="review.user.avatar_url"
                        :alt="review.user.name"
                      />
                      <div v-else class="user-avatar-placeholder">
                        {{ review.user?.name?.charAt(0)?.toUpperCase() || "?" }}
                      </div>
                    </div>
                    <span class="user-name">{{
                      review.user?.name || "Удален"
                    }}</span>
                  </div>
                </td>
                <td class="content-cell">
                  <div class="content-info">
                    <div
                      class="content-cover"
                      v-if="review.content?.cover_image_url"
                    >
                      <img
                        :src="review.content.cover_image_url"
                        :alt="review.content.title"
                      />
                    </div>
                    <div class="content-details">
                      <span class="content-title">{{
                        truncateText(review.content?.title || "—", 30)
                      }}</span>
                      <span class="content-artist">{{
                        review.content?.artist || ""
                      }}</span>
                    </div>
                  </div>
                </td>
                <td class="date-cell">{{ formatDate(review.created_at) }}</td>
                <td class="status-cell">
                  <span
                    class="status-badge"
                    :class="{
                      'status-active': !review.is_trashed,
                      'status-deleted': review.is_trashed,
                    }"
                  >
                    {{ review.is_trashed ? "Удалена" : "Активна" }}
                  </span>
                  <span
                    v-if="review.deleted_at"
                    class="delete-date"
                    :title="formatFullDate(review.deleted_at)"
                  >
                    {{ formatRelativeDate(review.deleted_at) }}
                  </span>
                </td>
                <td class="actions-cell">
                  <div class="action-buttons">
                    <button
                      class="action-btn view"
                      @click="openReviewModal(review)"
                      title="Просмотреть"
                    >
                      <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                        />
                      </svg>
                    </button>
                    <button
                      v-if="!review.is_trashed"
                      class="action-btn delete"
                      @click="confirmDelete(review)"
                      title="Мягкое удаление"
                    >
                      <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                        />
                      </svg>
                    </button>
                    <button
                      v-if="review.is_trashed"
                      class="action-btn restore"
                      @click="confirmRestore(review)"
                      title="Восстановить"
                    >
                      <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                        />
                      </svg>
                    </button>
                    <button
                      v-if="review.is_trashed"
                      class="action-btn force-delete"
                      @click="confirmForceDelete(review)"
                      title="Полное удаление"
                    >
                      <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                        />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="empty-state">
          <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"
            />
          </svg>
          <h3>Рецензии не найдены</h3>
          <p>Попробуйте изменить параметры поиска</p>
          <button class="btn btn--primary" @click="resetFilters">
            Сбросить все фильтры
          </button>
        </div>
      </div>

      <div v-if="totalPages > 1" class="pagination-section">
        <div class="pagination-info">
          Показано {{ (currentPage - 1) * perPage + 1 }} -
          {{ Math.min(currentPage * perPage, totalFound) }}
          из {{ totalFound }}
        </div>
        <div class="pagination-controls">
          <button
            class="pagination-btn"
            :disabled="currentPage === 1"
            @click="goToPage(currentPage - 1)"
          >
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15 19l-7-7 7-7"
              />
            </svg>
          </button>

          <div class="pagination-pages">
            <button
              v-for="page in visiblePages"
              :key="page"
              class="pagination-page"
              :class="{ 'pagination-page--active': page === currentPage }"
              @click="typeof page === 'number' && goToPage(page)"
              :disabled="page === '...'"
            >
              {{ page }}
            </button>
          </div>

          <button
            class="pagination-btn"
            :disabled="currentPage === totalPages"
            @click="goToPage(currentPage + 1)"
          >
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M9 5l7 7-7 7"
              />
            </svg>
          </button>
        </div>
        <div class="pagination-per-page">
          <select v-model="perPage" @change="resetPagination">
            <option :value="10">10 на странице</option>
            <option :value="25">25 на странице</option>
            <option :value="50">50 на странице</option>
            <option :value="100">100 на странице</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Просмотр рецензии -->
    <Teleport to="body">
      <div
        v-if="showReviewModal"
        class="modal-overlay"
        @click.self="closeReviewModal"
      >
        <div class="review-modal">
          <div class="review-modal__header">
            <h2 class="review-modal__title">Просмотр рецензии</h2>
            <button class="review-modal__close" @click="closeReviewModal">
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>

          <div class="review-modal__content">
            <div class="review-info-grid">
              <div class="review-info-card">
                <div class="review-info-card__header">
                  <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  <span>Информация</span>
                </div>
                <div class="review-info-card__body">
                  <div class="info-row">
                    <span class="info-label">Статус:</span>
                    <span
                      class="info-value"
                      :class="{
                        'status-deleted': selectedReview?.is_trashed,
                        'status-active': !selectedReview?.is_trashed,
                      }"
                    >
                      {{ selectedReview?.is_trashed ? "Удалена" : "Активна" }}
                    </span>
                  </div>
                  <div class="info-row">
                    <span class="info-label">Создана:</span>
                    <span class="info-value">{{
                      formatFullDate(selectedReview?.created_at)
                    }}</span>
                  </div>
                  <div
                    v-if="
                      selectedReview?.updated_at !== selectedReview?.created_at
                    "
                    class="info-row"
                  >
                    <span class="info-label">Обновлена:</span>
                    <span class="info-value">{{
                      formatFullDate(selectedReview?.updated_at)
                    }}</span>
                  </div>
                  <div v-if="selectedReview?.deleted_at" class="info-row">
                    <span class="info-label">Удалена:</span>
                    <span class="info-value">{{
                      formatFullDate(selectedReview?.deleted_at)
                    }}</span>
                  </div>
                </div>
              </div>

              <div class="review-info-card">
                <div class="review-info-card__header">
                  <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                    />
                  </svg>
                  <span>Лайки</span>
                </div>
                <div class="review-info-card__body">
                  <div class="likes-stats-modal">
                    <div class="like-stat">
                      <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="like-icon"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                        />
                      </svg>
                      <span class="like-label">Всего:</span>
                      <span class="like-value">{{
                        selectedReview?.likes_count || 0
                      }}</span>
                    </div>
                    <div class="like-stat">
                      <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="like-icon"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"
                        />
                      </svg>
                      <span class="like-label">Обычные:</span>
                      <span class="like-value">{{
                        selectedReview?.regular_likes_count || 0
                      }}</span>
                    </div>
                    <div class="like-stat">
                      <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="like-icon like-icon--author"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
                        />
                      </svg>
                      <span class="like-label">Авторские:</span>
                      <span class="like-value">{{
                        selectedReview?.author_likes_count || 0
                      }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="review-author-card">
              <div class="author-avatar">
                <img
                  v-if="selectedReview?.user?.avatar_url"
                  :src="selectedReview.user.avatar_url"
                  :alt="selectedReview.user.name"
                />
                <div v-else class="author-avatar-placeholder">
                  {{
                    selectedReview?.user?.name?.charAt(0)?.toUpperCase() || "?"
                  }}
                </div>
              </div>
              <div class="author-details">
                <h3 class="author-name">
                  {{ selectedReview?.user?.name || "Пользователь удален" }}
                </h3>
                <p class="author-email">
                  {{ selectedReview?.user?.email || "" }}
                </p>
              </div>
            </div>

            <div class="review-title-section">
              <h1 class="review-full-title">{{ selectedReview?.title }}</h1>
            </div>

            <div class="review-text-section">
              <div class="review-text-content">
                <p>{{ selectedReview?.text }}</p>
              </div>
            </div>

            <div v-if="selectedReview?.content" class="content-info-card">
              <div class="content-info-card__header">
                <svg
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M4 6h16M4 12h16M4 18h16"
                  />
                  <rect
                    x="4"
                    y="4"
                    width="16"
                    height="16"
                    rx="2"
                    stroke="currentColor"
                    fill="none"
                  />
                </svg>
                <span>О рецензируемом контенте</span>
              </div>
              <div class="content-info-card__body">
                <div
                  class="content-cover-large"
                  v-if="selectedReview.content.cover_image_url"
                >
                  <img
                    :src="selectedReview.content.cover_image_url"
                    :alt="selectedReview.content.title"
                  />
                </div>
                <div class="content-fields">
                  <div class="content-field">
                    <span class="field-label">Название:</span>
                    <span class="field-value">{{
                      selectedReview.content.title
                    }}</span>
                  </div>
                  <div class="content-field">
                    <span class="field-label">Исполнитель:</span>
                    <span class="field-value">{{
                      selectedReview.content.artist || "—"
                    }}</span>
                  </div>
                  <div class="content-field">
                    <span class="field-label">ID контента:</span>
                    <span class="field-value">{{
                      selectedReview.content.id
                    }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="review-modal__footer">
            <button class="btn btn--outline" @click="closeReviewModal">
              Закрыть
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <div
        v-if="showDeleteModal"
        class="modal-overlay"
        @click.self="closeModal"
      >
        <div class="modal-container modal-container--small">
          <div
            class="modal-header"
            :class="{
              'modal-header--danger':
                modalAction === 'delete' || modalAction === 'force-delete',
              'modal-header--warning': modalAction === 'restore',
            }"
          >
            <h2 class="modal-title">{{ modalTitle }}</h2>
            <button class="modal-close" @click="closeModal">
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>

          <div class="modal-body">
            <div
              class="modal-icon"
              :class="{
                'modal-icon--danger':
                  modalAction === 'delete' || modalAction === 'force-delete',
                'modal-icon--warning': modalAction === 'restore',
              }"
            >
              <svg
                v-if="modalAction === 'delete'"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                />
              </svg>
              <svg
                v-else-if="modalAction === 'restore'"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                />
              </svg>
              <svg
                v-else-if="modalAction === 'force-delete'"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                />
              </svg>
            </div>

            <p class="modal-text">{{ modalMessage }}</p>

            <div v-if="pendingReview" class="review-preview">
              <div class="review-preview__title">
                {{ truncateText(pendingReview.title, 60) }}
              </div>
              <div class="review-preview__author">
                Автор: {{ pendingReview.user?.name || "Пользователь удален" }}
              </div>
            </div>
          </div>

          <div
            class="modal-actions"
            :class="{
              'modal-actions--danger':
                modalAction === 'delete' || modalAction === 'force-delete',
              'modal-actions--warning': modalAction === 'restore',
            }"
          >
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
                'btn--danger':
                  modalAction === 'delete' || modalAction === 'force-delete',
                'btn--warning': modalAction === 'restore',
              }"
              @click="executeAction"
              :disabled="modalLoading"
            >
              <svg v-if="modalLoading" class="spinner" viewBox="0 0 50 50">
                <circle
                  cx="25"
                  cy="25"
                  r="20"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="4"
                ></circle>
              </svg>
              <span v-else>{{ modalButtonText }}</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
  <ThemeToggle />
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, inject } from "vue";
import { useRouter } from "vue-router";
import { useUserStore } from "@/stores/user";
import Cookies from "js-cookie";
import ThemeToggle from "@/components/ThemeToggle.vue";

interface Content {
  id: number;
  title: string;
  artist: string;
  cover_image_url: string | null;
}

interface User {
  id: number;
  name: string;
  email?: string;
  avatar_url: string | null;
}

interface Review {
  id: number;
  title: string;
  text: string;
  content: Content | null;
  user: User | null;
  likes_count: number;
  regular_likes_count: number;
  author_likes_count: number;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  is_trashed: boolean;
}

interface SearchResponse {
  success: boolean;
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    stats: {
      total_found: number;
      total_with_trashed: number | null;
      total_only_trashed: number | null;
    };
    filters_applied: any;
  };
  data: Review[];
}

interface Filters {
  search: string;
  userName: string;
  contentId: string;
  trashed: "with" | "without" | "only";
  sortBy: string;
  sortOrder: string;
}

const router = useRouter();
const userStore = useUserStore();
const toast = inject("toast") as {
  success: (message: string, timeout?: number) => void;
  error: (message: string, timeout?: number) => void;
  warning: (message: string, timeout?: number) => void;
  info: (message: string, timeout?: number) => void;
};

const isLoading = ref(false);
const reviews = ref<Review[]>([]);
const totalFound = ref(0);
const currentPage = ref(1);
const perPage = ref(25);
const totalPages = ref(1);
const stats = ref<SearchResponse["meta"]["stats"] | null>(null);

const filters = reactive<Filters>({
  search: "",
  userName: "",
  contentId: "",
  trashed: "without",
  sortBy: "created_at",
  sortOrder: "desc",
});

const showReviewModal = ref(false);
const selectedReview = ref<Review | null>(null);

const showDeleteModal = ref(false);
const modalLoading = ref(false);
const modalAction = ref<"delete" | "restore" | "force-delete" | null>(null);
const pendingReview = ref<Review | null>(null);

const visiblePages = computed(() => {
  const delta = 2;
  const range: number[] = [];
  const rangeWithDots: (number | string)[] = [];
  let l: number | undefined;

  for (let i = 1; i <= totalPages.value; i++) {
    if (
      i === 1 ||
      i === totalPages.value ||
      (i >= currentPage.value - delta && i <= currentPage.value + delta)
    ) {
      range.push(i);
    }
  }

  range.forEach((i) => {
    if (l) {
      if (i - l === 2) {
        rangeWithDots.push(l + 1);
      } else if (i - l !== 1) {
        rangeWithDots.push("...");
      }
    }
    rangeWithDots.push(i);
    l = i;
  });

  return rangeWithDots;
});

const appliedFilters = computed(() => {
  const applied: { key: string; label: string; value: string }[] = [];
  if (filters.search) {
    applied.push({ key: "search", label: "Поиск", value: filters.search });
  }
  if (filters.userName) {
    applied.push({ key: "userName", label: "Автор", value: filters.userName });
  }
  if (filters.contentId) {
    applied.push({
      key: "contentId",
      label: "Контент ID",
      value: filters.contentId,
    });
  }
  if (filters.trashed !== "without") {
    applied.push({
      key: "trashed",
      label: "Статус",
      value: filters.trashed === "with" ? "Все" : "Только удаленные",
    });
  }
  return applied;
});

const isFiltersDefault = computed(() => {
  return (
    !filters.search &&
    !filters.userName &&
    !filters.contentId &&
    filters.trashed === "without" &&
    filters.sortBy === "created_at" &&
    filters.sortOrder === "desc"
  );
});

const modalTitle = computed(() => {
  switch (modalAction.value) {
    case "delete":
      return "Подтверждение удаления";
    case "restore":
      return "Подтверждение восстановления";
    case "force-delete":
      return "Подтверждение полного удаления";
    default:
      return "";
  }
});

const modalMessage = computed(() => {
  switch (modalAction.value) {
    case "delete":
      return "Вы уверены, что хотите мягко удалить эту рецензию? Она будет скрыта с сайта, но её можно будет восстановить.";
    case "restore":
      return "Вы уверены, что хотите восстановить эту рецензию? Она снова будет отображаться на сайте.";
    case "force-delete":
      return "Вы уверены, что хотите полностью удалить эту рецензию? Это действие необратимо!";
    default:
      return "";
  }
});

const modalButtonText = computed(() => {
  switch (modalAction.value) {
    case "delete":
      return "Удалить";
    case "restore":
      return "Восстановить";
    case "force-delete":
      return "Удалить навсегда";
    default:
      return "";
  }
});

let searchTimeout: ReturnType<typeof setTimeout>;

const API_URL = import.meta.env.VITE_API_URL + '/api' || 'http://localhost:8000/api'

const getToken = (): string | null => {
  return Cookies.get("auth_token") || null;
};

const formatDate = (dateString: string) => {
  if (!dateString) return "—";
  const date = new Date(dateString);
  return date.toLocaleDateString("ru-RU", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
};

const formatFullDate = (dateString: string) => {
  if (!dateString) return "—";
  return new Date(dateString).toLocaleDateString("ru-RU", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const formatRelativeDate = (dateString: string) => {
  const date = new Date(dateString);
  const now = new Date();
  const diffTime = Math.abs(now.getTime() - date.getTime());
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

  if (diffDays === 1) return "сегодня";
  if (diffDays < 7) return `${diffDays} дн. назад`;
  if (diffDays < 30) return `${Math.floor(diffDays / 7)} нед. назад`;
  return formatDate(dateString);
};

const truncateText = (text: string, maxLength: number) => {
  if (!text) return "";
  if (text.length <= maxLength) return text;
  return text.slice(0, maxLength) + "...";
};

const clearSearch = () => {
  filters.search = "";
  applyFilters();
};

const removeFilter = (key: string) => {
  switch (key) {
    case "search":
      filters.search = "";
      break;
    case "userName":
      filters.userName = "";
      break;
    case "contentId":
      filters.contentId = "";
      break;
    case "trashed":
      filters.trashed = "without";
      break;
  }
  applyFilters();
};

const searchReviews = async () => {
  const token = getToken();
  if (!token) {
    router.push("/login");
    return;
  }

  isLoading.value = true;

  try {
    const body: any = {
      sort_by: filters.sortBy,
      sort_order: filters.sortOrder,
      trashed: filters.trashed,
      per_page: perPage.value,
      page: currentPage.value,
    };

    if (filters.search) body.search = filters.search;
    if (filters.userName) body.user_name = filters.userName;
    if (filters.contentId) body.content_id = parseInt(filters.contentId);

    const response = await fetch(`${API_URL}/reviews/search`, {
      method: "POST",
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify(body),
    });

    if (response.status === 401) {
      handleUnauthorized();
      return;
    }

    const data: SearchResponse = await response.json();

    if (response.ok && data.success) {
      reviews.value = data.data;
      totalFound.value = data.meta.total;
      totalPages.value = data.meta.last_page;
      currentPage.value = data.meta.current_page;
      stats.value = data.meta.stats;
    } else {
      console.error("Error searching reviews:", data);
      toast.error("Ошибка при поиске рецензий");
      reviews.value = [];
      totalFound.value = 0;
    }
  } catch (error) {
    console.error("Error searching reviews:", error);
    toast.error("Произошла ошибка при поиске");
    reviews.value = [];
    totalFound.value = 0;
  } finally {
    isLoading.value = false;
  }
};

const handleUnauthorized = () => {
  Cookies.remove("auth_token");
  Cookies.remove("user_data");
  userStore.clearUser();
  router.push("/login");
  toast.error("Сессия истекла. Пожалуйста, войдите снова.");
};

const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    currentPage.value = 1;
    applyFilters();
  }, 500);
};

const applyFilters = () => {
  searchReviews();
};

const resetFilters = () => {
  filters.search = "";
  filters.userName = "";
  filters.contentId = "";
  filters.trashed = "without";
  filters.sortBy = "created_at";
  filters.sortOrder = "desc";
  currentPage.value = 1;
  searchReviews();
};

const resetPagination = () => {
  currentPage.value = 1;
  searchReviews();
};

const goToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    searchReviews();
    window.scrollTo({ top: 0, behavior: "smooth" });
  }
};

const goToDashboard = () => {
  router.push("/admin");
};

// Методы для работы с модальными окнами
const openReviewModal = (review: Review) => {
  selectedReview.value = review;
  showReviewModal.value = true;
  document.body.style.overflow = "hidden";
};

const closeReviewModal = () => {
  showReviewModal.value = false;
  selectedReview.value = null;
  document.body.style.overflow = "";
};

const confirmDelete = (review: Review) => {
  pendingReview.value = review;
  modalAction.value = "delete";
  showDeleteModal.value = true;
  document.body.style.overflow = "hidden";
};

const confirmRestore = (review: Review) => {
  pendingReview.value = review;
  modalAction.value = "restore";
  showDeleteModal.value = true;
  document.body.style.overflow = "hidden";
};

const confirmForceDelete = (review: Review) => {
  pendingReview.value = review;
  modalAction.value = "force-delete";
  showDeleteModal.value = true;
  document.body.style.overflow = "hidden";
};

// Методы для кнопок в модальном окне просмотра
const confirmRestoreFromModal = () => {
  if (selectedReview.value) {
    const reviewToRestore = selectedReview.value;
    closeReviewModal();
    setTimeout(() => {
      confirmRestore(reviewToRestore);
    }, 200);
  }
};

const confirmForceDeleteFromModal = () => {
  if (selectedReview.value) {
    const reviewToDelete = selectedReview.value;
    closeReviewModal();
    setTimeout(() => {
      confirmForceDelete(reviewToDelete);
    }, 200);
  }
};

const closeModal = () => {
  showDeleteModal.value = false;
  pendingReview.value = null;
  modalAction.value = null;
  document.body.style.overflow = "";
};

const executeAction = async () => {
  if (!pendingReview.value || !modalAction.value) return;

  const token = getToken();
  if (!token) {
    router.push("/login");
    return;
  }

  if (modalAction.value === "delete" && pendingReview.value.is_trashed) {
    toast.warning("Эта рецензия уже удалена");
    closeModal();
    return;
  }

  modalLoading.value = true;

  try {
    let url = "";
    let method = "";
    let successMessage = "";

    if (modalAction.value === "delete") {
      url = `${API_URL}/delete-review/${pendingReview.value.id}`;
      method = "DELETE";
      successMessage = "Рецензия успешно удалена";
    } else if (modalAction.value === "restore") {
      url = `${API_URL}/reviews/${pendingReview.value.id}/restore`;
      method = "POST";
      successMessage = "Рецензия успешно восстановлена";
    } else if (modalAction.value === "force-delete") {
      url = `${API_URL}/reviews/${pendingReview.value.id}/force`;
      method = "DELETE";
      successMessage = "Рецензия полностью удалена из системы";
    }

    const response = await fetch(url, {
      method,
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
        "Content-Type": "application/json",
      },
    });

    if (response.status === 401) {
      handleUnauthorized();
      return;
    }

    if (response.status === 403) {
      toast.error("У вас нет прав для выполнения этого действия");
      closeModal();
      return;
    }

    if (response.status === 404) {
      toast.error("Рецензия не найдена");
      closeModal();
      return;
    }

    if (response.status === 400) {
      const data = await response.json();
      if (data.message === "Review is already deleted") {
        toast.warning("Рецензия уже была удалена");
      } else {
        toast.error(data.message || "Ошибка при выполнении операции");
      }
      closeModal();
      await searchReviews();
      return;
    }

    if (response.ok) {
      toast.success(successMessage);
      closeModal();
      await searchReviews();
    } else {
      const data = await response.json();
      toast.error(
        data.message || data.error?.message || "Ошибка при выполнении операции",
      );
      closeModal();
    }
  } catch (error) {
    console.error("Error executing action:", error);
    toast.error("Произошла ошибка при выполнении операции");
    closeModal();
  } finally {
    modalLoading.value = false;
  }
};

onMounted(() => {
  searchReviews();
});
</script>

<style lang="scss" scoped>
.admin-reviews {
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
    padding: var(--space-md) var(--space-xl) var(--space-md)
      calc(var(--space-md) * 2 + 20px);
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
  grid-template-columns: repeat(5, 1fr) auto;
  gap: var(--space-md);
  margin-bottom: var(--space-lg);
  align-items: flex-end;

  @media (max-width: 1200px) {
    grid-template-columns: repeat(3, 1fr) auto;
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

.filter-select,
.filter-input {
  width: 100%;
  padding: var(--space-sm) var(--space-md);
  background: var(--color-bg-secondary);
  border: 2px solid var(--border-color);
  border-radius: var(--radius-md);
  font-size: var(--font-size-md);
  color: var(--color-text-primary);
  cursor: pointer;
  transition: var(--transition-base);
}

.filter-select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M6 9l6 6 6-6'%3E%3C/path%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right var(--space-md) center;
}

.filter-input {
  cursor: text;
}

.filter-select:focus,
.filter-input:focus {
  outline: none;
  border-color: var(--color-accent);
  box-shadow: 0 0 0 3px rgba(var(--color-accent-rgb), 0.1);
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

.stats-summary {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-sm);
  margin-bottom: var(--space-xl);
}

.stat-chip {
  padding: var(--space-xs) var(--space-md);
  background: var(--color-bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-full);
  font-size: var(--font-size-sm);

  &__label {
    color: var(--color-text-secondary);
  }

  &__value {
    font-weight: 600;
    color: var(--color-text-primary);
    margin-left: 4px;

    &--danger {
      color: var(--color-error);
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
    vertical-align: middle;
  }

  tr:last-child td {
    border-bottom: none;
  }

  tr:hover td {
    background: rgba(var(--color-accent-rgb), 0.05);
  }

  .trashed-row {
    background: rgba(239, 68, 68, 0.05);

    &:hover td {
      background: rgba(239, 68, 68, 0.1);
    }
  }
}

.title-cell {
  min-width: 200px;

  .review-title {
    font-weight: 500;
  }
}

.text-cell {
  min-width: 300px;
  max-width: 400px;

  .review-text-preview {
    color: var(--color-text-secondary);
    line-height: 1.5;
  }

  .view-full-link {
    display: inline-block;
    margin-left: var(--space-xs);
    color: var(--color-accent);
    background: none;
    border: none;
    font-size: var(--font-size-xs);
    cursor: pointer;
    text-decoration: underline;

    &:hover {
      color: var(--color-accent-dark);
    }
  }
}

.user-cell {
  min-width: 150px;

  .user-info {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
  }

  .user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    overflow: hidden;
    background: linear-gradient(
      135deg,
      var(--color-accent),
      var(--color-accent-light)
    );
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
      font-size: var(--font-size-sm);
    }
  }

  .user-name {
    font-weight: 500;
  }
}

.content-cell {
  min-width: 180px;

  .content-info {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
  }

  .content-cover {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-md);
    overflow: hidden;
    flex-shrink: 0;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
  }

  .content-details {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .content-title {
    font-weight: 500;
    font-size: var(--font-size-sm);
  }

  .content-artist {
    font-size: var(--font-size-xs);
    color: var(--color-text-tertiary);
  }
}

.date-cell {
  white-space: nowrap;
  font-size: var(--font-size-xs);
  color: var(--color-text-secondary);
}

.status-cell {
  min-width: 100px;
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
  min-width: 120px;
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

  &.view:hover {
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
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
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

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
  backdrop-filter: blur(4px);
}

.review-modal {
  position: relative;
  width: 90vw;
  max-width: 900px;
  max-height: 85vh;
  background: var(--color-bg-card);
  border-radius: 24px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: modal-appear 0.3s cubic-bezier(0.34, 1.2, 0.64, 1);

  &__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    background: linear-gradient(
      135deg,
      var(--color-bg-card) 0%,
      var(--color-bg-secondary) 100%
    );
    border-bottom: 2px solid var(--color-accent);
    flex-shrink: 0;
  }

  &__title {
    font-size: 1.5rem;
    font-weight: 700;
    background: linear-gradient(
      135deg,
      var(--color-text-primary),
      var(--color-accent)
    );
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin: 0;
  }

  &__close {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--color-bg-secondary);
    border: 1px solid var(--border-color);
    color: var(--color-text-secondary);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;

    &:hover {
      background: rgba(239, 68, 68, 0.1);
      color: #ef4444;
      border-color: #ef4444;
      transform: rotate(90deg);
    }

    svg {
      width: 20px;
      height: 20px;
    }
  }

  &__content {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  &__footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 16px 24px;
    background: var(--color-bg-secondary);
    border-top: 1px solid var(--border-color);
    flex-shrink: 0;

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 20px;
      border-radius: 12px;
      font-weight: 600;
      font-size: 0.875rem;
      transition: all 0.2s ease;

      svg {
        width: 18px;
        height: 18px;
      }

      &--outline {
        background: transparent;
        border: 1px solid var(--border-color);
        color: var(--color-text-primary);

        &:hover {
          background: var(--color-bg-card);
          border-color: var(--color-accent);
        }
      }

      &--danger {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        border: none;
        color: white;

        &:hover {
          transform: translateY(-2px);
          box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
        }
      }

      &--warning {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        border: none;
        color: white;

        &:hover {
          transform: translateY(-2px);
          box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
        }
      }
    }
  }
}

.review-info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.review-info-card {
  background: var(--color-bg-secondary);
  border-radius: 16px;
  border: 1px solid var(--border-color);
  overflow: hidden;

  &__header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    background: rgba(var(--color-accent-rgb), 0.05);
    border-bottom: 1px solid var(--border-color);
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--color-text-primary);

    svg {
      width: 18px;
      height: 18px;
      color: var(--color-accent);
    }
  }

  &__body {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.875rem;

  .info-label {
    color: var(--color-text-secondary);
    font-weight: 500;
  }

  .info-value {
    color: var(--color-text-primary);
    font-weight: 500;

    &.status-active {
      color: #10b981;
    }

    &.status-deleted {
      color: #ef4444;
    }
  }
}

.likes-stats-modal {
  display: flex;
  justify-content: space-around;
  gap: 12px;
}

.like-stat {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  background: var(--color-bg-card);
  border-radius: 12px;
  flex: 1;
  justify-content: center;

  .like-icon {
    width: 18px;
    height: 18px;
  }

  .like-label {
    font-size: 0.75rem;
    color: var(--color-text-secondary);
  }

  .like-value {
    font-weight: 700;
    font-size: 1rem;
    color: var(--color-text-primary);
  }
}

.review-author-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  background: linear-gradient(
    135deg,
    var(--color-bg-secondary),
    rgba(var(--color-accent-rgb), 0.05)
  );
  border-radius: 20px;
  border: 1px solid var(--border-color);

  .author-avatar {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    overflow: hidden;
    background: linear-gradient(
      135deg,
      var(--color-accent),
      var(--color-accent-light)
    );
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);

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
      font-weight: 700;
      font-size: 1.75rem;
    }
  }

  .author-details {
    flex: 1;
  }

  .author-name {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0 0 4px 0;
    color: var(--color-text-primary);
  }

  .author-email {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
    margin: 0;
  }
}

.review-title-section {
  padding: 16px 0 8px 0;
  border-bottom: 2px solid var(--color-accent);

  .review-full-title {
    font-size: 1.75rem;
    font-weight: 800;
    line-height: 1.3;
    margin: 0;
    color: var(--color-text-primary);
  }
}

.review-text-section {
  .review-text-content {
    background: var(--color-bg-secondary);
    border-radius: 16px;
    padding: 24px;
    border: 1px solid var(--border-color);

    p {
      margin: 0;
      line-height: 1.8;
      font-size: 1rem;
      color: var(--color-text-primary);
      white-space: pre-wrap;
      word-break: break-word;
    }
  }
}

.content-info-card {
  background: var(--color-bg-secondary);
  border-radius: 16px;
  border: 1px solid var(--border-color);
  overflow: hidden;

  &__header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    background: rgba(var(--color-accent-rgb), 0.05);
    border-bottom: 1px solid var(--border-color);
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--color-text-primary);

    svg {
      width: 18px;
      height: 18px;
      color: var(--color-accent);
    }
  }

  &__body {
    padding: 20px;
    display: flex;
    gap: 20px;

    @media (max-width: 640px) {
      flex-direction: column;
      align-items: center;
      text-align: center;
    }
  }
}

.content-cover-large {
  width: 120px;
  height: 120px;
  border-radius: 12px;
  overflow: hidden;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
}

.content-fields {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.content-field {
  display: flex;
  gap: 12px;

  @media (max-width: 640px) {
    flex-direction: column;
    gap: 4px;
  }

  .field-label {
    min-width: 100px;
    font-weight: 600;
    color: var(--color-text-secondary);
    font-size: 0.875rem;
  }

  .field-value {
    color: var(--color-text-primary);
    font-size: 0.875rem;
    font-weight: 500;
  }
}

.modal-container {
  background: var(--color-bg-card);
  border-radius: 24px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  animation: modal-appear 0.3s ease;

  &--small {
    max-width: 450px;
  }
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid var(--border-color);
  position: sticky;
  top: 0;
  background: var(--color-bg-card);
  z-index: 10;

  &--danger {
    border-bottom-color: #ef4444;
    background: linear-gradient(to right, rgba(239, 68, 68, 0.05), transparent);
  }

  &--warning {
    border-bottom-color: #f59e0b;
    background: linear-gradient(
      to right,
      rgba(245, 158, 11, 0.05),
      transparent
    );
  }
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--color-text-primary);
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  color: var(--color-text-secondary);
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;

  svg {
    width: 20px;
    height: 20px;
  }

  &:hover {
    background: var(--color-bg-secondary);
    color: #ef4444;
  }
}

.modal-body {
  padding: 24px;
}

.modal-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 20px;
  display: flex;
  align-items: center;
  justify-content: center;

  svg {
    width: 100%;
    height: 100%;
  }

  &--danger {
    color: #ef4444;
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
  font-size: 1rem;
  color: var(--color-text-primary);
  margin-bottom: 20px;
  line-height: 1.6;
}

.review-preview {
  margin-top: 20px;
  padding: 16px;
  background: var(--color-bg-secondary);
  border-radius: 12px;
  border: 1px solid var(--border-color);

  &__title {
    font-weight: 600;
    margin-bottom: 8px;
    color: var(--color-text-primary);
  }

  &__author {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
  }
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 16px 24px;
  border-top: 1px solid var(--border-color);
  position: sticky;
  bottom: 0;
  background: var(--color-bg-card);

  &--danger {
    border-top-color: #ef4444;
    background: linear-gradient(
      to bottom,
      transparent,
      rgba(239, 68, 68, 0.05)
    );
  }

  &--warning {
    border-top-color: #f59e0b;
    background: linear-gradient(
      to bottom,
      transparent,
      rgba(245, 158, 11, 0.05)
    );
  }
}

.btn {
  padding: 10px 20px;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1px solid transparent;
  display: inline-flex;
  align-items: center;
  gap: 8px;

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
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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
    background: #ef4444;
    color: white;

    &:hover:not(:disabled) {
      background: #dc2626;
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
    }
  }

  &--warning {
    background: #f59e0b;
    color: white;

    &:hover:not(:disabled) {
      background: #d97706;
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
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
  to {
    transform: rotate(360deg);
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

@media (max-width: 768px) {
  .admin-reviews {
    padding: var(--space-lg) 0;
  }

  .admin-container {
    padding: 0 var(--space-md);
  }

  .admin-header {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--space-md);
  }

  .filters-section {
    padding: var(--space-lg);
  }

  .stats-summary {
    gap: var(--space-xs);
  }

  .stat-chip {
    font-size: var(--font-size-xs);
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

  .review-modal {
    width: 95vw;
    max-height: 90vh;
  }

  .review-info-grid {
    grid-template-columns: 1fr;
  }

  .review-modal__content {
    padding: 16px;
    gap: 16px;
  }

  .review-full-title {
    font-size: 1.25rem;
  }

  .review-text-content {
    padding: 16px;
  }

  .review-author-card {
    padding: 16px;
  }

  .author-avatar {
    width: 50px;
    height: 50px;
  }

  .author-name {
    font-size: 1rem;
  }

  .content-cover-large {
    width: 80px;
    height: 80px;
  }

  .modal-container {
    margin: 16px;
  }
}

.likes-stats-modal {
  display: flex;
  gap: var(--space-md);
  flex-wrap: wrap;
}

.like-stat {
  display: flex;
  align-items: center;
  gap: var(--space-xs);
  padding: var(--space-xs) var(--space-sm);
  background: var(--color-bg-secondary);
  border-radius: var(--radius-full);
  border: 1px solid var(--border-color);

  .like-icon {
    width: 18px;
    height: 18px;
    color: var(--color-text-secondary);

    &--author {
      color: var(--color-accent);
    }
  }

  .like-label {
    font-size: var(--font-size-xs);
    color: var(--color-text-tertiary);
  }

  .like-value {
    font-weight: 600;
    color: var(--color-text-primary);
    font-size: var(--font-size-sm);
  }
}
</style>
