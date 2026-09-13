export * from './auth';
export * from './navigation';
export * from './ui';
export * from './transaction';

export interface Category {
    id: number;
    name: string;
    name_ar: string;
    type: 'income' | 'expense';
    color: string | null;
    icon: string | null;
    is_default: boolean;
}

export interface Budget {
    id: number;
    category_id: number;
    amount: number;
    month: string;
    category: Category;
    spent: number;
}

export interface FinancialGoal {
    id: number;
    title: string;
    title_ar: string;
    description: string | null;
    target_amount: number;
    current_amount: number;
    deadline: string | null;
    category_id: number | null;
    status: 'active' | 'completed' | 'abandoned';
    category: Category | null;
}
