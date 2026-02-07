<?php

namespace App\Observers;

use App\Models\Common\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GenericActivityObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $label = $this->label($model);
        $properties = $this->baseProperties($model);
        $properties['attributes'] = $model->getAttributes();

        ActivityLog::log(
            $model,
            $this->eventName($model, 'created'),
            "{$label} was created",
            $properties
        );
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $properties = $this->baseProperties($model);
        $properties['changes'] = [];

        foreach ($changes as $key => $newValue) {
            $properties['changes'][$key] = [
                'old' => $model->getOriginal($key),
                'new' => $newValue,
            ];
        }

        ActivityLog::log(
            $model,
            $this->eventName($model, 'updated'),
            "{$this->label($model)} was updated",
            $properties
        );
    }

    private function eventName(Model $model, string $action): string
    {
        $base = Str::snake(class_basename($model));

        return "{$base}_{$action}";
    }

    private function label(Model $model): string
    {
        $base = Str::title(str_replace('_', ' ', Str::snake(class_basename($model))));
        $id = $model->getKey();

        return $id ? "{$base} #{$id}" : $base;
    }

    private function baseProperties(Model $model): array
    {
        $properties = [
            'model' => get_class($model),
            'id' => $model->getKey(),
        ];

        if (isset($model->company_id)) {
            $properties['company_id'] = $model->company_id;
        }

        return $properties;
    }
}
