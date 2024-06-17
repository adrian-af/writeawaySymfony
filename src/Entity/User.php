<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface {
    #[ORM\Id]
    #[ORM\Column(name: 'ID')]
    #[ORM\GeneratedValue]
    private $userId;

    #[ORM\Column(type: 'string', name: 'email')]
    private $email;

    #[ORM\Column(type: 'string', name: 'username')]
    private $username;

    #[ORM\Column(type: 'string', name: 'password')]
    private $password;

    #[ORM\Column(type: 'blob', name: 'photo')]
    private $photo;

    #[ORM\Column(type:'integer', name: 'confirmationCode')]
    private $confCod;

    #[ORM\Column(type:'string', name: 'about')]
    private $about;

    #[ORM\Column(type: 'integer', name: 'role')]
    private $role;

    #[ORM\OneToMany(targetEntity: "Story", mappedBy:"user")]
    private $stories; //array of stories

    #[ORM\OneToMany(targetEntity: "Comment", mappedBy: "user")]
    private $comments;

    private string $imageBase64;


    #[ORM\ManyToMany(targetEntity: Story::class, inversedBy: "usersThatFaved")]
    #[ORM\JoinTable(
        name: "users_fav_stories",
        joinColumns: [new ORM\JoinColumn(name: "user_id", referencedColumnName: "ID")],
        inverseJoinColumns: [new ORM\JoinColumn(name: "story_id", referencedColumnName: "ID")]
    )]
    public $favStories; //stories faved by this user

    #[ORM\ManyToMany(targetEntity: Story::class, inversedBy: "collabUsers")]
    #[ORM\JoinTable(
        name: "rel_story_user",
        joinColumns: [new ORM\JoinColumn(name: "user_id", referencedColumnName: "ID")],
        inverseJoinColumns: [new ORM\JoinColumn(name: "story_id", referencedColumnName: "ID")]
    )]
    public $collabStories; //stories in which this user collabs
    /**
     * User constructor
     */
    public function __construct()
    {
    }

    /**
     * Get the value of id
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the value of id
     */
    // public function setId($userID): self
    // {
    //     $this->userID = $userID;

    //     return $this;
    // }

    /**
     * Get the value of email
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set the value of email
     */
    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of username
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Set the value of username
     */
    public function setUsername($username): self
    {
        $this->username = $username;

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
     */
    public function setPassword($password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of photo
     */
    public function getImageBase64()
    {
        if (null === $this->photo) {
            return null;
        }

        if (!isset($this->imageBase64)) {
            $data = stream_get_contents($this->photo);
            fclose($this->photo);
            $this->imageBase64 = base64_encode($data);
        }

        return $this->imageBase64;
    }

    /**
     * Set the value of photo
     */
    public function setPhoto($photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get the value of confCod
     */
    public function getConfCod(): ?int
    {
        return $this->confCod;
    }

    /**
     * Set the value of confCod
     */
    public function setConfCod($confCod): self
    {
        $this->confCod = $confCod;

        return $this;
    }

    /**
     * Get the value of about
     */
    public function getAbout(): ?string
    {
        return $this->about;
    }

    /**
     * Set the value of about
     */
    public function setAbout($about): self
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

    /**
     * Get the value of stories
     */
    public function getStories()
    {
        return $this->stories;
    }

    /**
     * Set the value of stories
     *
     * @return  self
     */
    public function setStories($stories)
    {
        $this->stories = $stories;

        return $this;
    }

    public function sendEmail($from, $subject, $template, MailerInterface $mailer)
    {
        //send email
        $emailObject = new TemplatedEmail();
        $emailObject->from($from);
        $emailObject->to($this->email);
        $emailObject->subject($subject);
        $confirmationCode = $this->confCod;
        $emailObject->htmlTemplate($template, ['code' => $confirmationCode]);
        $emailObject->context(['code' => $confirmationCode]);

        try
        {
            $mailer->send($emailObject);
        }
        catch(\Exception $e)
        {
            $mensaje = "Error sending email" .$e->getMessage();
            return $mensaje;
        }
        return 0;
    }

    /**
     * Get the value of comments
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set the value of comments
     *
     * @return  self
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    
    /**
     * Get the value of favStories
     */ 
    public function getFavStories()
    {
        return $this->favStories;
    }

    /**
     * Set the value of favStories
     *
     * @return  self
     */
    public function setFavStories($favStories)
    {
        $this->favStories = $favStories;

        return $this;
    }

    public function addFavedStory($story)
    {
        $this->favStories[] = $story;
        return $this->favStories;
    }

    public function getCollabStories()
    {
        return $this->collabStories;
    }

    /**
     * @param mixed $collabStories
     */
    public function setCollabStories($collabStories)
    {
        $this->collabStories = $collabStories;

        return $this;
    }

    public function addCollabStories($story)
    {
        $current = $this->collabStories;
        $new = true;
        foreach($current as $collabStory)
        {
            if($story == $collabStory)
            {
                $new = false;
            }
        }
        if($new)
        {
            $this->collabStories[] = $story;
        }
        return $this->collabStories;
    }

}