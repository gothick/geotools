<?php

namespace Gothick\Geotools;

use Gothick\Geotools\IPolylineFormatter;
use Gothick\Geotools\Polyline;
use Gothick\Geotools\Coordinate;

class PolylineGeoJsonFormatter implements IPolylineFormatter
{
    public function format(Polyline $polyline): string
    {
        $result = [
            'type' => 'LineString',
            'coordinates' => []
        ];

        /** @var Coordinate $coord */
        foreach ($polyline as $coord) {
            array_push($result['coordinates'], [$coord->getLng(), $coord->getLat()]);
        }
        return json_encode($result);
    }
}
