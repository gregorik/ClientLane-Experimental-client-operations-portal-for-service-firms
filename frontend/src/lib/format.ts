export function formatDate(value: string | null) {
  if (!value) {
    return "Not set";
  }

  return new Intl.DateTimeFormat("en", {
    month: "short",
    day: "numeric",
    year: "numeric",
  }).format(new Date(value));
}

export function formatDateTime(value: string | null) {
  if (!value) {
    return "No activity yet";
  }

  return new Intl.DateTimeFormat("en", {
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
  }).format(new Date(value));
}

export function formatBytes(value: number) {
  if (value < 1024) {
    return `${value} B`;
  }

  const kb = value / 1024;

  if (kb < 1024) {
    return `${kb.toFixed(1)} KB`;
  }

  return `${(kb / 1024).toFixed(1)} MB`;
}

export function stripForInput(value: string | null) {
  if (!value) {
    return "";
  }

  return new Date(value).toISOString().slice(0, 10);
}

export function toneForStatus(status: string) {
  switch (status) {
    case "completed":
      return "bg-emerald-100 text-emerald-900";
    case "waiting_on_client":
      return "bg-amber-100 text-amber-900";
    case "waiting_on_staff":
      return "bg-sky-100 text-sky-900";
    case "in_progress":
      return "bg-orange-100 text-orange-900";
    case "archived":
      return "bg-stone-200 text-stone-700";
    default:
      return "bg-stone-100 text-stone-800";
  }
}

export function toneForPriority(priority: string) {
  switch (priority) {
    case "urgent":
      return "bg-rose-100 text-rose-900";
    case "high":
      return "bg-orange-100 text-orange-900";
    case "low":
      return "bg-teal-100 text-teal-900";
    default:
      return "bg-stone-100 text-stone-800";
  }
}
