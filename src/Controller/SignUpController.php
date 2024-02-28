<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SignUpController extends AbstractController
{
    #[Route('/signup', name: 'app_signup')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer, SessionInterface $session): Response
    {
        // Initialize error message
        $error = $request->query->get('error');

        // Process form submission
        if ($request->isMethod('POST')) 
        {
            $formData = $request->request->all();
            $passLen = 3;
            $email = $formData['email'];
            $plainPassword = $formData['password'];
            $username = $formData['username'];
            $password2 = $formData['password2'];

            // Validate form data
            if(strlen($username) < 1 || strlen($email) < 1)
            {
                $error = "No empty fields<br>";
            }
            if(strlen($plainPassword) < $passLen)
            {
                $error .= "Password must be at least " .$passLen ." characters long<br>";
            }
            if($plainPassword != $password2)
            {
                $error .= "Passwords do not match<br>";
            }

            $existingUsers = $entityManager->getRepository(User::class);

            $usernameTaken = $existingUsers->findOneBy(['username' => $username]);
            if($usernameTaken != null)
            {
                $error .= "Username already taken<br>";
            }
            $emailTaken = $existingUsers->findOneBy(['email' => $email]);
            if($emailTaken != null)
            {
                $error .= "Email already in use<br>";
            }

            if (!$error) 
            {
                // Create a new user entity and set its properties
                $user = new User();
                $user->setUsername($username);
                $user->setEmail($email);
                $user->setAbout("I am a user of WriteAway!");

                // Encode the plain password
                $encodedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($encodedPassword);
                $confirmationCode = rand(10, 10000000); 
                $user->setConfCod($confirmationCode);
                $user->setRole(0);

                // Save the user 
                $entityManager->persist($user);
                //session
                $session->set('username', $username);
                $session->set('code', $confirmationCode);
                $session->set('userId', $user->getUserId());
                
                //send email (function inside User entity)
                $error = $user->sendEmail('welcome@writeaway.com', "Welcome to Writeaway", "confirmation.html.twig", $mailer);
                if($error != 0)
                {
                    return $this->redirectToRoute('app_signup', ['error' => $error]);
                }
                else
                {
                    $entityManager->flush();
                    //session
                    $session->set('username', $username);
                    $session->set('code', $confirmationCode);
                    $session->set('userId', $user->getUserId());
                    return $this->redirectToRoute('checkemail');
                }
            }
            return $this->redirectToRoute('app_signup', ['error' => $error]);
        }

        // Render the sign-up form
        return $this->render('signup.html.twig', ['error' => $error]);
    }
    #[Route('/response', name: 'response')]
    public function errorPage(Request $request)
    {
        $error = $request->query->get('error');
        // Render the error page template and pass the error message
        return $this->render('app_login', ['error' => $error]);
    }

    #[Route('/checkemail', name: 'checkemail')]
    public function checkEmail(Request $request, SessionInterface $session, EntityManagerInterface $entityManager)
    {
        // Initialize error message
        $error = $request->query->get('error');

        // Process form submission
        if ($request->isMethod('POST'))
        {
            $formData = $request->request->all();
            $codeUser = $formData['code'];
            $codeDB = $session->get('code');
            $idUser = $session->get('userId');

            if($idUser == null)
            {
                $user = $this->getUser();
                $idUser = $user->getUserId();
                $codeDB = $user->getConfCod();
            }
            if($codeUser == $codeDB)
            {
                $user = $entityManager->find(User::class, $idUser);
                $user->setConfCod('0');
                $entityManager->persist($user); //only do persist when the entity is new
                $entityManager->flush();

                return $this->render('login.html.twig', ['error' => "Your account has been verified! Now login."]);
            }
            else
            {
                return $this->render('checkemail.html.twig', ["error" => "Wrong code. Please, try again"]);
            }
        }
        return $this->render('checkemail.html.twig');
    }
    #[Route(path:"/checkEmailUnverified", name: "checkemailUnverified")]
    public function checkEmailUnverified()
    {
        return $this->render('checkemail.html.twig');
    }
}
