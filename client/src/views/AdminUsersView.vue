<template>
  <div class="admin-users">
    <div class="admin-container">
      <header class="admin-header">
        <div class="admin-header__left">
          <h1 class="admin-title">Управление пользователями</h1>
          <span class="admin-subtitle"
            >Всего пользователей: {{ totalFound }}</span
          >
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
            placeholder="Поиск по имени, email или bio..."
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
            <label class="filter-label">Роль</label>
            <select
              v-model="filters.roleId"
              @change="applyFilters"
              class="filter-select"
            >
              <option value="all">Все роли</option>
              <option value="1">Администратор</option>
              <option value="2">Пользователь</option>
              <option value="3">Автор</option>
            </select>
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
            <label class="filter-label">GitHub</label>
            <select
              v-model="filters.hasGithub"
              @change="applyFilters"
              class="filter-select"
            >
              <option value="all">Все</option>
              <option value="true">Привязан</option>
              <option value="false">Не привязан</option>
            </select>
          </div>

          <div class="filter-group">
            <label class="filter-label">Google</label>
            <select
              v-model="filters.hasGoogle"
              @change="applyFilters"
              class="filter-select"
            >
              <option value="all">Все</option>
              <option value="true">Привязан</option>
              <option value="false">Не привязан</option>
            </select>
          </div>

          <div class="filter-group">
            <label class="filter-label">Аватар</label>
            <select
              v-model="filters.hasAvatar"
              @change="applyFilters"
              class="filter-select"
            >
              <option value="all">Все</option>
              <option value="true">Есть аватар</option>
              <option value="false">Нет аватара</option>
            </select>
          </div>

          <div class="filter-group">
            <label class="filter-label">Сортировка</label>
            <select
              v-model="filters.sortBy"
              @change="applyFilters"
              class="filter-select"
            >
              <option value="created_at">По дате регистрации</option>
              <option value="name">По имени</option>
              <option value="email">По email</option>
              <option value="reviews_count">По количеству рецензий</option>
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

        <div class="date-range">
          <div class="filter-group">
            <label class="filter-label">Дата от</label>
            <input
              type="date"
              v-model="filters.dateFrom"
              @change="applyFilters"
              class="filter-input"
            />
          </div>
          <div class="filter-group">
            <label class="filter-label">Дата до</label>
            <input
              type="date"
              v-model="filters.dateTo"
              @change="applyFilters"
              class="filter-input"
            />
          </div>
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
          <span class="stat-chip__label">Всего пользователей:</span>
          <span class="stat-chip__value">{{ stats.total_users }}</span>
        </div>
        <div class="stat-chip">
          <span class="stat-chip__label">Удаленные:</span>
          <span class="stat-chip__value stat-chip__value--danger">{{
            stats.total_only_trashed
          }}</span>
        </div>
        <div class="stat-chip">
          <span class="stat-chip__label">С GitHub:</span>
          <span class="stat-chip__value">{{
            stats.total_with_socials?.github || 0
          }}</span>
        </div>
        <div class="stat-chip">
          <span class="stat-chip__label">С Google:</span>
          <span class="stat-chip__value">{{
            stats.total_with_socials?.google || 0
          }}</span>
        </div>
        <div v-if="stats.total_by_role?.admin" class="stat-chip">
          <span class="stat-chip__label">Администраторы:</span>
          <span class="stat-chip__value">{{ stats.total_by_role.admin }}</span>
        </div>
        <div v-if="stats.total_by_role?.reviewer" class="stat-chip">
          <span class="stat-chip__label">Пользователи:</span>
          <span class="stat-chip__value">{{
            stats.total_by_role.reviewer
          }}</span>
        </div>
        <div v-if="stats.total_by_role?.author" class="stat-chip">
          <span class="stat-chip__label">Авторы:</span>
          <span class="stat-chip__value">{{ stats.total_by_role.author }}</span>
        </div>
      </div>

      <div class="table-section">
        <div v-if="isLoading" class="table-skeleton">
          <div v-for="i in perPage" :key="i" class="skeleton-row"></div>
        </div>

        <div v-else-if="users.length > 0" class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Аватар</th>
                <th>Имя</th>
                <th>Email</th>
                <th>Роль</th>
                <th>Соц. сети</th>
                <th>Рецензий</th>
                <th>Дата регистрации</th>
                <th>Статус</th>
                <th>Действия</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="user in users"
                :key="user.id"
                :class="{ 'trashed-row': user.is_trashed }"
              >
                <td class="avatar-cell">
                  <div class="table-avatar">
                    <img
                      v-if="user.avatar_url"
                      :src="user.avatar_url"
                      :alt="user.name"
                    />
                    <div v-else class="table-avatar-placeholder">
                      {{ user.name?.charAt(0)?.toUpperCase() || "?" }}
                    </div>
                  </div>
                </td>
                <td class="name-cell">
                  <div class="user-info">
                    <span class="user-name">{{ user.name }}</span>
                    <span v-if="user.bio" class="user-bio">{{
                      truncateText(user.bio, 50)
                    }}</span>
                  </div>
                </td>
                <td class="email-cell">{{ user.email }}</td>
                <td class="role-cell">
                  <span
                    class="role-badge"
                    :class="getRoleClass(user.role?.name || user.role)"
                  >
                    {{ getRoleLabel(user.role?.name || user.role) }}
                  </span>
                </td>
                <td class="socials-cell">
                  <div class="social-icons">
                    <span
                      v-if="user.socials?.github"
                      class="social-icon github"
                      title="Привязан GitHub"
                    >
                      <svg viewBox="0 0 24 24" fill="currentColor">
                        <path
                          d="M12 2C6.48 2 2 6.48 2 12c0 4.42 2.87 8.17 6.84 9.49.5.09.68-.22.68-.48 0-.24-.01-.88-.01-1.73-2.78.6-3.37-1.34-3.37-1.34-.46-1.16-1.11-1.47-1.11-1.47-.91-.62.07-.61.07-.61 1 .07 1.53 1.03 1.53 1.03.89 1.52 2.34 1.08 2.91.83.09-.65.35-1.09.63-1.34-2.22-.25-4.55-1.11-4.55-4.94 0-1.09.39-1.98 1.03-2.68-.1-.25-.45-1.27.1-2.64 0 0 .84-.27 2.75 1.02.8-.22 1.65-.33 2.5-.33.85 0 1.7.11 2.5.33 1.91-1.29 2.75-1.02 2.75-1.02.55 1.37.2 2.39.1 2.64.64.7 1.03 1.59 1.03 2.68 0 3.84-2.34 4.69-4.57 4.94.36.31.68.92.68 1.85 0 1.34-.01 2.42-.01 2.75 0 .27.18.58.69.48C19.13 20.17 22 16.42 22 12c0-5.52-4.48-10-10-10z"
                        />
                      </svg>
                    </span>
                    <span
                      v-if="user.socials?.google"
                      class="social-icon google"
                      title="Привязан Google"
                    >
                      <svg viewBox="0 0 24 24" fill="currentColor">
                        <path
                          d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                        />
                        <path
                          d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                        />
                        <path
                          d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                        />
                        <path
                          d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                        />
                      </svg>
                    </span>
                    <span
                      v-if="!user.socials?.github && !user.socials?.google"
                      class="social-icon none"
                      >—</span
                    >
                  </div>
                </td>
                <td class="reviews-cell">{{ user.reviews_count || 0 }}</td>
                <td class="date-cell">{{ formatDate(user.created_at) }}</td>
                <td class="status-cell">
                  <span
                    class="status-badge"
                    :class="{
                      'status-active': !user.is_trashed,
                      'status-deleted': user.is_trashed,
                    }"
                  >
                    {{ user.is_trashed ? "Удален" : "Активен" }}
                  </span>
                  <span
                    v-if="user.deleted_at"
                    class="delete-date"
                    :title="formatFullDate(user.deleted_at)"
                  >
                    {{ formatRelativeDate(user.deleted_at) }}
                  </span>
                </td>
                <td class="actions-cell">
                  <div class="action-buttons">
                    <button
                      class="action-btn role"
                      @click="openRoleModal(user)"
                      title="Изменить роль"
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
                          d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                        />
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M12 4a8 8 0 100 16 8 8 0 000-16z"
                        />
                      </svg>
                    </button>
                    <button
                      v-if="
                        !user.is_trashed && user.id !== 1 && user.can_delete
                      "
                      class="action-btn delete"
                      @click="confirmDelete(user)"
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
                      v-if="user.is_trashed && user.can_restore"
                      class="action-btn restore"
                      @click="confirmRestore(user)"
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
                      v-if="user.is_trashed && user.can_force_delete"
                      class="action-btn force-delete"
                      @click="confirmForceDelete(user)"
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
              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
            />
          </svg>
          <h3>Пользователи не найдены</h3>
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
  </div>

  <Teleport to="body">
    <div
      v-if="showRoleModal"
      class="modal-overlay"
      @click.self="closeRoleModal"
    >
      <div class="modal-container modal-container--small">
        <div class="modal-header">
          <h2 class="modal-title">Изменение роли</h2>
          <button class="modal-close" @click="closeRoleModal">
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
          <div class="user-preview">
            <div class="user-preview__avatar">
              <img
                v-if="selectedUser?.avatar_url"
                :src="selectedUser.avatar_url"
                :alt="selectedUser.name"
              />
              <div v-else class="user-preview__avatar-placeholder">
                {{ selectedUser?.name?.charAt(0)?.toUpperCase() || "?" }}
              </div>
            </div>
            <div class="user-preview__info">
              <h4 class="user-preview__name">{{ selectedUser?.name }}</h4>
              <p class="user-preview__email">{{ selectedUser?.email }}</p>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Новая роль</label>
            <select v-model="roleForm.roleId" class="form-select">
              <option value="1">Администратор</option>
              <option value="2" selected>Пользователь</option>
              <option value="3">Автор</option>
            </select>
          </div>
        </div>

        <div class="modal-actions">
          <button
            type="button"
            class="btn btn--outline"
            @click="closeRoleModal"
            :disabled="roleLoading"
          >
            Отмена
          </button>
          <button
            type="button"
            class="btn btn--primary"
            @click="updateRole"
            :disabled="roleLoading || !selectedUser"
          >
            <svg v-if="roleLoading" class="spinner" viewBox="0 0 50 50">
              <circle
                cx="25"
                cy="25"
                r="20"
                fill="none"
                stroke="currentColor"
                stroke-width="4"
              ></circle>
            </svg>
            <span v-else>Сохранить</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>

  <Teleport to="body">
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeModal">
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

          <div v-if="selectedUser" class="user-preview user-preview--compact">
            <div class="user-preview__avatar">
              <img
                v-if="selectedUser.avatar_url"
                :src="selectedUser.avatar_url"
                :alt="selectedUser.name"
              />
              <div v-else class="user-preview__avatar-placeholder">
                {{ selectedUser.name?.charAt(0)?.toUpperCase() || "?" }}
              </div>
            </div>
            <div class="user-preview__info">
              <h4 class="user-preview__name">{{ selectedUser.name }}</h4>
              <p class="user-preview__email">{{ selectedUser.email }}</p>
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
  <ThemeToggle></ThemeToggle>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, inject } from "vue";
