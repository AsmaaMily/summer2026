<?php

namespace App\Models;

use App\PaymentMethod;
use App\RecurringFrequency;
use App\TransactionType;
use Database\Factories\RecurringTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $category_id
 * @property int $amount
 * @property string $type
 * @property string $frequency
 * @property int|null $day_of_month
 * @property int|null $day_of_week
 * @property string $start_date
 * @property string|null $end_date
 * @property string|null $notes
 * @property string $payment_method
 * @property bool $is_active
 * @property Carbon|null $last_reminded_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Category $category
 * @property-read Collection<Transaction> $transactions
 */
#[Fillable(['user_id', 'category_id', 'amount', 'type', 'frequency', 'day_of_month', 'day_of_week', 'start_date', 'end_date', 'notes', 'payment_method', 'is_active', 'last_reminded_at'])]
class RecurringTransaction extends Model
{
    /** @use HasFactory<RecurringTransactionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'frequency' => RecurringFrequency::class,
            'payment_method' => PaymentMethod::class,
            'amount' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'day_of_month' => 'integer',
            'day_of_week' => 'integer',
            'is_active' => 'boolean',
            'last_reminded_at' => 'datetime',
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
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
