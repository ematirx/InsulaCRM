# AI LeadFlow CRM Demo Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Deliver a safe, public, branded real-estate AI lead-qualification demo with a restricted test user and truthful portfolio material.

**Architecture:** Pin the upstream release in a protected baseline branch, then customize a separate portfolio branch. Use the existing Laravel AI routes and services, with a curated single tenant/demo-data seeder and tenant-facing branding. Preserve the application’s BYOK design while making all outbound services inert by default.

**Tech Stack:** Laravel 12, PHP 8.2, Blade/Tabler, MySQL 8, existing InsulaCRM AI services, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-09-22-ai-leadflow-demo-design.md`

## Global Constraints

- Base implementation on upstream `v1.1.0` commit `9263924b816abef470869c8bac13695ab20d422d`.
- Use `baseline/insula-v1.1.0` for recovery and `portfolio/ai-leadflow-demo` for all product work.
- Keep the upstream MIT license and attribution intact; describe the result as a customized implementation/demo.
- Use only fictional real-estate data and do not publish an administrator credential.
- Default mail to `log`; leave SMTP, SMS, payment, API access, outbound webhooks, and sequences inactive.
- Do not add an AI API key, purchase a service, create a provider account, or deploy n8n.
- Stop before any action requiring a new account, login, payment method, domain, or API key.

## Review Focus

- A restricted demo user must be unable to reach settings, team management, API, webhooks, backups, plugins, or other administrative pages.
- A missing AI key must render an explicit configuration-needed state, never a false successful AI result.
- DNC lead records must suppress quick outreach actions and cannot be enrolled in a delivery sequence.
- The demo seed must contain exactly the curated fictional tenant and not leave the public administrator password documented.
- Deployment configuration must retain the non-delivery mail driver and no active outbound integration records.

---

### Task 1: Establish the recoverable Git history

**Files:**
- Modify: `.git/config` only through `git remote add upstream`
- Create: Git refs `baseline/insula-v1.1.0` and `portfolio/ai-leadflow-demo`

**Interfaces:**
- Consumes: `https://github.com/InsulaCRM/InsulaCRM`, tag `v1.1.0`
- Produces: a local and remote `upstream` remote, a pinned baseline branch, and an implementation branch

- [ ] **Step 1: Fetch the upstream tag and verify its peeled commit**

Run: `git ls-remote --tags https://github.com/InsulaCRM/InsulaCRM.git v1.1.0^{}`

Expected: `9263924b816abef470869c8bac13695ab20d422d`.

- [ ] **Step 2: Add the upstream remote without changing origin**

Run: `git remote add upstream https://github.com/InsulaCRM/InsulaCRM.git && git remote -v`

Expected: `origin` points to `ematirx/InsulaCRM` and `upstream` points to `InsulaCRM/InsulaCRM`.

- [ ] **Step 3: Create and push the recovery baseline**

Run: `git branch baseline/insula-v1.1.0 9263924b816abef470869c8bac13695ab20d422d && git push origin baseline/insula-v1.1.0`

Expected: remote branch resolves to the pinned commit.

- [ ] **Step 4: Create and push the implementation branch from the baseline**

Run: `git switch --create portfolio/ai-leadflow-demo baseline/insula-v1.1.0 && git push --set-upstream origin portfolio/ai-leadflow-demo`

Expected: implementation work is separated from `main` and the baseline.

- [ ] **Step 5: Commit the reference-only branch setup note**

Run: `git commit --allow-empty -m "chore: establish InsulaCRM portfolio baseline"`

Expected: the branch history records its purpose without altering upstream source.

### Task 2: Add a minimal curated demo dataset and restricted account

