<?php
// src/Controller/HeatherController.php

namespace App\Controller;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Genre;
use App\Entity\Stories;

class HeaderController extends AbstractController
{
    #[Route(path:'/header', name: 'genre_page')]
    public function  genresList(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $genres = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
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
    }

    #[Route(path: '/genres/{genreName}', name: 'show_genre')]
    public function showGenres(EntityManagerInterface $entityManagerInterface): Response
    {
        $stories = $entityManagerInterface->getRepository(Stories::class)->findAll();
    }
}