# CampusClaim - Ashesi Lost and Found Platform

CampusClaim is a centralized web application designed for students and staff at Ashesi University to report lost and found items. The platform features an intelligent auto-matching engine and real-time notifications to facilitate the quick recovery of personal belongings.

## Features
- **Secure Authentication:** Role-based access for Students and Admins.
- **Intelligent Matching:** Advanced algorithm that cross-references lost and found reports based on category, location, and keywords.
- **Real-time Notifications:** Users are alerted immediately when a potential match is found or when an admin updates their claim status.
- **Admin Dashboard:** Centralized panel for managing claims and verifying ownership.
- **Mobile Responsive:** Modern, premium UI designed for both desktop and mobile use.

## Tech Stack
- **Backend:** PHP 8.2 (Object-Oriented, Custom DAO Architecture)
- **Database:** MySQL
- **Design Patterns:** Strategy, Factory, DAO, Singleton
- **Containerization:** Docker
- **CI/CD:** GitHub Actions

## Installation & Setup

### Prerequisites
- Docker and Docker Compose installed.

### Setup Steps
1. **Clone the repository:**
   ```bash
   git clone <your-repository-url>
   cd CampusClaim
   ```

2. **Start the environment:**
   ```bash
   docker-compose up -d
   ```

3. **Database Configuration:**
   The system will automatically initialize the database using the `database.sql` file provided in the root directory.

4. **Access the App:**
   Open your browser and navigate to `campusclaim-production.up.railway.app`.

## Architecture
The project follows a modular **Model-View-Controller (MVC)** influenced architecture:
- `/classes`: Contains the Data Access Objects (DAOs) and domain models.
- `/auth`: Handles registration, login, and session management.
- `/items`: Core logic for reporting, browsing, and matching items.
- `/admin`: Management dashboard for system administrators.
- `/assets`: CSS, JS, and image resources.

## CI/CD
Automated workflows are handled via **GitHub Actions**. Every push to the main branch triggers:
1. PHP Linting (Syntax Check)
2. Docker Build Validation

## Semantic Matching and Grounded Assistant

CampusClaim can optionally index open item reports in Qdrant using OpenAI
embeddings. The semantic index powers hybrid lost/found matching and an
authenticated assistant whose answers link back to current CampusClaim reports.
MySQL remains the source of truth; every vector result is reloaded from MySQL
before it is shown to a user.

### Setup

1. Apply `migrations/001_ai_search.sql` to an existing database. New databases
   created from `database.sql` already contain the required tables.
2. Provision Qdrant and configure the variables documented in `.env.example`.
3. Leave `AI_FEATURES_ENABLED=false` while applying the migration.
4. Set `AI_FEATURES_ENABLED=true`, then backfill and process the index:

   ```bash
   php bin/backfill-index.php
   php bin/index-worker.php 500
   ```

5. Run `php bin/index-worker.php 50` continuously or on a short schedule.
6. After confirming retrieval works, set `RAG_ASSISTANT_ENABLED=true`.

The report workflow writes indexing jobs transactionally. Provider failures are
retried by the worker and do not block ordinary browsing. Semantic matching
falls back to the existing keyword strategy when its providers are unavailable.

### AI environment variables

- `OPENAI_API_KEY`: server-side API key; never expose it to browser JavaScript.
- `OPENAI_EMBEDDING_MODEL`: defaults to `text-embedding-3-small`.
- `OPENAI_CHAT_MODEL`: generation model used by the assistant.
- `EMBEDDING_DIMENSIONS`: must match the Qdrant collection vector size.
- `QDRANT_URL`, `QDRANT_API_KEY`, `QDRANT_COLLECTION`: vector-store connection.
- `AI_FEATURES_ENABLED`: enables indexing and semantic matching.
- `RAG_ASSISTANT_ENABLED`: independently exposes assistant requests.

### Validation

Run PHP syntax checks and the lightweight test suite inside the application
container:

```bash
find . -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l
php tests/run.php
```

---
