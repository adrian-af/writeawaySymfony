<?php
namespace App\Entity;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
#[ORM\Table(name: 'genres')]

class Genre
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', name: 'ID')]
    #[ORM\GeneratedValue]
    private $genreID;

    #[ORM\Column(type: 'string', name: 'name')]
    private $genre;

    #[ORM\OneToMany(targetEntity:"Story", mappedBy:"genreId")]
    private $stories;
    public function getStories()
    {
        return $this->stories;
    }
    public function setStories($stories)
    {
        $this->stories = $stories;
    }
    public function __construct()
    {
        $this->stories = new ArrayCollection();

    }

    /**
     * Get the value of ID
     */ 
    public function getID()
    {
        return $this->genreID;
    }

    /**
     * Get the value of email
     */ 
    public function getName()
    {
        return $this->genre;
    }

    public function setName(string $genre): self
    {
        $this->name = $genre;

        return $this;
    }

}