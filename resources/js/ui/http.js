// resources/js/ui/http.js
export async function apiFetch(url, opts = {}) {
  const tokenEl = document.querySelector('meta[name="csrf-token"]');
  const token = tokenEl ? tokenEl.getAttribute('content') : '';

  const headers = {
    Accept: "application/json",
    "Content-Type": "application/json",
    ...(token ? { "X-CSRF-TOKEN": token } : {}),
    ...(opts.headers || {}),
  };

  const res = await fetch(url, {
    credentials: "same-origin", // penting biar cookie/session ikut
    ...opts,
    headers,
  });

  // kalau server ngembaliin HTML (error 419/500), json() bakal gagal, jadi amanin
  const body = await res.json().catch(() => null);

  if (body && typeof body === "object") {
    // merge supaya pemanggil bisa langsung pakai success/data/message
    return { ok: res.ok, status: res.status, body, ...body };
  }

  return { ok: res.ok, status: res.status, body };
}
