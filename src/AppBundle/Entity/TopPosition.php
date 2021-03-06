<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="top_position", uniqueConstraints={@ORM\UniqueConstraint(name="top_place", columns={"place", "date"})})
 */
class TopPosition implements \JsonSerializable
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(type="integer", options={"length":7,"unsigned":true})
	 */
	private $place;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $date;

	/**
     * @ORM\ManyToOne(targetEntity="Film", cascade={"persist"})
	 * @ORM\JoinColumn(name="film_id", referencedColumnName="id")
     */
	private $film;

    /**
	 * @ORM\Column(type="integer", options={"unsigned":true}) 
	 */
	private $votes;

	/**
	 * @ORM\Column(type="decimal", options={"scale":3})
	 */
	private $raiting;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set place
     *
     * @param integer $place
     *
     * @return TopPosition
     */
    public function setPlace($place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return integer
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set date
     *
     * @param integer $date
     *
     * @return TopPosition
     */
    public function setDate(\DateTime $date)
    {
		$this->date = $date;
		$this->date->setTime(0, 0, 0);
        return $this;
    }

    /**
     * Get date
     *
     * @return integer
     */
    public function getDate()
    {
        return $this->date->format('Y-m-d');
    }

    /**
     * Set votes
     *
     * @param integer $votes
     *
     * @return TopPosition
     */
    public function setVotes($votes)
    {
        $this->votes = $votes;

        return $this;
    }

    /**
     * Get votes
     *
     * @return integer
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * Set raiting
     *
     * @param string $raiting
     *
     * @return TopPosition
     */
    public function setRaiting($raiting)
    {
        $this->raiting = $raiting;

        return $this;
    }

    /**
     * Get raiting
     *
     * @return string
     */
    public function getRaiting()
    {
        return $this->raiting;
    }

    /**
     * Set film
     *
     * @param \AppBundle\Entity\Film $film
     *
     * @return TopPosition
     */
    public function setFilm(\AppBundle\Entity\Film $film = null)
    {
        $this->film = $film;
        return $this;
    }

    /**
     * Get film
     *
     * @return \AppBundle\Entity\Film
     */
    public function getFilm()
    {
        return $this->film;
    }

	public function jsonSerialize()
	{
		$vars = get_object_vars($this);
		$vars['date'] = $this->date->format('Y-m-d');
		return (object)$vars;
	}
}
