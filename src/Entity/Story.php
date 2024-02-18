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

    #[ORM\Column(type: 'integer', name: 'userId')]
    private $userid;

    
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "stories")]
    #[ORM\JoinColumn(name:"userId", referencedColumnName: "ID")]
    private $user;

    #[ORM\Column(type: 'string', name: 'title')]
    private $storyTitle;

    /**Genre ID here */
    #[ORM\ManyToOne(targetEntity: Genre::class, inversedBy: "genres")]
    #[ORM\JoinColumn(name:"genreId", referencedColumnName: "ID")]
    private $genreID;

    #[ORM\Column(type: 'string', name: 'text')]
    private $storyText;

    #[ORM\Column(name: 'public')]
    private $public;

    #[ORM\Column(type: 'datetime', name: 'datetime')]
    private $datetime;

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

    public function getUserID()
    {
        return $this->userID;
    }
    

    /**
     * Get the value of title
     */
    public function getStoryTitle(): ?string
    {
        return $this->storyTitle;
    }

    public function getGenreID()
    {
        return $this->genreID;
    }
    public function setGenreID(int $genreID)
    {
        $this->genreId = $genreID;

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

    public function setPublic(SmallIntType $public): self
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
}