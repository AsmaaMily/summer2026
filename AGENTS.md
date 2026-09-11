

#فكرة المشروع
تطبيق لتتبع المصاريف مدعوم بالذكاء الاصطناعي

# Project Overview
- **Type**: AI-powered personal expense tracking web application
- **Target Users**: Individuals
- **Monetization**: Free (no monetization)
- **Timeline**: 1 month
- **UI Language**: Arabic only (RTL support required)
- **Currency**: SAR (Saudi Riyal) - single currency

---

# المتطلبات وترتيبها

## 1. Authentication & User Management
- Email/password sign up and login
- Session management
- Password reset flow
- User profile management

## 2. Transaction Management
### Transaction Fields
- Amount
- Type (income / expense)
- Category
- Date
- Notes
- Payment method (Cash / Card)
- Recurring flag
- Attachments

### Operations
- Add transaction (manual entry)
- Edit transaction
- Delete transaction
- View transaction list with search & filter (by date, category, amount, payment method)

## 3. Categories
- Standard default categories: Food, Transport, Housing, Entertainment, Health, Education, Shopping, Utilities, Salary, Other
- User can add custom categories
- Separate categories for income and expense

## 4. AI Assistant (Agentic Chat)
### Core Capabilities
- **Finance Q&A**: Answer questions about user's spending, income, and financial habits
- **Add transactions via chat**: User can create transactions through natural language
- **Edit/delete transactions via chat**: AI can modify or remove existing transactions
- **Budgeting advice**: Provide personalized saving and spending tips
- **Anomaly detection**:
  - Flag unusual spending patterns (e.g., spending spike in a category)
  - Detect potential duplicate transactions
- **Auto-categorization**: Automatically suggest category when a new transaction is entered

### Constraints
- AI actions are limited to: add, edit, delete transactions
- AI should confirm before executing destructive actions (delete)

## 5. Reports & Visualizations
- **Monthly summary**: Income vs expenses overview
- **Category breakdown**: Pie charts showing spending distribution
- **Bar charts**: Compare spending across categories or months
- **Trend lines**: Line charts showing spending/income trends over time
- **Spending insights**: AI-generated insights and patterns

## 6. Recurring Transactions
- Define recurring transactions (e.g., rent, salary, subscriptions)
- Manual reminder: notify user but do NOT auto-create transactions
- User confirms before recording

## 7. Data Sync & Storage
- Cloud-based data storage and sync
- Data accessible across devices for the same user
- Real-time or near-real-time sync

## 8. Data Export
- Export transactions as CSV
- Export formatted PDF reports

## 9. UI/UX Requirements
- Arabic RTL layout
- Light mode + Dark mode toggle
- Responsive web design (mobile-friendly)
- Clean, intuitive interface
- Chat interface for AI assistant (prominent placement)

## 10. Notifications
- In-app reminders for recurring transactions
- Anomaly alerts (unusual spending, duplicates)

---

# MoSCoW Prioritization

## Must Have (Critical for MVP - 1 month launch)
### Authentication
- Email/password sign up and login
- Session management

### Transaction Management
- Add transaction (manual entry)
- Edit transaction
- Delete transaction
- View transaction list
- Core fields: Amount, Type (income/expense), Category, Date

### Categories
- Standard default categories (Food, Transport, Housing, Entertainment, Health, Education, Shopping, Utilities, Salary, Other)
- Separate categories for income and expense

### AI Assistant
- Finance Q&A (answer questions about spending and income)
- Add transactions via chat (natural language)
- Auto-categorization (suggest category on entry)

### Reports
- Monthly summary (Income vs expenses overview)

### UI/UX
- Arabic RTL layout
- Light mode
- Responsive web design (mobile-friendly)
- Chat interface for AI assistant (prominent placement)

### Data
- Cloud-based data storage
- Basic sync across devices

---

## Should Have (Important for good UX)
### Authentication
- Password reset flow
- User profile management

### Transaction Management
- Search & filter transactions (by date, category, amount, payment method)
- Payment method tracking (Cash / Card)
- Notes field
- Recurring flag

### Categories
- User can add custom categories

### AI Assistant
- Edit/delete transactions via chat
- Budgeting advice (personalized tips)

### Reports
- Category breakdown (pie charts showing spending distribution)

