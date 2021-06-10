<?php declare(strict_types=1);

use Gothick\Geotools\Coordinate;
use PHPUnit\Framework\TestCase;

use Gothick\Geotools\Polyline;
use Gothick\Geotools\PolylineGeoJsonFormatter;

use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;

final class PolylineGeoJsonFormatterTest extends TestCase
{
    /** @var string */
    private $simpleGpxData;

    protected function setUp(): void
    {
        // TODO: This would probably be better as a file.
        // Genuine Garmin GPX data
        $this->simpleGpxData = <<<EOT
<?xml version="1.0"?>
<gpx xmlns="http://www.topografix.com/GPX/1/1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:wptx1="http://www.garmin.com/xmlschemas/WaypointExtension/v1" xmlns:gpxtrx="http://www.garmin.com/xmlschemas/GpxExtensions/v3" xmlns:gpxtpx="http://www.garmin.com/xmlschemas/TrackPointExtension/v1" xmlns:gpxx="http://www.garmin.com/xmlschemas/GpxExtensions/v3" xmlns:trp="http://www.garmin.com/xmlschemas/TripExtensions/v1" xmlns:adv="http://www.garmin.com/xmlschemas/AdventuresExtensions/v1" xmlns:prs="http://www.garmin.com/xmlschemas/PressureExtension/v1" xmlns:tmd="http://www.garmin.com/xmlschemas/TripMetaDataExtensions/v1" xmlns:vptm="http://www.garmin.com/xmlschemas/ViaPointTransportationModeExtensions/v1" xmlns:ctx="http://www.garmin.com/xmlschemas/CreationTimeExtension/v1" xmlns:gpxacc="http://www.garmin.com/xmlschemas/AccelerationExtension/v1" xmlns:gpxpx="http://www.garmin.com/xmlschemas/PowerExtension/v1" xmlns:vidx1="http://www.garmin.com/xmlschemas/VideoExtension/v1" creator="Garmin Desktop App" version="1.1" xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd http://www.garmin.com/xmlschemas/WaypointExtension/v1 http://www8.garmin.com/xmlschemas/WaypointExtensionv1.xsd http://www.garmin.com/xmlschemas/TrackPointExtension/v1 http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd http://www.garmin.com/xmlschemas/GpxExtensions/v3 http://www8.garmin.com/xmlschemas/GpxExtensionsv3.xsd http://www.garmin.com/xmlschemas/ActivityExtension/v1 http://www8.garmin.com/xmlschemas/ActivityExtensionv1.xsd http://www.garmin.com/xmlschemas/AdventuresExtensions/v1 http://www8.garmin.com/xmlschemas/AdventuresExtensionv1.xsd http://www.garmin.com/xmlschemas/PressureExtension/v1 http://www.garmin.com/xmlschemas/PressureExtensionv1.xsd http://www.garmin.com/xmlschemas/TripExtensions/v1 http://www.garmin.com/xmlschemas/TripExtensionsv1.xsd http://www.garmin.com/xmlschemas/TripMetaDataExtensions/v1 http://www.garmin.com/xmlschemas/TripMetaDataExtensionsv1.xsd http://www.garmin.com/xmlschemas/ViaPointTransportationModeExtensions/v1 http://www.garmin.com/xmlschemas/ViaPointTransportationModeExtensionsv1.xsd http://www.garmin.com/xmlschemas/CreationTimeExtension/v1 http://www.garmin.com/xmlschemas/CreationTimeExtensionsv1.xsd http://www.garmin.com/xmlschemas/AccelerationExtension/v1 http://www.garmin.com/xmlschemas/AccelerationExtensionv1.xsd http://www.garmin.com/xmlschemas/PowerExtension/v1 http://www.garmin.com/xmlschemas/PowerExtensionv1.xsd http://www.garmin.com/xmlschemas/VideoExtension/v1 http://www.garmin.com/xmlschemas/VideoExtensionv1.xsd">
    <metadata>
    <link href="http://www.garmin.com">
        <text>Garmin International</text>
    </link>
    <time>2021-04-10T15:30:18Z</time>
    <bounds maxlat="51.451027253642678" maxlon="-2.599115688353777" minlat="51.443294528871775" minlon="-2.622673530131578"/>
    </metadata>
    <trk>
    <name>10-APR-21 16:20:22</name>
    <extensions>
        <gpxx:TrackExtension>
        <gpxx:DisplayColor>Cyan</gpxx:DisplayColor>
        </gpxx:TrackExtension>
    </extensions>
    <trkseg>
        <trkpt lat="51.450744699686766" lon="-2.621252126991749">
        <ele>1.1</ele>
        <time>2021-04-10T12:37:14Z</time>
        <extensions>
            <gpxtpx:TrackPointExtension>
            <gpxtpx:cad>0</gpxtpx:cad>
            </gpxtpx:TrackPointExtension>
            <gpxx:TrackPointExtension/>
        </extensions>
        </trkpt>
        <trkpt lat="51.450771605595946" lon="-2.621229244396091">
        <ele>3.02</ele>
        <time>2021-04-10T12:37:27Z</time>
        <extensions>
            <gpxtpx:TrackPointExtension>
            <gpxtpx:cad>0</gpxtpx:cad>
            </gpxtpx:TrackPointExtension>
            <gpxx:TrackPointExtension/>
        </extensions>
        </trkpt>
        <trkpt lat="51.450770935043693" lon="-2.621175432577729">
        <ele>4.94</ele>
        <time>2021-04-10T12:37:34Z</time>
        <extensions>
            <gpxtpx:TrackPointExtension>
            <gpxtpx:cad>0</gpxtpx:cad>
            </gpxtpx:TrackPointExtension>
            <gpxx:TrackPointExtension/>
        </extensions>
        </trkpt>
    </trkseg>
    </trk>
</gpx>
EOT;
    }

