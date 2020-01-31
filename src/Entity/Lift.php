<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LiftRepository")
 */
class Lift
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $floor;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $minuteOfDay;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $floorsTraveled;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=true)
     */
    private $totalFloorsTraveledByDay;

    public static function create()
    {
        $lift = new self();

        $lift->setFloor(0);
        $lift->setMinuteOfDay(0);
        $lift->setTotalFloorsTraveledByDay(0);

        return $lift;
    }

    /**
     * @param int $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
    * @return integer
    */
    public function getFloor(): int
    {
        return $this->floor;
    }

    /**
     * @param mixed $floor
     */
    public function setFloor($floor): void
    {
        $this->floor = $floor;
    }

    /**
     * @return int
     */
    public function getMinuteOfDay()
    {
        return $this->minuteOfDay;
    }

    /**
     * @param int $minuteOfDay
     */
    public function setMinuteOfDay(int $minuteOfDay): void
    {
        $this->minuteOfDay = $minuteOfDay;
    }

    private function moveLift(int $destinationFloor): void
    {
        $actualFloor = $this->getFloor();
        $floorsTraveled = $this->getFloorsTraveled();

        if ($actualFloor < $destinationFloor) {
            $floorsTraveled += $destinationFloor - $actualFloor;
        } elseif ($actualFloor > $destinationFloor) {
            $floorsTraveled += $actualFloor - $destinationFloor;
        }

        $this->setFloor($destinationFloor);
        $this->setFloorsTraveled($floorsTraveled);
    }

    public function moveFloorToCallingFloor($callingFloor): void
    {
        $this->setFloorsTraveled(0);
        if ($this->getFloor() <> $callingFloor) {
            $this->moveLift($callingFloor);
        }
    }

    public function moveFloorToDestination($destinationFloor): void
    {
        $this->moveLift($destinationFloor);
        $totalFloorsTraveledByDay = $this->getTotalFloorsTraveledByDay() + $this->getFloorsTraveled();
        $this->setTotalFloorsTraveledByDay($totalFloorsTraveledByDay);
    }

    public function getFloorsTraveled(): ?int
    {
        return $this->floorsTraveled;
    }

    public function setFloorsTraveled(?int $floorsTraveled): self
    {
        $this->floorsTraveled = $floorsTraveled;

        return $this;
    }

    public function getTotalFloorsTraveledByDay(): ?int
    {
        return $this->totalFloorsTraveledByDay;
    }

    public function setTotalFloorsTraveledByDay(?int $totalFloorsTraveledByDay): self
    {
        $this->totalFloorsTraveledByDay = $totalFloorsTraveledByDay;

        return $this;
    }
}