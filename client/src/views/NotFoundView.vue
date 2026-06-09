<template>
  <div class="not-found">
    <div class="not-found__background">
      <img :src="bgImage" alt="404 background" />
      <div class="not-found__overlay"></div>
    </div>

    <div class="not-found__container">

      <div class="not-found__vinyl">
        <div class="vinyl">
          <div class="vinyl__inner">
            <div class="vinyl__label">
              <span class="vinyl__number">4</span>
              <span class="vinyl__number">0</span>
              <span class="vinyl__number">4</span>
            </div>
          </div>
          <div class="vinyl__shine"></div>
        </div>
        <div class="vinyl__shadow"></div>
      </div>

      <h1 class="not-found__title">
        <span class="not-found__title-main">СТРАНИЦА НЕ НАЙДЕНА</span>
      </h1>


      <router-link to="/" class="not-found__button">
        <span class="not-found__button-text">Вернуться к музыке</span>
        <svg class="not-found__button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
const bgImage = 'https://images.unsplash.com/photo-1511379938547-c1f69419868d?q=80&w=2070&auto=format&fit=crop'
</script>

<style lang="scss" src="@/assets/styles/notFound.scss" scoped>

</style>