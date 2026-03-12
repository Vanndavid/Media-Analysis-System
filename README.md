# Media Analysis System

A containerized media pipeline for real-estate teams to upload listing videos, process them asynchronously, and capture viewer engagement events.

## Tech Stack

| Layer | Technology | Responsibility |
| --- | --- | --- |
| Backend API | Laravel 11 (API-only) | Video ingestion, job orchestration, analytics endpoints |
| Frontend | Vue 3 + Vuetify | Listing/video dashboard and upload workflow |
| Queue | Redis 7 + Laravel Queue | Background processing for simulated transcoding |
| Database | MySQL 8 | Listings, videos, assets, and playback event persistence |
| Reverse Proxy | Traefik v3 | Local hostname routing for frontend + API |
| Runtime | Docker Compose | Local development parity across services |

## Core Features

- Video uploads per listing (`.mp4`, `.webm`) through the dashboard/API.
- Asynchronous processing flow with queued `TranscodeVideoJob` execution.
- Video status lifecycle: `UPLOADED -> PROCESSING -> READY | FAILED`.
- Asset storage for original uploads and mock renditions under Laravel public storage.
- Playback analytics (play / complete tracking + top-played summaries).
- Fully containerized local setup (frontend, API, queue, DB, proxy).

## Project Structure

```text
.
├── backend/      # Laravel API, queue jobs, tests
├── frontend/     # Vue + Vuetify SPA
├── nginx/        # Proxy/support config
├── docker-compose.dev.yml
└── docker-compose.prod.yml
```

## Local Development

### Prerequisites

- Docker Engine + Docker Compose plugin
- A hosts entry for `video.localhost`

### 1) Configure environment

```bash
cp backend/.env.example backend/.env
```

### 2) Start the stack

```bash
docker compose -f docker-compose.dev.yml up -d --build
```

### 3) Initialize the backend

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

### 4) Add hosts entry

Add this line to your system hosts file:

```text
127.0.0.1 video.localhost
```

### 5) Verify services

- Frontend: <http://video.localhost>
- API health: <http://video.localhost/api/health>

## Running Tests

From the project root:

```bash
docker compose exec app php artisan test
```

Current feature coverage includes:

- API health endpoint responds with HTTP 200.
- Video creation endpoint stores records and dispatches queue work.

## API Quick Reference

- `GET /api/health` - service heartbeat.
- `POST /api/videos` - upload a listing video.
- `POST /api/events` - track playback engagement events.
- `GET /api/analytics/top-played` - fetch top-played video metrics.

## Design Notes

- API-first Laravel backend with resource-based responses.
- Queue-first processing model to keep upload UX responsive.
- Infrastructure defined in Compose for reproducible local onboarding.
- Deliberately focused on backend architecture and workflow clarity.

## Roadmap

- Replace simulated jobs with FFmpeg-based transcoding.
- Cloud object storage integration (S3-compatible).
- Queue observability via Horizon.
- AuthN/AuthZ for listings and uploads.
- Rich analytics filtering and visualization.

## Author

Vanndavid Teng  
<https://vanndavidteng.com>
