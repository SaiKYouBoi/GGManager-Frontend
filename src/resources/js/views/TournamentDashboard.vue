<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold">Tournaments</h1>
            <router-link
                v-if="authStore.user?.role === 'organizer'"
                to="/tournaments/create"
                class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 px-6 py-2 rounded-lg font-semibold transition"
            >
                + Create Tournament
            </router-link>
        </div>

        <!-- Filters -->
        <div
            class="bg-gray-900 border border-gray-800 rounded-lg p-4 space-y-4"
        >
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input
                    v-model="filters.search"
                    type="text"
                    placeholder="Search tournaments..."
                    class="bg-gray-800 border border-gray-700 rounded px-3 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
                />
                <select
                    v-model="filters.status"
                    class="bg-gray-800 border border-gray-700 rounded px-3 py-2 text-white focus:outline-none focus:border-purple-500"
                >
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select
                    v-model="filters.game"
                    class="bg-gray-800 border border-gray-700 rounded px-3 py-2 text-white focus:outline-none focus:border-purple-500"
                >
                    <option value="">All Games</option>
                    <option value="valorant">Valorant</option>
                    <option value="csgo">CS:GO</option>
                    <option value="dota2">Dota 2</option>
                    <option value="lol">League of Legends</option>
                </select>
            </div>
        </div>

        <!-- Loading State -->
        <div
            v-if="tournamentStore.loading"
            class="flex justify-center items-center h-40"
        >
            <div class="animate-spin">
                <div
                    class="w-10 h-10 border-4 border-gray-700 border-t-purple-500 rounded-full"
                ></div>
            </div>
        </div>

        <!-- Error State -->
        <div
            v-if="tournamentStore.error"
            class="bg-red-900/20 border border-red-800 rounded-lg p-4 text-red-400"
        >
            {{ tournamentStore.error }}
        </div>

        <!-- Tournaments Grid -->
        <div
            v-if="filteredTournaments.length > 0"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
        >
            <div
                v-for="tournament in filteredTournaments"
                :key="tournament.id"
                class="bg-gray-900 border border-gray-800 rounded-lg overflow-hidden hover:border-purple-600/50 transition group cursor-pointer"
                @click="() => selectTournament(tournament)"
            >
                <!-- Tournament Card Header -->
                <div
                    class="bg-gradient-to-r from-purple-600/10 to-pink-600/10 border-b border-gray-800 p-4"
                >
                    <h3
                        class="text-lg font-bold text-white group-hover:text-purple-400 transition"
                    >
                        {{ tournament.name }}
                    </h3>
                    <p class="text-sm text-gray-400">{{ tournament.game }}</p>
                </div>

                <!-- Tournament Card Body -->
                <div class="p-4 space-y-3">
                    <!-- Status Badge -->
                    <div class="flex items-center gap-2">
                        <span
                            :class="[
                                'px-3 py-1 rounded-full text-xs font-semibold',
                                tournament.status === 'active'
                                    ? 'bg-green-600/20 text-green-400'
                                    : tournament.status === 'completed'
                                      ? 'bg-blue-600/20 text-blue-400'
                                      : 'bg-red-600/20 text-red-400',
                            ]"
                        >
                            {{ tournament.status }}
                        </span>
                        <span
                            v-if="tournament.registrations_closed"
                            class="px-3 py-1 rounded-full text-xs bg-purple-600/20 text-purple-400"
                        >
                            Bracket Generated
                        </span>
                    </div>

                    <!-- Tournament Details -->
                    <div class="space-y-2 text-sm text-gray-400">
                        <p>📅 {{ formatDate(tournament.start_date) }}</p>
                        <p>
                            👥 {{ tournament.participants_count }}/{{
                                tournament.max_participants
                            }}
                            Players
                        </p>
                        <p v-if="tournament.organizer">
                            🏢 {{ tournament.organizer.name }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2 pt-3 border-t border-gray-800">
                        <router-link
                            v-if="tournament.registrations_closed"
                            :to="`/tournaments/${tournament.id}/bracket`"
                            class="flex-1 bg-purple-600 hover:bg-purple-700 text-center px-3 py-2 rounded text-sm font-semibold transition"
                        >
                            View Bracket
                        </router-link>
                        <button
                            v-if="
                                authStore.user?.id ===
                                    tournament.organizer_id &&
                                campaign.status === 'active'
                            "
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-center px-3 py-2 rounded text-sm font-semibold transition"
                            @click.stop="joinTournament(tournament)"
                        >
                            Close Registrations
                        </button>
                        <router-link
                            v-if="
                                authStore.user?.id === tournament.organizer_id
                            "
                            :to="`/tournaments/${tournament.id}/edit`"
                            class="flex-1 bg-gray-700 hover:bg-gray-600 text-center px-3 py-2 rounded text-sm font-semibold transition"
                            @click.stop
                        >
                            Edit
                        </router-link>
                        <button
                            v-else
                            class="flex-1 bg-pink-600 hover:bg-pink-700 text-center px-3 py-2 rounded text-sm font-semibold transition"
                            @click.stop="joinTournament(tournament)"
                        >
                            Register
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
            <p class="text-gray-400 text-lg">No tournaments found</p>
        </div>

        <!-- Tournament Detail Modal -->
        <div
            v-if="selectedTournament"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
            @click="selectedTournament = null"
        >
            <div
                class="bg-gray-900 border border-gray-800 rounded-lg p-6 max-w-lg w-full mx-4"
                @click.stop
            >
                <h2 class="text-2xl font-bold mb-4">
                    {{ selectedTournament.name }}
                </h2>
                <div class="space-y-3 text-gray-300 mb-6">
                    <p><strong>Game:</strong> {{ selectedTournament.game }}</p>
                    <p>
                        <strong>Status:</strong> {{ selectedTournament.status }}
                    </p>
                    <p>
                        <strong>Start Date:</strong>
                        {{ formatDate(selectedTournament.start_date) }}
                    </p>
                    <p>
                        <strong>Max Participants:</strong>
                        {{ selectedTournament.max_participants }}
                    </p>
                    <p>
                        <strong>Current Registrations:</strong>
                        {{ selectedTournament.participants_count }}
                    </p>
                    <p>
                        <strong>Organizer:</strong>
                        {{ selectedTournament.organizer?.name }}
                    </p>
                </div>
                <button
                    @click="selectedTournament = null"
                    class="w-full bg-gray-800 hover:bg-gray-700 px-4 py-2 rounded font-semibold transition"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useAuthStore } from "../stores/auth";
import { useTournamentStore } from "../stores/tournament";

const authStore = useAuthStore();
const tournamentStore = useTournamentStore();

const filters = ref({
    search: "",
    status: "",
    game: "",
});

const selectedTournament = ref(null);

const filteredTournaments = computed(() => {
    return tournamentStore.tournaments.filter((tournament) => {
        const matchesSearch = tournament.name
            .toLowerCase()
            .includes(filters.value.search.toLowerCase());
        const matchesStatus =
            !filters.value.status || tournament.status === filters.value.status;
        const matchesGame =
            !filters.value.game || tournament.game === filters.value.game;
        return matchesSearch && matchesStatus && matchesGame;
    });
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const selectTournament = (tournament) => {
    selectedTournament.value = tournament;
};

const joinTournament = async (tournament) => {
    try {
        await tournamentStore.fetchTournament(tournament.id);
        // Make API call to join
        selectedTournament.value = null;
    } catch (error) {
        console.error("Failed to join tournament:", error);
    }
};

onMounted(async () => {
    await tournamentStore.fetchTournaments();
});
</script>
