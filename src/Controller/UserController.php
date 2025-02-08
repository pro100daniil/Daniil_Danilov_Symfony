<?php

namespace App\Controller;

use App\Entity\User;
use App\Formatter\ApiResponseFormatter;
use App\Repository\UserRepository;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users2')]
class UserController extends AbstractController
{
    public function __construct(
        private UserService $userService,
        private ApiResponseFormatter $apiResponseFormatter
    ) {
    }

    /**
     * Отримати всіх користувачів.
     */
    #[Route('', name: 'app_user2', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $users = $this->userService->getAllUsers();

        return $this->apiResponseFormatter
            ->withData($users)
            ->withMessage('Users retrieved successfully')
            ->response();
    }

    /**
     * Отримати одного користувача за ID.
     */
    #[Route('/{id}', name: 'app_user_show2', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->apiResponseFormatter
                ->withMessage('User not found')
                ->withStatus(Response::HTTP_NOT_FOUND)
                ->response();
        }

        return $this->apiResponseFormatter
            ->withData($user)
            ->withMessage('User retrieved successfully')
            ->response();
    }

    /**
     * Створити нового користувача.
     */
    #[Route('', name: 'create_user2', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser($request->getContent());

            return $this->apiResponseFormatter
                ->withData($user)
                ->withMessage('User created successfully')
                ->withStatus(Response::HTTP_CREATED)
                ->response();
        } catch (\InvalidArgumentException $e) {
            return $this->apiResponseFormatter
                ->withMessage($e->getMessage())
                ->withStatus($e->getCode())
                ->response();
        }
    }

    /**
     * Оновити користувача за ID.
     */
    #[Route('/{id}', name: 'update_user2', methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $user = $this->userService->updateUser($id, $request->getContent());

            return $this->apiResponseFormatter
                ->withData($user)
                ->withMessage('User updated successfully')
                ->response();
        } catch (\InvalidArgumentException $e) {
            return $this->apiResponseFormatter
                ->withMessage($e->getMessage())
                ->withStatus($e->getCode())
                ->response();
        }
    }

    /**
     * Видалити користувача за ID.
     */
    #[Route('/{id}', name: 'delete_user2', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->userService->deleteUser($id);

            return $this->apiResponseFormatter
                ->withMessage('User deleted successfully')
                ->response();
        } catch (\InvalidArgumentException $e) {
            return $this->apiResponseFormatter
                ->withMessage($e->getMessage())
                ->withStatus($e->getCode())
                ->response();
        }
    }
}