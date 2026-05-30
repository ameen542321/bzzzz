<?php

namespace App\Services;

use App\Models\Log;
use App\Models\Store;
use App\Models\User;

class LogService
{
    public function add($action, $description, $model = null, $details = null)
    {
        $actor = auth()->user() ?? auth('accountant')->user();

        $storeId = null;
        if ($model && isset($model->store_id)) {
            $storeId = (int) $model->store_id;
        }

        if (! $storeId && $actor && isset($actor->store_id)) {
            $storeId = (int) $actor->store_id;
        }

        if (! $storeId && $actor && isset($actor->current_store_id)) {
            $storeId = (int) $actor->current_store_id;
        }

        $ownerUserId = null;
        if ($actor instanceof User) {
            $ownerUserId = (int) $actor->id;
        } elseif ($storeId) {
            $ownerUserId = (int) (Store::where('id', $storeId)->value('user_id') ?? 0);
        }

        if (is_string($details)) {
            $decoded = json_decode($details, true);
            $details = is_array($decoded) ? $decoded : ['message' => $details];
        } elseif ($details === null) {
            $details = [];
        } elseif (! is_array($details)) {
            $details = ['data' => $details];
        }

        return Log::create([
            'store_id'    => $storeId,
            'user_id'     => $ownerUserId ?: null,
            'actor_type'  => $actor ? get_class($actor) : null,
            'actor_id'    => $actor?->id,
            'action'      => (string) $action,
            'description' => (string) $description,
            'model_type'  => $model ? get_class($model) : null,
            'model_id'    => $model?->id,
            'details'     => $details,
            'ip'          => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }
}
