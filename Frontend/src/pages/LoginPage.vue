<script setup>
// Login page authenticates user against backend and stores bearer token automatically.
import { reactive, ref } from "vue";
import { useRoute, useRouter, RouterLink } from "vue-router";
import InlineAlert from "../components/InlineAlert.vue";
import { loginUser, requestPasswordReset } from "../services/authService";

const router = useRouter();
const route = useRoute();

const form = reactive({
    email: "",
    password: "",
});

const loading = ref(false);
const resetLoading = ref(false);
const showForgotPanel = ref(false);
const resetEmail = ref("");
const errorMessage = ref("");
const successMessage = ref("");

// Submits credentials and redirects to intended route when login succeeds.
async function submitLogin() {
    loading.value = true;
    errorMessage.value = "";
    successMessage.value = "";

    try {
        await loginUser({ ...form });
        successMessage.value = "Login successful. Redirecting...";

        const nextPath = String(route.query.next || "/");
        await router.push(nextPath);
    } catch (error) {
        errorMessage.value =
            error?.message || "Login failed. Check your credentials.";
    } finally {
        loading.value = false;
    }
}

// Sends forgot-password request so backend can email a reset link.
async function submitForgotPassword() {
    resetLoading.value = true;
    errorMessage.value = "";
    successMessage.value = "";

    try {
        const emailToUse = resetEmail.value || form.email;
        await requestPasswordReset(emailToUse);
        successMessage.value =
            "If that email exists, a reset link has been sent.";
    } catch (error) {
        errorMessage.value =
            error?.message || "Could not request password reset right now.";
    } finally {
        resetLoading.value = false;
    }
}

// Toggles forgot-password panel and pre-fills email from login form.
function toggleForgotPanel() {
    showForgotPanel.value = !showForgotPanel.value;
    resetEmail.value = form.email;
}
</script>

<template>
    <section class="auth-page">
        <article class="auth-card glass-card">
            <h2 class="section-title">Sign In</h2>
            <p class="muted">
            Login to your account to start generating cinematic prompts with AI. 
                </p>

            <InlineAlert
                v-if="errorMessage"
                type="error"
                :message="errorMessage"
            />
            <InlineAlert
                v-if="successMessage"
                type="success"
                :message="successMessage"
            />

            <form class="auth-form" @submit.prevent="submitLogin">
                <div class="field">
                    <label for="login-email">Email</label>
                    <input
                        id="login-email"
                        v-model="form.email"
                        class="input"
                        type="email"
                        required
                    />
                </div>

                <div class="field">
                    <label for="login-password">Password</label>
                    <input
                        id="login-password"
                        v-model="form.password"
                        class="input"
                        type="password"
                        required
                    />
                </div>

                <!-- <button
                    class="forgot-link"
                    type="button"
                    @click="toggleForgotPanel"
                >
                    {{
                        showForgotPanel
                            ? "Hide forgot password"
                            : "Forgot password?"
                    }}
                </button>

                <div v-if="showForgotPanel" class="forgot-panel">
                    <label for="reset-email">Reset email</label>
                    <input
                        id="reset-email"
                        v-model="resetEmail"
                        class="input"
                        type="email"
                        required
                    />
                    <button
                        class="btn btn-secondary"
                        type="button"
                        :disabled="resetLoading"
                        @click="submitForgotPassword"
                    >
                        {{
                            resetLoading
                                ? "Sending reset link..."
                                : "Send reset link"
                        }}
                    </button> 
                </div>-->

                <button
                    class="btn btn-primary"
                    type="submit"
                    :disabled="loading"
                >
                    {{ loading ? "Signing in..." : "Sign In" }}
                </button>
            </form>

            <p class="switch-link">
                No account yet?
                <RouterLink to="/register">Create one</RouterLink>
            </p>
        </article>
    </section>
</template>

<style scoped>
.auth-page {
    display: grid;
    place-items: center;
    min-height: calc(100vh - 190px);
}

.auth-card {
    width: min(500px, 100%);
    padding: 1.2rem;
}

.auth-form {
    margin-top: 1rem;
    display: grid;
    gap: 0.8rem;
}

.field label {
    display: block;
    margin-bottom: 0.3rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-secondary);
}

.switch-link {
    margin: 0.9rem 0 0;
    color: var(--text-secondary);
}

.switch-link a {
    color: var(--accent);
    font-weight: 700;
}

.forgot-link {
    width: fit-content;
    border: none;
    background: none;
    color: var(--accent);
    font-weight: 600;
    padding: 0;
    margin-top: -0.25rem;
    cursor: pointer;
}

.forgot-panel {
    border: 1px solid var(--stroke-soft);
    background: var(--surface-muted);
    border-radius: 12px;
    padding: 0.7rem;
    display: grid;
    gap: 0.55rem;
}

.forgot-panel label {
    color: var(--text-secondary);
    font-size: 0.82rem;
    font-weight: 600;
}
</style>
