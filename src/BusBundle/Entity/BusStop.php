<?php

namespace BusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use Doctrine\Common\Collections\Criteria;

/**
 * BusStop
 *
 * @ORM\Table(name="bus_stop")
 * @ORM\Entity(repositoryClass="BusBundle\Repository\BusStopRepository")
 * @ExclusionPolicy("all")
 */
class BusStop
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Expose
     */
    private $name;

    /**
     *
     * @ORM\OneToMany(targetEntity="BusDeparture", mappedBy="busStop")
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

    public function getDepartures()
    {
        return $this->busDepartures;
    }

    public function setName($value)
    {
        $this->name = $value;
        return $this;
    }
}

