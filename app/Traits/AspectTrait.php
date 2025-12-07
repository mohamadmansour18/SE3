<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait AspectTrait
{
    protected function around(
        callable $before = null ,
        callable $callback = null ,
        callable $after = null,
        callable $audit = null ,
        callable $onError = null ,
    ){

        try {

            if($before)
            {
                $before();
            }

            $result = $callback();

            if($audit)
            {
                $payload = $audit($result);
                if($payload)
                {
                    AuditLog::query()->create([
                        'actor_id'     => $payload['actor_id'] ?? Auth::id(),
                        'subject_type' => $payload['subject_type'] ?? null,
                        'subject_id'   => $payload['subject_id'] ?? null,
                        'changes'      => $payload['changes'] ?? [],
                    ]);
                }
            }

            if($after)
            {
                $after($result);
            }

            return $result;
        }catch(\Throwable $exception)
        {
            if($onError)
            {
                $onError($exception);
            }
            throw $exception;
        }
    }
}
