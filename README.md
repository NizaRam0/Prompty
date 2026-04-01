# Prompty - AI Image-to-Prompt Generator

<p align="center">
  <strong>Transform images into detailed AI prompts instantly</strong>
</p>

<p align="center">
  <a href="#features">Features</a> •
  <a href="#tech-stack">Tech Stack</a> •
  <a href="#getting-started">Getting Started</a> •
  <a href="#project-structure">Structure</a> •
  <a href="#api-endpoints">API</a> •
  <a href="#daily-quotas">Quotas</a> •
  <a href="#development">Development</a> •
  <a href="#license">License</a>
</p>

---

## Overview

**Prompty** is a full-stack web application that uses OpenAI's vision capabilities to analyze uploaded images and generate detailed, reusable prompts suitable for image-generation workflows (DALL-E, Midjourney, Stable Diffusion, etc.). Users can build a searchable history of generated prompts, with daily usage limits to control API costs.

**Perfect for:** Creators, prompt engineers, marketers, and designers who want to reverse-engineer image styles and compositions into reusable prompt text.

---

## Features

### Core Functionality

- 🖼️ **Drag-and-drop image upload** with live preview (JPG, PNG)
- 🤖 **AI-powered prompt generation** using OpenAI GPT-4 Vision
- ⏱️ **Generation timing** - See how long each prompt took to generate
- 💾 **Searchable history** - Access all your generated prompts with pagination
- 🔄 **Regenerate** - Instantly create new prompts from the same image
- 📋 **Copy-to-clipboard** - Quickly reuse prompt text

### User Management

- 🔐 **Token-based authentication** (Laravel Sanctum)
- 📧 **Email verification** - Secure account creation
- 🔑 **Password reset** - Email-based recovery flow
- 👤 **Profile management** - View account stats and update settings

### Rate Limiting & Quotas

- ⚡ **Daily generation limit** (5 prompts/day per user)
- 🎯 **Real-time quota display** - See remaining generations
- 🚫 **Hard backend block** - OpenAI never called when quota exhausted
- ⭐ **Whitelist support** - Unlimited access for admin/test accounts
- 📊 **Usage analytics** - Track total prompts generated

### Performance & UX

- ⚙️ **Fast validation** - File checks happen client-side before upload
- 🎨 **Dark/Light theme** - Persistent user preference
- 📱 **Fully responsive** - Works seamlessly on mobile, tablet, desktop
- ♿ **Accessible UI** - Semantic HTML, live alerts, proper labeling
- 🌐 **Service layer abstraction** - Clean API consumption pattern

---

## Tech Stack

### Backend

- **Framework:** Laravel 13 (PHP 8.3)
- **Database:** SQLite (local dev, easily swappable)
- **Authentication:** Laravel Sanctum (token-based)
- **AI Integration:** OpenAI PHP client (gpt-4o-mini vision)
- **Rate Limiting:** Laravel Cache RateLimiter
- **API Documentation:** Dedoc Scramble (OpenAPI generation)
- **Testing:** Pest 4 with Laravel plugin

### Frontend

- **Framework:** Vue 3 (Composition API with `<script setup>`)
- **Build Tool:** Vite 8
- **Routing:** Vue Router 5
- **State:** Local reactive refs (no centralized store)
- **HTTP Client:** Custom fetch wrapper with token persistence
- **Styling:** Scoped CSS + global design tokens
- **Validation:** Client-side form validators

### Infrastructure

- **Local Dev:** PHP 8.3 + Composer + Node.js
- **Build Scripts:** Composer + npm scripts
- **Environment:** `.env` based configuration

---

## Getting Started

### Prerequisites

- PHP 8.3+
- Composer
- Node.js 16+
- npm or yarn
- OpenAI API key

### Installation

1. **Clone the repository**

    ```bash
    git clone https://github.com/NizaRam0/Prompty.git
    cd ChatyAi
    ```

2. **Install dependencies**

    ```bash
    composer install
    npm install
    ```

3. **Configure environment**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. **Set up database**

    ```bash
    php artisan migrate
    ```

