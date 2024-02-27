<?php
// src/Controller/LoginController.php

namespace App\Controller;
use App\Entity\Story;
use App\Entity\Genre;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
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
                
                $base64Pfp = $user->getImageBase64();
                $userPfp = null;
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
        
        $error = $authenticationUtils->getLastAuthenticationError();
        if($error)
        {
            $error = $error->getMessage();
        }
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
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
}