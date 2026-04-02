# Session Changes - April 1, 2026

**Commit:** 50da306  
**Summary:** Added daily generation quota system with rate limiting, UI controls, and generation timing display.

---

## Overview

This session implemented a complete daily generation quota system to prevent API abuse and provide users with clear visibility into their usage limits. The quota system is enforced at both frontend and backend levels, with visual indicators when limits are reached.

**Key Features:**

- Daily limit: 5 image-to-prompt generations per user (configurable)
- Unlimited access for whitelisted emails
- Frontend validation and blocking
- Backend pre-check before OpenAI API calls
- Real-time quota display on upload page and profile
- Generation duration timing display

---

## Files Modified

### Backend

#### 1. `app/Http/Resources/UserResource.php`

**Changes:**

- Added RateLimiter import for quota computation
- Added constants for `DAILY_PROMPT_LIMIT` (5) and `UNLIMITED_EMAILS` (whitelisted emails)
- Added `generationQuota()` private method to compute remaining quota using RateLimiter
- Extended user response with 4 new fields:
    - `daily_generation_limit`: The configured daily cap (null if unlimited)
    - `daily_generation_remaining`: How many generations left today
    - `daily_generation_used`: How many generations used today
    - `daily_generation_unlimited`: Boolean flag for unlimited users

**Purpose:** Provides frontend with accurate quota data for every authenticated user request.

---

#### 2. `app/Http/Controllers/Api/V1/PromptGenerationController.php`

**Changes:**

- Added RateLimiter import
- Added constants matching UserResource: `DAILY_PROMPT_LIMIT` and `UNLIMITED_EMAILS`
- Added `isQuotaExhausted()` private method that:
    - Returns false for whitelisted email addresses
    - Uses RateLimiter::tooManyAttempts() to check if user hit daily limit
- Added quota guard in `store()` method:
    - Checks `isQuotaExhausted()` before handling image
    - Returns 429 (Too Many Requests) response if exhausted
    - **Crucially: prevents any OpenAI API call if quota exceeded**

**Purpose:** Server-side hard block ensuring no OpenAI charges occur when user is over quota, even if frontend is bypassed.

---

#### 3. `app/Providers/AppServiceProvider.php`

**Changes:**

- Fixed bug in `prompt-generation` rate limiter definition:
    - **Before:** `$myEmail = 'nizar@gmail.com'||'elnizarramadan61@gmail.com'` evaluated to boolean `true`
    - **After:** Created `$allowedEmails` array and used `in_array()` for proper email matching
- Result: Whitelisted emails now correctly bypass the daily limit

**Purpose:** Corrected rate limiter configuration so designated admin/test accounts have unlimited generations.

---

#### 4. `routes/api.php`

**Changes:**

- Reordered route definitions:
    - **Before:** `apiResource('prompt-generations')` defined POST first, then explicit throttled POST was added after
    - **After:** Moved explicit throttled POST route above `apiResource()` to ensure it takes precedence
- Removed `'store'` from `apiResource()` scope to avoid duplicate route definition
- Kept `'index'` and `'destroy'` in apiResource for other operations

**Purpose:** Ensures the rate-limited prompt-generation endpoint is always used for POST requests.

---

### Frontend

#### 1. `Frontend/src/pages/UploadPage.vue`

**Changes:**

- **Imports:** Added `fetchCurrentUser` from userService and `getCurrentUserId` from authService
- **State variables:**
    - `quota`: Holds current user's quota data
    - `quotaLoading`: Loading state for quota fetch
- **Computed properties:**
    - `isQuotaExhausted`: Returns true when remaining quota ≤ 0 (and non-unlimited)
    - `quotaText`: Friendly message showing remaining generations (e.g., "You have 4 generations left today")
    - `quotaDangerText`: Red danger message when exhausted (e.g., "You have 0 generations left today. Generation is disabled.")
- **Functions:**
    - `loadQuota()`: Fetches current user profile and extracts quota data
    - `submitGeneration()`: Added early return + error message if quota exhausted before any API call
- **Lifecycle:**
    - `onMounted()`: Load quota on page entry
    - After successful generation: Call `loadQuota()` to refresh counter
- **UI changes:**
    - Added quota loading indicator
    - Display quota text with conditional danger styling (red when 0)
    - Generate button now shows "Daily Limit Reached" label when disabled
    - Generate button gets danger styling class `btn-limit-disabled` when quota exhausted
    - Regenerate button also disabled when quota exhausted
- **CSS additions:**
    - `.quota-note`: Bold gold-colored quota display
    - `.danger-note`: Red styling for exhaustion message
    - `.btn-limit-disabled:disabled`: Dark red gradient button for limit-reached state

**Purpose:** Frontend enforcement and user communication of daily limits.

---

#### 2. `Frontend/src/pages/UserProfile.vue`

**Changes:**

- **Computed property added:**
    - `dailyGenerationText`: Formats quota data for display
        - Shows "Unlimited generations" for whitelisted users
        - Shows remaining count (e.g., "4 generations left today") or "1 generation left today"
- **UI update:**
    - Added new stat item in profile summary showing daily generation limit

**Purpose:** Users can see their remaining quota in their profile.

---

#### 3. `Frontend/src/components/GeneratedPromptCard.vue`

**Changes:**

- **Props added:**
    - `generationDurationMs`: Optional number for how long generation took
- **Computed property added:**
    - `generationDurationLabel`: Formats duration as seconds (e.g., "1.42s")
- **UI update:**
    - Added conditional metadata chip showing "Generated in Xs" when duration is available

**Purpose:** Display generation timing to users for performance insight.

---

