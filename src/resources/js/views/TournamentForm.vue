<template>
    <div class="max-w-2xl">
        <div class="flex items-center gap-4 mb-6">
            <router-link
                to="/tournaments"
                class="text-purple-400 hover:text-purple-300"
            >
                ← Back
            </router-link>
            <h1 class="text-3xl font-bold">
                {{ isEditing ? "Edit Tournament" : "Create Tournament" }}
            </h1>
        </div>

        <form
            @submit.prevent="handleSubmit"
            class="bg-gray-900 border border-gray-800 rounded-lg p-6 space-y-6"
        >
            <div>
                <label class="block text-sm font-semibold mb-2"
                    >Tournament Name *</label
                >
                <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2 text-white focus:outline-none focus:border-purple-500"
                    placeholder="e.g., Spring Valorant Championship"
                />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-2"
                        >Game *</label
                    >
                    <select
                        v-model="form.game"
                        required
                        class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2 text-white focus:outline-none focus:border-purple-500"
                    >
                        <option value="">Select a game</option>
                        <option value="valorant">Valorant</option>
                        <option value="csgo">CS:GO</option>
                        <option value="dota2">Dota 2</option>
                        <option value="lol">League of Legends</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2"
                        >Max Participants *</label
                    >
                    <input
                        v-model.number="form.max_participants"
                        type="number"
                        required
                        min="2"
                        class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2 text-white focus:outline-none focus:border-purple-500"
                        placeholder="16"
                    />
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2"
                    >Start Date *</label
                >
                <input
                    v-model="form.start_date"
                    type="datetime-local"
                    required
                    class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2 text-white focus:outline-none focus:border-purple-500"
                />
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2"
                    >Description</label
                >
                <textarea
                    v-model="form.description"
                    rows="4"
                    class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2 text-white focus:outline-none focus:border-purple-500"
                    placeholder="Tournament details and rules..."
                ></textarea>
            </div>

            <div
                v-if="error"
                class="bg-red-900/20 border border-red-800 rounded p-3 text-red-400"
            >
                {{ error }}
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-800">
                <button
                    type="submit"
                    :disabled="loading"
                    class="flex-1 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 disabled:opacity-50 px-4 py-2 rounded font-bold transition"
                >
                    {{
                        loading
                            ? "Saving..."
                            : isEditing
                              ? "Update Tournament"
                              : "Create Tournament"
                    }}
                </button>
                <button
                    type="button"
                    @click="() => $router.back()"
                    class="flex-1 bg-gray-800 hover:bg-gray-700 px-4 py-2 rounded font-bold transition"
                >
                    Cancel
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useTournamentStore } from "../stores/tournament";

const router = useRouter();
const route = useRoute();
const tournamentStore = useTournamentStore();

const form = ref({
    name: "",
    game: "",
    max_participants: 16,
    start_date: "",
    description: "",
});

const loading = ref(false);
const error = ref("");
const isEditing = computed(() => !!route.params.id);

const handleSubmit = async () => {
    loading.value = true;
    error.value = "";

    try {
        if (isEditing.value) {
            await tournamentStore.updateTournament(route.params.id, form.value);
        } else {
            await tournamentStore.createTournament(form.value);
        }
        router.push("/tournaments");
    } catch (err) {
        error.value =
            err.response?.data?.message ||
            err.message ||
            "Failed to save tournament";
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    if (isEditing.value) {
        try {
            await tournamentStore.fetchTournament(route.params.id);
            const tournament = tournamentStore.currentTournament;
            form.value = {
                name: tournament.name,
                game: tournament.game,
                max_participants: tournament.max_participants,
                start_date: tournament.start_date,
                description: tournament.description,
            };
        } catch (err) {
            error.value = "Failed to load tournament";
        }
    }
});
</script>
