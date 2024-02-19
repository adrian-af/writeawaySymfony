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
<<<<<<< HEAD
    public function  genresList(EntityManagerInterface $entityManager): Response
=======
    
    public function  genresList(EntityManagerInterface $entityManager, AuthenticationUtils $authenticationUtils): Response
>>>>>>> 95d7f65b7df179d9b894d4b0257f3f4ba1242d59
    {
        $user = $this->getUser();
        $genres = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
<<<<<<< HEAD
        if($userPfp)
        {
            $base64Pfp = "data:image/jpg;charset=utf8;base64," .base64_encode(stream_get_contents($userPfp));
        }
        else
        {
            $base64Pfp = null;
        }
        return $this->render('header.html.twig', [
            'genres' => $genres,
            'userPfp' => $base64Pfp,
            ]);
=======
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }
        return $this->render('header.html.twig', [
            'genres' => $genres,
            'userPfp' => $base64Pfp
        ]);
>>>>>>> 95d7f65b7df179d9b894d4b0257f3f4ba1242d59
    }

    #[Route(path: '/genres/{genreID}', name: 'show_genre')]
    public function showGenres(EntityManagerInterface $entityManager, $genreID)
    {
        $repository = $entityManager->getRepository(Story::class);
        $stories = $repository->findBy(['genreID' => $genreID]);
        $genres = $entityManager->getRepository(Genre::class)->findAll();
        return $this->render('stories.html.twig', [
            'stories' => $stories,
            'genres' => $genres
        ]);
    }
}