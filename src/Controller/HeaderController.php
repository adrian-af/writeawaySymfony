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
        $genres = $entityManager->getRepository(Genre::class)->findAll();
        return $this->render('header.html.twig', array('genres'=>$genres));
    }

    #[Route(path: '/genres/{genreName}', name: 'show_genre')]
    public function showGenres(EntityManagerInterface $entityManagerInterface): Response
    {
        $stories = $entityManagerInterface->getRepository(Stories::class)->findAll();
    }
}