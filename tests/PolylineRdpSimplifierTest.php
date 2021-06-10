<?php declare(strict_types=1);

use Gothick\Geotools\Coordinate;
use PHPUnit\Framework\TestCase;
use Gothick\Geotools\Polyline;
use Gothick\Geotools\PolylineGeoJsonFormatter;
use Gothick\Geotools\PolylineRdpSimplifier;

final class PolylineRdpSimplifierTest extends TestCase
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
        // TODO: This would probably be better as a file.
        // Genuine Garmin GPX data
        $this->gpxDataSimple = file_get_contents(TEST_GPX_FILES_FOLDER . DIRECTORY_SEPARATOR . 'simple.gpx');
        $this->gpxDataApr = file_get_contents(TEST_GPX_FILES_FOLDER . DIRECTORY_SEPARATOR . '01-APR-21.GPX');
        $this->gpxDataFeb = file_get_contents(TEST_GPX_FILES_FOLDER . DIRECTORY_SEPARATOR . '01-FEB-21.GPX');
        $this->gpxDataDec = file_get_contents(TEST_GPX_FILES_FOLDER . DIRECTORY_SEPARATOR . '01-DEC-20.GPX');
    }

    public function testNoChangeSimplification(): void
    {
        $polyline = Polyline::fromGpxData($this->gpxDataSimple);
        $simplifier = new PolylineRdpSimplifier(1); // 1 metre should keep every point the same
        $simplified = $simplifier->ramerDouglasPeucker($polyline);
        $this->assertEquals(3, count($simplified), "Polyline should contain the three points from the GPX");
        $this->assertTrue($simplified->containsPoint(51.450744699686766, -2.621252126991749), 'Polyline missing first GPX point');
        $this->assertTrue($simplified->containsPoint(51.450771605595946, -2.621229244396091), 'Polyline missing second GPX point');
        $this->assertTrue($simplified->containsPoint(51.450770935043693, -2.621175432577729), 'Polyline missing third GPX point');
    }

    public function testSimpleSimplification(): void
    {
        $polyline = Polyline::fromGpxData($this->gpxDataSimple);
        $simplifier = new PolylineRdpSimplifier(3); // 3 metres epsilon should eliminate a single point
        $simplified = $simplifier->ramerDouglasPeucker($polyline);
        $this->assertEquals(2, count($simplified), "Polyline should contain the three points from the GPX");
        $this->assertTrue($simplified->containsPoint(51.450744699686766, -2.621252126991749), 'Polyline missing first GPX point');
        $this->assertTrue($simplified->containsPoint(51.450770935043693, -2.621175432577729), 'Polyline missing third GPX point');
    }

    public function testSimplifyLongerPath(): void
    {
        $polyline = Polyline::fromGpxData($this->gpxDataDec);
        $this->assertEquals(300, count($polyline));
        $simplifier = new PolylineRdpSimplifier(3); // 3 metres epsilon should eliminate a lot of points
        $simplified = $simplifier->ramerDouglasPeucker($polyline);
        $this->assertEquals(115, count($simplified), "Simplified Polyline with 3-metre epsilon should contain 115 points");

        $even_simplifier = new PolylineRdpSimplifier(10); // Should strip a lot more points
        $even_simplifieder = $even_simplifier->ramerDouglasPeucker($polyline);
        $this->assertEquals(57, count($even_simplifieder), "Simplified Polyline with 10-metre epsilon should contain 57 points");
    }
}
