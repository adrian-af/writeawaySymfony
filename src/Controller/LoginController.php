<?php
// src/Controller/LoginController.php

namespace App\Controller;
use App\Entity\Story;
use App\Entity\Genre;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils, EntityManagerInterface $entityManager, MailerInterface $mailer, Request $request): Response
    {
      // get the login error if there is one
        if ($this->getUser()) //user is logged in
        {
            //get the user entity that is logged in through symfony 
            $user = $this->getUser();
            //check if the user is verified
            if($user->getConfCod() != 0)
            {
                //user is NOT verified
                return $this->render("unverified.html.twig");
            }
            else
            {

                //Redirecting to home page. Get all the public stories from the db ordered by date
                $repository = $entityManager->getRepository(Story::class);
                $stories = $repository->findBy(['public' => 1], ['datetime' => 'DESC']);
                //select the 10 latest stories
                $stories = array_slice($stories, 0, 10);
                
                 //For the header
                $user = $this->getUser();
                $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
                $userPfp=null;
                $base64Pfp = $user->getImageBase64();
                if ($base64Pfp !== null) {
                    $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
                }
                
                return $this->render('hello.html.twig', [ 
                    'user' => $user, //user entity you can use to show the properties
                    'stories' => $stories, //array with 10 latest stories
                    //For the header
                    'genres' => $genresHeader,
                    'userPfp'=>$userPfp
                ]);
            }
        }

        $error = null;
        if($request->query->get('error') != null)
        {
            $error = $request->query->get('error');
        }
        $success = null;
        if($request->query->get('success') != null)
        {
            $success = $request->query->get('success');
        }
        
        if($error == null){
            $error = $authenticationUtils->getLastAuthenticationError();
            if($error)
            {
                $error = $error->getMessage();
            }
        }
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
            'success' => $success,
        ]);
        
    }
 
    #[Route('/logout', name:'app_logout')]
    public function logout()
    {
        return;
    }
    
    #[Route('/resend' ,name: "resend")]
    public function resend(MailerInterface $mailer)
    {
        //resend the verification email
        $user = $this->getUser();
        //send email (function inside User entity)
        $error = $user->sendEmail('welcome@writeaway.com', "Writeaway verification", "confirmation.html.twig", $mailer);
        if($error != 0)
        {
            return $this->redirectToRoute('app_login', ['error' => $error]);
        }
        else
        {
            return $this->redirectToRoute('checkemail');
        }
    }

    //Password recovery

    #[Route('/forgot-password', name: 'forgot_password')]
    public function forgotPassword(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer)
    {
        $error = null;
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                $code = rand(100000, 999999); // Generate a random 6-digit code
                $user->setForgor($code);

                $entityManager->persist($user);
                $entityManager->flush();

                // Send email with the code
                $emailMessage = (new Email())
                    ->from('noreply@writeaway.com')
                    ->to($email)
                    ->subject('Password Reset Code')
                    ->text('Your password reset code is: ' . $code);

                $mailer->send($emailMessage);

                return $this->redirectToRoute('verify_code', ['email' => $email]);
            } else {
                $error = 'No user found with this email.';
            }
        }

        return $this->render('forgot_password.html.twig', ['error' => $error]);
    }

    #[Route('/verify-code', name: 'verify_code')]
    public function verifyCode(Request $request, EntityManagerInterface $entityManager)
    {
        $error = null;
        $email = $request->query->get('email');

        if ($request->isMethod('POST')) {
            $code = $request->request->get('code');
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user && $user->getForgor() == $code) {
                return $this->redirectToRoute('change_password', ['mail' => $email]);
            } else {
                $error = 'Invalid code.';
            }
        }

        return $this->render('verify_code.html.twig', ['error' => $error, 'email' => $email]);
    }

    #[Route('/passwordChange/{mail}', name:'change_password')]
    public function changePassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, $mail)
    {
        $error = null;
        $success = $request->query->get('success');

        if ($request->isMethod('POST')) {
            $formData = $request->request->all();
            $plainPassword = $formData['password'];
            $password2 = $formData['password2'];

            if ($plainPassword !== $password2) {
                $error = "Passwords do not match!";
            } else {
                $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $mail]);

                if (!$user) {
                    $error = 'User not found.';
                } else {
                    try {
                        // Hash the plain password using the password hasher
                        $hashedPassword = $userPasswordHasher->hashPassword($user, $plainPassword);
                        $user->setPassword($hashedPassword);

                        $entityManager->persist($user);
                        $entityManager->flush();

                        return $this->redirectToRoute('app_login', ['success' => true]);
                    } catch (\Exception $e) {
                        error_log('Error changing password: ' . $e->getMessage()); // Log the error for debugging
                        $error = 'Failed to change password: ' . $e->getMessage();
                    }
                }
            }
        }

        return $this->render('changePassword.html.twig', [
            'error' => $error,
            'mail' => $mail,
            'success' => $success,
        ]);
    }

}