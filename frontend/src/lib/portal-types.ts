export type UserRole = "staff" | "client";
export type RequestStatus =
  | "new"
  | "waiting_on_staff"
  | "waiting_on_client"
  | "in_progress"
  | "completed"
  | "archived";
export type RequestPriority = "low" | "normal" | "high" | "urgent";

export interface PortalUser {
  id: number;
  name: string;
  email: string;
  title: string | null;
  role: UserRole;
  role_label: string;
  firm_id: number | null;
  client_id: number | null;
  last_login_at: string | null;
}

export interface Firm {
  id: number;
  name: string;
  slug: string;
  niche: string | null;
  portal_tagline: string | null;
  primary_color: string;
}

export interface Client {
  id: number;
  firm_id: number;
  company_name: string;
  primary_contact_name: string;
  email: string;
  phone: string | null;
  notes: string | null;
  is_active: boolean;
  portal_user: PortalUser | null;
  open_requests_count?: number | null;
  total_requests_count?: number | null;
  created_at?: string | null;
}

export interface RequestComment {
  id: number;
  body: string;
  is_internal: boolean;
  created_at: string | null;
  author: PortalUser | null;
}

export interface RequestFile {
  id: number;
  original_name: string;
  mime_type: string | null;
  size_bytes: number;
  download_url: string;
  created_at: string | null;
  uploaded_by: PortalUser | null;
}

export interface ActivityLog {
  id: number;
  type: string;
  description: string;
  metadata: Record<string, unknown>;
  created_at: string | null;
  actor: PortalUser | null;
}

export interface WorkRequest {
  id: number;
  firm_id: number;
  client_id: number;
  title: string;
  request_type: string;
  summary: string;
  status: RequestStatus;
  status_label: string;
  priority: RequestPriority;
  priority_label: string;
  due_at: string | null;
  last_reminded_at: string | null;
  completed_at: string | null;
  created_at: string | null;
  updated_at: string | null;
  client: Client | null;
  submitted_by: PortalUser | null;
  assigned_to: PortalUser | null;
  comments_count?: number | null;
  files_count?: number | null;
  comments: RequestComment[];
  files: RequestFile[];
  activities: ActivityLog[];
}

export interface PortalNotification {
  id: string;
  type: string;
  title: string;
  message: string;
  action_url: string | null;
  severity: string;
  read_at: string | null;
  created_at: string | null;
}

export interface CannedReply {
  id: number;
  title: string;
  category: string;
  target_status: string | null;
  content: string;
}

export interface DashboardPayload {
  firm: Firm;
  user: PortalUser;
  stats: Record<string, number>;
  requests_by_status: Record<RequestStatus, number>;
  recent_requests: WorkRequest[];
  recent_activity: ActivityLog[];
  notifications: PortalNotification[];
  unread_notifications_count: number;
}

export interface AuthPayload {
  token: string;
  user: PortalUser;
  firm: Firm;
  client: Client | null;
}
