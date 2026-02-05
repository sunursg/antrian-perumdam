import React from "react";
import TopBar from "../components/TopBar";
import ServiceCard from "../components/ServiceCard";
import ConfirmModal from "../components/ConfirmModal";
import TicketSuccessModal from "../components/TicketSuccessModal";
import { createTicket, getPublicStatus } from "../lib/api";
import type { PublicStatus, ServiceStatus, TicketResponse } from "../lib/types";

const fallbackCompany = {
  name: "PERUMDAM TIRTA PERWIRA",
  slogan: "Melayani dengan Sepenuh Hati",
  logo_url: "/logo.png",
};

export default function Kiosk() {
  const [status, setStatus] = React.useState<PublicStatus | null>(null);
  const [loading, setLoading] = React.useState(true);
  const [error, setError] = React.useState<string | null>(null);
  const [selected, setSelected] = React.useState<ServiceStatus | null>(null);
  const [confirmOpen, setConfirmOpen] = React.useState(false);
  const [submitting, setSubmitting] = React.useState(false);
  const [successTicket, setSuccessTicket] = React.useState<TicketResponse | null>(
    null
  );

  const loadStatus = React.useCallback(async () => {
    setLoading(true);
    const data = await getPublicStatus();
    if (data) {
      setStatus(data);
      setError(null);
    } else {
      setError("Gagal memuat data antrian. Silakan coba lagi.");
    }
    setLoading(false);
  }, []);

  React.useEffect(() => {
    loadStatus().catch(() => undefined);
  }, [loadStatus]);

  const services = (status?.services ?? []).map((service, index) => ({
    ...service,
    theme: service.theme ?? (index % 2 === 0 ? "blue" : "green"),
  }));

  const handleTakeTicket = (service: ServiceStatus) => {
    setSelected(service);
    setConfirmOpen(true);
  };

  const handleConfirm = async () => {
    if (!selected) return;
    setSubmitting(true);
    try {
      const ticket = await createTicket(selected.code, selected.name);
      setSuccessTicket(ticket);
      setConfirmOpen(false);
      setSelected(null);
      await loadStatus();
    } catch (err) {
      setError(
        err instanceof Error ? err.message : "Terjadi kesalahan saat mengambil tiket."
      );
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="min-h-screen w-full bg-gradient-to-br from-indigo-950 via-purple-900 to-blue-900 text-white relative overflow-hidden">
      <div className="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.16),_transparent_45%)] opacity-80" />
      <div className="relative z-10 flex min-h-screen flex-col">
        <TopBar
          brandName={fallbackCompany.name}
          slogan={fallbackCompany.slogan}
          logoUrl={fallbackCompany.logo_url}
          showClock
        />

        <main className="mx-auto w-full max-w-[1400px] flex-1 px-6 py-8 lg:py-12">
          <div className="text-center animate-fade-up">
            <h1 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold">
              Pilih Jenis Layanan
            </h1>
            <p className="text-base sm:text-lg text-white/80 mt-3">
              Sentuh salah satu tombol di bawah untuk mengambil nomor antrian.
            </p>
          </div>

          {error ? (
            <div className="mt-6 rounded-2xl bg-rose-500/20 border border-rose-400/40 px-5 py-4 text-rose-100 text-sm text-center">
              {error}
            </div>
          ) : null}

          <div className="mt-8 grid gap-6 lg:grid-cols-2 animate-fade-up animate-fade-up-delay">
            {loading && services.length === 0 ? (
              <div className="col-span-full rounded-3xl bg-white/10 border border-white/15 p-8 text-center text-white/70">
                Memuat data layanan...
              </div>
            ) : null}

            {services.map((service) => (
              <ServiceCard
                key={service.id}
                service={service}
                busy={submitting}
                onTakeTicket={handleTakeTicket}
              />
            ))}
          </div>
        </main>
      </div>

      {/* Modal konfirmasi untuk mencegah klik iseng di kiosk */}
      <ConfirmModal
        open={confirmOpen && !!selected}
        title={`Yakin ambil tiket untuk layanan "${selected?.name}"?`}
        description="Sentuh Ya untuk mengambil tiket dan menunggu panggilan."
        onClose={() => {
          if (!submitting) {
            setConfirmOpen(false);
            setSelected(null);
          }
        }}
        onConfirm={handleConfirm}
        loading={submitting}
      />

      {/* Modal sukses setelah tiket berhasil dibuat */}
      <TicketSuccessModal
        open={!!successTicket}
        ticket={successTicket}
        onClose={() => setSuccessTicket(null)}
      />
    </div>
  );
}
