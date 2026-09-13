<?php

namespace App;

enum NotificationType: string
{
    case RecurringReminder = 'recurring_reminder';
    case BudgetAlert = 'budget_alert';
    case AnomalyAlert = 'anomaly_alert';
    case GoalMilestone = 'goal_milestone';
}
