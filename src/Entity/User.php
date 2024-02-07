<?php
namespace App\Entity;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', name: 'ID')]
    #[ORM\GeneratedValue]
    private $ID;

    #[ORM\Column(type: 'string', name: 'email')]
    private $email;

    #[ORM\Column(type: 'string', name: 'username')]
    private $username;

    #[ORM\Column(type: 'string', name: 'password')]
    private $password;

    #[ORM\Column(type: 'integer', name:'confirmationCode')]
    private $confirmationCode;

    #[ORM\Column(type: 'string', name:'about')]
    private $about;

    #[ORM\Column(type: 'integer', name: 'role')]
    private $role;


    /**
     * Restaurant constructor
     */
    public function __construct()
    {
    }
    /**
     * Get the value of ID
     */ 
    public function getID()
    {
        return $this->ID;
    }

    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of Mail
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of password
     */ 
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */ 
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    

    /**
     * Get the value of confirmationCode
     */ 
    public function getConfirmationCode()
    {
        return $this->confirmationCode;
    }

    /**
     * Set the value of confirmationCode
     *
     * @return  self
     */ 
    public function setConfirmationCode($confirmationCode)
    {
        $this->confirmationCode = $confirmationCode;

        return $this;
    }

    /**
     * Get the value of about
     */ 
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * Set the value of about
     *
     * @return  self
     */ 
    public function setAbout($about)
    {
        $this->about = $about;

        return $this;
    }

    /**
     * Get the value of role
     */ 
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set the value of role
     *
     * @return  self
     */ 
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }
   
    /**
     * Returns the identifier for this user (e.g. username or email address).
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }
    public function eraseCredentials(): void
    {
        //$this->password = "";
    } 
    public function getRoles(): array
    {
        if($this->role === 0)
        {
            //0 is normal user
            return ["ROLE_USER"];
        }
        else
        {
            //1 is admin, which is both user and admin
            return ["ROLE_USER", "ROLE_ADMIN"]; //you can also do just role_admin and define the role hierarchy in security.yaml
        }
    }
}