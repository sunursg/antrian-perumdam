import React, { useEffect, useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import {
    Mic,
    RefreshCcw,
    SkipForward,
    CheckCircle,
    LogOut,
    Monitor,
    User as UserIcon,
    Clock,
    Users,
    Activity
} from "lucide-react";
import { apiFetch } from "../lib/http";
import { announce } from "../lib/audio";
import clsx from "clsx";
import { twMerge } from "tailwind-merge";

// Utility for classes
function cn(...inputs: (string | undefined | null | false)[]) {
    return twMerge(clsx(inputs));
}

// Interfaces
interface User {
    id: number;
    name: string;
    email: string;
    roles: string[];
}

interface QueueStats {
    total: number;
    pending: number;
    completed: number;
    avg_service_time: number;
}

interface Loket {
    id: number;
    code: string;
    name: string;
    is_active: boolean;
    service?: {
        code: string;
        name: string;
    };
    queue_stats?: QueueStats;
}

interface Organization {
    name: string;
    tagline?: string;
    logo_path?: string;
}

interface CounterProps {
    user: User;
    organization?: Organization;
    logoUrl: string;
    apiBase?: string;
}

// Toast Implementation (kept inline for simplicity as requested)
const showToast = (title: string, message: string, variant: 'info' | 'success' | 'warning' | 'danger' = 'info') => {
    const host = document.getElementById('toast-host') || (() => {
        const el = document.createElement('div');
        el.id = 'toast-host';
        el.className = "fixed bottom-6 right-6 z-[9999] space-y-3 pointer-events-none";
        document.body.appendChild(el);
        return el;
    })();

    const el = document.createElement("div");
    const colors = {
        info: "bg-blue-600",
        success: "bg-emerald-600",
        warning: "bg-amber-600",
        danger: "bg-rose-600",
    };

    el.className = cn(
        "pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-white min-w-[300px]",
        colors[variant],
        "animate-[toastIn_0.3s_ease-out]"
    );

    el.innerHTML = `
        <div class="flex-1">
            <p class="font-bold text-sm">${title}</p>
            <p class="text-xs opacity-90">${message}</p>
        </div>
    `;

    host.appendChild(el);
    setTimeout(() => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(10px)';
        el.style.transition = 'all 0.3s';
        setTimeout(() => el.remove(), 300);
    }, 3000);
};

