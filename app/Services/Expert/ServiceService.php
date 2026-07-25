<?php

namespace App\Services\Expert;

use App\Models\PreBookingQuestion;
use App\Models\Service;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;

class ServiceService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    use ApiResponseTrait;

    // GET /expert/services
    public function index()
    {
        $expert   = Auth::user();
        $services = Service::where('provider_type', 'expert')
            ->where('provider_id', $expert->id)
            ->where('is_active', true)
            ->with('category')
            ->latest()
            ->paginate(10);

        return $this->sendResponse([
            'services'        => $services->items(),
            'nextPageUrl'     => $services->nextPageUrl(),
            'previousPageUrl' => $services->previousPageUrl(),
        ], 'Services retrieved successfully.');
    }

    // POST /expert/services
    public function store(array $input)
    {
        $expert  = Auth::user();
        $service = Service::create([
            'provider_type'             => 'expert',
            'provider_id'               => $expert->id,
            'category_id'               => $input['category_id'] ?? null,
            'name'                      => $input['name'],
            'description'               => $input['description'] ?? null,
            'price'                     => $input['price'],
            'duration_minutes'          => $input['duration_minutes'],
            'deposit_percent'           => $input['deposit_percent'] ?? 20.00,
            'cancellation_deadline_hrs' => $input['cancellation_deadline_hrs'] ?? 24,
            'gender_for'                => $input['gender_for'] ?? 'both',
        ]);

        return $this->sendResponse(['service' => $service], 'Service created successfully.');
    }

    // PUT /expert/services/{service}
    public function update(array $input, Service $service)
    {
        if (!$this->isOwner($service)) {
            return $this->sendError('غير مصرح.', 403);
        }

        $service->update($input);

        return $this->sendResponse(['service' => $service], 'Service updated successfully.');
    }

    // DELETE /expert/services/{service}
    public function destroy(Service $service)
    {
        if (!$this->isOwner($service)) {
            return $this->sendError('غير مصرح.', 403);
        }

        $service->update(['is_active' => false]);

        return $this->sendResponse([], 'Service deleted successfully.');
    }

    // POST /expert/services/{service}/instructions
    public function updateInstructions(array $input, Service $service)
    {
        if (!$this->isOwner($service)) {
            return $this->sendError('غير مصرح.', 403);
        }

        $service->update(['instructions' => $input['instructions']]);

        return $this->sendResponse([], 'Instructions updated successfully.');
    }

    // POST /expert/services/{service}/questions
    public function setQuestions(array $input, Service $service)
    {
        if (!$this->isOwner($service)) {
            return $this->sendError('غير مصرح.', 403);
        }

        $expert = Auth::user();

        // حذف الأسئلة القديمة واستبدالها
        PreBookingQuestion::where('service_id', $service->id)
            ->where('provider_type', 'expert')
            ->where('provider_id', $expert->id)
            ->delete();

        foreach ($input['questions'] as $index => $q) {
            PreBookingQuestion::create([
                'provider_type' => 'expert',
                'provider_id'   => $expert->id,
                'service_id'    => $service->id,
                'question_text' => $q['question_text'],
                'answer_type'   => $q['answer_type'],
                'options_json'  => $q['options_json'] ?? null,
                'is_required'   => $q['is_required'] ?? true,
                'sort_order'    => $index,
            ]);
        }

        return $this->sendResponse([], 'Questions set successfully.');
    }

    // PUT /expert/services/{service}/min-bookings
    public function updateMinBookings(array $input, Service $service)
    {
        if (!$this->isOwner($service)) {
            return $this->sendError('غير مصرح.', 403);
        }

        $service->update(['min_bookings_remote' => $input['min_bookings_remote']]);

        return $this->sendResponse([], 'Min bookings updated successfully.');
    }

    // ====================================================
    private function isOwner(Service $service): bool
    {
        $expert = Auth::user();
        return $service->provider_type === 'expert'
            && $service->provider_id === $expert->id;
    }
}
