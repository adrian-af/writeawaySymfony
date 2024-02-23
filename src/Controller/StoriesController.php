<?php
// src/Controller/OrderController.php
//controller for pages that only logged in users can use

namespace App\Controller;
use App\Entity\Genre;
use App\Entity\Story;
use App\Entity\User;
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
    public function hello(EntityManagerInterface $entityManager)
    {   
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }
        return $this->render('hello.html.twig', [
            //For header
            'genres' => $genresHeader,
            'userPfp' => $base64Pfp
        ]);
    }

    #[Route(path:'/otherProfile', name: 'otherProfile')]
    public function otherProfile(EntityManagerInterface $entityManager)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }
        return $this->render('otherProfile.html.twig',[
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp
        ]);
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
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }
        return $this->render('seeStory.html.twig', [
            'story' => $story,
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp
        ]);
    }

    #[Route(path:'/write', name: 'write')]
    public function write(EntityManagerInterface $entityManager, Request $request)
    {
        $user = $this->getUser();
        $genres = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }
        if($request->request->all())
        {
            $formData = $request->request->all();
            $title = $formData['title'];
            $genreId = (int) $formData['genre'];
            $public = (int) $formData['public'];
            $story = $formData['story'];
            $storyEntity = new Story();
            $storyEntity->setStoryTitle($title);
            foreach($genres as $genre)
            {
                if($genre->getID() == $genreId)
                {
                    $genreEntity = $genre;
                }
            }
            $storyEntity->setGenre($genreEntity);
            $storyEntity->setPublic($public);
            $storyEntity->setStoryText($story);
            $storyEntity->setUser($user);
            $storyEntity->setDatetime(new \DateTime());
            dump($genreEntity);
            try
            {
                $entityManager->persist($storyEntity);
                $entityManager->flush($storyEntity);
                $response = "Story created successfully";
            }
            catch(\Exception $e)
            {
                $response = "An error occurred while trying to create your story: " .$e->getMessage();
            } 
            return $this->render('write.html.twig', [
                'genres' => $genres,
                'userPfp' => $userPfp,
                'response' => $response
            ]);
        }
        return $this->render('write.html.twig', [
            'genres' => $genres,
            'userPfp' => $userPfp
        ]);
    }
    #[Route(path:'/ownProfile', name: 'ownProfile')]
    public function ownProfile(EntityManagerInterface $entityManager, Request $request)
    {
        $user = $this->getUser();
        
        //For the header
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }
        if ($request->isMethod('GET')) 
        {
            if($request->query->get('id') != null)
            {
                $deleteid = $request->query->get('deleteid');
                $story = $entityManager->find(Story::class,  $deleteid);
                try
                {
                    $entityManager->remove($story);
                    $entityManager->flush();
                    $deleted = "Story deleted successfully";
                }
                catch(\Exception $e)
                {
                    $deleted = "There was an error deleting the story: " .$e->getMessage();
                }
                return $this->render('ownProfile.html.twig',[
                    //For the header
                    'genres' => $genresHeader,
                    'userPfp'=>$userPfp,
                    'user' => $user,
                    'deleted' => $deleted
                ]);
            }
        }
        return $this->render('ownProfile.html.twig',[
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp,
            'user' => $user,
        ]);
    }

    #[Route(path:'/editStory', name: 'editStory')]
    public function editStory(EntityManagerInterface $entityManager)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }
        dump($user);
        return $this->render('editStor.html.twig',[
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp,
            'user' => $user
        ]);
    }
}