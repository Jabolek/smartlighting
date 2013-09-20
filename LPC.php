<?php

class LanternPositionsCalculator {

    private $lanterns;
    private $lastLantern;
    private $roadCoords;
    private $totalRoadCoords;
    private $roadRadius;
    private $roadDiameter;
    private $bulbRadius;
    private $bulbDiameter;
    private $lanternsOnBothSides;
    private $scalingEnabled;
    private $startPoint;

    function __construct($scalingEnabled = true) {

        $this->scalingEnabled = $scalingEnabled;
    }

    function calculateLanternPositions($roadCoords, $roadDiameter, $bulbRadius) {

        try {

            $this->initParams($roadCoords, $roadDiameter, $bulbRadius);

            $lastSectonLanternPlaced = false;

            $skipLinesCheck = false;

            foreach ($this->roadCoords as $i => $currRoadPoint) {

                $nextPointIndex = $i + 1;

                if ($nextPointIndex == 6) {
                    $lol = 1;
                    $lol++;
                }

                $nextRoadPoint = $this->roadCoords[$nextPointIndex];

                $centerLine = new Line($currRoadPoint, $nextRoadPoint);

                $leftCurbLine = $centerLine->getParalellLine($this->roadRadius);

                $rightCurbLine = $centerLine->getParalellLine(-$this->roadRadius);

                $currPointCenterLineNormal = $centerLine->getNormalLineCrossingGivenPoint($currRoadPoint);

                $nextPointCenterLineNormal = $centerLine->getNormalLineCrossingGivenPoint($nextRoadPoint);

                while (!$this->isRoadSectionLighted($nextRoadPoint)) {

                    try {

                        if (!isset($helperPoint)) {

                            $helperPoint = $leftCurbLine->getLinesIntersectionPoint($currPointCenterLineNormal);

                            $helperCircle = new Circle($helperPoint, $this->bulbRadius);
                        } else {

                            $lanternCircle = new Circle($this->lastLantern, $this->bulbRadius);

                            $intersectingPoints = $lanternCircle->getIntersectionPointsWithLine($leftCurbLine);

                            $helperPoint = $this->getClosestPoint($nextRoadPoint, $intersectingPoints);

                            $helperCircle = new Circle($helperPoint, $this->bulbRadius);
                        }

                        $possibleLanternPositions = $helperCircle->getIntersectionPointsWithLine($rightCurbLine);

                        $lantern = $this->getClosestPoint($nextRoadPoint, $possibleLanternPositions);
                    } catch (Exception $e) {

                        if (strpos($e->getMessage(), 'No intersection points') !== false && $lastSectonLanternPlaced == false) {
                            $skipLinesCheck = true;
                        } else {
                            throw $e;
                        }
                    }

                    if ($skipLinesCheck || $lantern->isBetweenLines($currPointCenterLineNormal, $nextPointCenterLineNormal)) {
                        $this->lanterns[] = $lantern;
                        $this->lastLantern = $lantern;
                        $lastSectonLanternPlaced = true;
                        $skipLinesCheck = false;
                    } else {
                        $lastSectonLanternPlaced = false;
                        break;
                    }
                }

                if ($nextPointIndex == $this->totalRoadCoords - 1)
                    break;
            }
        } catch (Exception $e) {
            LanternPositionsCalculator::displayErrorMessage($e->getMessage());
        }

        $this->scaleLanternCoords();

        return $this->lanterns;
    }

    private function metersToDegrees($val) {

        return $val / 110000;
    }

    private function degreesToMeters($val) {

        return $val * 110000;
    }

    private function scaleRoadCoords() {

        if ($this->scalingEnabled) {

            $startCoords = $this->roadCoords[0];

            $startX = $startCoords->x;
            $startY = $startCoords->y;


            foreach ($this->roadCoords as &$point) {

                $x = $point->x;
                $y = $point->y;

                $xDiff = $startX - $x;
                $yDiff = $startY - $y;

                $point->x = $this->degreesToMeters($xDiff);
                $point->y = $this->degreesToMeters($yDiff);
            }
        }
    }

