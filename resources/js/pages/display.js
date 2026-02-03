import { apiFetch } from "../ui/http";
import { pulseHighlight } from "../ui/animate";

const dataEl = document.getElementById("display-data");
let initialData = {};
if (dataEl?.textContent) {
  try {
    initialData = JSON.parse(dataEl.textContent);
  } catch (e) {
    console.error("Failed to parse display initial data", e);
  }
}

const grid = document.getElementById("displayGrid");
const badge = document.getElementById("connBadge");
const btnSound = document.getElementById("btnSound");
const clockEl = document.getElementById("nowClock");
const dateEl = document.getElementById("nowDate");
const heroTicket = document.getElementById("heroTicket");
const heroCounter = document.getElementById("heroCounter");
const heroWaiting = document.getElementById("heroWaiting");
const announcementArea = document.getElementById("announcementArea");
const marqueeEl = document.getElementById("marqueeText");

const state = {
  counters: {},
  announcements: [],
  organization: {},
  lastEventId: 0,
};

let soundEnabled = false;

function mediaUrl(path) {
  if (!path) return null;
  if (path.startsWith("http")) return path;
  return `/storage/${path}`;
}

function conn(ok) {
  if (!badge) return;
  badge.textContent = ok ? "Terhubung" : "Rekoneksi…";
  badge.className = ok
    ? "text-xs px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-700"
    : "text-xs px-3 py-1.5 rounded-full bg-amber-100 text-amber-700";
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

function speak(text) {
  if (!soundEnabled) return;
  if (!("speechSynthesis" in window)) return;
  const u = new SpeechSynthesisUtterance(text);
  u.lang = "id-ID";
  u.rate = 1.0;
  window.speechSynthesis.speak(u);
}

btnSound?.addEventListener("click", () => {
  soundEnabled = !soundEnabled;
  btnSound.textContent = soundEnabled ? "Suara Aktif" : "Aktifkan Suara";
  if (soundEnabled) bellBeep();
});

function renderCounters() {
  if (!grid) return;
  grid.innerHTML = "";
  const entries = Object.values(state.counters);
  if (!entries.length) {
    grid.innerHTML = `<div class="rounded-2xl bg-white/5 border border-white/10 p-6 text-white/70">Belum ada loket untuk ditampilkan.</div>`;
    return;
  }

  entries
    .sort((a, b) => a.code.localeCompare(b.code))
    .forEach((data) => {
      const el = document.createElement("div");
      el.dataset.loket = data.code;
      el.className = "rounded-3xl bg-white/5 border border-white/10 shadow-lg p-5";
      const ticketText = data.current || "-";
      const statusLabel = data.is_active ? "Aktif" : "Tidak Aktif";
      const statusColor = data.is_active ? "text-emerald-200" : "text-amber-200";

      el.innerHTML = `
        <div class="flex items-center justify-between">
          <div>
            <p class="text-white/60 text-xs">Loket</p>
            <p class="text-xl font-semibold">${data.name} <span class="text-white/50 text-sm">(${data.code})</span></p>
            <p class="text-white/60 text-xs mt-1">${data.service_name ?? "-"}</p>
          </div>
          <div class="text-right">
            <p class="text-white/60 text-xs">SEDANG DIPANGGIL</p>
            <p class="text-3xl font-semibold tracking-tight mt-1">${ticketText}</p>
          </div>
        </div>
        <div class="mt-5 rounded-2xl bg-slate-900/40 border border-white/10 p-4 flex items-center justify-between">
          <p class="text-sm font-semibold ${statusColor}">${statusLabel}</p>
          <span class="text-xs text-white/60">Realtime</span>
        </div>
      `;

      grid.appendChild(el);
    });

  // update hero section with first active
  const active = entries.find((c) => c.is_active) || entries[0];
  if (heroTicket && heroCounter) {
    heroTicket.textContent = active?.current || "-";
    heroCounter.textContent = active ? `Loket ${active.code}` : "Loket -";
    if (heroWaiting) {
      heroWaiting.textContent = active?.service_name || "";
    }
  }
}

function updateMarquee(text) {
  if (!marqueeEl) return;
  const content = text || state.organization?.general_notice || state.announcements[0]?.body || state.organization?.tagline || state.organization?.name || "Sistem Antrian";
  marqueeEl.innerHTML = `
    <span class="mx-8">${content}</span>
    <span class="mx-8">${content}</span>
    <span class="mx-8">${content}</span>
  `;
}

function renderAnnouncements() {
  if (!announcementArea) return;
  announcementArea.innerHTML = "";
  const active = state.announcements?.[0];
  if (!active) {
    announcementArea.innerHTML = `<p class="text-sm text-slate-500">Pengumuman / Video akan tampil di sini.</p>`;
    updateMarquee();
    return;
  }

  if (active.type === "VIDEO") {
    const video = document.createElement("video");
    video.className = "w-full rounded-xl shadow";
    video.controls = true;
    video.autoplay = true;
    video.loop = true;
    video.muted = true;
    const src = document.createElement("source");
    src.src = active.video_url || mediaUrl(active.media_path);
    src.type = "video/mp4";
    video.appendChild(src);
    announcementArea.appendChild(video);
  } else {
    const wrap = document.createElement("div");
    wrap.innerHTML = `
      <p class="text-sm font-semibold text-slate-900">${active.title}</p>
      <p class="text-sm text-slate-600 mt-1">${active.body ?? ""}</p>
    `;
    announcementArea.appendChild(wrap);
  }

  updateMarquee(active.body || active.title);
}

function hydrateInitial() {
  const hasParsed = initialData && Object.keys(initialData).length > 0;
  const init = hasParsed ? initialData : (window.DISPLAY_INITIAL || {});
  state.organization = init.organization || {};
  state.announcements = init.announcements || [];
  (init.counters || []).forEach((c) => {
    state.counters[c.loket.code] = {
      code: c.loket.code,
      name: c.loket.name,
      service_name: c.service?.name,
      is_active: c.is_active,
      current: c.sedang_dipanggil || "-",
    };
  });
  renderCounters();
  renderAnnouncements();
}

async function refreshFromApi() {
  const r = await apiFetch("/api/public/status");
  if (!r.ok || !r.body?.data) return;

  state.organization = r.body.data.organization || {};
  state.announcements = r.body.data.announcements || [];
  state.counters = {};
  (r.body.data.counters || []).forEach((row) => {
    state.counters[row.loket.code] = {
      code: row.loket.code,
      name: row.loket.name,
      service_name: row.service?.name,
      is_active: row.is_active,
      current: row.sedang_dipanggil || "-",
    };
  });

  renderCounters();
  renderAnnouncements();
}

function handleQueueEvent(ev) {
  if (!ev.loket_code) return;
  const code = ev.loket_code;
  if (!state.counters[code]) {
    state.counters[code] = {
      code,
      name: `Loket ${code}`,
      service_name: ev.service_code || "",
      is_active: true,
      current: "-",
    };
  }

  const prev = state.counters[code].current;

  if (ev.status === "DIPANGGIL") {
    state.counters[code].current = ev.ticket_no;

    setTimeout(() => {
      const el = document.querySelector(`[data-loket="${code}"]`);
      if (el) pulseHighlight(el);
    }, 0);

    if (soundEnabled) {
      bellBeep();
      speak(`Nomor ${ev.ticket_no}, silakan menuju loket ${code}`);
    }
  }

  if (ev.status === "SELESAI" || ev.status === "NO_SHOW") {
    if (state.counters[code].current === ev.ticket_no) {
      state.counters[code].current = "-";
    }
  }

  if (prev !== state.counters[code].current) {
    renderCounters();
  }
}

function handleCounterStatusUpdate(ev) {
  const counters = ev.payload?.counters || [];
  counters.forEach((c) => {
    state.counters[c.code] = {
      code: c.code,
      name: c.name,
      service_name: c.service?.name,
      is_active: c.is_active,
      current: state.counters[c.code]?.current ?? "-",
    };
  });
  renderCounters();
}

function handleAnnouncementUpdate(ev) {
  state.announcements = ev.payload?.announcements || [];
  renderAnnouncements();
}

function handleOrganizationUpdate(ev) {
  if (ev.payload?.organization) {
    state.organization = ev.payload.organization;
    updateMarquee();
  }
}

function startSse() {
  conn(false);
  const url = state.lastEventId ? `/api/sse/antrian?lastEventId=${state.lastEventId}` : `/api/sse/antrian`;
  const es = new EventSource(url);

  es.addEventListener("open", () => conn(true));

  es.addEventListener("queue_update", (e) => {
    if (e.lastEventId) state.lastEventId = parseInt(e.lastEventId, 10) || state.lastEventId;
    let data; try { data = JSON.parse(e.data); } catch { return; }
    handleQueueEvent(data);
  });

  es.addEventListener("counter_status_update", (e) => {
    if (e.lastEventId) state.lastEventId = parseInt(e.lastEventId, 10) || state.lastEventId;
    let data; try { data = JSON.parse(e.data); } catch { return; }
    handleCounterStatusUpdate(data);
  });

  es.addEventListener("announcement_update", (e) => {
    if (e.lastEventId) state.lastEventId = parseInt(e.lastEventId, 10) || state.lastEventId;
    let data; try { data = JSON.parse(e.data); } catch { return; }
    handleAnnouncementUpdate(data);
  });

  es.addEventListener("organization_update", (e) => {
    if (e.lastEventId) state.lastEventId = parseInt(e.lastEventId, 10) || state.lastEventId;
    let data; try { data = JSON.parse(e.data); } catch { return; }
    handleOrganizationUpdate(data);
  });

  es.addEventListener("error", () => {
    conn(false);
    es.close();
    setTimeout(startSse, 1000);
  });
}

function tickClock() {
  const now = new Date();
  if (clockEl) clockEl.textContent = now.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" });
  if (dateEl) dateEl.textContent = now.toLocaleDateString("id-ID", { weekday: "long", day: "numeric", month: "long", year: "numeric" });
}

hydrateInitial();
refreshFromApi().catch(() => {});
startSse();
tickClock();
setInterval(tickClock, 1000);
