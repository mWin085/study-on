<?php

namespace App\Controller;

use App\Entity\Course;
use App\Repository\CourseRepository;
use App\Service\BillingClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    private BillingClient $billingClient;

    public function __construct(BillingClient $billingClient)
    {
        $this->billingClient = $billingClient;
    }

    #[IsGranted('ROLE_USER')]
    #[Route(path: '/transactions', name: 'app_transactions')]
    public function getTransactions(CourseRepository $courseRepository)
    {
        $transactions = [];
        $courseIds = [];
        if ($user = $this->getUser()) {

            try {
                $transactions = $this->billingClient->transactions($user->getApiToken());
                $coursesData = $courseRepository->findBy(['code' => array_unique(array_column($transactions, 'code'))]);
                foreach ($coursesData as $courseData) {
                    $courseIds[$courseData->getCode()] = $courseData->getId();
                }

            } catch (\Exception $exception) {
            }
        }

        return $this->render('user/transactions.html.twig', [
            'transactions' => $transactions,
            'courseIds' => $courseIds
        ]);
    }
}