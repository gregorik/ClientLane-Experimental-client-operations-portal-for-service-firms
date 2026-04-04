<h1>ClientLane MVP</h1>

![Client0](https://github.com/user-attachments/assets/0aa179b1-a32e-41cf-b30c-efede5c49129)


<p>ClientLane is a client operations portal MVP for service firms. 

- It solves a concrete, expensive annoyance: status-check emails, missing files, and scattered client communication.
- The positioning is sharp: “client operations portal” is easier to sell than a generic portal, CRM, or dashboard.
- The first buyer is clear: small accounting and bookkeeping firms with recurring client work and constant document
    collection.
- The value is easy to demonstrate in one demo: submit request, upload files, track status, send reminder, leave
    comments.
- The ROI is legible: less admin chasing, faster turnaround, cleaner client experience, better audit trail.
- The message is strong because it is operational, not abstract: “Give clients one place for requests, files, and
    updates.”
- The MVP is small enough to ship without fake breadth: auth, clients, requests, statuses, files, comments, reminders,
    notifications, dashboard.
- The workflow is sticky because firms repeat it every month or quarter, which creates natural retention.
- It has a clean upgrade path: hosted SaaS, team seats, branding, audit logs, exports, API/webhooks later.
- It fits the stack well: Laravel handles auth, storage, notifications, queues, and business rules; Next.js handles
    the portal UI and landing page.
- It can be piloted manually with a few firms before broader launch, so validation does not require ads or a large go-
    to-market motion.
- It is narrow enough to be credible and broad enough to expand into adjacent service niches later.

![Client1](https://github.com/user-attachments/assets/94b4733b-6ed1-4d64-bfff-558dd2e9b8c3)

  
  
This workspace now contains:</p>
<ul>
<li><code>backend/</code>: Laravel 13 API with SQLite, Sanctum token auth, request workflow, comments, files, reminders, notifications, canned replies, and seeded demo data</li>
<li><code>frontend/</code>: Next.js 16 portal UI and landing page wired to the Laravel API</li>
<li><code>.tools/</code>: portable PHP runtime plus Composer so the backend can run even without a system PHP install</li>
</ul>
<h2>Demo credentials</h2>
<ul>
<li>Staff: <code>admin@clientlane.test</code> / <code>password</code></li>
<li>Staff: <code>ops@clientlane.test</code> / <code>password</code></li>
<li>Client: <code>mina@harborbakery.test</code> / <code>password</code></li>
</ul>
<h2>Run locally</h2>
<ol>
<li>Start the Laravel API:</li>
</ol>
<pre><code class="language-powershell">cd \backend
&amp; &#39;\.tools\php\php.exe&#39; artisan serve
</code></pre>
<p>Use this exact command instead:</p>
<pre><code class="language-powershell">cd \backend
&amp; &#39;\.tools\php\php.exe&#39; artisan migrate:fresh --seed
&amp; &#39;\.tools\php\php.exe&#39; artisan serve
</code></pre>
<ol start="2">
<li>Start the Next.js frontend:</li>
</ol>
<pre><code class="language-powershell">cd \frontend
copy .env.local.example .env.local
npm run dev
</code></pre>
<ol start="3">
<li>Open:</li>
</ol>
<ul>
<li>Frontend: <code>http://localhost:3000</code></li>
<li>Portal: <code>http://localhost:3000/portal</code></li>
<li>API: <code>http://127.0.0.1:8000</code></li>
</ul>
<h2>Verified commands</h2>
<p>Backend:</p>
<pre><code class="language-powershell">cd \backend
&amp; &#39;\.tools\php\php.exe&#39; artisan migrate:fresh --seed
&amp; &#39;\.tools\php\php.exe&#39; artisan test
</code></pre>
<p>Frontend:</p>
<pre><code class="language-powershell">cd \frontend
npm run lint
npm run build
</code></pre>
