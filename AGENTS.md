

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
- Budget creation with hard limits
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
- Budget creation with hard limits
- Bank/account sync
- Receipt/photo scanning
- Voice input
- Social login
- Data import
- Mobile apps (native)
- Multi-language support


#اختيار التقنيات
- Laravel
- Inertia (laravel svelte template)
- TailwindCSS