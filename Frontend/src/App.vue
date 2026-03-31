<script setup>
// App shell that holds shared layout across frontend pages.
import { computed, onMounted, onUnmounted, ref } from "vue";
import { RouterLink, RouterView, useRoute } from "vue-router";
import AppHeader from "./components/AppHeader.vue";
import InlineAlert from "./components/InlineAlert.vue";
import { getToken } from "./services/apiClient";

const THEME_STORAGE_KEY = "chatyai_theme";
const theme = ref("dark");
const route = useRoute();
const authToken = ref(getToken());

// Reactively tracks signed-in state for centered route tabs.
const isAuthenticated = computed(() => Boolean(authToken.value));

// Applies active class to current centered tab.
function tabClass(path) {
    return route.path === path ? "center-tab active" : "center-tab";
}

// Keeps auth state synchronized after login/logout events.
function syncTokenState() {
    authToken.value = getToken();
}

// Reads initial theme from storage or system preference.
function resolveInitialTheme() {
    const savedTheme = localStorage.getItem(THEME_STORAGE_KEY);
    if (savedTheme === "light" || savedTheme === "dark") {
        return savedTheme;
    }

    return window.matchMedia("(prefers-color-scheme: light)").matches
        ? "light"
        : "dark";
}

// Applies selected theme to root html data attribute.
function applyTheme(nextTheme) {
    theme.value = nextTheme;
    document.documentElement.setAttribute("data-theme", nextTheme);
    localStorage.setItem(THEME_STORAGE_KEY, nextTheme);
}

// Toggles between dark and light modes.
function toggleTheme() {
    applyTheme(theme.value === "dark" ? "light" : "dark");
}
// App-wide message for lightweight feedback from header actions.
const appMessage = ref("");
const appMessageType = ref("info");

// Displays a timed global notification.
function notify(message, type = "info") {
    appMessage.value = message;
    appMessageType.value = type;

    window.setTimeout(() => {
        appMessage.value = "";
    }, 2200);
}

onMounted(() => {
    applyTheme(resolveInitialTheme());
    window.addEventListener("auth-token-changed", syncTokenState);
});

onUnmounted(() => {
    window.removeEventListener("auth-token-changed", syncTokenState);
});
</script>

<template>
    <div class="app-shell">
        <AppHeader
            :theme="theme"
            @notify="notify"
            @toggle-theme="toggleTheme"
        />

        <section
            class="center-tabs-wrap container"
            aria-label="Section navigation"
        >
            <nav class="center-tabs glass-card">
                <template v-if="isAuthenticated">
                    <RouterLink :class="tabClass('/')" to="/"
                        >Upload</RouterLink
                    >
                    <RouterLink :class="tabClass('/history')" to="/history"
                        >History</RouterLink
                    >
                    <RouterLink :class="tabClass('/profile')" to="/profile"
                        >Profile</RouterLink
                    >
                </template>
                <template v-else>
                    <RouterLink :class="tabClass('/login')" to="/login"
                        >Login</RouterLink
                    >
                    <RouterLink :class="tabClass('/register')" to="/register"
                        >Register</RouterLink
                    >
                </template>
            </nav>
        </section>

        <main class="app-main container">
            <InlineAlert
                v-if="appMessage"
                :type="appMessageType"
                :message="appMessage"
            />
            <RouterView />
        </main>
    </div>
</template>

<style scoped>
.center-tabs-wrap {
    margin-top: 1rem;
}

.center-tabs {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.6rem;
    padding: 0.45rem;
    width: fit-content;
    margin: 0 auto;
}

.center-tab {
    text-decoration: none;
    color: var(--text-secondary);
    border: 1px solid var(--stroke-soft);
    background: var(--surface-muted);
    border-radius: 999px;
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    font-weight: 600;
    transition: all 0.25s ease;
}

.center-tab:hover {
    border-color: var(--stroke-strong);
    color: var(--text-main);
    transform: translateY(-1px);
}

.center-tab.active {
    color: var(--text-main);
    border-color: transparent;
    background: linear-gradient(135deg, var(--accent), var(--accent-strong));
    box-shadow: 0 10px 24px rgba(124, 58, 237, 0.33);
}

@media (max-width: 640px) {
    .center-tabs-wrap {
        margin-top: 1.35rem;
    }

    .center-tabs {
        width: 100%;
    }

    .center-tab {
        flex: 1;
        text-align: center;
    }
}
</style>
