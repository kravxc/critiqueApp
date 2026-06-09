<!-- src/components/ThemeToggle.vue -->
<template>
  <button
    class="theme-toggle"
    @click="toggleTheme"
    :aria-label="isLightTheme ? 'Переключить на темную тему' : 'Переключить на светлую тему'"
  >
    <svg v-if="isLightTheme" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
    </svg>
    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
  </button>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'

const isLightTheme = ref(false)

const toggleTheme = () => {
  isLightTheme.value = !isLightTheme.value
  localStorage.setItem('theme', isLightTheme.value ? 'light' : 'dark')

  if (isLightTheme.value) {
    document.documentElement.classList.add('light-theme')
    document.body.classList.add('light-theme')
  } else {
    document.documentElement.classList.remove('light-theme')
    document.body.classList.remove('light-theme')
  }
}

onMounted(() => {
  const savedTheme = localStorage.getItem('theme')
  if (savedTheme === 'light') {
    isLightTheme.value = true
    document.documentElement.classList.add('light-theme')
    document.body.classList.add('light-theme')
  } else if (savedTheme === 'dark') {
    isLightTheme.value = false
  } else {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    isLightTheme.value = !prefersDark
    if (isLightTheme.value) {
      document.documentElement.classList.add('light-theme')
      document.body.classList.add('light-theme')
    }
  }
})

watch(isLightTheme, (isLight) => {
  document.documentElement.style.colorScheme = isLight ? 'light' : 'dark'
})
</script>

<style lang="scss" scoped>
.theme-toggle {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: var(--color-bg-card);
  border: 1px solid var(--border-color-strong);
  color: var(--color-text-primary);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  transition: all 0.3s ease;
  box-shadow: var(--shadow-lg);

  &:hover {
    transform: scale(1.1);
    box-shadow: var(--shadow-accent);
    border-color: var(--color-accent);
  }

  svg {
    width: 24px;
    height: 24px;
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
</style>