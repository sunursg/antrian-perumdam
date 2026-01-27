export function skeletonBlock(lines = 3) {
  const wrap = document.createElement("div");
  wrap.className = "space-y-2";
  for (let i = 0; i < lines; i++) {
    const line = document.createElement("div");
    line.className = `h-3 rounded bg-slate-200/70 animate-pulse ${i === lines-1 ? 'w-2/3' : 'w-full'}`;
    wrap.appendChild(line);
  }
  return wrap;
}
