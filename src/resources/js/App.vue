<template>
    <div class="min-h-screen bg-gray-950 text-gray-100">
        <!-- Navigation -->
        <nav
            class="border-b border-gray-800 bg-gray-900/50 backdrop-blur-sm sticky top-0 z-40"
        >
            <div
                class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between"
            >
                <router-link
                    to="/"
                    class="text-2xl font-bold bg-gradient-to-r from-purple-500 to-pink-500 bg-clip-text text-transparent"
                >
                    🎮 GGManager
                </router-link>

                <div class="flex items-center gap-6">
                    <router-link
                        v-if="!authStore.isAuthenticated"
                        to="/login"
                        class="text-gray-400 hover:text-white transition"
                    >
                        Login
                    </router-link>
                    <router-link
                        v-if="!authStore.isAuthenticated"
                        to="/register"
                        class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-lg transition"
                    >
                        Register
                    </router-link>

                    <template v-if="authStore.isAuthenticated">
                        <span class="text-gray-400 text-sm">{{
                            authStore.user?.name
                        }}</span>
                        <span
                            v-if="authStore.user?.role === 'organizer'"
                            class="bg-blue-600/20 text-blue-400 px-3 py-1 rounded-full text-xs"
                        >
                            Organizer
                        </span>
                        <span
                            v-else-if="authStore.user?.role === 'player'"
                            class="bg-green-600/20 text-green-400 px-3 py-1 rounded-full text-xs"
                        >
                            Player
                        </span>
                        <button
                            @click="logout"
                            class="text-gray-400 hover:text-white transition"
                        >
                            Logout
                        </button>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 py-8">
            <router-view />
        </main>
    </div>
</template>

<script setup>
import { useAuthStore } from "./stores/auth";

const authStore = useAuthStore();

const logout = async () => {
    await authStore.logout();
};
</script>

<style scoped>
:deep(*) {
    @apply transition-colors duration-200;
}
</style>