import { useRouter } from "vue-router";
import { useUserStore } from "@/stores/user";
import Cookies from "js-cookie";
import ThemeToggle from "@/components/ThemeToggle.vue";

interface UserSocials {
  github: boolean;
  google: boolean;
}

interface User {
  id: number;
  name: string;
  email: string;
  bio: string | null;
  avatar_url: string | null;
  role_id: number;
  role: { id: number; name: string } | string;
  socials: UserSocials;
  reviews_count: number;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  is_trashed: boolean;
  can_edit: boolean;
  can_delete: boolean;
  can_restore: boolean;
  can_force_delete: boolean;
}

interface SearchResponse {
  success: boolean;
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    stats: {
      total_users: number;
      total_with_trashed: number;
      total_only_trashed: number;
      total_by_role: {
        admin?: number;
        reviewer?: number;
        author?: number;
      };
      total_with_socials: {
        github: number;
        google: number;
      };
    };
    filters_applied: any;
  };
  data: User[];
}

interface Filters {
  search: string;
  roleId: string;
  trashed: "with" | "without" | "only";
  hasGithub: string;
  hasGoogle: string;
  hasAvatar: string;
  sortBy: string;
  sortOrder: string;
  dateFrom: string;
  dateTo: string;
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
const users = ref<User[]>([]);
const totalFound = ref(0);
const currentPage = ref(1);
const perPage = ref(25);
const totalPages = ref(1);
const stats = ref<SearchResponse["meta"]["stats"] | null>(null);

const filters = reactive<Filters>({
  search: "",
  roleId: "all",
  trashed: "without",
  hasGithub: "all",
  hasGoogle: "all",
  hasAvatar: "all",
  sortBy: "created_at",
  sortOrder: "desc",
  dateFrom: "",
  dateTo: "",
});

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
  if (filters.roleId !== "all") {
    const roleMap: Record<string, string> = {
      "1": "Администратор",
      "2": "Пользователь",
      "3": "Автор",
    };
    applied.push({
      key: "roleId",
      label: "Роль",
      value: roleMap[filters.roleId] || "",
    });
  }
  if (filters.trashed !== "without") {
    applied.push({
      key: "trashed",
      label: "Статус",
      value: filters.trashed === "with" ? "Все" : "Только удаленные",
    });
  }
  if (filters.hasGithub !== "all") {
    applied.push({
      key: "hasGithub",
      label: "GitHub",
      value: filters.hasGithub === "true" ? "Привязан" : "Не привязан",
    });
  }
  if (filters.hasGoogle !== "all") {
    applied.push({
      key: "hasGoogle",
      label: "Google",
      value: filters.hasGoogle === "true" ? "Привязан" : "Не привязан",
    });
  }
  if (filters.hasAvatar !== "all") {
    applied.push({
      key: "hasAvatar",
      label: "Аватар",
      value: filters.hasAvatar === "true" ? "Есть" : "Нет",
    });
  }
  if (filters.dateFrom) {
    applied.push({
      key: "dateFrom",
      label: "Дата от",
      value: filters.dateFrom,
    });
  }
  if (filters.dateTo) {
    applied.push({ key: "dateTo", label: "Дата до", value: filters.dateTo });
  }
  return applied;
});

