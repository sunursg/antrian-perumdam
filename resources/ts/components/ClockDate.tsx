import React from "react";

const timeFormatter = new Intl.DateTimeFormat("id-ID", {
  hour: "2-digit",
  minute: "2-digit",
  second: "2-digit",
});

const dateFormatter = new Intl.DateTimeFormat("id-ID", {
  weekday: "long",
  day: "2-digit",
  month: "long",
  year: "numeric",
});

export default function ClockDate() {
  const [now, setNow] = React.useState(() => new Date());

  React.useEffect(() => {
    // Clock tick for realtime display.
    const timer = window.setInterval(() => setNow(new Date()), 1000);
    return () => window.clearInterval(timer);
  }, []);

  return (
    <div className="flex flex-col items-end justify-center h-full">
      <p className="text-5xl font-black tracking-tighter text-white drop-shadow-lg leading-none font-sans">
        {timeFormatter.format(now)}
      </p>
      <p className="text-sm font-bold text-pdam-gold uppercase tracking-widest mt-1">
        {dateFormatter.format(now)}
      </p>
    </div>
  );
}