    private function scaleLanternCoords() {

        if ($this->scalingEnabled) {

            $startCoords = $this->roadCoords[0];

            $startX = $startCoords->x;
            $startY = $startCoords->y;


            foreach ($this->lanterns as &$point) {

                $x = $point->x;
                $y = $point->y;

                $xDiff = $x - $startX;
                $yDiff = $y - $startY;

                $point->x = $this->startPoint->x - $this->metersToDegrees($xDiff);
                $point->y = $this->startPoint->y - $this->metersToDegrees($yDiff);
            }
        }
    }

    private function initParams(&$roadCoords, &$roadDiameter, &$bulbRadius) {

        $this->lanterns = array();

        $this->roadDiameter = (float) $roadDiameter;

        $this->roadRadius = $roadDiameter / 2;

        if (!$this->roadRadius) {
            throw new Exception('Road radius should be greater than 0!');
        }

        $this->bulbRadius = (float) $bulbRadius;

        $this->bulbDiameter = 2 * $this->bulbRadius;

        if (!$this->bulbRadius) {
            throw new Exception('Bulb radius should be greater than 0!');
        }

        if ($this->bulbDiameter <= $this->roadRadius) {
            throw new Exception('Bulb radius too small for this type of road!');
        }

        if ($this->bulbRadius < $this->roadDiameter) {
            $this->lanternsOnBothSides = true;
        } else {
            $this->lanternsOnBothSides = false;
        }

        $coordPairs = explode(';', $roadCoords);

        $this->roadCoords = array();

        if (count($coordPairs) < 2) {
            throw new Exception('Road needs at least 2 coordination points!');
        }

        $totalRoadCoords = 0;

        foreach ($coordPairs as &$pair) {

            $coords = explode(',', $pair);

            if (count($coords) != 2) {
                throw new Exception('Invalid road coords format!');
            }

            $lat = $coords[0];
            $lon = $coords[1];

            $this->roadCoords[] = new Point($lat, $lon);

            if ($totalRoadCoords == 0) {
                $this->startPoint = new Point($lat, $lon);
            }

            $totalRoadCoords++;
        }

        $this->totalRoadCoords = $totalRoadCoords;

        $this->scaleRoadCoords();
    }

    private function isRoadSectionLighted(Point $roadPoint) {

        $lanternsCount = count($this->lanterns);

        if ($lanternsCount == 0)
            return false;

        $lastLantern = $this->lanterns[$lanternsCount - 1];

        $dist = $roadPoint->getDistance($lastLantern);

        if ($dist <= $this->bulbRadius)
            return true;
    }

    private function getClosestPoint(Point $nextRoadPoint, $positions) {

        $positionsCount = count($positions);

        if ($positionsCount == 0) {
            throw new Exception('Unable to find lantern position!');
        } else if ($positionsCount == 1) {
            return $positions[0];
        }

        $p1 = $positions[0];
        $p2 = $positions[1];

        $p1Dist = $nextRoadPoint->getDistance($p1);
        $p2Dist = $nextRoadPoint->getDistance($p2);

        if ($p1Dist < $p2Dist) {
            return $p1;
        } else {
            return $p2;
        }
    }

    public static function displayErrorMessage($msg) {

        echo "ERROR: {$msg}";
        die;
    }

    public static function displayLine($line) {

        echo $line . "<br />\n";
    }

    public static function displayJsonResponse($data) {

        header('Content-type: application/json');
        echo json_encode($data);
    }

    public static function displayTsvResponse($points) {

        header('Content-type: text/csv');
        foreach ($points as $p) {
            echo $p->x . "\t" . $p->y . "\n";
        }
    }

}

class Circle {

    private $center = null;
    private $radius = null;

    function __construct(Point $p = null, $radius = null) {

        $this->center = $p;

        $this->radius = $radius;
    }

