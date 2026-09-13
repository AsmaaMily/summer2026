export interface Category {
    id: number;
    name: string;
    name_ar: string;
    type: 'income' | 'expense';
    color: string | null;
    icon: string | null;
    is_default: boolean;
}

export interface Transaction {
    id: number;
    amount: number;
    type: 'income' | 'expense';
    date: string;
    notes: string | null;
    payment_method: 'cash' | 'card';
    is_recurring: boolean;
    category: Category;
}

export interface CategoryBreakdown {
    category_id: number;
    category_name: string;
    category_name_ar: string;
    color: string | null;
    amount: number;
}

export interface RecurringTransaction {
    id: number;
    amount: number;
    type: 'income' | 'expense';
    frequency: string;
    notes: string | null;
    category: { id: number; name_ar: string };
}

export interface DashboardProps {
    netBalance: number;
    totalIncome: number;
    totalExpenses: number;
    categoryBreakdown: CategoryBreakdown[];
    recentTransactions: Transaction[];
    recurringUpcoming: RecurringTransaction[];
    selectedMonth: string;
}

export interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

export interface TransactionFilters {
    search: string;
    type: string;
    category_id: string;
    payment_method: string;
    date_from: string;
    date_to: string;
}

export interface TransactionsProps {
    transactions: Paginator<Transaction>;
    categories: Category[];
    filters: TransactionFilters;
}
