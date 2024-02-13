<?php
// src/Controller/RegistrationController.php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Config\SecurityConfig;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use App\Entity\User;

class SignUpController extends AbstractController
{
    #[Route('/signup', name: 'app_signup')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager)
    {
        // Initialize variables to hold form data
        $formData = $request->request->all();

        // Check if form is submitted
        if ($request->isMethod('POST')) 
        {
            // Get form data
            $email = $formData['email'];
            $plainPassword = $formData['password'];
            $username = $formData['username'];
            $password2 = $formData['password2'];

            //compare plaintext passwords
            if($password2 != $plainPassword)
            {
                //if any condition is true, it will return to the singup twig with the error description
                return $this->render('signin.html.twig', ['error' => 'Passwords do not match']);
            }
            //get all the users
            $userRepository = $entityManager->getRepository(User::class);

            $existingEmail = $userRepository->findOneBy(['email' => $email]);

            //if any user has been found searching by email, then the email is already in use
            if ($existingEmail != null)
            {
                return $this->render('signin.html.twig', ['error' => 'Email already registered']);
            }

            $existingUsername = $userRepository->findOneBy(['username' => $username]);

            //if any user has been found searching by username, then the username is already in use
            if($existingUsername != null)
            {
                return $this->render('signin.html.twig', ['error' => 'Username already taken']);
            }

            // Create a new user entity and set its properties
            $user = new User();
            $user->setEmail($email);

            // Encode the plain password using the method from the UserPasswordEncoderInterface
            $encodedPassword = $passwordHasher->hashPassword($user, $plainPassword);

            $user->setPassword($encodedPassword);
            $user->setUsername($username);
            $confirmationCode = rand(10, 10000000);
            $user->setConfirmationCode($confirmationCode);
            $user->setRole(0);

            // Save the user to the database
            //the entity manager takes the instance of the entity User that has been created and "saves" the changes
            $entityManager->persist($user);
            //as it uses transactions, the changes aren't actually done on the DB until the flush is executed
            $entityManager->flush();

            // Redirect to login page after signup
            return new Response('<html><body>Check your email to verify your account</body></html>');
        }
        else
        {
            return $this->render('signin.html.twig');
        }
    }
}
