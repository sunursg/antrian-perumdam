import React from "react";
import TopBar from "../components/TopBar";
import QueueCallPanel from "../components/QueueCallPanel";
import VideoFrame from "../components/VideoFrame";
import { connectDisplaySse } from "../lib/sse";
import { getDisplayState } from "../lib/api";
import type { DisplayState } from "../lib/types";

const fallbackCompany = {
  name: "PERUMDAM TIRTA PERWIRA",
  slogan: "Melayani dengan Sepenuh Hati",
  logo_url: "/logo.png",
};

export default function Display() {
  const [state, setState] = React.useState<DisplayState | null>(null);
  const [connection, setConnection] = React.useState<"sse" | "poll" | "idle">(
    "idle"
  );
  const [lastUpdated, setLastUpdated] = React.useState<Date | null>(null);

  const loadState = React.useCallback(async () => {
    const data = await getDisplayState();
    if (data) {
      setState(data);
      setLastUpdated(new Date());
    }
  }, []);

  React.useEffect(() => {
    let pollTimer: number | null = null;
    let source: EventSource | null = null;

    const startPolling = () => {
      if (pollTimer) return;
      setConnection("poll");
      // Polling fallback when SSE is unavailable.
      pollTimer = window.setInterval(() => {
        loadState().catch(() => undefined);
      }, 3000);
    };

    loadState().catch(() => undefined);

    if ("EventSource" in window) {
      source = connectDisplaySse({
        onOpen: () => setConnection("sse"),
        onState: (data) => {
          setState(data);
          setLastUpdated(new Date());
        },
        onError: () => {
          source?.close();
          startPolling();
        },
      });
    } else {
      startPolling();
    }

    return () => {
      if (source) source.close();
      if (pollTimer) window.clearInterval(pollTimer);
    };
  }, [loadState]);

  const company = state?.company ?? fallbackCompany;
  const nowServing = state?.now_serving;
  const activeAnnouncement =
    state?.announcements?.find((item) => item.active) ??
    state?.announcements?.[0] ??
    null;

  const announcementText =
    activeAnnouncement?.title ?? "Selamat datang di layanan kami.";

  const connectionLabel =
    connection === "sse" ? "SSE Aktif" : connection === "poll" ? "Berkala" : "-";

  return (
    <div className="h-screen w-full bg-gradient-to-br from-indigo-950 via-purple-900 to-blue-900 text-white relative overflow-hidden">
      <div className="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.18),_transparent_45%)] opacity-80" />
      <div className="relative z-10 flex h-full flex-col">
        <TopBar
          brandName={company.name}
          slogan={company.slogan}
          logoUrl={company.logo_url ?? undefined}
          showClock
        />

        <main className="mx-auto w-full max-w-[1600px] flex-1 min-h-0 px-6 py-6 lg:py-8 overflow-y-auto lg:overflow-hidden">
          <div className="grid h-full gap-6 lg:grid-cols-[0.35fr_0.65fr] lg:items-stretch">
            <QueueCallPanel
              nowServing={nowServing}
              announcement={announcementText}
              connectionLabel={connectionLabel}
              lastUpdated={lastUpdated}
              className="order-2 lg:order-1 h-full animate-fade-up"
            />

            <VideoFrame
              announcement={activeAnnouncement}
              connectionLabel={connectionLabel}
              className="order-1 lg:order-2 min-h-[320px] h-[40vh] sm:h-[48vh] lg:h-full aspect-video lg:aspect-auto animate-fade-up animate-fade-up-delay"
            />
          </div>
        </main>
      </div>
    </div>
  );
}
