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

    #[ORM\Column(type: 'string', name: 'text')]
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
     * Get the value of ID
     */ 
    public function getID()
    {
        return $this->commentID;
    }

    public function getUserID()
    {
        return $this->userID;
    }

    public function getStoryID()
    {
        return $this->storyID;
    }

    public function getText()
    {
        return $this->commentText;
    }

    public function setText(string $commentText): self
    {
        $this->commentText = $commentText;

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
}