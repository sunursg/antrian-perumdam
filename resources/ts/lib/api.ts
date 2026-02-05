import type { DisplayState, PublicStatus, TicketResponse } from "./types";

type TicketApiPayload = {
  ticket_no: string;
  service?: string | { name?: string; code?: string };
  counter_hint?: string | null;
};

const USE_STUB = import.meta.env.MODE !== "production";

const getCsrfToken = () => {
  const token = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content");
  return token ?? "";
};

const safeJson = async <T,>(response: Response): Promise<T | null> => {
  try {
    return (await response.json()) as T;
  } catch {
    return null;
  }
};

const request = async <T,>(
  url: string,
  options: RequestInit = {}
): Promise<{ ok: boolean; data: T | null; status: number }> => {
  const response = await fetch(url, {
    credentials: "same-origin",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...(options.headers ?? {}),
    },
    ...options,
  });

  const data = await safeJson<T>(response);
  return { ok: response.ok, data, status: response.status };
};

const unwrapData = <T,>(payload: T | null): T | null => {
  if (!payload) return null;
  if (typeof payload === "object" && payload !== null && "data" in payload) {
    const nested = (payload as { data?: T }).data;
    return nested ?? null;
  }
  return payload;
};

const mockDisplayState = (): DisplayState => ({
  company: {
    name: "PERUMDAM TIRTA PERWIRA",
    slogan: "Melayani dengan Sepenuh Hati",
    logo_url: "/logo.png",
  },
  now_serving: {
    counter: "Loket Pembayaran 1",
    ticket_no: "A003",
    service: "Pembayaran Rekening Air",
  },
  next_queue: [
    { ticket_no: "A004", service: "Pembayaran Rekening Air" },
    { ticket_no: "A005", service: "Pembayaran Rekening Air" },
    { ticket_no: "B001", service: "Pelayanan Pelanggan" },
    { ticket_no: "A006", service: "Pembayaran Rekening Air" },
    { ticket_no: "B002", service: "Pelayanan Pelanggan" },
  ],
  counters: [
    { name: "Loket Pembayaran 1", active: true },
    { name: "Loket Pembayaran 2", active: false },
    { name: "Loket Pelanggan 1", active: true },
  ],
  announcements: [
    {
      title: "Selamat datang di layanan publik kami",
      active: true,
      video_url: "",
    },
  ],
});

const mockPublicStatus = (): PublicStatus => ({
  services: [
    {
      id: 1,
      code: "BAY",
      name: "Pembayaran Rekening Air",
      description: "Bayar tagihan bulanan air PDAM",
      theme: "blue",
      current_ticket: "A003",
      waiting: 8,
      estimated_minutes: 35,
    },
    {
      id: 2,
      code: "CS",
      name: "Pelayanan Pelanggan",
      description: "Konsultasi, pengaduan, dan layanan umum",
      theme: "green",
      current_ticket: "-",
      waiting: 0,
      estimated_minutes: 0,
    },
    {
      id: 3,
      code: "INF",
      name: "Informasi",
      description: "Informasi prosedur dan pertanyaan umum",
      theme: "amber",
      current_ticket: "-",
      waiting: 0,
      estimated_minutes: 0,
    },
  ],
});

let mockTicketSeed = 4;
const mockTicket = (service: string): TicketResponse => {
  const next = `A${String(mockTicketSeed).padStart(3, "0")}`;
  mockTicketSeed += 1;
  return {
    ticket_no: next,
    service,
    counter_hint: service.includes("Pelanggan")
      ? "Loket Pelanggan 1"
      : "Loket Pembayaran 1",
  };
};

export const getDisplayState = async (): Promise<DisplayState | null> => {
  try {
    const res = await request<DisplayState | { data: DisplayState } | any>(
      "/api/public/display/state"
    );
    if (res.ok && res.data) {
      const payload = unwrapData(res.data);
      if (payload?.company) return payload as DisplayState;
      if (payload?.organization) {
        return {
          company: {
            name: payload.organization.name,
            slogan: payload.organization.tagline,
            logo_url: payload.organization.logo_path
              ? `/storage/${payload.organization.logo_path}`
              : "/logo.png",
          },
          now_serving: null,
          next_queue: [],
          counters: (payload.counters ?? []).map((counter: any) => ({
            name: counter?.loket?.name ?? counter?.name ?? "-",
            active: !!counter?.is_active,
          })),
          announcements: (payload.announcements ?? []).map((item: any) => ({
            title: item?.title ?? "",
            active: true,
            video_url: item?.video_url ?? item?.media_path ?? null,
          })),
        };
      }
    }
  } catch {
    // handled below
  }
  return USE_STUB ? mockDisplayState() : null;
};

export const getPublicStatus = async (): Promise<PublicStatus | null> => {
  try {
    const res = await request<PublicStatus | { data: PublicStatus } | any>(
      "/api/public/status"
    );
    if (res.ok && res.data) {
      const payload = unwrapData(res.data);
      if (payload?.services) return payload as PublicStatus;
    }
  } catch {
    // handled below
  }
  return USE_STUB ? mockPublicStatus() : null;
};

export const createTicket = async (
  serviceCode: string,
  serviceName: string
): Promise<TicketResponse> => {
  try {
    const res = await request<TicketApiPayload | { data: TicketApiPayload }>(
      "/api/public/tickets",
      {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": getCsrfToken(),
        },
        body: JSON.stringify({
          service_code: serviceCode,
          source: "kiosk",
          confirm_service: true,
        }),
      }
    );
    if (res.ok && res.data) {
      const payload = unwrapData(res.data) as TicketApiPayload | null;
      if (payload) {
        const serviceValue =
          typeof payload.service === "string"
            ? payload.service
            : payload.service?.name ?? payload.service?.code ?? serviceName;

        return {
          ticket_no: payload.ticket_no,
          service: serviceValue,
          counter_hint: payload.counter_hint ?? null,
        };
      }
    }
    throw new Error(`Gagal mengambil tiket (${res.status})`);
  } catch (err) {
    if (USE_STUB) return mockTicket(serviceName);
    throw err instanceof Error ? err : new Error("Gagal mengambil tiket");
  }
};
