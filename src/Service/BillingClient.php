<?php

namespace App\Service;

use App\Exception\BillingUnavailableException;

class BillingClient
{
    public function authenticate(string $credentials)
    {
        $head = $this->getResponse('/api/v1/auth', true, $credentials);

        return json_decode($head, true);
    }

    public function profile(string $apiToken): array
    {
        $head = $this->getResponse('/api/v1/users/current', true,false, $apiToken);

        return json_decode($head, true);

    }

    public function register(string $request): array
    {

        $head = $this->getResponse('/api/v1/register', true, $request);

        return json_decode($head, true);
    }

    public function refresh(string $apiToken): array
    {

        $head = $this->getResponse('/api/v1/token/refresh', true, json_encode(['refresh_token' => $apiToken]));

        return json_decode($head, true);

    }

    public function courses(): array
    {
        $head = $this->getResponse('/api/v1/courses', false);

        return json_decode($head, true);
    }

    public function transactions(string $apiToken, array $filter = []): array
    {
        $head = $this->getResponse('/api/v1/transactions?' . http_build_query($filter), false, false, $apiToken);

        return json_decode($head, true);
    }

    public function getCourse($code)
    {
        $head = $this->getResponse('/api/v1/courses/' . $code, false);

        return json_decode($head, true);

    }

    public function buyCourse(string $apiToken, string $code)
    {
        $head = $this->getResponse("/api/v1/courses/$code/pay", false, false, $apiToken);

        return json_decode($head, true);

    }

    /**
     * @param string $credentials
     * @return bool|string
     * @throws BillingUnavailableException
     */
    public function getResponse($link, $isPost = true, $credentials = false, $apiToken = ''): string|bool
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $_ENV['BILLING'] . $link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ($isPost) {
            curl_setopt($ch, CURLOPT_POST, 1);
        }

        if ($credentials) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $credentials);
        }

        if ($apiToken !== '') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiToken
            ]);
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
        }

        $head = curl_exec($ch);
        if (!$head) {
            throw new BillingUnavailableException('Сервис временно недоступен. Попробуйте авторизоваться позднее');
        }

        curl_close($ch);

        return $head;
    }

    public function addCourse(string $credentials, string $apiToken)
    {
        $head = $this->getResponse("/api/v1/courses", true, $credentials, $apiToken);

        return json_decode($head, true);
    }

    public function editCourse(string $credentials, string $apiToken, string $code)
    {
        $head = $this->getResponse("/api/v1/courses/$code", true, $credentials, $apiToken);

        return json_decode($head, true);
    }

}