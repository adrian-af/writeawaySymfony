<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class SignUpController extends AbstractController
{
    #[Route('/signup', name: 'app_signup')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        // Initialize error message
        $error = $request->query->get('error');

        // Process form submission
        if ($request->isMethod('POST')) 
        {
            $formData = $request->request->all();
            $passLen = 3;
            $email = $formData['email'];
            $plainPassword = $formData['password'];
            $username = $formData['username'];
            $password2 = $formData['password2'];

            // Validate form data
            if(strlen($username) < 1 || strlen($email) < 1)
            {
                $error = "No empty fields<br>";
            }
            if(strlen($formData['password']) < $passLen)
            {
                $error .= "Password must be at least " .$passLen ." characters long<br>";
            }
            if($formData['password'] != $formData['password2'])
            {
                $error .= "Passwords do not match<br>";
            }
            $existingUsers = $entityManager->getRepository(User::class);

            $usernameTaken = $existingUsers->findOneBy(['username' => $username]);
            if($usernameTaken != null)
            {
                $error .= "Username already taken<br>";
            }
            $emailTaken = $existingUsers->findOneBy(['email' => $email]);
            if($emailTaken != null)
            {
                $error .= "Email already in use<br>";
            }

            if (!$error) 
            {
                // Create a new user entity and set its properties
                $user = new User();
                $user->setUsername($username);
                $user->setEmail($email);

                // Encode the plain password
                $encodedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($encodedPassword);
                $confirmationCode = rand(10, 10000000); 
                $user->setConfCod($confirmationCode);
                $user->setRole(0);

                // Save the user to the database
                $entityManager->persist($user);
                
                //send email
                $respuesta = $this->sendEmail($confirmationCode, $email);
                if($respuesta)
                {
                    $text = "Error sending confirmation email: " .$respuesta;
                    return $this->redirectToRoute('app_signup', ['error' => $text]);
                }
                $entityManager->flush();
                
                // Redirect to login page after successful signup
                return $this->render('checkemail.html.twig');
            }
            return $this->redirectToRoute('app_signup', ['error' => $error]);
        }

        // Render the sign-up form
        return $this->render('signup.html.twig', ['error' => $error]);
    }
    #[Route('/response', name: 'response')]
    public function errorPage(Request $request): Response
    {
        $error = $request->query->get('error');
        // Render the error page template and pass the error message
        return $this->render('response.html.twig', ['error' => $error]);
    }
    public function sendEmail($confirmationCode, $email)
    {
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = 0;                      
            $mail->isSMTP();                                           
            $mail->Host       = "smtp.gmail.com";                    
            $mail->SMTPAuth   = true;                                   
            $mail->Username   = 'adrian.fernandez.close@gmail.com';     
            $mail->Password   = 'mmko xwhh gjqv psbk';                  
            $mail->SMTPSecure = "tls";         
            $mail->Port       = 587;                                    

            //Recipients
            $mail->setFrom('welcome@writeaway.com', 'Writeaway');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $htmlBody = $this->renderView('confirmation.html.twig', ['code' => $confirmationCode]);
            $mail->Subject = 'Welcome to Writeaway!';
            $mail->Body    = $htmlBody;

            $mail->send();
            return false;
        } catch (Exception $e) 
        {
            return $this->json(['error' => $mail->ErrorInfo], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
