import React from "react";

type ConfirmModalProps = {
  open: boolean;
  title: string;
  description?: string;
  confirmLabel?: string;
  cancelLabel?: string;
  loading?: boolean;
  onConfirm: () => void;
  onClose: () => void;
};

export default function ConfirmModal({
  open,
  title,
  description,
  confirmLabel = "Ya, Ambil",
  cancelLabel = "Batal",
  loading = false,
  onConfirm,
  onClose,
}: ConfirmModalProps) {
  if (!open) return null;

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4 animate-fade-in"
      role="dialog"
      aria-modal="true"
    >
      <div className="relative w-full max-w-lg rounded-[32px] bg-[#002b5b]/90 backdrop-blur-xl border border-pdam-cyan/30 p-8 shadow-2xl shadow-pdam-cyan/20 overflow-hidden animate-scale-up">
        {/* Background Ambient Glow */}
        <div className="absolute top-0 left-0 w-full h-32 bg-pdam-cyan/10 blur-[50px] pointer-events-none" />

        <div className="relative z-10 text-center">
          <h3 className="text-3xl font-black text-white tracking-wide uppercase drop-shadow-md mb-3">
            {title}
          </h3>
          {description ? (
            <p className="text-white/80 text-lg font-medium leading-relaxed">
              {description}
            </p>
          ) : null}
        </div>

        <div className="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
          <button
            type="button"
            className="flex-1 min-h-[60px] rounded-2xl border-2 border-white/10 bg-white/5 text-lg font-bold text-white/80 hover:bg-white/10 hover:border-white/30 hover:text-white transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed uppercase tracking-wider"
            onClick={onClose}
            disabled={loading}
          >
            {cancelLabel}
          </button>
          <button
            type="button"
            className="flex-1 min-h-[60px] rounded-2xl bg-pdam-cyan text-white text-lg font-black uppercase tracking-wider shadow-lg shadow-pdam-cyan/30 hover:bg-sky-400 hover:shadow-pdam-cyan/50 hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            onClick={onConfirm}
            disabled={loading}
          >
            {loading ? "Memproses..." : confirmLabel}
          </button>
        </div>
      </div>
    </div>
  );
}
