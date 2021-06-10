<?php

namespace Gothick\Geotools;

use ArrayAccess;
use Exception;
use Haversini\Haversini;

class Coordinate
{
    /** @var float */
    private $lat;
    /** @var float */
    private $lng;

    public function __construct(float $lat, float $lng)
    {
        $this->lat = $lat;
        $this->lng = $lng;
    }
    public function getLat(): float
    {
        return $this->lat;
    }
    public function getLng(): float
    {
        return $this->lng;
    }
    public function isSameLocationAs(Coordinate $compare, float $toleranceMetres = 0.001): bool
    {
        $distance = Haversini::calculate($this->getLat(), $this->getLng(), $compare->getLat(), $compare->getLng(), 'm');
        return $distance <= $toleranceMetres;
    }
}
