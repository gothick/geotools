<?php

namespace Gothick\Geotools;

use Gothick\Geotools\Polyline;

interface IPolylineFormatter
{
    public function format(Polyline $p): string;
}
