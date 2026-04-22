import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { api } from "../services/api";
import { useWebSocketService } from "../services/wsService";

export const useTournamentStore = defineStore("tournament", () => {
    const tournaments = ref([]);
    const currentTournament = ref(null);
    const matches = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const wsService = useWebSocketService();

    // Computed properties
    const activeTournaments = computed(() =>
        tournaments.value.filter((t) => t.status === "active"),
    );

    const closedRegistrationTournaments = computed(() =>
        tournaments.value.filter((t) => t.registrations_closed),
    );

    // Fetch all tournaments
    const fetchTournaments = async (filters = {}) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.get("/tournaments", { params: filters });
            tournaments.value = response.data.data || response.data;
            return tournaments.value;
        } catch (err) {
            error.value = err.message;
            console.error("Failed to fetch tournaments:", err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Fetch single tournament
    const fetchTournament = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.get(`/tournaments/${id}`);
            currentTournament.value = response.data.data || response.data;

            // Subscribe to real-time updates for this tournament
            subscribeToTournament(id);

            return currentTournament.value;
        } catch (err) {
            error.value = err.message;
            console.error("Failed to fetch tournament:", err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Create tournament
    const createTournament = async (data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post("/tournaments", data);
            const newTournament = response.data.data || response.data;
            tournaments.value.push(newTournament);
            return newTournament;
        } catch (err) {
            error.value = err.message;
            console.error("Failed to create tournament:", err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Update tournament
    const updateTournament = async (id, data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.put(`/tournaments/${id}`, data);
            const updated = response.data.data || response.data;
            const index = tournaments.value.findIndex((t) => t.id === id);
            if (index !== -1) {
                tournaments.value[index] = updated;
            }
            if (currentTournament.value?.id === id) {
                currentTournament.value = updated;
            }
            return updated;
        } catch (err) {
            error.value = err.message;
            console.error("Failed to update tournament:", err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Delete tournament
    const deleteTournament = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            await api.delete(`/tournaments/${id}`);
            tournaments.value = tournaments.value.filter((t) => t.id !== id);
            if (currentTournament.value?.id === id) {
                currentTournament.value = null;
            }
            return true;
        } catch (err) {
            error.value = err.message;
            console.error("Failed to delete tournament:", err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Close registrations
    const closeRegistrations = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post(
                `/tournaments/${id}/close-registrations`,
            );
            const updated = response.data.data || response.data;
            const index = tournaments.value.findIndex((t) => t.id === id);
            if (index !== -1) {
                tournaments.value[index] = updated;
            }
            if (currentTournament.value?.id === id) {
                currentTournament.value = updated;
            }
            return updated;
        } catch (err) {
            error.value = err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Fetch matches for tournament
    const fetchMatches = async (tournamentId) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.get(
                `/tournaments/${tournamentId}/matches`,
            );
            matches.value = response.data.data || response.data;
            return matches.value;
        } catch (err) {
            error.value = err.message;
            console.error("Failed to fetch matches:", err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Update match score
    const updateMatchScore = async (matchId, player1Score, player2Score) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post(`/matches/${matchId}/score`, {
                player1_score: player1Score,
                player2_score: player2Score,
            });
            const updated = response.data.data || response.data;
            const index = matches.value.findIndex((m) => m.id === matchId);
            if (index !== -1) {
                matches.value[index] = updated;
            }
            return updated;
        } catch (err) {
            error.value = err.message;
            console.error("Failed to update match score:", err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // WebSocket integration
    const subscribeToTournament = (tournamentId) => {
        wsService.subscribe(`tournament.${tournamentId}`, (data) => {
            handleRealtimeUpdate(data);
        });
    };

    const unsubscribeFromTournament = (tournamentId) => {
        wsService.unsubscribe(`tournament.${tournamentId}`);
    };

    const handleRealtimeUpdate = (data) => {
        if (data.type === "score_updated") {
            const index = matches.value.findIndex(
                (m) => m.id === data.match_id,
            );
            if (index !== -1) {
                matches.value[index] = data.match;
            }
        } else if (data.type === "match_created") {
            matches.value.push(data.match);
        } else if (data.type === "registration_closed") {
            if (currentTournament.value?.id === data.tournament_id) {
                currentTournament.value.registrations_closed = true;
                currentTournament.value.bracket = data.bracket;
            }
        }
    };

    return {
        tournaments,
        currentTournament,
        matches,
        loading,
        error,
        activeTournaments,
        closedRegistrationTournaments,
        fetchTournaments,
        fetchTournament,
        createTournament,
        updateTournament,
        deleteTournament,
        closeRegistrations,
        fetchMatches,
        updateMatchScore,
        subscribeToTournament,
        unsubscribeFromTournament,
    };
});
