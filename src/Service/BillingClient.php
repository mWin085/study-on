<?php

namespace App\Service;

use App\Exception\BillingUnavailableException;

class BillingClient
{
    public function authenticate(string $credentials)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $_ENV['BILLING'] . '/api/v1/auth');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $credentials);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $head = curl_exec($ch);
        if (!$head) {
            throw new BillingUnavailableException('Сервис временно недоступен. Попробуйте авторизоваться позднее');
        }
        curl_close($ch);

        return json_decode($head, true);
    }

    public function profile(string $apiToken): array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $_ENV['BILLING'] . '/api/v1/users/current');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiToken
        ]);
        $head = curl_exec($ch);

        if ($head === false ) {
            throw new BillingUnavailableException('Сервис временно недоступен');
        }
        curl_close($ch);

        return json_decode($head, true);

    }

    public function register(string $request): array
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $_ENV['BILLING'] . '/api/v1/register');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $head = curl_exec($ch);
        if (!$head) {
            throw new BillingUnavailableException('Сервис временно недоступен');
        }
        curl_close($ch);

        return json_decode($head, true);
    }

}