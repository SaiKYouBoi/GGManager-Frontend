<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <router-link
                to="/tournaments"
                class="text-purple-400 hover:text-purple-300"
            >
                ← Back to Tournaments
            </router-link>
            <h1 class="text-3xl font-bold" v-if="tournament">
                {{ tournament.name }} - Bracket
            </h1>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center h-40">
            <div class="animate-spin">
                <div
                    class="w-10 h-10 border-4 border-gray-700 border-t-purple-500 rounded-full"
                ></div>
            </div>
        </div>

        <!-- Bracket Visualization -->
        <div
            v-else
            class="bg-gray-900 border border-gray-800 rounded-lg p-6 overflow-x-auto"
        >
            <div
                v-if="bracket && bracket.rounds.length > 0"
                class="flex gap-8 min-w-max pb-4"
            >
                <!-- Each Round -->
                <div
                    v-for="(round, roundIndex) in bracket.rounds"
                    :key="roundIndex"
                    class="flex flex-col gap-4"
                >
                    <!-- Round Label -->
                    <div class="text-center">
                        <h3 class="font-semibold text-gray-300">
                            {{
                                getRoundLabel(roundIndex, bracket.rounds.length)
                            }}
                        </h3>
                        <p class="text-xs text-gray-500">
                            {{ round.matches.length }} match(es)
                        </p>
                    </div>

                    <!-- Matches in Round -->
                    <div class="flex flex-col gap-6 justify-center">
                        <div
                            v-for="match in round.matches"
                            :key="match.id"
                            class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden hover:border-purple-600/50 transition w-80"
                        >
                            <!-- Match Header -->
                            <div
                                class="bg-gradient-to-r from-purple-600/10 to-pink-600/10 border-b border-gray-700 px-4 py-2"
                            >
                                <p class="text-xs text-gray-400">
                                    Match #{{ match.id }} - Round
                                    {{ roundIndex + 1 }}
                                </p>
                            </div>

                            <!-- Players -->
                            <div class="divide-y divide-gray-700">
                                <!-- Player 1 -->
                                <div
                                    :class="[
                                        'px-4 py-3 flex items-center justify-between',
                                        match.winner_id === match.player1_id &&
                                        match.player1_score !== null
                                            ? 'bg-green-600/10 border-l-2 border-green-500'
                                            : 'hover:bg-gray-700/50',
                                    ]"
                                >
                                    <div class="flex-1">
                                        <p class="font-semibold">
                                            {{
                                                getPlayerName(match.player1_id)
                                            }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ match.player1?.rank }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2 ml-4">
                                        <span
                                            v-if="match.player1_score !== null"
                                            :class="[
                                                'px-3 py-1 rounded font-bold text-lg',
                                                match.winner_id ===
                                                match.player1_id
                                                    ? 'bg-green-600 text-white'
                                                    : 'bg-gray-700 text-gray-300',
                                            ]"
                                        >
                                            {{ match.player1_score }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-gray-500 text-sm"
                                            >-</span
                                        >
                                    </div>
                                </div>

                                <!-- Player 2 -->
                                <div
                                    :class="[
                                        'px-4 py-3 flex items-center justify-between',
                                        match.winner_id === match.player2_id &&
                                        match.player2_score !== null
                                            ? 'bg-green-600/10 border-l-2 border-green-500'
                                            : 'hover:bg-gray-700/50',
                                    ]"
                                >
                                    <div class="flex-1">
                                        <p class="font-semibold">
                                            {{
                                                getPlayerName(match.player2_id)
                                            }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ match.player2?.rank }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2 ml-4">
                                        <span
                                            v-if="match.player2_score !== null"
                                            :class="[
                                                'px-3 py-1 rounded font-bold text-lg',
                                                match.winner_id ===
                                                match.player2_id
                                                    ? 'bg-green-600 text-white'
                                                    : 'bg-gray-700 text-gray-300',
                                            ]"
                                        >
                                            {{ match.player2_score }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-gray-500 text-sm"
                                            >-</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Match Status -->
                            <div
                                class="bg-gray-800 px-4 py-2 border-t border-gray-700 flex items-center justify-between"
                            >
                                <span
                                    v-if="
                                        match.player1_score !== null &&
                                        match.player2_score !== null
                                    "
                                    class="text-xs font-semibold text-green-400"
                                >
                                    ✓ Completed
                                </span>
                                <span v-else class="text-xs text-gray-500"
                                    >Pending</span
                                >

                                <!-- Score Input (Organizer Only) -->
                                <button
                                    v-if="isOrganizer && !match.winner_id"
                                    @click="showScoreInput(match)"
                                    class="text-xs bg-blue-600 hover:bg-blue-700 px-2 py-1 rounded transition"
                                >
                                    Set Score
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="text-center text-gray-400">
                Bracket not yet generated. Registrations must be closed first.
            </div>
        </div>

        <!-- Score Input Modal -->
        <div
            v-if="scoreInputMatch"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
            @click="scoreInputMatch = null"
        >
            <div
                class="bg-gray-900 border border-gray-800 rounded-lg p-6 max-w-sm w-full mx-4"
                @click.stop
            >
                <h3 class="text-xl font-bold mb-4">Set Match Score</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-2"
                            >{{
                                getPlayerName(scoreInputMatch.player1_id)
                            }}
                            Score</label
                        >
                        <input
                            v-model.number="scoreInput.player1"
                            type="number"
                            min="0"
                            class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2 text-white"
                        />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-2"
                            >{{
                                getPlayerName(scoreInputMatch.player2_id)
                            }}
                            Score</label
                        >
                        <input
                            v-model.number="scoreInput.player2"
                            type="number"
                            min="0"
                            class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2 text-white"
                        />
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="submitScore"
                            class="flex-1 bg-green-600 hover:bg-green-700 px-4 py-2 rounded font-semibold transition"
                        >
                            Submit
                        </button>
                        <button
                            @click="scoreInputMatch = null"
                            class="flex-1 bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded font-semibold transition"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Updates Indicator -->
        <div
            v-if="wsConnected"
            class="fixed bottom-6 right-6 bg-green-600 text-white px-4 py-2 rounded-lg flex items-center gap-2"
        >
            <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
            Live Updates
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRoute } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { useTournamentStore } from "../stores/tournament";
import { useWebSocketService } from "../services/wsService";

