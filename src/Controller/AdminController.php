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
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Genre;
use App\Entity\User;
use App\Entity\Story;
use App\Entity\Comments;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route(path:'/adminIndex', name: 'adminIndex')]
    public function adminIndex(EntityManagerInterface $entityManager)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }

        return $this->render('adminIndex.html.twig',[
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp,
        ]);
    }
    #[Route(path: '/adminAction', name: 'adminAction')]
    public function adminAction(EntityManagerInterface $entityManager, Request $request)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }

        if ($request->isMethod('POST')) 
        {
            $action = $request->request->get("action");
            if($action == 1) //users
            {
                $users = $entityManager->getRepository(User::class)->findAll();
                return $this->render("adminAction.html.twig", [
                    //For the header
                    'genres' => $genresHeader,
                    'userPfp'=>$userPfp,
                    'response' => 1,
                    'users' => $users,
                ]);
            }
            else if($action == 2) //stories
            {
                $stories = $entityManager->getRepository(Story::class)->findAll();
                return $this->render("adminAction.html.twig", [
                    //For the header
                    'genres' => $genresHeader,
                    'userPfp'=>$userPfp,
                    'response' => 2,
                    'stories' => $stories,
                ]);
            }
            else if($action == 3) //genres
            {   
                $stories = $entityManager->getRepository(Story::class)->findAll();
                return $this->render("adminAction.html.twig", [
                    //For the header
                    'genres' => $genresHeader,
                    'userPfp'=>$userPfp,
                    'response' => 3,
                    'stories' => $stories,
                ]);
            }
        }
        return $this->render("adminIndex.html.twig", [
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp,
        ]);
    }

    #[Route(path: '/changeUser/{id}', name: 'changeUser')]
    public function changeUser(EntityManagerInterface $entityManager, Request $request, $id)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }
        $userChanged = $entityManager->find(User::class, $id);
        if($request->isMethod("POST"))
        {
            $formData = $request->request->all();
            //ifs to avoid updating the whole table unnecessarily
            $username = $formData['username'];
            //only change if username is different form the passed username
            if($userChanged->getUsername() != $username)
            {
                $userChanged->setUsername($username);
            }
            $email = $formData['email'];
            if($userChanged->getEmail() != $email)
            {
                $userChanged->setEmail($email);
            }
            $verified = $formData['verified'];
            //confCod is 0 when user is verified
            if($verified == 0 && $userChanged->getConfCod() != 0)
            {
                $userChanged->setConfCod(0);
            }
            //confCod is a random number > 10 if the user is not verified
            else if($verified == 1 && $userChanged->getConfCod() == 0)
            {
                $userChanged->setConfCod(rand(10, 10000000));
            }
            $about = $formData['about'];
            if($userChanged->getAbout() != $about)
            {
                $userChanged->setAbout($about);
            }
            $role = $formData['role'];
            if($userChanged->getRole() != $role)
            {
                $userChanged->setRole($role);
            }
            try
            {
                $entityManager->flush();
                $changed = "User changed successfully";
            }
            catch(\Exception $e)
            {
                $changed = "Error changing the user: " .$e->getMessage();
            }
            finally
            {
                return $this->render("changeUser.html.twig", [
                    //For the header
                    'genres' => $genresHeader,
                    'userPfp'=>$userPfp,
                    'userChanged' => $userChanged,
                    'changed' => $changed
                ]);
            }
        }
        return $this->render("changeUser.html.twig", [
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp,
            'userChanged' => $userChanged,
        ]);
    }

    #[Route(path: '/deleteUser/{id}', name: 'deleteUser')]
    public function deleteUser(EntityManagerInterface $entityManager, $id)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }

        $userDelete = $entityManager->find(User::class, $id);

        try
        {
            //user created to host stories from deleted users
            $del = $entityManager->find(User::class, 161);

            if($userDelete == $del || $userDelete->getUserId() == 161)
            {
                $users = $entityManager->getRepository(User::class)->findAll();
                return $this->render("adminAction.html.twig", [
                    //For the header
                    'genres' => $genresHeader,
                    'userPfp'=>$userPfp,
                    'response' => 1,
                    'deleted' => "Can't delete that user as it is necessary for the app functionality",
                    'users' => $users
                ]);
            }
            foreach($userDelete->getStories() as $story)
            {
                $story->setUser($del);
            }
            $entityManager->remove($userDelete);
            $entityManager->flush();
            $deleted = "User $id deleted successfully";
        }
        catch(\Exception $e)
        {
            $deleted = "There was an error deleting the user $id: " .$e->getMessage();
        }
        finally
        {
            $users = $entityManager->getRepository(User::class)->findAll();
            
            return $this->render("adminAction.html.twig", [
                //For the header
                'genres' => $genresHeader,
                'userPfp'=>$userPfp,
                'response' => 1,
                'deleted' => $deleted,
                'users' => $users,
            ]);
        }
    }

    #[Route(path: "/moderateComments", name: "moderateComments")]
    public function moderateComments(EntityManagerInterface $entityManager)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }
        return $this->render("moderateComments.html.twig", [
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp,
        ]);
    }
    #[Route(path: "/deleteStory/{id}", name: "deleteStory")]
    public function deleteStory(EntityManagerInterface $entityManager, $id)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }
        //find the entity
        $story = $entityManager->find(Story::class, $id);
        try
        {
            //delete all related comments
            foreach($story->getComments() as $comment)
            {
                $entityManager->remove($comment);
            }
            $entityManager->remove($story);
            $entityManager->flush();
            $deleted = "Story with ID $id deleted successfully";
        }
        catch(\Exception $e)
        {
            $deleted = "There was an error deleting the story $id: " .$e->getMessage();
        }
        finally
        {
            $stories = $entityManager->getRepository(Story::class)->findAll();
            return $this->render("adminAction.html.twig", [
                //For the header
                'genres' => $genresHeader,
                'userPfp'=>$userPfp,
                'response' => 2,
                'deleted' => $deleted,
                'stories' => $stories,
            ]);
        }
    }
    #[Route(path: "/deleteGenre/{id}", name: "deleteGenre")]
    public function deleteGenre(EntityManagerInterface $entityManager, $id)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }

        $genre = $entityManager->find(Genre::class, $id);
        $deleted ="not initialized";
        try
        {
            $genreOther = $entityManager->getRepository(Genre::class)->findOneBy(['name' => "other"]);
            //change all related stories
            if($genre == $genreOther)
            {
                return $this->render("adminAction.html.twig", [
                    //For the header
                    'genres' => $genresHeader,
                    'userPfp'=>$userPfp,
                    'response' => 3,
                    'deleted' => "Can't delete that user as it is necessary for the app functionality",
                ]);
            }
            foreach($genre->getStories() as $story)
            {
                $story->setGenre($genreOther);
            }
            $entityManager->remove($genre);
            $entityManager->flush();
            $deleted = "Genre with ID $id deleted successfully";
            $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
            $users = $entityManager->getRepository(User::class)->findAll();
            return $this->render("adminAction.html.twig", [
                //For the header
                'genres' => $genresHeader,
                'userPfp'=>$userPfp,
                'response' => 3,
                'deleted' => $deleted,
            ]);
        }
        catch(\Exception $e)
        {
            $deleted = "There was an error deleting the genre $id: " .$e->getMessage();
            return $this->render("adminAction.html.twig", [
                //For the header
                'genres' => $genresHeader,
                'userPfp'=>$userPfp,
                'response' => 3,
                'deleted' => $deleted,
            ]);
        }
    }
    #[Route(path: "/createGenre", name: "createGenre")]
    public function createGenre(EntityManagerInterface $entityManager, Request $request)
    {
        //For the header
        $user = $this->getUser();
        $genresHeader = $entityManager->getRepository(Genre::class)->findAll();
        $userPfp = $user?->getPhoto();
        $base64Pfp = null;
        if ($userPfp !== null) {
            $base64Pfp = 'data:image/jpg;charset=utf8;base64,' . base64_encode(stream_get_contents($userPfp));
        }

        if($request->request->all())
        {
            //get the submitted data
            $name = $request->request->get("genreName");
            
            //create the entity
            $genreEntity = new Genre();
            //assign the values to the entity's attributes
            $genreEntity->setName($name);
            
            try
            {
                //"save" the entity to the ORM
                $entityManager->persist($genreEntity);
                //commit the changes to the DB
                $entityManager->flush($genreEntity);
                $response = "Genre created successfully";
            }
            catch(\Exception $e) //if the flush fails
            {
                $response = "An error occurred while trying to create the genre: " .$e->getMessage();
            } 
            finally
            {

                return $this->render('write.html.twig', [
                    'genres' => $genresHeader,
                    'userPfp' => $userPfp,     
                    'response' => 3,
                    'deleted' => $response,
                ]);
            }
        }
        return $this->render("adminAction.html.twig", [
            //For the header
            'genres' => $genresHeader,
            'userPfp'=>$userPfp,
        ]);
    }
}