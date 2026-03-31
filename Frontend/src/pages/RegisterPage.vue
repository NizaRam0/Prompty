<script setup>
// Register page creates account and then performs automatic login to capture token.
import { reactive, ref } from "vue";
import { useRouter, RouterLink } from "vue-router";
import InlineAlert from "../components/InlineAlert.vue";
import { registerAndLogin } from "../services/authService";

const router = useRouter();

const form = reactive({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
});

const loading = ref(false);
const errorMessage = ref("");
const successMessage = ref("");

// Performs register request and then automatic login when successful.
async function submitRegister() {
    loading.value = true;
    errorMessage.value = "";
    successMessage.value = "";

    try {
        await registerAndLogin({ ...form });
        successMessage.value =
            "Account created and authenticated. Redirecting...";
        await router.push("/");
    } catch (error) {
        errorMessage.value =
            error?.message || "Registration failed. Please verify your input.";
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <section class="auth-page">
        <article class="auth-card glass-card">
            <h2 class="section-title">Create Account</h2>
            <p class="muted">
                Register a new account to start generating cinematic prompts with AI.
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

            <form class="auth-form" @submit.prevent="submitRegister">
                <div class="field">
                    <label for="register-name">Name</label>
                    <input
                        id="register-name"
                        v-model="form.name"
                        class="input"
                        type="text"
                        required
                    />
                </div>

                <div class="field">
                    <label for="register-email">Email</label>
                    <input
                        id="register-email"
                        v-model="form.email"
                        class="input"
                        type="email"
                        required
                    />
                </div>

                <div class="field">
                    <label for="register-password">Password</label>
                    <input
                        id="register-password"
                        v-model="form.password"
                        class="input"
                        type="password"
                        required
                    />
                </div>

                <div class="field">
                    <label for="register-password-confirm"
                        >Confirm Password</label
                    >
                    <input
                        id="register-password-confirm"
                        v-model="form.password_confirmation"
                        class="input"
                        type="password"
                        required
                    />
                </div>

                <button
                    class="btn btn-primary"
                    type="submit"
                    :disabled="loading"
                >
                    {{ loading ? "Creating account..." : "Create Account" }}
                </button>
            </form>

            <p class="switch-link">
                Already registered?
                <RouterLink to="/login">Sign in</RouterLink>
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
    width: min(520px, 100%);
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
</style>
