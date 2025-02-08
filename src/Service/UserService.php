<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Response;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * Отримати всіх користувачів.
     */
    public function getAllUsers(): array
    {
        $users = $this->userRepository->findAll();
        return array_map(fn(User $user) => $user->toArray(), $users);
    }

    /**
     * Отримати одного користувача за ID.
     */
    public function getUserById(int $id): ?array
    {
        $user = $this->userRepository->find($id);
        return $user ? $user->toArray() : null;
    }

    /**
     * Створити нового користувача.
     */
    public function createUser(string $jsonData): array
    {
        /** @var User $user */
        $user = $this->serializer->deserialize($jsonData, User::class, 'json');

        // Валідація даних
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string) $errors, Response::HTTP_BAD_REQUEST);
        }

        // Збереження користувача
        $this->userRepository->save($user, true);

        return $user->toArray();
    }

    /**
     * Оновити користувача за ID.
     */
    public function updateUser(int $id, string $jsonData): array
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new \InvalidArgumentException('User not found', Response::HTTP_NOT_FOUND);
        }

        // Оновлення даних
        $data = json_decode($jsonData, true);
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['password'])) {
            $user->setPassword($data['password']);
        }

        // Валідація
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string) $errors, Response::HTTP_BAD_REQUEST);
        }

        $this->userRepository->save($user, true);

        return $user->toArray();
    }

    /**
     * Видалити користувача за ID.
     */
    public function deleteUser(int $id): void
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new \InvalidArgumentException('User not found', Response::HTTP_NOT_FOUND);
        }

        $this->userRepository->remove($user, true);
    }
}