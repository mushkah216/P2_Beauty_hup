<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expert\UpdateCalendarRequest;
use App\services\Expert\CalendarServices;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    //
        use ApiResponseTrait;

    public function __construct(protected CalendarServices $calendarService) {}

    public function index()
    {
        $schedule = $this->calendarService->getExpertSchedule();

        return $this->sendResponse(['schedule' => $schedule], 'Schedule retrieved successfully.');
    }

    public function update(UpdateCalendarRequest $request)
    {
        $schedule = $this->calendarService->updateExpertSchedule($request->validated());

        return $this->sendResponse(['schedule' => $schedule], 'Schedule updated successfully.');
    }

}
