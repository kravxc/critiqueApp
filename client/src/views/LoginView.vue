<template>
  <div class="login">
    <div class="login__background">
      <img :src="bgImage" alt="login background" />
      <div class="login__overlay"></div>
    </div>

    <div class="login__container">
      <div class="login__card">

        <div class="login__header">
          <router-link to="/" class="login__logo">
            Наше<span class="gradient-text"> мнение</span>
          </router-link>
          <h1 class="login__title">Войти в аккаунт</h1>
          <p class="login__subtitle">Добро пожаловать обратно!</p>
        </div>

        <form @submit.prevent="handleSubmit" class="login__form">
          <div class="form-group">
            <label for="email" class="form-label">
              <svg class="form-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              Email
            </label>
            <div class="input-wrapper">
              <input
                type="email"
                id="email"
                v-model="form.email"
                @blur="validateField('email')"
                @input="clearFieldError('email')"
                class="form-input"
                :class="{
                  'form-input--error': errors.email,
                  'form-input--server-error': serverErrorField === 'email'
                }"
                placeholder="your@email.com"
              />
            </div>
            <span v-if="errors.email" class="form-error">{{ errors.email }}</span>
          </div>

          <div class="form-group">
            <label for="password" class="form-label">
              <svg class="form-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              Пароль
            </label>
            <div class="password-input-wrapper">
              <input
                :type="showPassword ? 'text' : 'password'"
                id="password"
                v-model="form.password"
                @blur="validateField('password')"
                @input="clearFieldError('password')"
                class="form-input"
                :class="{
                  'form-input--error': errors.password,
                  'form-input--server-error': serverErrorField === 'password'
                }"
                placeholder="Введите пароль"
              />
              <button
                type="button"
                class="password-toggle"
                @click="showPassword = !showPassword"
              >
                <svg v-if="showPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
              </button>
            </div>
            <span v-if="errors.password" class="form-error">{{ errors.password }}</span>
          </div>

          <div v-if="serverError" class="server-error">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ serverError }}
          </div>

          <button
            type="submit"
            class="btn btn--primary btn--large login__submit"
            :disabled="isSubmitting"
          >
            <span v-if="isSubmitting" class="spinner"></span>
            <span v-else>Войти</span>
          </button>
        </form>

        <div class="login__divider">
          <span class="login__divider-line"></span>
          <span class="login__divider-text">или</span>
          <span class="login__divider-line"></span>
        </div>

        <div class="login__social">
          <button class="social-btn social-btn--google" @click="handleGoogleLogin" :disabled="isSocialLoading">
            <span v-if="isSocialLoading && socialProvider === 'google'" class="spinner spinner--small"></span>
            <svg v-else class="social-btn__icon" viewBox="0 0 24 24">
              <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
              <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
              <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
              <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
            </svg>
            <span class="social-btn__text">Google</span>
          </button>

          <button class="social-btn social-btn--github" @click="handleGithubLogin" :disabled="isSocialLoading">
            <span v-if="isSocialLoading && socialProvider === 'github'" class="spinner spinner--small"></span>
            <svg v-else class="social-btn__icon" viewBox="0 0 24 24">
              <path fill="currentColor" d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.87 8.17 6.84 9.5.5.08.66-.23.66-.5v-1.69c-2.77.6-3.36-1.34-3.36-1.34-.46-1.16-1.11-1.47-1.11-1.47-.91-.62.07-.6.07-.6 1 .07 1.53 1.03 1.53 1.03.87 1.52 2.34 1.07 2.91.83.09-.65.35-1.09.63-1.34-2.22-.25-4.55-1.11-4.55-4.92 0-1.11.38-2 1.03-2.71-.1-.25-.45-1.29.1-2.64 0 0 .84-.27 2.75 1.02.79-.22 1.65-.33 2.5-.33.85 0 1.71.11 2.5.33 1.91-1.29 2.75-1.02 2.75-1.02.55 1.35.2 2.39.1 2.64.65.71 1.03 1.6 1.03 2.71 0 3.82-2.34 4.66-4.57 4.91.36.31.69.92.69 1.85V21c0 .27.16.59.67.5C19.14 20.16 22 16.42 22 12A10 10 0 0 0 12 2z" />
            </svg>
            <span class="social-btn__text">GitHub</span>
          </button>
        </div>

        <div class="login__footer">
          <p class="login__register">
            Нет аккаунта? <router-link to="/register" class="register-link">Зарегистрироваться</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'

