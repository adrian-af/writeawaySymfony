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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;



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
        if($userP)
        {
            $otherPhoto = $userP->getImageBase64();
            $otherUserPhoto = null;
            if($otherPhoto != null)
            {
                $otherUserPhoto = 'data:image/jpg;charset=utf8;base64,' . $otherPhoto;
            }
        }
        else
        {
            $otherUserPhoto = null;
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
                    'comments' => $comments,
                    'user' => $user
                ]);
            }
        }

        return $this->render('seeStory.html.twig', [
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp,
            'user' => $user
        ]);
    }

    #[Route(path: '/fav', name: 'fav')]
    public function fav(EntityManagerInterface $entityManager, Request $request)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        
        $base64Pfp = $user->getImageBase64();
        $userPfp = null;
        if ($base64Pfp !== null) {
            $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
        }

        if($request->request->all())
        {
            //get the submitted data
            $formData = $request->request->all();
            $storyId = $formData['storyID'];
            $action = $formData['action'];

            $userEntity = $this->getUser();
            $storyEntity = $entityManager->find(Story::class, $storyId);
        
            if($action == 'fav') //add this story as a favourite
            {
                $userEntity->addFavedStory($storyEntity);
                $storyEntity->addUserThatFaved($userEntity);
            }
            else if($action == 'unfav') //remove this story as a favourite
            {
                $userEntity->getFavStories()->removeElement($storyEntity);
                $storyEntity->getUsersThatFaved()->removeElement($userEntity);
            }
            $entityManager->persist($userEntity);
            $entityManager->persist($storyEntity);
            $entityManager->flush();
            $comments = $storyEntity->getComments();
            return $this->redirectToRoute('seeStory', ['id' => $storyId]);
        }
    }

    #[Route(path:'/write', name: 'write')]
    public function write(EntityManagerInterface $entityManager, Request $request)
    {
        $response = "";
        //For the header
        $user = $this->getUser();
        $genres = $entityManager->getRepository(Genre::class)->findAll();
        $users = $entityManager->getRepository(User::class)->findAll(); //Obtener todos los usuarios para los colaboradores
        
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
            $storyText = $formData['story'];
            $collaboratorIds = $formData['collaborators'] ?? []; //IDs de los colaboradores
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
            $storyEntity->setStoryText($storyText);
            $storyEntity->setUser($user);
            //set the current datetime as the datetime attribute
            $storyEntity->setDatetime(new \DateTime());
            foreach($collaboratorIds as $collaboratorId) {
                $collaborator = $entityManager->getRepository(User::class)->find($collaboratorId);
                if($collaborator){
                    $storyEntity->addCollabUsers($collaborator);
                    $collaborator->addCollabStories($storyEntity);
                    $entityManager->persist($collaborator);
                }
            }
            
            try
            {
                //"save" the entity to the ORM
                $entityManager->persist($storyEntity);
                //commit the changes to the DB
                $entityManager->flush();
                $response = "Story created successfully";
            }
            catch(\Exception $e) //if the flush fails
            {
                $response = "An error occurred while trying to create your story: " .$e->getMessage();
            } 
            finally
            {
                return $this->redirectToRoute('write', ['response' => $response]);
            }
        }
        return $this->render('write.html.twig', [
            'genres' => $genres,
            'users' => $users,
            'userPfp' => $userPfp,
            'response' => $response
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
        //deletion of stories and comments is done from within the user profile page
        if ($request->isMethod('POST'))
        {
            if($request->query->get('deleteid') != null)
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
                    return $this->redirectToRoute('ownProfile',[
                        //For the header
                        'genres' => $genresHeader,
                        'userPfp'=>$userPfp,
                        'user' => $user,
                        'deleted' => $deleted
                    ]);
                }
            }
            else if($request->query->get('deletecomment') != null)
            {
                $deletecomment = $request->query->get('deletecomment');
                $comment = $entityManager->find(Comment::class, $deletecomment);
                try
                {
                    $entityManager->remove($comment);
                    $entityManager->flush();
                    $deleted = "Comment deleted successfully";
                }
                catch(\Exception $e)
                {
                    $deleted = "There was an error deleting the comment: " .$e->getMessage();
                }
                finally
                {
                    return $this->redirectToRoute('ownProfile',[
                        'user' => $user
                    ]);
                }
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
        $users = $entityManager->getRepository(User::class)->findAll();
        
        $base64Pfp = $user->getImageBase64();
        $userPfp = null;
        if ($base64Pfp !== null) {
            $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
        }
        //find the story by the passed ID
        $story = $entityManager->find(Story::class, $id);
        if($story->getUser() != $user)
        {
            return $this->redirectToRoute('app_login');
        }
        $collaborators = $story->getCollabUsers();
        //handle the edit if it has been submitted
        if($request->request->all())
        {
            $formData = $request->request->all();
            $title = $formData['title'];
            $genreId = (int) $formData['genre'];
            $public = (int) $formData['public'];
            $storyText = $formData['story'];
            $collaboratorIds = $formData['collaborators'] ?? [];
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
            foreach($collaboratorIds as $collaboratorId) {
                $collaborator = $entityManager->getRepository(User::class)->find($collaboratorId);
                if($collaborator){
                    $story->addCollabUsers($collaborator);
                    $collaborator->addCollabStories($story);
                    $entityManager->persist($collaborator);
                }
            }

            try
            {
                //save to DB
                $entityManager->flush();
                $response = "Story edited successfully";
            }
            catch(\Exception $e) //in case the flush fails
            {
                $response = "An error occurred while trying to edit your story: " .$e->getMessage();
            } 
            finally
            {
                $story = $entityManager->find(Story::class, $id);
                return $this->redirectToRoute('editStory', [
                    'response' => $response,
                    'user' => $user,
                    'story' => $story,
                    'users' => $users,
                    'collaborators' => $collaborators,
                    'id' => $id
                ]);
            }
        }
        
        return $this->render('editStory.html.twig',[
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp,
            'user' => $user,
            'story' => $story,
            'collaborators' => $collaborators,
            'users' => $users
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
            $type = $formData['searchType'];
            if($type == "story")
            {
                return $this->redirectToRoute('storySearch',[
                    'term' => $term
                ]);
            }
            else if($type == "user")
            {
                return $this->redirectToRoute('userSearch', [
                    'term' => $term,
                ]);
            }

        }
        return $this->render('userSearch.html.twig',[
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp,
            'error'=>"not post"
        ]);
    }

    #[Route(path:'/storySearch/{term}', name: 'storySearch')]
    public function storySearch(EntityManagerInterface $entityManager, Request $request, $term)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();

        $base64Pfp = $user->getImageBase64();
        $userPfp = null;
        if ($base64Pfp !== null) {
            $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
        }

        $existingStories = $entityManager->getRepository(Story::class)->findAll();
        //get only the ones that match the search
        $matchingStories = [];
        foreach ($existingStories as $currentStory)
        {
            if($currentStory->getPublic() == 1)
            {
                $found = stripos(strtolower($currentStory->getStoryTitle()), strtolower($term));
                if($found !== false)
                {
                    array_push($matchingStories, $currentStory);
                    continue;
                }
                $found = stripos(strtolower($currentStory->getStoryText()), strtolower($term));
                if($found !== false)
                {
                    array_push($matchingStories, $currentStory);
                }
            }
        }
        return $this->render('storySearch.html.twig', [
            'genres' => $genresHeader,
            'userPfp' => $userPfp, 
            'stories' => $matchingStories,
            'term' => $term
        ]);
            
    
    }

    #[Route(path:'/userSearch/{term}', name: 'userSearch')]
    public function userSearch(EntityManagerInterface $entityManager, Request $request, $term)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();

        $base64Pfp = $user->getImageBase64();
        $userPfp = null;
        if ($base64Pfp !== null) {
            $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
        }
        $existingUsers = $entityManager->getRepository(User::class)->findAll();
        //get only the users that match the search
        $matchingUsers = [];
        foreach($existingUsers as $currentuser)
        {
            $found = stripos(strtolower($currentuser->getUsername()), strtolower($term));
            if($found !== false)
            {
                array_push($matchingUsers, $currentuser);
            }
        }
        return $this->render("userSearch.html.twig", [
            'genres' => $genresHeader,
            'userPfp' => $userPfp, 
            'term' => $term,
            'users' => $matchingUsers
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
                return $this->redirectToRoute('seeStory', [ 
                    'story' => $story,
                    'commentError' => "Comment was empty!",
                    'id' => $storyId
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
                return $this->redirectToRoute('seeStory', [
                    'story' => $story,
                    'comments' => $comments,
                    'id' => $storyId
                ]);
            }
            catch(\Exception $e)
            {
                $message = "Error processing your comment. Please try again later:" .$e->getMessage();
                return $this->redirectToRoute('seeStory', [
                    'story' => $story,
                    'commentError' => $message,
                    'id' => $storyId
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
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $base64Pfp = $user->getImageBase64();
        $userPfp = null;
        if ($base64Pfp !== null) {
            $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
        }
        if ($request->files->get('photo')) 
        {
            //get the uploaded photo
            $uploadedFile = $request->files->get('photo');
            
            // Open the file in binary mode to obtain a stream resource
            $fileStream = fopen($uploadedFile->getPathname(), 'rb');
            
            try
            {
                // Set the file stream directly to the $photo property in the entity
                $user->setPhoto($fileStream);
                
                $entityManager->flush();
                $changed = "Photo changed successfully!";
            }
            catch(\Exception $e)
            {
                $changed = "There was an error changing your photo: " .$e->getMessage(); 
            }
            finally
            {
                return $this->redirectToRoute("ownProfile", [
                    'genres' => $genresHeader,
                    'userPfp' => $userPfp,
                    'deleted' => $changed,
                    'user' => $user
                ]);   

            }
        }
        return $this->render("changePhoto.html.twig", [
            'genres' => $genresHeader,
            'userPfp' => $userPfp,
        ]);
    }

    #[Route(path:"/editUsername", name: "editUsername")]
    public function editUsername(EntityManagerInterface $entityManager,  Request $request)
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
            $newUsername = $formData['newUsername'];
            //only change if necessary
            if($user->getUsername() != $newUsername)
            {
                $usernameTaken = $entityManager->getRepository(User::class)->findOneBy(['username' => $newUsername]);
            
                if($usernameTaken != null)
                {
                    $changed = "Username already taken";
                    return $this->redirectToRoute('editUsername', ['changed' => $changed]);
                }
                $user->setUsername($newUsername);
                try
                {
                    $entityManager->flush();
                    $changed = "Username changed successfully";
                }
                catch(\Exception $e)
                {
                    $changed = "An error occurred when trying to update your username: " .$e->getMessage();
                }
                finally
                {
                    return $this->redirectToRoute('editUsername',[
                        'changed' => $changed,
                    ]);
                }
            }
            else
            {
                $changed = "No changes made";
            }
            return $this->redirectToRoute('editUsername',[
                'changed' => $changed
            ]);
        }
        if($request->isMethod('GET'))
        {
            $formData = $request->request->all();
            if(isset($formData['change']))
            {
                $change = $formData['change'];
                return $this->render('editUsername.html.twig',[
                    //For the header
                    'genres' => $genresHeader,
                    'userPfp'=> $userPfp,
                    'user' => $user,
                    'change' => $change
                ]);
            }
        }
        return $this->render('editUsername.html.twig',[
            //For the header
            'genres' => $genresHeader,
            'userPfp'=> $userPfp,
            'user' => $user
        ]);
    }

    #[Route(path:"changePassword", name: "changePassword")]
    public function changePassword(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        
        $base64Pfp = $user->getImageBase64();
        $userPfp = null;
        if ($base64Pfp !== null) {
            $userPfp = 'data:image/jpg;charset=utf8;base64,' . $base64Pfp;
        }

        if($request->isMethod('POST'))
        {
            $password = $request->request->get('password');
            $password2 = $request->request->get('password2');

            if($password != $password2)
            {
                $error = "Passwords do not match.";
                return $this->redirectToRoute('changePassword',[
                    'error' => $error,
                ]);
            }
            else if(strlen($password) < 3)
            {
                $error = "Password must be at least 3 characters long.";
                return $this->redirectToRoute('changePassword',[
                    'error' => $error,
                ]);
            }
            else
            {
                try{
                    $encodedPassword = $passwordHasher->hashPassword($user, $password);
                    $user->setPassword($encodedPassword);
                    $entityManager->flush();
                    return $this->redirectToRoute('changePassword',[
                        'success' => "Password changed successfully!",
                    ]);
                }catch(\Exception $e)
                {
                    $error = "Error saving the password: " .$e->getMessage();
                    return $this->redirectToRoute('changePassword',[
                        'error' => $error,
                    ]);
                }
            }
        }
        $error = null;
        if($request->query->get('error') != null)
        {
            $error = $request->query->get('error');
        }
        $success = null;
        if($request->query->get('success') != null)
        {
            $success = $request->query->get('success');
        }
        return $this->render('changePassword.html.twig',[
            'genres' => $genresHeader,
            'userPfp'=> $userPfp,
            'error' => $error,
            'success' => $success,
        ]);
    }

    
}