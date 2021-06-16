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
    private function round(float $val, ?float $precision): float
    {
        if ($precision === null) {
            return $val;
        }
        return round($val, $precision);
    }
    public function getLat(?float $precision = null): float
    {
        return $this->round($this->lat, $precision);
    }
    public function getLng(?float $precision = null): float
    {
        return $this->round($this->lng, $precision);
    }
    public function isSameLocationAs(Coordinate $compare, float $toleranceMetres = 0.001): bool
    {
        $distance = Haversini::calculate($this->getLat(), $this->getLng(), $compare->getLat(), $compare->getLng(), 'm');
        return $distance <= $toleranceMetres;
    }
    public static function rounded(self $in, ?float $precision = null): self
    {
        return new self($in->getLat($precision), $in->getLng($precision));
    }
}
