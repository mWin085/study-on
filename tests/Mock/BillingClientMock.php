<?php

namespace App\Tests\Mock;

use App\Service\BillingClient;
use Symfony\Component\HttpFoundation\Response;

class BillingClientMock extends BillingClient
{

    const COURSE_LIST_MOCK = [
        [
            "code" => 'course_1',
            'type' => 'free'
        ],
        [
            "code" => 'course_2',
            'type' => 'rent',
            'price' => 299
        ], [
            "code" => 'course_3',
            'type' => 'buy',
            'price' => 1200
        ], [
            "code" => 'course_4',
            'type' => 'rent',
            'price' => 400
        ],
    ];

    const USER_LIST_MOCK = [
        [
            "username" => 'admin@admin.com',
            "password" => 'adminadmin',
            "role" => [
                'ROLE_SUPER_ADMIN'
            ]
        ],
        [
            "username" => 'user@user.com',
            "password" => 'useruser',
            "role" => [
                'ROLE_USER'
            ]
        ],
        [
            "username" => 'user1@user.com',
            "password" => 'useruser',
            "role" => [
                'ROLE_USER'
            ]
        ]
    ];

    const TRANSACTIONS_LIST_MOCK = [
        'user@user.com' => [
            [
                "id" => 1,
                "code" => "course_2",
                "type" => "Оплата",
                "amount" => 299,
                "createdAt" => "",
                "expires_at" => ""
            ],
            [
                "id" => 2,
                "code" => "course_3",
                "type" => "Оплата",
                "amount" => 1299,
                "createdAt" => ""
            ],
        ]
    ];

    public function authenticate(string $credentials)
    {
        $data = json_decode($credentials, true);

        if ($item = array_filter(self::USER_LIST_MOCK, function ($user) use ($data) {
            return $user['username'] === $data['username'] && $user['password'] === $data['password'];
        })){
            $item = reset($item);
            $token = base64_encode(json_encode([
                'iat' => (new \DateTime())->getTimestamp(),
                'exp' => (new \DateTime())->modify('+1 day')->getTimestamp(),
                'username' => $item['username'],
                'roles' => $item['role']
            ]));
            $refreshToken = base64_encode(json_encode([
                'iat' => (new \DateTime())->getTimestamp(),
                'exp' => (new \DateTime())->modify('+1 month')->getTimestamp(),
                'username' => $item['username'],
                'roles' => $item['role'],
            ]));

            return [
                'token' => '1.' . $token,
                'refresh_token' => $refreshToken,
            ];

        }

        return [
            'code' => 401,
            'message' => 'Invalid credentials',
        ];
    }


    public function refresh(string $refreshToken): array
    {
        $data = json_decode(base64_decode($refreshToken), true);

        if ($item = array_filter(self::USER_LIST_MOCK, function ($user) use ($data) {
            return $user['username'] === $data['username'];
        })){
            $item = reset($item);

            $token = base64_encode(json_encode([
                'iat' => (new \DateTime())->getTimestamp(),
                'exp' => (new \DateTime())->modify('+1 day')->getTimestamp(),
                'username' => $item['username'],
                'roles' => $item['role']
            ]));
            $refreshToken = base64_encode(json_encode([
                'iat' => (new \DateTime())->getTimestamp(),
                'exp' => (new \DateTime())->modify('+1 month')->getTimestamp(),
                'username' => $item['username'],
                'roles' => $item['role'],
            ]));

            return [
                'token' => '1.' . $token,
                'refresh_token' => $refreshToken,
            ];

        }

        return [
            'code' => 401,
            'message' => 'Invalid credentials',
        ];
    }