const isFiltersDefault = computed(() => {
  return (
    !filters.search &&
    filters.roleId === "all" &&
    filters.trashed === "without" &&
    filters.hasGithub === "all" &&
    filters.hasGoogle === "all" &&
    filters.hasAvatar === "all" &&
    !filters.dateFrom &&
    !filters.dateTo &&
    filters.sortBy === "created_at" &&
    filters.sortOrder === "desc"
  );
});

const showRoleModal = ref(false);
const roleLoading = ref(false);
const selectedUser = ref<User | null>(null);
const roleForm = reactive({ roleId: "" });

const showDeleteModal = ref(false);
const modalLoading = ref(false);
const modalAction = ref<"delete" | "restore" | "force-delete" | null>(null);

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
      return "Вы уверены, что хотите мягко удалить этого пользователя? Он будет скрыт с сайта, но его можно будет восстановить.";
    case "restore":
      return "Вы уверены, что хотите восстановить этого пользователя? Он снова сможет пользоваться сайтом.";
    case "force-delete":
      return "Вы уверены, что хотите полностью удалить этого пользователя? Это действие необратимо! Все связанные данные (рецензии, лайки, избранное) также будут удалены.";
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

const getRoleLabel = (roleName: string | { name: string }): string => {
  const name = typeof roleName === "object" ? roleName.name : roleName;
  const roles: Record<string, string> = {
    admin: "Администратор",
    reviewer: "Пользователь",
    author: "Автор",
  };
  return roles[name] || name || "Пользователь";
};

