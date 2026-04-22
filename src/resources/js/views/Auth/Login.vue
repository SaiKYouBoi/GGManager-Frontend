<template>
    <div class="flex items-center justify-center min-h-[60vh]">
        <div
            class="bg-gray-900 border border-gray-800 rounded-lg p-8 w-full max-w-md space-y-6"
        >
            <div class="text-center space-y-2">
                <h1 class="text-3xl font-bold">Login</h1>
                <p class="text-gray-400">Sign in to your account</p>
            </div>

            <form @submit.prevent="handleLogin" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-2"
                        >Email</label
                    >
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                        placeholder="you@example.com"
                    />
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2"
                        >Password</label
                    >
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                        placeholder="••••••••"
                    />
                </div>

                <div
                    v-if="error"
                    class="bg-red-900/20 border border-red-800 rounded p-3 text-red-400 text-sm"
                >
                    {{ error }}
                </div>

                <button
                    :disabled="loading"
                    type="submit"
                    class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 disabled:opacity-50 px-4 py-2 rounded font-bold transition"
                >
                    {{ loading ? "Logging in..." : "Login" }}
                </button>
            </form>

            <div class="text-center space-y-3 border-t border-gray-800 pt-4">
                <router-link
                    to="/register"
                    class="text-purple-400 hover:text-purple-300"
                >
                    Don't have an account? Register
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "../../stores/auth";

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const form = ref({ email: "", password: "" });
const loading = ref(false);
const error = ref("");

const handleLogin = async () => {
    loading.value = true;
    error.value = "";

    try {
        await authStore.login(form.value.email, form.value.password);
        const redirect = route.query.redirect || "/tournaments";
        router.push(redirect);
    } catch (err) {
        error.value =
            err.response?.data?.message || err.message || "Login failed";
    } finally {
        loading.value = false;
    }
};
</script>