    public function getIntersectionPointsWithLine(Line $l) {

        if ($l->isVertical) {
            return $this->getIntersectionPointsWithVerticalLine($l);
        }

        $a = - $l->bX / $l->aY;
        $b = - $l->c / $l->aY;

        $p = $this->center->x;
        $q = $this->center->y;

        $s = $b - $q;

        $r = $this->radius;

        $aX2 = 1 + pow($a, 2);
        $bX = (2 * $a * $s) - (2 * $p);
        $c = pow($p, 2) + pow($s, 2) - pow($r, 2);

        $delta = pow($bX, 2) - ( 4 * $aX2 * $c);

        if ($delta < 0) {

            throw new Exception("No intersection points for line: {$l->toStringSecondary()} circle: {$this->toString()}");
        } else if ($delta == 0) {

            $xValue1 = - $bX / (2 * $aX2);
            $p1 = new Point($xValue1, $l->getValue($xValue1));

            return array(
                0 => $p1,
            );
        } else {

            $xValue1 = (- $bX - sqrt($delta)) / (2 * $aX2);
            $p1 = new Point($xValue1, $l->getValue($xValue1));

            $xValue2 = (- $bX + sqrt($delta)) / (2 * $aX2);
            $p2 = new Point($xValue2, $l->getValue($xValue2));

            return array(
                0 => $p1,
                1 => $p2,
            );
        }
    }

    private function getIntersectionPointsWithVerticalLine(Line $l) {

        $xVal = (-$l->c / $l->bX);

        $p = $this->center->x;
        $q = $this->center->y;

        $t = $xVal - $p;

        $r = $this->radius;

        $aX2 = 1;
        $bX = - 2 * $q;
        $c = pow($q, 2) + pow($t, 2) - pow($r, 2);

        $delta = pow($bX, 2) - ( 4 * $aX2 * $c);

        if ($delta < 0) {

            throw new Exception("No intersection points for vertical line: {$l->toString()} circle: {$this->toString()}");
        } else if ($delta == 0) {

            $yValue1 = - $bX / (2 * $aX2);
            $p1 = new Point($xVal, $yValue1);

            return array(
                0 => $p1,
            );
        } else {

            $yValue1 = (- $bX - sqrt($delta)) / (2 * $aX2);
            $p1 = new Point($xVal, $yValue1);

            $yValue2 = (- $bX + sqrt($delta)) / (2 * $aX2);
            $p2 = new Point($xVal, $yValue2);

            return array(
                0 => $p1,
                1 => $p2,
            );
        }
    }

    public function toString() {

        return "(x-{$this->center->x})^2 + (y-{$this->center->y})^2 ={$this->radius}^2";
    }

}

class Line {

    public $isVertical = false;
    public $isHorizontal = false;

    /* Primary coefficients ax + by + c = 0 */
    public $aY;
    public $bX;
    public $c;

    /* Secondary coefficients y = ax + b */
    public $aX;
    public $b;

    function __construct(Point $p1 = null, Point $p2 = null) {

        if ($p1 && $p2) {

            if ($p1->x == $p2->x) {

                $this->isVertical = true;
                $this->aY = 0;
                $this->bX = 1;
                $this->c = - $p1->x;
            } else if ($p1->y == $p2->y) {

                $this->isHorizontal = true;
                $this->aY = 1;
                $this->bX = 0;
                $this->c = - $p1->y;
            } else {

                $this->aY = $p2->x - $p1->x;
                $this->bX = $p1->y - $p2->y;
                $this->c = (-(($p2->x - $p1->x) * $p1->y)) + (($p2->y - $p1->y) * $p1->x);

                $this->initSecondaryCoefficients();
            }
        }
    }

    function initBySecondaryCoefficients($aX, $b) {
        $this->aX = $aX;
        $this->b = $b;

        $this->initPrimaryCoefficients();
    }

