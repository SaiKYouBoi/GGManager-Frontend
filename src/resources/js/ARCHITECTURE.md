# GGManager - Vue.js 3 Frontend Architecture

## Project Overview

This is a complete, production-ready Vue.js 3 frontend for an e-sports/tournament management system with:

- Role-based access control (Guest, Player, Organizer)
- Real-time WebSocket updates
- Tournament management and bracket visualization
- Live scoring with automatic bracket progression

---

## 📁 Project Structure

```
src/resources/
├── css/
│   └── app.css                          # Tailwind CSS configuration
├── js/
│   ├── App.vue                          # Root component with navigation
│   ├── app.js                           # Entry point - Vue 3 setup
│   ├── bootstrap.js                     # Bootstrap configuration
│   ├── router/
│   │   └── index.js                     # Vue Router with role guards
│   ├── stores/
│   │   ├── auth.js                      # Authentication store (Pinia)
│   │   └── tournament.js                # Tournament store with WebSocket
│   ├── services/
│   │   ├── api.js                       # Axios HTTP client
│   │   └── wsService.js                 # WebSocket service
│   └── views/
│       ├── Home.vue                     # Landing page
│       ├── TournamentDashboard.vue      # Main tournament management
│       ├── BracketView.vue              # Tournament bracket visualization
│       ├── TournamentForm.vue           # Create/Edit tournaments
│       ├── NotFound.vue                 # 404 page
│       └── Auth/
│           ├── Login.vue                # Login form
│           └── Register.vue             # Registration form
└── index.html                           # HTML entry point
```

---

## 🔐 Authentication & Authorization

### Role Hierarchy

```javascript
ROLES = {
    GUEST: "guest", // No authentication
    PLAYER: "player", // Can browse and register for tournaments
    ORGANIZER: "organizer", // Can create and manage tournaments
};
```

### Route Guards

All routes are protected with role-based guards in `router/index.js`:

```javascript
// Public routes (no auth required)
/               // Home page
/login          // Login form
/register       // Registration form

// Protected routes (requires authentication)
/tournaments                 // Available to Player, Organizer
/tournaments/create          // Organizer only
/tournaments/:id/edit        // Organizer only
/tournaments/:id/bracket     // Player, Organizer only
```

---

## 🗄️ State Management (Pinia Stores)

### `stores/auth.js`

Handles user authentication and authorization:

- **login(email, password)** - Authenticate user
- **register(data)** - Register new user
- **logout()** - Clear authentication
- **fetchUser()** - Fetch current user profile
- **isAuthenticated** - Computed property for auth status

```javascript
// Usage in components
const authStore = useAuthStore();
authStore.login(email, password);
console.log(authStore.user?.role); // 'player' or 'organizer'
```

### `stores/tournament.js`

Manages tournament data and WebSocket subscriptions:

**Tournament Management:**

- **fetchTournaments(filters)** - Get all tournaments
- **fetchTournament(id)** - Get single tournament details
- **createTournament(data)** - Create new tournament
- **updateTournament(id, data)** - Update tournament
- **deleteTournament(id)** - Delete tournament
- **closeRegistrations(id)** - Close registrations and generate bracket

**Match Management:**

- **fetchMatches(tournamentId)** - Get tournament matches
- **updateMatchScore(matchId, p1Score, p2Score)** - Set match score

**Real-Time Features:**

- **subscribeToTournament(id)** - Subscribe to live updates
- **unsubscribeFromTournament(id)** - Unsubscribe from updates
- **handleRealtimeUpdate(data)** - Process WebSocket messages

```javascript
// Usage
const tournamentStore = useTournamentStore();
await tournamentStore.fetchTournaments();
tournamentStore.subscribeToTournament(tournamentId);
await tournamentStore.updateMatchScore(matchId, 3, 1);
```

---

## 🌐 Real-Time Features (WebSockets)

### `services/wsService.js`

Singleton WebSocket service for real-time updates:

