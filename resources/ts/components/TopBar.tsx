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
  address,
  showClock = true,
}: TopBarProps & { address?: string }) {
  return (
    <header className="w-full min-h-[72px] bg-gradient-to-r from-blue-900 via-indigo-900 to-blue-900 shadow-xl relative z-20 border-b border-white/10">
      <div className="mx-auto flex h-full w-full max-w-[1920px] items-center justify-between gap-6 px-6 py-2 sm:px-8">
        <div className="flex items-center gap-4">
          <div className="h-12 w-12 shrink-0 rounded-xl bg-white/95 p-1.5 shadow-lg backdrop-blur-sm">
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
          <div className="flex flex-col justify-center">
            <h1 className="text-xl sm:text-2xl font-black tracking-wider text-white uppercase drop-shadow-md">
              {brandName}
            </h1>
            {address ? (
              <p className="text-xs text-cyan-100/90 font-medium tracking-wide mt-0.5 max-w-2xl leading-tight hidden sm:block">
                {address}
              </p>
            ) : slogan ? (
              <p className="text-sm text-cyan-100/80 font-medium tracking-wide">{slogan}</p>
            ) : null}
          </div>
        </div>
        {showClock ? <ClockDate /> : null}
      </div>
    </header>
  );
}
