<script setup>
// Shared header that provides navigation and auth session actions.
import { computed, onMounted, onUnmounted, ref } from "vue";
import { useRouter } from "vue-router";
import { getToken } from "../services/apiClient";
import { logoutUser } from "../services/authService";

const emit = defineEmits(["notify", "toggle-theme"]);
defineProps({
    theme: {
        type: String,
        default: "dark",
    },
});

const router = useRouter();

const authToken = ref(getToken());
const logoutLoading = ref(false);

// Reactive auth state driven by token storage.
const isAuthenticated = computed(() => Boolean(authToken.value));

// Syncs local token snapshot with storage changes.
function syncTokenState() {
    authToken.value = getToken();
}

// Revokes token on backend and redirects to login.
async function handleLogout() {
    logoutLoading.value = true;

    try {
        await logoutUser();
        emit("notify", "Logged out successfully.", "success");
        await router.push("/login");
    } catch (error) {
        emit("notify", error?.message || "Logout failed.", "error");
    } finally {
        logoutLoading.value = false;
    }
}

onMounted(() => {
    window.addEventListener("auth-token-changed", syncTokenState);
});

onUnmounted(() => {
    window.removeEventListener("auth-token-changed", syncTokenState);
});
</script>

<template>
    <header class="header-wrap">
        <div class="container header-inner glass-card">
            <div class="brand-area">
                <div class="brand-mark">
                    <img
                        src="/Prompty.png"
                        alt="prompty logo"
                        class="brand-logo"
                    />
                    <p class="brand-kicker">Prompty</p>
                </div>
                <h1 class="brand-title">Image Prompt Generator</h1>
            </div>

            <div class="token-area">
                <p class="token-state" :class="isAuthenticated ? 'ok' : 'warn'">
                    {{ isAuthenticated ? "Authenticated" : "Guest" }}
                </p>
                <button
                    class="btn btn-ghost theme-toggle"
                    type="button"
                    :aria-label="
                        theme === 'dark'
                            ? 'Switch to light mode'
                            : 'Switch to dark mode'
                    "
                    @click="$emit('toggle-theme')"
                >
                    <svg
                        v-if="theme === 'dark'"
                        class="theme-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true"
                    >
                        <circle
                            cx="12"
                            cy="12"
                            r="4"
                            stroke="currentColor"
                            stroke-width="1.8"
                        />
                        <path
                            d="M12 2V4.2M12 19.8V22M4.9 4.9L6.45 6.45M17.55 17.55L19.1 19.1M2 12H4.2M19.8 12H22M4.9 19.1L6.45 17.55M17.55 6.45L19.1 4.9"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                        />
                    </svg>
                    <svg
                        v-else
                        class="theme-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true"
                    >
                        <path
                            d="M20.4 14.2C19.5 14.6 18.5 14.8 17.5 14.8C13.4 14.8 10.2 11.6 10.2 7.5C10.2 6.5 10.4 5.5 10.8 4.6C7.3 5.2 4.7 8.2 4.7 11.9C4.7 16 8 19.3 12.1 19.3C15.8 19.3 18.8 16.7 19.4 13.2C19.1 13.6 18.9 14 18.7 14.4"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                    <span class="theme-text">
                        {{ theme === "dark" ? "Light Mode" : "Dark Mode" }}
                    </span>
                </button>
                <button
                    v-if="isAuthenticated"
                    class="btn btn-secondary logout-btn"
                    type="button"
                    :disabled="logoutLoading"
                    :aria-label="logoutLoading ? 'Logging out' : 'Logout'"
                    @click="handleLogout"
                >
                    <svg
                        class="logout-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true"
                    >
                        <path
                            d="M15 7V5.8C15 4.81 14.19 4 13.2 4H7.8C6.81 4 6 4.81 6 5.8V18.2C6 19.19 6.81 20 7.8 20H13.2C14.19 20 15 19.19 15 18.2V17"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M10.5 12H21M21 12L17.8 8.8M21 12L17.8 15.2"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                    <span class="logout-text">
                        {{ logoutLoading ? "Logging out..." : "Logout" }}
                    </span>
                </button>
            </div>
        </div>
    </header>
</template>

<style scoped>
.header-wrap {
    position: sticky;
    top: 0;
    z-index: 30;
    padding: 1rem 0 0;
}

.header-inner {
    padding: 1rem;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 1rem;
    align-items: end;
}

.brand-kicker {
    margin: 0;
    font-weight: 600;
    color: var(--accent);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 0.75rem;
}

.brand-mark {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
}

.brand-logo {
    width: 20px;
    height: 20px;
    object-fit: contain;
}

.brand-title {
    margin: 0.2rem 0 0;
    font-family: var(--font-heading);
    font-size: clamp(1rem, 2vw, 1.45rem);
}

.token-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-secondary);
}

.token-state {
    margin: 0 0 0.4rem;
    font-size: 0.85rem;
    font-weight: 700;
}

.token-state.ok {
    color: var(--status-success);
}

.token-state.warn {
    color: var(--status-warning);
}

.token-area {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.theme-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
}

.theme-icon {
    width: 1rem;
    height: 1rem;
}

.logout-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
}

.logout-icon {
    width: 1rem;
    height: 1rem;
}

@media (max-width: 940px) {
    .header-inner {
        grid-template-columns: 1fr;
        align-items: start;
    }

    .token-area {
        justify-content: flex-start;
    }
}

@media (max-width: 640px) {
    .header-wrap {
        padding-top: 0.6rem;
    }

    .header-inner {
        grid-template-columns: 1fr auto;
        align-items: center;
        gap: 0.6rem;
        padding: 0.7rem 0.8rem;
    }

    .brand-mark {
        gap: 0.38rem;
    }

    .brand-kicker {
        font-size: 0.72rem;
        letter-spacing: 0.06em;
    }

    .brand-logo {
        width: 18px;
        height: 18px;
    }

    .brand-title {
        display: none;
    }

    .token-area {
        flex-wrap: nowrap;
        gap: 0.35rem;
    }

    .token-state {
        margin: 0;
        font-size: 0.74rem;
        white-space: nowrap;
    }

    .btn {
        padding: 0.5rem 0.62rem;
        font-size: 0.78rem;
    }

    .theme-toggle {
        width: 2.1rem;
        height: 2.1rem;
        justify-content: center;
        padding: 0;
        border-radius: 999px;
    }

    .theme-text {
        display: none;
    }

    .logout-btn {
        width: 2.1rem;
        height: 2.1rem;
        justify-content: center;
        padding: 0;
        border-radius: 999px;
        gap: 0;
    }

    .logout-text {
        display: none;
    }
}
</style>