**Connection:**

- **connect()** - Establish WebSocket connection
- **disconnect()** - Close connection
- **isConnected** - Connection status (reactive)

**Subscriptions:**

- **subscribe(channel, callback)** - Subscribe to channel
- **unsubscribe(channel, callback)** - Unsubscribe
- **send(channel, data)** - Send message

```javascript
// Usage
const wsService = useWebSocketService();
await wsService.connect();

wsService.subscribe("tournament.1", (data) => {
    console.log("Score update:", data);
});

// Auto-reconnect on disconnection (3 second delay)
```

**WebSocket Message Format:**

```json
{
    "channel": "tournament.1",
    "payload": {
        "type": "score_updated",
        "match_id": 5,
        "match": { "id": 5, "player1_score": 3, "player2_score": 1 }
    }
}
```

---

## 🔌 API Integration

### `services/api.js`

Axios HTTP client with automatic token injection and error handling:

```javascript
// Usage
import { api } from "@/services/api";

// Automatically includes Authorization header
const response = await api.get("/tournaments");
const tournament = await api.post("/tournaments", tournamentData);
await api.put(`/tournaments/${id}`, updateData);
await api.delete(`/tournaments/${id}`);
```

**Features:**

- Automatic token injection from localStorage
- 401 response redirects to login
- Configurable base URL via `VITE_API_URL` env var

---

## 🎨 Component Architecture

### TournamentDashboard.vue

Main tournament management view with:

- **Grid Layout** - Responsive tournament cards
- **Filtering** - Search, status, game filters
- **Quick Actions** - Register, view bracket, edit
- **Modal Details** - Tournament information popup
- **Responsive Design** - Mobile-optimized

**Key Features:**

- Real-time tournament list
- Player registration workflow
- Organizer management options
- Bracket generation UI

### BracketView.vue

Tournament bracket visualization with:

- **Round Visualization** - Successive tournament rounds
- **Match Display** - Player names, scores, status
- **Live Updates** - Real-time score synchronization
- **Score Input** - Organizer match result entry
- **Winner Highlighting** - Visual winner indication

**Key Features:**

- Automatic round labeling (Finals, Semifinals, etc.)
- Score validation and submission
- Live WebSocket score updates
- Responsive bracket layout

---

## 🛠️ Setup & Installation

### 1. Install Dependencies

```bash
cd src
npm install
# or
yarn install
```

### 2. Environment Configuration

Create `.env` file in `src/`:

```env
VITE_API_URL=http://localhost:8000/api
VITE_WS_URL=localhost:8000
```

### 3. Development Server

```bash
npm run dev
# Runs on http://localhost:5173 by default
```

### 4. Build for Production

```bash
npm run build
# Output in dist/
```

---

## 📦 Key Dependencies

| Package                     | Purpose              |
| --------------------------- | -------------------- |
| `vue@3.4.0`                 | UI framework         |
| `vue-router@4.2.0`          | Client-side routing  |
| `pinia@2.1.0`               | State management     |
| `axios@1.11.0`              | HTTP requests        |
| `tailwindcss@4.0.0`         | CSS framework        |
| `@vitejs/plugin-vue@5.0.0`  | Vue Vite plugin      |
| `@tailwindcss/vite@4.0.0`   | Tailwind Vite plugin |
| `laravel-vite-plugin@3.0.0` | Laravel integration  |

---

## 🎯 SOLID Principles Implementation

### Single Responsibility

- **Stores**: Each store handles one domain (auth, tournaments)
- **Services**: Separate concerns (API, WebSocket)
- **Components**: Focused on single user interactions

### Open/Closed

- **Role Guards**: Easily extend with new roles
- **WebSocket Service**: Singleton pattern allows flexible subscriptions
- **Pinia Stores**: Composable and extendable

### Liskov Substitution

- **Authentication Flow**: Uniform login/register pattern
- **HTTP Interceptors**: Transparent token handling
- **Error Handling**: Consistent error states

