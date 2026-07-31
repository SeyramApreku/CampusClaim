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

---