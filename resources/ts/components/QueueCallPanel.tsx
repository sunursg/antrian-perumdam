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
      className={`relative w-full h-full overflow-hidden rounded-[32px] glass-card flex flex-col border-2 border-glow-cyan ${nowServing ? "scale-[1.01]" : "scale-100"} ${className ?? ""}`}
    >
      {/* Branded Wave Overlay (Subtle) */}
      <div className="absolute inset-0 opacity-10 pointer-events-none bg-[radial-gradient(circle_at_50%_0%,var(--color-pdam-cyan),transparent_60%)]" />

      {/* HEADER: MEMANGGIL ANTRIAN */}
      <div className="bg-pdam-deep-blue/80 w-full py-5 flex items-center justify-center shrink-0 border-b border-white/10 backdrop-blur-md">
        <h2 className="text-white font-black tracking-[0.4em] text-2xl uppercase drop-shadow-lg font-sans">
          {nowServing ? "MEMANGGIL ANTRIAN" : "ANTRIAN SAAT INI"}
        </h2>
      </div>

      {/* BODY: Ticket Number (Golden Yellow Hero) */}
      <div className="flex-1 flex flex-col items-center justify-center relative overflow-hidden group">
        {/* Shine effect */}
        <div className="absolute inset-x-0 h-full w-40 bg-linear-to-r from-transparent via-white/10 to-transparent skew-x-[-25deg] -translate-x-full group-hover:translate-x-[250%] transition-transform duration-[1.5s] ease-in-out" />

        <div className="relative z-10 flex flex-col items-center">
          <span className="text-[14rem] sm:text-[18rem] leading-none font-black text-glow-gold tracking-tighter font-sans select-none">
            {nowServing?.ticket_no || "--"}
          </span>
        </div>
      </div>

      {/* FOOTER: Counter Name */}
      <div className="bg-pdam-deep-blue/90 w-full py-8 flex flex-col items-center justify-center shrink-0 border-t border-white/10 backdrop-blur-md">
        <span className="text-white/60 text-xs font-black tracking-[0.5em] uppercase mb-1">
          SILAKAN KE
        </span>
        <h3 className="text-5xl font-black text-white tracking-widest uppercase font-sans drop-shadow-md">
          {nowServing?.counter ?? "LOKET -"}
        </h3>
      </div>
    </section>
  );
}
