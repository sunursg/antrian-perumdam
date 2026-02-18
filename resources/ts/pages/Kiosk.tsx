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

const COMPANY_ADDRESS =
  "Jl. Letnan Jenderal S Parman No.62, Kedung Menjangan, Bancar, Kec. Purbalingga, Kabupaten Purbalingga, Jawa Tengah 53316";

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
    <div className="min-h-screen w-full bg-ocean text-white relative overflow-hidden font-sans selection:bg-pdam-cyan/30">
      {/* Background Pro-level Gradients */}
      <div className="absolute inset-x-0 top-0 h-96 bg-linear-to-b from-pdam-cyan/10 to-transparent z-0" />
      <div className="absolute -top-24 -left-24 w-96 h-96 bg-pdam-cyan/20 blur-[150px] rounded-full pointer-events-none opacity-50" />
      <div className="absolute -bottom-24 -right-24 w-96 h-96 bg-pdam-emerald/10 blur-[150px] rounded-full pointer-events-none opacity-30" />

      <div className="relative z-10 flex min-h-screen flex-col">
        <TopBar
          brandName={fallbackCompany.name}
          address={COMPANY_ADDRESS}
          logoUrl={fallbackCompany.logo_url}
          showClock
        />

        <main className="mx-auto w-full max-w-[1400px] flex-1 px-6 py-8 lg:py-12 flex flex-col justify-center">
          <div className="text-center animate-fade-up px-4 mb-12">
            <h1 className="text-4xl sm:text-5xl lg:text-7xl font-black text-white tracking-widest drop-shadow-2xl font-sans uppercase mb-4">
              SELAMAT <span className="text-pdam-cyan text-glow-cyan">DATANG</span>
            </h1>
            <div className="h-1 w-32 bg-pdam-gold mx-auto rounded-full mb-6 shadow-[0_0_20px_rgba(253,184,19,0.5)]" />
            <p className="text-lg sm:text-2xl text-white/80 font-bold tracking-[0.2em] max-w-3xl mx-auto uppercase drop-shadow-md">
              SILAKAN AMBIL NOMOR ANTRIAN ANDA
            </p>
          </div>

          {error ? (
            <div className="mt-6 rounded-2xl bg-rose-500/20 border border-rose-400/40 px-5 py-4 text-rose-100 text-sm text-center">
              {error}
            </div>
          ) : null}

          <div className="grid gap-8 lg:grid-cols-2 animate-fade-up animate-fade-up-delay max-w-6xl mx-auto w-full">
            {loading && services.length === 0 ? (
              <div className="col-span-full rounded-3xl bg-white/5 border border-white/10 p-12 text-center text-white/60 animate-pulse">
                <span className="inline-block px-6 py-2 rounded-full bg-white/10 text-sm font-bold tracking-widest uppercase">
                  Memuat Layanan...
                </span>
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
      <div className="relative z-50">
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
        <div className="relative z-50">
          <TicketSuccessModal
            open={!!successTicket}
            ticket={successTicket}
            onClose={() => setSuccessTicket(null)}
          />
        </div>
      </div>
    </div>
  );
}