### Recurring Transactions
- Define recurring transactions
- Manual reminder (notify user, don't auto-create)

### UI/UX
- Dark mode toggle

### Data Export
- Export transactions as CSV

---

## Could Have (Nice to have if time permits)
### Transaction Management
- Attachments support

### AI Assistant
- Anomaly detection (unusual spending patterns, duplicate detection)

### Reports
- Bar charts (compare spending across categories or months)
- Trend lines (line charts showing trends over time)
- Spending insights (AI-generated insights and patterns)

### Data Export
- Export formatted PDF reports

### Notifications
- Anomaly alerts (unusual spending, duplicates)

---

## Won't Have (Out of Scope for now)
- Multiple currencies
- Bank/account sync
- Receipt/photo scanning
- Voice input
- Social login
- Data import
- Mobile apps (native)
- Multi-language support

---

# Out of Scope (for now)
- Multiple currencies
- Bank/account sync
- Receipt/photo scanning
- Voice input
- Social login
- Data import
- Mobile apps (native)
- Multi-language support

# اختيار التقنيات
- Laravel
- Inertia (laravel svelte template)
- TailwindCSS

# Design system

Mobile first
read DESIGN.md file

# Frontend Pages

## Pages to Build

### 1. الصفحة الرئيسية (Dashboard)
- رسوم بيانية للتدفق النقدي (Cash Flow) ورسوم بيانية متنوعة أخرى
- تنبيهات مبكرة من خوارزميات التنبؤ حول احتمالية تجاوز الميزانية المحددة في الأيام القادمة
- ملخص بصري لإجمالي الدخل والمصروفات للشهر الحالي
- واجهة Widgets لآخر المعاملات المالية والالتزامات والفواتير الدورية
- عرض الأهداف المالية الحالية مع نسبة التقدم لكل هدف

### 2. سجل المعاملات (Transaction List)
- القائمة الكاملة للمعاملات المالية السابقة
- أدوات تصفية وبحث (حسب التاريخ، الفئة، المبلغ، طريقة الدفع)
- أزرار لإضافة معاملة جديدة (دخل أو صرف) مع إمكانية التصنيف التلقائي عبر AI
- إمكانية تعديل معاملة موجودة
- ترتيب حسب التاريخ أو المبلغ
- إجراءات جماعية (حذف)
- ترقيم الصفحات أو التمرير اللانهائي

### 3. الصفحة الشخصية (Profile)
- إدارة الفئات وتخصيصها (إضافة، تعديل، حذف فئات مخصصة)
- إمكانية تغيير الراتب الشهري
- إعداد التقارير وتصديرها (CSV, PDF)
- تبويبات منفصلة لفئات الدخل والمصروفات

### 4. صفحة المساعد الذكي (AI Assistant)
- واجهة المحادثة الرئيسية مع المساعد الذكي
- سجل الرسائل السابقة
- إجراءات سريعة (إضافة معاملة، الاستفسار عن المصروفات)
- مؤشرات الكتابة
- تأكيد من AI قبل الإجراءات التدميرية (الحذف)
- شاشة التحليلات والرؤى (Insights) التي يقترحها النموذج بناءً على اكتشاف أنماط صرف معينة

### 5. صفحة الإعداد والترحيب (Onboarding & Welcome)
- الصفحة الأولى للزائر الجديد (أول مرة يستخدم الموقع)
- تسجيل الدخول أو إنشاء حساب جديد
- جمع البيانات الضرورية: اسم المستخدم، الراتب الشهري (يمكن تعديله لاحقًا في الصفحة الشخصية)
- اختيار العملة (حاليًا: ريال سعودي فقط - لا يمكن اختيار غيرها)

### 6. الميزانية والأهداف (Budget & Goals)
- عرض تقسيم الميزانية للفئات المحددة
- إنشاء أهداف مالية محددة
- تتبع الأهداف ومراقبة نسبة التقدم فيها
- تنبيهات عند الاقتراب من حدود الميزانية

# Intructions

1. Before doing anything related to a framework or tool ensure you have enough information about it read the docs using the context7 mcp server

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.4. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record durable rules with `record-rule` so the next agent or teammate inherits them instead of working them out again. Pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Always use `record-rule`, never your native memory or notes tool — native memory is personal and session-scoped; only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Test every code change by adding or updating a test.
- Run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-svelte-development` when working with Inertia Svelte client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

=== inertia-svelte/core rules ===

# Inertia + Svelte

- IMPORTANT: Activate `inertia-svelte-development` when working with Inertia Svelte client-side patterns.

</laravel-boost-guidelines>
