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

class Genre
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', name: 'ID')]
    #[ORM\GeneratedValue]
    private $commentID;

    /**User ID here */
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="Stories")
     * @ORM\JoinColumn(name="ID", referencedColumnName="ID")
     */
    private $userID;

    /**Genre ID here */
    /**
     * @ORM\ManyToOne(targetEntity="Stories", inversedBy="Comments")
     * @ORM\JoinColumn(name="ID", referencedColumnName="ID")
     */
    private $storyID;

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