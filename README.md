# 🚀 Job Intelligent

**AI-Powered Career Intelligence Platform** built with Laravel 11 + Vue 3.

Job Intelligent matches your skills with real job opportunities using an intelligent recommendation engine. It integrates with external APIs (JSearch/RapidAPI) to fetch real-world job listings from Indeed, LinkedIn, and more.

---

## Tech Stack

| Layer     | Technology                            |
|-----------|---------------------------------------|
| Backend   | Laravel 11, PHP 8.2+                 |
| Frontend  | Vue 3, Vite, Pinia, Tailwind CSS 4   |
| Database  | PostgreSQL                            |
| Auth      | Laravel Sanctum (token-based)         |
| Charts    | Chart.js + vue-chartjs                |
| Jobs API  | JSearch via RapidAPI                  |

---

## Features

- 🔐 **Authentication** — Register, Login, Logout with Sanctum tokens
- 📊 **Dashboard** — Stats cards, match distribution chart, top recommendations
- 💼 **Jobs** — Browse, search, filter jobs by keyword & location
- 🤖 **AI Matching** — Skill-based recommendation engine with match scores (%)
- 📄 **Applications** — Apply to jobs, track status, withdraw applications
- 👤 **Profile** — Manage skills, experience, and preferences
- 🌐 **External API** — Fetch real jobs from Indeed/LinkedIn via JSearch (RapidAPI)
- 🎨 **Premium UI** — Dark glassmorphism SaaS design with animations

---

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- PostgreSQL
- (Optional) RapidAPI account for real job data

### 1. Clone & Install

```bash
git clone <your-repo-url>
cd MindJod
```

### 2. Backend Setup

```bash
cd backend
composer install
cp .env.example .env    # or edit .env directly
php artisan key:generate
```

Edit `.env` with your DB credentials:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=jobmind
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

Run migrations & seed demo data:
```bash
php artisan migrate:fresh --seed
```

Start the backend:
```bash
php artisan serve --port=8000
```

### 3. Frontend Setup

```bash
cd frontend
npm install
npm run dev
```

Open **http://localhost:5173** in your browser.

### 4. Demo Account

| Email              | Password   |
|--------------------|-----------|
| demo@jobmind.test  | password  |

---

## Fetching Real Jobs (RapidAPI)

1. Sign up at [rapidapi.com](https://rapidapi.com)
2. Subscribe to the **JSearch** API (free tier: 500 requests/month)
3. Copy your API key and add it to `.env`:
   ```env
   RAPIDAPI_KEY=your_api_key_here
   ```
4. Fetch jobs:
   ```bash
   # Basic search
   php artisan jobs:fetch "Laravel developer"

   # With location
   php artisan jobs:fetch "frontend developer" --location="Paris"

   # Multiple pages (more results)
   php artisan jobs:fetch "React developer" --location="Remote" --pages=3
   ```

Jobs are automatically deduplicated, skills are extracted from descriptions, and everything is stored in the database.

---

## API Endpoints

### Authentication
| Method | Endpoint          | Description           |
|--------|-------------------|-----------------------|
| POST   | `/api/register`   | Create account        |
| POST   | `/api/login`      | Login (returns token) |
| POST   | `/api/logout`     | Logout (auth required)|

### Jobs
| Method | Endpoint          | Description                |
|--------|-------------------|----------------------------|
| GET    | `/api/jobs`       | List jobs (search/filter)  |
| GET    | `/api/jobs/{id}`  | Job details                |
| POST   | `/api/jobs`       | Create job (auth required) |

### Applications
| Method | Endpoint                | Description              |
|--------|-------------------------|--------------------------|
| GET    | `/api/applications`     | List my applications     |
| POST   | `/api/applications`     | Apply to a job           |
| DELETE | `/api/applications/{id}`| Withdraw application     |

### Other
| Method | Endpoint              | Description              |
|--------|-----------------------|--------------------------|
| GET    | `/api/user`           | Get profile              |
| PUT    | `/api/user`           | Update profile           |
| GET    | `/api/dashboard`      | Dashboard stats          |
| GET    | `/api/recommendations`| AI-matched jobs          |

> All endpoints except `/register` and `/login` require `Authorization: Bearer <token>` header.

---

## Project Structure

```
MindJod/
├── backend/                    # Laravel 11 API
│   ├── app/
│   │   ├── Console/Commands/   # FetchExternalJobs command
│   │   ├── Http/Controllers/   # Auth, Jobs, Profile, Dashboard, Applications
│   │   ├── Models/             # User, Job, JobApplication
│   │   └── Services/           # RecommendationService, ExternalJobService
│   ├── database/
│   │   ├── migrations/         # DB schema
│   │   └── seeders/            # Demo data
│   └── routes/api.php          # API routes
│
├── frontend/                   # Vue 3 + Vite
│   ├── src/
│   │   ├── api/                # Axios client
│   │   ├── components/         # Navbar, Sidebar, JobCard, StatsCard, Chart
│   │   ├── layouts/            # AppLayout
│   │   ├── router/             # Vue Router config
│   │   ├── stores/             # Pinia stores (auth, jobs)
│   │   └── views/              # Pages (Dashboard, Jobs, Profile, Applications)
│   └── vite.config.js
│
└── plan.md                     # Project specification
```

---

## License

MIT
