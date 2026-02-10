<?php

namespace App\Observers;

use App\Models\Common\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

abstract class BaseActivityObserver
{
    protected function logCreated(Model $model): void
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

    protected function logUpdated(Model $model): void
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

    protected function eventName(Model $model, string $action): string
    {
        $base = Str::snake(class_basename($model));

        return "{$base}_{$action}";
    }

    protected function label(Model $model): string
    {
        $base = Str::title(str_replace('_', ' ', Str::snake(class_basename($model))));
        $id = $model->getKey();

        return $id ? "{$base} #{$id}" : $base;
    }

    protected function baseProperties(Model $model): array
    {
        $properties = [
            'model' => get_class($model),
            'id' => $model->getKey(),
        ];

        if (isset($model->company_id)) {
            $properties['company_id'] = $model->company_id;
        }

        foreach ([
            'order_number',
            'invoice_number',
            'quote_number',
            'delivery_note_number',
            'credit_note_number',
            'document_number',
            'sequence',
            'number',
            'status_id',
            'payment_status_id',
        ] as $key) {
            if (isset($model->{$key})) {
                $properties[$key] = $model->{$key};
            }
        }

        return $properties;
    }
}
