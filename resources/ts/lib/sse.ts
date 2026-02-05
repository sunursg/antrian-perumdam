import type { DisplayState } from "./types";

type DisplaySseOptions = {
  onState: (state: DisplayState) => void;
  onOpen?: () => void;
  onError?: () => void;
};

export const connectDisplaySse = ({
  onState,
  onOpen,
  onError,
}: DisplaySseOptions): EventSource => {
  const es = new EventSource("/api/sse/queue");
  let refreshPromise: Promise<void> | null = null;

  const refreshState = () => {
    if (refreshPromise) return;
    refreshPromise = fetch("/api/public/display/state", {
      credentials: "same-origin",
      headers: {
        Accept: "application/json",
      },
    })
      .then((response) => (response.ok ? response.json() : null))
      .then((payload) => {
        if (!payload) return;
        const normalized =
          typeof payload === "object" &&
          payload !== null &&
          "data" in payload &&
          (payload as { data?: DisplayState }).data
            ? (payload as { data: DisplayState }).data
            : (payload as DisplayState);
        onState(normalized);
      })
      .catch(() => undefined)
      .finally(() => {
        refreshPromise = null;
      });
  };

  es.addEventListener("open", () => onOpen?.());

  es.addEventListener("display_state", (event) => {
    try {
      const parsed = JSON.parse((event as MessageEvent).data) as
        | DisplayState
        | { data: DisplayState };
      const payload =
        typeof parsed === "object" && parsed !== null && "data" in parsed
          ? (parsed as { data: DisplayState }).data
          : (parsed as DisplayState);
      onState(payload);
    } catch {
      // ignore malformed payload
    }
  });

  es.addEventListener("error", () => {
    onError?.();
  });

  // Existing backend emits queue_update / counter_status_update / announcement_update / organization_update.
  ["queue_update", "counter_status_update", "announcement_update", "organization_update"].forEach(
    (name) => {
      es.addEventListener(name, () => {
        refreshState();
      });
    }
  );

  return es;
};
