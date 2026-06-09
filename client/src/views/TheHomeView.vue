<template>
  <div class="home">
    <section class="hero" :class="{ 'hero--loaded': heroLoaded }">
      <div class="hero__background">
        <img :src="heroImage" alt="hero background" @load="heroLoaded = true" />
        <div class="hero__overlay"></div>
      </div>

      <div class="hero__content">
        <h1 class="hero__title" :style="{ transitionDelay: '0ms' }">
          Музыка, которую <br />
          <span class="gradient-text">стоит услышать</span>
        </h1>

        <p class="hero__subtitle" :style="{ transitionDelay: '200ms' }">
          Честные рецензии, глубокие разборы и сообщество ценителей музыки.
          Присоединяйтесь к дискуссии о том, что звучит сегодня.
        </p>

        <div class="hero__actions" :style="{ transitionDelay: '400ms' }">
          <router-link to="/reviews" class="btn btn--primary">
            Читать рецензии
          </router-link>
          <router-link to="/write" class="btn btn--outline">
            Написать свою
          </router-link>
        </div>
      </div>

      <button
        class="hero__scroll"
        @click="scrollToReviews"
        aria-label="Прокрутить вниз"
      >
        <span></span>
      </button>
    </section>

    <section id="reviews" class="reviews" ref="reviewsSection">
      <div class="container">
        <div
          class="section-header"
          :class="{ 'section-header--visible': reviewsVisible }"
        >
          <h2 class="section-title">Популярные рецензии</h2>
          <p class="section-subtitle">Лучшее по мнению сообщества</p>
        </div>

        <div v-if="isLoading" class="reviews__grid">
          <div
            v-for="i in 6"
            :key="i"
            class="review-card review-card--skeleton"
          >
            <div class="review-card__content">
              <div class="review-card__meta">
                <div class="skeleton skeleton--small"></div>
                <div class="skeleton skeleton--small"></div>
              </div>
              <div class="skeleton skeleton--title"></div>
              <div class="skeleton skeleton--text"></div>
              <div class="skeleton skeleton--text"></div>
              <div class="review-card__footer">
                <div class="review-card__author">
                  <div class="skeleton skeleton--avatar"></div>
                  <div class="skeleton skeleton--small"></div>
                </div>
                <div class="skeleton skeleton--small"></div>
              </div>
            </div>
          </div>
        </div>

        <div v-else-if="reviews.length > 0" class="reviews__grid">
          <TransitionGroup
            name="card"
            @before-enter="beforeCardEnter"
            @enter="cardEnter"
          >
            <article
              v-for="(review, index) in displayedReviews"
              :key="review.id"
              class="review-card"
              :data-index="index"
            >
              <div class="review-card__content">
                <div class="review-card__meta">
                  <span class="review-card__author-name">{{
                    review.user?.name || "Пользователь"
                  }}</span>
                  <span class="review-card__date">{{
                    formatDate(review.created_at)
                  }}</span>
                </div>

                <h3 class="review-card__title">{{ review.title }}</h3>
                <p class="review-card__excerpt">
                  {{ truncateText(review.text, 150) }}
                </p>

                <div class="review-card__footer">
                  <div class="review-card__likes">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                      <path
                        d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"
                      />
                    </svg>
                    {{ review.likes_count }}
                  </div>
                </div>
              </div>
            </article>
          </TransitionGroup>
        </div>

        <div v-else-if="error" class="reviews__error">
          <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <p>{{ error }}</p>
          <button @click="fetchPopularReviews" class="btn btn--outline">
            Попробовать снова
          </button>
        </div>

        <div v-else class="reviews__empty">
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
          <p>Пока нет популярных рецензий</p>
        </div>
      </div>
    </section>

    <section id="features" class="features" ref="featuresSection">
      <div class="container">
        <div
          class="section-header"
          :class="{ 'section-header--visible': featuresVisible }"
        >
          <h2 class="section-title">Почему выбирают Наше мнение?</h2>
          <p class="section-subtitle">
            Мы создаем пространство для истинных ценителей звука
          </p>
        </div>

        <div class="features__grid">
          <div
            v-for="(feature, index) in features"
            :key="feature.id"
            class="feature-card"
            :class="{ 'feature-card--visible': featuresVisible }"
            :style="{ transitionDelay: `${index * 200}ms` }"
          >
            <div class="feature-card__icon" v-html="feature.icon"></div>
            <h3>{{ feature.title }}</h3>
            <p>{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <section
      class="cta"
      ref="ctaSection"
      :class="{ 'cta--visible': ctaVisible }"
    >
      <div class="container">
        <div class="cta__content">
          <h2>Готовы поделиться мнением?</h2>
          <p>Присоединяйтесь к тысячам меломанов уже сегодня</p>
          <router-link to="/write" class="btn btn--primary btn--large">
            Начать писать
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";

interface Review {
  id: number;
  title: string;
  text: string;
  likes_count: number;
  user: {
    id: number;
    name: string;
  };
  created_at: string;
}

const router = useRouter();
const heroLoaded = ref(false);
const reviewsVisible = ref(false);
const featuresVisible = ref(false);
const ctaVisible = ref(false);

const isLoading = ref(true);
const error = ref<string | null>(null);
const reviews = ref<Review[]>([]);

const reviewsSection = ref<HTMLElement | null>(null);
const featuresSection = ref<HTMLElement | null>(null);
const ctaSection = ref<HTMLElement | null>(null);

const heroImage =
  "https://images.unsplash.com/photo-1429962714451-bb934ecdc4ec?q=80&w=2070&auto=format&fit=crop";

const API_URL =
  import.meta.env.VITE_API_URL + "/api" || "http://localhost:8000/api";

const displayedReviews = computed(() => {
  return reviews.value.slice(0, 6);
});

const fetchPopularReviews = async () => {
  isLoading.value = true;
  error.value = null;

  try {
    const response = await fetch(`${API_URL}/reviews/popular`, {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
    });

    const data = await response.json();
    console.log("Popular reviews response:", data);

    if (response.ok && data.data) {
      if (Array.isArray(data.data)) {
        reviews.value = data.data;
      } else {
        reviews.value = [];
      }
    } else {
      throw new Error(data.message || "Ошибка при загрузке рецензий");
    }
  } catch (err: any) {
    console.error("Error fetching popular reviews:", err);
    error.value = err.message || "Не удалось загрузить популярные рецензии";
  } finally {
    isLoading.value = false;
  }
};

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  const now = new Date();
  const diffTime = Math.abs(now.getTime() - date.getTime());
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

  if (diffDays === 1) return "вчера";
  if (diffDays < 7) return `${diffDays} дня назад`;

  return date.toLocaleDateString("ru-RU", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
};

const truncateText = (text: string, maxLength: number) => {
  if (text.length <= maxLength) return text;
  return text.slice(0, maxLength) + "...";
};

const goToReview = (review: Review) => {
  router.push(`/reviews/${review.id}`);
};

const scrollToReviews = () => {
  document.getElementById("reviews")?.scrollIntoView({ behavior: "smooth" });
};

const beforeCardEnter = (el: Element) => {
  (el as HTMLElement).style.opacity = "0"(el as HTMLElement).style.transform =
    "translateY(30px)";
};

const cardEnter = (el: Element, done: () => void) => {
  const delay = ((el as HTMLElement).dataset.index || 0) * 100;
  setTimeout(() => {
    (el as HTMLElement).style.transition =
      "all 0.5s ease"(el as HTMLElement).style.opacity =
      "1"(el as HTMLElement).style.transform =
        "translateY(0)";
    done();
  }, delay);
};

onMounted(() => {
  fetchPopularReviews();

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          if (entry.target.id === "reviews") {
            reviewsVisible.value = true;
          } else if (entry.target.id === "features") {
            featuresVisible.value = true;
          } else if (entry.target.classList.contains("cta")) {
            ctaVisible.value = true;
          }
        }
      });
    },
    { threshold: 0.3 },
  );

  if (reviewsSection.value) observer.observe(reviewsSection.value);
  if (featuresSection.value) observer.observe(featuresSection.value);
  if (ctaSection.value) observer.observe(ctaSection.value);
});

const features = ref([
  {
    id: 1,
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>`,
    title: "Независимые мнения",
    description:
      "Никакой цензуры и влияния лейблов. Только честные эмоции и профессиональный анализ.",
  },
  {
    id: 2,
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>`,
    title: "Активное сообщество",
    description:
      "Обсуждайте альбомы, спорьте о треках и находите единомышленников в комментариях.",
  },
  {
    id: 3,
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>`,
    title: "Экспертный подход",
    description:
      "Наши авторы — музыканты, критики и журналисты с многолетним стажем.",
  },
]);
</script>

<style lang="scss" src="@/assets/styles/home.scss" scoped></style>
