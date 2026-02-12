import { apiFetch } from "../ui/http";
import { toast } from "../ui/toast";

const hostLokets = document.getElementById("opLokets");
const controls = document.getElementById("opControls");
const btnBackHome = document.getElementById("btnBackHome");
const opCurrentLoket = document.getElementById("opCurrentLoket");

const btnNext = document.getElementById("btnNext");
const btnRecall = document.getElementById("btnRecall");
const btnSkip = document.getElementById("btnSkip");
const btnServe = document.getElementById("btnServe");
const opCurrent = document.getElementById("opCurrent");

const apiBase = document.body?.dataset?.operatorApiBase || "/counter-api";
let isActionRunning = false;
let loketCode = localStorage.getItem("op_loket_code") || null;

btnBackHome?.addEventListener("click", () => (window.location.href = "/"));

const getErrorMessage = (res) => {
  if (!res) return "Tidak dapat terhubung ke server.";
  if (typeof res.message === "string" && res.message.trim() !== "") return res.message;
  if (res.status === 401) return "Sesi login habis. Silakan login ulang.";
  if (res.status === 403) return "Akses ditolak. Akun belum ditugaskan ke loket.";
  if (res.status >= 500) return "Server sedang bermasalah. Coba lagi sebentar.";
  return "Permintaan gagal diproses.";
};

function setButtonsDisabled(disabled) {
  [btnNext, btnRecall, btnSkip, btnServe].forEach((btn) => {
    if (btn) btn.disabled = disabled;
  });
}

async function refreshCurrent() {
  if (!loketCode || !opCurrent) return;

  const res = await apiFetch(`${apiBase}/lokets/${loketCode}/current`, {
    method: "GET",
  });

  if (res?.success) {
    opCurrent.textContent = res.data?.ticket_no ?? "-";
  }
}

function renderLoketItem(loket) {
  const el = document.createElement("button");
  el.dataset.code = loket.code;
  el.className =
    "w-full text-left p-5 rounded-2xl border border-slate-200 hover:border-sky-300 hover:shadow-md bg-white/90 transition";

  el.innerHTML = `
    <p class="text-lg font-bold text-slate-900">${loket.name}</p>
    <p class="text-xs tracking-[0.18em] text-slate-500 mt-1">LAYANAN: ${loket.service?.code ?? "-"} • ${loket.service?.name ?? "Belum diset"}</p>
    <p class="text-xs mt-2 ${loket.is_active ? "text-emerald-700" : "text-rose-700"}">${loket.is_active ? "Loket aktif" : "Loket nonaktif"}</p>
  `;
  if (!loket.is_active) {
    el.classList.add("opacity-70", "cursor-not-allowed");
  }

  const selectLoket = async (showToast) => {
    loketCode = loket.code;
    localStorage.setItem("op_loket_code", loketCode);

    [...hostLokets.children].forEach((child) => {
      child.classList.remove("ring-2", "ring-sky-300", "shadow-lg");
    });

    el.classList.add("ring-2", "ring-sky-300", "shadow-lg");

    controls?.classList.remove("hidden");
    if (opCurrentLoket) opCurrentLoket.textContent = loket.code;

    setButtonsDisabled(false);
    await refreshCurrent().catch(() => {});

    if (showToast) {
      toast({
        title: "Loket dipilih",
        message: `${loket.name} (${loket.code})`,
        variant: "success",
      });
    }
  };

  el.addEventListener("click", () => {
    if (!loket.is_active) {
      toast({
        title: "Loket nonaktif",
        message: "Loket ini sedang nonaktif. Hubungi superadmin untuk mengaktifkan.",
        variant: "warning",
      });
      return;
    }
    selectLoket(true).catch(() => {});
  });

  return { el, selectLoket };
}

async function loadLokets() {
  if (!hostLokets) return;

  hostLokets.innerHTML = '<div class="text-sm text-slate-500">Memuat daftar loket...</div>';

  const res = await apiFetch(`${apiBase}/lokets`, { method: "GET" });

  if (!res?.ok || !res?.success) {
    hostLokets.innerHTML = `<div class="text-sm text-rose-600">${getErrorMessage(res)}</div>`;
    controls?.classList.add("hidden");
    setButtonsDisabled(true);
    return;
  }

  const lokets = Array.isArray(res.data) ? res.data : [];
  const activeLokets = lokets.filter((x) => x.is_active !== false);
  hostLokets.innerHTML = "";

  if (lokets.length === 0) {
    hostLokets.innerHTML = '<div class="text-sm text-slate-500">Tidak ada loket yang ditugaskan untuk akun ini.</div>';
    controls?.classList.add("hidden");
    setButtonsDisabled(true);
    return;
  }

  const rendered = lokets.map(renderLoketItem);
  rendered.forEach((entry) => hostLokets.appendChild(entry.el));

  if (activeLokets.length === 0) {
    controls?.classList.add("hidden");
    setButtonsDisabled(true);
    return;
  }

  const target = rendered.find((item) => item.el.dataset.code === loketCode && activeLokets.some((l) => l.code === loketCode));

  if (target) {
    await target.selectLoket(false);
    return;
  }

  loketCode = activeLokets[0].code;
  localStorage.setItem("op_loket_code", loketCode);
  await rendered[0].selectLoket(false);
}

async function action(path, okMsg) {
  if (isActionRunning) return;

  if (!loketCode) {
    toast({
      title: "Pilih loket dulu",
      message: "Loket tugas belum dipilih.",
      variant: "warning",
    });
    return;
  }

  isActionRunning = true;
  setButtonsDisabled(true);

  const res = await apiFetch(path, { method: "POST" });

  if (!res?.ok || !res?.success) {
    toast({ title: "Gagal", message: getErrorMessage(res), variant: "danger" });
    isActionRunning = false;
    setButtonsDisabled(false);
    return;
  }

  toast({ title: "Berhasil", message: okMsg, variant: "success" });
  await refreshCurrent().catch(() => {});

  isActionRunning = false;
  setButtonsDisabled(false);
}

btnNext?.addEventListener("click", () =>
  action(`${apiBase}/lokets/${loketCode}/call-next`, "Memanggil berikutnya.")
);
btnRecall?.addEventListener("click", () =>
  action(`${apiBase}/lokets/${loketCode}/recall`, "Memanggil ulang.")
);
btnSkip?.addEventListener("click", () =>
  action(`${apiBase}/lokets/${loketCode}/skip`, "Menandai no-show.")
);
btnServe?.addEventListener("click", () =>
  action(`${apiBase}/lokets/${loketCode}/serve`, "Menyelesaikan layanan.")
);

loadLokets().catch((e) => {
  console.error(e);
  if (hostLokets) {
    hostLokets.innerHTML = '<div class="text-sm text-rose-600">Terjadi kesalahan saat memuat loket.</div>';
  }
});

setInterval(() => {
  refreshCurrent().catch(() => {});
}, 5000);
