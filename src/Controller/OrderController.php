<?php
// src/Controller/OrderController.php
//controller for pages that only logged in users can use

namespace App\Controller;
use App\Entity\Genre;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    #[Route(path:'/helloUser', name: 'helloUser')]
    public function hello()
    {
        return $this->render('hello.html.twig');
    }

    #[Route(path:'/otherProfile', name: 'otherProfile')]
    public function otherProfile()
    {
        return $this->render('otherProfile.html.twig');
    }
    #[Route(path:'/seeStory', name: 'seeStory')]
    public function seeStory(EntityManagerInterface $entityManager)
    {
        //get all the genres so the header shows all of them
        $repositoryGenres = $entityManager->getRepository(Genre::class);
        $genres = $repositoryGenres->findAll();
        
        return $this->render('seeStory.html.twig', ['genres' => $genres]);
    }

    #[Route(path:'/header', name:'header')]
    public function header(EntityManagerInterface $entityManager)
    {
        $genres= $entityManager->getRepository(Genre::class)->findAll();
        return $this->render('header.html.twig');
        //return $this->render('header.html.twig', array('genres'=>$genres));
    }
}