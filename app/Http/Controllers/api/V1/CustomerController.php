<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Customer\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(public CustomerService $customerService)
    {
    }

    public function guestLogin()
    {
        return $this->customerService->guestLogin();
    }

    public function register(Request $request)
    {
        return $this->customerService->register($request);
    }

    public function login(Request $request)
    {
        return $this->customerService->login($request);
    }

    public function forgotPassword(Request $request)
    {
        return $this->customerService->forgotPassword($request);
    }

    public function verifyOtp(Request $request)
    {
        return $this->customerService->verifyOtp($request);
    }

    public function resetPassword(Request $request)
    {
        return $this->customerService->resetPassword($request);
    }

    public function logout(Request $request)
    {
        return $this->customerService->logout($request);
    }

    public function home()
    {
        return $this->customerService->home();
    }

    public function topRatedSalons()
    {
        return $this->customerService->topRatedSalons();
    }

    public function topRatedBeautyCenters()
    {
        return $this->customerService->topRatedBeautyCenters();
    }

    public function topRatedExperts()
    {
        return $this->customerService->topRatedExperts();
    }

    public function searchProviders(Request $request)
    {
        return $this->customerService->searchProviders($request);
    }

    public function showProvider(string $type, int $id)
    {
        return $this->customerService->showProvider($type, $id);
    }

    public function providerPosts(string $type, int $id)
    {
        return $this->customerService->providerPosts($type, $id);
    }

    public function feedPosts()
    {
        return $this->customerService->feedPosts();
    }

    public function filterNearby(Request $request)
    {
        return $this->customerService->filterNearby($request);
    }

    public function filterByGovernorate(Request $request)
    {
        return $this->customerService->filterByGovernorate($request);
    }

    public function filterByCity(Request $request)
    {
        return $this->customerService->filterByCity($request);
    }

    public function filterByServiceType(Request $request)
    {
        return $this->customerService->filterByServiceType($request);
    }

    public function filterByPrice(Request $request)
    {
        return $this->customerService->filterByPrice($request);
    }

    public function providerEmployees(string $type, int $id)
    {
        return $this->customerService->providerEmployees($type, $id);
    }

    public function providerServices(string $type, int $id)
    {
        return $this->customerService->providerServices($type, $id);
    }

    public function serviceDetails(int $id)
    {
        return $this->customerService->serviceDetails($id);
    }

    public function serviceQuestions(int $id)
    {
        return $this->customerService->serviceQuestions($id);
    }

    public function providerSchedule(string $type, int $id)
    {
        return $this->customerService->providerSchedule($type, $id);
    }

    public function employeeSchedule(string $type, int $id, int $employeeId)
    {
        return $this->customerService->employeeSchedule($type, $id, $employeeId);
    }

    public function postDetails(int $id)
    {
        return $this->customerService->postDetails($id);
    }

    public function postComments(int $id)
    {
        return $this->customerService->postComments($id);
    }

    public function addPostComment(Request $request, int $id)
    {
        return $this->customerService->addPostComment($request, $id);
    }

    public function replyToComment(Request $request, int $id, int $commentId)
    {
        return $this->customerService->replyToComment($request, $id, $commentId);
    }

    public function toggleLikePost(Request $request, int $id)
    {
        return $this->customerService->toggleLikePost($request, $id);
    }

    public function toggleFavoritePost(Request $request, int $id)
    {
        return $this->customerService->toggleFavoritePost($request, $id);
    }

    public function toggleFollowProvider(Request $request, string $type, int $id)
    {
        return $this->customerService->toggleFollowProvider($request, $type, $id);
    }

    public function toggleBlockProvider(Request $request, string $type, int $id)
    {
        return $this->customerService->toggleBlockProvider($request, $type, $id);
    }

    public function toggleBlockUser(Request $request, int $id)
    {
        return $this->customerService->toggleBlockUser($request, $id);
    }

    public function storeBooking(Request $request)
    {
        return $this->customerService->storeBooking($request);
    }

    public function showBooking(Request $request, int $id)
    {
        return $this->customerService->showBooking($request, $id);
    }

    public function bookingStatus(Request $request, int $id)
    {
        return $this->customerService->bookingStatus($request, $id);
    }

    public function cancelBooking(Request $request, int $id)
    {
        return $this->customerService->cancelBooking($request, $id);
    }

    public function rescheduleBooking(Request $request, int $id)
    {
        return $this->customerService->rescheduleBooking($request, $id);
    }

    public function rateBooking(Request $request, int $id)
    {
        return $this->customerService->rateBooking($request, $id);
    }

    public function reportBooking(Request $request, int $id)
    {
        return $this->customerService->reportBooking($request, $id);
    }

    public function bookingInvoice(Request $request, int $id)
    {
        return $this->customerService->bookingInvoice($request, $id);
    }

    public function bookingHistory(Request $request)
    {
        return $this->customerService->bookingHistory($request);
    }

    public function upcomingBookings(Request $request)
    {
        return $this->customerService->upcomingBookings($request);
    }

    public function bookingDetails(Request $request, int $id)
    {
        return $this->customerService->bookingDetails($request, $id);
    }

    public function bookingServices(Request $request, int $id)
    {
        return $this->customerService->bookingServices($request, $id);
    }

    public function bookingEmployee(Request $request, int $id)
    {
        return $this->customerService->bookingEmployee($request, $id);
    }

    public function previewBooking(Request $request)
    {
        return $this->customerService->previewBooking($request);
    }

    public function storeServiceSelection(Request $request)
    {
        return $this->customerService->storeServiceSelection($request);
    }

    public function storeBookingAnswers(Request $request)
    {
        return $this->customerService->storeBookingAnswers($request);
    }

    public function bookingSummary(Request $request)
    {
        return $this->customerService->bookingSummary($request);
    }

    public function selectedServiceState(Request $request)
    {
        return $this->customerService->selectedServiceState($request);
    }

    public function notifications(Request $request)
    {
        return $this->customerService->notifications($request);
    }

    public function markNotificationRead(Request $request, int $id)
    {
        return $this->customerService->markNotificationRead($request, $id);
    }

    public function wallet(Request $request)
    {
        return $this->customerService->wallet($request);
    }

    public function withdrawFromWallet(Request $request)
    {
        return $this->customerService->withdrawFromWallet($request);
    }

    public function walletTransactions(Request $request)
    {
        return $this->customerService->walletTransactions($request);
    }

    public function showProfile(Request $request)
    {
        return $this->customerService->showProfile($request);
    }

    public function updateProfile(Request $request)
    {
        return $this->customerService->updateProfile($request);
    }

    public function showMedicalRecord(Request $request)
    {
        return $this->customerService->showMedicalRecord($request);
    }

    public function updateMedicalRecord(Request $request)
    {
        return $this->customerService->updateMedicalRecord($request);
    }

    public function listAddresses(Request $request)
    {
        return $this->customerService->listAddresses($request);
    }

    public function storeAddress(Request $request)
    {
        return $this->customerService->storeAddress($request);
    }
}
