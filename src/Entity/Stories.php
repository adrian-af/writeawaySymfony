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

class Genre
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', name: 'ID')]
    #[ORM\GeneratedValue]
    private $storiesID;

    /**User ID here */
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="Stories")
     * @ORM\JoinColumn(name="ID", referencedColumnName="ID")
     */
    private $userID;

    #[ORM\Column(type: 'string', name: 'title')]
    private $storyTitle;

    /**Genre ID here */
    /**
     * @ORM\ManyToOne(targetEntity="Genre", inversedBy="Stories")
     * @ORM\JoinColumn(name="ID", referencedColumnName="ID")
     */
    private $genreID;

    #[ORM\Column(type: 'string', name: 'text')]
    private $storyText;

    #[ORM\Column(type: 'smaillint', name: 'public')]
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
    public function getID()
    {
        return $this->storiesID;
    }

    public function getUserID()
    {
        return $this->userID;
    }

    /**
     * Get the value of title
     */
    public function getTitle(): ?string
    {
        return $this->storyTitle;
    }

    public function getGenreID()
    {
        return $this->genreID;
    }

    public function setTitle(string $storyTitle): self
    {
        $this->storyTitle = $storyTitle;

        return $this;
    }

    public function getText()
    {
        return $this->storyText;
    }

    public function setText(string $storyText): self
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
}