<template>
  <div :class="['app', { 'light-theme': isLightTheme }]">
    <template v-if="isAdminRoute">
      <router-view v-slot="{ Component }">
        <component :is="Component" :key="$route.fullPath" />
      </router-view>
    </template>
    <template v-else>
      <header class="header" :class="{ 'header--scrolled': isScrolled }">
        <div class="container header__container">
          <router-link to="/" class="header__logo">
            Наше<span class="gradient-text"> мнение</span>
          </router-link>

          <nav class="header__nav">
            <router-link
              to="/"
              class="header__link"
              :class="{ active: $route.path === '/' }"
            >
              Главная
            </router-link>
            <router-link
              to="/contents"
              class="header__link"
              :class="{ active: $route.path.startsWith('/contents') }"
            >
              Релизы
            </router-link>
            <router-link
              to="/about"
              class="header__link"
              :class="{ active: $route.path === '/about' }"
            >
              О нас
            </router-link>
            <router-link
              to="/contacts"
              class="header__link"
              :class="{ active: $route.path === '/contacts' }"
            >
              Контакты
            </router-link>
          </nav>

          <div class="header__actions">
            <template v-if="isPageLoading || userStore.isLoading">
              <div class="skeleton-loader skeleton-loader--small"></div>
              <div class="skeleton-loader skeleton-loader--small"></div>
            </template>

            <template v-else-if="userStore.isAuthenticated && userStore.user">
              <div
                class="user-menu"
                @click.stop="toggleUserMenu"
                ref="userMenuRef"
              >
                <div class="user-menu__trigger">
                  <div class="user-avatar">
                    <img
                      v-if="userStore.user.avatar_url || userStore.user.avatar"
                      :src="
                        getAvatarUrl(
                          userStore.user.avatar_url || userStore.user.avatar,
                        )
                      "
                      :alt="userStore.user.name"
                      class="user-avatar__image"
                    />
                    <div v-else class="user-avatar__placeholder">
                      {{ getUserInitials }}
                    </div>
                  </div>
                  <span class="user-menu__name">{{ userStore.user.name }}</span>
                  <svg
                    class="user-menu__arrow"
                    :class="{ 'user-menu__arrow--open': isUserMenuOpen }"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </div>

                <transition name="fade">
                  <div v-if="isUserMenuOpen" class="user-menu__dropdown">
                    <div class="user-menu__header">
                      <div class="user-menu__avatar-large">
                        <img
                          v-if="
                            userStore.user.avatar_url || userStore.user.avatar
                          "
                          :src="
                            getAvatarUrl(
                              userStore.user.avatar_url ||
                                userStore.user.avatar,
                            )
                          "
                          :alt="userStore.user.name"
                        />
                        <div v-else>{{ getUserInitials }}</div>
                      </div>
                      <div class="user-menu__info">
                        <div class="user-menu__full-name">
                          {{ userStore.user.name }}
                        </div>
                        <div class="user-menu__email">
                          {{ userStore.user.email }}
                        </div>
                      </div>
                    </div>

                    <div class="user-menu__items">
                      <router-link
                        to="/profile"
                        class="user-menu__item"
                        @click="isUserMenuOpen = false"
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
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                          />
                        </svg>
                        <span>Мой профиль</span>
                      </router-link>

                      <div class="user-menu__divider"></div>

                      <button
                        class="user-menu__item user-menu__item--logout"
                        @click="handleLogout"
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
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                          />
                        </svg>
                        <span>Выйти</span>
                      </button>
                    </div>
                  </div>
                </transition>
              </div>
            </template>

            <template v-else>
              <router-link to="/login" class="btn btn--outline">
                Войти
              </router-link>
              <router-link to="/register" class="btn btn--primary">
                Регистрация
              </router-link>
            </template>
          </div>

          <button
            class="header__burger"
            @click="toggleMobileMenu"
            :class="{ active: mobileMenuOpen }"
          >
            <span></span>
            <span></span>
            <span></span>
          </button>
        </div>

        <transition name="slide">
          <div v-if="mobileMenuOpen" class="header__mobile">
            <nav class="header__mobile-nav">
              <router-link
                to="/"
                class="header__mobile-link"
                @click="mobileMenuOpen = false"
              >
                Главная
              </router-link>
              <router-link
                to="/contents"
                class="header__mobile-link"
                @click="mobileMenuOpen = false"
              >
                Релизы
              </router-link>
              <router-link
                to="/about"
                class="header__mobile-link"
                @click="mobileMenuOpen = false"
              >
                О нас
              </router-link>
              <router-link
                to="/contacts"
                class="header__mobile-link"
                @click="mobileMenuOpen = false"
              >
                Контакты
              </router-link>

              <template v-if="userStore.isAuthenticated && userStore.user">
                <div class="header__mobile-user">
                  <div class="header__mobile-user-info">
                    <div class="header__mobile-avatar">
                      <img
                        v-if="
                          userStore.user.avatar_url || userStore.user.avatar
                        "
                        :src="
                          getAvatarUrl(
                            userStore.user.avatar_url || userStore.user.avatar,
                          )
                        "
                        :alt="userStore.user.name"
                      />
                      <div v-else>{{ getUserInitials }}</div>
                    </div>
                    <div>
                      <div class="header__mobile-name">
                        {{ userStore.user.name }}
                      </div>
                      <div class="header__mobile-email">
                        {{ userStore.user.email }}
                      </div>
                    </div>
                  </div>
                </div>
                <router-link
                  to="/profile"
                  class="header__mobile-link"
                  @click="mobileMenuOpen = false"
                >
                  Мой профиль
                </router-link>
                <router-link
                  to="/my-reviews"
                  class="header__mobile-link"
                  @click="mobileMenuOpen = false"
                >
                  Мои рецензии
                </router-link>
                <router-link
                  to="/settings"
                  class="header__mobile-link"
                  @click="mobileMenuOpen = false"
                >
                  Настройки
                </router-link>
                <button
                  class="header__mobile-link header__mobile-link--logout"
                  @click="handleLogout"
                >
                  Выйти
                </button>
              </template>

              <template v-else>
                <div class="header__mobile-actions">
                  <router-link
                    to="/login"
                    class="btn btn--outline"
                    @click="mobileMenuOpen = false"
                  >
                    Войти
                  </router-link>
                  <router-link
                    to="/register"
                    class="btn btn--primary"
                    @click="mobileMenuOpen = false"
                  >
                    Регистрация
                  </router-link>
                </div>
              </template>
            </nav>
          </div>
        </transition>
      </header>

      <main class="main">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" :key="$route.fullPath" />
          </transition>
        </router-view>
      </main>

      <footer class="footer">
        <div class="container">
          <div class="footer__grid">
            <div class="footer__col">
              <div class="footer__logo">
                Наше<span class="gradient-text"> мнение</span>
              </div>
              <p class="footer__description">
                Платформа для публикации и обсуждения рецензий музыкального
                контента. Честные мнения, живое сообщество и актуальные релизы.
              </p>
              <div class="footer__social">
                <a href="#" class="footer__social-link" aria-label="Telegram">
                  <svg viewBox="0 0 24 24" fill="currentColor">
                    <path
                      d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.12.02-1.96 1.25-5.54 3.69-.52.36-1 .53-1.42.52-.47-.01-1.37-.26-2.04-.48-.82-.27-1.47-.42-1.42-.88.03-.24.27-.48.74-.74 2.88-1.25 4.8-2.08 5.75-2.5 2.74-1.16 3.31-1.36 3.68-1.36.08 0 .26.02.38.12.1.08.13.19.14.26-.01.07.01.19 0 .3z"
                    />
                  </svg>
                </a>
                <a href="#" class="footer__social-link" aria-label="Twitter">
                  <svg viewBox="0 0 24 24" fill="currentColor">
                    <path
                      d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98-3.56-.18-6.73-1.89-8.84-4.48-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.52 8.52 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.9 20.29 6.16 21 8.58 21c7.88 0 12.21-6.54 12.21-12.21 0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.13-2.23z"
                    />
                  </svg>
                </a>
                <a href="#" class="footer__social-link" aria-label="Instagram">
                  <svg viewBox="0 0 24 24" fill="currentColor">
                    <path
                      d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zM5.838 12a6.162 6.162 0 1112.324 0 6.162 6.162 0 01-12.324 0zM12 16a4 4 0 110-8 4 4 0 010 8zm4.965-10.405a1.44 1.44 0 112.881.001 1.44 1.44 0 01-2.881-.001z"
                    />
                  </svg>
                </a>
              </div>
            </div>

            <div class="footer__col">
              <h4 class="footer__title">Навигация</h4>
              <ul class="footer__list">
                <li>
                  <router-link to="/" class="footer__link">Главная</router-link>
                </li>
                <li>
                  <router-link to="/contents" class="footer__link"
                    >Релизы</router-link
                  >
                </li>
                <li>
                  <router-link to="/about" class="footer__link"
                    >О нас</router-link
                  >
                </li>
                <li>
                  <router-link to="/contacts" class="footer__link"
                    >Контакты</router-link
                  >
                </li>
              </ul>
            </div>

            <div class="footer__col">
              <h4 class="footer__title">Аккаунт</h4>
              <ul class="footer__list">
                <li v-if="!userStore.isAuthenticated">
                  <router-link to="/login" class="footer__link"
                    >Вход</router-link
                  >
                </li>
                <li v-if="!userStore.isAuthenticated">
                  <router-link to="/register" class="footer__link"
                    >Регистрация</router-link
                  >
                </li>
                <li v-if="userStore.isAuthenticated">
                  <router-link to="/profile" class="footer__link"
                    >Профиль</router-link
                  >
                </li>
                <li v-if="userStore.isAuthenticated">
                  <router-link to="/my-reviews" class="footer__link"
                    >Мои рецензии</router-link
                  >
                </li>
                <li v-if="userStore.isAuthenticated">
                  <router-link to="/settings" class="footer__link"
                    >Настройки</router-link
                  >
                </li>
              </ul>
            </div>

            <div class="footer__col">
              <h4 class="footer__title">Поддержка</h4>
              <ul class="footer__list">
                <li><a href="#" class="footer__link">Помощь</a></li>
                <li><a href="#" class="footer__link">FAQ</a></li>
                <li>
                  <router-link to="/terms" class="footer__link"
                    >Правила</router-link
                  >
                </li>
                <li>
                  <router-link to="/privacy" class="footer__link"
                    >Конфиденциальность</router-link
                  >
                </li>
              </ul>
            </div>
          </div>

          <div class="footer__bottom">
            <p class="footer__copyright">
              © 2024 Наше мнение. Все права защищены.
            </p>
            <p class="footer__made">Сделано с ❤️ для меломанов</p>
          </div>
        </div>
      </footer>

      <button
        class="theme-toggle"
        @click="toggleTheme"
        :aria-label="
          isLightTheme
            ? 'Переключить на темную тему'
            : 'Переключить на светлую тему'
        "
      >
        <svg
          v-if="isLightTheme"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
          />
        </svg>
        <svg
          v-else
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
          />
        </svg>
      </button>
    </template>

    <Toast ref="toastRef" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed, provide, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useUserStore } from "@/stores/user";
