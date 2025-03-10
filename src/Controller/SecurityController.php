<?php

namespace App\Controller;

use App\Exception\BillingUnavailableException;
use App\Form\RegisterType;
use App\Security\AppCustomAuthenticator;
use App\Security\User;
use App\Service\BillingClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class SecurityController extends AbstractController
{

    private BillingClient $billingClient;

    public function __construct(BillingClient $billingClient)
    {
        $this->billingClient = $billingClient;
    }
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/register', name: 'app_register')]
    public function registration(Request $request,  UserAuthenticatorInterface $authenticator, AppCustomAuthenticator $formAuthenticator): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_profile');
        }

        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $result = $this->billingClient->register(json_encode([
                    'username' => $form->get('email')->getData(),
                    'password' => $form->get('password')->getData()
                ]));

                if (isset($result['error'])) {
                    throw new CustomUserMessageAuthenticationException($result['error']);
                }

                $user->setApiToken($result['token']);
                $user->setRefreshToken($result['refreshToken']);
            } catch (BillingUnavailableException $e) {
                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form->createView(),
                    'error' => $e->getMessage(),
                ]);
            }
            return $authenticator->authenticateUser(
                $user,
                $formAuthenticator,
                $request);
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
            'error' => false
        ]);
        //..
    }

    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {

        $user = $this->getUser();
        try {
            if (!$user){
                return $this->redirectToRoute('app_course_index');
            }
            $response = $this->billingClient->profile($user->getApiToken());
        } catch (BillingUnavailableException $e) {
            return $this->redirectToRoute('app_course_index');
        }

        if ($response['code'] != 201) {
            return $this->redirectToRoute('app_course_index');
        }

        return $this->render('user/index.html.twig', [
            'username' => $response['username'],
            'roles' => $response['roles'],
            'balance' => $response['balance'],
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
