<?php 

namespace App\Form\DataTransformer;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class UserToIdTransformer implements DataTransformerInterface
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function transform($user): mixed
    {
        if (!$user) return '';

        return $user->getPseudonym();
    }

    public function reverseTransform($userId): ?User
    {
        if (!$userId) return null;

        $user = $this->userRepository->find($userId);

        if (!$user) {
            throw new TransformationFailedException(sprintf(
                'A user with ID "%s" does not exist!',
                $userId
            ));
        }

        return $user;
    }
}