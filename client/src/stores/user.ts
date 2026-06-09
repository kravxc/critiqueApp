import { defineStore } from 'pinia'
import { ref } from 'vue'
import Cookies from 'js-cookie'

const API_URL = import.meta.env.VITE_API_URL + '/api'|| "http://localhost:8000/api";

export const useUserStore = defineStore('user', () => {
  const user = ref<any>(null)
  const isAuthenticated = ref(false)
  const isLoading = ref(false)
  const isInitialized = ref(false)

  const initFromCookies = () => {
    const token = Cookies.get('auth_token')
    const userData = Cookies.get('user_data')

    if (token && userData) {
      try {
        user.value = JSON.parse(userData)
        isAuthenticated.value = true
      } catch (e) {
        console.error('Error parsing user data from cookies:', e)
        Cookies.remove('auth_token')
        Cookies.remove('user_data')
        isAuthenticated.value = false
        user.value = null
      }
    } else {
      isAuthenticated.value = false
      user.value = null
    }
    isInitialized.value = true
  }

  initFromCookies()

  const setToken = (token: string) => {
    Cookies.set('auth_token', token, {
      expires: 7,
      secure: false,
      sameSite: 'lax',
      path: '/'
    })
  }

  const setUser = (userData: any, token: string) => {
    console.log('Setting user in store:', userData)
    user.value = userData
    isAuthenticated.value = true

    Cookies.set('auth_token', token, {
      expires: 7,
      secure: false,
      sameSite: 'lax',
      path: '/'
    })

    Cookies.set('user_data', JSON.stringify(userData), {
      expires: 7,
      secure: false,
      sameSite: 'lax',
      path: '/'
    })
  }

  const clearUser = () => {
    user.value = null
    isAuthenticated.value = false
    Cookies.remove('auth_token')
    Cookies.remove('user_data')
  }

  const fetchUser = async () => {
    isLoading.value = true
    console.log('Fetching user from server...')

    try {
      const token = Cookies.get('auth_token')

      if (!token) {
        console.log('No token found')
        clearUser()
        isLoading.value = false
        return
      }

      console.log('Token found, fetching user data')
      const response = await fetch(`${API_URL}/api/user`, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      })

      const data = await response.json()
      console.log('User data response:', data)

      if (response.ok) {
        let userData = null

        if (data.data) {
          userData = data.data
        }
        else if (data.user) {
          userData = data.user
        }
        else if (data.id) {
          userData = data
        }

        if (userData) {
          console.log('User authenticated:', userData)
          isAuthenticated.value = true
          user.value = userData

          Cookies.set('user_data', JSON.stringify(userData), {
            expires: 7,
            secure: false,
            sameSite: 'lax',
            path: '/'
          })
        } else {
          clearUser()
        }
      } else {
        clearUser()
      }
    } catch (error) {
      console.error('Fetch user error:', error)
      clearUser()
    } finally {
      isLoading.value = false
    }
  }

  const logout = async () => {
    try {
      const token = Cookies.get('auth_token')

      if (token) {
        console.log('Logging out...')
        await fetch(`${API_URL}/logout`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        })
      }
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      clearUser()
    }
  }

  return {
    user,
    isAuthenticated,
    isLoading,
    isInitialized,
    setToken,
    setUser,
    clearUser,
    fetchUser,
    logout
  }
})