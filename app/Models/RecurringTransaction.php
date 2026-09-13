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
     * Resolve the next occurrence of this recurring transaction on or after the given date.
     */
    public function nextDueDate(?Carbon $from = null): ?Carbon
    {
        $from = ($from ?? Carbon::today())->copy()->startOfDay();
        $start = Carbon::parse($this->start_date)->startOfDay();
        $cursor = $from->greaterThan($start) ? $from->copy() : $start->copy();

        $next = match ($this->frequency) {
            RecurringFrequency::Daily => $cursor->copy(),
            RecurringFrequency::Weekly => $cursor->copy()->addDays(
                ((int) $this->day_of_week - $cursor->dayOfWeek + 7) % 7
            ),
            RecurringFrequency::Monthly => $this->nextMonthlyOccurrence($cursor),
            RecurringFrequency::Yearly => $this->nextYearlyOccurrence($start, $cursor),
        };

        if ($this->end_date !== null && $next->greaterThan(Carbon::parse($this->end_date)->endOfDay())) {
            return null;
        }

        return $next;
    }

    private function nextMonthlyOccurrence(Carbon $cursor): Carbon
    {
        $day = (int) $this->day_of_month;
        $candidate = $this->dayInMonth($cursor, $day);

        if ($candidate->lessThan($cursor)) {
            $candidate = $this->dayInMonth($cursor->copy()->startOfMonth()->addMonthNoOverflow(), $day);
        }

        return $candidate;
    }

    private function nextYearlyOccurrence(Carbon $start, Carbon $cursor): Carbon
    {
        $candidate = $this->dayInMonth($cursor->copy()->month($start->month), $start->day);

        if ($candidate->lessThan($cursor)) {
            $candidate = $this->dayInMonth($candidate->copy()->addYearNoOverflow(), $start->day);
        }

        return $candidate;
    }

    private function dayInMonth(Carbon $month, int $day): Carbon
    {
        $day = min($day, $month->copy()->endOfMonth()->day);

        return $month->copy()->startOfMonth()->addDays($day - 1)->startOfDay();
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
