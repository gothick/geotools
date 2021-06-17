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

        // Going fully manual rather than using json_encode because json_encode
        // seems to introduce unwarranted levels of precision, and is at the mercy
        // of serialize_precision https://stackoverflow.com/questions/42981409/php7-1-json-encode-float-issue

        // e.g.  { "type": "LineString", "coordinates": [ [100.0, 0.0], [101.0, 1.0] ]}
        // e.g. {"type":"LineString","coordinates":[[-2.6212521269917][51.450744699687],[-2.6212292443961][51.450771605596],[-2.6211754325777][51.450770935044]]}

        $json = '{"type":"LineString","coordinates":[';

        /** @var Coordinate $coord */
        $coords = [];
        foreach ($polyline as $coord) {
            $lat = $coord->getLat();
            $lng = $coord->getLng();
            if ($this->precision !== null) {
                $lat = round($lat, $this->precision);
                $lng = round($lng, $this->precision);
            }
            $coords[] = '[' . $lng . ', ' . $lat . ']';
        }
        $json .= implode(",", $coords);
        // return json_encode($result);
        $json .= ']}';
        return $json;
    }
}
