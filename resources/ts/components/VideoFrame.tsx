import React, { useMemo, useRef, useState } from "react";

type VideoFrameProps = {
  announcement?: {
    title?: string;
    video_url?: string | null;
  } | null;
  connectionLabel?: string;
  className?: string;
};

const isYouTubeUrl = (url: string) =>
  /youtube\.com|youtu\.be/.test(url.toLowerCase());

const isVideoFile = (url: string) =>
  /\.(mp4|webm|ogg)(\?.*)?$/i.test(url);

const getYouTubeEmbedUrl = (url: string, muted: boolean) => {
  try {
    const parsed = new URL(url);
    let id: string | null = null;

    if (parsed.hostname.includes("youtu.be")) {
      id = parsed.pathname.slice(1);
    }

    if (!id && parsed.hostname.includes("youtube.com")) {
      if (parsed.pathname.startsWith("/embed/")) {
        id = parsed.pathname.split("/")[2] ?? null;
      } else {
        id = parsed.searchParams.get("v");
      }
    }

    if (!id) return null;
    const muteFlag = muted ? "1" : "0";
    const origin = encodeURIComponent(window.location.origin);
    return `https://www.youtube.com/embed/${id}?autoplay=1&mute=${muteFlag}&controls=0&loop=1&playlist=${id}&modestbranding=1&rel=0&enablejsapi=1&origin=${origin}`;
  } catch {
    return null;
  }
};

export default function VideoFrame({
  announcement,
  connectionLabel,
  className,
}: VideoFrameProps) {
  const title = announcement?.title || "Tidak ada pengumuman";
  const videoUrl = announcement?.video_url?.trim();
  const [isMuted, setIsMuted] = useState(true);
  const videoRef = useRef<HTMLVideoElement | null>(null);
  const iframeRef = useRef<HTMLIFrameElement | null>(null);

  const isVideo = useMemo(() => {
    if (!videoUrl) return false;
    return isYouTubeUrl(videoUrl) || isVideoFile(videoUrl);
  }, [videoUrl]);

  let content: React.ReactNode = (
    <div className="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-center px-8">
      <div>
        <p className="text-sm uppercase tracking-[0.3em] text-white/60">
          Pengumuman
        </p>
        <p className="mt-4 text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white">
          {title}
        </p>
        <p className="mt-3 text-sm text-white/70">
          Tidak ada video yang sedang diputar.
        </p>
      </div>
    </div>
  );

  if (videoUrl) {
    if (isYouTubeUrl(videoUrl)) {
      const embedUrl = getYouTubeEmbedUrl(videoUrl, isMuted);
      content = embedUrl ? (
        <iframe
          key={embedUrl}
          ref={iframeRef}
          src={embedUrl}
          title={title}
          className="h-full w-full"
          allow="autoplay; encrypted-media; fullscreen"
          allowFullScreen
        />
      ) : (
        content
      );
    } else if (isVideoFile(videoUrl)) {
      content = (
        <video
          ref={videoRef}
          className="h-full w-full object-cover"
          autoPlay
          muted={isMuted}
          loop
          playsInline
        >
          <source src={videoUrl} />
        </video>
      );
    }
  }

  const badgeText = videoUrl ? "LIVE" : "PENGUMUMAN";

  return (
    <section
      className={`relative flex h-full w-full items-center justify-center group ${className ?? ""}`}
    >
      <div className="absolute inset-0 rounded-[36px] bg-black/30 blur-2xl" />
      <div className="relative h-full w-full rounded-[36px] border border-white/20 bg-slate-950/60 p-4 shadow-2xl">
        <div className="relative h-full w-full overflow-hidden rounded-[28px] border border-white/10 bg-slate-900/80">
          {content}
        </div>
      </div>

      {isVideo ? (
        <button
          type="button"
          className="absolute right-4 bottom-4 sm:right-8 sm:bottom-8 z-20 rounded-full bg-slate-950/80 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-white/90 shadow-lg hover:bg-slate-950 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
          onClick={() => {
            const next = !isMuted;
            setIsMuted(next);
            if (videoRef.current) {
              // ensure audio toggle is applied immediately for HTML5 video
              videoRef.current.muted = next;
            }
            if (iframeRef.current) {
              // YouTube JS API: unmute/mute then play
              const command = next ? "mute" : "unMute";
              iframeRef.current.contentWindow?.postMessage(
                JSON.stringify({
                  event: "command",
                  func: command,
                  args: [],
                }),
                "*"
              );
              iframeRef.current.contentWindow?.postMessage(
                JSON.stringify({
                  event: "command",
                  func: "playVideo",
                  args: [],
                }),
                "*"
              );
            }
          }}
        >
          {isMuted ? "Unmute" : "Mute"}
        </button>
      ) : null}
    </section>
  );
}
