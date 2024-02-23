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
    private $name;

    #[ORM\OneToMany(targetEntity:"Story", mappedBy:"genre")]
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
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

}