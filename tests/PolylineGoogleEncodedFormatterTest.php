<?php declare(strict_types=1);

use Gothick\Geotools\Coordinate;
use PHPUnit\Framework\TestCase;

use Gothick\Geotools\Polyline;
use Gothick\Geotools\PolylineGoogleEncodedFormatter;

use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;

final class PolylineGoogleEncodedFormatterTest extends TestCase
{
    /** @var string */
    private $gpxDataSimple;

    protected function setUp(): void
    {
        // Based on a real Garmin GPX file.
        // Co-ords taken from Google example at https://developers.google.com/maps/documentation/utilities/polylinealgorithm
        // (38.5, -120.2), (40.7, -120.95), (43.252, -126.453) should produce: _p~iF~ps|U_ulLnnqC_mqNvxq`@
        $this->gpxDataSimple = file_get_contents(TEST_GPX_FILES_FOLDER . DIRECTORY_SEPARATOR . 'google_encoding_example.gpx');
    }

    public function testEncodingToGoogleExample(): void
    {
        $polyline = Polyline::fromGpxData($this->gpxDataSimple);
        $formatter = new PolylineGoogleEncodedFormatter();
        $encoded = $formatter->format($polyline);
        $this->assertEquals("_p~iF~ps|U_ulLnnqC_mqNvxq`@", $encoded, "Does not produce Google's example output given Google's example input.");
    }
}