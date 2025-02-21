<?php
namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Block login if the user is frozen
        if ($user->isFrozen()) {
            throw new CustomUserMessageAccountStatusException('Your account is frozen. Please contact support in this email : Fcourse@gmail.com');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // No additional checks after authentication
    }
}
