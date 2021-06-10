<?php

/**
 * Based on code: http://www.loughrigg.org/rdp/ by David R. Edgar
 * and an earlier version of https://github.com/krasilnikov-spb by
 * Alexander Krasilnikov <alexander@krasilnikov.spb.ru>
 *
 * The author has placed this work in the Public Domain, thereby relinquishing all copyrights.
 * You may use, modify, republish, sell or give away this work without prior consent.
 * This implementation comes with no warranty or guarantee of fitness for any purpose.
 *
 * =========================================================================
 * An implementation of the Ramer-Douglas-Peucker algorithm for reducing
 * the number of points on a polyline
 * see http://en.wikipedia.org/wiki/Ramer%E2%80%93Douglas%E2%80%93Peucker_algorithm
 * =========================================================================
 */


namespace Gothick\Geotools;

use Exception;

/**
 * Class phpRdp
 * @package phpRdp
 */
class PolylineRdpSimplifier
{
    private $epsilon = 0;
    private $earthRadius = 0;

    /**
     * phpRdp constructor.
     * @param $lat_path - set path inside each value of object for latitude using dot as separator,
     *                    e.g. "point.lat"
     * @param $lon_path - set path inside each value of object for longitude using dot as separator,
     *                    e.g. "point.lat"
     * @param $epsilon - epsilon value, in km.
     * @param $epsilon_dimension - epsilon dimension "km" - kilometers, "mi" - miles, "m" - meters
     * @throws string -
     */

    function __construct($epsilon = 1, $epsilon_dimension = "m")
    {
        $this->epsilon = $epsilon;
        if ($epsilon_dimension == "km") {
            $this->earthRadius = 6371;
        } elseif ($epsilon_dimension == "mi") {
            $this->earthRadius = 3959;
        } elseif ($epsilon_dimension == "m") {
            $this->earthRadius = 6371000;
        } else {
            throw new Exception('Incorrect epsilon_dimension');
        }
    }

    /**
     * Calculates perpendicular distance from a point to a straight line.
     * All coordinates MUST be in the same dimensions, e.g. km.
     * @param $ptX - x coordinate for testing point
     * @param $ptY - y coordinate for testing point
     * @param $l1x - x coordinate for left point
     * @param $l1y - y coordinate for left point
     * @param $l2x - x coordinate for right point
     * @param $l2y - y coordinate for right point
     * @return float
     */
    private function perpendicularDistance($ptX, $ptY, $l1x, $l1y, $l2x, $l2y)
    {
        $result = 0;
        if ($l2x == $l1x) {
            //vertical lines - treat this case specially to avoid divide by zero
            $result = abs($ptX - $l2x);
        } else {
            $slope = (($l2y - $l1y) / ($l2x - $l1x));
            $passThroughY = (0 - $l1x) * $slope + $l1y;
            $result = (abs(($slope * $ptX) - $ptY + $passThroughY)) / (sqrt($slope * $slope + 1));
        }
        return $result;
    }


    /**
     * Convert coordinate to coordinate system relative to gps point 0,0.
     * @param $lat - geographic coordinate, latitude
     * @param $lon - geographic coordinate, longitude
     * @return array - two variables with relative distances lat, lon in km
     */

    private function convertLatLonToAbsKm($lat, $lon)
    {
        $new_lat_in_km = $this->haversineGreatCircleDistance(0, 0, $lat, 0);
        $new_lon_in_km = $this->haversineGreatCircleDistance(0, 0, 0, $lon);
        return [$new_lat_in_km, $new_lon_in_km];
    }

    /**
     * Calculates the great-circle distance between two points, with
     * the Haversine formula.
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @return float Distance between points in (same as earthRadius)
     */
    private function haversineGreatCircleDistance(
        $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo)
    {
        $earthRadius = $this->earthRadius;
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    /**
     * RamerDouglasPeucker
     * Do initial prepare of data and call doRamerDouglasPeucker for simplification.
     *
     * @param $pointList - array of track. Each value of array represents one geopoint.
     * @return array - simplified track
     * @throws string - in case of absence or incorrect data in path
     */
    public function ramerDouglasPeucker(Polyline $polyline): Polyline
    {
        $epsilon = $this->epsilon;
        $pointList_short = [];
        foreach ($polyline as $key => $coord) {
            list($lat, $lon) = $this->convertLatLonToAbsKm(
                $coord->getLat(),
                $coord->getLng()
            );
            $pointList_short[$key] = ['lat' => $lat, 'lon' => $lon, 'key' => $key];
        }
        $pointList_short = $this->doRamerDouglasPeucker($pointList_short, $epsilon);

        $output = new Polyline();
        foreach ($pointList_short as $foo => $point){
            $output->addCoord($polyline[$point['key']]);
        }
        return $output;
    }

    /**
     * doRamerDouglasPeucker
     * Reduces the number of points on a polyline by removing those that are closer to the line
     * than the distance $epsilon.
     * It is assumed that the coordinates and distance $epsilon are given in the same units.
     * The result is returned as an array in a similar format.
     * Each point returned in the result array will retain all its original data, including its E and N
     * values along with any others.
     *
     * @param $pointList - array of track. Each value of array represents one geopoint.
     * @param $epsilon - epsilon value in km.
     * @return array - simplified track
     * @throws string - in case of absence or incorrect data in path
     */
    private function doRamerDouglasPeucker($pointList, $epsilon)
    {
        // Find the point with the maximum distance
        $dmax = 0;
        $index = 0;
        $totalPoints = count($pointList);
        for ($i = 1; $i < ($totalPoints - 1); $i++) {

            $d = $this->perpendicularDistance($pointList[$i]["lat"], $pointList[$i]["lon"],
                $pointList[0]["lat"], $pointList[0]["lon"],
                $pointList[$totalPoints - 1]["lat"], $pointList[$totalPoints - 1]["lon"]);

            if ($d > $dmax) {
                $index = $i;
                $dmax = $d;
            }
        }

        // If max distance is greater than epsilon, recursively simplify
        if ($dmax >= $epsilon) {
            // Recursive call
            $recResults1 = $this->doRamerDouglasPeucker(array_slice($pointList, 0, $index + 1), $epsilon);
            $recResults2 = $this->doRamerDouglasPeucker(array_slice($pointList, $index, $totalPoints - $index), $epsilon);

            // Build the result list
            $resultList = array_merge(array_slice($recResults1, 0, count($recResults1) - 1),
                array_slice($recResults2, 0, count($recResults2)));
        } else {
            $resultList = array($pointList[0], $pointList[$totalPoints - 1]);
        }

        // Return the result
        return $resultList;
    }
}