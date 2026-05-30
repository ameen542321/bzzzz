<?php

namespace App\Services;

use App\Models\Notification;

class NotificationQueryService
{
    public static function getUnreadCountFor($userId): int
    {
        if (!$userId) return 0;

        return Notification::unreadCountFor($userId);
    }

    public static function getLatestFor($userId, int $limit = 5)
    {
        if (!$userId) return collect([]);

        return Notification::orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->filter(function ($n) use ($userId) {
                if ($n->target_type === 'all') return true;
                if (in_array($userId, $n->target_ids ?? [])) return true;
                return false;
            });
    }
}
