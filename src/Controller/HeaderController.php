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
    
    public function  genresList(EntityManagerInterface $entityManager): Response
    {
        $genres = $entityManager->getRepository(Genre::class)->findAll();
       
        return $this->render('header.html.twig', array('genres' => $genres));
    }
    public function pfp(EntityManagerInterface $entityManager): Response{
        $userPfp = $entityManager->getRepository(User::class)->find('photo');
        return $this->render('header.html.twig', array('userPfp' => $userPfp) );
    }

    #[Route(path: '/genres/{genreId}', name: 'show_genre')]
    public function showGenres(EntityManagerInterface $entityManager, $genreID)
    {
        $stories = $entityManager->find(Story::class, $genreID);
        return $this->render('stories.html.twig', array('stories'=>$stories));
    }
}