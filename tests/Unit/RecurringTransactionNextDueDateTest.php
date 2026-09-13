<?php

namespace Tests\Unit;

use App\Models\RecurringTransaction;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RecurringTransactionNextDueDateTest extends TestCase
{
    private function recurring(array $attributes): RecurringTransaction
    {
        return new RecurringTransaction($attributes);
    }

    private function today(): Carbon
    {
        return Carbon::create(2026, 9, 13);
    }

    public function test_daily_returns_the_current_day(): void
    {
        $recurring = $this->recurring([
            'frequency' => 'daily',
            'start_date' => '2026-01-01',
        ]);

        $this->assertSame('2026-09-13', $recurring->nextDueDate($this->today())->format('Y-m-d'));
    }

    public function test_monthly_returns_upcoming_day_in_current_month(): void
    {
        $recurring = $this->recurring([
            'frequency' => 'monthly',
            'day_of_month' => 20,
            'start_date' => '2026-01-01',
        ]);

        $this->assertSame('2026-09-20', $recurring->nextDueDate($this->today())->format('Y-m-d'));
    }

    public function test_monthly_rolls_over_to_next_month_when_day_has_passed(): void
    {
        $recurring = $this->recurring([
            'frequency' => 'monthly',
            'day_of_month' => 5,
            'start_date' => '2026-01-01',
        ]);

        $this->assertSame('2026-10-05', $recurring->nextDueDate($this->today())->format('Y-m-d'));
    }

    public function test_monthly_clamps_day_to_end_of_short_month(): void
    {
        $recurring = $this->recurring([
            'frequency' => 'monthly',
            'day_of_month' => 31,
            'start_date' => '2026-01-01',
        ]);

        $this->assertSame('2026-09-30', $recurring->nextDueDate($this->today())->format('Y-m-d'));
    }

    public function test_weekly_returns_the_next_matching_weekday(): void
    {
        $recurring = $this->recurring([
            'frequency' => 'weekly',
            'day_of_week' => 3,
            'start_date' => '2026-01-01',
        ]);

        $this->assertSame('2026-09-16', $recurring->nextDueDate($this->today())->format('Y-m-d'));
    }

    public function test_weekly_returns_today_when_it_matches(): void
    {
        $recurring = $this->recurring([
            'frequency' => 'weekly',
            'day_of_week' => $this->today()->dayOfWeek,
            'start_date' => '2026-01-01',
        ]);

        $this->assertSame('2026-09-13', $recurring->nextDueDate($this->today())->format('Y-m-d'));
    }

    public function test_yearly_returns_next_anniversary(): void
    {
        $recurring = $this->recurring([
            'frequency' => 'yearly',
            'start_date' => '2020-12-01',
        ]);

        $this->assertSame('2026-12-01', $recurring->nextDueDate($this->today())->format('Y-m-d'));
    }

    public function test_future_start_date_is_respected(): void
    {
        $recurring = $this->recurring([
            'frequency' => 'monthly',
            'day_of_month' => 1,
            'start_date' => '2026-12-01',
        ]);

        $this->assertSame('2026-12-01', $recurring->nextDueDate($this->today())->format('Y-m-d'));
    }

    public function test_returns_null_when_after_end_date(): void
    {
        $recurring = $this->recurring([
            'frequency' => 'monthly',
            'day_of_month' => 20,
            'start_date' => '2026-01-01',
            'end_date' => '2026-08-31',
        ]);

        $this->assertNull($recurring->nextDueDate($this->today()));
    }
}
