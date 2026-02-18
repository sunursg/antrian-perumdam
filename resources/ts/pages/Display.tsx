import React, { useEffect, useState, useCallback } from "react";
import TopBar from "../components/TopBar";
import QueueCallPanel from "../components/QueueCallPanel";
import VideoFrame from "../components/VideoFrame";
import RunningText from "../components/RunningText";
import { connectDisplaySse } from "../lib/sse";
import { getDisplayState } from "../lib/api";
import { announce } from "../lib/audio";
import type { DisplayState } from "../lib/types";

const fallbackCompany = {
  name: "PERUMDAM TIRTA PERWIRA",
  slogan: "Melayani dengan Sepenuh Hati",
  logo_url: "/logo.png",
};

// FALLBACK DATA FOR INITIAL LOAD
const DEFAULT_COUNTERS = [
  { name: "LOKET 1", active: true, current_ticket: "-" },
  { name: "LOKET 2", active: true, current_ticket: "-" },
];

const COMPANY_ADDRESS =
  "Jl. Letnan Jenderal S Parman No.62, Kedung Menjangan, Bancar, Kec. Purbalingga, Kabupaten Purbalingga, Jawa Tengah 53316";

const TICKER_TEXT =
  "Selamat Datang di Perumdam Tirta Perwira. Budayakan antri untuk kenyamanan bersama. Loket buka jam 08:00 - 15:00 WIB.";

