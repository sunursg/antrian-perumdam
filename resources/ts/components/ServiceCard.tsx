import React from "react";
import type { ServiceStatus } from "../lib/types";

type ServiceCardProps = {
  service: ServiceStatus;
  busy?: boolean;
  onTakeTicket: (service: ServiceStatus) => void;
};

export default function ServiceCard({
  service,
  busy = false,
  onTakeTicket,
}: ServiceCardProps) {
  return (
    <div className="group relative overflow-hidden rounded-3xl glass-card flex flex-col transition-all duration-300 hover:scale-[1.02] hover:border-pdam-cyan/60 hover:shadow-glow-cyan">

      {/* HEADER: Service Name */}
      <div className="bg-pdam-deep-blue/80 py-6 px-6 border-b border-white/10 backdrop-blur-md">
        <h3 className="text-3xl font-black text-white tracking-widest font-sans uppercase drop-shadow-md text-center">
          {service.name}
        </h3>
      </div>

      {/* BODY: Info Section */}
      <div className="flex-1 p-8 flex flex-col gap-6 relative overflow-hidden">
        {/* Shine effect */}
        <div className="absolute inset-0 bg-linear-to-tr from-transparent via-white/5 to-transparent skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-1000" />

        <div className="relative z-10 grid grid-cols-1 gap-6 text-sm font-medium">
          <div className="flex items-center justify-between p-4 rounded-2xl bg-black/20 border border-white/5 backdrop-blur-sm">
            <span className="text-white/70 font-bold uppercase tracking-wider text-xs">Antrian saat ini</span>
            <span className="text-4xl font-black text-white tracking-tighter font-mono drop-shadow-lg">
              {service.current_ticket || "-"}
            </span>
          </div>

          <div className="flex items-center justify-between px-2">
            <span className="text-white/60 font-medium">Menunggu</span>
            <span className="text-xl font-black text-pdam-gold text-glow-gold">
              {service.waiting ?? 0} <span className="text-sm font-bold text-white/50">orang</span>
            </span>
          </div>
        </div>

        <button
          type="button"
          className="relative z-10 w-full py-5 rounded-2xl bg-pdam-cyan text-white font-black text-xl uppercase tracking-[0.2em] shadow-lg hover:bg-sky-400 hover:shadow-[0_0_30px_rgba(56,189,248,0.4)] active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed group-hover:animate-pulse-slow"
          onClick={() => onTakeTicket(service)}
          disabled={busy}
        >
          {busy ? "Memproses..." : "AMBIL TIKET"}
        </button>
      </div>
    </div>
  );
}