import Toast from "@/components/Toast.vue";

const route = useRoute();
const router = useRouter();
const userStore = useUserStore();

const isLightTheme = ref(false);
const mobileMenuOpen = ref(false);
const isScrolled = ref(false);
const isUserMenuOpen = ref(false);
const userMenuRef = ref<HTMLElement | null>(null);
const isPageLoading = ref(true);
const toastRef = ref<InstanceType<typeof Toast>>();

const isAdminRoute = computed(() => {
  return route.path.startsWith("/admin");
});

const toast = {
  success: (message: string, timeout?: number) => {
    toastRef.value?.addToast({ type: "success", message, timeout });
  },
  error: (message: string, timeout?: number) => {
    toastRef.value?.addToast({ type: "error", message, timeout });
  },
  warning: (message: string, timeout?: number) => {
    toastRef.value?.addToast({ type: "warning", message, timeout });
  },
  info: (message: string, timeout?: number) => {
    toastRef.value?.addToast({ type: "info", message, timeout });
  },
};

provide("toast", toast);

const getUserInitials = computed(() => {
  if (!userStore.user?.name) return "?";
  return userStore.user.name
    .split(" ")
    .map((word: string) => word[0])
    .join("")
    .toUpperCase()
    .slice(0, 2);
});

const getAvatarUrl = (avatar: string) => {
  if (!avatar) return "";
  if (avatar.startsWith("http")) {
    return avatar;
  }
  if (avatar.startsWith("storage/") || avatar.startsWith("avatars/")) {
    return `https://critiqueassets.storage.yandexcloud.net/${avatar}`;
  }
  return avatar;
};

