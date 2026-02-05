let host;

function ensureHost() {
  if (host) return host;
  host = document.createElement("div");
  host.className = "fixed top-4 right-4 z-[9999] space-y-3";
  document.body.appendChild(host);
  return host;
}

export function toast({ title = "Info", message = "", variant = "info", timeout = 3200 } = {}) {
  const root = ensureHost();
  const el = document.createElement("div");

  const styles = {
    info: "border-sky-200 bg-white/95",
    success: "border-emerald-200 bg-white/95",
    warning: "border-amber-200 bg-white/95",
    danger: "border-rose-200 bg-white/95",
  };

  el.className = `
    w-[360px] max-w-[92vw] rounded-xl border ${styles[variant] || styles.info}
    shadow-lg backdrop-blur px-4 py-3
    animate-[toastIn_.22s_ease-out]
  `.replace(/\s+/g, " ");

  el.innerHTML = `
    <div class="flex items-start gap-3">
      <div class="mt-0.5 h-2.5 w-2.5 rounded-full ${variant === 'success' ? 'bg-emerald-500' : variant === 'danger' ? 'bg-rose-500' : variant === 'warning' ? 'bg-amber-500' : 'bg-sky-500'}"></div>
      <div class="min-w-0">
        <p class="text-sm font-semibold">${title}</p>
        <p class="text-sm text-slate-600 mt-0.5 break-words">${message}</p>
      </div>
      <button class="ml-auto text-slate-400 hover:text-slate-700" aria-label="Tutup">x</button>
    </div>
  `;

  const btn = el.querySelector("button");
  const remove = () => {
    el.classList.add("animate-[toastOut_.18s_ease-in_forwards]");
    setTimeout(() => el.remove(), 180);
  };
  btn.addEventListener("click", remove);

  root.appendChild(el);
  setTimeout(remove, timeout);
}

const style = document.createElement("style");
style.textContent = `
@keyframes toastIn { from { opacity:0; transform: translateY(-8px); } to { opacity:1; transform: translateY(0);} }
@keyframes toastOut { to { opacity:0; transform: translateY(-8px);} }
`;
document.head.appendChild(style);