const getRoleClass = (roleName: string | { name: string }): string => {
  const name = typeof roleName === "object" ? roleName.name : roleName;
  const classes: Record<string, string> = {
    admin: "role-badge--admin",
    reviewer: "role-badge--reviewer",
    author: "role-badge--author",
  };
  return classes[name] || "role-badge--user";
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

  if (diffDays === 1) return "вчера";
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
    case "roleId":
      filters.roleId = "all";
      break;
    case "trashed":
      filters.trashed = "without";
      break;
    case "hasGithub":
      filters.hasGithub = "all";
      break;
    case "hasGoogle":
      filters.hasGoogle = "all";
      break;
    case "hasAvatar":
      filters.hasAvatar = "all";
      break;
    case "dateFrom":
      filters.dateFrom = "";
      break;
    case "dateTo":
      filters.dateTo = "";
      break;
  }
  applyFilters();
};

const searchUsers = async () => {
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
    if (filters.roleId !== "all") body.role_id = parseInt(filters.roleId);
    if (filters.hasGithub !== "all")
      body.has_github = filters.hasGithub === "true";
    if (filters.hasGoogle !== "all")
      body.has_google = filters.hasGoogle === "true";
    if (filters.hasAvatar !== "all")
      body.has_avatar = filters.hasAvatar === "true";
    if (filters.dateFrom) body.date_from = filters.dateFrom;
    if (filters.dateTo) body.date_to = filters.dateTo;

    const response = await fetch(`${API_URL}/users/search`, {
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
      users.value = data.data;
      totalFound.value = data.meta.total;
      totalPages.value = data.meta.last_page;
      currentPage.value = data.meta.current_page;
      stats.value = data.meta.stats;
    } else {
      console.error("Error searching users:", data);
      toast.error("Ошибка при поиске пользователей");
      users.value = [];
      totalFound.value = 0;
    }
  } catch (error) {
    console.error("Error searching users:", error);
    toast.error("Произошла ошибка при поиске");
    users.value = [];
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
  searchUsers();
};

const resetFilters = () => {
  filters.search = "";
  filters.roleId = "all";
  filters.trashed = "without";
  filters.hasGithub = "all";
  filters.hasGoogle = "all";
  filters.hasAvatar = "all";
  filters.sortBy = "created_at";
  filters.sortOrder = "desc";
  filters.dateFrom = "";
  filters.dateTo = "";
  currentPage.value = 1;
  searchUsers();
};

const resetPagination = () => {
  currentPage.value = 1;
  searchUsers();
};

const goToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    searchUsers();
    window.scrollTo({ top: 0, behavior: "smooth" });
  }
};

