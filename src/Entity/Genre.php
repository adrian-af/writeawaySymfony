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

    /**
     * Genres constructor
     */
    public function __construct()
    {
        $this->genre = new ArrayCollection();
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

    public function setID(int $genreID): self
    {
        $this->ID = $genreID;

        return $this;
    }

}