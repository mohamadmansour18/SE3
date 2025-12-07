<?php

use App\Http\Controllers\Audit\NotificationController;
use App\Http\Controllers\Auth\UserController;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('/v1/citizen')->group(function () {

    Route::post('/register' , [UserController::class , 'registerCitizen'])->middleware(['throttle:registerApi' , 'Logging:register.citizen']);
    Route::post('/login' , [UserController::class , 'loginCitizen'])->middleware(['throttle:loginApi' , 'Logging:login.citizen']);

    Route::post('/verifyAccount' , [UserController::class , 'verifyRegistrationCitizen'])->middleware(['Logging:verify.citizen.account']);
    Route::post('/resendOtp' , [UserController::class , 'resendOtp'])->middleware(['throttle:registerApi' , 'Logging:resendOtp.for.verification']);

    /** @noinspection PhpParamsInspection */
    Route::middleware(['throttle:forgotPasswordApi' , 'Logging:reset.password'])->group(function () {

        Route::post('/forgotPassword' , [UserController::class , 'forgotPassword']);
        Route::post('/verifyForgotPasswordEmail' , [UserController::class , 'verifyForgotPasswordEmail']);
        Route::post('/resetPassword' , [UserController::class , 'resetPassword']);
        Route::post('/resendPasswordResetOtp' , [UserController::class , 'resendPasswordResetOtp']);

    });

    /** @noinspection PhpParamsInspection */
    Route::middleware(['jwt' ,'role:citizen' , 'throttle:roleBasedApi'])->group(function () {
        Route::get('/logout' , [UserController::class , 'logout'])->middleware('Logging:logout');


        Route::get('/notification' , [NotificationController::class , 'getCitizenNotifications'])->middleware('Logging:show.citizen.notification');
    });
});

Route::post('/refresh' , [UserController::class , 'refresh'])->middleware('jwt.refresh');









Route::get('/test-fcm', function (FirebaseNotificationService $fcm) {
    \App\Events\FcmNotificationRequested::dispatch([3] , "الوووووو" , "مرحبا زعييييم");
    return "تمت العملية بنجاح";
//    try {
//        $fcm->send(
//            'Hello Obeda',
//            'Test',
//            ['feQ0xpsbSAS89BkAniZ-F8:APA91bFuSmS4SLeYZvzsOW2XTvMlFMPRm9od8T58CHOvzy9yucQzO1upEemIZN_5XEtxICcL8jKzAgq9mqimAbYsw_oRhVtrutRwmANsmA1ACnODnknqCNw']
//        );
//        return "تمت العملية بنجاح";
//    }catch (\Exception $e)
//    {
//        return response()->json([
//            'title' => "خطا اتصال من الشبكة!",
//            'body' => $e->getMessage(),
//        ], 422);
//    }
});
/*
 * Route::get('/search', ...)
    ->middleware('throttle:30,1'); //max.minutes
 */
