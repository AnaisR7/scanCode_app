<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductRepository")
 */
class Product
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="barCode", type="string")
     */
    private $barCode;

    /**
     * @var int
     *
     * @ORM\Column(name="nbConsultation", type="integer")
     */
    private $nbConsultation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastView_date", type="string")
     */
    private $lastViewDate;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set barCode
     *
     * @param integer $barCode
     *
     * @return Product
     */
    public function setBarCode($barCode)
    {
        $this->barCode = $barCode;

        return $this;
    }

    /**
     * Get barCode
     *
     * @return int
     */
    public function getBarCode()
    {
        return $this->barCode;
    }

    /**
     * Set nbConsultation
     *
     * @param integer $nbConsultation
     *
     * @return Product
     */
    public function setNbConsultation($nbConsultation)
    {
        $this->nbConsultation = $nbConsultation;

        return $this;
    }

    /**
     * Get nbConsultation
     *
     * @return int
     */
    public function getNbConsultation()
    {
        return $this->nbConsultation;
    }

    /**
     * Set lastViewDate
     *
     * @param \DateTime $lastViewDate
     *
     * @return Product
     */
    public function setLastViewDate($lastViewDate)
    {
        $this->lastViewDate = $lastViewDate;

        return $this;
    }

    /**
     * Get lastViewDate
     *
     * @return \DateTime
     */
    public function getLastViewDate()
    {
        return $this->lastViewDate;
    }
}

