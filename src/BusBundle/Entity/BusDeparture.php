<?php

namespace BusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;

/**
 * BusDeparture
 *
 * @ORM\Table(name="bus_departure")
 * @ORM\Entity(repositoryClass="BusBundle\Repository\BusDepartureRepository")
 */
class BusDeparture
{
    const TYPE_WEEKDAY = 1;
    const TYPE_SATURDAY = 2;
    const TYPE_HOLIDAY = 3;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="BusStop", inversedBy="busDepartures")
     */
    private $busStop;

    /**
     * @ORM\ManyToOne(targetEntity="BusLine", inversedBy="busDepartures")
     */
    private $busLine;

    /**
     * @ORM\Column(type="time")
     */
    private $departureTime;

    /**
     * @ORM\Column(type="integer")
     */
    private $departureType;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getBusStop()
    {
        return $this->busStop;
    }

    public function getBusLine()
    {
        return $this->busLine;
    }

    public function getDepartureTime()
    {
        return $this->departureTime;
    }

    public function getDepartureType()
    {
        return $this->departureType;
    }

    public function setDepartureTime($value)
    {
        $this->departureTime = $value;
        return $this;
    }

    public function setDepartureType($value)
    {
        $this->departureType = $value;
        return $this;
    }

    public function setBusLine($value)
    {
        $this->busLine = $value;
        return $this;
    }

    public function setBusStop($value)
    {
        $this->busStop = $value;
        return $this;
    }
}