5. **Configure OpenAI API**
    - Add your OpenAI API key to `.env`:
        ```
        OPENAI_API_KEY=sk-xxx...
        ```

6. **Set frontend API URL** (if not localhost:8000)
    ```
    VITE_API_BASE_URL=http://localhost:8000/api
    ```

### Running the App

**Development mode:**

```bash
composer run dev
```

This starts:

- Laravel dev server (port 8000)
- Vite dev server (port 3000, auto HMR to http://localhost:3000)
- Queue listener for jobs
- Log stream

**Production build:**

```bash
npm run build
php artisan serve
```

---

## Project Structure

```
ChatyAi/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/V1/
│   │   │   ├── PromptGenerationController.php
│   │   │   ├── UserController.php
│   │   │   └── PostController.php
│   │   ├── Requests/
│   │   │   └── GeneratePromptRequest.php
│   │   ├── Resources/
│   │   │   └── UserResource.php
│   │   │   └── PromptGenerationResource.php
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php
│   │   ├── PromptGeneration.php
│   │   └── Post.php
│   ├── Services/
│   │   └── OpenAiService.php
│   └── Providers/
│       └── AppServiceProvider.php (rate limiter config)
├── routes/
│   ├── api.php (versioned API routes)
│   ├── auth.php (auth endpoints)
│   └── web.php
├── database/
│   ├── migrations/ (schema version history)
│   └── factories/ (seeders)
├── Frontend/
│   ├── src/
│   │   ├── pages/
│   │   │   ├── UploadPage.vue (main generation interface)
│   │   │   ├── HistoryPage.vue (searchable list)
│   │   │   ├── LoginPage.vue
│   │   │   ├── RegisterPage.vue
│   │   │   ├── UserProfile.vue
│   │   │   └── ResetPasswordPage.vue
│   │   ├── components/
│   │   │   ├── FileDropzone.vue
│   │   │   ├── GeneratedPromptCard.vue
│   │   │   ├── HistoryFilters.vue
│   │   │   ├── AppHeader.vue
│   │   │   ├── InlineAlert.vue
│   │   │   └── PaginationControls.vue
│   │   ├── services/
│   │   │   ├── apiClient.js (HTTP wrapper)
│   │   │   ├── authService.js (login/register/logout)
│   │   │   ├── userService.js (profile API)
│   │   │   └── promptGenerationService.js (upload/history)
│   │   ├── utils/
│   │   │   ├── validators.js
│   │   │   ├── formatters.js
│   │   │   └── constants.js
│   │   ├── router/
│   │   │   └── index.js (route guards, auth checks)
│   │   ├── App.vue (shell with theme toggle)
│   │   ├── main.js (entry point)
│   │   └── style.css (global design tokens)
│   ├── public/
│   ├── vite.config.js
│   └── package.json
├── tests/
│   ├── Feature/ (integration tests)
│   └── Unit/ (isolated tests)
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   ├── services.php (OpenAI config)
│   └── ...
└── composer.json
```

---

## API Endpoints

### Authentication (Public)

```
POST   /api/register              - Create account
POST   /api/login                 - Get API token
POST   /api/logout                - Revoke token
POST   /api/forgot-password       - Request reset email
POST   /api/reset-password        - Complete password reset
GET    /api/verify-email/:id/:hash - Email verification
```

### Prompt Generation (Protected)

```
POST   /api/v1/prompt-generations              - Upload image & generate prompt
GET    /api/v1/prompt-generations              - List user's generated prompts
GET    /api/v1/prompt-generations?page=1&search=... - Filter/paginate history
DELETE /api/v1/prompt-generations/:id          - Remove prompt from history
```

**Query Parameters:**

- `page` - Pagination page number
- `per_page` - Items per page (default 6)
- `search` - Search generated prompt text
- `mime_type` - Filter by image type (image/jpeg, image/png)
- `sort` - Sort field (created_at, file_size, mime_type)

### User Management (Protected)

```
GET    /api/v1/user/:id           - Fetch profile + quota info
PATCH  /api/v1/user/:id           - Update name/password
DELETE /api/v1/user/:id           - Delete account
```

### Response Format

```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "number_of_prompts_generated": 5,
    "daily_generation_limit": 5,
    "daily_generation_remaining": 2,
    "daily_generation_used": 3,
    "daily_generation_unlimited": false,
    "created_at": "2026-04-01 10:30:00",
    "updated_at": "2026-04-01 14:22:00"
}
```

---

## Daily Quotas

### How It Works

- **Limit:** 5 image-to-prompt generations per user per day
- **Window:** Resets daily at midnight (UTC)
- **Cost Control:** Prevents accidental OpenAI spending
- **Security:** Hard-blocked at backend before API call

### Quota Display

- **Upload Page:** Shows remaining count (e.g., "You have 4 generations left today")
- **Profile Page:** Shows daily limit stat
- **Disabled State:** Red warning when count reaches 0
- **Real-time Refresh:** Counter updates after each generation

### Whitelisted Unlimited Access

The following emails bypass daily limits:

- `nizar@gmail.com`
- `elnizarramadan61@gmail.com`

To add more: Edit `UNLIMITED_EMAILS` constant in:

- `app/Providers/AppServiceProvider.php`
- `app/Http/Controllers/Api/V1/PromptGenerationController.php`
- `app/Http/Resources/UserResource.php`

### Error Responses

When quota exhausted:

- **Frontend:** Button disabled, red warning message
- **Backend:** HTTP 429 (Too Many Requests)
- **OpenAI:** Never called if quota = 0

---

## Development

### Running Tests

```bash
composer test
```

### Code Quality

```bash
# Run Laravel Pint (auto-formatter)
composer run format

# Check syntax
composer run lint
```

### Debugging

- Vue DevTools browser extension for frontend state
- Laravel Tinker for quick backend queries:
    ```bash
    php artisan tinker
    > User::find(1)->PromptGenerations->count()
    ```

### Database Management

```bash
# Fresh migrate
php artisan migrate:fresh

# Seed test data
php artisan db:seed

# Rollback last migration
php artisan migrate:rollback
```

### Environment Customization

`.env` variables:

```
APP_NAME=Prompty
APP_ENV=local/production
APP_DEBUG=true/false
DB_CONNECTION=sqlite
OPENAI_API_KEY=sk-...
FRONTEND_URL=http://localhost:3000
```

---

## Architecture Decisions

### Why Local State Over Global Store?

- Small app scope (3 main pages)
- Page-specific state rarely shared
- Composition API refs are simpler/faster than global state

### Why Service Layer?

- Centralizes API contract
- Makes endpoint changes easy
- Normalizes response shapes
- Simplifies testing

### Why Rate Limit in Both Frontend & Backend?

- **Frontend:** Better UX feedback (instant disable)
- **Backend:** Security (can't bypass client code)
- **Critical:** OpenAI call never reaches service if limit hit

### Why Vue 3 Composition API?

- Modern syntax, less boilerplate
- Better TypeScript support
- Easier to test and reuse logic

---

## Future Roadmap

### Phase 1: Enhanced Prompts

- [ ] Prompt editing & refinement assistant
- [ ] Multiple output variants per image
- [ ] Style presets (cinematic, anime, product photo, etc.)
- [ ] Collections/folders to organize prompts

### Phase 2: Advanced Features

- [ ] Saved prompt templates
- [ ] Team workspaces with shared libraries
- [ ] Export formats (txt, JSON, CSV)
- [ ] Format-specific syntax (Midjourney, SDXL, Flux)

### Phase 3: Intelligence & Analytics

- [ ] Negative prompt suggestions
- [ ] Style-matching mode (reference image blend)
- [ ] Usage dashboard & trends
- [ ] Cost estimation per generation

### Phase 4: Monetization

- [ ] Tiered plans (50/day, 200/day, unlimited)
- [ ] Stripe billing integration
- [ ] Admin dashboard
- [ ] Public prompt gallery

---

## Contributing

Contributions welcome! Please open an issue or submit a pull request with improvements.

---

## License

MIT License - See LICENSE file for details.

---

## Support

For questions or issues, please open a GitHub issue or contact the maintainer.

**Version:** 1.0.0  
**Last Updated:** April 1, 2026  
**Status:** Active Development
