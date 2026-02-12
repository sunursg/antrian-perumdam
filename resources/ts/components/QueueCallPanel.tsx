import React from "react";
import type { DisplayState } from "../lib/types";

type QueueCallPanelProps = {
  nowServing?: DisplayState["now_serving"] | null;
  announcement?: string;
  connectionLabel?: string;
  lastUpdated?: Date | null;
  className?: string;
};

export default function QueueCallPanel({
  nowServing,
  announcement,
  connectionLabel,
  lastUpdated,
  className,
}: QueueCallPanelProps) {
  return (
    <section
      className={`relative w-full h-full overflow-hidden rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl flex flex-col items-center ${className ?? ""}`}
    >
      {/* --- BACKGROUND EFFECTS --- */}
      {/* 1. Base Gradient (Midnight Blue) */}
      <div className="absolute inset-0 bg-gradient-to-b from-slate-900 via-[#0B1120] to-slate-950 z-0" />

      {/* 2. Radial Glow (Ambient Light behind the number) */}
      <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[80%] h-[80%] bg-cyan-500/10 blur-[100px] rounded-full z-0" />

      {/* 3. Subtle Grid Pattern (Tech feel) */}
      <div className="absolute inset-0 opacity-[0.03] bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px] z-0" />

      {/* --- CONTENT LAYER --- */}
      <div className="relative z-10 w-full h-full flex flex-col items-center">

        {/* HEADER BANNER - FLOATING (Image 2 Style) */}
        <div className="absolute top-6 left-1/2 -translate-x-1/2 w-[90%] max-w-lg h-12 sm:h-14 rounded-2xl bg-gradient-to-r from-amber-400 to-orange-500 shadow-xl flex items-center justify-center z-20 border border-orange-300/30">
          <p className="text-slate-900 font-extrabold tracking-[0.2em] text-xs sm:text-sm uppercase drop-shadow-sm">
            MEMANGGIL ANTRIAN
          </p>
        </div>

        {/* MAIN DISPLAY AREA */}
        <div className="flex-1 flex flex-col items-center justify-center pt-24 w-full px-4">

          {/* TICKET NUMBER - The Hero Element (MOVED UP) */}
          <div className="relative group mb-2 text-center">
            {/* Main Text - No Ghost/Blur Layer */}
            <h2 className="relative text-7xl sm:text-8xl lg:text-9xl leading-none font-black text-white tracking-tighter drop-shadow-[0_0_30px_rgba(34,211,238,0.6)] font-sans">
              {nowServing?.ticket_no || "-"}
            </h2>
          </div>

          {/* LOKET NAME - Simple Text (MOVED DOWN) */}
          <div className="mb-2">
            <p className="text-2xl sm:text-3xl font-medium text-white/90 tracking-wide drop-shadow-md">
              {nowServing?.counter ?
                // Convert "LOKET 1" to "Loket 1" for softer look
                nowServing.counter.charAt(0).toUpperCase() + nowServing.counter.slice(1).toLowerCase()
                : "Loket -"}
            </p>
          </div>

          {/* SERVICE NAME - Grey Text */}
          <div className="text-center">
            <p className="text-lg sm:text-xl font-normal text-slate-400 tracking-wide max-w-[300px] mx-auto leading-relaxed">
              {nowServing?.service ?? "Menunggu antrian..."}
            </p>
          </div>

        </div>
      </div>
    </section>
  );
}
