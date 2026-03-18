<h1>ClientLane MVP</h1>
<p>ClientLane is a client operations portal MVP for service firms. This workspace now contains:</p>
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
<pre><code class="language-powershell">cd C:\1gregorigin\NEW\backend
&amp; &#39;C:\1gregorigin\NEW\.tools\php\php.exe&#39; artisan serve
</code></pre>
<p>Use this exact command instead:</p>
<pre><code class="language-powershell">cd C:\1gregorigin\NEW\backend
&amp; &#39;C:\1gregorigin\NEW\.tools\php\php.exe&#39; artisan migrate:fresh --seed
&amp; &#39;C:\1gregorigin\NEW\.tools\php\php.exe&#39; artisan serve
</code></pre>
<ol start="2">
<li>Start the Next.js frontend:</li>
</ol>
<pre><code class="language-powershell">cd C:\1gregorigin\NEW\frontend
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
<pre><code class="language-powershell">cd C:\1gregorigin\NEW\backend
&amp; &#39;C:\1gregorigin\NEW\.tools\php\php.exe&#39; artisan migrate:fresh --seed
&amp; &#39;C:\1gregorigin\NEW\.tools\php\php.exe&#39; artisan test
</code></pre>
<p>Frontend:</p>
<pre><code class="language-powershell">cd C:\1gregorigin\NEW\frontend
npm run lint
npm run build
</code></pre>
