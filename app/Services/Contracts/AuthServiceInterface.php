<?php

namespace App\Services\Contracts;

interface AuthServiceInterface
{
    public function registerCitizen(array $data): array;
    public function loginCitizen(array $data): array;
    public function verifyRegistration(array $data): array;
    public function resendOtp(string $email , string $purpose): array;
    ///////////////////////////////////////////////////////
    public function forgotPassword(string $email): array;
    public function verifyForgotPasswordEmail(array $data): array;
    public function resetPassword(array $data): array;

}
