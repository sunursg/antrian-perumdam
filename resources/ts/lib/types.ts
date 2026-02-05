export type DisplayState = {
  company: {
    name: string;
    slogan?: string;
    logo_url?: string | null;
  };
  now_serving?: {
    counter: string;
    ticket_no: string;
    service: string;
  } | null;
  next_queue?: Array<{
    ticket_no: string;
    service: string;
  }>;
  counters?: Array<{
    name: string;
    active: boolean;
  }>;
  announcements: Array<{
    title: string;
    active: boolean;
    video_url?: string | null;
  }>;
};

export type ServiceStatus = {
  id: number;
  code: string;
  name: string;
  description: string;
  theme?: "blue" | "green" | string;
  current_ticket: string;
  waiting: number;
  estimated_minutes: number;
};

export type PublicStatus = {
  services: ServiceStatus[];
};

export type TicketResponse = {
  ticket_no: string;
  service: string;
  counter_hint?: string | null;
};