const route = useRoute();
const authStore = useAuthStore();
const tournamentStore = useTournamentStore();
const wsService = useWebSocketService();

const tournament = ref(null);
const bracket = ref(null);
const loading = ref(true);
const scoreInputMatch = ref(null);
const scoreInput = ref({ player1: 0, player2: 0 });
const wsConnected = ref(false);

const isOrganizer = computed(() => authStore.user?.role === "organizer");

const getRoundLabel = (index, totalRounds) => {
    const labels = ["Finals", "Semifinals", "Quarterfinals", "Round of 16"];
    const reverseIndex = totalRounds - index - 1;
    return labels[reverseIndex] || `Round ${reverseIndex + 1}`;
};

const getPlayerName = (playerId) => {
    const match = bracket.value?.rounds
        .flatMap((r) => r.matches)
        .find((m) => m.player1_id === playerId || m.player2_id === playerId);
    if (match?.player1_id === playerId)
        return match.player1?.name || "Unknown Player";
    if (match?.player2_id === playerId)
        return match.player2?.name || "Unknown Player";
    return "TBD";
};

const showScoreInput = (match) => {
    scoreInputMatch.value = match;
    scoreInput.value = {
        player1: match.player1_score || 0,
        player2: match.player2_score || 0,
    };
};

const submitScore = async () => {
    if (scoreInputMatch.value) {
        try {
            await tournamentStore.updateMatchScore(
                scoreInputMatch.value.id,
                scoreInput.value.player1,
                scoreInput.value.player2,
            );
            scoreInputMatch.value = null;
        } catch (error) {
            console.error("Failed to update match score:", error);
        }
    }
};

const handleScoreUpdate = (data) => {
    if (data.match) {
        const match = bracket.value?.rounds
            .flatMap((r) => r.matches)
            .find((m) => m.id === data.match.id);
        if (match) {
            Object.assign(match, data.match);
        }
    }
};

onMounted(async () => {
    try {
        await tournamentStore.fetchTournament(route.params.id);
        tournament.value = tournamentStore.currentTournament;
        bracket.value = tournament.value?.bracket;

        // Connect WebSocket and subscribe to updates
        await wsService.connect();
        wsConnected.value = wsService.isConnected.value;
        wsService.subscribe(`tournament.${route.params.id}`, handleScoreUpdate);

        tournamentStore.subscribeToTournament(route.params.id);
    } catch (error) {
        console.error("Failed to load bracket:", error);
    } finally {
        loading.value = false;
    }
});

onUnmounted(() => {
    tournamentStore.unsubscribeFromTournament(route.params.id);
});
</script>