const goToDashboard = () => {
  router.push("/admin");
};

const openRoleModal = (user: User) => {
  selectedUser.value = user;
  roleForm.roleId = String(user.role_id);
  showRoleModal.value = true;
  document.body.style.overflow = "hidden";
};

const closeRoleModal = () => {
  showRoleModal.value = false;
  selectedUser.value = null;
  document.body.style.overflow = "";
};

const updateRole = async () => {
  if (!selectedUser.value || !roleForm.roleId) return;

  const token = getToken();
  if (!token) {
    router.push("/login");
    return;
  }

  roleLoading.value = true;

  try {
    const response = await fetch(
      `${API_URL}/update-role/${selectedUser.value.id}`,
      {
        method: "POST",
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ role_id: parseInt(roleForm.roleId) }),
      },
    );

    if (response.status === 401) {
      handleUnauthorized();
      return;
    }

    if (response.status === 403) {
      toast.error("У вас нет прав для изменения роли");
      closeRoleModal();
      return;
    }

    if (response.status === 409) {
      const data = await response.json();
      toast.error(
        data.error?.message || "Нельзя изменить роль главного администратора",
      );
      closeRoleModal();
      return;
    }

    if (response.ok) {
      const data = await response.json();
      toast.success(data.data?.message || "Роль успешно изменена");
      closeRoleModal();
      await searchUsers();
    } else {
      const data = await response.json();
      toast.error(data.error?.message || "Ошибка при изменении роли");
    }
  } catch (error) {
    console.error("Error updating role:", error);
    toast.error("Произошла ошибка при изменении роли");
  } finally {
    roleLoading.value = false;
  }
};

const confirmDelete = (user: User) => {
  selectedUser.value = user;
  modalAction.value = "delete";
  showDeleteModal.value = true;
  document.body.style.overflow = "hidden";
};

const confirmRestore = (user: User) => {
  selectedUser.value = user;
  modalAction.value = "restore";
  showDeleteModal.value = true;
  document.body.style.overflow = "hidden";
};

const confirmForceDelete = (user: User) => {
  selectedUser.value = user;
  modalAction.value = "force-delete";
  showDeleteModal.value = true;
  document.body.style.overflow = "hidden";
};

const closeModal = () => {
  showDeleteModal.value = false;
  selectedUser.value = null;
  modalAction.value = null;
  document.body.style.overflow = "";
};

