import type { FormEvent } from "react";
import { downloadProtectedFile } from "@/lib/api";
import {
  formatBytes,
  formatDate,
  formatDateTime,
  toneForPriority,
  toneForStatus,
} from "@/lib/format";
import type { CannedReply, WorkRequest } from "@/lib/portal-types";

type PortalDetailProps = {
  token: string;
  isStaff: boolean;
  busy: boolean;
  errorHandler: (message: string) => void;
  selectedRequest: WorkRequest | null;
  commentBody: string;
  commentInternal: boolean;
  reminderMessage: string;
  selectedFile: File | null;
  requestUpdate: {
    status: string;
    due_at: string;
  };
  cannedReplies: CannedReply[];
  statuses: readonly string[];
  onCommentBodyChange: (value: string) => void;
  onCommentInternalChange: (value: boolean) => void;
  onReminderMessageChange: (value: string) => void;
  onSelectedFileChange: (file: File | null) => void;
  onRequestUpdateChange: (field: "status" | "due_at", value: string) => void;
  onSaveRequestUpdate: () => void;
  onSendReminder: () => void;
  onSubmitComment: (event: FormEvent<HTMLFormElement>) => void;
  onUploadFile: () => void;
};

export function PortalDetail({
  token,
  isStaff,
  busy,
  errorHandler,
  selectedRequest,
  commentBody,
  commentInternal,
  reminderMessage,
  selectedFile,
  requestUpdate,
  cannedReplies,
  statuses,
  onCommentBodyChange,
  onCommentInternalChange,
  onReminderMessageChange,
  onSelectedFileChange,
  onRequestUpdateChange,
  onSaveRequestUpdate,
  onSendReminder,
  onSubmitComment,
  onUploadFile,
}: PortalDetailProps) {
  if (!selectedRequest) {
    return (
      <section className="panel p-5">
        <div className="rounded-[24px] border border-dashed border-black/12 px-5 py-10 text-center text-[color:var(--muted)]">
          Select a request to open the thread, files, activity, and staff controls.
        </div>
      </section>
    );
  }

  return (
    <section className="panel p-5">
      <div className="space-y-6">
        <div className="flex flex-wrap items-start justify-between gap-4">
          <div>
            <p className="mono text-xs uppercase tracking-[0.24em] text-[color:var(--muted)]">
              Selected Request
            </p>
            <h2 className="mt-2 text-3xl font-semibold tracking-tight">
              {selectedRequest.title}
            </h2>
            <p className="mt-2 leading-8 text-[color:var(--muted)]">
              {selectedRequest.summary}
            </p>
          </div>
          <div className="flex flex-wrap gap-2">
            <span
              className={`rounded-full px-3 py-1 text-sm font-medium ${toneForStatus(
                selectedRequest.status,
              )}`}
            >
              {selectedRequest.status_label}
            </span>
            <span
              className={`rounded-full px-3 py-1 text-sm font-medium ${toneForPriority(
                selectedRequest.priority,
              )}`}
            >
              {selectedRequest.priority_label}
            </span>
          </div>
        </div>

        <div className="grid gap-3 sm:grid-cols-2">
          <div className="rounded-[22px] border border-black/8 bg-white/60 p-4">
            <p className="mono text-xs uppercase tracking-[0.24em] text-[color:var(--muted)]">
              Client
            </p>
            <p className="mt-2 text-lg font-medium">
              {selectedRequest.client?.company_name ?? "No client"}
            </p>
          </div>
          <div className="rounded-[22px] border border-black/8 bg-white/60 p-4">
            <p className="mono text-xs uppercase tracking-[0.24em] text-[color:var(--muted)]">
              Due Date
            </p>
            <p className="mt-2 text-lg font-medium">
              {formatDate(selectedRequest.due_at)}
            </p>
          </div>
        </div>

        {isStaff ? (
          <div className="rounded-[24px] border border-black/8 bg-white/65 p-4">
            <p className="mono text-xs uppercase tracking-[0.24em] text-[color:var(--muted)]">
              Staff Controls
            </p>
            <div className="mt-4 grid gap-3 sm:grid-cols-2">
              <select
                className="field"
                onChange={(event) =>
                  onRequestUpdateChange("status", event.target.value)
                }
                value={requestUpdate.status}
              >
                {statuses.map((status) => (
                  <option key={status} value={status}>
                    {status.replaceAll("_", " ")}
                  </option>
                ))}
              </select>
              <input
                className="field"
                onChange={(event) =>
                  onRequestUpdateChange("due_at", event.target.value)
                }
                type="date"
                value={requestUpdate.due_at}
              />
            </div>
            <button
              className="btn-secondary mt-3 w-full"
              disabled={busy}
              onClick={onSaveRequestUpdate}
              type="button"
            >
              Save request changes
            </button>
            <div className="mt-4 space-y-3">
              <textarea
                className="field min-h-24"
                onChange={(event) => onReminderMessageChange(event.target.value)}
                placeholder="Optional reminder text"
                value={reminderMessage}
              />
              <button
                className="btn-primary w-full"
                disabled={busy}
                onClick={onSendReminder}
                type="button"
              >
                Send reminder
              </button>
            </div>
          </div>
        ) : null}

        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="text-xl font-semibold tracking-tight">Thread</h3>
            <span className="rounded-full bg-stone-100 px-3 py-1 text-sm">
              {selectedRequest.comments.length} replies
            </span>
          </div>
          <div className="space-y-3">
            {selectedRequest.comments.map((comment) => (
              <article
                key={comment.id}
                className={`rounded-[22px] border p-4 ${
                  comment.is_internal
                    ? "border-amber-200 bg-amber-50"
                    : "border-black/8 bg-white/70"
                }`}
              >
                <div className="flex items-center justify-between gap-3">
                  <p className="font-medium">
                    {comment.author?.name ?? "Unknown author"}
                  </p>
                  <p className="text-xs text-[color:var(--muted)]">
                    {formatDateTime(comment.created_at)}
                  </p>
                </div>
                <p className="mt-3 leading-7 text-[color:var(--muted)]">
                  {comment.body}
                </p>
              </article>
            ))}
          </div>
          <form className="space-y-3" onSubmit={onSubmitComment}>
            <textarea
              className="field min-h-28"
              onChange={(event) => onCommentBodyChange(event.target.value)}
              placeholder="Add a reply to this request..."
              required
              value={commentBody}
            />
            {isStaff ? (
              <label className="flex items-center gap-3 text-sm text-[color:var(--muted)]">
                <input
                  checked={commentInternal}
                  onChange={(event) => onCommentInternalChange(event.target.checked)}
                  type="checkbox"
                />
                Save as internal note
              </label>
            ) : null}
            {isStaff && cannedReplies.length > 0 ? (
              <div className="flex flex-wrap gap-2">
                {cannedReplies.map((reply) => (
                  <button
                    key={reply.id}
                    className="btn-ghost"
                    onClick={() => onCommentBodyChange(reply.content)}
                    type="button"
                  >
                    {reply.title}
                  </button>
                ))}
              </div>
            ) : null}
            <button className="btn-primary w-full" disabled={busy} type="submit">
              Post reply
            </button>
          </form>
        </div>

        <div className="grid gap-6 lg:grid-cols-2">
          <div className="space-y-3">
            <div className="flex items-center justify-between">
              <h3 className="text-xl font-semibold tracking-tight">Files</h3>
              <span className="rounded-full bg-stone-100 px-3 py-1 text-sm">
                {selectedRequest.files.length}
              </span>
            </div>
            <div className="space-y-3">
              {selectedRequest.files.map((file) => (
                <button
                  key={file.id}
                  className="w-full rounded-[20px] border border-black/8 bg-white/70 px-4 py-3 text-left"
                  onClick={() =>
                    downloadProtectedFile(token, file.download_url).catch((caught) =>
                      errorHandler(
                        caught instanceof Error
                          ? caught.message
                          : "Could not download the file.",
                      ),
                    )
                  }
                  type="button"
                >
                  <p className="font-medium">{file.original_name}</p>
                  <p className="mt-1 text-sm text-[color:var(--muted)]">
                    {formatBytes(file.size_bytes)} · {formatDateTime(file.created_at)}
                  </p>
                </button>
              ))}
            </div>
            <div className="rounded-[22px] border border-dashed border-black/12 p-4">
              <input
                className="field"
                onChange={(event) =>
                  onSelectedFileChange(event.target.files?.[0] ?? null)
                }
                type="file"
              />
              <button
                className="btn-secondary mt-3 w-full"
                disabled={busy || !selectedFile}
                onClick={onUploadFile}
                type="button"
              >
                Upload selected file
              </button>
            </div>
          </div>

          <div className="space-y-3">
            <div className="flex items-center justify-between">
              <h3 className="text-xl font-semibold tracking-tight">Activity</h3>
              <span className="rounded-full bg-stone-100 px-3 py-1 text-sm">
                {selectedRequest.activities.length}
              </span>
            </div>
            <div className="space-y-3">
              {selectedRequest.activities.map((activity) => (
                <article
                  key={activity.id}
                  className="rounded-[20px] border border-black/8 bg-white/70 px-4 py-3"
                >
                  <p className="font-medium">{activity.description}</p>
                  <p className="mt-1 text-sm text-[color:var(--muted)]">
                    {activity.actor?.name ?? "System"} ·{" "}
                    {formatDateTime(activity.created_at)}
                  </p>
                </article>
              ))}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