export default function Display() {
  const [state, setState] = useState<DisplayState | null>(null);
  const [connection, setConnection] = useState<"sse" | "poll" | "idle">(
    "idle"
  );
  const [lastUpdated, setLastUpdated] = useState<Date | null>(null);
  const lastTicketRef = React.useRef<string | null>(null);

  const loadState = useCallback(async () => {
    const data = await getDisplayState();
    if (data) {
      setState(data);
      setLastUpdated(new Date());
    }
  }, []);

  useEffect(() => {
    let pollTimer: number | null = null;
    let source: EventSource | null = null;

    const startPolling = () => {
      if (pollTimer) return;
      setConnection("poll");
      pollTimer = window.setInterval(() => {
        loadState().catch(() => undefined);
      }, 3000);
    };

    loadState().catch(() => undefined);

    // Set to true to enable SSE if supported by server config
    const enableSSE = false;

    if (enableSSE && "EventSource" in window) {
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

  // Handle Automatic Audio Announcement
  useEffect(() => {
    const currentTicket = state?.now_serving?.ticket_no;
    const currentCounter = state?.now_serving?.counter;

    if (currentTicket && currentTicket !== lastTicketRef.current) {
      lastTicketRef.current = currentTicket;
      announce(currentTicket, currentCounter || "Loket");
    }
  }, [state?.now_serving]);

  const company = state?.company ?? fallbackCompany;
  const nowServing = state?.now_serving;
  const counters = state?.counters ?? [];
  const activeAnnouncement =
    state?.announcements?.find((item) => item.active) ??
    state?.announcements?.[0] ??
    null;

  const announcementText =
    activeAnnouncement?.title ?? "Selamat datang di layanan kami.";

  const connectionLabel =
    connection === "sse" ? "SSE Aktif" : connection === "poll" ? "Berkala" : "-";

  // Dynamic Grid Logic
  const gridClass =
    counters.length <= 2
      ? "grid-cols-2"
      : counters.length === 3
        ? "grid-cols-3"
        : "grid-cols-4";

  return (
    <div className="h-screen w-full bg-ocean text-white relative flex flex-col font-sans overflow-hidden selection:bg-pdam-cyan/30">
      {/* Background Ambience - Deep Sea / Corporate Vibe */}
      <div className="absolute inset-x-0 top-0 h-96 bg-linear-to-b from-pdam-cyan/5 to-transparent z-0" />
      <div className="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] rounded-full bg-pdam-cyan/5 blur-[150px] opacity-40" />
      <div className="absolute bottom-[-10%] right-[-5%] w-[40%] h-[40%] rounded-full bg-pdam-emerald/5 blur-[120px] opacity-20" />

      {/* Main Content Area - Maximized */}
      <main className="flex-1 relative z-10 flex flex-col overflow-hidden min-h-0">

        {/* UPPER SECTION: Split View (Expanded Height) */}
        <div className="flex-[1.5] flex flex-col lg:flex-row min-h-0">

          {/* LEFT COLUMN: Header + Call Panel (40%) */}
          <div className="w-full lg:w-[40%] flex flex-col p-4 sm:p-6 pb-0 gap-6">
            {/* Header - Moved Inside Left Column */}
            <div className="shrink-0 relative z-20">
              <TopBar
                brandName={company.name}
                address={COMPANY_ADDRESS}
                logoUrl="/logo.png"
                showClock={false}
              />
            </div>

            {/* Hero Call Panel */}
            <div className="flex-1 min-h-0">
              <QueueCallPanel
                nowServing={nowServing}
                announcement={announcementText}
                connectionLabel={connectionLabel}
                lastUpdated={lastUpdated}
                className="w-full h-full animate-fade-up"
              />
            </div>
          </div>

          {/* RIGHT COLUMN: Video Player (60%) - Full Height "Nembus Topbar" */}
          <div className="w-full lg:w-[60%] h-full p-4 sm:p-6 pl-0">
            <div className="w-full h-full rounded-[32px] overflow-hidden shadow-2xl bg-black border-2 border-glow-cyan ring-4 ring-pdam-cyan/5 flex flex-col">
              <VideoFrame
                announcement={activeAnnouncement}
                connectionLabel={connectionLabel}
                className="h-full w-full object-cover flex-1"
              />
            </div>
          </div>

        </div>

        {/* LOWER SECTION: Horizontal Counter List (Compact) */}
        <div className="h-[22%] shrink-0 w-full overflow-hidden px-4 sm:px-6 pb-6">
          {(() => {
            const counters = state?.counters && state.counters.length > 0 ? state.counters : DEFAULT_COUNTERS;
            const gridCols = counters.length <= 4 ? "grid-cols-4" : "grid-cols-5";

            return (
              <div className={`grid ${gridCols} gap-6 h-full p-1`}>
                {counters.slice(0, 5).map((counter, idx) => (
                  <div
                    key={idx}
                    className="relative w-full h-full rounded-[24px] glass-card flex flex-col overflow-hidden group transition-all duration-500 hover:border-pdam-cyan/30 hover:bg-pdam-deep-blue/80 shadow-xl"
                  >
                    {/* Status Indicator */}
                    <div className="absolute top-4 left-4 flex items-center gap-2 z-20">
                      <div className={`w-2.5 h-2.5 rounded-full ${counter.active ? "bg-pdam-emerald shadow-[0_0_10px_#00a651]" : "bg-white/20"} ${counter.active ? "animate-pulse" : ""}`} />
                      <span className="text-[9px] font-black tracking-[0.2em] text-white/40 uppercase">
                        {counter.active ? "Online" : "Offline"}
                      </span>
                    </div>

                    {/* Body: Ticket Number */}
                    <div className="flex-1 flex flex-col items-center justify-center pt-6">
                      <span className={`text-6xl xl:text-7xl font-black ${counter.active ? "text-white" : "text-white/10"} font-sans tracking-tight drop-shadow-xl`}>
                        {counter.current_ticket || '-'}
                      </span>
                    </div>

                    {/* Footer: Counter Name */}
                    <div className="bg-pdam-deep-blue/90 py-3 text-center border-t border-white/5 shrink-0">
                      <span className="text-pdam-cyan font-black text-lg xl:text-xl tracking-widest uppercase">
                        {counter.name}
                      </span>
                    </div>

                    {/* Shine Effect */}
                    <div className="absolute inset-0 bg-linear-to-tr from-transparent via-white/5 to-transparent skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-1000" />
                  </div>
                ))}
              </div>
            );
          })()}
        </div>

      </main>

      {/* Footer Marquee & Clock (Merged) */}
      <div className="bg-pdam-deep-blue border-t border-white/10 relative z-30 flex h-16 shrink-0">
        {/* Clock Section (Bottom Left) */}
        <div className="w-auto shrink-0 bg-pdam-deep-blue border-r border-white/10 flex items-center justify-center px-8 relative overflow-hidden">
          <div className="absolute inset-0 bg-pdam-cyan/5" />
          <div className="relative z-10 w-full">
            <ClockDateWidget />
          </div>
        </div>

        {/* Ticker Section */}
        <div className="flex-1 relative overflow-hidden flex items-center bg-pdam-deep-blue/50">
          <RunningText text={TICKER_TEXT} speed={40} className="text-white font-bold tracking-widest text-sm w-full h-full flex items-center" />
        </div>
      </div>
    </div>
  );
}

// Inline Clock Widget for Footer
function ClockDateWidget() {
  const [now, setNow] = useState(() => new Date());

  useEffect(() => {
    const timer = window.setInterval(() => setNow(new Date()), 1000);
    return () => window.clearInterval(timer);
  }, []);

  const timeFormatter = new Intl.DateTimeFormat("id-ID", {
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
  });

  const dateFormatter = new Intl.DateTimeFormat("id-ID", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  });

  return (
    <div className="flex flex-col items-start justify-center">
      <div className="text-3xl font-black tracking-tighter text-white drop-shadow-lg leading-none font-sans flex items-baseline gap-1.5 tabular-nums">
        {timeFormatter.format(now).split('.').map((part, i, arr) => (
          <React.Fragment key={i}>
            <span>{part}</span>
            {i < arr.length - 1 && <span className="animate-pulse text-pdam-cyan">:</span>}
          </React.Fragment>
        ))}
      </div>
      <div className="text-[10px] font-bold text-pdam-gold uppercase tracking-widest mt-0.5">
        {dateFormatter.format(now)}
      </div>
    </div>
  );
}