const executeAction = async () => {
  if (!selectedUser.value || !modalAction.value) return;

  const token = getToken();
  if (!token) {
    router.push("/login");
    return;
  }

  modalLoading.value = true;

  try {
    let url = `${API_URL}/delete-user/${selectedUser.value.id}`;
    let method = "DELETE";
    let successMessage = "";

    if (modalAction.value === "delete") {
      method = "DELETE";
      successMessage = "Пользователь успешно удален";
    } else if (modalAction.value === "restore") {
      url = `${API_URL}/users/${selectedUser.value.id}/restore`;
      method = "POST";
      successMessage = "Пользователь успешно восстановлен";
    } else if (modalAction.value === "force-delete") {
      url = `${API_URL}/users/${selectedUser.value.id}/force`;
      method = "DELETE";
      successMessage = "Пользователь полностью удален из системы";
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
      toast.error("Пользователь не найден");
      closeModal();
      return;
    }

    if (response.status === 409) {
      const data = await response.json();
      toast.error(data.error?.message || "Невозможно выполнить операцию");
      closeModal();
      return;
    }

    if (response.ok) {
      toast.success(successMessage);
      closeModal();
      await searchUsers();
    } else {
      const data = await response.json();
      toast.error(data.error?.message || "Ошибка при выполнении операции");
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
  searchUsers();
});
</script>

<style lang="scss" scoped>
.admin-users {
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
  grid-template-columns: repeat(7, 1fr) auto;
  gap: var(--space-md);
  margin-bottom: var(--space-lg);
  align-items: flex-end;

  @media (max-width: 1400px) {
    grid-template-columns: repeat(4, 1fr) auto;
  }

  @media (max-width: 1024px) {
    grid-template-columns: repeat(2, 1fr);
  }

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
    gap: var(--space-sm);
  }
}

.date-range {
  display: flex;
  gap: var(--space-md);
  margin-top: var(--space-md);
  padding-top: var(--space-md);
  border-top: 1px solid var(--border-color);

  @media (max-width: 768px) {
    flex-direction: column;
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

.avatar-cell {
  width: 50px;
}

.table-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  overflow: hidden;
  background: linear-gradient(
    135deg,
    var(--color-accent),
    var(--color-accent-light)
  );
  display: flex;
  align-items: center;
  justify-content: center;

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
    font-size: var(--font-size-md);
  }
}

.name-cell {
  min-width: 180px;
}

.user-info {
  display: flex;
  flex-direction: column;
  gap: 2px;

  .user-name {
    font-weight: 600;
  }

  .user-bio {
    font-size: var(--font-size-xs);
    color: var(--color-text-tertiary);
  }
}

.email-cell {
  min-width: 200px;
  color: var(--color-text-secondary);
}

.role-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: var(--radius-full);
  font-size: var(--font-size-xs);
  font-weight: 600;

  &--admin {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
  }

  &--reviewer {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
  }

  &--author {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
  }

  &--user {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
  }
}

.socials-cell {
  min-width: 80px;
}

.social-icons {
  display: flex;
  gap: var(--space-xs);
  align-items: center;
}

.social-icon {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: var(--font-size-xs);

  svg {
    width: 16px;
    height: 16px;
  }

  &.github {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
  }

  &.google {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
  }

  &.none {
    background: var(--color-bg-secondary);
    color: var(--color-text-tertiary);
  }
}

.reviews-cell {
  text-align: center;
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

  &.role:hover {
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
    background: linear-gradient(
      to right,
      rgba(245, 158, 11, 0.05),
      transparent
    );
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

.user-preview {
  display: flex;
  align-items: center;
  gap: var(--space-md);
  padding: var(--space-md);
  background: var(--color-bg-secondary);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-color);
  margin-bottom: var(--space-lg);

  &--compact {
    margin-top: var(--space-md);
  }

  &__avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    background: linear-gradient(
      135deg,
      var(--color-accent),
      var(--color-accent-light)
    );
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;

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
      font-size: var(--font-size-xl);
    }
  }

  &__info {
    flex: 1;
    min-width: 0;
  }

  &__name {
    font-size: var(--font-size-md);
    font-weight: 600;
    color: var(--color-text-primary);
    margin-bottom: 2px;
  }

  &__email {
    font-size: var(--font-size-xs);
    color: var(--color-text-tertiary);
  }
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

.form-select {
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

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: var(--space-sm);
  padding: var(--space-lg);
  border-top: 1px solid var(--border-color);

  &--danger {
    border-top-color: var(--color-error);
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
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 768px) {
  .admin-users {
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

  .modal-container {
    margin: var(--space-sm);
  }
}
</style>
