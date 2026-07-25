<?php

namespace App\services\Expert;

use App\Models\ProviderSchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CalendarServices
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getExpertSchedule()
    {
        $expert = Auth::user();

        return ProviderSchedule::where('entity_type', 'expert')
            ->where('entity_id', $expert->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    public function updateExpertSchedule(array $data)
    {
        $expert = Auth::user();

        return DB::transaction(function () use ($expert, $data) {
            // نمسح الجدول القديم بالكامل ونستبدله بالجديد (Replace كامل، مش إضافة)
            ProviderSchedule::where('entity_type', 'expert')
                ->where('entity_id', $expert->id)
                ->delete();

            $rows = collect($data['schedule'])->map(fn ($slot) => [
                'entity_type'           => 'expert',
                'entity_id'             => $expert->id,
                'day_of_week'           => $slot['day_of_week'],
                'start_time'            => $slot['start_time'],
                'end_time'              => $slot['end_time'],
                'slot_duration_minutes' => $slot['slot_duration_minutes'],
                'is_active'             => $slot['is_active'] ?? true,
                'created_at'            => now(),
                'updated_at'            => now(),
            ])->toArray();

            ProviderSchedule::insert($rows);

            return ProviderSchedule::where('entity_type', 'expert')
                ->where('entity_id', $expert->id)
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();
        });
    }
}
