import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { api } from "../services/api";

export const useAuthStore = defineStore("auth", () => {
    const user = ref(null);
    const isAuthenticated = computed(() => !!user.value);
    const token = ref(localStorage.getItem("auth_token"));

    if (token.value) {
        api.defaults.headers.common["Authorization"] = `Bearer ${token.value}`;
    }

    const login = async (email, password) => {
        try {
            const response = await api.post("/auth/login", { email, password });
            token.value = response.data.token;
            user.value = response.data.user;
            localStorage.setItem("auth_token", token.value);
            api.defaults.headers.common["Authorization"] =
                `Bearer ${token.value}`;
            return response.data;
        } catch (error) {
            console.error("Login failed:", error);
            throw error;
        }
    };

    const register = async (data) => {
        try {
            const response = await api.post("/auth/register", data);
            token.value = response.data.token;
            user.value = response.data.user;
            localStorage.setItem("auth_token", token.value);
            api.defaults.headers.common["Authorization"] =
                `Bearer ${token.value}`;
            return response.data;
        } catch (error) {
            console.error("Registration failed:", error);
            throw error;
        }
    };

    const logout = async () => {
        try {
            await api.post("/auth/logout");
        } catch (error) {
            console.error("Logout failed:", error);
        } finally {
            user.value = null;
            token.value = null;
            localStorage.removeItem("auth_token");
            delete api.defaults.headers.common["Authorization"];
        }
    };

    const fetchUser = async () => {
        try {
            const response = await api.get("/auth/me");
            user.value = response.data;
            return response.data;
        } catch (error) {
            console.error("Failed to fetch user:", error);
            logout();
        }
    };

    return {
        user,
        token,
        isAuthenticated,
        login,
        register,
        logout,
        fetchUser,
    };
});