export default function Counter({ user, organization, logoUrl, apiBase = "/counter-api" }: CounterProps) {
    const [lokets, setLokets] = useState<Loket[]>([]);
    const [selectedLoket, setSelectedLoket] = useState<Loket | null>(null);
    const [currentTicket, setCurrentTicket] = useState<string>("-");
    const [isLoadingLokets, setIsLoadingLokets] = useState(true);
    const [isActionRunning, setIsActionRunning] = useState(false);

    useEffect(() => {
        loadLokets();
        const style = document.createElement("style");
        style.innerHTML = `@keyframes toastIn { from { opacity:0; transform: translateY(20px); } to { opacity:1; transform: translateY(0); } }`;
        document.head.appendChild(style);
    }, []);

    useEffect(() => {
        let interval: ReturnType<typeof setInterval>;
        if (selectedLoket) {
            refreshCurrent(selectedLoket.code);
            interval = setInterval(() => {
                refreshCurrent(selectedLoket.code);
                // Also refresh stats implicitly by reloading lokets periodically? 
                // For now, let's keep stats static until page reload or maybe refresh them slightly less often?
                // Actually, let's refresh lokets too to get updated stats.
                loadLokets(true);
            }, 5000);
        }
        return () => clearInterval(interval);
    }, [selectedLoket]);

    const loadLokets = async (silent = false) => {
        if (!silent) setIsLoadingLokets(true);
        const res = await apiFetch(`${apiBase}/lokets`);

        if (!res.ok || !res.success) {
            if (!silent) setIsLoadingLokets(false);
            return;
        }

        const data: Loket[] = Array.isArray(res.data) ? res.data : [];
        setLokets(data);
        if (!silent) setIsLoadingLokets(false);

        // Update selected loket stats if selected
        if (selectedLoket) {
            const updated = data.find(l => l.id === selectedLoket.id);
            if (updated) setSelectedLoket(updated);
        } else {
            // Restore selection or pick first active
            const savedCode = localStorage.getItem("op_loket_code");
            if (savedCode) {
                const found = data.find(l => l.code === savedCode && l.is_active);
                if (found) setSelectedLoket(found);
            }
        }
    };

    const refreshCurrent = async (code: string) => {
        const res = await apiFetch(`${apiBase}/lokets/${code}/current`);
        if (res.success && res.data) {
            setCurrentTicket(res.data.ticket_no ?? "-");
        }
    };

    const handleSelectLoket = (loket: Loket) => {
        if (!loket.is_active) {
            showToast("Loket Nonaktif", "Loket ini sedang tidak aktif.", "warning");
            return;
        }
        setSelectedLoket(loket);
        localStorage.setItem("op_loket_code", loket.code);
        setCurrentTicket("-");
        refreshCurrent(loket.code);
        showToast("Loket Dipilih", `Bertugas di ${loket.name}`, "success");
    };


    const handleAction = async (action: 'call-next' | 'recall' | 'skip' | 'serve') => {
        if (isActionRunning || !selectedLoket) return;

        setIsActionRunning(true);
        const actionMap = {
            'call-next': { url: 'call-next', msg: 'Memanggil antrian berikutnya...' },
            'recall': { url: 'recall', msg: 'Memanggil ulang antrian...' },
            'skip': { url: 'skip', msg: 'Antrian dilewati (No-Show).' },
            'serve': { url: 'serve', msg: 'Layanan selesai.' },
        };

        const target = actionMap[action];

        // Optimistic UI updates could happen here, but for now we wait for server
        const res = await apiFetch(`${apiBase}/lokets/${selectedLoket.code}/${target.url}`, {
            method: 'POST'
        });

        if (!res.ok || !res.success) {
            let errorMsg = "Gagal memproses permintaan.";
            if (res.message) errorMsg = res.message;
            if (res.data?.ticket && action === 'recall') errorMsg = res.data.ticket[0];

            showToast("Gagal", errorMsg, "danger");
        } else {
            const ticketNo = res.data?.ticket_no;
            const msg = ticketNo ? `${target.msg} (${ticketNo})` : target.msg;
            showToast("Berhasil", msg, "success");
            setCurrentTicket(ticketNo || "-");
            loadLokets(true); // Refresh stats

            if ((action === 'call-next' || action === 'recall') && ticketNo) {
                announce(ticketNo, selectedLoket.name);
            }
        }

        setIsActionRunning(false);
    };

    const stats = selectedLoket?.queue_stats;

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100 font-sans selection:bg-blue-500/30 overflow-hidden relative flex flex-col">
            {/* Ambient Background */}
            <div className="fixed inset-0 z-0 pointer-events-none">
                <div className="absolute top-[-20%] left-[-10%] w-[40%] h-[40%] rounded-full bg-blue-600/10 blur-[150px]"></div>
                <div className="absolute bottom-[-20%] right-[-10%] w-[40%] h-[40%] rounded-full bg-indigo-600/10 blur-[150px]"></div>
            </div>

            {/* Header */}
            <header className="relative z-10 border-b border-white/5 bg-slate-900/50 backdrop-blur-xl h-20">
                <div className="max-w-[1920px] mx-auto px-6 h-full flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <img src={logoUrl} alt="Logo" className="h-10 w-auto" />
                        <div>
                            <h1 className="text-lg font-bold text-white leading-tight tracking-tight">
                                {organization?.name ?? "PERUMDAM Tirta Perwira"}
                            </h1>
                            <div className="flex items-center gap-2">
                                <span className="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                    OPERATOR
                                </span>
                                {organization?.tagline && (
                                    <span className="text-xs text-slate-400 border-l border-white/10 pl-2">
                                        {organization.tagline}
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center gap-4">
                        <a
                            href="/display"
                            target="_blank"
                            className="hidden md:flex items-center gap-2 px-4 py-2 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 text-sm font-medium transition-colors"
                        >
                            <Monitor className="w-4 h-4" />
                            <span>Live TV Display</span>
                        </a>

                        <div className="h-8 w-px bg-white/10 mx-2 hidden md:block"></div>

                        <div className="flex items-center gap-3">
                            <div className="text-right hidden sm:block">
                                <p className="text-sm font-bold text-white">{user.name}</p>
                                <p className="text-xs text-slate-400">{user.email}</p>
                            </div>
                            <div className="h-10 w-10 rounded-full bg-slate-800 border border-white/10 flex items-center justify-center">
                                <UserIcon className="w-5 h-5 text-slate-400" />
                            </div>
                            <form method="POST" action="/counter/logout">
                                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''} />
                                <button
                                    type="submit"
                                    className="p-2 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-colors"
                                    title="Logout"
                                >
                                    <LogOut className="w-5 h-5" />
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {/* Main Content */}
            <main className="flex-1 relative z-10 p-6 lg:p-8 flex flex-col xl:flex-row gap-6 lg:gap-8 max-w-[1920px] mx-auto w-full">

                {/* Left Side: Controls */}
                <div className="flex-1 flex flex-col gap-6">

                    {/* Main Display Card */}
                    <div className="flex-1 min-h-[400px] relative rounded-3xl overflow-hidden border border-white/10 bg-slate-900/40 backdrop-blur-sm shadow-2xl flex flex-col items-center justify-center p-8 group">
                        {/* Glow Effect */}
                        <div className="absolute inset-0 bg-gradient-to-b from-blue-500/5 to-transparent opacity-50"></div>

                        {selectedLoket ? (
                            <>
                                <motion.div
                                    initial={{ opacity: 0, y: 20 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    className="relative z-10 flex flex-col items-center"
                                >
                                    <span className="text-blue-400 font-bold tracking-[0.2em] text-sm uppercase mb-4 animate-pulse">
                                        Sedang Melayani
                                    </span>

                                    <AnimatePresence mode="wait">
                                        <motion.h2
                                            key={currentTicket}
                                            initial={{ scale: 0.9, opacity: 0 }}
                                            animate={{ scale: 1, opacity: 1 }}
                                            exit={{ scale: 1.1, opacity: 0 }}
                                            transition={{ type: "spring", stiffness: 200, damping: 20 }}
                                            className="text-[8rem] lg:text-[12rem] leading-none font-bold text-white tracking-tighter drop-shadow-[0_0_40px_rgba(59,130,246,0.5)] font-mono"
                                        >
                                            {currentTicket}
                                        </motion.h2>
                                    </AnimatePresence>

                                    <div className="mt-8 px-6 py-2 rounded-full bg-white/5 border border-white/10 flex items-center gap-3">
                                        <div className="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)] animate-pulse"></div>
                                        <span className="text-lg font-medium text-slate-200">
                                            {selectedLoket.name} <span className="text-slate-500 mx-1">•</span> {selectedLoket.service?.name}
                                        </span>
                                    </div>
                                </motion.div>
                            </>
                        ) : (
                            <div className="relative z-10 flex flex-col items-center text-slate-500">
                                <Monitor className="w-16 h-16 mb-4 opacity-50" />
                                <p className="text-xl font-medium">Pilih loket untuk memulai</p>
                            </div>
                        )}
                    </div>

                    {/* Stats Row */}
                    {selectedLoket && stats && (
                        <div className="grid grid-cols-3 gap-4">
                            <div className="rounded-2xl bg-slate-800/50 border border-white/5 p-4 flex items-center gap-4">
                                <div className="p-3 rounded-xl bg-blue-500/10 text-blue-400">
                                    <Users className="w-6 h-6" />
                                </div>
                                <div>
                                    <p className="text-xs text-slate-400 font-medium uppercase tracking-wider">Total Antrian</p>
                                    <p className="text-2xl font-bold text-white">{stats.total}</p>
                                </div>
                            </div>
                            <div className="rounded-2xl bg-slate-800/50 border border-white/5 p-4 flex items-center gap-4">
                                <div className="p-3 rounded-xl bg-amber-500/10 text-amber-400">
                                    <Activity className="w-6 h-6" />
                                </div>
                                <div>
                                    <p className="text-xs text-slate-400 font-medium uppercase tracking-wider">Sisa Antrian</p>
                                    <p className="text-2xl font-bold text-white">{stats.pending}</p>
                                </div>
                            </div>
                            <div className="rounded-2xl bg-slate-800/50 border border-white/5 p-4 flex items-center gap-4">
                                <div className="p-3 rounded-xl bg-emerald-500/10 text-emerald-400">
                                    <Clock className="w-6 h-6" />
                                </div>
                                <div>
                                    <p className="text-xs text-slate-400 font-medium uppercase tracking-wider">Rata-rata Waktu</p>
                                    <p className="text-2xl font-bold text-white">{stats.avg_service_time}<span className="text-sm font-normal text-slate-500 ml-1">menit</span></p>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Action Grid */}
                    <div className={cn(
                        "grid grid-cols-2 lg:grid-cols-4 gap-4 transition-all duration-300",
                        !selectedLoket && "opacity-50 pointer-events-none filter grayscale"
                    )}>
                        <button
                            onClick={() => handleAction('call-next')}
                            disabled={isActionRunning}
                            className="group col-span-2 lg:col-span-1 h-32 relative overflow-hidden rounded-2xl bg-emerald-600 hover:bg-emerald-500 transition-all active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed shadow-lg shadow-emerald-900/20"
                        >
                            <div className="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div className="relative h-full p-6 flex flex-col justify-between items-start">
                                <Mic className="w-8 h-8 text-white" />
                                <div>
                                    <p className="text-emerald-100 text-xs font-bold uppercase tracking-wider mb-1">Prioritas</p>
                                    <p className="text-white text-2xl font-bold">Panggil</p>
                                </div>
                            </div>
                        </button>

                        <button
                            onClick={() => handleAction('recall')}
                            disabled={isActionRunning}
                            className="group h-32 relative overflow-hidden rounded-2xl bg-slate-800 border border-slate-700 hover:bg-slate-700 hover:border-slate-600 transition-all active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed"
                        >
                            <div className="relative h-full p-6 flex flex-col justify-between items-start">
                                <RefreshCcw className="w-8 h-8 text-indigo-400 group-hover:rotate-180 transition-transform duration-500" />
                                <div>
                                    <p className="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Ulangi</p>
                                    <p className="text-white text-xl font-bold">Recall</p>
                                </div>
                            </div>
                        </button>

                        <button
                            onClick={() => handleAction('skip')}
                            disabled={isActionRunning}
                            className="group h-32 relative overflow-hidden rounded-2xl bg-amber-500/10 border border-amber-500/20 hover:bg-amber-500/20 hover:border-amber-500/30 transition-all active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed"
                        >
                            <div className="relative h-full p-6 flex flex-col justify-between items-start">
                                <SkipForward className="w-8 h-8 text-amber-500 group-hover:translate-x-1 transition-transform" />
                                <div>
                                    <p className="text-amber-500/70 text-xs font-bold uppercase tracking-wider mb-1">No-Show</p>
                                    <p className="text-amber-500 text-xl font-bold">Skip</p>
                                </div>
                            </div>
                        </button>

                        <button
                            onClick={() => handleAction('serve')}
                            disabled={isActionRunning}
                            className="group h-32 relative overflow-hidden rounded-2xl bg-blue-600 hover:bg-blue-500 transition-all active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed shadow-lg shadow-blue-900/20"
                        >
                            <div className="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div className="relative h-full p-6 flex flex-col justify-between items-start">
                                <CheckCircle className="w-8 h-8 text-white" />
                                <div>
                                    <p className="text-blue-100 text-xs font-bold uppercase tracking-wider mb-1">Selesai</p>
                                    <p className="text-white text-2xl font-bold">Finish</p>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                {/* Right Side: Loket List */}
                <div className="xl:w-80 flex flex-col gap-4 h-full">
                    <div className="p-4 rounded-t-2xl bg-slate-800/50 border border-white/5 border-b-0 backdrop-blur-sm">
                        <h3 className="text-lg font-bold text-white flex items-center gap-2">
                            <Monitor className="w-5 h-5 text-indigo-400" />
                            Daftar Loket
                        </h3>
                    </div>

                    <div className="flex-1 rounded-b-2xl bg-slate-900/40 border border-white/5 overflow-hidden flex flex-col">
                        <div className="overflow-y-auto p-2 space-y-2 flex-1">
                            {isLoadingLokets ? (
                                <div className="text-center py-8 text-slate-500 text-sm">Memuat...</div>
                            ) : lokets.map(loket => (
                                <button
                                    key={loket.id}
                                    onClick={() => handleSelectLoket(loket)}
                                    disabled={!loket.is_active}
                                    className={cn(
                                        "w-full text-left p-3 rounded-xl border transition-all relative overflow-hidden group",
                                        selectedLoket?.id === loket.id
                                            ? "bg-blue-600/10 border-blue-500/50"
                                            : "bg-white/5 border-transparent hover:bg-white/10",
                                        !loket.is_active && "opacity-50 grayscale cursor-not-allowed"
                                    )}
                                >
                                    <div className="flex items-center justify-between mb-1">
                                        <span className={cn(
                                            "font-bold text-sm",
                                            selectedLoket?.id === loket.id ? "text-blue-400" : "text-white"
                                        )}>
                                            {loket.name}
                                        </span>
                                        <div className={cn(
                                            "w-2 h-2 rounded-full",
                                            loket.is_active ? "bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]" : "bg-red-500"
                                        )}></div>
                                    </div>
                                    <p className="text-xs text-slate-400 font-medium">
                                        {loket.service?.name}
                                    </p>
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Footer Alert */}
                    <div className="mt-auto rounded-xl bg-slate-800/80 border border-indigo-500/20 p-4 flex gap-3 items-start">
                        <div className="p-2 bg-indigo-500/20 rounded-lg text-indigo-400">
                            <Monitor className="w-4 h-4" />
                        </div>
                        <div className="text-xs text-slate-400 leading-relaxed">
                            <strong className="text-indigo-300 block mb-0.5">Note:</strong>
                            Pastikan browser tetap terbuka dan tidak diminimize agar notifikasi suara tetap terdengar.
                        </div>
                    </div>
                </div>
            </main>
        </div>
    );
}
