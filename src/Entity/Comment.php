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
#[ORM\Table(name: 'comments')]

class Comment
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', name: 'ID')]
    #[ORM\GeneratedValue]
    private $commentID;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "comments")]
    #[ORM\JoinColumn(name:"userId", referencedColumnName: "ID")]
    private $user;

    #[ORM\ManyToOne(targetEntity: Story::class, inversedBy: "comments")]
    #[ORM\JoinColumn(name:"storyId", referencedColumnName: "ID")]
    private $story;

    #[ORM\Column(type: 'string', name: 'content')]
    private $commentText;

    #[ORM\Column(type: 'datetime', name: 'datetime')]
    private $datetime;


    /**
     * Genres constructor
     */
    public function __construct()
    {
    }

    /**
     * Get the value of commentID
     */ 
    public function getCommentID()
    {
        return $this->commentID;
    }

    /**
     * Set the value of commentID
     *
     * @return  self
     */ 
    public function setCommentID($commentID)
    {
        $this->commentID = $commentID;

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
     * Get the value of story
     */ 
    public function getStory()
    {
        return $this->story;
    }

    /**
     * Set the value of story
     *
     * @return  self
     */ 
    public function setStory($story)
    {
        $this->story = $story;

        return $this;
    }

    /**
     * Get the value of commentText
     */ 
    public function getCommentText()
    {
        return $this->commentText;
    }

    /**
     * Set the value of commentText
     *
     * @return  self
     */ 
    public function setCommentText($commentText)
    {
        $this->commentText = $commentText;

        return $this;
    }

    /**
     * Get the value of datetime
     */ 
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set the value of datetime
     *
     * @return  self
     */ 
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }
    }
