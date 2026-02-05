import React from "react";
import type { DisplayState } from "../lib/types";

type QueueCallPanelProps = {
  nowServing?: DisplayState["now_serving"] | null;
  announcement?: string;
  connectionLabel?: string;
  lastUpdated?: Date | null;
  className?: string;
};

const slotChars = (ticket?: string | null) => {
  const raw = ticket && ticket.trim() ? ticket.trim() : "----";
  const padded = raw.length >= 4 ? raw : raw.padStart(4, "-");
  return padded.slice(-4).split("");
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
      className={`glass-panel p-6 lg:p-8 flex flex-col gap-6 ${className ?? ""}`}
    >
      <div className="rounded-2xl bg-gradient-to-r from-amber-300 via-amber-400 to-orange-400 text-slate-900 px-4 py-3 text-center text-base font-extrabold tracking-[0.2em]">
        MEMANGGIL ANTRIAN
      </div>

      <div className="text-center space-y-3">
        <p className="text-xs uppercase tracking-[0.4em] text-white/70">
          NOMOR ANTRIAN
        </p>
        <div className="flex justify-center gap-2">
          {slotChars(nowServing?.ticket_no).map((char, index) => (
            <div key={`${char}-${index}`} className="slot-box">
              {char}
            </div>
          ))}
        </div>
        <p className="text-base sm:text-lg font-semibold text-white/90">
          {nowServing?.counter ?? "Loket -"}
        </p>
        <p className="text-sm text-white/70">
          {nowServing?.service ?? "Menunggu panggilan berikutnya"}
        </p>
      </div>

      <button type="button" className="btn-muted" disabled>
        MENUNGGU PANGGILAN
      </button>

      <div className="rounded-2xl bg-white/10 border border-white/15 p-4">
        <p className="text-xs uppercase tracking-[0.28em] text-white/70">
          Pengumuman
        </p>
        <p className="text-base font-semibold mt-2">
          {announcement || "Tidak ada pengumuman saat ini."}
        </p>
      </div>

      <div className="flex items-center justify-between text-sm text-white/70">
        <span>Status koneksi</span>
        <span className="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white">
          {connectionLabel || "-"}
        </span>
      </div>

      {lastUpdated ? (
        <p className="text-xs text-white/60">
          Update terakhir:{" "}
          {new Intl.DateTimeFormat("id-ID", {
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
          }).format(lastUpdated)}
        </p>
      ) : null}
    </section>
  );
}
