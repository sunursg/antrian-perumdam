import React from "react";
import ClockDate from "./ClockDate";

type TopBarProps = {
  brandName: string;
  slogan?: string | null;
  logoUrl?: string | null;
  showClock?: boolean;
};

export default function TopBar({
  brandName,
  slogan,
  logoUrl,
  showClock = true,
}: TopBarProps) {
  return (
    <header className="w-full min-h-[96px] bg-gradient-to-r from-indigo-900 via-purple-800 to-blue-800 shadow-2xl">
      <div className="mx-auto flex max-w-[1400px] items-center justify-between gap-6 px-6 py-5 sm:px-8">
        <div className="flex items-center gap-4">
          <div className="h-14 w-14 rounded-2xl bg-white/95 p-2 shadow-lg">
            {logoUrl ? (
              <img
                src={logoUrl}
                alt={brandName}
                className="h-full w-full object-contain"
              />
            ) : (
              <div className="h-full w-full rounded-xl bg-slate-200" />
            )}
          </div>
          <div>
            <p className="text-xl sm:text-2xl font-extrabold tracking-wide text-white">
              {brandName}
            </p>
            {slogan ? (
              <p className="text-xs sm:text-sm text-white/70">{slogan}</p>
            ) : null}
          </div>
        </div>
        {showClock ? <ClockDate /> : null}
      </div>
    </header>
  );
}
