<?php

namespace App\Models;

use App\GoalStatus;
use Database\Factories\FinancialGoalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $title_ar
 * @property string|null $description
 * @property int $target_amount
 * @property int $current_amount
 * @property string|null $deadline
 * @property int|null $category_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Category|null $category
 */
#[Fillable(['user_id', 'title', 'title_ar', 'description', 'target_amount', 'current_amount', 'deadline', 'category_id', 'status'])]
class FinancialGoal extends Model
{
    /** @use HasFactory<FinancialGoalFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => GoalStatus::class,
            'target_amount' => 'integer',
            'current_amount' => 'integer',
            'deadline' => 'date',
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
}
