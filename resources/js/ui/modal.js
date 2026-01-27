export function confirmModal({ title="Konfirmasi", message="Lanjutkan?", confirmText="Ya", cancelText="Batal" } = {}) {
  return new Promise((resolve) => {
    const overlay = document.createElement("div");
    overlay.className = "fixed inset-0 z-[9998] bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4";

    overlay.innerHTML = `
      <div class="w-[420px] max-w-[94vw] rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden">
        <div class="p-5">
          <h3 class="text-base font-semibold">${title}</h3>
          <p class="text-sm text-slate-600 mt-1">${message}</p>
        </div>
        <div class="px-5 pb-5 flex gap-2 justify-end">
          <button data-cancel class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50"> ${cancelText} </button>
          <button data-ok class="px-4 py-2 rounded-xl bg-sky-600 text-white hover:bg-sky-700 shadow-sm"> ${confirmText} </button>
        </div>
      </div>
    `;

    const cleanup = (val) => { overlay.remove(); resolve(val); };

    overlay.addEventListener("click", (e) => { if (e.target === overlay) cleanup(false); });
    overlay.querySelector("[data-cancel]").addEventListener("click", () => cleanup(false));
    overlay.querySelector("[data-ok]").addEventListener("click", () => cleanup(true));

    document.body.appendChild(overlay);
  });
}
