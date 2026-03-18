import type { FormEvent } from "react";
import { formatDateTime } from "@/lib/format";
import type { Client, DashboardPayload, PortalNotification } from "@/lib/portal-types";

type PortalSidebarProps = {
  isStaff: boolean;
  busy: boolean;
  dashboard: DashboardPayload | null;
  notifications: PortalNotification[];
  clients: Client[];
  clientForm: {
    company_name: string;
    primary_contact_name: string;
    email: string;
    notes: string;
    create_portal_user: boolean;
    password: string;
  };
  onMarkNotificationsRead: () => void;
  onClientFormChange: (
    field:
      | "company_name"
      | "primary_contact_name"
      | "email"
      | "notes"
      | "create_portal_user"
      | "password",
    value: string | boolean,
  ) => void;
  onSubmitClient: (event: FormEvent<HTMLFormElement>) => void;
};

export function PortalSidebar({
  isStaff,
  busy,
  dashboard,
  notifications,
  clients,
  clientForm,
  onMarkNotificationsRead,
  onClientFormChange,
  onSubmitClient,
}: PortalSidebarProps) {
  return (
    <div className="space-y-6">
      <section className="panel p-5">
        <div className="flex items-center justify-between gap-3">
          <div>
            <p className="mono text-xs uppercase tracking-[0.24em] text-[color:var(--muted)]">
              Notifications
            </p>
            <h2 className="mt-2 text-2xl font-semibold tracking-tight">Inbox</h2>
          </div>
          <button className="btn-ghost" onClick={onMarkNotificationsRead} type="button">
            Clear
          </button>
        </div>
        <div className="mt-3 rounded-full bg-white/70 px-3 py-1 text-sm">
          {dashboard?.unread_notifications_count ?? 0} unread
        </div>
        <div className="mt-5 space-y-3">
          {notifications.map((notification) => (
            <article
              key={notification.id}
              className="rounded-[22px] border border-black/8 bg-white/65 px-4 py-4"
            >
              <p className="font-medium">{notification.title}</p>
              <p className="mt-2 text-sm leading-7 text-[color:var(--muted)]">
                {notification.message}
              </p>
              <p className="mt-3 text-xs text-[color:var(--muted)]">
                {formatDateTime(notification.created_at)}
              </p>
            </article>
          ))}
        </div>
      </section>

      {isStaff ? (
        <>
          <section className="panel p-5">
            <p className="mono text-xs uppercase tracking-[0.24em] text-[color:var(--muted)]">
              Client Directory
            </p>
            <h2 className="mt-2 text-2xl font-semibold tracking-tight">
              Active clients
            </h2>
            <div className="mt-5 space-y-3">
              {clients.map((client) => (
                <article
                  key={client.id}
                  className="rounded-[20px] border border-black/8 bg-white/65 px-4 py-4"
                >
                  <p className="font-medium">{client.company_name}</p>
                  <p className="mt-1 text-sm text-[color:var(--muted)]">
                    {client.primary_contact_name} · {client.email}
                  </p>
                  <div className="mt-3 flex flex-wrap gap-2 text-sm">
                    <span className="rounded-full bg-stone-100 px-3 py-1">
                      {client.open_requests_count ?? 0} open
                    </span>
                    <span className="rounded-full bg-stone-100 px-3 py-1">
                      {client.total_requests_count ?? 0} total
                    </span>
                  </div>
                </article>
              ))}
            </div>
          </section>

          <section className="panel p-5">
            <p className="mono text-xs uppercase tracking-[0.24em] text-[color:var(--muted)]">
              Add Client
            </p>
            <h2 className="mt-2 text-2xl font-semibold tracking-tight">
              Create portal access
            </h2>
            <form className="mt-5 space-y-3" onSubmit={onSubmitClient}>
              <input
                className="field"
                onChange={(event) =>
                  onClientFormChange("company_name", event.target.value)
                }
                placeholder="Company name"
                required
                value={clientForm.company_name}
              />
              <input
                className="field"
                onChange={(event) =>
                  onClientFormChange("primary_contact_name", event.target.value)
                }
                placeholder="Primary contact"
                required
                value={clientForm.primary_contact_name}
              />
              <input
                className="field"
                onChange={(event) => onClientFormChange("email", event.target.value)}
                placeholder="contact@example.com"
                required
                type="email"
                value={clientForm.email}
              />
              <textarea
                className="field min-h-24"
                onChange={(event) => onClientFormChange("notes", event.target.value)}
                placeholder="Optional onboarding notes"
                value={clientForm.notes}
              />
              <label className="flex items-center gap-3 text-sm text-[color:var(--muted)]">
                <input
                  checked={clientForm.create_portal_user}
                  onChange={(event) =>
                    onClientFormChange("create_portal_user", event.target.checked)
                  }
                  type="checkbox"
                />
                Create portal user immediately
              </label>
              {clientForm.create_portal_user ? (
                <input
                  className="field"
                  onChange={(event) =>
                    onClientFormChange("password", event.target.value)
                  }
                  placeholder="Temporary password"
                  required
                  value={clientForm.password}
                />
              ) : null}
              <button className="btn-primary w-full" disabled={busy} type="submit">
                Add client
              </button>
            </form>
          </section>
        </>
      ) : (
        <section className="panel p-5">
          <p className="mono text-xs uppercase tracking-[0.24em] text-[color:var(--muted)]">
            Your Access
          </p>
          <h2 className="mt-2 text-2xl font-semibold tracking-tight">
            Client workspace
          </h2>
          <div className="mt-5 space-y-3">
            {clients.map((client) => (
              <article
                key={client.id}
                className="rounded-[22px] border border-black/8 bg-white/65 px-4 py-4"
              >
                <p className="font-medium">{client.company_name}</p>
                <p className="mt-2 text-sm leading-7 text-[color:var(--muted)]">
                  Requests, files, and reminders for your team stay in this
                  workspace thread instead of scattered email chains.
                </p>
              </article>
            ))}
          </div>
        </section>
      )}
    </div>
  );
}
