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

    /** @var string */
    private $gpxDataApr;

    protected function setUp(): void
    {
        // Co-ords taken from Google example at https://developers.google.com/maps/documentation/utilities/polylinealgorithm
        // (38.5, -120.2), (40.7, -120.95), (43.252, -126.453) should produce: _p~iF~ps|U_ulLnnqC_mqNvxq`@
        $this->gpxDataSimple = file_get_contents(TEST_GPX_FILES_FOLDER . DIRECTORY_SEPARATOR . 'google_encoding_example.gpx');

        // An actual real track from a walk.
        $this->gpxDataApr = file_get_contents(TEST_GPX_FILES_FOLDER . DIRECTORY_SEPARATOR . '01-APR-21.GPX');
    }

    public function testSingleCoord(): void {
        $polyline = new Polyline();
        $polyline->addCoord(new Coordinate(-179.9832104, -179.9832104));
        $formatter = new PolylineGoogleEncodedFormatter();
        $encoded = $formatter->format($polyline);
        // NB Single quoted string here because backslashes can appear in the encoded output so
        // if you paste data in here you don't want it representing an escape character.
        $this->assertEquals('`~oia@`~oia@', $encoded, "Simplest example from Google successfully encoded.");
    }

    public function testEncodingToGoogleExample(): void
    {
        $polyline = Polyline::fromGpxData($this->gpxDataSimple);
        $formatter = new PolylineGoogleEncodedFormatter();
        $encoded = $formatter->format($polyline);
        // NB Single quoted string here because backslashes can appear in the encoded output so
        // if you paste data in here you don't want it representing an escape character.
        $this->assertEquals('_p~iF~ps|U_ulLnnqC_mqNvxq`@', $encoded, "Does not produce Google's example output given Google's example input.");
    }

    public function testRealWorldGpxData(): void
    {
        $polyline = Polyline::fromGpxData($this->gpxDataApr);
        $formatter = new PolylineGoogleEncodedFormatter();
        $encoded = $formatter->format($polyline);
        // NB Single quoted string here because backslashes can appear in the encoded output so
        // if you paste data in here you don't want it representing an escape character.
        $this->assertEquals('}{_yHji_ORIP@HHH@^^l@l@Pl@?ARk@Re@Rc@`@aARM@DTNBF@BZAZYJLFRPB@Bd@F??`@YDmATSLo@De@BWVk@PWh@u@\e@\q@BCNa@DCRy@@q@TmATaA@CH[Zy@T_@AK?GH[BSPgA@c@FeACaA?_AK]IBi@IKBGOa@i@U]GG_@y@[g@??]i@]{@EIIIa@{@EKa@y@KUWy@?CWy@Qw@O{@Ka@@KBC?KAYGSOy@?A?MEo@L]@AV[\WDAJUEBBBJ[Pa@Cc@M_AAq@Qk@Fa@P]H@FA@?A??AABCDEBA@A?@CDOFO@E@GTy@NQXDLND`@H~@Bh@@b@?BLh@BJD\Fd@HDHRDRDn@Tx@??FNFj@Lt@Xv@Lf@Ln@B^@TB`@BNDt@@~@B^Jx@HjAJfA?HHp@H\NbA@\D~@L~@?bA?HK|AK~@Ut@Yt@_@^KDABA@s@Ne@Fc@HI`@ETQh@GTQv@K~@G^K~@K|@In@Q~@?NEHSp@K@WHQJGPOb@AL?@ADGTQXa@h@MDKOSIIYYEG@]I]YI@UDS@O`@Et@Or@]LYCAAUY',
            $encoded,
            "Encoded output has changed since this test was first created.");
    }
}