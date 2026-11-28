import {
    AreaCode,
    ConnectionStatus,
    ConnectionStatusHandler,
    DirectChannelIdentifier,
    NCEngine
} from "@nexconn/chat";
import { NCChatUIApplication } from "@nexconn/chatui";

const appElement = document.getElementById("nexconnChatApp");
const statusElement = document.getElementById("nexconnChatStatus");

if (appElement && statusElement) {
    const csrfToken = appElement.dataset.csrfToken || "";
    const listingId = Number.parseInt(appElement.dataset.productId || "", 10);
    let chatApp = null;
    const channelListNotReadyCode = 39005;
    const themedShadowRoots = new WeakSet();
    const chatThemeObservers = [];
    const chatThemeStyles = `
        .nc-input-send-button:not(.disabled) {
            background-color: var(--pup-maroon, #800000) !important;
            color: #fff !important;
        }

        .nc-input-send-button:hover:not(.disabled) {
            background-color: var(--pup-maroon-dark, #5f0000) !important;
        }

        .nc-input-send-button.disabled {
            background-color: var(--line, #ebe6e1) !important;
            color: var(--muted, #716b68) !important;
        }

        .nc-chatui-modal-dialog-footer .confirm,
        button.confirm {
            border-color: var(--pup-maroon, #800000) !important;
            background-color: var(--pup-maroon, #800000) !important;
            color: #fff !important;
        }

        .nc-chatui-modal-dialog-footer .confirm:hover:not(:disabled),
        button.confirm:hover:not(:disabled) {
            border-color: var(--pup-maroon-dark, #5f0000) !important;
            background-color: var(--pup-maroon-dark, #5f0000) !important;
        }

        .nc-chatui-modal-dialog-footer .cancel {
            border-color: var(--line, #ebe6e1) !important;
            background-color: var(--canvas, #f8f6f3) !important;
            color: var(--ink, #292524) !important;
        }

        .nc-chatui-connection-status.is-warning {
            background-color: #fff6bf !important;
        }

        .unread-box {
            background-color: var(--pup-maroon, #800000) !important;
            color: #fff !important;
        }

        .unread-box.muted {
            background-color: var(--line, #ebe6e1) !important;
            color: var(--muted, #716b68) !important;
        }

        .nc-reaction-active {
            border-color: var(--pup-maroon, #800000) !important;
            background-color: #f4ece8 !important;
            color: var(--pup-maroon, #800000) !important;
        }

        .nc-reaction-count:hover,
        .nc-reaction-users-count:hover,
        .nc-message-edit-status,
        a:hover {
            color: var(--pup-maroon, #800000) !important;
        }

        .reactions-tab-item-active,
        .line.active {
            background-color: var(--pup-maroon, #800000) !important;
            color: #fff !important;
        }

        .forward-icon {
            border-color: var(--pup-maroon, #800000) !important;
        }

        .nc-chatui-modal-dialog-footer .confirm svg [fill="#0047FF"],
        .nc-chatui-modal-dialog-footer .confirm svg [fill="#0099FF"],
        button.confirm svg [fill="#0047FF"],
        button.confirm svg [fill="#0099FF"] {
            fill: #fff !important;
        }

        svg [fill="#0047FF"],
        svg [fill="#0099FF"],
        svg [stroke="#0047FF"],
        svg [stroke="#0099FF"] {
            fill: var(--pup-maroon, #800000) !important;
            stroke: var(--pup-maroon, #800000) !important;
        }
    `;

    function setStatus(message, state = "loading") {
        statusElement.textContent = message;
        statusElement.dataset.state = state;
    }

    function installChatTheme(element) {
        const shadowRoot = element.shadowRoot;
        if (!shadowRoot || themedShadowRoots.has(shadowRoot)) {
            return;
        }

        themedShadowRoots.add(shadowRoot);
        const themeStyle = document.createElement("style");
        themeStyle.dataset.pupmarketChatTheme = "true";
        themeStyle.textContent = chatThemeStyles;
        shadowRoot.append(themeStyle);

        const observer = new MutationObserver((records) => {
            for (const record of records) {
                for (const node of record.addedNodes) {
                    if (!(node instanceof Element)) {
                        continue;
                    }
                    installChatTheme(node);
                    node.querySelectorAll("*").forEach(installChatTheme);
                }
            }
        });
        observer.observe(shadowRoot, { childList: true, subtree: true });
        chatThemeObservers.push(observer);

        shadowRoot.querySelectorAll("*").forEach(installChatTheme);
    }

    async function openChannelWhenReady(identifier) {
        const deadline = Date.now() + 15000;
        while (true) {
            const result = await chatApp.openChannel(identifier, true);
            if (result.isOk || result.code !== channelListNotReadyCode || Date.now() >= deadline) {
                return result;
            }
            await new Promise((resolve) => window.setTimeout(resolve, 200));
        }
    }

    async function requestJson(url, options) {
        const response = await fetch(url, options);
        let payload;
        try {
            payload = await response.json();
        } catch {
            throw new Error("The chat service returned an unreadable response.");
        }

        if (!payload || typeof payload !== "object" || !response.ok || !payload.success) {
            const message = payload && typeof payload === "object" ? payload.message : "";
            throw new Error(message || "The chat request could not be completed.");
        }

        return payload;
    }

    async function getUserProfiles(userIds) {
        const payload = await requestJson("api/nexconn_profiles.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": csrfToken
            },
            body: JSON.stringify({ user_ids: userIds })
        });
        return payload.profiles;
    }

    async function startChat() {
        setStatus("Connecting to Nexconn…", "loading");

        const requestBody = {};
        if (Number.isInteger(listingId) && listingId > 0) {
            requestBody.listing_id = listingId;
        }

        const session = await requestJson("api/nexconn_token.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": csrfToken
            },
            body: JSON.stringify(requestBody)
        });

        const areaCodes = {
            SG: AreaCode.SG,
            NA: AreaCode.NA,
            SA: AreaCode.SA
        };
        const areaCode = areaCodes[session.region];
        if (areaCode === undefined) {
            throw new Error("The configured Nexconn data center is not supported by this SDK.");
        }

        NCEngine.initialize({ appKey: session.appKey, areaCode });
        chatApp = NCChatUIApplication.initialize({
            language: "en_US",
            hooks: {
                reqUserProfiles: getUserProfiles,
                reqGroupProfiles: async (groupIds) => groupIds.map((groupId) => ({
                    groupId,
                    name: "PUPMarket group",
                    memberCount: 0
                })),
                reqSystemProfiles: async (systemIds) => systemIds.map((systemId) => ({
                    systemId,
                    name: "PUPMarket"
                })),
                reqGroupMembers: async () => [],
                getDefaultUserProfile: (userId) => ({
                    userId,
                    name: "PUPMarket Student"
                })
            }
        });

        if (!chatApp) {
            throw new Error("Nexconn ChatUI could not be initialized.");
        }

        const chatChannels = new Map();
        let channelListLoaded = false;
        let conversationSelected = false;

        function updateUnreadSummary() {
            const unreadChannels = [...chatChannels.values()]
                .filter((channel) => Number(channel.unreadCount) > 0);
            const unreadMessages = unreadChannels.reduce(
                (total, channel) => total + Number(channel.unreadCount),
                0
            );
            const summary = document.getElementById("nexconnUnreadSummary");
            if (!summary) {
                return;
            }

            if (unreadMessages > 0) {
                summary.textContent =
                    `${unreadChannels.length} unread conversation${unreadChannels.length === 1 ? "" : "s"} · ` +
                    `${unreadMessages} unread message${unreadMessages === 1 ? "" : "s"}`;
                summary.dataset.state = "unread";
            } else {
                summary.textContent = "No unread conversations.";
                summary.dataset.state = "clear";
            }
        }

        function updateIdleStatus() {
            if (session.peer || conversationSelected || !channelListLoaded) {
                return;
            }

            if (chatChannels.size === 0) {
                setStatus(
                    "No conversations yet. Contact a seller from a listing to start a chat.",
                    "empty"
                );
            } else {
                setStatus("Your conversations are ready. Choose one to view messages.", "connected");
            }
        }

        chatApp.addEventListener("CHANNELS_LIST_RESET", ({ data }) => {
            if (!Array.isArray(data?.list)) {
                return;
            }
            chatChannels.clear();
            data.list.forEach((channel) => chatChannels.set(channel.channelId, channel));
            channelListLoaded = true;
            updateUnreadSummary();
            updateIdleStatus();
        });
        chatApp.addEventListener("CHANNELS_ITEM_CHANGE", ({ data }) => {
            if (!Array.isArray(data)) {
                return;
            }
            data.forEach((channel) => chatChannels.set(channel.channelId, channel));
            updateUnreadSummary();
            updateIdleStatus();
        });
        chatApp.addEventListener("CHANNELS_ITEM_DELETED", ({ data }) => {
            if (!Array.isArray(data)) {
                return;
            }
            data.forEach((channel) => chatChannels.delete(channel.channelId));
            updateUnreadSummary();
            updateIdleStatus();
        });
        chatApp.addEventListener("CHANNEL_SELECTED", () => {
            conversationSelected = true;
            setStatus("Conversation opened. Messages appear here.", "connected");
        });

        chatApp.setMessageBubbleConfig({
            radius: 16,
            backgroundColorForMyself: 0x800000,
            backgroundColorForOthers: 0xF4ECE8,
            textColorForMyself: 0xFFFFFF,
            textColorForOthers: 0x292524
        });
        chatApp.setChannelsItemConfig({
            radius: 12,
            topBackgroundColor: 0xFFFFFF,
            hoverBackgroundColor: 0xF8F6F3,
            activeBackgroundColor: 0xF4ECE8
        });

        chatApp.ready();
        const chatRoot = document.createElement("nc-chat-ui-app-provider");
        chatRoot.className = "nexconn-chat-root";
        appElement.replaceChildren(chatRoot);
        installChatTheme(chatRoot);

        NCEngine.addConnectionStatusHandler(
            "pupmarket-chat-status",
            new ConnectionStatusHandler({
                onConnectionStatusChanged({ status }) {
                    if (status === ConnectionStatus.CONNECTED) {
                        setStatus("Connected to Nexconn.", "connected");
                    }
                }
            })
        );

        const connection = await NCEngine.connect({ token: session.token });
        if (!connection.isOk) {
            throw new Error(`Could not connect to Nexconn (code ${connection.code}).`);
        }

        setStatus("Connected to Nexconn.", "connected");
        if (session.peer) {
            setStatus("Opening the seller conversation…", "loading");
            const opened = await openChannelWhenReady(
                new DirectChannelIdentifier(session.peer.userId)
            );
            if (!opened.isOk) {
                console.error("Nexconn could not open the seller channel.", { code: opened.code });
                setStatus(
                    `Could not open the seller chat (Nexconn code ${opened.code}). Choose another conversation or try again.`,
                    "error"
                );
            } else {
                setStatus("Conversation opened. You can message the seller here.", "connected");
            }
        } else {
            updateIdleStatus();
        }
    }

    startChat().catch((error) => {
        console.error("Nexconn chat could not be started.", error);
        setStatus(error instanceof Error ? error.message : "Nexconn chat could not be started.", "error");
        chatApp?.destroy();
        chatApp = null;
        appElement.replaceChildren();
    });

    window.addEventListener("pagehide", () => {
        chatThemeObservers.forEach((observer) => observer.disconnect());
        chatApp?.destroy();
        NCEngine.removeConnectionStatusHandler("pupmarket-chat-status");
    }, { once: true });
}
