<?php

namespace App\Controller;

use App\Entity\Course;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use App\Service\BillingClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/courses')]
final class CourseController extends AbstractController
{

    private BillingClient $billingClient;

    public function __construct(BillingClient $billingClient)
    {
        $this->billingClient = $billingClient;
    }

    #[Route(name: 'app_course_index', methods: ['GET'])]
    public function index(CourseRepository $courseRepository): Response
    {
        $courses = $courseRepository->findAll();

        $courseTypes = [];
        $transactions = [];
        try {
            $response = $this->billingClient->courses();

            if ($response){
                $courseTypes = array_column($response, null, 'code');
            }

        } catch (\Exception $exception) {
        }

        if ($user = $this->getUser()){

            try {
                $response = $this->billingClient->transactions($user->getApiToken(),
                    [
                        'skip_expired' => true,
                        'type' => 'payment',
                    ]
                );

                if ($response){
                    $transactions = array_column($response, null, 'code');
                }

            } catch (\Exception $exception) {
            }
        }

        return $this->render('course/index.html.twig', [
            'courses' => $courses,
            'courseTypes' => $courseTypes,
            'transactions' => $transactions,
        ]);
    }

    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/new', name: 'app_course_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $courseData = $request->get('course');
            $credentials = json_encode([
                'code' => $courseData['code'],
                'title' => $courseData['title'],
                'type' => $courseData['type'],
                'price' => $courseData['price'],
            ]);
            $response = $this->billingClient->addCourse($credentials, $user->getApiToken());

            if ($response['success']){
                $entityManager->persist($course);
                $entityManager->flush();

                return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $form->addError(new FormError($response['error']));
            }
        }

        return $this->render('course/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_course_show', methods: ['GET'])]
    public function show(Course $course): Response
    {
        $courseType = [];
        $transactions = [];
        $available = false;
        try {
            $courseType = $this->billingClient->getCourse($course->getCode());

        } catch (\Exception $exception) {
        }

        try {
            if ($user = $this->getUser()) {
                $response = $this->billingClient->profile($user->getApiToken());
                if ($response['code'] == 200){
                    if ($response['balance'] > $courseType['price']) {
                        $available = true;
                    }
                }

            }


        } catch (\Exception $exception) {

        }

        if ($user = $this->getUser()){

            try {
                $transactions = $this->billingClient->transactions($user->getApiToken(),
                    [
                        'course_code' => $course->getCode(),
                        'skip_expired' => true,
                        'type' => 'payment',
                    ]
                );

            } catch (\Exception $exception) {
            }
        }

        return $this->render('course/show.html.twig', [
            'course' => $course,
            'courseType' => $courseType,
            'transactions' => $transactions,
            'lessons' => $course->getLessons()->toArray(),
            'available' => $available,
        ]);
    }

    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/{id}/edit', name: 'app_course_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Course $course, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CourseType::class, $course);
        try {

            $response = $this->billingClient->getCourse($course->getCode());
            if ($response){
                $form->get('type')->setData($response['type']);
                $form->get('price')->setData($response['price']);
            }

        } catch (\Exception $exception) {

        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUser();
            $courseData = $request->get('course');
            $credentials = json_encode([
                'code' => $courseData['code'],
                'title' => $courseData['title'],
                'type' => $courseData['type'],
                'price' => $courseData['price'],
            ]);
            $response = $this->billingClient->editCourse($credentials, $user->getApiToken(), $course->getCode());

            if ($response['success']){
                $entityManager->flush();

                return $this->redirectToRoute('app_course_show', ['id' => $course->getId()]);
            } else {
                $form->addError(new FormError($response['error']));
            }
        }

        return $this->render('course/edit.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/{id}', name: 'app_course_delete', methods: ['POST'])]
    public function delete(Request $request, Course $course, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$course->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($course);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/buy/{id}', name: 'app_course_buy', methods: ['POST'])]
    public function courseBuy(Request $request, Course $course, ): Response
    {
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('buy'.$course->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $response = $this->billingClient->buyCourse($user->getApiToken(), $course->getCode());
                if ($response['code'] == Response::HTTP_OK && $response['success']){
                    $this->addFlash('success', 'Курс успешно оплачен');
                }
                if ($response['code'] == Response::HTTP_NOT_ACCEPTABLE && $response['error']){
                    $this->addFlash('error', $response['error']);
                }

            } catch (\Exception $exception) {
                $this->addFlash('error', $exception->getMessage());
            }

        }

        return $this->redirectToRoute('app_course_show', ['id' => $course->getId()], Response::HTTP_SEE_OTHER);
    }
}
