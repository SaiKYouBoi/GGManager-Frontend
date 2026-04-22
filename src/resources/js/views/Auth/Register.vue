<template>
    <div class="flex items-center justify-center min-h-[60vh]">
        <div
            class="bg-gray-900 border border-gray-800 rounded-lg p-8 w-full max-w-md space-y-6"
        >
            <div class="text-center space-y-2">
                <h1 class="text-3xl font-bold">Register</h1>
                <p class="text-gray-400">Create your account</p>
            </div>

            <form @submit.prevent="handleRegister" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-2"
                        >Full Name</label
                    >
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                        placeholder="John Doe"
                    />
                </div>

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

                <div>
                    <label class="block text-sm font-semibold mb-2">Role</label>
                    <select
                        v-model="form.role"
                        class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2 text-white focus:outline-none focus:border-purple-500"
                    >
                        <option value="player">Player</option>
                        <option value="organizer">Organizer</option>
                    </select>
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
                    {{ loading ? "Registering..." : "Register" }}
                </button>
            </form>

            <div class="text-center space-y-3 border-t border-gray-800 pt-4">
                <router-link
                    to="/login"
                    class="text-purple-400 hover:text-purple-300"
                >
                    Already have an account? Login
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
    name: "",
    email: "",
    password: "",
    role: "player",
});
const loading = ref(false);
const error = ref("");

const handleRegister = async () => {
    loading.value = true;
    error.value = "";

    try {
        await authStore.register(form.value);
        router.push("/tournaments");
    } catch (err) {
        error.value =
            err.response?.data?.message || err.message || "Registration failed";
    } finally {
        loading.value = false;
    }
};
</script>
