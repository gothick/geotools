<?php

/**
 * Implementation of Google Maps' Encoded Polyline Algorithm Format
 * https://developers.google.com/maps/documentation/utilities/polylinealgorithm
 *
 * This is not exactly an efficient implementation; it's my literal first attempt,
 * and I'm only putting my own walking routes on a single website, for my own use,
 * and I cache the encoded lines anyway, so if you're looking for code to copy,
 * someone else's might be better...
 */

namespace Gothick\Geotools;

use Gothick\Geotools\IPolylineFormatter;
use Gothick\Geotools\Polyline;
use Gothick\Geotools\Coordinate;

class PolylineGoogleEncodedFormatter implements IPolylineFormatter
{
    /** @var int */
    private $precision;

    public function __construct()
    {
    }

    // Based on example at https://developers.google.com/maps/documentation/utilities/polylinealgorithm.
    private function encode(int $val): string {
        $output = "";
        $val = $val < 0 ? ~($val << 1) : ($val << 1 );
        while (($val & ~0b11111) != 0) {
            $output .= chr((($val & 0b11111) | 0b100000) + 63);
            $val = $val >> 5;
        }
        $output .= chr($val + 63);
        return $output;
    }

    public function format(Polyline $polyline): string
    {
        /** @var Coordinate $coord */
        $encoded = "";
        $prevlat = 0;
        $prevlng = 0;
        foreach ($polyline as $coord) {
            $lat = (int) round($coord->getLat() * 100000);
            $lng = (int) round($coord->getLng() * 100000);
            $deltaLat = $lat - $prevlat;
            $deltaLng = $lng - $prevlng;
            $prevlat = $lat;
            $prevlng = $lng;
            $encoded .= $this->encode($deltaLat) . $this->encode($deltaLng);
        }
        return $encoded;
    }
}
