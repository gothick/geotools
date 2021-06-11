<?php

namespace Gothick\Geotools;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Exception;
use Gothick\Geotools\Coordinate;
use IteratorAggregate;
use Traversable;

class Polyline implements Countable, IteratorAggregate, ArrayAccess
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

    /**
     * Warning: not actually a centroid. If this were a real centroid function I'd
     * probably be calculating the convex hull or at least the bounding box, paying
     * attention to great circle distance, caring about crossing the dateline and
     * all sorts of things. For my purposes--walking around Bristol--just the average
     * of the latitudes and longitudes is good enough for now.
     */
    public function getCentroid(): Coordinate
    {
        $count = count($this->coords);
        if ($count == 0) {
            throw new Exception("Can't find the centre point of a zero-length Polyline");
        }
        // "Centroid" my backside. Just average the latitudes and longitudes. I feel a
        // bit dirty doing this with a Coordinate as the sum aggregator, but it's
        // convenient.
        $summed = array_reduce($this->coords, function(Coordinate $carry, Coordinate $coord) {
            return new Coordinate($carry->getLat() + $coord->getLat(), $carry->getLng() + $coord->getLng());
        }, new Coordinate(0, 0));
        return new Coordinate($summed->getLat() / $count, $summed->getLng() / $count);
    }

    public function count(): int
    {
        return count($this->coords);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->coords);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->coords[$offset]);
    }
    public function offsetGet($offset)
    {
        return $this->coords[$offset];
    }

    // I don't think we'll actually use these, but hey...
    public function offsetSet($offset, $value): void
    {
        $this->coords[$offset] = $value;
    }
    public function offsetUnset($offset): void
    {
        unset($this->coords[$offset]);
    }
}