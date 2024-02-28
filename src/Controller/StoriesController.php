<?php
// src/Controller/OrderController.php
//controller for pages that only logged in users can use

namespace App\Controller;
use App\Entity\Genre;
use App\Entity\Story;
use App\Entity\User;
use App\Entity\Comment;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

#[IsGranted('ROLE_USER')]
class StoriesController extends AbstractController
{
    #[Route(path:'/helloUser', name: 'helloUser')]
    public function hello(EntityManagerInterface $entityManager)
    {   
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        
        $base64Pfp = $user->getImageBase64();
        $userPfp = null;
        if ($base64Pfp !== null) {
            $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
        }
        return $this->render('hello.html.twig', [
            //For header
            'genres' => $genresHeader,
            'userPfp' => $userPfp,
            'user' => $user
        ]);
    }

    #[Route(path:'/otherProfile/{id}', name: 'otherProfile')]
    public function otherProfile(EntityManagerInterface $entityManager, $id)
    {
        //the user you clicked on
        $userP = $entityManager->find(User::class, $id);
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        
        $base64Pfp = $user->getImageBase64();
        $userPfp = null;
        if ($base64Pfp !== null) {
            $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
        }
        $otherPhoto = $userP->getImageBase64();
        $otherUserPhoto = null;
        if($otherPhoto != null)
        {
            $otherUserPhoto = 'data:image/jpg;charset=utf8;base64,' . $otherPhoto;
        }
        return $this->render('otherProfile.html.twig',[
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp,
            'user' => $userP,
            'otherUserPhoto' => $otherUserPhoto
        ]);
    }
    #[Route(path:'/seeStory', name: 'seeStory')]
    public function seeStory(EntityManagerInterface $entityManager, Request $request)
    {
        //get all the genres so the header shows all of them
        $repositoryGenres = $entityManager->getRepository(Genre::class);
        $genres = $repositoryGenres->findAll();
         //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        
        $base64Pfp = $user->getImageBase64();
        $userPfp = null;
        if ($base64Pfp !== null) {
            $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
        }
        //get the current story
        if($request->isMethod('GET'))
        {
            $id = $request->query->get('id');
            if($id)
            {
                $story = $entityManager->find(Story::class, $id);
                //get the comments
                $comments = $story->getComments();
                return $this->render('seeStory.html.twig', [
                    'genres' => $genresHeader,
                    'userPfp' => $userPfp,  
                    'story' => $story,
                    'comments' => $comments
                ]);
            }
        }

        return $this->render('seeStory.html.twig', [
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp
        ]);
    }

