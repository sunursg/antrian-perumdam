/**
 * Audio Utility for Queue Announcements
 */

/**
 * Plays a synthetic bell/chime sound using Web Audio API
 */
export async function playBell() {
    return new Promise<void>((resolve) => {
        try {
            const AudioContextClass = window.AudioContext || (window as any).webkitAudioContext;
            const context = new AudioContextClass();

            const playChime = (time: number, freq: number, volume: number, duration: number) => {
                const osc = context.createOscillator();
                const gain = context.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, time);

                gain.gain.setValueAtTime(0, time);
                gain.gain.linearRampToValueAtTime(volume, time + 0.01);
                gain.gain.exponentialRampToValueAtTime(0.001, time + duration);

                osc.connect(gain);
                gain.connect(context.destination);

                osc.start(time);
                osc.stop(time + duration);
            };

            const now = context.currentTime;
            // A simple "Ding-Dong" or dual-tone chime
            playChime(now, 880, 0.4, 1.5); // A5
            playChime(now + 0.1, 1100, 0.3, 1.2); // C6

            setTimeout(() => {
                context.close();
                resolve();
            }, 2000);
        } catch (e) {
            console.error("Failed to play bell:", e);
            resolve();
        }
    });
}

/**
 * Converts a number to Indonesian words
 * Supports up to 999 for queue numbers
 */
function numberToIndonesian(num: number): string {
    const units = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

    if (num < 12) {
        return units[num];
    } else if (num < 20) {
        return units[num - 10] + ' belas';
    } else if (num < 100) {
        const ten = Math.floor(num / 10);
        const unit = num % 10;
        return units[ten] + ' puluh ' + (unit > 0 ? units[unit] : '');
    } else if (num < 200) {
        return 'seratus ' + (num > 100 ? numberToIndonesian(num - 100) : '');
    } else if (num < 1000) {
        const hundred = Math.floor(num / 100);
        const rest = num % 100;
        return units[hundred] + ' ratus ' + (rest > 0 ? numberToIndonesian(rest) : '');
    } else {
        // Fallback for larger numbers (rare for daily queues)
        return num.toString();
    }
}

/**
 * Formats a ticket number for natural speech
 * e.g., "A-001" -> "A, satu"
 * e.g., "B-012" -> "B, dua belas"
 */
export function formatTicketForSpeech(ticketNo: string): string {
    const parts = ticketNo.split('-');
    const prefix = parts[0] || '';
    const numberPart = parts[1] || '';

    // Parse the numeric part (removes leading zeros automatically)
    const numberVal = parseInt(numberPart, 10);

    // Spell out prefix letters
    const spelledPrefix = prefix.split('').join(', ');

    // Get natural spoken number
    const spelledNumber = !isNaN(numberVal) ? numberToIndonesian(numberVal) : numberPart;

    return `${spelledPrefix}, ${spelledNumber}`;
}

/**
 * Full announcement sequence: Bell -> Speech
 */
export async function announce(ticketNo: string, loketName: string) {
    // 1. Play Bell
    await playBell();

    // 2. Small delay
    await new Promise(r => setTimeout(r, 500));

    // 3. Speech
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel();

        const spelledNo = formatTicketForSpeech(ticketNo);
        const text = `Nomor Antrian, ${spelledNo}, Silakan menuju ${loketName}`;

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'id-ID';
        utterance.rate = 0.85; // Slightly slower for clarity
        utterance.pitch = 1.0; // Normal pitch

        // Try to find an Indonesian female voice if available
        const voices = window.speechSynthesis.getVoices();
        const idVoice = voices.find(v => v.lang.includes('id') && v.name.toLowerCase().includes('female'))
            || voices.find(v => v.lang.includes('id'))
            || voices.find(v => v.name.toLowerCase().includes('female'));

        if (idVoice) utterance.voice = idVoice;

        window.speechSynthesis.speak(utterance);
    }
}
