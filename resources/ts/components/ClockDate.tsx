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
    <div className="text-right">
      <p className="text-2xl sm:text-3xl lg:text-4xl font-semibold tracking-tight text-shadow-soft">
        {timeFormatter.format(now)}
      </p>
      <p className="text-xs sm:text-sm text-white/80 mt-1">
        {dateFormatter.format(now)}
      </p>
    </div>
  );
}
