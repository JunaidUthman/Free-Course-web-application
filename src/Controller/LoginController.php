<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
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

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): Response
    {
        // // Custom logic after logout to handle redirection
        // $user = $this->getUser();

        // if ($user && in_array('ROLE_ADMIN', $user->getRoles())) {
        //     // Redirect admins to the admin dashboard after logout
        //     return $this->redirectToRoute('admin_dashboard');
        // }

        // // Redirect normal users to the main page after logout
        // return $this->redirectToRoute('app_main');
        throw new \LogicException('Cette méthode est interceptée par le système de déconnexion de Symfony.');
    }
}
