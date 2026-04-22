import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "../stores/auth";

// Views
const Home = () => import("../views/Home.vue");
const Login = () => import("../views/Auth/Login.vue");
const Register = () => import("../views/Auth/Register.vue");
const TournamentDashboard = () => import("../views/TournamentDashboard.vue");
const BracketView = () => import("../views/BracketView.vue");
const TournamentForm = () => import("../views/TournamentForm.vue");
const NotFound = () => import("../views/NotFound.vue");

// Role enum
export const ROLES = {
    GUEST: "guest",
    PLAYER: "player",
    ORGANIZER: "organizer",
};

// Route guard to check authentication
const requireAuth = (to, from, next) => {
    const authStore = useAuthStore();

    if (!authStore.isAuthenticated) {
        next({ name: "login", query: { redirect: to.fullPath } });
    } else {
        next();
    }
};

// Route guard to check role
const requireRole = (roles) => {
    return (to, from, next) => {
        const authStore = useAuthStore();
        const userRole = authStore.user?.role || ROLES.GUEST;

        if (roles.includes(userRole)) {
            next();
        } else {
            next({ name: "home" });
        }
    };
};

const routes = [
    {
        path: "/",
        name: "home",
        component: Home,
        meta: {
            title: "GGManager - Tournament Management",
            public: true,
        },
    },
    {
        path: "/login",
        name: "login",
        component: Login,
        meta: {
            title: "Login",
            public: true,
        },
    },
    {
        path: "/register",
        name: "register",
        component: Register,
        meta: {
            title: "Register",
            public: true,
        },
    },
    {
        path: "/tournaments",
        component: TournamentDashboard,
        meta: {
            title: "Tournaments",
            requiresAuth: true,
        },
        beforeEnter: requireAuth,
    },
    {
        path: "/tournaments/create",
        name: "create-tournament",
        component: TournamentForm,
        meta: {
            title: "Create Tournament",
            requiresAuth: true,
            roles: [ROLES.ORGANIZER],
        },
        beforeEnter: (to, from, next) => {
            requireAuth(to, from, () => {
                requireRole([ROLES.ORGANIZER])(to, from, next);
            });
        },
    },
    {
        path: "/tournaments/:id/edit",
        name: "edit-tournament",
        component: TournamentForm,
        meta: {
            title: "Edit Tournament",
            requiresAuth: true,
            roles: [ROLES.ORGANIZER],
        },
        beforeEnter: (to, from, next) => {
            requireAuth(to, from, () => {
                requireRole([ROLES.ORGANIZER])(to, from, next);
            });
        },
    },
    {
        path: "/tournaments/:id/bracket",
        name: "tournament-bracket",
        component: BracketView,
        meta: {
            title: "Tournament Bracket",
            requiresAuth: true,
            roles: [ROLES.PLAYER, ROLES.ORGANIZER],
        },
        beforeEnter: (to, from, next) => {
            requireAuth(to, from, () => {
                requireRole([ROLES.PLAYER, ROLES.ORGANIZER])(to, from, next);
            });
        },
    },
    {
        path: "/:pathMatch(.*)*",
        name: "not-found",
        component: NotFound,
        meta: {
            title: "Not Found",
        },
    },
];

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes,
});

// Global navigation guard
router.beforeEach((to, from, next) => {
    document.title = to.meta.title || "GGManager";
    next();
});

export default router;
