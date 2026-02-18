import React from "react";

type RunningTextProps = {
    text: string;
    speed?: number; // duration in seconds
    className?: string;
};

export default function RunningText({ text, speed = 20, className }: RunningTextProps) {
    return (
        <div className={`w-full overflow-hidden relative z-50 ${className ?? "bg-blue-900 border-t-4 border-amber-400 text-white py-3 shadow-[0_-4px_20px_rgba(0,0,0,0.3)]"}`}>
            <div
                className="whitespace-nowrap inline-block animate-marquee"
                style={{ animationDuration: `${speed}s` }}
            >
                <span className="text-lg sm:text-xl font-bold tracking-widest px-4">
                    {text}
                </span>
                {/* Duplicate for seamless loop */}
                <span className="text-lg sm:text-xl font-bold tracking-widest px-4">
                    {text}
                </span>
                <span className="text-lg sm:text-xl font-bold tracking-widest px-4">
                    {text}
                </span>
            </div>
            <style>{`
        @keyframes marquee {
          0% { transform: translateX(0); }
          100% { transform: translateX(-33.33%); }
        }
        .animate-marquee {
          animation: marquee linear infinite;
        }
      `}</style>
        </div>
    );
}