const handleLogout = async () => {
  await userStore.logout();
  isUserMenuOpen.value = false;
  mobileMenuOpen.value = false;
  router.push("/");
  toast.success("Вы успешно вышли из системы");
};

const toggleUserMenu = () => {
  isUserMenuOpen.value = !isUserMenuOpen.value;
};

const handleClickOutside = (event: MouseEvent) => {
  if (userMenuRef.value && !userMenuRef.value.contains(event.target as Node)) {
    isUserMenuOpen.value = false;
  }
};

const toggleTheme = () => {
  isLightTheme.value = !isLightTheme.value;
  localStorage.setItem("theme", isLightTheme.value ? "light" : "dark");
};

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value;
  if (mobileMenuOpen.value) {
    document.body.style.overflow = "hidden";
  } else {
    document.body.style.overflow = "";
  }
};

const handleScroll = () => {
  isScrolled.value = window.scrollY > 50;
};

const messageHandler = async (event: MessageEvent) => {
  if (event.origin !== window.location.origin) return;

  const { type, data } = event.data;
  console.log("📩 Received message:", type, data);

  if (type === "github-auth-success" || type === "google-auth-success") {
    if (data.token) {
      userStore.setToken(data.token);

      await userStore.fetchUser();

      if (data.is_bind) {
        toast.success(data.message || "Аккаунт успешно привязан");
        if (router.currentRoute.value.path !== "/profile") {
          router.push("/profile");
        }
      } else {
        toast.success("Успешный вход");
        router.push("/");
      }
    } else {
      toast.error("Не получен токен авторизации");
    }
  } else if (type === "github-auth-error" || type === "google-auth-error") {
    toast.error(data.message || "Ошибка при авторизации");
  }
};

