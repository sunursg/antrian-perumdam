export async function apiFetch(url, opts = {}) {
  const headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
    ...(opts.headers || {}),
  };

  const res = await fetch(url, { ...opts, headers });
  const json = await res.json().catch(() => null);

  if (!res.ok) {
    return { ok: false, status: res.status, body: json };
  }
  return { ok: true, status: res.status, body: json };
}
