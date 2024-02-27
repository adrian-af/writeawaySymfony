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

use App\Entity\Genre;
use App\Entity\User;
use App\Entity\Story;

#[IsGranted('ROLE_USER')]
class HeaderController extends AbstractController
{
    #[Route(path:'/header', name: 'genre_page')]
    
    public function  genresList(EntityManagerInterface $entityManager, AuthenticationUtils $authenticationUtils): Response
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        
        $base64Pfp = $user->getImageBase64();
        if ($base64Pfp !== null) {
            $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
        }
        return $this->render('header.html.twig', [
            'genres' => $genresHeader,
            'userPfp' => $base64Pfp
        ]);
    }

    #[Route(path: '/genres/{genreID}', name: 'show_genre')]
    public function showGenres(EntityManagerInterface $entityManager, $genreID)
    {
        $repository = $entityManager->getRepository(Story::class);
        $genre = $entityManager->find(Genre::class, $genreID);
        $stories = $repository->findBy(['public' => 1, 'genre' => $genreID]);

        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        
        $base64Pfp = $user->getImageBase64();
        if ($base64Pfp !== null) {
            $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
        }
        return $this->render('stories.html.twig', [
            'stories' => $stories,
            'genre' => $genre,
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp,
        ]);
    }

    #[Route(path: "/ownProfile", name: "ownProfile")]
    public function profile(EntityManagerInterface $entityManager)
    {
        return $this->render("ownProfile.html.twig");
    }

    #[Route(path: "/about", name: "about")]
    public function about(EntityManagerInterface $entityManager)
    {
        return $this->render("about.html.twig");
    }
}