import { apiFetch } from "../ui/http";
import { toast } from "../ui/toast";
import { confirmModal } from "../ui/modal";
import { skeletonBlock } from "../ui/skeleton";

const dataEl = document.getElementById("app-data");
const buttonsHost = document.getElementById("serviceButtons");
const resultHost = document.getElementById("ticketResult");
const palette = [
  { bg: "bg-blue-50", icon: "bg-blue-600", text: "text-blue-900" },
  { bg: "bg-emerald-50", icon: "bg-emerald-600", text: "text-emerald-900" },
  { bg: "bg-amber-50", icon: "bg-amber-500", text: "text-amber-900" },
  { bg: "bg-violet-50", icon: "bg-violet-600", text: "text-violet-900" },
];
let services = [];
let organization = {};
let announcements = [];

if (dataEl?.textContent) {
  try {
    const parsed = JSON.parse(dataEl.textContent);
    services = parsed.services || [];
    organization = parsed.organization || {};
    announcements = parsed.announcements || [];
  } catch (e) {
    console.error("Failed to parse app data JSON", e);
  }
}

async function confirmForService(service) {
  if (service.requires_confirmation) {
    return new Promise((resolve) => {
      const overlay = document.createElement("div");
      overlay.className = "fixed inset-0 z-[9998] bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4";

      overlay.innerHTML = `
        <div class="w-[520px] max-w-[96vw] rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden">
          <div class="p-6 space-y-3">
            <h3 class="text-lg font-semibold text-slate-900">Konfirmasi Layanan Pelanggan</h3>
            <p class="text-sm text-slate-600 leading-relaxed">
              Pastikan Anda benar-benar membutuhkan layanan ini. Penyalahgunaan antrian dapat mengganggu pelayanan.
            </p>
            <label class="flex items-start gap-3 text-sm text-slate-700">
              <input type="checkbox" class="mt-1 h-4 w-4 text-sky-600 border-slate-300 rounded" id="confirmCheckbox" />
              <span>Saya mengerti dan menyetujui ketentuan di atas.</span>
            </label>
          </div>
          <div class="px-6 pb-6 flex gap-2 justify-end">
            <button data-cancel class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">Batal</button>
            <button data-ok disabled class="px-4 py-2 rounded-xl bg-sky-600 text-white hover:bg-sky-700 shadow-sm disabled:opacity-40">Setuju & Ambil</button>
          </div>
        </div>
      `;

      const checkbox = overlay.querySelector("#confirmCheckbox");
      const okBtn = overlay.querySelector("[data-ok]");

      checkbox?.addEventListener("change", () => {
        okBtn.disabled = !checkbox.checked;
      });

      const cleanup = (val) => { overlay.remove(); resolve(val); };
      overlay.addEventListener("click", (e) => { if (e.target === overlay) cleanup(false); });
      overlay.querySelector("[data-cancel]").addEventListener("click", () => cleanup(false));
      okBtn.addEventListener("click", () => cleanup(true));

      document.body.appendChild(overlay);
    });
  }

  return confirmModal({
    title: "Ambil tiket?",
    message: `Anda akan mengambil tiket untuk layanan: ${service.name} (${service.code}).`,
    confirmText: "Ambil Tiket",
    cancelText: "Batal",
  });
}

function serviceBtn(s, idx) {
  const el = document.createElement("button");
  const theme = palette[idx % palette.length];
  el.className = `
    rounded-3xl border border-white/30 bg-white/95 hover:translate-y-[1px]
    p-6 text-left shadow-lg transition transform max-w-xl w-full
  `.replace(/\s+/g, " ");
  el.innerHTML = `
    <div class="flex flex-col items-center text-center space-y-3">
      <div class="h-12 w-12 rounded-full ${theme.icon} flex items-center justify-center text-white text-xl font-semibold shadow-md">
        ${s.code.slice(0,2)}
      </div>
      <div>
        <p class="text-lg font-semibold text-slate-900">${s.name}</p>
        <p class="text-sm text-slate-600 mt-1">${s.description || "Layanan tersedia"}</p>
      </div>
    </div>
    <div class="mt-4 grid grid-cols-2 gap-2 text-sm w-full">
      <div class="rounded-xl ${theme.bg} ${theme.text} px-3 py-2 border border-white/60 text-left">
        <p class="text-xs opacity-80">Antrian saat ini</p>
        <p class="font-semibold text-xl" data-current>–</p>
      </div>
      <div class="rounded-xl ${theme.bg} ${theme.text} px-3 py-2 border border-white/60 text-right">
        <p class="text-xs opacity-80">Menunggu</p>
        <p class="font-semibold text-xl" data-waiting>–</p>
      </div>
    </div>
    ${s.requires_confirmation ? '<p class="text-xs mt-3 text-amber-600 font-semibold text-center">Perlu konfirmasi sebelum ambil.</p>' : ""}
  `;
  el.addEventListener("click", async () => {
    const ok = await confirmForService(s);
    if (!ok) return;
    await takeTicket(s);
  });
  return el;
}

if (buttonsHost) {
  buttonsHost.innerHTML = "";
  services.forEach((s, idx) => buttonsHost.appendChild(serviceBtn(s, idx)));
}

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
        <p>Harap hadir 10 menit sebelum dipanggil.</p>
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

async function takeTicket(service) {
  resultHost.innerHTML = "";
  resultHost.appendChild(skeletonBlock(8));

  const body = {
    service_code: service.code,
  };

  if (service.requires_confirmation) {
    body.confirm_service = true;
  }

  const r = await apiFetch("/api/public/tickets", {
    method: "POST",
    body: JSON.stringify(body),
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

// Optional: hydrate waiting stats from /api/public/status if available
async function hydrateServiceStats() {
  if (!services.length) return;
  try {
    const r = await apiFetch("/api/public/status");
    if (!r.ok || !r.body?.data) return;
    const counters = r.body.data.counters || [];
    const waiting = r.body.data.waiting_by_service || {};

    services = services.map((s) => {
      const counter = counters.find((c) => c.service?.code === s.code);
      return {
        ...s,
        current_no: counter?.sedang_dipanggil || "-",
        waiting_count: waiting[s.code] ?? counter?.waiting ?? 0,
      };
    });

    // re-render small stats
    if (buttonsHost) {
      [...buttonsHost.children].forEach((card, i) => {
        card.querySelector("[data-current]")?.replaceChildren(document.createTextNode(services[i].current_no ?? "–"));
        card.querySelector("[data-waiting]")?.replaceChildren(document.createTextNode(services[i].waiting_count ?? "0"));
      });
    }
  } catch (e) {
    console.warn("hydrateServiceStats failed", e);
  }
}

hydrateServiceStats();
