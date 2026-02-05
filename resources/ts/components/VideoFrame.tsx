import React from "react";

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

const getYouTubeEmbedUrl = (url: string) => {
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
    return `https://www.youtube.com/embed/${id}?autoplay=1&mute=1&controls=0&loop=1&playlist=${id}&modestbranding=1&rel=0`;
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
      const embedUrl = getYouTubeEmbedUrl(videoUrl);
      content = embedUrl ? (
        <iframe
          src={embedUrl}
          title={title}
          className="h-full w-full"
          allow="autoplay; encrypted-media"
          allowFullScreen
        />
      ) : (
        content
      );
    } else if (isVideoFile(videoUrl)) {
      content = (
        <video
          className="h-full w-full object-cover"
          autoPlay
          muted
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
      className={`relative flex h-full w-full items-center justify-center ${className ?? ""}`}
    >
      <div className="absolute inset-0 rounded-[36px] bg-black/30 blur-2xl" />
      <div className="relative h-full w-full rounded-[36px] border border-white/20 bg-slate-950/60 p-4 shadow-2xl">
        <div className="relative h-full w-full overflow-hidden rounded-[28px] border border-white/10 bg-slate-900/80">
          {content}
        </div>
      </div>

      <div className="absolute left-4 top-4 sm:left-8 sm:top-8 rounded-full bg-rose-500/90 px-4 py-1.5 text-xs font-bold tracking-[0.3em] text-white shadow-lg">
        {badgeText}
      </div>
      <div className="absolute right-4 top-4 sm:right-8 sm:top-8 rounded-full bg-white/15 px-4 py-1.5 text-xs font-semibold text-white">
        {connectionLabel || "-"}
      </div>
      <div className="absolute bottom-4 left-4 sm:bottom-8 sm:left-8 rounded-2xl bg-slate-950/70 px-4 py-2 text-sm text-white/80">
        {title}
      </div>
    </section>
  );
}
