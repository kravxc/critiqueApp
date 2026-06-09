
<template>
  <div class="google-callback">
    <div class="google-callback__container">
      <div class="google-callback__card">
        <div v-if="loading" class="google-callback__loading">
          <div class="google-callback__spinner"></div>
          <h2 class="google-callback__title">Авторизация через Google</h2>
        </div>

        <div v-else-if="error" class="google-callback__error">
          <div class="google-callback__error-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h2 class="google-callback__title">Ошибка авторизации</h2>
          <p class="google-callback__message">{{ errorMessage }}</p>
          <button class="google-callback__button" @click="goToLogin">Вернуться на страницу входа</button>
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
    if (route.query.error) {
      error.value = true
      errorMessage.value = route.query.message as string || 'Ошибка авторизации через Google'
      loading.value = false
      return
    }

    if (route.query.data) {
      const decodedData = decodeURIComponent(route.query.data as string)
      const userInfo = JSON.parse(decodedData)

      console.log('📦 Received Google user data:', userInfo)

      if (userInfo.token) {
        userStore.setToken(userInfo.token)

        await userStore.fetchUser()

        router.push('/')
      } else {
        throw new Error('Не получен токен авторизации')
      }
    } else {
      throw new Error('Не получены данные авторизации')
    }
  } catch (e: any) {
    console.error('Error in GoogleCallback:', e)
    error.value = true
    errorMessage.value = e.message || 'Ошибка при обработке данных'
    loading.value = false
  }
})

const goToLogin = () => {
  router.push('/login')
}
</script>

<style lang="scss" src="@/assets/styles/googleCallback.scss" scoped>
</style>