<?php
require_once('vendor/autoload.php');
use Solt9029\Utility;

class UtilityTest extends PHPUnit\Framework\TestCase {
  public function testDist() {
    $this->assertEquals(10, Utility::dist([[0, 0], [10, 0]]));
    $this->assertGreaterThan(1.4, Utility::dist([[0, 0], [1, 1]]));
  }

  public function testIsWithinCircle() {
    $center_position = [100, 100];
    $radius = 50;

    $target_position = [90, 90];
    $this->assertTrue(Utility::isWithinCircle($center_position, $radius, $target_position));

    $target_position = [10, 10];
    $this->assertFalse(Utility::isWithinCircle($center_position, $radius, $target_position));
  }

  public function testGetCircleContainingAllPositions() {
    // 点が1つの場合
    $circle = Utility::getCircleContainingAllPositions([[0, 0]]);
    $this->assertEquals(0, $circle['radius']);
    $this->assertEquals([0, 0], $circle['center_position']);

    // 点が2つの場合
    $circle = Utility::getCircleContainingAllPositions([[0, 0], [10, 0]]);
    $this->assertEquals(5, $circle['radius']);
    $this->assertEquals([5, 0], $circle['center_position']);

    // 点が3つ以上の場合
    $center_position = [100, 100];
    $radius = 50;

    $positions = [
      [$center_position[0] - $radius, $center_position[1]],
      $center_position,
      [$center_position[0] + $radius, $center_position[1]]
    ];

    // for ($i = 0; $i < 10; $i++) {
    //   $degree = rand(0, 360);
    //   $distance = rand(0, $radius);
    //   $x = cos(deg2rad($degree)) * $distance;
    //   $y = sin(deg2rad($degree)) * $distance;
    //   $positions[] = [$center_position[0] + $x, $center_position[1] + $y];
    // }

    $circle = Utility::getCircleContainingAllPositions($positions);
    $this->assertEquals($radius, $circle['radius']);
    $this->assertEquals($center_position, $circle['center_position']);
  }


}