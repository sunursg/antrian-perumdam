import { apiFetch } from "../ui/http";
import { toast } from "../ui/toast";
import { confirmModal } from "../ui/modal";
import { skeletonBlock } from "../ui/skeleton";

const buttonsHost = document.getElementById("serviceButtons");
const resultHost = document.getElementById("ticketResult");

document.querySelectorAll("[data-svc]").forEach((btn) => {
  btn.addEventListener("click", async () => {
    const code = btn.getAttribute("data-svc");
    const ok = await confirmModal({
      title: "Ambil tiket?",
      message: `Anda akan mengambil tiket untuk layanan: ${code}.`,
      confirmText: "Ambil Tiket",
      cancelText: "Batal",
    });
    if (!ok) return;
    await takeTicket(code);
  });
});

const services = [
  { code: "CS", label: "Layanan Pelanggan", desc: "Urusan pelanggan & administrasi" },
  { code: "BAY", label: "Pembayaran", desc: "Tagihan & transaksi" },
  { code: "PENG", label: "Pengaduan", desc: "Keluhan & laporan gangguan" },
  { code: "INF", label: "Informasi", desc: "Tanya layanan & prosedur" },
];

function serviceBtn(s) {
  const el = document.createElement("button");
  el.className = `
    rounded-2xl border border-slate-200 bg-white hover:bg-slate-50
    p-5 text-left shadow-sm active:scale-[0.99] transition
  `.replace(/\s+/g, " ");
  el.innerHTML = `
    <div class="flex items-center justify-between">
      <p class="text-base font-semibold">${s.label}</p>
      <span class="text-xs px-2 py-1 rounded-full border border-slate-200 text-slate-700">${s.code}</span>
    </div>
    <p class="text-sm text-slate-600 mt-2">${s.desc}</p>
  `;
  el.addEventListener("click", async () => {
    const ok = await confirmModal({
      title: "Ambil tiket?",
      message: `Anda akan mengambil tiket untuk layanan: ${s.label}.`,
      confirmText: "Ambil Tiket",
      cancelText: "Batal",
    });
    if (!ok) return;
    await takeTicket(s.code);
  });
  return el;
}

buttonsHost.innerHTML = "";
services.forEach((s) => buttonsHost.appendChild(serviceBtn(s)));

function ticketCard(data) {
  const el = document.createElement("div");
  el.className = "rounded-2xl border border-slate-200 bg-slate-50 p-6";
  const taken = new Date(data.taken_at);
  el.innerHTML = `
    <div class="flex items-start justify-between gap-4">
      <div>
        <p class="text-xs text-slate-500">Nomor Antrian</p>
        <p class="text-4xl md:text-5xl font-semibold tracking-tight mt-1">${data.ticket_no}</p>
        <p class="text-sm text-slate-600 mt-2">${data.service.name}</p>
      </div>
      <div class="text-right">
        <p class="text-xs text-slate-500">Waktu Ambil</p>
        <p class="text-sm font-medium mt-1">${taken.toLocaleString("id-ID")}</p>
        <p class="text-xs text-slate-500 mt-3">Estimasi</p>
        <p class="text-sm font-semibold">${data.estimate_minutes} menit</p>
      </div>
    </div>

    <div class="mt-5 grid gap-3">
      <div class="rounded-xl bg-white border border-slate-200 p-4">
        <p class="text-xs text-slate-500">QR/ID</p>
        <p class="text-sm font-mono mt-1">${data.qr_value}</p>
      </div>

      <div class="flex flex-wrap gap-2">
        <button data-copy class="px-4 py-2 rounded-xl bg-sky-600 text-white hover:bg-sky-700 shadow-sm">Salin Kode</button>
        <button data-print class="px-4 py-2 rounded-xl border border-slate-200 hover:bg-white">Cetak</button>
      </div>

      <div class="text-sm text-slate-600">
        <p>Harap hadir <span class="font-medium">10 menit</span> sebelum perkiraan dipanggil.</p>
        <p>Simpan kode tiket untuk melihat status.</p>
      </div>
    </div>
  `;

  el.querySelector("[data-copy]").addEventListener("click", async () => {
    await navigator.clipboard.writeText(data.ticket_no);
    toast({ title: "Tersalin", message: `Kode ${data.ticket_no} tersalin ke clipboard.`, variant: "success" });
  });

  el.querySelector("[data-print]").addEventListener("click", () => {
    const w = window.open("", "_blank");
    w.document.write(`
      <html><head><title>Cetak Tiket</title></head>
      <body style="font-family: Arial; padding: 20px;">
        <h2>Perumdam Tirta Perwira Kabupaten Purbalingga</h2>
        <p><b>Nomor:</b> ${data.ticket_no}</p>
        <p><b>Layanan:</b> ${data.service.name}</p>
        <p><b>Waktu:</b> ${new Date(data.taken_at).toLocaleString("id-ID")}</p>
        <p><b>Estimasi:</b> ${data.estimate_minutes} menit</p>
        <hr />
        <p>Harap hadir 10 menit sebelum perkiraan dipanggil.</p>
      </body></html>
    `);
    w.print();
  });

  return el;
}

async function takeTicket(serviceCode) {
  resultHost.innerHTML = "";
  resultHost.appendChild(skeletonBlock(8));

  const r = await apiFetch("/api/public/tickets", {
    method: "POST",
    body: JSON.stringify({ service_code: serviceCode }),
  });

  resultHost.innerHTML = "";

  if (!r.ok || !r.body) {
    toast({ title: "Gagal", message: "Server tidak merespons dengan baik.", variant: "danger" });
    resultHost.innerHTML = `<div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
      Gagal mengambil tiket. Silakan coba lagi.
    </div>`;
    return;
  }

  if (!r.body.success) {
    const msg = r.body.message || "Tidak dapat mengambil tiket.";
    toast({ title: "Ditolak", message: msg, variant: "warning" });

    const errText = JSON.stringify(r.body.errors || {}, null, 2);
    resultHost.innerHTML = `<div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
      <p class="font-medium">${msg}</p>
      <pre class="mt-2 text-xs whitespace-pre-wrap">${errText}</pre>
    </div>`;
    return;
  }

  toast({ title: "Berhasil", message: "Tiket berhasil dibuat.", variant: "success" });
  resultHost.appendChild(ticketCard(r.body.data));
}
