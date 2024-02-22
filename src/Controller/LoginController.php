<?php
// src/Controller/LoginController.php

namespace App\Controller;
use App\Entity\Story;
use App\Entity\Genre;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils, EntityManagerInterface $entityManager): Response
    {
      // get the login error if there is one
        if ($this->getUser()) //user is logged in
        {
            //get the user entity that is logged in through symfony 
            $user = $this->getUser();

            //get all the stories from the db
            $repository = $entityManager->getRepository(Story::class);
            $stories = $repository->findBy([], ['datetime' => 'DESC']);;
            $stories = array_slice($stories, 0, 10);
            //find the 10 latest stories in the repository
            //$stories = $repository->findBy([], ['datetime' => 'DESC'], 10);
            
            //For the header
            $user = $this->getUser();
            $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
            $userPfp = $user?->getPhoto();
            $base64Pfp = null;
            if ($userPfp !== null) {
                $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
            }

            return $this->render('hello.html.twig', [ 
                'user' => $user, //user entity you can use to show the properties
                'stories' => $stories, //array with 10 latest stories
                //For the header
                'genres' => $genresHeader,
                'userPfp'=>$userPfp
            ]);
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
    
}