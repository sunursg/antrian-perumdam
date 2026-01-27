export function pulseHighlight(el) {
  if (!el) return;
  el.classList.remove("glow-call");
  el.classList.remove("animate-pulse");
  void el.offsetWidth;
  el.classList.add("glow-call");
  el.classList.add("animate-pulse");
  setTimeout(() => {
    el.classList.remove("animate-pulse");
  }, 1400);
}