    public function testJsonIsActuallyJson(): void
    {
        $polyline = Polyline::fromGpxData($this->simpleGpxData);
        $formatter = new PolylineGeoJsonFormatter();
        $geoJson = $formatter->format($polyline);
        $this->assertNotNull(json_decode($geoJson));
    }
    public function testType(): void
    {
        $polyline = Polyline::fromGpxData($this->simpleGpxData);
        $formatter = new PolylineGeoJsonFormatter();
        $geoJson = $formatter->format($polyline);
        $decoded = json_decode($geoJson, true);
        $this->assertEquals($decoded['type'], 'LineString', 'GeoJSON type field should be LineString');
    }
    public function testCoords(): void
    {
        $polyline = Polyline::fromGpxData($this->simpleGpxData);
        $formatter = new PolylineGeoJsonFormatter();
        $geoJson = $formatter->format($polyline);
        $decoded = json_decode($geoJson, true);

        $this->assertCount(3, $decoded['coordinates'], 'GeoJSON should have three coordinates');

        $coord0 = $decoded['coordinates'][0];
        $this->assertCount(2, $coord0);
        // NB Longitude comes first with geoJSON
        $this->assertEqualsWithDelta($coord0[0], -2.621252126991749, 0.00000001, 'Point 0 incorrect longitude');
        $this->assertEqualsWithDelta($coord0[1], 51.450744699686766, 0.00000001, 'Point 0 incorrect latitude');

        $coord1 = $decoded['coordinates'][1];
        $this->assertCount(2, $coord1);
        // NB Longitude comes first with geoJSON
        $this->assertEqualsWithDelta($coord1[0], -2.621229244396091, 0.00000001, 'Point 1 incorrect longitude');
        $this->assertEqualsWithDelta($coord1[1], 51.450771605595946, 0.00000001, 'Point 1 incorrect latitude');

        $coord2 = $decoded['coordinates'][2];
        $this->assertCount(2, $coord2);
        // NB Longitude comes first with geoJSON
        $this->assertEqualsWithDelta($coord2[0], -2.621175432577729, 0.00000001, 'Point 2 incorrect longitude');
        $this->assertEqualsWithDelta($coord2[1], 51.450770935043693, 0.00000001, 'Point 2 incorrect latitude');
    }

    // TODO: Probably want to test what happens if there's only one point, say, or zero:
    // what does an invalid GeoJSON LineString actually look like? Also: maybe test for
    // additional properties that shouldn't be present.
}