### Interface Segregation

- **Store Methods**: Granular functions (fetchTournaments, updateMatchScore)
- **Component Props**: Only pass necessary data
- **WebSocket API**: Clean subscribe/unsubscribe interface

### Dependency Inversion

- **Injection**: Stores use services (api, wsService)
- **Composition API**: Functional composition over mixins
- **Decoupled Services**: No direct dependencies between services

---

## 🎨 Dark Mode Styling

All components use Tailwind CSS with dark theme optimizations:

- **Background**: `bg-gray-950` (main), `bg-gray-900` (cards)
- **Text**: `text-gray-100` (primary), `text-gray-400` (secondary)
- **Accents**: Purple (`purple-600`) and Pink (`pink-600`) gradients
- **Borders**: `border-gray-800` for dark separation
- **Hover States**: Increased opacity and subtle transitions

**Example Component Styling:**

```vue
<div
    class="bg-gray-900 border border-gray-800 rounded-lg p-4
            hover:border-purple-600/50 transition"
>
  <!-- Dark themed component -->
</div>
```

---

## 🚀 Key Features Demonstration

### Real-Time Score Updates

```javascript
// Components automatically reactify when scores update via WebSocket
wsService.subscribe("tournament.1", (data) => {
    if (data.type === "score_updated") {
        // Match state updates automatically
        // Vue reactivity triggers component re-render
    }
});
```

### Automatic Bracket Generation

```javascript
// When organizer closes registrations:
await tournamentStore.closeRegistrations(tournamentId);
// Backend generates bracket
// WebSocket notifies clients: 'registration_closed'
// Bracket automatically populated and displayed
```

### Player Registration Flow

```javascript
// 1. Player browses tournaments
await tournamentStore.fetchTournaments();

// 2. Player registers
await api.post(`/registrations/${tournamentId}`);

// 3. Once bracket generated, player joins matches
router.push(`/tournaments/${tournamentId}/bracket`);
```

---

## 🔧 Development Tips

### Adding New Routes

```javascript
// router/index.js
{
  path: '/new-feature',
  component: NewFeature,
  beforeEnter: requireRole([ROLES.ORGANIZER])
}
```

### Adding New Store

```javascript
// stores/newStore.js
export const useNewStore = defineStore("newStore", () => {
    const data = ref([]);
    const fetchData = async () => {
        /* ... */
    };
    return { data, fetchData };
});
```

### Subscribing to WebSocket

```javascript
// In any component
const wsService = useWebSocketService();
onMounted(async () => {
    await wsService.connect();
    wsService.subscribe("channel.name", (data) => {
        console.log("Received:", data);
    });
});
```

---

## ✅ Quality Assurance

### Code Quality

- ✓ Vue 3 Composition API best practices
- ✓ Reactive state management with Pinia
- ✓ Type-safe routing guards
- ✓ Proper error handling and loading states
- ✓ Accessible HTML structure

### Performance

- ✓ Lazy route loading (async components)
- ✓ Computed properties for derived state
- ✓ Efficient WebSocket subscriptions
- ✓ Responsive Tailwind CSS (no unused styles)

### Maintainability

- ✓ Clear file organization
- ✓ Consistent naming conventions
- ✓ Docstring comments on complex logic
- ✓ Modular component structure
- ✓ Separated concerns (stores, services, views)

---

## 📝 Next Steps

1. **Backend Integration**: Ensure API endpoints match the expected interface
2. **WebSocket Setup**: Configure Reverb (Laravel) or similar for real-time updates
3. **Environment Variables**: Set up .env files for different environments
4. **Testing**: Add Vue Test Utils and Vitest for unit/integration tests
5. **Deployment**: Build and optimize for production environments

---

## 📞 Support

For issues or questions:

1. Check the component comments for usage examples
2. Review the store documentation in the code
3. Verify WebSocket connection in browser DevTools
4. Check API endpoint responses in Network tab