#### 4. `Frontend/src/pages/RegisterPage.vue`

**Changes:**

- Minor formatting adjustments (no functional changes in this session)

---

#### 5. `Frontend/src/components/WelcomePopup.vue` (NEW)

**Changes:**

- New component created (untracked file committed)
- Status: Created but not yet used in app

---

## Configuration Summary

### Rate Limiter Settings

- **Daily Limit:** 5 generations per user per day
- **Whitelisted Emails (Unlimited):**
    - `nizar@gmail.com`
    - `elnizarramadan61@gmail.com`
- **Unauthenticated Users:** Limited by IP address

### Shared Constants (Kept in Sync)

These appear in three places for redundancy and security:

- `app/Http/Resources/UserResource.php`
- `app/Http/Controllers/Api/V1/PromptGenerationController.php`
- `app/Providers/AppServiceProvider.php`

**Future improvement:** Consolidate to a single config source.

---

## User Experience Flow

### When User Has Remaining Quota

1. Upload page shows: "You have 5 generations left today."
2. Generate button is enabled and labeled "Generate Prompt"
3. After clicking, image is validated, sent to backend, OpenAI is called, prompt is generated
4. Result is displayed with generation duration (e.g., "Generated in 2.15s")
5. Quota counter refreshes: "You have 4 generations left today."
6. Profile page shows: "Daily Limit: 4 generations left today"

### When User Reaches Quota (0 remaining)

1. Upload page shows in red: "You have 0 generations left today. Generation is disabled."
2. Generate button is visually distinct (dark red), labeled "Daily Limit Reached", and disabled
3. Regenerate button is also disabled
4. If user somehow bypasses frontend, backend returns 429 (Too Many Requests)
5. OpenAI API is **never called** when quota is exhausted
6. Profile page shows: "Daily Limit: 0 generations left today"

### For Whitelisted Users

1. All quota displays show: "Unlimited generations"
2. Generate button always remains enabled
3. No rate limiting is applied server-side
4. Can generate indefinitely

---

## Security & Robustness

### Frontend Protections

- ✅ Submit function returns early if quota exhausted
- ✅ Generate/Regenerate buttons are disabled when quota at 0
- ✅ Clear visual feedback (red danger styling)

### Backend Protections (Most Important)

- ✅ Controller checks quota before storing image
- ✅ OpenAI service is **never called** if quota exhausted
- ✅ Returns 429 HTTP status code
- ✅ No database entries created for quota-exceeded attempts
- ✅ Rate limiter uses Laravel's cache for fast checks

### Defense-in-Depth

- If frontend is disabled/bypassed, backend still blocks
- If attacker modifies API calls, rate limiter enforces
- Whitelisted emails are checked server-side (can't be spoofed from client)

---

## Testing Recommendations

1. **Quota Countdown:**
    - Generate 5 prompts and watch counter decrement
    - Verify "You have 0 left" message appears on 6th attempt
    - Refresh page and confirm quota hasn't reset

2. **Button State:**
    - Check Generate button disables and turns red at quota=0
    - Check Regenerate is also disabled
    - Verify labels change appropriately

3. **Backend Block:**
    - Use curl/Postman to POST to `/api/v1/prompt-generations` when quota=0
    - Confirm 429 response is returned
    - Check no image is stored

4. **Whitelisted Emails:**
    - Login as nizar@gmail.com or elnizarramadan61@gmail.com
    - Generate 10+ prompts and verify no limit is enforced
    - Check profile shows "Unlimited generations"

5. **Rate Limit Reset:**
    - Generate all 5 quota, confirm next day resets to 5 (test by manipulating system date if needed)

---

## Files by Category

| Category            | File                                                         | Change Type        |
| ------------------- | ------------------------------------------------------------ | ------------------ |
| Backend Logic       | `app/Http/Resources/UserResource.php`                        | Modified           |
| Backend Logic       | `app/Http/Controllers/Api/V1/PromptGenerationController.php` | Modified           |
| Backend Config      | `app/Providers/AppServiceProvider.php`                       | Modified (Bug Fix) |
| Backend Routes      | `routes/api.php`                                             | Modified           |
| Frontend Pages      | `Frontend/src/pages/UploadPage.vue`                          | Modified (Major)   |
| Frontend Pages      | `Frontend/src/pages/UserProfile.vue`                         | Modified           |
| Frontend Pages      | `Frontend/src/pages/RegisterPage.vue`                        | Modified (Minor)   |
| Frontend Components | `Frontend/src/components/GeneratedPromptCard.vue`            | Modified           |
| Frontend Components | `Frontend/src/components/WelcomePopup.vue`                   | Created            |

---

## Commit Information

**Hash:** 50da306  
**Message:** `feat: add daily generation quota system with rate limiting and UI controls`  
**Date:** April 1, 2026  
**Files Changed:** 7 (6 modified, 1 created)  
**Insertions:** 263  
**Deletions:** 16

---

## Next Steps / Future Improvements

1. **Consolidate Constants:** Move `DAILY_PROMPT_LIMIT` and `UNLIMITED_EMAILS` to single config file
2. **Time-Until-Reset:** Show "Resets in 4 hours" or similar countdown
3. **Usage Analytics:** Track which users are hitting limits frequently
4. **Tiered Plans:** Implement paid tiers with higher limits (50/day for Pro, 200/day for Enterprise)
5. **Usage Dashboard:** Show historical generation counts, failure rates, cost per user
6. **Retry Logic:** Add background queue for failed generations with retry on 429
7. **Email Notification:** Alert users when they reach 80%, 100% capacity

---

**Session completed successfully. All changes committed and pushed to main branch.**
