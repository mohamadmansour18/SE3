<?php

namespace App\Http\Controllers\Audit;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly NotificationService $notificationService,
    )
    {}

    public function getCitizenNotifications(): JsonResponse
    {
        $data = $this->notificationService->getCitizenNotifications(Auth::id());

        return $this->dataResponse(data: $data, statusCode: 200);
    }
}
