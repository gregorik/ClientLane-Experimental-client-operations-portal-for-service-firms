import type { FormEvent } from "react";
import type { Client, WorkRequest } from "@/lib/portal-types";
import { formatDate, toneForPriority, toneForStatus } from "@/lib/format";

type PortalBoardProps = {
  isStaff: boolean;
  busy: boolean;
  loadingBoard: boolean;
  search: string;
  statusFilter: string;
  requests: WorkRequest[];
  clients: Client[];
  selectedRequestId: number | null;
  requestForm: {
    client_id: string;
    title: string;
    request_type: string;
    summary: string;
    priority: string;
    due_at: string;
  };
  statuses: readonly string[];
  priorities: readonly string[];
  onSearchChange: (value: string) => void;
  onStatusFilterChange: (value: string) => void;
  onSelectRequest: (requestId: number) => void;
  onRequestFormChange: (
    field: "client_id" | "title" | "request_type" | "summary" | "priority" | "due_at",
    value: string,
  ) => void;
  onSubmitRequest: (event: FormEvent<HTMLFormElement>) => void;
};

export function PortalBoard({
  isStaff,
  busy,
  loadingBoard,
  search,
  statusFilter,
  requests,
  clients,
  selectedRequestId,
  requestForm,
  statuses,
  priorities,
  onSearchChange,
  onStatusFilterChange,
  onSelectRequest,
  onRequestFormChange,
  onSubmitRequest,
}: PortalBoardProps) {
  return (
    <div className="space-y-6">
      <section className="panel p-5">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <div>
            <p className="mono text-xs uppercase tracking-[0.24em] text-[color:var(--muted)]">
              Request Board
            </p>
            <h2 className="mt-2 text-2xl font-semibold tracking-tight">
              Active requests
            </h2>
          </div>
          <span className="rounded-full bg-white/70 px-3 py-1 text-sm">
            {requests.length} visible
          </span>
        </div>
        <div className="mt-5 space-y-3">
          <input
            className="field"
            onChange={(event) => onSearchChange(event.target.value)}
            placeholder="Search title, client, type..."
            value={search}
          />
          <div className="flex flex-wrap gap-2">
            <button
              className={`btn-ghost ${statusFilter === "" ? "bg-[color:var(--ink)] text-white" : ""}`}
              onClick={() => onStatusFilterChange("")}
              type="button"
            >
              All
            </button>
            {statuses.map((status) => (
              <button
                key={status}
                className={`btn-ghost ${statusFilter === status ? "bg-[color:var(--ink)] text-white" : ""}`}
                onClick={() => onStatusFilterChange(status)}
                type="button"
              >
                {status.replaceAll("_", " ")}
              </button>
            ))}
          </div>
        </div>
        <div className="mt-5 space-y-3">
          {loadingBoard ? (
            <div className="rounded-[22px] border border-dashed border-black/10 px-4 py-6 text-sm text-[color:var(--muted)]">
              Refreshing the board...
            </div>
          ) : null}
          {!loadingBoard && requests.length === 0 ? (
            <div className="rounded-[22px] border border-dashed border-black/10 px-4 py-6 text-sm text-[color:var(--muted)]">
              No requests match the current filters.
            </div>
          ) : null}
          {requests.map((item) => (
            <button
              key={item.id}
              className={`w-full rounded-[24px] border px-4 py-4 text-left transition ${
                selectedRequestId === item.id
                  ? "border-[color:var(--accent)] bg-white shadow-lg"
                  : "border-black/8 bg-white/55 hover:bg-white/80"
              }`}
              onClick={() => onSelectRequest(item.id)}
              type="button"
            >
              <div className="flex items-start justify-between gap-3">
                <div>
                  <p className="text-lg font-medium">{item.title}</p>
                  <p className="mt-1 text-sm text-[color:var(--muted)]">
                    {item.client?.company_name ?? "Client request"} · {item.request_type}
                  </p>
                </div>
                <span
                  className={`rounded-full px-3 py-1 text-xs font-medium ${toneForStatus(item.status)}`}
                >
                  {item.status_label}
                </span>
              </div>
              <div className="mt-4 flex flex-wrap items-center gap-2 text-sm">
                <span
                  className={`rounded-full px-3 py-1 ${toneForPriority(item.priority)}`}
                >
                  {item.priority_label}
                </span>
                <span className="rounded-full bg-stone-100 px-3 py-1">
                  {item.files_count ?? 0} files
                </span>
                <span className="rounded-full bg-stone-100 px-3 py-1">
                  Due {formatDate(item.due_at)}
                </span>
              </div>
            </button>
          ))}
        </div>
      </section>

      <section className="panel p-5">
        <p className="mono text-xs uppercase tracking-[0.24em] text-[color:var(--muted)]">
          New Request
        </p>
        <h2 className="mt-2 text-2xl font-semibold tracking-tight">
          Add work into the lane
        </h2>
        <form className="mt-5 space-y-3" onSubmit={onSubmitRequest}>
          {isStaff ? (
            <select
              className="field"
              onChange={(event) => onRequestFormChange("client_id", event.target.value)}
              required
              value={requestForm.client_id}
            >
              <option value="">Select client</option>
              {clients.map((client) => (
                <option key={client.id} value={client.id}>
                  {client.company_name}
                </option>
              ))}
            </select>
          ) : null}
          <input
            className="field"
            onChange={(event) => onRequestFormChange("title", event.target.value)}
            placeholder="Request title"
            required
            value={requestForm.title}
          />
          <input
            className="field"
            onChange={(event) => onRequestFormChange("request_type", event.target.value)}
            placeholder="Request type"
            required
            value={requestForm.request_type}
          />
          <textarea
            className="field min-h-28"
            onChange={(event) => onRequestFormChange("summary", event.target.value)}
            placeholder="Describe the work, outstanding documents, or approval needed."
            required
            value={requestForm.summary}
          />
          <div className="grid gap-3 sm:grid-cols-2">
            <select
              className="field"
              onChange={(event) => onRequestFormChange("priority", event.target.value)}
              value={requestForm.priority}
            >
              {priorities.map((priority) => (
                <option key={priority} value={priority}>
                  {priority}
                </option>
              ))}
            </select>
            {isStaff ? (
              <input
                className="field"
                onChange={(event) => onRequestFormChange("due_at", event.target.value)}
                type="date"
                value={requestForm.due_at}
              />
            ) : null}
          </div>
          <button className="btn-primary w-full" disabled={busy} type="submit">
            {busy ? "Saving..." : "Create request"}
          </button>
        </form>
      </section>
    </div>
  );
}
