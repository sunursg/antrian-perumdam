import { apiFetch } from "../ui/http";
import { pulseHighlight } from "../ui/animate";

const grid = document.getElementById("displayGrid");
const badge = document.getElementById("connBadge");
const btnSound = document.getElementById("btnSound");

let soundEnabled = false;
let lastEventId = 0;

function conn(ok) {
  badge.textContent = ok ? "Terhubung" : "Rekoneksi…";
  badge.className = ok
    ? "text-xs px-3 py-1.5 rounded-full bg-emerald-500/15 border border-emerald-400/20 text-emerald-200"
    : "text-xs px-3 py-1.5 rounded-full bg-amber-500/15 border border-amber-400/20 text-amber-200";
}

function bellBeep() {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const o = ctx.createOscillator();
    const g = ctx.createGain();
    o.type = "sine";
    o.frequency.value = 880;
    g.gain.value = 0.08;
    o.connect(g);
    g.connect(ctx.destination);
    o.start();
    setTimeout(() => {
      o.stop();
      ctx.close();
    }, 180);
  } catch (_) {}
}

btnSound.addEventListener("click", () => {
  soundEnabled = !soundEnabled;
  btnSound.textContent = soundEnabled ? "Suara Aktif" : "Aktifkan Suara";
  btnSound.classList.toggle("bg-emerald-500/20", soundEnabled);
  btnSound.classList.toggle("border-emerald-400/30", soundEnabled);
  if (soundEnabled) bellBeep();
});

function speak(text) {
  if (!soundEnabled) return;
  if (!("speechSynthesis" in window)) return;
  const u = new SpeechSynthesisUtterance(text);
  u.lang = "id-ID";
  u.rate = 1.0;
  window.speechSynthesis.speak(u);
}

const state = { lokets: {} };

function loketCard(loketCode) {
  const data = state.lokets[loketCode];
  const el = document.createElement("div");
  el.dataset.loket = loketCode;
  el.className = "rounded-3xl bg-white/5 border border-white/10 shadow-lg p-6";

  const sedang = data.sedang || "-";
  const nextList = (data.berikutnya || []).slice(0, 3);

  el.innerHTML = `
    <div class="flex items-center justify-between">
      <div>
        <p class="text-white/70 text-sm">Loket</p>
        <p class="text-2xl font-semibold">${data.loket_name} <span class="text-white/60 text-base">(${loketCode})</span></p>
        <p class="text-white/60 text-sm mt-1">${data.service_name}</p>
      </div>
      <div class="text-right">
        <p class="text-white/60 text-sm">SEDANG DIPANGGIL</p>
        <p class="text-4xl font-semibold tracking-tight mt-1">${sedang}</p>
      </div>
    </div>

    <div class="mt-6 rounded-2xl bg-slate-900/40 border border-white/10 p-5">
      <p class="text-white/70 text-sm">Berikutnya</p>
      <div class="mt-3 grid grid-cols-3 gap-3">
        ${[0,1,2].map((i) => `
          <div class="rounded-xl bg-white/5 border border-white/10 p-3 text-center">
            <p class="text-sm font-semibold">${nextList[i] || "-"}</p>
          </div>
        `).join("")}
      </div>
    </div>
  `;
  return el;
}

function render() {
  grid.innerHTML = "";
  const keys = Object.keys(state.lokets);
  if (!keys.length) {
    grid.innerHTML = `<div class="rounded-2xl bg-white/5 border border-white/10 p-6 text-white/70">
      Belum ada loket aktif untuk ditampilkan.
    </div>`;
    return;
  }
  keys.sort().forEach((k) => grid.appendChild(loketCard(k)));
}

async function loadInitial() {
  const r = await apiFetch("/api/public/status");
  if (!r.ok || !r.body?.success) {
    render();
    return;
  }

  (r.body.data || []).forEach((row) => {
    state.lokets[row.loket.code] = {
      loket_name: row.loket.name,
      service_name: row.service.name,
      sedang: row.sedang_dipanggil,
      berikutnya: [],
    };
  });
  render();
}

function updateFromEvent(ev) {
  if (!ev.loket_code) return;

  const lk = ev.loket_code;
  if (!state.lokets[lk]) {
    state.lokets[lk] = { loket_name: `Loket ${lk}`, service_name: ev.service_code || "", sedang: null, berikutnya: [] };
  }

  const prevSedang = state.lokets[lk].sedang;

  if (ev.status === "DIPANGGIL") {
    state.lokets[lk].sedang = ev.ticket_no;

    setTimeout(() => {
      const el = document.querySelector(`[data-loket=\"${lk}\"]`);
      pulseHighlight(el);
    }, 0);

    if (soundEnabled) {
      bellBeep();
      speak(`Nomor ${ev.ticket_no}, silakan menuju loket ${lk}`);
    }
  }

  if (ev.status === "SELESAI" || ev.status === "NO_SHOW") {
    if (state.lokets[lk].sedang === ev.ticket_no) {
      state.lokets[lk].sedang = "-";
    }
  }

  if (prevSedang !== state.lokets[lk].sedang) render();
}

function startSse() {
  conn(false);

  const url = lastEventId ? `/api/sse/antrian?lastEventId=${lastEventId}` : `/api/sse/antrian`;
  const es = new EventSource(url);

  es.addEventListener("open", () => conn(true));

  es.addEventListener("queue_update", (e) => {
    if (e.lastEventId) lastEventId = parseInt(e.lastEventId, 10) || lastEventId;
    let data;
    try { data = JSON.parse(e.data); } catch { return; }
    updateFromEvent(data);
  });

  es.addEventListener("error", () => {
    conn(false);
    es.close();
    setTimeout(startSse, 900);
  });
}

await loadInitial();
startSse();
