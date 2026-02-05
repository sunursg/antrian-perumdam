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
      className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 px-4"
      role="dialog"
      aria-modal="true"
    >
      <div className="panel-light w-full max-w-lg p-6 sm:p-8">
        <h3 className="text-2xl font-extrabold text-slate-900">{title}</h3>
        {description ? (
          <p className="text-sm text-slate-600 mt-2">{description}</p>
        ) : null}

        <div className="mt-8 flex flex-col sm:flex-row gap-3">
          <button
            type="button"
            className="min-h-[50px] rounded-2xl border border-slate-200 px-6 text-base font-semibold text-slate-700 hover:bg-slate-50"
            onClick={onClose}
            disabled={loading}
          >
            {cancelLabel}
          </button>
          <button
            type="button"
            className="min-h-[50px] rounded-2xl bg-blue-600 px-6 text-base font-semibold text-white shadow-lg shadow-blue-900/30 hover:bg-blue-700"
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
