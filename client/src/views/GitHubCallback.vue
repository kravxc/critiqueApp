<template>
  <div class="github-callback">
    <div class="github-callback__container">
      <div class="github-callback__card">
        <div v-if="loading" class="github-callback__loading">
          <div class="github-callback__spinner"></div>
          <h2 class="github-callback__title">Авторизация через GitHub</h2>
          <p class="github-callback__message">Пожалуйста, подождите...</p>
        </div>

        <div v-else-if="error" class="github-callback__error">
          <div class="github-callback__error-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h2 class="github-callback__title">Ошибка авторизации</h2>
          <p class="github-callback__message">{{ errorMessage }}</p>
          <button class="github-callback__button" @click="goToLogin">Вернуться на страницу входа</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()

const loading = ref(true)
const error = ref(false)
const errorMessage = ref('')

onMounted(async () => {
  try {
    // Проверяем наличие ошибки в URL
    if (route.query.error) {
      error.value = true
      errorMessage.value = route.query.message as string || 'Ошибка авторизации через GitHub'
      loading.value = false
      return
    }

    // Проверяем наличие данных в URL
    if (route.query.data) {
      const decodedData = decodeURIComponent(route.query.data as string)
      const userInfo = JSON.parse(decodedData)

      console.log('📦 Received GitHub user data:', userInfo)

      if (userInfo.token) {
        // Сохраняем токен
        userStore.setToken(userInfo.token)

        // Получаем данные пользователя
        await userStore.fetchUser()

        // Перенаправляем на главную
        router.push('/')
      } else {
        throw new Error('Не получен токен авторизации')
      }
    } else {
      throw new Error('Не получены данные авторизации')
    }
  } catch (e: any) {
    console.error('Error in GitHubCallback:', e)
    error.value = true
    errorMessage.value = e.message || 'Ошибка при обработке данных'
    loading.value = false
  }
})

const goToLogin = () => {
  router.push('/login')
}
</script>

<style lang="scss" src="@/assets/styles/gitHubCallback.scss" scoped>
</style>