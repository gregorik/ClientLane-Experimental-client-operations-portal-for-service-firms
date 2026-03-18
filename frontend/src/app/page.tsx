import Link from "next/link";

const featureCards = [
  {
    title: "Request Intake",
    description:
      "Capture client requests with clear owners, due dates, and status lanes instead of starting from inbox chaos.",
  },
  {
    title: "File Collection",
    description:
      "Pull documents into the same thread as the work so staff can stop chasing attachments across email and shared drives.",
  },
  {
    title: "Status Visibility",
    description:
      "Give clients a direct view of what is new, in progress, waiting on them, or completed without another update call.",
  },
  {
    title: "Operational History",
    description:
      "Keep a clean activity trail for reminders, comments, file uploads, and handoffs inside a single workspace.",
  },
];

export default function Home() {
  return (
    <main className="shell space-y-6 sm:space-y-8">
      <section className="panel overflow-hidden">
        <div className="grid gap-10 px-6 py-8 sm:px-8 sm:py-10 lg:grid-cols-[1.2fr_0.8fr] lg:px-12 lg:py-14">
          <div className="space-y-6">
            <span className="eyebrow">Client Operations Portal</span>
            <div className="space-y-4">
              <h1 className="max-w-3xl text-4xl font-semibold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                ClientLane gives service firms one place for requests, files,
                updates, and reminders.
              </h1>
              <p className="max-w-2xl text-base leading-8 text-[color:var(--muted)] sm:text-lg">
                Built as an MVP for accountants, bookkeepers, agencies, and
                other B2B service teams that need a cleaner client-facing
                workflow than email plus spreadsheets.
              </p>
            </div>
            <div className="flex flex-wrap gap-3">
              <Link className="btn-primary" href="/portal">
                Open The Portal
              </Link>
              <a className="btn-secondary" href="#mvp">
                See MVP Scope
              </a>
            </div>
            <div className="grid gap-4 pt-4 sm:grid-cols-3">
              <div className="rounded-[24px] border border-black/8 bg-white/55 p-4">
                <p className="mono text-xs uppercase tracking-[0.24em] text-[color:var(--muted)]">
                  Core Promise
                </p>
                <p className="mt-2 text-lg font-medium">
                  Reduce status-check emails in the first week.
                </p>
              </div>
              <div className="rounded-[24px] border border-black/8 bg-white/55 p-4">
                <p className="mono text-xs uppercase tracking-[0.24em] text-[color:var(--muted)]">
                  Best Wedge
                </p>
                <p className="mt-2 text-lg font-medium">
                  Small accounting and bookkeeping firms.
                </p>
              </div>
              <div className="rounded-[24px] border border-black/8 bg-white/55 p-4">
                <p className="mono text-xs uppercase tracking-[0.24em] text-[color:var(--muted)]">
                  Stack
                </p>
                <p className="mt-2 text-lg font-medium">
                  Laravel API and Next.js portal UI.
                </p>
              </div>
            </div>
          </div>

          <div className="panel-strong flex flex-col justify-between gap-6 p-6">
            <div className="space-y-4">
              <p className="mono text-xs uppercase tracking-[0.24em] text-[color:var(--muted)]">
                Pilot Workflow
              </p>
              <div className="space-y-3">
                {[
                  "Client submits a request and uploads the first file batch.",
                  "Staff set the due date, owner, and current lane.",
                  "Replies, reminders, and missing documents stay on the same thread.",
                  "Everyone sees the same status without asking for another update.",
                ].map((step, index) => (
                  <div
                    key={step}
                    className="flex gap-4 rounded-[22px] border border-black/8 bg-[color:var(--panel-soft)] p-4"
                  >
                    <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[color:var(--ink)] text-sm font-medium text-white">
                      {index + 1}
                    </span>
                    <p className="leading-7 text-[color:var(--muted)]">{step}</p>
                  </div>
                ))}
              </div>
            </div>
            <div className="rounded-[24px] bg-[linear-gradient(135deg,#1e1b17,#3a332c)] p-5 text-white">
              <p className="mono text-xs uppercase tracking-[0.24em] text-white/65">
                Positioning
              </p>
              <p className="mt-2 text-xl font-medium">
                The client operations portal for service firms.
              </p>
            </div>
          </div>
        </div>
      </section>

      <section id="mvp" className="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <div className="panel p-6 sm:p-8">
          <span className="eyebrow">MVP Outcome</span>
          <h2 className="section-title mt-4">
            A sharp first release, not another generic dashboard.
          </h2>
          <p className="mt-4 max-w-xl leading-8 text-[color:var(--muted)]">
            The initial scope stays tight around the operational handoff
            between firm and client: requests, files, comments, reminders,
            statuses, and an admin view for the staff side.
          </p>
          <div className="mt-6 grid gap-3 sm:grid-cols-2">
            {[
              "Client login",
              "Request intake",
              "Secure file upload",
              "Status tracking",
              "Comments per request",
              "Activity log",
              "Notifications",
              "Admin dashboard",
            ].map((item) => (
              <div
                key={item}
                className="rounded-[20px] border border-black/8 bg-white/60 px-4 py-3"
              >
                {item}
              </div>
            ))}
          </div>
        </div>

        <div className="grid gap-6 sm:grid-cols-2">
          {featureCards.map((feature) => (
            <article key={feature.title} className="panel p-6">
              <p className="mono text-xs uppercase tracking-[0.24em] text-[color:var(--muted)]">
                {feature.title}
              </p>
              <h3 className="mt-4 text-2xl font-medium tracking-tight">
                {feature.title}
              </h3>
              <p className="mt-3 leading-8 text-[color:var(--muted)]">
                {feature.description}
              </p>
            </article>
          ))}
        </div>
      </section>
    </main>
  );
}
