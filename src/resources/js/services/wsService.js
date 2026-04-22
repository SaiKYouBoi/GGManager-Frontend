import { ref } from "vue";

let wsService = null;

export const useWebSocketService = () => {
    if (wsService) return wsService;

    const subscribers = new Map();
    const ws = ref(null);
    const isConnected = ref(false);

    const getWsUrl = () => {
        const protocol = window.location.protocol === "https:" ? "wss:" : "ws:";
        const host = import.meta.env.VITE_WS_URL || window.location.host;
        return `${protocol}//${host}`;
    };

    const connect = () => {
        return new Promise((resolve, reject) => {
            try {
                ws.value = new WebSocket(getWsUrl());

                ws.value.onopen = () => {
                    console.log("WebSocket connected");
                    isConnected.value = true;
                    resolve();
                };

                ws.value.onmessage = (event) => {
                    try {
                        const data = JSON.parse(event.data);
                        const channel = data.channel;

                        if (subscribers.has(channel)) {
                            const callbacks = subscribers.get(channel);
                            callbacks.forEach((callback) =>
                                callback(data.payload),
                            );
                        }
                    } catch (error) {
                        console.error(
                            "Error processing WebSocket message:",
                            error,
                        );
                    }
                };

                ws.value.onerror = (error) => {
                    console.error("WebSocket error:", error);
                    isConnected.value = false;
                    reject(error);
                };

                ws.value.onclose = () => {
                    console.log("WebSocket disconnected");
                    isConnected.value = false;
                    // Attempt to reconnect after 3 seconds
                    setTimeout(() => connect(), 3000);
                };
            } catch (error) {
                reject(error);
            }
        });
    };

    const subscribe = (channel, callback) => {
        if (!subscribers.has(channel)) {
            subscribers.set(channel, []);
        }
        subscribers.get(channel).push(callback);

        // Send subscription message
        if (isConnected.value && ws.value) {
            ws.value.send(
                JSON.stringify({
                    action: "subscribe",
                    channel,
                }),
            );
        }
    };

    const unsubscribe = (channel, callback) => {
        if (subscribers.has(channel)) {
            const callbacks = subscribers.get(channel);
            const index = callbacks.indexOf(callback);
            if (index > -1) {
                callbacks.splice(index, 1);
            }
            if (callbacks.length === 0) {
                subscribers.delete(channel);

                // Send unsubscribe message
                if (isConnected.value && ws.value) {
                    ws.value.send(
                        JSON.stringify({
                            action: "unsubscribe",
                            channel,
                        }),
                    );
                }
            }
        }
    };

    const send = (channel, data) => {
        if (isConnected.value && ws.value) {
            ws.value.send(
                JSON.stringify({
                    channel,
                    payload: data,
                }),
            );
        }
    };

    const disconnect = () => {
        if (ws.value) {
            ws.value.close();
            ws.value = null;
        }
        subscribers.clear();
    };

    wsService = {
        connect,
        subscribe,
        unsubscribe,
        send,
        disconnect,
        isConnected,
        ws,
    };

    return wsService;
};
