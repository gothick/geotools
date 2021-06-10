<?php

namespace Gothick\Geotools;

use ArrayIterator;
use Countable;
use Gothick\Geotools\Coordinate;
use IteratorAggregate;
use PhpParser\Node\Expr\ArrayItem;
use Traversable;

class Polyline implements Countable, IteratorAggregate
{
    /** @var Coordinate[] */
    private $coords = [];

    public static function fromGpxData(string $gpxData): self
    {
        $polyline = new static();

        $gpx = simplexml_load_string($gpxData);
        foreach ($gpx->trk as $trk) {
            foreach($trk->trkseg as $seg){
                foreach($seg->trkpt as $pt){
                    $polyline->addCoord(new Coordinate((double) $pt["lat"], (double) $pt["lon"]));
                }
            }
        }
        unset($gpx);
        return $polyline;
    }

    public function addCoord(Coordinate $coord): void
    {
        $this->coords[] = $coord;
    }

    public function containsPoint(float $lat, float $lng, float $allowedDistance = 0.001): bool
    {
        $check = new Coordinate($lat, $lng);
        foreach ($this->coords as $coord) {
            if ($coord->isSameLocationAs($check, $allowedDistance)) {
                return true;
            }
        }
        return false;
    }

    public function count():int
    {
        return count($this->coords);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->coords);
    }
}