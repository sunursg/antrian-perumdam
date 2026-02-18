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
    <header className="w-full min-h-[80px] bg-[#003366] bg-pdam-deep-blue shadow-2xl relative z-20 border-b-2 border-pdam-gold/50 flex shrink-0">
      <div className="flex h-full w-full max-w-[1920px] mx-auto items-center justify-between px-8 py-3">
        {/* LEFT: Branding */}
        <div className="flex items-center gap-6">
          <div className="h-16 w-16 shrink-0 rounded-2xl bg-white p-2 shadow-lg shadow-pdam-cyan/20">
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
            <h1 className="text-3xl font-black tracking-widest text-white uppercase drop-shadow-md font-sans">
              {brandName}
            </h1>
            {address ? (
              <p className="text-sm text-white/90 font-bold tracking-wide mt-1 max-w-2xl leading-tight hidden xl:block drop-shadow-sm">
                {address}
              </p>
            ) : slogan ? (
              <p className="text-sm text-pdam-highlight/80 font-medium tracking-wide">{slogan}</p>
            ) : null}
          </div>
        </div>

        {/* RIGHT: Big Digital Clock & Date */}
        {showClock ? (
          <div className="text-right">
            <ClockDate />
          </div>
        ) : null}
      </div>
    </header>
  );
}