    #[Route(path:'/write', name: 'write')]
    public function write(EntityManagerInterface $entityManager, Request $request)
    {
        //For the header
        $user = $this->getUser();
        $genres = $entityManager->getRepository(Genre::class)->findAll();
        
        $base64Pfp = $user->getImageBase64();
        $userPfp = null;
        if ($base64Pfp !== null) {
            $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
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
                if($genre->getGenreID() == $genreId)
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
            finally
            {

                return $this->render('write.html.twig', [
                    'genres' => $genres,
                    'userPfp' => $userPfp,
                    'response' => $response
                ]);
            }
        }
        return $this->render('write.html.twig', [
            'genres' => $genres,
            'userPfp' => $userPfp
        ]);
    }
    #[Route(path:'/ownProfile', name: 'ownProfile')]
    public function ownProfile(EntityManagerInterface $entityManager, Request $request)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        
        $base64Pfp = $user->getImageBase64();
        $userPfp = null;
        if ($base64Pfp !== null) {
            $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
        }
        //deletion of stories is done from within the user profile page
        if ($request->isMethod('POST')) 
        {
            $deleteid = $request->query->get('deleteid');
            //find the story by ID
            $story = $entityManager->find(Story::class, $deleteid);
            try
            {
                foreach($story->getComments() as $comment)
                {
                    $entityManager->remove($comment);
                }
                $entityManager->remove($story);
                $entityManager->flush();
                $deleted = "Story deleted successfully";
            }
            catch(\Exception $e)
            {
                $deleted = "There was an error deleting the story: " .$e->getMessage();
            }
            finally
            {
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
        
        $base64Pfp = $user->getImageBase64();
        $userPfp = null;
        if ($base64Pfp !== null) {
            $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
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
                if($genre->getGenreID() == $genreId)
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
            finally
            {
                $story = $entityManager->find(Story::class, $id);
                return $this->render('editStory.html.twig', [
                    'genres' => $genresHeader,
                    'userPfp' => $userPfp,
                    'response' => $response,
                    'user' => $user,
                    'story' => $story
                ]);
            }
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
    public function search(EntityManagerInterface $entityManager,  Request $request)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        
        $base64Pfp = $user->getImageBase64();
        $userPfp = null;
        if ($base64Pfp !== null) {
            $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
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
    #[Route(path:'/comment', name: 'comment')]
    public function comment(EntityManagerInterface $entityManager,  Request $request)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        
        $base64Pfp = $user->getImageBase64();
        $userPfp = null;
        if ($base64Pfp !== null) {
            $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
        }
        //take the input from the form
        if ($request->isMethod('POST')) 
        {
            $formData = $request->request->all();
            $text = $formData['text'];
            $storyId = $formData['storyId'];
            $story = $entityManager->find(Story::class,  $storyId);
            $comments = $story->getComments();
            
            //check the content
            if (strlen($text) < 1)
            {
                return $this->render('seeStory.html.twig', [
                    'genres' => $genresHeader,
                    'userPfp' => $userPfp,  
                    'story' => $story,
                    'comments' => $comments,
                    'commentError' => "Comment was empty!"
                ]);
            }
            //create the entity
            $comment = new Comment();
            $comment->setUser($user);
            $comment->setCommentText($text);
            $comment->setStory($story);
            $comment->setDatetime(new DateTime());
            $entityManager->persist($comment);
            //add it to the db
            try
            {
                $entityManager->flush();
                return $this->render('seeStory.html.twig', [
                    'genres' => $genresHeader,
                    'userPfp' => $userPfp,  
                    'story' => $story,
                    'comments' => $comments,
                ]);
            }
            catch(\Exception $e)
            {
                $message = "Error processing your comment. Please try again later:" .$e->getMessage();
                return $this->render('seeStory.html.twig', [
                    'genres' => $genresHeader,
                    'userPfp' => $userPfp,  
                    'story' => $story,
                    'comments' => $comments,
                    'commentError' => $message,
                ]);
            }
        }
        return $this->redirectToRoute("app_login");
    }
    
    #[Route(path:"/editAbout", name: "editAbout")]
    public function editAbout(EntityManagerInterface $entityManager,  Request $request)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        
        $base64Pfp = $user->getImageBase64();
        $userPfp = null;
        if ($base64Pfp !== null) {
            $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
        }
        
        if($request->isMethod("POST"))
        {
            $formData = $request->request->all();
            $about = $formData['about'];
            //only change if necessary
            if($user->getAbout() != $about)
            {
                $user->setAbout($about);
                try
                {
                    $entityManager->flush();
                    $changed = "About changed successfully";
                }
                catch(\Exception $e)
                {
                    $changed = "An error occurred when trying to update your about: " .$e->getMessage();
                }
                finally
                {
                    return $this->render('editAbout.html.twig',[
                        //For the header
                        'genres' => $genresHeader,
                        'userPfp'=> $userPfp,
                        'changed' => $changed,
                        'user' => $user
                    ]);
                }
            }
            else
            {
                $changed = "No changes made";
            }
            return $this->render('editAbout.html.twig',[
                //For the header
                'genres' => $genresHeader,
                'userPfp'=> $userPfp,
                'changed' => $changed,
                'user' => $user
            ]);
        }
        return $this->render('editAbout.html.twig',[
            //For the header
            'genres' => $genresHeader,
            'userPfp'=> $userPfp,
            'user' => $user
        ]);
    }

    #[Route(path:"/changePhoto", name: "changePhoto")]
    public function changePhoto(Request $request, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        if ($request->files->get('photo')) 
        {
            //get the uploaded photo
            $uploadedFile = $request->files->get('photo');
            //read the file contents
            /* $fileContents = file_get_contents($uploadedFile->getPathname());
            //set it in the entity
            $user->setPhoto($fileContents); */
            // Open the file in binary mode to obtain a stream resource
            $fileStream = fopen($uploadedFile->getPathname(), 'rb');
            
            // Set the file stream directly to the $photo property in your entity
            $user->setPhoto($fileStream);
            
            $entityManager->flush();

            $user = $this->getUser();
            $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
            $base64Pfp = $user->getImageBase64();
            $userPfp = null;
            if ($base64Pfp !== null) {
                $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
            }

            return $this->redirectToRoute("ownProfile", [
                'genres' => $genresHeader,
                'userPfp' => $userPfp,
                'deleted' => "Photo changed successfully!",
                'user' => $user
            ]);   
        }
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        
        $base64Pfp = $user->getImageBase64();
        $userPfp = null;
        if ($base64Pfp !== null) {
            $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
        }

        return $this->render("changePhoto.html.twig", [
            'genres' => $genresHeader,
            'userPfp' => $userPfp,
        ]);
    }
}