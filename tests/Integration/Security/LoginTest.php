<?php

namespace App\Tests\Integration\Security;

use App\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginTest extends WebTestCase
{
    public function testUserCanLogIn(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $connection = $container->get(Connection::class);
        $connection->executeStatement('TRUNCATE "user" RESTART IDENTITY CASCADE');

        $entityManager = $container->get(EntityManagerInterface::class);
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail('login@example.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'Password123!'));

        $entityManager->persist($user);
        $entityManager->flush();

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign in')->form([
            'email' => 'login@example.com',
            'password' => 'Password123!',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }
}
