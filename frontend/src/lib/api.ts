import type {
  AuthPayload,
  CannedReply,
  Client,
  DashboardPayload,
  PortalNotification,
  WorkRequest,
} from "@/lib/portal-types";

const API_BASE =
  process.env.NEXT_PUBLIC_API_URL?.replace(/\/$/, "") ??
  "http://127.0.0.1:8000/api";

type RequestOptions = {
  token?: string;
  method?: string;
  body?: BodyInit | null;
  json?: unknown;
  headers?: HeadersInit;
};

async function request<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const headers = new Headers(options.headers);

  if (options.token) {
    headers.set("Authorization", `Bearer ${options.token}`);
  }

  let body = options.body ?? null;

  if (options.json !== undefined) {
    headers.set("Content-Type", "application/json");
    body = JSON.stringify(options.json);
  }

  const response = await fetch(`${API_BASE}${path}`, {
    method: options.method ?? (options.json !== undefined ? "POST" : "GET"),
    headers,
    body,
  });

  if (!response.ok) {
    const text = await response.text();

    try {
      const parsed = JSON.parse(text) as { message?: string; errors?: Record<string, string[]> };
      const errorDetails = parsed.errors
        ? Object.values(parsed.errors).flat().join(" ")
        : parsed.message;
      throw new Error(errorDetails || "Request failed.");
    } catch {
      throw new Error(text || "Request failed.");
    }
  }

  if (response.status === 204) {
    return undefined as T;
  }

  return (await response.json()) as T;
}

export async function login(email: string, password: string): Promise<AuthPayload> {
  return request<AuthPayload>("/login", {
    json: { email, password },
  });
}

export async function getMe(token: string): Promise<Omit<AuthPayload, "token">> {
  return request<Omit<AuthPayload, "token">>("/me", { token });
}

export async function logout(token: string): Promise<void> {
  await request<void>("/logout", {
    token,
    method: "POST",
  });
}

export async function getDashboard(token: string): Promise<DashboardPayload> {
  return request<DashboardPayload>("/dashboard", { token });
}

export async function getRequests(
  token: string,
  filters?: { status?: string; search?: string; priority?: string },
): Promise<WorkRequest[]> {
  const params = new URLSearchParams();

  if (filters?.status) params.set("status", filters.status);
  if (filters?.search) params.set("search", filters.search);
  if (filters?.priority) params.set("priority", filters.priority);

  const query = params.toString() ? `?${params.toString()}` : "";
  const payload = await request<{ data: WorkRequest[] }>(`/requests${query}`, { token });

  return payload.data;
}

export async function getRequest(token: string, requestId: number): Promise<WorkRequest> {
  const payload = await request<{ data: WorkRequest }>(`/requests/${requestId}`, { token });

  return payload.data;
}

export async function createRequest(
  token: string,
  payload: Record<string, unknown>,
): Promise<WorkRequest> {
  const response = await request<{ data: WorkRequest }>("/requests", {
    token,
    json: payload,
  });

  return response.data;
}

export async function updateRequest(
  token: string,
  requestId: number,
  payload: Record<string, unknown>,
): Promise<WorkRequest> {
  const response = await request<{ data: WorkRequest }>(`/requests/${requestId}`, {
    token,
    method: "PATCH",
    json: payload,
  });

  return response.data;
}

export async function createComment(
  token: string,
  requestId: number,
  payload: Record<string, unknown>,
): Promise<void> {
  await request(`/requests/${requestId}/comments`, {
    token,
    json: payload,
  });
}

export async function uploadRequestFile(
  token: string,
  requestId: number,
  file: File,
): Promise<void> {
  const formData = new FormData();
  formData.append("attachment", file);

  await request(`/requests/${requestId}/files`, {
    token,
    method: "POST",
    body: formData,
  });
}

export async function downloadProtectedFile(token: string, url: string): Promise<void> {
  const response = await fetch(url, {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });

  if (!response.ok) {
    throw new Error("Could not download the selected file.");
  }

  const blob = await response.blob();
  const contentDisposition = response.headers.get("Content-Disposition");
  const fileNameMatch = contentDisposition?.match(/filename="?([^"]+)"?/);
  const fileName = fileNameMatch?.[1] ?? "download";
  const objectUrl = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = objectUrl;
  link.download = fileName;
  link.click();
  URL.revokeObjectURL(objectUrl);
}

export async function getClients(token: string): Promise<Client[]> {
  const payload = await request<{ data: Client[] }>("/clients", { token });

  return payload.data;
}

export async function createClient(
  token: string,
  payload: Record<string, unknown>,
): Promise<Client> {
  const response = await request<{ data: Client }>("/clients", {
    token,
    json: payload,
  });

  return response.data;
}

export async function getCannedReplies(token: string): Promise<CannedReply[]> {
  const payload = await request<{ data: CannedReply[] }>("/canned-replies", { token });

  return payload.data;
}

export async function sendReminder(
  token: string,
  requestId: number,
  message: string,
): Promise<void> {
  await request(`/requests/${requestId}/reminders`, {
    token,
    json: { message },
  });
}

export async function markNotificationsRead(token: string): Promise<void> {
  await request("/notifications/read", {
    token,
    method: "POST",
  });
}

export async function getNotifications(token: string): Promise<PortalNotification[]> {
  const payload = await request<{ data: PortalNotification[] }>("/notifications", { token });

  return payload.data;
}
