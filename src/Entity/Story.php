<?php
namespace App\Entity;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

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

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="stories")
     * @ORM\JoinTable(
     *      name="rel_story_user",
     *      joinColumns={@ORM\JoinColumn(name="story_id", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="ID")}
     * )
     */
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


    /**
     * Genres constructor
     */
    public function __construct()
    {
        $this->user = new ArrayCollection();
        $this->comments = new ArrayCollection();
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


}