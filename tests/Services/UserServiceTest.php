<?php

namespace App\Tests\Service;

use App\Service\UserService;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private $userRepository;
    private $entityManager;
    private $serializer;
    private $validator;
    private $userService;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->userService = new UserService(
            $this->userRepository,
            $this->entityManager,
            $this->serializer,
            $this->validator
        );
    }

    public function testGetAllUsers(): void
    {
        $user1 = new User();
        $user1->setEmail('user1@example.com');

        $user2 = new User();
        $user2->setEmail('user2@example.com');

        $this->userRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$user1, $user2]);

        $result = $this->userService->getAllUsers();

        $this->assertCount(2, $result);
        $this->assertEquals('user1@example.com', $result[0]['email']);
        $this->assertEquals('user2@example.com', $result[1]['email']);
    }

    public function testGetUserByIdFound(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');

        $this->userRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($user);

        $result = $this->userService->getUserById(1);

        $this->assertNotNull($result);
        $this->assertEquals('user@example.com', $result['email']);
    }

    public function testGetUserByIdNotFound(): void
    {
        $this->userRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn(null);

        $result = $this->userService->getUserById(1);

        $this->assertNull($result);
    }

    public function testCreateUserSuccess(): void
    {
        $jsonData = '{"email":"newuser@example.com","password":"password123"}';

        $user = new User();
        $user->setEmail('newuser@example.com');
        $user->setPassword('password123');

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($jsonData, User::class, 'json')
            ->willReturn($user);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($user)
            ->willReturn(new ConstraintViolationList());

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user, true);

        $result = $this->userService->createUser($jsonData);

        $this->assertEquals('newuser@example.com', $result['email']);
    }

    public function testCreateUserValidationErrors(): void
    {
        $jsonData = '{"email":"invalid-email","password":"pass"}';

        $user = new User();
        $user->setEmail('invalid-email');
        $user->setPassword('pass');

        $violation = new ConstraintViolation(
            'Invalid email format', // message
            null,                   // message template
            [],                     // message parameters
            '',                     // root
            'email',                // property path
            'invalid-email'         // invalid value
        );

        $violationList = new ConstraintViolationList([$violation]);
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($jsonData, User::class, 'json')
            ->willReturn($user);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($user)
            ->willReturn($violationList);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        $this->userService->createUser($jsonData);
    }

    public function testUpdateUserSuccess(): void
    {
        $jsonData = '{"email":"updateduser@example.com"}';

        $user = new User();
        $user->setEmail('olduser@example.com');

        $this->userRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($user);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($user)
            ->willReturn(new ConstraintViolationList());

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user, true);

        $result = $this->userService->updateUser(1, $jsonData);

        $this->assertEquals('updateduser@example.com', $result['email']);
    }

    public function testUpdateUserNotFound(): void
    {
        $jsonData = '{"email":"updateduser@example.com"}';

        $this->userRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User not found');

        $this->userService->updateUser(1, $jsonData);
    }

    public function testDeleteUserSuccess(): void
    {
        $user = new User();

        $this->userRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($user);

        $this->userRepository
            ->expects($this->once())
            ->method('remove')
            ->with($user, true);

        $this->userService->deleteUser(1);

        $this->assertTrue(true); // If no exception is thrown, the test passes
    }

    public function testDeleteUserNotFound(): void
    {
        $this->userRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User not found');

        $this->userService->deleteUser(1);
    }
}
