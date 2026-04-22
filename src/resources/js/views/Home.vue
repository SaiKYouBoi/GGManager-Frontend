<template>
    <div class="space-y-8">
        <!-- Hero Section -->
        <div class="text-center py-12 space-y-6">
            <h1
                class="text-5xl font-bold bg-gradient-to-r from-purple-500 to-pink-500 bg-clip-text text-transparent"
            >
                🎮 Welcome to GGManager
            </h1>
            <p class="text-xl text-gray-400 max-w-2xl mx-auto">
                The ultimate platform for organizing and managing e-sports
                tournaments with real-time bracket updates and live scoring.
            </p>
            <div class="flex gap-4 justify-center flex-wrap">
                <router-link
                    v-if="!authStore.isAuthenticated"
                    to="/register"
                    class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 px-8 py-3 rounded-lg font-bold transition"
                >
                    Get Started
                </router-link>
                <router-link
                    to="/tournaments"
                    class="bg-gray-800 hover:bg-gray-700 px-8 py-3 rounded-lg font-bold transition border border-gray-700"
                >
                    Browse Tournaments
                </router-link>
            </div>
        </div>

        <!-- Features -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div
                class="bg-gray-900 border border-gray-800 rounded-lg p-6 space-y-3"
            >
                <p class="text-3xl">🏆</p>
                <h3 class="text-lg font-bold">Tournament Management</h3>
                <p class="text-gray-400 text-sm">
                    Create and manage tournaments with flexible settings and
                    constraints.
                </p>
            </div>
            <div
                class="bg-gray-900 border border-gray-800 rounded-lg p-6 space-y-3"
            >
                <p class="text-3xl">⚡</p>
                <h3 class="text-lg font-bold">Real-Time Updates</h3>
                <p class="text-gray-400 text-sm">
                    WebSocket-powered live scoring and bracket generation for
                    instant updates.
                </p>
            </div>
            <div
                class="bg-gray-900 border border-gray-800 rounded-lg p-6 space-y-3"
            >
                <p class="text-3xl">👥</p>
                <h3 class="text-lg font-bold">Role-Based Access</h3>
                <p class="text-gray-400 text-sm">
                    Granular permissions for players, organizers, and
                    spectators.
                </p>
            </div>
        </div>

        <!-- CTA Section -->
        <div
            v-if="!authStore.isAuthenticated"
            class="bg-gradient-to-r from-purple-600/20 to-pink-600/20 border border-purple-600/30 rounded-lg p-8 text-center space-y-4"
        >
            <h2 class="text-2xl font-bold">Ready to Compete?</h2>
            <p class="text-gray-300">
                Sign up now to join tournaments or create your own.
            </p>
            <router-link
                to="/register"
                class="inline-block bg-purple-600 hover:bg-purple-700 px-6 py-2 rounded-lg font-bold transition"
            >
                Join Now
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { useAuthStore } from "../stores/auth";

const authStore = useAuthStore();
</script>
