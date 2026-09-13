<?php

namespace App\Models;

use App\TransactionType;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property string $name_ar
 * @property string $type
 * @property string|null $icon
 * @property string|null $color
 * @property string|null $description
 * @property int $sort_order
 * @property bool $is_default
 * @property bool $is_archived
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Collection<Transaction> $transactions
 * @property-read Collection<RecurringTransaction> $recurringTransactions
 * @property-read Collection<Budget> $budgets
 * @property-read Collection<FinancialGoal> $financialGoals
 */
#[Fillable(['user_id', 'name', 'name_ar', 'type', 'icon', 'color', 'description', 'sort_order', 'is_default', 'is_archived'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'sort_order' => 'integer',
            'is_default' => 'boolean',
            'is_archived' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return HasMany<RecurringTransaction, $this>
     */
    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    /**
     * @return HasMany<Budget, $this>
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * @return HasMany<FinancialGoal, $this>
     */
    public function financialGoals(): HasMany
    {
        return $this->hasMany(FinancialGoal::class);
    }
}
