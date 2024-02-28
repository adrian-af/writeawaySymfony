<?php
// src/Controller/LoginController.php

namespace App\Controller;
use App\Entity\Story;
use App\Entity\Genre;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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

    //Password recovery
    
    #[Route('/checkmail', name: 'forgot_password_mail')]
    public function forgot(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer,){
        //Initialize error message
        $error = $request->query->get('error');

        //Process of submission
        if($request->isMethod('POST')){
            $formData = $request->request->all();
            $mail = $formData['mail'];

            $existingUser = $entityManager-> getRepository(User::class);
            $existingMail = $existingUser->findOneBy(['email'=>$mail]);
            if($existingMail == null){
                $error .= "Email not registered";
            }

            if(!$error){
                return $this->redirectToRoute('change_password',['mail'=>$mail]);
            }

            return $this->redirectToRoute('forgot_password_mail', ['error'=>$error]);
        }

        //For the header
        
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        
        $base64Pfp = null;

        return $this->render('askEmail.html.twig', [
            'error'=>$error,
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$base64Pfp
        ]);
    }
    
    #[Route('/passwordChange/{mail}', name:'change_password')]
    public function changePassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, $mail){
        //Initialize error message
        $error = $request->query->get('error');

        if($request->isMethod("POST")){
            $formData = $request->request->all();
            $passLen = 3;
            $plainPassword = $formData['password'];
            $password2 = $formData['password2'];

            //validation
            if(strlen($plainPassword) < $passLen){
                $error = "Password must be at least".$passLen ." characters long<br>";
            }
            if($plainPassword != $password2){
                $error  .= "Passwords do not match!";
            }

            if(!$error){
                $user = $entityManager->getRepository(User::class)->findOneBy([
                    'email'=>$mail
                ]);
                $encodedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($encodedPassword);

                $entityManager->persist( $user );
                try{
                    $entityManager->flush();
                }catch(\Exception $e){
                    $error = "El error es". $e->getMessage();
                }

                return $this->redirectToRoute('app_login', ['error'=>$error]);
            }

            //For the header
        
            $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
            
            $base64Pfp = null;

            return $this->redirectToRoute('change_password',[
                'error'=>$error,
                'mail'=>$mail,
                //For the header
                'genres' => $genresHeader,
                'userPfp'=>$base64Pfp
            ]);
        }
         //For the header
        
         $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
            
         $base64Pfp = null;

        return $this->render('changePassword.html.twig',[
            'error'=>$error,
            'mail'=>$mail,
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$base64Pfp
        ]);
    }
    
}