# AI LeadFlow CRM Demo Design

## Purpose

Create a public, safe-to-test portfolio demonstration named **AI LeadFlow CRM Demo** for the Upwork project **AI-Powered Lead Qualification & Follow-Up CRM**. It demonstrates workflow implementation on a customized MIT-licensed InsulaCRM installation; it does not claim the CRM was developed from scratch.

## Baseline and ownership

- Fork: `ematirx/InsulaCRM`.
- Upstream: `https://github.com/InsulaCRM/InsulaCRM`.
- Immutable baseline: upstream `v1.1.0`, commit `9263924b816abef470869c8bac13695ab20d422d`.
- Baseline branch: `baseline/insula-v1.1.0`.
- Implementation branch: `portfolio/ai-leadflow-demo`.
- The upstream MIT `LICENSE`, copyright notices, and attribution remain unchanged.
- The customized README/CHANGELOG must state that this is a customized implementation and demo built on MIT-licensed InsulaCRM, rather than a wholly original CRM.

## Product boundary

InsulaCRM is a real-estate CRM. The demo keeps that domain so its lead fields, scoring rationale, property data, and pipeline remain internally consistent. It does not claim broad multi-industry CRM capabilities.

The demo must retain the existing login experience, dashboard, leads, and role-based access. It may change tenant-facing name, logo/text assets, demo data, and safe defaults only where the change is required for the portfolio flow.

## Demo journey

1. A visitor reaches an HTTPS public URL and sees the normal login page branded **AI LeadFlow CRM Demo**.
2. They sign in with one restricted demo user and enter the dashboard.
3. They open Leads and filter or inspect 8–12 clearly fictional records covering Hot, Warm, and Cold qualification, multiple source channels, budgets, intent levels, and follow-up states.
4. On an eligible lead they can view existing AI scoring, generate or view an AI follow-up draft, and view AI task suggestions when a provider key has been configured.
5. When no AI key is configured, the UI continues to load and explains that live generation is awaiting a BYOK provider key; no synthetic AI output is passed off as live AI output.

## Roles and data

- Create a dedicated restricted Demo Agent role/user. It can view the curated demo tenant and make only safe lead/activity changes needed for exploration.
- It cannot access settings, user management, API keys, SMTP, webhooks, plugin installation, exports containing non-demo data, backups, or destructive bulk actions.
- The existing administrator is reserved for installation and maintenance and is never published as the customer demo credential.
- All leads, properties, contacts, and activities are fictional. Seed data deliberately includes 8–12 visible leads, with at least three Hot, three Warm, and two Cold examples. Each has a stated business context, budget range, source, owner, status, and next-action state.

## AI configuration and safety

The implementation uses InsulaCRM's existing provider-agnostic `AiService` and its existing lead-scoring, follow-up-draft, and task-suggestion routes. It does not introduce a paid provider, submit a key on the user's behalf, or make a provider account.

The deployment defaults to a non-delivery mail driver (`log`), has no SMTP credentials, has no SMS gateway credentials, and contains no active outbound webhook. No public API key, scheduled outbound sequence, real payment configuration, or customer data is included. n8n remains documented as a future integration only; it is not deployed in this MVP.

## Branding and documentation

The login and visible tenant branding use **AI LeadFlow CRM Demo**. The portfolio title is **AI-Powered Lead Qualification & Follow-Up CRM** (Chinese: **AI 驱动的线索筛选与跟进 CRM**).

The project includes a `PORTFOLIO.md` (or equivalent README section) with:

- Public URL and restricted demo credentials after deployment.
- A bilingual list of the actual customization and implementation work: environment setup, branding, role restriction, fictional demo data, AI configuration placeholders, safety isolation, and deployment.
- An English portfolio description that explicitly calls it a customized implementation/demo based on MIT-licensed InsulaCRM.
- A capture checklist for Login, Dashboard, AI Lead Scoring, AI Follow-Up, and Automation/Workflow screenshots.

## Deployment

Before publishing, verify the application locally using its Laravel test suite and a manual login flow. The deployment target must be low-cost, HTTPS-capable, and usable from a normal public URL. Reuse an already connected service only when it supports Laravel, MySQL, queued jobs, and persistent storage.

If publication requires a new account, browser login, payment method, domain purchase, an API key, or another authorization, stop at that exact action and ask the user to complete it. Do not use a free-trial or paid service without the user's action.

## Acceptance criteria

- The Fork has an upstream remote, the pinned baseline branch, and a separate implementation branch.
- The public demo begins at the branded login page and uses HTTPS.
- The restricted demo account can view the curated fictional leads and safe AI workflow UI.
- The administrator account and all external-delivery settings remain private and disabled.
- No AI API key is required for the basic demonstration; absence is clearly represented.
- License and attribution remain intact and portfolio wording accurately describes a customized implementation.
- The delivery notes include the URL, restricted credential, exact completed work, limitations, screenshot checklist, and any user action still required.

## Excluded from this MVP

- A new CRM product or rewrite of InsulaCRM.
- Real customer records or real prospect outreach.
- Paid domains, paid deployment, paid API usage, or purchased services.
- A live n8n/Make/Zapier agent workflow.
- Changes to upstream InsulaCRM or direct development on `main`.
