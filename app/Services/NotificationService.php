<?php

namespace App\Services;

use App\Repositories\Audit\NotificationRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class NotificationService
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
    )
    {}

    public function getCitizenNotifications(int $citizenId): array
    {
        $cacheKey = "citizen:notifications:{$citizenId}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($citizenId) {

            $notifications = $this->notificationRepository->getUserNotifications($citizenId);

            return $notifications->map(function ($notification) {
                return [
                    'id'    => $notification->id,
                    'title' => $notification->data['title'] ?? '',
                    'body'  => $notification->data['body'] ?? '',
                    'date'  => Carbon::parse($notification->created_at)->diffForHumans(),
                ];
            })->toArray();
        });
    }
}