onMounted(async () => {
  const savedTheme = localStorage.getItem("theme");
  if (savedTheme === "light") {
    isLightTheme.value = true;
  } else if (savedTheme === "dark") {
    isLightTheme.value = false;
  } else {
    const prefersDark = window.matchMedia(
      "(prefers-color-scheme: dark)",
    ).matches;
    isLightTheme.value = !prefersDark;
  }

  window.addEventListener("scroll", handleScroll);
  document.addEventListener("click", handleClickOutside);
  window.addEventListener("message", messageHandler);

  await userStore.fetchUser();
  isPageLoading.value = false;
});

onUnmounted(() => {
  window.removeEventListener("scroll", handleScroll);
  document.removeEventListener("click", handleClickOutside);
  window.removeEventListener("message", messageHandler);
  document.body.style.overflow = "";
});

watch(isLightTheme, (isLight) => {
  document.documentElement.style.colorScheme = isLight ? "light" : "dark";
});

watch(
  () => route.path,
  () => {
    mobileMenuOpen.value = false;
    document.body.style.overflow = "";
  },
);

watch(
  isLightTheme,
  (isLight) => {
    document.documentElement.style.colorScheme = isLight ? "light" : "dark";

    if (isLight) {
      document.documentElement.classList.add("light-theme");
      document.body.classList.add("light-theme");
    } else {
      document.documentElement.classList.remove("light-theme");
      document.body.classList.remove("light-theme");
    }
  },
  { immediate: true },
);

