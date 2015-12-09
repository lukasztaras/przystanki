<?php

namespace BusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BusLine
 *
 * @ORM\Table(name="bus_line")
 * @ORM\Entity(repositoryClass="BusBundle\Repository\BusLineRepository")
 */
class BusLine
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
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $endpoint;

    /**
     * @ORM\OneToMany(targetEntity="BusDeparture", mappedBy="busLine")
     */
    private $busDepartures;


    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function getBusDepartures()
    {
        return $this->busDepartures;
    }

    public function setName($value)
    {
        $this->name = $value;
        return $this;
    }

    public function setEndpoint($value)
    {
        $this->endpoint = $value;
        return $this;
    }

    public function addDeparture(BusDeparture $departure)
    {
        $this->busDepartures->add($departure);
    }
}

