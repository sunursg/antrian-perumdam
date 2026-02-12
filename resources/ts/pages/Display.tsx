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

// DUMMY DATA FOR SIMULATION
const DUMMY_COUNTERS = [
  { name: "LOKET 1", active: true, current_ticket: "A-005" },
  { name: "LOKET 2", active: true, current_ticket: "B-002" },
  { name: "LOKET 3", active: true, current_ticket: "C-029" },
  { name: "LOKET 4", active: true, current_ticket: "D-132" },

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
    <div className="h-screen w-full bg-slate-900 text-white relative flex flex-col font-sans overflow-hidden selection:bg-cyan-500/30">
      {/* Background with Glass/Water Theme */}
      <div className="absolute inset-0 bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 z-0" />
      <div className="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(6,182,212,0.15),_transparent_50%)] z-0" />

      {/* Header */}
      <TopBar
        brandName={company.name}
        // slogan={company.slogan} // Address replaces slogan in this design
        address={COMPANY_ADDRESS}
        logoUrl={company.logo_url || "/logo.png"}
        showClock
      />

      {/* Main Content Area */}
      <main className="flex-1 relative z-10 flex flex-row p-6 gap-6 min-h-0 overflow-hidden">

        {/* LEFT COLUMN (30%) - Split & Stack */}
        <div className="w-[30%] flex flex-col gap-6 h-full min-w-0">

          {/* TOP: Call Panel */}
          <div className="h-auto shrink-0">
            <QueueCallPanel
              nowServing={nowServing}
              announcement={announcementText}
              connectionLabel={connectionLabel}
              lastUpdated={lastUpdated}
              className="w-full border border-cyan-500/30 bg-slate-900/40 backdrop-blur-xl rounded-[2rem] shadow-2xl animate-fade-up"
            />
          </div>

          {/* BOTTOM: Dynamic Counter List */}
          <div className="flex-1 relative min-h-0 rounded-[2rem] border border-white/10 bg-slate-900/40 backdrop-blur-md overflow-hidden flex flex-col">
            {/* Header for Counter List */}
            <div className="p-4 border-b border-white/5 bg-white/5 flex items-center justify-between shrink-0">
              <h3 className="font-bold text-cyan-400 tracking-wider uppercase text-sm">Status Loket</h3>
              <div className="flex gap-1">
                <div className="w-2 h-2 rounded-full bg-red-500" />
                <div className="w-2 h-2 rounded-full bg-yellow-500" />
                <div className="w-2 h-2 rounded-full bg-green-500" />
              </div>
            </div>

            {/* Content Container */}
            <div className="flex-1 relative overflow-hidden p-4">
              {/* 
                    LOGIC:
                    - If <= 2 counters: Grid Layout
                    - If > 2 counters: Marquee / Scroll Layout 
                    For simulation, we use DUMMY_COUNTERS which has 4 items.
                    Actual implementation would use `counters` from state.
                  */}
              {/* SIMULATION MODE: Using DUMMY_COUNTERS instead of `counters` */}
              {DUMMY_COUNTERS.length <= 4 ? (
                <div className="grid grid-cols-1 gap-4 h-full">
                  {DUMMY_COUNTERS.map((counter, idx) => (
                    <div key={idx} className="relative group overflow-hidden rounded-xl border border-white/10 bg-white/5 p-4 flex flex-col items-center justify-center text-center shadow-lg">
                      <h3 className="text-sm font-bold tracking-[0.2em] text-cyan-200 uppercase mb-1 opacity-80">{counter.name}</h3>
                      <p className="text-4xl font-black text-white tracking-widest drop-shadow-lg">{counter.current_ticket}</p>
                    </div>
                  ))}
                </div>
              ) : (
                // Marquee / Scroll Layout for > 2 items
                <div className="absolute inset-0 overflow-hidden pointer-events-none">
                  <div className="animate-marquee-vertical flex flex-col gap-4 py-2 w-full">
                    {[...DUMMY_COUNTERS, ...DUMMY_COUNTERS].map((counter, idx) => (
                      <div key={`${counter.name}-${idx}`} className="shrink-0 relative overflow-hidden rounded-xl border border-white/10 bg-white/5 p-4 flex items-center justify-between shadow-lg mx-2">
                        <div className="flex items-center gap-3">
                          <div className={`w-2 h-12 rounded-full ${counter.active ? 'bg-cyan-400 shadow-[0_0_10px_rgba(34,211,238,0.5)]' : 'bg-slate-600'}`}></div>
                          <h3 className="text-lg font-bold tracking-wider text-cyan-200 uppercase">{counter.name}</h3>
                        </div>
                        <p className="text-4xl font-black text-white tracking-widest drop-shadow-lg">{counter.current_ticket}</p>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* RIGHT COLUMN (70%) - Video/Multimedia */}
        <div className="w-[70%] h-full min-w-0">
          <VideoFrame
            announcement={activeAnnouncement}
            connectionLabel={connectionLabel}
            className="h-full w-full rounded-[2rem] overflow-hidden shadow-2xl border border-white/10 bg-slate-900/40"
          />
        </div>

      </main>

      {/* Footer Marquee */}
      <RunningText text={TICKER_TEXT} speed={25} />
    </div>
  );
}
