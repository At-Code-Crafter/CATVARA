<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope that hides inactive (is_active = false) records by default.
 *
 * Bypass it with the model's `withInactive()` local scope, e.g.
 *   Product::withInactive()->find($id);
 * or the framework helper:
 *   Product::withoutGlobalScope(ActiveScope::class)->get();
 */
class ActiveScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Qualify the column so it stays unambiguous when joins are present
        // (e.g. DataTables ordering joins categories/brands, which also have is_active).
        $builder->where($model->getTable() . '.is_active', true);
    }
}