**Files:**
- Create: `database/seeders/AiLeadFlowDemoSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Create: `tests/Feature/AiLeadFlowDemoSeederTest.php`

**Interfaces:**
- Consumes: `Role`, `Tenant`, `User`, `Lead`, and the existing base seeder
- Produces: `AiLeadFlowDemoSeeder::run(): void`, callable from `DatabaseSeeder`

- [ ] **Step 1: Write the failing seeder test**

```php
public function test_ai_leadflow_demo_seed_creates_a_restricted_user_and_curated_leads(): void
{
    $this->seed(\Database\Seeders\AiLeadFlowDemoSeeder::class);

    $this->assertDatabaseHas('tenants', ['name' => 'AI LeadFlow CRM Demo']);
    $this->assertDatabaseHas('users', ['email' => 'demo.agent@aileadflow.test']);
    $this->assertSame(10, Lead::query()->count());
    $this->assertSame(3, Lead::where('temperature', 'hot')->count());
    $this->assertSame(3, Lead::where('temperature', 'warm')->count());
    $this->assertSame(4, Lead::where('temperature', 'cold')->count());
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test tests/Feature/AiLeadFlowDemoSeederTest.php`

Expected: FAIL because `AiLeadFlowDemoSeeder` does not exist.

- [ ] **Step 3: Implement deterministic, fictional seed data**

Create `AiLeadFlowDemoSeeder` that calls `BaseSeeder`, creates one tenant named `AI LeadFlow CRM Demo`, one non-admin agent user, and exactly ten named fictional leads. Use `example.test` emails and reserved test phone numbers; give the leads the 3/3/4 temperature distribution, source, budget, intent note, assigned owner, activity status, and next action stated in the spec.

- [ ] **Step 4: Make the demo account safely reusable**

Use the existing `agent` role and its existing permissions. Set `is_active` true, leave 2FA disabled for the demo login, and define the password only through a deployment secret/seed environment variable. Do not put a real administrator credential in code or documentation.

- [ ] **Step 5: Run the seeder test and relevant authorization tests**

Run: `php artisan test tests/Feature/AiLeadFlowDemoSeederTest.php tests/Feature/PolicyAuthorizationTest.php`

Expected: PASS.

- [ ] **Step 6: Commit the curated demo data**

Run: `git add database/seeders tests/Feature/AiLeadFlowDemoSeederTest.php && git commit -m "feat: add AI LeadFlow demo seed data"`

### Task 3: Make AI availability and safe defaults clear in the demo

**Files:**
- Modify: `resources/views/leads/show.blade.php` and the existing AI component/partial used by the lead detail screen
- Modify: `resources/views/settings/partials/ai.blade.php` only if status copy is shared there
- Create: `tests/Feature/AiDemoAvailabilityTest.php`
- Verify: `.env.example`, `app/Http/Controllers/AiController.php`, `app/Services/AiService.php`

**Interfaces:**
- Consumes: `AiService::isAvailable(): bool` and AI routes such as `ai.scoreLead`, `ai.draftFollowUp`, `ai.suggestTasks`
- Produces: a lead-detail status notice that accurately distinguishes unavailable AI from successful live AI output

- [ ] **Step 1: Write failing availability tests**

```php
public function test_demo_user_sees_ai_setup_notice_when_no_provider_is_configured(): void
{
    $response = $this->actingAs($this->demoAgent)->get(route('leads.show', $this->lead));

    $response->assertOk()->assertSee('Live AI is awaiting a provider key');
}

public function test_unconfigured_ai_follow_up_returns_a_clear_422_response(): void
{
    $response = $this->actingAs($this->demoAgent)->postJson(route('ai.draftFollowUp'), [
        'lead_id' => $this->lead->id,
        'type' => 'note',
    ]);

    $response->assertUnprocessable()->assertJsonPath('error', 'AI is not configured. Go to Settings > AI to set up.');
}
```

- [ ] **Step 2: Run the tests to verify the missing UI state**

Run: `php artisan test tests/Feature/AiDemoAvailabilityTest.php`

Expected: the JSON assertion passes with current behavior; the page notice assertion fails.

- [ ] **Step 3: Add a non-deceptive availability notice**

Render a visible lead-detail notice when `AiService::isAvailable()` is false: `Live AI is awaiting a provider key. Existing lead qualification data is demo data.` Keep the existing buttons but label their unavailable response clearly. Do not fabricate a score, message, or task as a live AI result.

- [ ] **Step 4: Verify all external delivery defaults remain inert**

Confirm `.env.example` retains `MAIL_MAILER=log`. Ensure the demo seeder creates no active webhook records and does not create SMTP, SMS, API-key, payment, or scheduled sequence configuration.

- [ ] **Step 5: Run availability, DNC, and AI route tests**

Run: `php artisan test tests/Feature/AiDemoAvailabilityTest.php tests/Feature/WhatsAppQuickActionTest.php`

Expected: PASS.

- [ ] **Step 6: Commit the safe AI presentation**

Run: `git add resources/views .env.example tests/Feature/AiDemoAvailabilityTest.php && git commit -m "feat: clarify demo AI availability"`

### Task 4: Apply demo branding and truthful portfolio documentation

**Files:**
- Modify: the existing tenant/logo setting or auth view used by `resources/views/layouts/auth.blade.php`
- Create: `PORTFOLIO.md`
- Modify: `README.md` or `CHANGELOG.md`
- Create: `tests/Feature/AiLeadFlowBrandingTest.php`

**Interfaces:**
- Consumes: demo tenant name and existing tenant branding fields
- Produces: branded login copy, preserved attribution, bilingual portfolio material, capture checklist

- [ ] **Step 1: Write the failing branding test**

```php
public function test_login_page_shows_ai_leadflow_demo_branding(): void
{
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('AI LeadFlow CRM Demo');
}
```

- [ ] **Step 2: Run the branding test to verify it fails**

Run: `php artisan test tests/Feature/AiLeadFlowBrandingTest.php`

Expected: FAIL because the login page still shows InsulaCRM-only branding.

- [ ] **Step 3: Add tenant-safe demo branding**

Update the existing auth/template branding path so this installation visibly says `AI LeadFlow CRM Demo` without removing the upstream attribution or license. Keep the application’s original copyright and `LICENSE` file unchanged.

- [ ] **Step 4: Add the portfolio source document**

Create `PORTFOLIO.md` with the exact English portfolio title, Chinese translation, accurate customized-implementation disclosure, completed-work list, AI-key limitation, screenshot checklist, and placeholders for final HTTPS URL and restricted demo credential. Do not list a credential until deployment creates it.

- [ ] **Step 5: Add repository attribution**

Add a short `README.md` or `CHANGELOG.md` entry: `AI LeadFlow CRM Demo is a customized implementation and portfolio demo based on MIT-licensed InsulaCRM. Original copyright notices and LICENSE are retained.`

- [ ] **Step 6: Run the branding test and inspect the documents**

Run: `php artisan test tests/Feature/AiLeadFlowBrandingTest.php && git diff --check`

Expected: PASS and no whitespace errors.

- [ ] **Step 7: Commit branding and portfolio material**

Run: `git add README.md CHANGELOG.md PORTFOLIO.md resources/views tests/Feature/AiLeadFlowBrandingTest.php && git commit -m "docs: describe AI LeadFlow demo implementation"`

### Task 5: Verify locally and prepare deployment handoff

**Files:**
- Modify: `PORTFOLIO.md` only after a real deployment URL and restricted account exist
- Verify: `docker-compose.yml`, `.env.example`, `README.md`

**Interfaces:**
- Consumes: implementation branch, Docker/Laravel environment, restricted demo account
- Produces: verified deployment-ready source and a clearly bounded user-action handoff when an account or payment is required

- [ ] **Step 1: Start the local stack with a temporary test database**

Run: `docker compose up -d --build`

Expected: app, nginx, MySQL, and Redis report healthy. If Docker is unavailable, document the exact local blocker without changing deployment scope.

- [ ] **Step 2: Run migrations and the curated demo seeder**

Run: `docker compose exec app php artisan migrate:fresh --seed`

Expected: one demo tenant and ten curated leads are available.

- [ ] **Step 3: Run the required verification suite**

Run: `docker compose exec app php artisan test tests/Feature/AiLeadFlowDemoSeederTest.php tests/Feature/AiDemoAvailabilityTest.php tests/Feature/AiLeadFlowBrandingTest.php tests/Feature/PolicyAuthorizationTest.php`

Expected: PASS.

- [ ] **Step 4: Perform a manual safe-flow check**

Open the local login page; sign in as the restricted demo user; check Dashboard, Leads, one Hot lead, the AI scoring/follow-up/task UI, and the missing-key notice. Confirm Settings and external delivery routes are inaccessible.

- [ ] **Step 5: Inspect deployment eligibility without creating an account**

Check only already-connected deployment services for HTTPS, PHP 8.2, MySQL, persistent storage, and background-process support. Do not create a site or enter billing.

- [ ] **Step 6: Stop for authorization if a deployment account is absent**

Report the exact provider/account action needed. Do not register, log in, add a payment method, buy a domain, or deploy until the user completes the required authorization.

- [ ] **Step 7: After an authorized deployment, update only final portfolio facts**

Add the actual HTTPS URL, restricted credential, screenshot filenames, deployment date, and completed/remaining items to `PORTFOLIO.md`; then commit with `git commit -am "docs: record deployed demo access"`.

## Self-review

- Spec coverage: Tasks 1–5 cover recovery history, curated data, roles, AI state, outbound safety, branding/attribution, documentation, local validation, and authorization-gated deployment.
- Placeholder scan: deployment URL and credential are deliberately post-deployment facts and are named as such; no implementation task depends on an unspecified interface.
- Type consistency: only existing Laravel models/routes are consumed; the sole new interface is `AiLeadFlowDemoSeeder::run(): void`.
- Review focus: each listed risk has a proposed owning test or manual verification step.