onMounted(async () => {
  const savedTheme = localStorage.getItem("theme");
  let currentIsLight = false;

  if (savedTheme === "light") {
    currentIsLight = true;
  } else if (savedTheme === "dark") {
    currentIsLight = false;
  } else {
    currentIsLight = !window.matchMedia("(prefers-color-scheme: dark)").matches;
  }

  isLightTheme.value = currentIsLight;

  if (currentIsLight) {
    document.documentElement.classList.add("light-theme");
    document.body.classList.add("light-theme");
  } else {
    document.documentElement.classList.remove("light-theme");
    document.body.classList.remove("light-theme");
  }

  window.addEventListener("scroll", handleScroll);
  document.addEventListener("click", handleClickOutside);
  window.addEventListener("message", messageHandler);

  await userStore.fetchUser();
  isPageLoading.value = false;
});
</script>

<style lang="scss">
@import "@/assets/styles/main.scss";

.app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  transition: background-color 0.3s ease;
  background-color: var(--color-bg-primary);
  color: var(--color-text-primary);
}

.main {
  flex: 1;
  margin-top: 70px;
}

.skeleton-loader {
  background: linear-gradient(
    90deg,
    var(--color-bg-secondary) 25%,
    var(--color-bg-elevated) 50%,
    var(--color-bg-secondary) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: var(--radius-full);

  &--small {
    width: 80px;
    height: 36px;
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

.header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: var(--z-fixed);
  background-color: var(--color-bg-primary);
  border-bottom: 1px solid var(--border-color);
  transition: var(--transition-base);
  height: 70px;

  &--scrolled {
    backdrop-filter: blur(10px);
    background-color: rgba(var(--color-bg-primary-rgb), 0.9);
  }

  &__container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 100%;
  }

  &__logo {
    font-size: var(--font-size-xl);
    font-weight: 800;
    text-decoration: none;
    color: var(--color-text-primary);
    letter-spacing: -0.5px;
  }

  &__nav {
    display: flex;
    gap: var(--space-lg);

    @media (max-width: 768px) {
      display: none;
    }
  }

  &__link {
    color: var(--color-text-secondary);
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition-base);
    position: relative;

    &:hover {
      color: var(--color-accent);
    }

    &.active {
      color: var(--color-accent);

      &::after {
        content: "";
        position: absolute;
        bottom: -4px;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--color-accent);
        border-radius: var(--radius-full);
      }
    }
  }

  &__actions {
    display: flex;
    align-items: center;
    gap: var(--space-sm);

    @media (max-width: 768px) {
      display: none;
    }
  }

  &__burger {
    display: none;
    flex-direction: column;
    gap: 6px;
    background: none;
    border: none;
    cursor: pointer;
    padding: var(--space-xs);
    z-index: 100;

    @media (max-width: 768px) {
      display: flex;
    }

    span {
      display: block;
      width: 24px;
      height: 2px;
      background: var(--color-text-primary);
      transition: var(--transition-base);
    }

    &.active {
      span {
        &:first-child {
          transform: rotate(45deg) translate(6px, 6px);
        }
        &:nth-child(2) {
          opacity: 0;
        }
        &:last-child {
          transform: rotate(-45deg) translate(6px, -6px);
        }
      }
    }
  }

  &__mobile {
    position: fixed;
    top: 70px;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: var(--color-bg-primary);
    padding: var(--space-xl);
    z-index: 99;
    overflow-y: auto;

    &-nav {
      display: flex;
      flex-direction: column;
      gap: var(--space-lg);
    }

    &-link {
      color: var(--color-text-primary);
      text-decoration: none;
      font-size: var(--font-size-xl);
      font-weight: 600;
      padding: var(--space-sm) 0;
      border-bottom: 1px solid var(--border-color);
      transition: var(--transition-base);

      &:hover {
        color: var(--color-accent);
        padding-left: var(--space-sm);
      }

      &--logout {
        color: var(--color-error);
        background: none;
        border: none;
        cursor: pointer;
        font-size: var(--font-size-xl);
        text-align: left;
        width: 100%;

        &:hover {
          color: var(--color-error);
          opacity: 0.8;
        }
      }
    }

    &-actions {
      display: flex;
      flex-direction: column;
      gap: var(--space-sm);
      margin-top: var(--space-xl);

      .btn {
        width: 100%;
      }
    }

    &-user {
      padding: var(--space-lg) 0;
      border-bottom: 1px solid var(--border-color);
      margin-bottom: var(--space-md);
    }

    &-user-info {
      display: flex;
      align-items: center;
      gap: var(--space-md);
    }

    &-avatar {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      background: linear-gradient(
        135deg,
        var(--color-accent),
        var(--color-accent-light)
      );
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      font-size: var(--font-size-lg);

      img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
      }
    }

    &-name {
      font-weight: 600;
      color: var(--color-text-primary);
      margin-bottom: 2px;
    }

    &-email {
      font-size: var(--font-size-xs);
      color: var(--color-text-tertiary);
    }
  }
}

