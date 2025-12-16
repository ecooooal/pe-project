  
# PCU Proficiency Examination Application

<div align="center">

![Static Badge](https://img.shields.io/badge/Linux-FCC624?style=for-the-badge&logo=linux&logoColor=black)
[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://docker.com/)
[![PostgresSQL](https://img.shields.io/badge/postgresql-4169e1?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![FastAPI](https://img.shields.io/badge/fastapi-109989?style=for-the-badge&logo=FASTAPI&logoColor=white)](https://fastapi.tiangolo.com/)
[![Redis](https://img.shields.io/badge/redis-CC0000.svg?&style=for-the-badge&logo=redis&logoColor=white)](https://redis.io/)

</div>


- [Features](#features)

- [Requirements](#requirements)

- [Setting up the Application](#setting-up-the-application)

- [VPS Installation](#vps-installation)

- [Technical Details](#technical-details)

---

# Why was this Created?

## Client (COI Department)
- Provide rapid, objective, and data-driven assessment of students’ academic readiness. 
- Deliver instant exam analytics and performance reports, empowering faculty to recalibrate teaching methods for future cohorts. 
- Meet emerging requirements in higher education policy for learning analytics and standardization in assessment

---

# Features


## Data for Analysis
- Exam performance data and question performance data are designed to be data analysis ready.
## Exam Cloning and Builder
- Exams are able to be cloned for fast generation of similar exams.
- Exams have their own Exam builders for fast insertion of questions into the exam determined by greedy algorithm or dynamic programming approach.

## Coding Questions
- The application supports coding challenges (Java only) similar to platforms like Codewars, with automated test cases and validation.
- Coding questions without test cases are supported for creation of syntax/compilation only coding question.

## Automated Distribution of Reviewers
- Review materials are automatically distributed to students based on topics and subjects where they scored low.

## Automated Generation of Student Performance Report in an Exam
- Plots in this reports are generated using [Plotly js](https://plotly.com/javascript/) from ETL processes handled by a FastAPI container in the Backend.
- Reports consists of Data Overview, Descriptive Statistics, Exploratory Data Analysis, Individual Student Performance, and Individual Question Performance.
- Data Overview gives number counts for students, subjects, topics, questions, passed students, failed students, perfect scored students, and passing rate.
- Descriptive statistics gives data summaries for bloom's taxonomy levels, measure of centers, and subject highs and lows
- Exploratory Data Analysis gives the following
    - Exam Scores distribution Histogram
    - Exam Scores Box Plot
    - Performance Distribution by Subjects 1D Heat map
    - Performance Distribution by Topics 1D Heat map
    - Grouped Bar plots of Question types with Bloom's Level
    - Performance Distribution by Question Level 1D Heat maps
- Individual Student Performance gives summary performance for each student with features such as bloom's level accuracy, attempts, etc.
- Individual Question Performance gives summary performance for each question in the exam with Question item Analysis.
## Data Exports
- Reports Raw data, Individual Student Performance data, and Individual Question Performance are exportable in spreadsheets format.


---
# Requirements
- Linux based Operating system (WSL, Ubuntu, etc)
- Docker installed
- Docker compose enabled
- Git installed
- Linux, Docker, and Git knowledge

---

# Setting up the Application

## Configuring `.env` File.

- Copy and paste a `.env` file from `.env.example` file.

- Add the necessary database credentials.

- To enable google sign-in, add the necessary firebase credentials.

## Build the Laravel development environment.

- This is a requirement to generate Laravel key.

```bash

docker compose -f compose.dev.yaml build

```

## Install Dependencies and Generate the Laravel Key.

- Run the `compose.dev.yaml` file to generate `APP_KEY` in `.env` file.

```bash

docker compose -f compose.dev.yaml up

```

- Wait for the workspace container to be created and is running.

- Generate the key using the workspace container in the compose.dev.yaml

```

docker compose -f compose.dev.yaml exec workspace bash

```

```bash
# Install PHP Dependencies
composer install

```

```bash
# Install Javascript Dependencies
npm install

```

```bash

php artisan key:generate

```

- Restart the compose containers.

```bash

docker compose -f compose.dev.yaml down

docker compose -f compose.dev.yaml up

```

## Running Database Seeders

- This application requires running a database seeder for reasons of:

- Populating the roles and permissions for the RBAC system inside the application

- Populating a sample account and the super admin inside the application

- Populating a sample data to be used as an example for the user.

- Run the database seeder through the **workspace container**.

```bash

docker compose -f compose.dev.yaml exec workspace bash

```

```bash

php artisan migrate:fresh --seed

php artisan config:clear

php artisan config:cache

```

- Restart the compose containers.

```bash

docker compose -f compose.dev.yaml down

docker compose -f compose.dev.yaml up

```

## Running the Application in Dev mode

- Spin up the compose file for dev.

```bash

docker compose -f compose.dev.yaml up

```

- Go inside the workspace container and run NPM to compile and build CSS and JS files.

```bash

docker compose -f compose.dev.yaml exec workspace bash

```

```bash

npm run dev

```

- Visit the `localhost` in your browser.

  

  

---

# Enabling Google Single Sign On (SSO)

1. Create Firebase Project - Go to [https://console.firebase.google.com/](https://console.firebase.google.com/?fbclid=IwYnJpZBExd0xDeVo2N0xXa0FEQkhCdXNydGMGYXBwX2lkEDIyMjAzOTE3ODgyMDA4OTIAAR659atIwd8Gphco3RhG7bRdpx3ns2bcfm-82bTAFR3UauZ0_pDFedvvZ4_iEg&brid=ItnoTAuz8WD7ZzrYEpvXSA) 
    - Click "Add project" or "Create a project" 
    - Enter project name (we used pe-project) 
    - Follow the setup wizard
2. Enable Google Sign-in 
    - In Firebase Console, navigate to Build > Authentication 
    - Click "Get started" 
    - Go to "Sign-in method" tab
    - Click on "Google" provider 
    - Toggle "Enable" 
    - Set Project support email (use institutional email) 
    - Click "Save"
3. Register Web Application 
    - In Project Overview (gear icon > Project settings), scroll down to "Your apps" 
    - Click the web icon (</>) to add a web app 
    - Enter app nickname (e.g., "Proficiency Exam Web App") 
    - Click "Register app" 
4. Get Firebase Configuration 
    - After registration, copy the Firebase configuration object containing:
    ```
    apiKey authDomain projectId storageBucket messagingSenderId appId 
    ```
    
    - Keep this information for the next step 
5. Locate the .env file in the application root directory (or .env.example) 
    - Replace the Firebase configuration values with the new credentials from Step 4: 
        - `FIREBASE_API_KEY=your_new_api_key` 
        - `FIREBASE_AUTH_DOMAIN=your_new_auth_domain` 
        - `FIREBASE_PROJECT_ID=your_new_project_id` 
        - `FIREBASE_STORAGE_BUCKET=your_new_storage_bucket` 
        - `FIREBASE_MESSAGING_SENDER_ID=your_new_sender_id` 
        - `FIREBASE_APP_ID=your_new_app_id` 
6. Authorize Production Domain 
    - In Firebase Console, go to Authentication > Settings > Authorized domains 
    - Click "Add domain" 
    - Enter: pcud-pe.online Add both `www.pcud-pe.online` and `pcud-pe.online`
    - Save changes 
    - Clear Application Cache 
    - Test On VPS: 
    ```
    php artisan config:clear 
    php artisan cache:clear 
    ```
    - Test Google Sign-in functionality
  

---

# VPS Installation
> [!NOTE]
> For our own Production we use [Hostinger VPS](https://www.hostinger.com/ph)
## VPS Installation

### Prerequisites
- A VPS instance (Ubuntu 20.04+ recommended)
- SSH access to your VPS
- Domain name (optional, for production setup)

### Step 1: Connect to Your VPS
```bash
ssh root@your-vps-ip
```

### Step 2: Install Required Software
```bash
# Update package list
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo apt install docker-compose -y

# Install Git
sudo apt install git -y

# Verify installations
docker --version
docker-compose --version
git --version
```

### Step 3: Clone the Repository
```bash
# Navigate to your preferred directory
cd /var/www

# Clone the repository
git clone https://github.com/your-username/your-repo.git
cd your-repo
```

### Step 4: Environment Configuration
```bash
# Copy the example environment file
cp .env.example .env

# Edit the environment file with your settings
nano .env
```

**Required environment variables:**
```env
APP_NAME=ExaminationApp
APP_ENV=production
APP_KEY=  # Generate this
APP_URL=http://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

> [!CAUTION]
> This Application has multiple docker compose file, please refer in this site on how to use [Multiple Docker Compose](https://docs.docker.com/compose/how-tos/multiple-compose-files/)
> 
> The Instructions below uses single docker compose file for code clarity concern.
### Step 5: Build and Start Containers
```bash
# Build the Docker containers
docker-compose build

# Start the containers in detached mode
docker-compose up -d

# Generate application key
docker-compose exec php-cli bash (You may find this inside docker compose) 
> php artisan key:generate
# Migrate tables and seed them
> php artisan migrate:fresh --seed

```

### Step 6: Verify Installation
```bash
# Check running containers
docker-compose ps

# View applications logs
docker-compose logs -f 
```

Your application should now be accessible at `http://your-vps-ip` or your configured domain.

### Useful Commands
```bash
# Stop containers
docker-compose down

# Restart containers
docker-compose restart

# View logs
docker-compose logs -f

# Access application container shell
docker-compose exec app bash
```
  

---

# Technical Details

## Frontend
- HTML (Twig)
- CSS (Tailwind)
- Javascript

## Backend
- Laravel (PHP 9+)
- FastAPI (Python)
- Docker
- Polars & Plotly
- Java 
## Database
- PostgreSQL
- Redis
