import React from "react";
import type { TicketResponse } from "../lib/types";

type TicketSuccessModalProps = {
  open: boolean;
  ticket: TicketResponse | null;
  onClose: () => void;
};

export default function TicketSuccessModal({
  open,
  ticket,
  onClose,
}: TicketSuccessModalProps) {
  if (!open || !ticket) return null;

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md px-4 animate-fade-in"
      role="dialog"
      aria-modal="true"
    >
      <div className="relative w-full max-w-lg rounded-[40px] bg-[#002b5b]/90 backdrop-blur-xl border-2 border-pdam-emerald/40 p-10 shadow-[0_0_50px_rgba(0,166,81,0.2)] text-center overflow-hidden animate-scale-up">
        {/* Background Effects */}
        <div className="absolute top-[-50%] left-[-50%] w-[200%] h-[200%] bg-[radial-gradient(circle_at_center,rgba(0,166,81,0.15),transparent_70%)] pointer-events-none" />

        <div className="relative z-10 flex flex-col items-center">
          <div className="mb-4 inline-flex items-center justify-center w-16 h-16 rounded-full bg-pdam-emerald/20 border border-pdam-emerald/50 shadow-glow-emerald">
            <svg xmlns="http://www.w3.org/2000/svg" className="w-8 h-8 text-pdam-emerald" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={3}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
            </svg>
          </div>

          <p className="text-sm font-bold uppercase tracking-[0.3em] text-white/60 mb-2">
            Nomor Antrian Anda
          </p>

          <div className="relative py-4">
            <div className="text-7xl sm:text-8xl font-black text-white tracking-tighter drop-shadow-xl font-sans">
              {ticket.ticket_no}
            </div>
          </div>

          <p className="text-xl font-bold text-pdam-cyan tracking-wide mb-6">
            {ticket.service}
          </p>

          {ticket.counter_hint ? (
            <div className="w-full rounded-2xl bg-white/5 border border-white/10 px-4 py-3 text-base text-white/80 mb-6">
              Loket tujuan: <span className="font-bold text-white ml-2">{ticket.counter_hint}</span>
            </div>
          ) : null}

          <p className="text-base text-white/60 mb-8 max-w-xs mx-auto leading-relaxed">
            Silakan menunggu panggilan di area tunggu. Terima kasih.
          </p>

          <button
            type="button"
            className="w-full min-h-[64px] rounded-2xl bg-pdam-emerald text-white text-xl font-black uppercase tracking-widest shadow-lg shadow-pdam-emerald/30 hover:bg-[#00c868] hover:shadow-pdam-emerald/50 hover:scale-[1.02] active:scale-95 transition-all"
            onClick={onClose}
          >
            Selesai
          </button>
        </div>
      </div>

      {/* Confetti or simple particle effect could go here if using a library */}
    </div>
  );
}
