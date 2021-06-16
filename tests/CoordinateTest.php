<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use Gothick\Geotools\Coordinate;

final class CoordinateTest extends TestCase
{
    /** @var string */
    private $simpleGpxData;

    protected function setUp(): void
    {

    }

    public function testCoordinateBasics(): void
    {
        $coord = new Coordinate(51.450744699686766, -2.621252126991749);
        $this->assertEqualsWithDelta(51.450744699686766, $coord->getLat(), 0.0000000001, 'Problem setting/getting latitude');
        $this->assertEqualsWithDelta(-2.621252126991749, $coord->getLng(), 0.0000000001, 'Problem setting/getting latitude');
    }
    public function testCoordinateDistance(): void
    {
        $c1 = new Coordinate(51.450744699686766, -2.621252126991749);
        $c2 = new Coordinate(51.450744699686766, -2.621252126991749); // Same as $c1
        $c3 = new Coordinate(51.450770935043693, -2.621175432577729); // Short distance away

        $this->assertTrue($c1->isSameLocationAs($c2), 'Identical locations should be the same');
        $this->assertFalse($c1->isSameLocationAs($c3), 'Different locations should be different');
        $this->assertTrue($c1->isSameLocationAs($c3, 10), 'Different locations within tolerance should be treated as the same');
    }
    public function testCoordinateRounding(): void
    {
        $c1 = new Coordinate(51.450744699686766, -2.621252126991749);
        $c2 = new Coordinate(51.450744699686766, -2.621252126991749); // Same as $c1
        $c3 = new Coordinate(51.450770935043693, -2.621175432577729); // Short distance away

        $this->assertFalse($c1->isSameLocationAs($c3), 'Unrounded locations should be different');

        $r1 = Coordinate::rounded($c1, 3);
        $r3 = Coordinate::rounded($c3, 3);
        $this->assertTrue($r1->isSameLocationAs($r3), 'Rounded locations should be within tolerance');

        // Do we need to add some windage here with assertEqualsWithDelta?
        $this->assertEquals(51.451, $r1->getLat(), "Incorrect latitude rounding");
        $this->assertEquals(-2.621, $r1->getLng(), "Incorrect longitude rounding");
    }
}