<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="film")
 */
class Film implements \JsonSerializable
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="integer", options={"unique":true})
	 */
	private $kinopoiskId;
	
	/**
     * @ORM\Column(type="text")
     */
	private $name;
	
	/**
     * @ORM\Column(type="text", nullable=true)
     */
	private $originalName;
	
	/**
	 * @ORM\Column(type="integer", options={"unsigned":true})
	 */
	private $year;


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
     * Set kinopoiskId
     *
     * @param integer $kinopoiskId
     *
     * @return Film
     */
    public function setKinopoiskId($kinopoiskId)
    {
        $this->kinopoiskId = $kinopoiskId;

        return $this;
    }

    /**
     * Get kinopoiskId
     *
     * @return integer
     */
    public function getKinopoiskId()
    {
        return $this->kinopoiskId;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Film
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set originalName
     *
     * @param string $originalName
     *
     * @return Film
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;

        return $this;
    }

    /**
     * Get originalName
     *
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * Set year
     *
     * @param integer $year
     *
     * @return Film
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
}
