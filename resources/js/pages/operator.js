import { apiFetch } from "../ui/http";
import { toast } from "../ui/toast";

const hostLokets = document.getElementById("opLokets");
const controls = document.getElementById("opControls");
const btnBackHome = document.getElementById("btnBackHome");

const btnNext = document.getElementById("btnNext");
const btnRecall = document.getElementById("btnRecall");
const btnSkip = document.getElementById("btnSkip");
const btnServe = document.getElementById("btnServe");
const opCurrent = document.getElementById("opCurrent");

btnBackHome?.addEventListener("click", () => (window.location.href = "/"));

let loketCode = localStorage.getItem("op_loket_code") || null;

function loketItem(l) {
  const el = document.createElement("button");
  el.className =
    "w-full text-left p-5 rounded-2xl border border-slate-200 hover:bg-slate-50 shadow-sm transition";
  el.innerHTML = `
    <p class="text-lg font-bold text-slate-900">${l.name}</p>
    <p class="text-xs tracking-[0.18em] text-slate-400 mt-1">LAYANAN: ${l.service?.code ?? "-"} • ${l.service?.name ?? "-"}</p>
  `;

  el.addEventListener("click", () => {
    loketCode = l.code;
    localStorage.setItem("op_loket_code", loketCode);

    // unhighlight all
    [...hostLokets.children].forEach((c) =>
      c.classList.remove("ring-2", "ring-sky-200")
    );
    el.classList.add("ring-2", "ring-sky-200");

    controls?.classList.remove("hidden");
    toast({
      title: "Loket dipilih",
      message: `${l.name} (${l.code})`,
      variant: "success",
    });

    // optional refresh current
    refreshCurrent().catch(() => {});
  });

  // auto highlight if matches stored
  if (loketCode && l.code === loketCode) {
    el.classList.add("ring-2", "ring-sky-200");
    controls?.classList.remove("hidden");
  }

  return el;
}

async function loadLokets() {
  if (!hostLokets) return;

  hostLokets.innerHTML = `<div class="text-sm text-slate-500">Memuat daftar loket...</div>`;

  // endpoint ini sesuaikan dengan backend kamu
  // contoh: GET /api/operator/lokets
  const res = await apiFetch("/api/operator/lokets", { method: "GET" });

  if (!res?.success) {
    hostLokets.innerHTML = `<div class="text-sm text-red-600">Gagal memuat loket.</div>`;
    return;
  }

  hostLokets.innerHTML = "";
  (res.data || []).forEach((l) => hostLokets.appendChild(loketItem(l)));

  if ((res.data || []).length === 0) {
    hostLokets.innerHTML = `
      <div class="text-sm text-slate-500">
        Tidak ada loket yang ditugaskan untuk akun ini.
      </div>
    `;
  }
}

async function refreshCurrent() {
  if (!loketCode || !opCurrent) return;
  const res = await apiFetch(`/api/operator/lokets/${loketCode}/current`, {
    method: "GET",
  });
  if (res?.success) opCurrent.textContent = res.data?.ticket_no ?? "-";
}

async function action(path, okMsg) {
  if (!loketCode) {
    toast({ title: "Pilih loket dulu", message: "Loket tugas belum dipilih.", variant: "warning" });
    return;
  }
  const res = await apiFetch(path, { method: "POST" });
  if (!res?.success) {
    toast({ title: "Gagal", message: res?.message || "Aksi gagal.", variant: "danger" });
    return;
  }
  toast({ title: "Berhasil", message: okMsg, variant: "success" });
  await refreshCurrent();
}

btnNext?.addEventListener("click", () =>
  action(`/api/operator/lokets/${loketCode}/call-next`, "Memanggil berikutnya.")
);
btnRecall?.addEventListener("click", () =>
  action(`/api/operator/lokets/${loketCode}/recall`, "Memanggil ulang.")
);
btnSkip?.addEventListener("click", () =>
  action(`/api/operator/lokets/${loketCode}/skip`, "Menandai no-show.")
);
btnServe?.addEventListener("click", () =>
  action(`/api/operator/lokets/${loketCode}/serve`, "Menyelesaikan layanan.")
);

loadLokets().catch((e) => {
  console.error(e);
});