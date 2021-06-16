<?php

namespace Gothick\Geotools;

use Gothick\Geotools\IPolylineFormatter;
use Gothick\Geotools\Polyline;
use Gothick\Geotools\Coordinate;

class PolylineGeoJsonFormatter implements IPolylineFormatter
{
    /** @var int */
    private $precision;

    public function __construct(?float $coordPrecision = null)
    {
        $this->precision = $coordPrecision;
    }

    public function format(Polyline $polyline): string
    {
        $result = [
            'type' => 'LineString',
            'coordinates' => []
        ];

        /** @var Coordinate $coord */
        foreach ($polyline as $coord) {
            array_push($result['coordinates'], [$coord->getLng($this->precision), $coord->getLat($this->precision)]);
        }
        return json_encode($result);
    }
}
