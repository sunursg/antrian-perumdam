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
  const isGreen = service.theme === "green";
  const accent = isGreen
    ? "from-emerald-500 to-emerald-700"
    : "from-blue-500 to-blue-700";
  const buttonClass = isGreen ? "btn-cta-green" : "btn-cta";

  return (
    <div className="panel-light p-8 flex flex-col gap-6">
      <div className="flex items-start gap-4">
        <div
          className={`h-14 w-14 rounded-full bg-gradient-to-br ${accent} flex items-center justify-center`}
        >
          <span className="h-3 w-3 rounded-full bg-white/90" />
        </div>
        <div>
          <h3 className="text-2xl font-extrabold">{service.name}</h3>
          <p className="text-sm text-slate-600 mt-1">{service.description}</p>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-3 text-sm text-slate-600">
        <div className="flex items-center justify-between">
          <span>Antrian saat ini</span>
          <span className="text-lg font-semibold text-slate-900">
            {service.current_ticket || "-"}
          </span>
        </div>
        <div className="flex items-center justify-between">
          <span>Menunggu</span>
          <span className="text-lg font-semibold text-slate-900">
            {service.waiting ?? 0} orang
          </span>
        </div>
        <div className="flex items-center justify-between">
          <span>Perkiraan waktu tunggu</span>
          <span className="text-lg font-semibold text-slate-900">
            {service.estimated_minutes
              ? `~${service.estimated_minutes} menit`
              : "-"}
          </span>
        </div>
      </div>

      <button
        type="button"
        className={buttonClass}
        onClick={() => onTakeTicket(service)}
        disabled={busy}
      >
        {busy ? "Memproses..." : "Ambil Tiket"}
      </button>
    </div>
  );
}
