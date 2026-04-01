<script setup>
// Profile page for viewing and managing the authenticated user account.
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import InlineAlert from '../components/InlineAlert.vue'
import { clearToken } from '../services/apiClient'
import { getCurrentUserId } from '../services/authService'
import { deleteUserAccount, fetchCurrentUser, updateUserProfile } from '../services/userService'

const router = useRouter()

const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const profile = ref(null)

const form = reactive({
  name: '',
  password: '',
  password_confirmation: '',
})

const joinedOn = computed(() => {
  if (!profile.value?.created_at) return 'N/A'

  const parsed = new Date(String(profile.value.created_at).replace(' ', 'T'))
  if (Number.isNaN(parsed.getTime())) return profile.value.created_at

  return parsed.toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  })
})

const dailyGenerationText = computed(() => {
  if (!profile.value) return ''

  if (profile.value.daily_generation_unlimited) {
    return 'Unlimited generations'
  }

  const remaining = profile.value.daily_generation_remaining ?? 0
  return remaining === 1
    ? '1 generation left today'
    : `${remaining} generations left today`
})

function clearMessages() {
  errorMessage.value = ''
  successMessage.value = ''
}

function resolveUserId() {
  return getCurrentUserId()
}

async function loadProfile() {
  loading.value = true
  clearMessages()

  try {
    const userId = resolveUserId()
    if (!userId) {
      throw new Error('Profile session not found. Please log in again.')
    }

    const data = await fetchCurrentUser(userId)
    profile.value = data
    form.name = data?.name || ''
  } catch (error) {
    errorMessage.value = error?.message || 'Failed to load your profile.'
  } finally {
    loading.value = false
  }
}

async function submitUpdate() {
  saving.value = true
  clearMessages()

  try {
    const userId = resolveUserId()
    if (!userId) {
      throw new Error('Profile session not found. Please log in again.')
    }

    const payload = {
      name: form.name,
    }

    if (form.password) {
      payload.password = form.password
      payload.password_confirmation = form.password_confirmation
    }

    const updated = await updateUserProfile(userId, payload)
    profile.value = updated
    form.name = updated?.name || form.name
    form.password = ''
    form.password_confirmation = ''
    successMessage.value = 'Profile updated successfully.'
  } catch (error) {
    errorMessage.value = error?.message || 'Unable to update profile.'
  } finally {
    saving.value = false
  }
}

async function handleDeleteAccount() {
  if (!window.confirm('Delete your account permanently? This cannot be undone.')) {
    return
  }

  deleting.value = true
  clearMessages()

  try {
    const userId = resolveUserId()
    if (!userId) {
      throw new Error('Profile session not found. Please log in again.')
    }

    await deleteUserAccount(userId)
    clearToken()
    localStorage.removeItem('chatyai_user_id')
    window.dispatchEvent(new Event('auth-token-changed'))
    await router.push('/login')
  } catch (error) {
    errorMessage.value = error?.message || 'Unable to delete account.'
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  loadProfile()
})
</script>

