export interface ApiResponse<T = any> {
    ok: boolean;
    status: number;
    success?: boolean;
    message?: string;
    data?: T;
    [key: string]: any;
}

export async function apiFetch<T = any>(
    url: string, 
    opts: RequestInit = {}
): Promise<ApiResponse<T>> {
    const tokenEl = document.querySelector('meta[name="csrf-token"]');
    const token = tokenEl ? tokenEl.getAttribute('content') : '';

    const headers: HeadersInit = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        ...(token ? { 'X-CSRF-TOKEN': token } : {}),
        ...(opts.headers || {}),
    };

    try {
        const res = await fetch(url, {
            credentials: 'same-origin',
            ...opts,
            headers,
        });

        // Handle empty responses or non-JSON responses gracefully
        const text = await res.text();
        let body: any = null;
        try {
            body = text ? JSON.parse(text) : null;
        } catch (e) {
            console.error('Failed to parse JSON response:', text);
        }

        if (body && typeof body === 'object') {
            return { ok: res.ok, status: res.status, ...body };
        }

        return { ok: res.ok, status: res.status, message: body };
    } catch (error) {
        console.error('API Fetch Error:', error);
        return { 
            ok: false, 
            status: 0, 
            message: error instanceof Error ? error.message : 'Network error' 
        };
    }
}
