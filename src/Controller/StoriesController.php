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

    #[Route(path:'/otherProfile/{id}', name: 'otherProfile')]
    public function otherProfile(EntityManagerInterface $entityManager, $id)
    {
        //the user you clicked on
        $userP = $entityManager->find(User::class, $id);
        //For the header
        //the user that's logged in
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
            'userPfp'=>$userPfp,
            'user' => $userP
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
            //get the submitted data
            $formData = $request->request->all();
            $title = $formData['title'];
            $genreId = (int) $formData['genre'];
            $public = (int) $formData['public'];
            $story = $formData['story'];
            //create the entity
            $storyEntity = new Story();
            //assign the values to the entity's attributes
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
            //set the current datetime as the datetime attribute
            $storyEntity->setDatetime(new \DateTime());
            
            try
            {
                //"save" the entity to the ORM
                $entityManager->persist($storyEntity);
                //commit the changes to the DB
                $entityManager->flush($storyEntity);
                $response = "Story created successfully";
            }
            catch(\Exception $e) //if the flush fails
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
        //deletion of stories is done from within the user profile page
        if ($request->isMethod('GET')) 
        {
            if($request->query->get('id') != null)
            {
                $deleteid = $request->query->get('deleteid');
                //find the story by ID
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

    #[Route(path:'/editStory/{id}', name: 'editStory')]
    public function editStory(EntityManagerInterface $entityManager, $id, Request $request)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }
        
        //find the story by the passed ID
        $story = $entityManager->find(Story::class, $id);
        //handle the edit if it has been submitted
        if($request->request->all())
        {
            $formData = $request->request->all();
            $title = $formData['title'];
            $genreId = (int) $formData['genre'];
            $public = (int) $formData['public'];
            $storyText = $formData['story'];
            //set the new values
            $story->setStoryTitle($title);
            foreach($genresHeader as $genre)
            {
                if($genre->getID() == $genreId)
                {
                    $genreEntity = $genre;
                }
            }
            $story->setGenre($genreEntity);
            $story->setPublic($public);
            $story->setStoryText($storyText);

            try
            {
                //save to DB
                $entityManager->flush($story);
                $response = "Story edited successfully";
            }
            catch(\Exception $e) //in case the flush fails
            {
                $response = "An error occurred while trying to edit your story: " .$e->getMessage();
            } 
            $story = $entityManager->find(Story::class, $id);
            return $this->render('write.html.twig', [
                'genres' => $genresHeader,
                'userPfp' => $userPfp,
                'response' => $response,
                'user' => $user,
                'story' => $story
            ]);
        }
        return $this->render('editStory.html.twig',[
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp,
            'user' => $user,
            'story' => $story
        ]);
    }
    #[Route(path:'/search', name: 'search')]
    public function searc(EntityManagerInterface $entityManager,  Request $request)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }
        //get the term that's been searched from POST and handle it
        if ($request->isMethod('POST')) 
        {
            $formData = $request->request->all();
            $term = $formData['usersearched'];
            $existingUsers = $entityManager->getRepository(User::class)->findAll();
            //get only the users that match the search
            $matchingUsers = [];
            foreach($existingUsers as $currentuser)
            {
                $found = stripos($currentuser->getUsername(), $term); 
                if($found !== false)
                {
                    array_push($matchingUsers, $currentuser);
                }
            }
            dump($matchingUsers);
            return $this->render('userSearch.html.twig', [
                'genres' => $genresHeader,
                'userPfp' => $userPfp,
                'users' => $matchingUsers,
            ]);

        }
        return $this->render('userSearch.html.twig',[
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp,
        ]);
    }
}