# E-Sport Tournament API

A RESTful API for managing e-sport tournaments, player registrations, bracket generation, and real-time match score updates via WebSockets.

---

## Stack

- **Laravel 11** — PHP framework
- **PostgreSQL** — Database
- **Laravel Sanctum** — API token authentication
- **Laravel Reverb** — WebSocket server
- **Docker** — Containerized environment

---

## Setup

```bash
cp src/.env.example src/.env
# Fill in DB_USERNAME, DB_PASSWORD, REVERB_APP_ID, REVERB_APP_KEY, REVERB_APP_SECRET

docker compose up -d
docker exec php php artisan key:generate
docker exec php php artisan migrate
docker exec php php artisan queue:work
```

---

## API Documentation

Interactive Swagger UI available at:

```
http://localhost:8080/api/documentation
```

---

## Endpoints

### Auth

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/register` | No | Register a new user |
| POST | `/api/login` | No | Login and get token |
| POST | `/api/logout` | Yes | Logout current user |

**Register / Login body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password",
    "role": "organizer"
}
```
> `role` is either `organizer` or `player`

---

### Tournaments

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/tournaments` | No | List all tournaments |
| GET | `/api/tournaments/{id}` | No | Get tournament details |
| POST | `/api/tournaments` | Organizer | Create a tournament |
| PUT | `/api/tournaments/{id}` | Organizer | Update a tournament |
| DELETE | `/api/tournaments/{id}` | Organizer | Delete a tournament |
| GET | `/api/tournaments/{id}/bracket` | No | Get tournament bracket |

**Query params for listing:**
- `?game=valorant` — filter by game
- `?status=open` — filter by status (`open`, `closed`, `completed`)

**Create / Update body:**
```json
{
    "name": "Valorant Cup",
    "game": "Valorant",
    "max_participants": 8
}
```

---

### Registrations

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/tournaments/{id}/register` | Player | Register for a tournament |
| GET | `/api/tournaments/{id}/registrations` | Organizer | List confirmed players |
| PATCH | `/api/tournaments/{id}/close` | Organizer | Close registrations & generate bracket |

---

### Matches

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| PATCH | `/api/matches/{id}/score` | Organizer | Update match score |

**Body:**
```json
{
    "score_player1": 3,
    "score_player2": 1,
    "winner_id": 5
}
```

---

## WebSockets

Reverb WebSocket server runs on port `6001`.

**Connect:**
```
ws://localhost:6001/app/{REVERB_APP_KEY}
```

**Subscribe to a tournament channel:**
```json
{
    "event": "pusher:subscribe",
    "data": {
        "channel": "tournament.{id}"
    }
}
```

**Received event on score update:**
```json
{
    "event": "score.updated",
    "channel": "tournament.4",
    "data": {
        "match_id": 10,
        "round": 2,
        "position": 1,
        "player1": { "id": 1, "name": "khalid", "score": 3 },
        "player2": { "id": 2, "name": "ahmed", "score": 1 },
        "winner_id": 1,
        "tournament_status": "closed"
    }
}
```

---

## Tournament Flow

1. Organizer creates a tournament
2. Players register via `/register`
3. Organizer closes registrations via `/close` → bracket is auto-generated
4. Organizer updates match scores via `/score` → winners advance automatically
5. Final match winner → tournament marked as `completed`
