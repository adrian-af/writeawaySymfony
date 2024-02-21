<?php
// src/Controller/OrderController.php
//controller for pages that only logged in users can use

namespace App\Controller;
use App\Entity\Genre;
use App\Entity\Story;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[IsGranted('ROLE_USER')]
class StoriesController extends AbstractController
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
    public function seeStory(EntityManagerInterface $entityManager, Request $request)
    {
        //get all the genres so the header shows all of them
        $repositoryGenres = $entityManager->getRepository(Genre::class);
        $genres = $repositoryGenres->findAll();
        $story = null;
        //get the current story
        if($request->isMethod('GET'))
        {
            $id = $request->query->get('id');
            if($id)
            {
                $story = $entityManager->find(Story::class, $id);
            }
        }
        return $this->render('seeStory.html.twig', ['genres' => $genres, 'story' => $story]);
    }

    #[Route(path:'/write', name: 'write')]
    public function write(EntityManagerInterface $entityManager)
    {
        $genres = $entityManager->getRepository(Genre::class)->findAll();
        return $this->render('write.html.twig', ['genres' => $genres]);
    }

}