    public function register(string $request): array
    {
        $data = json_decode($request, true);
        if (in_array($data['username'], array_column(self::USER_LIST_MOCK, 'username'))) {
            return [
                'code' => 400,
                'error' => 'Invalid credentials',
            ];
        } else {
            $token = base64_encode(json_encode([
                'iat' => (new \DateTime())->getTimestamp(),
                'exp' => (new \DateTime())->modify('+1 day')->getTimestamp(),
                'username' => $data['username'],
                'roles' => ['ROLE_USER'],
            ]));
            $refreshToken = base64_encode(json_encode([
                'iat' => (new \DateTime())->getTimestamp(),
                'exp' => (new \DateTime())->modify('+1 month')->getTimestamp(),
                'username' => $data['username'],
                'roles' => ['ROLE_USER'],
            ]));

            return [
                'token' => $token,
                'roles' => [
                    'ROLE_USER'
                ],
                'refreshToken' => $refreshToken
            ];
        }
    }


    public function profile(string $apiToken): array
    {
        $data = json_decode(base64_decode(explode('.', $apiToken)[1]), true);

        if ($item = array_filter(self::USER_LIST_MOCK, function ($user) use ($data) {
            return $user['username'] === $data['username'];
        })) {
            $item = reset($item);
            return [
                'code' => 200,
                'username' => $data['username'],
                'roles' => $data['roles'],
                'balance' => 500,
            ];
        }
        return [
            "code" => 401,
            "message" => "Invalid JWT Token"
        ];
    }

    public function addCourse(string $credentials, string $apiToken)
    {
        $data = json_decode($credentials, true);
        if ($data['type'] !== 'free' && (int)$data['price'] <= 0) {
            return [
                'success' => false,
                'error' => 'Invalid credentials',
            ];
        }
        return [
            'success' => true
        ];
    }

    public function courses(): array
    {
        return self::COURSE_LIST_MOCK;
    }

    public function editCourse(string $credentials, string $apiToken, string $code)
    {
        if (!in_array($code, array_column(self::COURSE_LIST_MOCK, 'code'))) {
            return [
                'success' => false,
                'error' => 'Invalid code',
            ];
        }

        $data = json_decode($credentials, true);
        if ($data['code'] !== $code && in_array($code, array_column(self::COURSE_LIST_MOCK, 'code'))) {
            return [
                'success' => false,
                'error' => 'Invalid code',
            ];
        }

        if ($data['type'] !== 'free' && (int)$data['price'] <= 0) {
            return [
                'success' => false,
                'error' => 'Invalid credentials',
            ];
        }
        return [
            'success' => true
        ];
    }

    public function getCourse($code)
    {
        if (
            $course = array_filter(self::COURSE_LIST_MOCK, function ($item) use ($code) {
            return $item['code'] === $code;
            })
        ) {
            return reset($course);
        } else {
            return ['code' => Response::HTTP_NOT_FOUND, 'error' => 'Courses not found'];
        }
    }

    public function transactions(string $apiToken, array $filter = []): array
    {
        $data = json_decode(base64_decode(explode('.', $apiToken)[1]), true);

        if ($items = self::TRANSACTIONS_LIST_MOCK[$data['username']]){
            foreach ($items as &$item) {
                if (isset($item['createdAt'])){
                    $item['createdAt'] = (new \DateTime())->format('c');
                }
                if (isset($item['expires_at'])){
                    $item['expires_at'] = (new \DateTime())->modify('+7 day')->format('c');
                }
            }
            return $items;
        }
        return [];
    }

    public function buyCourse(string $apiToken, string $code)
    {

        if (
            $course = array_filter(self::COURSE_LIST_MOCK, function ($item) use ($code) {
                return $item['code'] === $code;
            })
        ) {
            $course = reset($course);
            $result = [
                'code' => Response::HTTP_OK,
                'success' => true,
                'course_type' => $course['type'],
            ];
            if ($course['type'] == 'rent'){
                $result['expires_at'] = (new \DateTime())->modify('+7 day');
            }
            return $result;
        } else {
            return ['code' => Response::HTTP_NOT_FOUND, 'error' => 'Courses not found'];
        }

    }

}