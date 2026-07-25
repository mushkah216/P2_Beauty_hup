<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expert\SetQuestionsRequest;
use App\Http\Requests\Expert\StoreServiceRequest;
use App\Http\Requests\Expert\UpdateInstructionsRequest;
use App\Http\Requests\Expert\UpdateMinBookingsRequest;
use App\Http\Requests\Expert\UpdateServiceRequest;
use App\Models\Service;
use App\Services\Expert\ServiceService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    //
    public ServiceService $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    public function index()
    {
        return $this->serviceService->index();
    }

    public function store(StoreServiceRequest $request)
    {
        return $this->serviceService->store($request->validated());
    }

    public function update(UpdateServiceRequest $request, Service $service)
    {
        return $this->serviceService->update($request->validated(), $service);
    }

    public function destroy(Service $service)
    {
        return $this->serviceService->destroy($service);
    }

    public function updateInstructions(UpdateInstructionsRequest $request, Service $service)
    {
        return $this->serviceService->updateInstructions($request->validated(), $service);
    }

    public function setQuestions(SetQuestionsRequest $request, Service $service)
    {
        return $this->serviceService->setQuestions($request->validated(), $service);
    }

    public function updateMinBookings(UpdateMinBookingsRequest $request, Service $service)
    {
        return $this->serviceService->updateMinBookings($request->validated(), $service);
    }
}
