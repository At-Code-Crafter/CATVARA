<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'properties' => 'array',
    ];

    public function subject()
    {
        return $this->morphTo();
    }

    public function causer()
    {
        return $this->morphTo();
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company\Company::class);
    }

    /**
     * Centralized logging helper
     */
    public static function log($subject, $event, $description, array $properties = [], $causer = null)
    {
        $causer = $causer ?? \Illuminate\Support\Facades\Auth::user();
        $companyId = active_company_id();

        return self::create([
            'company_id' => $companyId,
            'causer_type' => $causer ? get_class($causer) : null,
            'causer_id' => $causer ? $causer->id : null,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'event' => $event,
            'description' => $description,
            'properties' => $properties,
        ]);
    }
}
