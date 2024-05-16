<?php
namespace App\Entity;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\SmallIntType;

/**
 * @ORM\Entity
 */

#[ORM\Entity]
#[ORM\Table(name: 'stories')]

class Story
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', name: 'ID')]
    #[ORM\GeneratedValue]
    private $storyID;
    
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "stories")]
    #[ORM\JoinColumn(name:"userId", referencedColumnName: "ID")]
    private $user;

    #[ORM\Column(type: 'string', name: 'title')]
    private $storyTitle;
    
    #[ORM\ManyToOne(targetEntity: Genre::class, inversedBy: "stories")]
    #[ORM\JoinColumn(name:"genreId", referencedColumnName: "ID")]
    private $genre;

    #[ORM\Column(type: 'string', name: 'text')]
    private $storyText;

    #[ORM\Column(name: 'public')]
    private $public;

    #[ORM\Column(type: 'datetime', name: 'datetime')]
    private $datetime;

    #[ORM\OneToMany(targetEntity:"Comment", mappedBy: "story")]
    private $comments;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: "favStories")]
    private $usersThatFaved; //array of Users that have faved this story


    /**
     * Genres constructor
     */
    public function __construct()
    {
    }
    /**
     * Get the value of ID
     */ 
    public function getStoryID()
    {
        return $this->storyID;
    }

    public function setStoryID($storyID)
    {
        $this->storyID = $storyID;
        return $this;
    }

    /**
     * Get the value of title
     */
    public function getStoryTitle(): ?string
    {
        return $this->storyTitle;
    }

    public function getGenre(): ?Genre
    {
        return $this->genre;
    }
    public function setGenre(Genre $genre)
    {
        $this->genre = $genre;

        return $this;
    }

    public function setStoryTitle(string $storyTitle): self
    {
        $this->storyTitle = $storyTitle;

        return $this;
    }

    public function getStoryText()
    {
        return $this->storyText;
    }

    public function setStoryText(string $storyText): self
    {
        $this->storyText = $storyText;

        return $this;
    }

    public function getPublic()
    {
        return $this->public;
    }

    public function setPublic($public): self
    {
        $this->public = $public;

        return $this;
    }

    public function getDatetime()
    {
        return $this->datetime;
    }

    public function setDatetime(DateTime $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * Get the value of user
     */ 
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */ 
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
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
     * Get the value of favUsers
     */ 
    public function getUsersThatFaved()
    {
        return $this->usersThatFaved;
    }

    /**
     * Set the value of favUsers
     *
     * @return  self
     */ 
    public function setUsersThatFaved($usersThatFaved)
    {
        $this->usersThatFaved = $usersThatFaved;

        return $this;
    }

    public function addUserThatFaved($user)
    {
        $this->usersThatFaved[] = $user;
        return $this->usersThatFaved;
    }
}