const router = useRouter()
const userStore = useUserStore()

const bgImage = 'https://images.unsplash.com/photo-1524678606370-a47ad25cb82a?q=80&w=2069&auto=format&fit=crop'

const form = reactive({
  email: '',
  password: ''
})

const errors = reactive({
  email: '',
  password: ''
})

const serverErrorField = ref<'email' | 'password' | null>(null)

const showPassword = ref(false)
const isSubmitting = ref(false)
const serverError = ref('')

const isSocialLoading = ref(false)
const socialProvider = ref<'google' | 'github' | null>(null)

const API_URL = import.meta.env.VITE_API_URL + '/api' || 'http://localhost:8000/api'

const validateField = (field: string) => {
  switch (field) {
    case 'email':
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!form.email) {
        errors.email = 'Email обязателен для заполнения'
      } else if (!emailRegex.test(form.email)) {
        errors.email = 'Введите корректный email адрес'
      } else {
        errors.email = ''
      }
      break

    case 'password':
      if (!form.password) {
        errors.password = 'Пароль обязателен для заполнения'
      } else {
        errors.password = ''
      }
      break
  }
}

const clearFieldError = (field: string) => {
  if (field === 'email') {
    errors.email = ''
    serverErrorField.value = null
  }
  if (field === 'password') {
    errors.password = ''
    serverErrorField.value = null
  }
}

const validateForm = () => {
  validateField('email')
  validateField('password')

  return !errors.email && !errors.password
}

const handleGoogleLogin = async () => {
  try {
    isSocialLoading.value = true
    socialProvider.value = 'google'
    serverError.value = ''

    const response = await fetch(`${API_URL}/auth/google`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    })

    const data = await response.json()
    console.log('Google auth response:', data)

    if (data.redirect_url) {

      localStorage.setItem('oauth_action', 'login')
      localStorage.setItem('oauth_service', 'google')

      window.location.href = data.redirect_url
    } else {
      throw new Error('Не получен URL для перенаправления')
    }
  } catch (error: any) {
    console.error('Google auth error:', error)
    serverError.value = error.message || 'Ошибка при авторизации через Google'
    isSocialLoading.value = false
    socialProvider.value = null
  }
}

const handleGithubLogin = async () => {
  try {
    isSocialLoading.value = true
    socialProvider.value = 'github'
    serverError.value = ''

    const response = await fetch(`${API_URL}/auth/github`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    })

    const data = await response.json()
    console.log('GitHub auth response:', data)

    if (data.redirect_url) {
      localStorage.setItem('oauth_action', 'login')
      localStorage.setItem('oauth_service', 'github')

      window.location.href = data.redirect_url
    } else {
      throw new Error('Не получен URL для перенаправления')
    }
  } catch (error: any) {
    console.error('GitHub auth error:', error)
    serverError.value = error.message || 'Ошибка при авторизации через GitHub'
    isSocialLoading.value = false
    socialProvider.value = null
  }
}

const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }

  isSubmitting.value = true
  serverError.value = ''
  serverErrorField.value = null

  try {
    const response = await fetch(`${API_URL}/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        email: form.email,
        password: form.password
      })
    })

    const data = await response.json()
    console.log('Login response:', data)

    if (!response.ok) {
      if (data.errors) {
        if (data.errors.email) {
          errors.email = data.errors.email[0]
          serverErrorField.value = 'email'
        }
        if (data.errors.password) {
          errors.password = data.errors.password[0]
          serverErrorField.value = 'password'
        }
        serverError.value = data.message || 'Ошибка валидации'
      } else if (data.error) {
        serverError.value = data.error
        if (data.error.toLowerCase().includes('email') ||
          data.error.toLowerCase().includes('пароль') ||
          data.error === 'Неверный email или пароль') {
          serverErrorField.value = 'email'
        }
      } else if (data.message) {
        serverError.value = data.message
        if (data.message.toLowerCase().includes('email')) {
          serverErrorField.value = 'email'
        }
      } else {
        serverError.value = 'Произошла ошибка при входе в систему'
      }
      return
    }

    if (data.data?.token) {
      userStore.setToken(data.data.token)

      await userStore.fetchUser()

      setTimeout(() => {
        router.push('/')
      }, 100)
    }

  } catch (error: any) {
    console.error('Login error:', error)
    serverError.value = error.message || 'Произошла ошибка при входе. Попробуйте позже.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style lang="scss" src="@/assets/styles/login.scss" scoped>

</style>