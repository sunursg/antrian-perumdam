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
      className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 px-4"
      role="dialog"
      aria-modal="true"
    >
      <div className="panel-light w-full max-w-lg p-6 sm:p-8 text-center">
        <p className="text-sm uppercase tracking-[0.2em] text-slate-500">
          Nomor Antrian Anda
        </p>
        <div className="mt-3 text-5xl sm:text-6xl font-black text-slate-900">
          {ticket.ticket_no}
        </div>
        <p className="text-sm text-slate-600 mt-2">{ticket.service}</p>

        {ticket.counter_hint ? (
          <div className="mt-4 rounded-2xl bg-slate-100 px-4 py-3 text-sm text-slate-700">
            Loket tujuan: <span className="font-semibold">{ticket.counter_hint}</span>
          </div>
        ) : null}

        <p className="text-base text-slate-700 mt-5">
          Silakan menunggu panggilan di area tunggu.
        </p>

        <button
          type="button"
          className="mt-8 w-full min-h-[54px] rounded-2xl bg-emerald-600 text-white text-lg font-semibold shadow-lg shadow-emerald-900/30 hover:bg-emerald-700"
          onClick={onClose}
        >
          Selesai
        </button>
      </div>
    </div>
  );
}