    private function initSecondaryCoefficients() {

        $this->aX = - $this->bX / $this->aY;
        $this->b = - $this->c / $this->aY;
    }

    private function initPrimaryCoefficients() {

        $this->aY = 1;
        $this->bX = - $this->aX;
        $this->c = - $this->b;
    }

    function getParalellLine($dist) {

        $Line = new Line();

        if ($this->isVertical) {

            $Line->isVertical = true;

            $Line->aY = 0;
            $Line->bX = 1;
            $Line->c = $this->c + $dist;
        } else if ($this->isHorizontal) {

            $Line->isHorizontal = true;

            $Line->aY = 1;
            $Line->bX = 0;
            $Line->c = $this->c + $dist;
        } else {

            $val = abs($dist) * sqrt(1 + pow($this->aX, 2));

            if ($dist < 0) {
                $newB = $this->b - $val;
            } else {
                $newB = $this->b + $val;
            }

            $Line->initBySecondaryCoefficients($this->aX, $newB);
        }

        return $Line;
    }

    function getNormalLineCrossingGivenPoint(Point $p) {

        $Line = new Line();

        if ($this->isHorizontal) {

            $Line->isVertical = true;
            $Line->aY = 0;
            $Line->bX = 1;
            $Line->c = - $p->x;
        } else {

            if ($this->aX != 0) {
                $newAx = - 1 / $this->aX;
            } else {
                $newAx = 0;
            }

            $x = $p->x;
            $y = $p->y;

            $newB = -($x * $newAx) + $y;



            $Line->initBySecondaryCoefficients($newAx, $newB);
        }

        return $Line;
    }

    function getLinesIntersectionPoint(Line $l) {

        if ($this->isVertical) {

            $x = - $this->c;
            $y = $l->getValue($x);

            return new Point($x, $y);
        } else if ($l->isVertical) {
            return $l->getLinesIntersectionPoint($this);
        } else if ($this->isHorizontal) {

            $y = $this->b;
            $x = ((-$l->aY * $y) - $l->c) / $this->bX;

            return new Point($x, $y);
        } else if ($l->isHorizontal) {
            return $l->getLinesIntersectionPoint($this);
        }

        $det = ($this->aY * $l->bX) - ($l->aY * $this->bX);
        $detX = (-$this->c * $l->bX) - (-$l->c * $this->bX);
        $detY = ($this->aY * - $l->c) - ($l->aY * - $this->c);

        $x = $detX / $det;
        $y = $detY / $det;

        return new Point($x, $y);
    }

    function getValue($x) {

        if ($this->isVertical) {
            return 0;
        } else {
            return ((- $this->bX / $this->aY) * $x) - $this->c / $this->aY;
        }
    }

    function toString() {
        return "{$this->aY}y + {$this->bX}x + {$this->c} = 0";
    }

    function toStringSecondary() {
        return "y = {$this->aX}x + {$this->b}";
    }

}

class Point {

    public $x;
    public $y;

    function __construct($x, $y) {
        $this->x = $x;
        $this->y = $y;
    }

    function getDistance(Point $p2) {

        $dx = $this->x - $p2->x;
        $dy = $this->y - $p2->y;

        $dist = sqrt(pow($dx, 2) + pow($dy, 2));

        return $dist;
    }

    public function toString() {
        return "{$this->x},{$this->y}";
    }

    function isBetweenLines(Line $l1, Line $l2) {

        if ($l1->isVertical && $l2->isVertical) {

            $val = $this->x;

            $val1 = -$l1->c;
            $val2 = -$l2->c;
        } else {

            $val = $this->y;

            $val1 = $l1->getValue($this->x);
            $val2 = $l2->getValue($this->x);
        }

        $diff1 = $val1 - $val;
        $diff2 = $val2 - $val;

        if (($diff1 >= 0 && $diff2 <= 0) || ($diff1 <= 0 && $diff2 >= 0)) {
            return true;
        }

        return false;
    }

}