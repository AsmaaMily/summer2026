<?php

namespace App\Models;

use App\PaymentMethod;
use App\TransactionSource;
use App\TransactionType;
use Database\Factories\TransactionFactory;
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
 * @property int|null $recurring_transaction_id
 * @property int $amount
 * @property string $type
 * @property string $date
 * @property string|null $notes
 * @property string $payment_method
 * @property bool $is_recurring
 * @property string $source
 * @property bool $is_flagged_duplicate
 * @property string|null $flag_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Category $category
 * @property-read RecurringTransaction|null $recurringTransaction
 * @property-read Collection<Attachment> $attachments
 */
#[Fillable(['user_id', 'category_id', 'recurring_transaction_id', 'amount', 'type', 'date', 'notes', 'payment_method', 'is_recurring', 'source', 'is_flagged_duplicate', 'flag_reason'])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'payment_method' => PaymentMethod::class,
            'source' => TransactionSource::class,
            'amount' => 'integer',
            'date' => 'date',
            'is_recurring' => 'boolean',
            'is_flagged_duplicate' => 'boolean',
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
     * @return BelongsTo<RecurringTransaction, $this>
     */
    public function recurringTransaction(): BelongsTo
    {
        return $this->belongsTo(RecurringTransaction::class);
    }

    /**
     * @return HasMany<Attachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}
