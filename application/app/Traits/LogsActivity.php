<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    /**
     * Boot the trait and register Eloquent event listeners.
     */
    protected static function bootLogsActivity(): void
    {
        static::created(function (Model $model) {
            static::logModelEvent($model, 'created', 'Created model '.class_basename($model).' #'.$model->getKey(), [
                'attributes' => $model->getFilteredAttributes($model->getAttributes()),
            ]);
        });

        static::updated(function (Model $model) {
            $changes = $model->getChanges();
            $original = array_intersect_key($model->getOriginal(), $changes);

            // Filter out hidden or timestamp attributes if needed
            $oldDiff = $model->getFilteredAttributes($original);
            $newDiff = $model->getFilteredAttributes($changes);

            if (! empty($newDiff)) {
                static::logModelEvent($model, 'updated', 'Updated model '.class_basename($model).' #'.$model->getKey(), [
                    'old' => $oldDiff,
                    'attributes' => $newDiff,
                ]);
            }
        });

        static::deleted(function (Model $model) {
            static::logModelEvent($model, 'deleted', 'Deleted model '.class_basename($model).' #'.$model->getKey(), [
                'old' => $model->getFilteredAttributes($model->getAttributes()),
            ]);
        });
    }

    /**
     * Helper to log Eloquent model events.
     */
    protected static function logModelEvent(Model $model, string $event, string $description, array $properties): void
    {
        ActivityLog::record(
            event: $event,
            description: $description,
            subject: $model,
            properties: $properties
        );
    }

    /**
     * Filter out password, remember_token, or sensitive fields.
     */
    protected function getFilteredAttributes(array $attributes): array
    {
        $sensitive = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

        return array_diff_key($attributes, array_flip($sensitive));
    }
}
