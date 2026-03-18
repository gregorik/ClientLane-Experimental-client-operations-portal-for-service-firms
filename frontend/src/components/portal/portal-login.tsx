import type { FormEvent } from "react";

type PortalLoginProps = {
  busy: boolean;
  error: string | null;
  email: string;
  password: string;
  onEmailChange: (value: string) => void;
  onPasswordChange: (value: string) => void;
  onSubmit: (event: FormEvent<HTMLFormElement>) => void;
  onUseDemo: (email: string) => void;
};

export function PortalLogin({
  busy,
  error,
  email,
  password,
  onEmailChange,
  onPasswordChange,
  onSubmit,
  onUseDemo,
}: PortalLoginProps) {
  return (
    <main className="shell">
      <section className="panel overflow-hidden">
        <div className="grid gap-8 px-6 py-8 sm:px-8 sm:py-10 lg:grid-cols-[1.1fr_0.9fr] lg:px-12">
          <div className="space-y-6">
            <span className="eyebrow">ClientLane Portal</span>
            <div className="space-y-4">
              <h1 className="text-4xl font-semibold leading-tight tracking-tight sm:text-5xl">
                The operational inbox your clients can actually use.
              </h1>
              <p className="max-w-2xl leading-8 text-[color:var(--muted)]">
                Log in as staff or client to review the seeded MVP workspace:
                request queues, file uploads, comments, reminders, and a live
                admin dashboard.
              </p>
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
              <button
                className="panel-strong cursor-pointer p-5 text-left"
                onClick={() => onUseDemo("admin@clientlane.test")}
                type="button"
              >
                <p className="mono text-xs uppercase tracking-[0.24em] text-[color:var(--muted)]">
                  Staff Demo
                </p>
                <p className="mt-3 text-xl font-medium">admin@clientlane.test</p>
                <p className="mt-1 text-sm text-[color:var(--muted)]">
                  Managing partner view
                </p>
              </button>
              <button
                className="panel-strong cursor-pointer p-5 text-left"
                onClick={() => onUseDemo("mina@harborbakery.test")}
                type="button"
              >
                <p className="mono text-xs uppercase tracking-[0.24em] text-[color:var(--muted)]">
                  Client Demo
                </p>
                <p className="mt-3 text-xl font-medium">mina@harborbakery.test</p>
                <p className="mt-1 text-sm text-[color:var(--muted)]">
                  Harbor Bakery portal view
                </p>
              </button>
            </div>
          </div>

          <div className="panel-strong p-6 sm:p-8">
            <p className="mono text-xs uppercase tracking-[0.24em] text-[color:var(--muted)]">
              Sign In
            </p>
            <form className="mt-6 space-y-4" onSubmit={onSubmit}>
              <label className="block">
                <span className="mb-2 block text-sm text-[color:var(--muted)]">
                  Email
                </span>
                <input
                  className="field"
                  onChange={(event) => onEmailChange(event.target.value)}
                  type="email"
                  value={email}
                />
              </label>
              <label className="block">
                <span className="mb-2 block text-sm text-[color:var(--muted)]">
                  Password
                </span>
                <input
                  className="field"
                  onChange={(event) => onPasswordChange(event.target.value)}
                  type="password"
                  value={password}
                />
              </label>
              {error ? (
                <div className="rounded-2xl bg-rose-100 px-4 py-3 text-sm text-rose-900">
                  {error}
                </div>
              ) : null}
              <button className="btn-primary w-full" disabled={busy} type="submit">
                {busy ? "Signing in..." : "Enter the portal"}
              </button>
            </form>
          </div>
        </div>
      </section>
    </main>
  );
}