<template>
  <section class="profile-page">
    <div class="page-head">
      <h2 class="section-title">Your Profile</h2>
      <p class="muted">Manage personal details and account security.</p>
    </div>

    <InlineAlert v-if="errorMessage" type="error" :message="errorMessage" />
    <InlineAlert v-if="successMessage" type="success" :message="successMessage" />

    <p v-if="loading" class="loading-text">Loading profile...</p>

    <template v-else-if="profile">
      <div class="profile-grid">
        <article class="glass-card profile-summary">
          <p class="chip">Account overview</p>
          <h3 class="profile-name">{{ profile.name }}</h3>
          <p class="profile-email">{{ profile.email }}</p>

          <dl class="stats-grid">
            <div class="stat-item">
              <dt>Prompts Generated</dt>
              <dd>{{ profile.number_of_prompts_generated || 0 }}</dd>
            </div>
            <div class="stat-item">
              <dt>Daily Limit</dt>
              <dd>{{ dailyGenerationText }}</dd>
            </div>
            <div class="stat-item">
              <dt>Joined</dt>
              <dd>{{ joinedOn }}</dd>
            </div>
            <div class="stat-item full">
              <dt>Account ID</dt>
              <dd>#{{ profile.id }}</dd>
            </div>
          </dl>
        </article>

        <article class="glass-card edit-panel">
          <h3 class="panel-title">Edit Profile</h3>

          <form class="profile-form" @submit.prevent="submitUpdate">
            <label class="field-label" for="name">Name</label>
            <input
              id="name"
              v-model="form.name"
              class="input"
              type="text"
              autocomplete="name"
              minlength="2"
              disabled
              required
            />

            <label class="field-label" for="password">New Password</label>
            <input
              id="password"
              v-model="form.password"
              class="input"
              type="password"
              autocomplete="new-password"
              placeholder="Leave blank to keep current password"
            />

            <label class="field-label" for="password_confirmation">Confirm Password</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              class="input"
              type="password"
              autocomplete="new-password"
              placeholder="Repeat new password"
            />

            <div class="actions-row">
              <button class="btn btn-primary" type="submit" :disabled="saving || deleting">
                {{ saving ? 'Saving...' : 'Save Changes' }}
              </button>
              <button
                class="btn btn-ghost danger"
                type="button"
                :disabled="saving || deleting"
                @click="handleDeleteAccount"
              >
                {{ deleting ? 'Deleting...' : 'Delete Account' }}
              </button>
            </div>
          </form>
        </article>
      </div>
    </template>
  </section>
</template>

<style scoped>
.profile-page {
  animation: reveal 360ms ease;
}

.page-head {
  margin-bottom: 0.85rem;
}

.loading-text {
  margin: 1rem 0;
  font-weight: 600;
  color: var(--text-secondary);
}

.profile-grid {
  display: grid;
  gap: 1rem;
  grid-template-columns: 1fr 1.35fr;
}

.profile-summary,
.edit-panel {
  padding: 1rem;
}

.profile-summary {
  position: relative;
  overflow: hidden;
}

.profile-summary::after {
  content: '';
  position: absolute;
  width: 180px;
  height: 180px;
  right: -65px;
  top: -75px;
  border-radius: 50%;
  background: radial-gradient(circle, var(--accent-soft) 0%, transparent 70%);
  pointer-events: none;
}

.profile-name {
  margin: 0.8rem 0 0.3rem;
  font-family: var(--font-heading);
  letter-spacing: -0.03em;
}

.profile-email {
  margin: 0;
  color: var(--text-secondary);
}

.stats-grid {
  margin: 1rem 0 0;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.65rem;
}

.stat-item {
  border: 1px solid var(--stroke-soft);
  border-radius: var(--radius-sm);
  background: var(--surface-muted);
  padding: 0.65rem;
}

.stat-item.full {
  grid-column: 1 / -1;
}

.stat-item dt {
  color: var(--text-secondary);
  font-size: 0.8rem;
  margin-bottom: 0.35rem;
}

.stat-item dd {
  margin: 0;
  font-weight: 700;
  color: var(--text-main);
}

.panel-title {
  margin: 0 0 0.85rem;
  font-family: var(--font-heading);
}

.profile-form {
  display: grid;
  gap: 0.45rem;
}

.field-label {
  color: var(--text-soft);
  font-size: 0.9rem;
  margin-top: 0.3rem;
}

.actions-row {
  margin-top: 0.8rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.55rem;
}

.danger {
  border-color: color-mix(in srgb, var(--status-error) 44%, var(--stroke-strong));
  color: var(--status-error);
}

.danger:hover {
  background: color-mix(in srgb, var(--status-error) 14%, transparent);
}

@media (max-width: 920px) {
  .profile-grid {
    grid-template-columns: 1fr;
  }
}

@keyframes reveal {
  from {
    opacity: 0;
    transform: translateY(8px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
