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
        $user = $this->getUser();
        $genres = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }
        return $this->render('header.html.twig', [
            'genres' => $genres,
            'userPfp' => $base64Pfp
        ]);
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