<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use App\Repository\UserRepository;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;




class LoginManagerAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';
    private RouterInterface $router;


    public function __construct(private UrlGeneratorInterface $urlGenerator , private UserRepository $userRepository , RouterInterface $router)
    {
        $this->router = $router;
    }

    public function authenticate(Request $request): Passport
{
    $email = $request->getPayload()->getString('email');

    $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

    return new Passport(
        new UserBadge($email, function (string $userIdentifier) {
            // Get the UserRepository from Symfony's service container
            $user = $this->userRepository->findOneBy(['email' => $userIdentifier]);

            if (!$user) {
                throw new CustomUserMessageAuthenticationException('Invalid credentials.');
            }

            if (!$user->isVerified()) {
                throw new CustomUserMessageAuthenticationException('Your account is not verified. Please check your email.');
            }

            return $user;
        }),
        new PasswordCredentials($request->getPayload()->getString('password')),
        [
            new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
        ]
    );
}


    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return null;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return new RedirectResponse($this->router->generate('admin_dashboard'));
        }

        // For example:
        return new RedirectResponse($this->urlGenerator->generate('app_main'));
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }


    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