.footer {
  background-color: var(--color-bg-secondary);
  border-top: 1px solid var(--border-color);
  padding: var(--space-3xl) 0 var(--space-xl);
  margin-top: auto;

  &__grid {
    display: grid;
    grid-template-columns: 2fr repeat(3, 1fr);
    gap: var(--space-xl);
    margin-bottom: var(--space-2xl);

    @media (max-width: 768px) {
      grid-template-columns: 1fr;
      gap: var(--space-lg);
    }
  }

  &__col {
    &:first-child {
      @media (max-width: 768px) {
        text-align: center;
      }
    }
  }

  &__logo {
    font-size: var(--font-size-2xl);
    font-weight: 800;
    margin-bottom: var(--space-md);
    display: inline-block;
    color: var(--color-text-primary);
  }

  &__description {
    color: var(--color-text-secondary);
    font-size: var(--font-size-sm);
    line-height: 1.6;
    margin-bottom: var(--space-lg);
    max-width: 300px;

    @media (max-width: 768px) {
      max-width: none;
    }
  }

  &__social {
    display: flex;
    gap: var(--space-md);

    @media (max-width: 768px) {
      justify-content: center;
    }
  }

  &__social-link {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-full);
    background-color: var(--color-bg-card);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-secondary);
    transition: var(--transition-base);
    border: 1px solid var(--border-color);

    &:hover {
      color: var(--color-accent);
      transform: translateY(-3px);
      border-color: var(--color-accent);
    }

    svg {
      width: 18px;
      height: 18px;
    }
  }

  &__title {
    font-size: var(--font-size-lg);
    font-weight: 600;
    margin-bottom: var(--space-lg);
    color: var(--color-text-primary);

    @media (max-width: 768px) {
      text-align: center;
    }
  }

  &__list {
    list-style: none;

    @media (max-width: 768px) {
      text-align: center;
    }

    li {
      margin-bottom: var(--space-sm);
    }
  }

  &__link {
    color: var(--color-text-secondary);
    text-decoration: none;
    font-size: var(--font-size-sm);
    transition: var(--transition-base);

    &:hover {
      color: var(--color-accent);
      padding-left: var(--space-xs);
    }
  }

  &__bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: var(--space-xl);
    border-top: 1px solid var(--border-color);
    color: var(--color-text-tertiary);
    font-size: var(--font-size-sm);

    @media (max-width: 768px) {
      flex-direction: column;
      gap: var(--space-sm);
      text-align: center;
    }
  }

  &__made {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
  }
}

