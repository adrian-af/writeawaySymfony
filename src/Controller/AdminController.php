<?php
// src/Controller/HeatherController.php

namespace App\Controller;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Genre;
use App\Entity\User;
use App\Entity\Story;
use App\Entity\Comments;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route(path:'/adminIndex', name: 'adminIndex')]
    public function adminIndex(EntityManagerInterface $entityManager)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }

        return $this->render('adminIndex.html.twig',[
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp,
        ]);
    }
    #[Route(path: '/adminAction', name: 'adminAction')]
    public function adminAction(EntityManagerInterface $entityManager, Request $request)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }

        if ($request->isMethod('POST')) 
        {
            $action = $request->request->get("action");
            if($action == 1) //users
            {
                $users = $entityManager->getRepository(User::class)->findAll();
                return $this->render("adminAction.html.twig", [
                    //For the header
                    'genres' => $genresHeader,
                    'userPfp'=>$userPfp,
                    'response' => 1,
                    'users' => $users,
                ]);
            }
            else if($action == 2) //stories
            {
                $stories = $entityManager->getRepository(Story::class)->findAll();
                return $this->render("adminAction.html.twig", [
                    //For the header
                    'genres' => $genresHeader,
                    'userPfp'=>$userPfp,
                    'response' => 2,
                    'stories' => $stories,
                ]);
            }
            else //genres
            {   
                return $this->render("adminAction.html.twig", [
                    //For the header
                    'genres' => $genresHeader,
                    'userPfp'=>$userPfp,
                    'response' => 3,
                    'stories' => $stories,
                ]);
            }
        }
        return $this->render("adminIndex.html.twig", [
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp,
        ]);
    }

    #[Route(path: '/changeUser/{id}', name: 'changeUser')]
    public function changeUser(EntityManagerInterface $entityManager, Request $request, $id)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }
        $userChanged = $entityManager->find(User::class, $id);
        return $this->render("changeUser.html.twig", [
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp,
            'userChanged' => $userChanged,
        ]);
    }

    #[Route(path: '/deleteUser', name: 'deleteUser')]
    public function deleteUser(EntityManagerInterface $entityManager, Request $request)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }
        return $this->render("adminIndex.html.twig", [
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp,
        ]);
    }
}