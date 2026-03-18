"use client";

import { useCallback, useEffect, useState, type FormEvent } from "react";
import {
  createClient,
  createComment,
  createRequest,
  getCannedReplies,
  getDashboard,
  getMe,
  getRequest,
  getRequests,
  getClients,
  login,
  logout,
  markNotificationsRead,
  sendReminder,
  updateRequest,
  uploadRequestFile,
} from "@/lib/api";
import { stripForInput } from "@/lib/format";
import type {
  AuthPayload,
  CannedReply,
  Client,
  DashboardPayload,
  PortalNotification,
  WorkRequest,
} from "@/lib/portal-types";
import { PortalBoard } from "@/components/portal/portal-board";
import { PortalDetail } from "@/components/portal/portal-detail";
import { PortalLogin } from "@/components/portal/portal-login";
import { PortalSidebar } from "@/components/portal/portal-sidebar";

const TOKEN_KEY = "clientlane.portal.token";

const requestStatuses = [
  "new",
  "waiting_on_staff",
  "waiting_on_client",
  "in_progress",
  "completed",
  "archived",
] as const;

const requestPriorities = ["low", "normal", "high", "urgent"] as const;

export function PortalApp() {
  const [booting, setBooting] = useState(true);
  const [session, setSession] = useState<AuthPayload | null>(null);
  const [dashboard, setDashboard] = useState<DashboardPayload | null>(null);
  const [requests, setRequests] = useState<WorkRequest[]>([]);
  const [selectedRequestId, setSelectedRequestId] = useState<number | null>(null);
  const [selectedRequest, setSelectedRequest] = useState<WorkRequest | null>(null);
  const [clients, setClients] = useState<Client[]>([]);
  const [cannedReplies, setCannedReplies] = useState<CannedReply[]>([]);
  const [notifications, setNotifications] = useState<PortalNotification[]>([]);
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("");
  const [loadingBoard, setLoadingBoard] = useState(false);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [flash, setFlash] = useState<string | null>(null);

  const [loginForm, setLoginForm] = useState({ email: "", password: "" });
  const [requestForm, setRequestForm] = useState({
    client_id: "",
    title: "",
    request_type: "",
    summary: "",
    priority: "normal",
    due_at: "",
  });
  const [commentBody, setCommentBody] = useState("");
  const [commentInternal, setCommentInternal] = useState(false);
  const [reminderMessage, setReminderMessage] = useState("");
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [requestUpdate, setRequestUpdate] = useState({ status: "", due_at: "" });
  const [clientForm, setClientForm] = useState({
    company_name: "",
    primary_contact_name: "",
    email: "",
    notes: "",
    create_portal_user: true,
    password: "password",
  });

  async function restoreSession(token: string) {
    try {
      const profile = await getMe(token);
      setSession({ token, ...profile });
    } catch (caught) {
      window.localStorage.removeItem(TOKEN_KEY);
      setError(caught instanceof Error ? caught.message : "Could not restore the saved session.");
    } finally {
      setBooting(false);
    }
  }

  const loadOverview = useCallback(async (token: string, isStaff: boolean) => {
    try {
      const dashboardPayload = await getDashboard(token);
      setDashboard(dashboardPayload);
      setNotifications(dashboardPayload.notifications);

      if (isStaff) {
        const [clientItems, cannedReplyItems] = await Promise.all([
          getClients(token),
          getCannedReplies(token),
        ]);
        setClients(clientItems);
        setCannedReplies(cannedReplyItems);
      } else if (session?.client) {
        setClients([session.client]);
        setCannedReplies([]);
      }
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : "Could not load the dashboard.");
    }
  }, [session?.client]);

  const loadBoard = useCallback(async (token: string) => {
    setLoadingBoard(true);

    try {
      const items = await getRequests(token, {
        search: search || undefined,
        status: statusFilter || undefined,
      });
      setRequests(items);
      setSelectedRequestId((current) => {
        if (current && items.some((item) => item.id === current)) {
          return current;
        }

        return items[0]?.id ?? null;
      });
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : "Could not load requests.");
    } finally {
      setLoadingBoard(false);
      setBooting(false);
    }
  }, [search, statusFilter]);

  const loadRequestDetail = useCallback(async (token: string, requestId: number) => {
    try {
      const detail = await getRequest(token, requestId);
      setSelectedRequest(detail);
      setRequestUpdate({
        status: detail.status,
        due_at: stripForInput(detail.due_at),
      });
      setReminderMessage(
        detail.status === "waiting_on_client"
          ? `Please upload the remaining items for "${detail.title}" so we can keep the deadline on track.`
          : "",
      );
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : "Could not load the request detail.");
    }
  }, []);

  useEffect(() => {
    const token = window.localStorage.getItem(TOKEN_KEY);

    if (!token) {
      setBooting(false);
      return;
    }

    void restoreSession(token);
  }, []);

  useEffect(() => {
    if (!session?.token) {
      return;
    }

    void loadOverview(session.token, session.user.role === "staff");
    void loadBoard(session.token);
  }, [loadBoard, loadOverview, session?.token, session?.user.role]);

  useEffect(() => {
    if (!session?.token) {
      return;
    }

    void loadBoard(session.token);
  }, [loadBoard, session?.token]);

  useEffect(() => {
    if (!session?.token || !selectedRequestId) {
      setSelectedRequest(null);
      return;
    }

    void loadRequestDetail(session.token, selectedRequestId);
  }, [loadRequestDetail, selectedRequestId, session?.token]);

  useEffect(() => {
    if (!flash) {
      return;
    }

    const timeout = window.setTimeout(() => setFlash(null), 4000);
    return () => window.clearTimeout(timeout);
  }, [flash]);

  async function refreshWorkspace(targetRequestId?: number) {
    if (!session) {
      return;
    }

    await Promise.all([
      loadOverview(session.token, session.user.role === "staff"),
      loadBoard(session.token),
    ]);

    if (targetRequestId) {
      setSelectedRequestId(targetRequestId);
      await loadRequestDetail(session.token, targetRequestId);
    }
  }

  async function handleLogin(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setBusy(true);
    setError(null);

    try {
      const payload = await login(loginForm.email, loginForm.password);
      window.localStorage.setItem(TOKEN_KEY, payload.token);
      setSession(payload);
      setFlash(`Signed in as ${payload.user.name}.`);
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : "Login failed.");
    } finally {
      setBusy(false);
      setBooting(false);
    }
  }

  async function handleLogout() {
    if (!session) return;

    setBusy(true);

    try {
      await logout(session.token);
    } finally {
      window.localStorage.removeItem(TOKEN_KEY);
      setSession(null);
      setDashboard(null);
      setRequests([]);
      setSelectedRequest(null);
      setSelectedRequestId(null);
      setClients([]);
      setNotifications([]);
      setBusy(false);
    }
  }

  async function handleCreateRequest(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!session) return;

    setBusy(true);
    setError(null);

    try {
      const payload: Record<string, unknown> = {
        title: requestForm.title,
        request_type: requestForm.request_type,
        summary: requestForm.summary,
        priority: requestForm.priority,
      };

      if (session.user.role === "staff") {
        payload.client_id = Number(requestForm.client_id);
        payload.due_at = requestForm.due_at || null;
      }

      const created = await createRequest(session.token, payload);
      setRequestForm({
        client_id: "",
        title: "",
        request_type: "",
        summary: "",
        priority: "normal",
        due_at: "",
      });
      await refreshWorkspace(created.id);
      setFlash("Request created.");
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : "Could not create the request.");
    } finally {
      setBusy(false);
    }
  }

  async function handleSaveRequestUpdate() {
    if (!session || !selectedRequest) return;

    setBusy(true);
    setError(null);

    try {
      await updateRequest(session.token, selectedRequest.id, {
        status: requestUpdate.status,
        due_at: requestUpdate.due_at || null,
      });
      await refreshWorkspace(selectedRequest.id);
      setFlash("Request updated.");
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : "Could not save the request update.");
    } finally {
      setBusy(false);
    }
  }

  async function handleCommentSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!session || !selectedRequest) return;

    setBusy(true);

    try {
      await createComment(session.token, selectedRequest.id, {
        body: commentBody,
        is_internal: commentInternal,
      });
      setCommentBody("");
      setCommentInternal(false);
      await refreshWorkspace(selectedRequest.id);
      setFlash("Reply posted.");
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : "Could not add the reply.");
    } finally {
      setBusy(false);
    }
  }

  async function handleUploadFile() {
    if (!session || !selectedRequest || !selectedFile) return;

    setBusy(true);

    try {
      await uploadRequestFile(session.token, selectedRequest.id, selectedFile);
      setSelectedFile(null);
      await refreshWorkspace(selectedRequest.id);
      setFlash("File uploaded.");
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : "Could not upload the file.");
    } finally {
      setBusy(false);
    }
  }

  async function handleSendReminder() {
    if (!session || !selectedRequest || session.user.role !== "staff") return;

    setBusy(true);

    try {
      await sendReminder(session.token, selectedRequest.id, reminderMessage);
      await refreshWorkspace(selectedRequest.id);
      setFlash("Reminder sent.");
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : "Could not send the reminder.");
    } finally {
      setBusy(false);
    }
  }

  async function handleCreateClient(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!session || session.user.role !== "staff") return;

    setBusy(true);

    try {
      await createClient(session.token, clientForm);
      setClientForm({
        company_name: "",
        primary_contact_name: "",
        email: "",
        notes: "",
        create_portal_user: true,
        password: "password",
      });
      await refreshWorkspace();
      setFlash("Client added.");
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : "Could not add the client.");
    } finally {
      setBusy(false);
    }
  }

  async function handleMarkNotificationsRead() {
    if (!session) return;

    try {
      await markNotificationsRead(session.token);
      await loadOverview(session.token, session.user.role === "staff");
      setFlash("Notifications marked as read.");
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : "Could not update notifications.");
    }
  }

  if (booting) {
    return (
      <main className="shell">
        <section className="panel p-8 sm:p-10">
          <p className="mono text-sm uppercase tracking-[0.24em] text-[color:var(--muted)]">
            Loading ClientLane
          </p>
          <h1 className="mt-4 text-3xl font-semibold tracking-tight">
            Restoring the workspace and request board.
          </h1>
        </section>
      </main>
    );
  }

  if (!session) {
    return (
      <PortalLogin
        busy={busy}
        email={loginForm.email}
        error={error}
        onEmailChange={(value) => setLoginForm((current) => ({ ...current, email: value }))}
        onPasswordChange={(value) =>
          setLoginForm((current) => ({ ...current, password: value }))
        }
        onSubmit={handleLogin}
        onUseDemo={(email) => setLoginForm({ email, password: "password" })}
        password={loginForm.password}
      />
    );
  }

  const isStaff = session.user.role === "staff";

  return (
    <main className="shell space-y-6">
      <section className="panel overflow-hidden p-6 sm:p-8">
        <div className="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
          <div className="space-y-3">
            <span className="eyebrow">{session.firm.niche ?? "Service workspace"}</span>
            <div>
              <h1 className="text-3xl font-semibold tracking-tight sm:text-4xl">
                {session.firm.name}
              </h1>
              <p className="mt-2 max-w-2xl leading-8 text-[color:var(--muted)]">
                {session.firm.portal_tagline}
              </p>
            </div>
          </div>
          <div className="flex flex-wrap items-center gap-3">
            <div className="rounded-full border border-black/10 bg-white/65 px-4 py-2">
              <p className="text-sm font-medium">{session.user.name}</p>
              <p className="text-xs text-[color:var(--muted)]">
                {session.user.role_label} · {session.user.title ?? "Portal user"}
              </p>
            </div>
            <button className="btn-primary" onClick={handleLogout} type="button">
              Sign Out
            </button>
          </div>
        </div>
      </section>

      {error ? (
        <section className="rounded-[24px] bg-rose-100 px-5 py-4 text-sm text-rose-900">
          {error}
        </section>
      ) : null}

      {flash ? (
        <section className="rounded-[24px] bg-emerald-100 px-5 py-4 text-sm text-emerald-900">
          {flash}
        </section>
      ) : null}

      <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        {Object.entries(dashboard?.stats ?? {}).map(([label, value]) => (
          <article key={label} className="panel p-5">
            <p className="mono text-xs uppercase tracking-[0.24em] text-[color:var(--muted)]">
              {label.replaceAll("_", " ")}
            </p>
            <p className="mt-4 text-3xl font-semibold tracking-tight">{value}</p>
          </article>
        ))}
      </section>

      <section className="grid gap-6 xl:grid-cols-[1.05fr_1.25fr_0.9fr]">
        <PortalBoard
          busy={busy}
          clients={clients}
          isStaff={isStaff}
          loadingBoard={loadingBoard}
          onRequestFormChange={(field, value) =>
            setRequestForm((current) => ({ ...current, [field]: value }))
          }
          onSearchChange={setSearch}
          onSelectRequest={setSelectedRequestId}
          onStatusFilterChange={setStatusFilter}
          onSubmitRequest={handleCreateRequest}
          priorities={requestPriorities}
          requestForm={requestForm}
          requests={requests}
          search={search}
          selectedRequestId={selectedRequestId}
          statuses={requestStatuses}
          statusFilter={statusFilter}
        />

        <PortalDetail
          busy={busy}
          cannedReplies={cannedReplies}
          commentBody={commentBody}
          commentInternal={commentInternal}
          errorHandler={setError}
          isStaff={isStaff}
          onCommentBodyChange={setCommentBody}
          onCommentInternalChange={setCommentInternal}
          onReminderMessageChange={setReminderMessage}
          onRequestUpdateChange={(field, value) =>
            setRequestUpdate((current) => ({ ...current, [field]: value }))
          }
          onSaveRequestUpdate={handleSaveRequestUpdate}
          onSelectedFileChange={setSelectedFile}
          onSendReminder={handleSendReminder}
          onSubmitComment={handleCommentSubmit}
          onUploadFile={handleUploadFile}
          reminderMessage={reminderMessage}
          requestUpdate={requestUpdate}
          selectedFile={selectedFile}
          selectedRequest={selectedRequest}
          statuses={requestStatuses}
          token={session.token}
        />

        <PortalSidebar
          busy={busy}
          clientForm={clientForm}
          clients={clients}
          dashboard={dashboard}
          isStaff={isStaff}
          notifications={notifications}
          onClientFormChange={(field, value) =>
            setClientForm((current) => ({ ...current, [field]: value }))
          }
          onMarkNotificationsRead={handleMarkNotificationsRead}
          onSubmitClient={handleCreateClient}
        />
      </section>
    </main>
  );
}
