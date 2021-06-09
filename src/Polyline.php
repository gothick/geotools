<?php

namespace Gothick\Geotools;

use Location\Polyline as PhpGeoPolyline;
use Location\Coordinate as PhpGeoCoordinate;

class Polyline
{
    /** @var PhpGeoPolyline */
    private $phpGeoPolyline;

    public static function fromPhpGeoPolyline(PhpGeoPolyline $phpGeoPolyLine): self
    {
        $polyline = new static();
        $polyline->phpGeoPolyline = $phpGeoPolyLine;
        return $polyline;
    }

    public static function fromGpxData(string $gpxData): self
    {
        $polyLine = new static();

        $phpGeoPolyline = new PhpGeoPolyline();
        $gpx = simplexml_load_string($gpxData);
        foreach ($gpx->trk as $trk) {
            foreach($trk->trkseg as $seg){
                foreach($seg->trkpt as $pt){
                    $point = new PhpGeoCoordinate((double) $pt["lat"], (double) $pt["lon"]);
                    $phpGeoPolyline->addPoint($point);
                }
            }
        }
        unset($gpx);
        $polyLine->phpGeoPolyline = $phpGeoPolyline;
        return $polyLine;
    }

    public function containsPoint(float $lat, float $lng, float $allowedDistance = 0.001): bool
    {
        return $this->phpGeoPolyline->containsPoint(new PhpGeoCoordinate($lat, $lng), $allowedDistance);
    }

    public function getNumberOfPoints()
    {
        return $this->phpGeoPolyline->getNumberOfPoints();
    }
}