.user-menu {
  position: relative;
  cursor: pointer;

  &__trigger {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    padding: var(--space-xs) var(--space-sm);
    border-radius: var(--radius-full);
    background: var(--color-bg-secondary);
    border: 1px solid var(--border-color);
    transition: var(--transition-base);

    &:hover {
      border-color: var(--color-accent);
      background: var(--color-bg-elevated);
    }
  }

  &__name {
    font-size: var(--font-size-sm);
    font-weight: 500;
    color: var(--color-text-primary);
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  &__arrow {
    width: 16px;
    height: 16px;
    color: var(--color-text-secondary);
    transition: transform 0.3s ease;

    &--open {
      transform: rotate(180deg);
    }
  }

  &__dropdown {
    position: absolute;
    top: calc(100% + var(--space-xs));
    right: 0;
    width: 280px;
    background: var(--color-bg-card);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-lg);
    z-index: 1000;
    overflow: hidden;
  }

  &__header {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    padding: var(--space-md);
    background: var(--color-bg-secondary);
    border-bottom: 1px solid var(--border-color);
  }

  &__avatar-large {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(
      135deg,
      var(--color-accent),
      var(--color-accent-light)
    );
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: var(--font-size-lg);

    img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
    }
  }

  &__info {
    flex: 1;
  }

  &__full-name {
    font-weight: 600;
    color: var(--color-text-primary);
    margin-bottom: 2px;
  }

  &__email {
    font-size: var(--font-size-xs);
    color: var(--color-text-tertiary);
  }

  &__items {
    padding: var(--space-xs);
  }

  &__item {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    padding: var(--space-sm) var(--space-md);
    border-radius: var(--radius-md);
    color: var(--color-text-primary);
    text-decoration: none;
    transition: var(--transition-base);
    width: 100%;
    border: none;
    background: none;
    cursor: pointer;
    font-size: var(--font-size-sm);

    svg {
      width: 18px;
      height: 18px;
      color: var(--color-text-secondary);
    }

    &:hover {
      background: var(--color-bg-secondary);

      svg {
        color: var(--color-accent);
      }
    }

    &--logout {
      color: var(--color-error);

      svg {
        color: var(--color-error);
      }

      &:hover {
        background: rgba(var(--color-error-rgb), 0.1);
      }
    }
  }

  &__divider {
    height: 1px;
    background: var(--border-color);
    margin: var(--space-xs) 0;
  }
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  overflow: hidden;

  &__image {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  &__placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(
      135deg,
      var(--color-accent),
      var(--color-accent-light)
    );
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: var(--font-size-sm);
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
  transform: translateY(-100%);
}

.theme-toggle {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  width: 48px;
  height: 48px;
  border-radius: var(--radius-full);
  background-color: var(--color-bg-card);
  border: 1px solid var(--border-color-strong);
  color: var(--color-text-primary);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  z-index: var(--z-fixed);
  transition: var(--transition-base);
  box-shadow: var(--shadow-lg);

  &:hover {
    transform: scale(1.1);
    box-shadow: var(--shadow-accent);
  }

  svg {
    width: 24px;
    height: 24px;
    transition: var(--transition-base);
  }

  @media (max-width: 768px) {
    bottom: 1rem;
    right: 1rem;
    width: 40px;
    height: 40px;

    svg {
      width: 20px;
      height: 20px;
    }
  }
}
.btn {
    text-decoration: none;
  }
  
</style>
