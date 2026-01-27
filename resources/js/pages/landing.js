import { apiFetch } from "../ui/http";
import { skeletonBlock } from "../ui/skeleton";

const host = document.getElementById("landingStatus");
host.appendChild(skeletonBlock(6));

function card(item) {
  const el = document.createElement("div");
  el.className = "rounded-xl bg-white/10 border border-white/15 p-4";
  el.innerHTML = `
    <div class="flex items-center justify-between">
      <p class="text-sm font-semibold">${item.loket.name}</p>
      <span class="text-xs px-2 py-1 rounded-full bg-white/15 border border-white/15">${item.service.code}</span>
    </div>
    <div class="mt-3 grid grid-cols-3 gap-2 text-xs text-white/85">
      <div>
        <p class="text-white/70">Sedang Dipanggil</p>
        <p class="text-sm font-semibold text-white">${item.sedang_dipanggil || "-"}</p>
      </div>
      <div>
        <p class="text-white/70">Menunggu</p>
        <p class="text-sm font-semibold text-white">${item.jumlah_menunggu}</p>
      </div>
      <div>
        <p class="text-white/70">Estimasi</p>
        <p class="text-sm font-semibold text-white">${item.estimasi_menit} mnt</p>
      </div>
    </div>
  `;
  return el;
}

async function load() {
  const r = await apiFetch("/api/public/status");
  host.innerHTML = "";
  if (!r.ok || !r.body?.success) {
    host.innerHTML = `<div class="rounded-xl bg-white/10 border border-white/15 p-4 text-sm text-white/80">
      Gagal memuat status.
    </div>`;
    return;
  }

  const data = r.body.data || [];
  if (!data.length) {
    host.innerHTML = `<div class="rounded-xl bg-white/10 border border-white/15 p-4 text-sm text-white/80">
      Belum ada data loket aktif.
    </div>`;
    return;
  }

  data.slice(0, 3).forEach((it) => host.appendChild(card(it)));
}

load();
setInterval(load, 5000);
