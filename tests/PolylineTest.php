<?php declare(strict_types=1);

use Gothick\Geotools\Coordinate;
use PHPUnit\Framework\TestCase;
use Gothick\Geotools\Polyline;
use Gothick\Geotools\PolylineRdpSimplifier;

final class PolylineTest extends TestCase
{
    /** @var string */
    private $gpxDataSimple;

    /** @var string */
    private $gpxDataApr;

    /** @var string */
    private $gpxDataDec;

    /** @var string */
    private $gpxDataFeb;

    protected function setUp(): void
    {
        $this->gpxDataSimple = file_get_contents(TEST_GPX_FILES_FOLDER . DIRECTORY_SEPARATOR . 'simple.gpx');
        $this->gpxDataApr = file_get_contents(TEST_GPX_FILES_FOLDER . DIRECTORY_SEPARATOR . '01-APR-21.GPX');
        $this->gpxDataFeb = file_get_contents(TEST_GPX_FILES_FOLDER . DIRECTORY_SEPARATOR . '01-FEB-21.GPX');
        $this->gpxDataDec = file_get_contents(TEST_GPX_FILES_FOLDER . DIRECTORY_SEPARATOR . '01-DEC-20.GPX');
    }

    public function testPolylineFromGpx(): void
    {
        $polyline = Polyline::fromGpxData($this->gpxDataSimple);
        $this->assertEquals(3, count($polyline), "Polyline should contain the three points from the GPX");
        $this->assertTrue($polyline->containsPoint(51.450744699686766, -2.621252126991749), 'Polyline missing first GPX point');
        $this->assertTrue($polyline->containsPoint(51.450771605595946, -2.621229244396091), 'Polyline missing second GPX point');
        $this->assertTrue($polyline->containsPoint(51.450770935043693, -2.621175432577729), 'Polyline missing third GPX point');
        $this->assertFalse($polyline->containsPoint(51, -2), "Polyline shouldn't contain arbitrary point");
    }
    public function testAddCoord(): void
    {
        $polyline = Polyline::fromGpxData($this->gpxDataSimple);
        $this->assertEquals(3, count($polyline), "Initial Polyline should have three points");
        $polyline->addCoord(new Coordinate(0, 0));
        $this->assertEquals(4, count($polyline), "Polyline should contain four points after addition of a new point.");
        $this->assertTrue($polyline->containsPoint(0, 0), "Polyline should contain the point we just added.");
    }
    public function testIterator(): void
    {
        $polyline = Polyline::fromGpxData($this->gpxDataSimple);
        $items = 0;
        foreach ($polyline as $coord) {
            if ($items == 0) {
                $this->assertTrue($coord->isSameLocationAs(new Coordinate(51.450744699686766, -2.621252126991749)), "Coord mismatch on iterator 0 element");
            }
            if ($items == 1) {
                $this->assertTrue($coord->isSameLocationAs(new Coordinate(51.450771605595946, -2.621229244396091)), "Coord mismatch on iterator 1 element");
            }
            if ($items == 2) {
                $this->assertTrue($coord->isSameLocationAs(new Coordinate(51.450770935043693, -2.621175432577729)), "Coord mismatch on iterator 2 element");
            }
            $items++;
        }
        $this->assertEquals($items, 3, "Iterator should have iterated three elements.");
    }
    public function testPointlessCentroid(): void
    {
        $polyline = new Polyline();
        $this->expectException(Exception::class, "Trying to find the centre of an empty centroid should throw.");
        $polyline->getCentroid();
    }

    public function testCentroid(): void
    {
        $polyline = Polyline::fromGpxData($this->gpxDataSimple);
        $centroid = $polyline->getCentroid();
        $this->assertTrue($centroid->isSameLocationAs(new Coordinate(51.450762413442, -2.6212189346552), 1), "Calculated centroid was more than a metre away from where I think it should be");

        $polyline = Polyline::fromGpxData($this->gpxDataFeb);
        $centroid = $polyline->getCentroid();
        $this->assertTrue($centroid->isSameLocationAs(new Coordinate(51.451882930796, -2.6139644998652), 1), "Calculated centroid was more than a metre away from where I think it should be for February wander data");

        $polyline = Polyline::fromGpxData($this->gpxDataApr);
        $centroid = $polyline->getCentroid();
        $this->assertTrue($centroid->isSameLocationAs(new Coordinate(51.447778836782, -2.6170428407548), 1), "Calculated centroid was more than a metre away from where I think it should be for April wander data");

        $polyline = Polyline::fromGpxData($this->gpxDataDec);
        $centroid = $polyline->getCentroid();
        $this->assertTrue($centroid->isSameLocationAs(new Coordinate(51.446707723942, -2.6188230243511), 1), "Calculated centroid was more than a metre away from where I think it should be for December wander data");

    }
}
