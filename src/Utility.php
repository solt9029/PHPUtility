<?php
namespace Solt9029;

class Utility {
  // 引数：$positionsは任意の数の座標
  // 返値：全ての点を含む最小円の半径と中心点
  public function getCircleContainingAllPositions($positions) {
    $center_position = [];
    $radius = 0;

    if (count($positions) === 1) {
      $radius = 0;
      $center_position = $positions[0];
    } else if (count($positions) === 2) {
      $radius = $this->dist($positions) / 2.0;
      $center_positions = [(float)($positions[0][0] + $positions[1][0]) / 2.0, (float)($positions[0][1] + $positions[1][1]) / 2.0];
    } else {
      for ($first_index = 0; $first_index < count($positions); $first_index++) {
        for ($second_index = $first_index + 1; $second_index < count($positions); $second_index++) {
          for ($third_index = $second_index + 1; $third_index < count($positions); $third_index++) {
            $circle = $this->getCircleContainingThreePositions([$positions[$first_index], $positions[$second_index], $positions[$third_index]]);
            $is_containing_all_positions = true;
            for ($index = 0; $index < count($positions); $index++) {
              if (!$this->isWithinCircle($circle['center_position'], $circle['radius'], $positions[$index])) {
                $is_containing_all_positions = false;
                break;
              }
            }
            if ($is_containing_all_positions) {
              if ($radius === 0 || $circle['radius'] < $radius) {
                $radius = $circle['radius'];
                $center_position = $circle['center_position'];
              }
            }
          }
        }
      }
    }
    return ['center_position' => $center_position, 'radius' => $radius];
  }

  // 返値：点が指定した円に含まれるかどうか（boolean）
  public function isWithinCircle($center_position, $radius, $target_position) {
    if ($this->dist([$target_position, $center_position]) <= $radius) {
      return true;
    }
    return false;
  }

  // 引数：$positionsは3点の座標
  // 返値：3点を通る最小円の半径と中心点
  public function getCirclePassingThreePositions($positions) {
    $center_positions = [];
    $radius = 0;

    $t = (float)(($positions[2][0] - $positions[1][0]) * ($positions[2][0] - $positions[0][0]) - ($positions[0][1] - $positions[2][1]) * ($positions[2][1] - $positions[1][1]))
      / (float)(($positions[1][1] - $positions[0][1]) * ($positions[2][0] - $positions[0][0]) - ($positions[0][1] - $positions[2][1]) * ($positions[0][0] - $positions[1][0]))
      / 2.0;
    $center_position[0] = (float)($positions[0][0] + $positions[1][0]) / 2.0 + $t * (float)($positions[1][1] - $positions[0][1]);
    $center_position[1] = (float)($positions[0][1] + $positions[1][1]) / 2.0 - $t * (float)($positions[1][0] - $positions[0][0]);
    $radius = $this->dist([$positions[0], $center_position]);

    return ['center_position' => $center_position, 'radius' => $radius];
  }

  // 引数：$positionsは3点の座標
  // 返値：3点を含む最小円の半径と中心点
  public function getCircleContainingThreePositions($positions) {
    $center_positions = [];
    $radius = 0;

    $lines = [$this->dist([$positions[0], $positions[1]]), $this->dist([$positions[1], $positions[2]]), $this->dist([$positions[2], $positions[0]])];

    $max_line_index = 0;
    for ($index = 1; $index < count($lines); $index++) {
      if ($lines[$max_line_index] < $lines[$index]) {
        $max_line_index = $index;
      }
    }

    $other_line_indexes = [];
    for ($index = 0; $index < count($lines); $index++) {
      if ($index === $max_line_index) {
        continue;
      }
      $other_line_indexes[] = $index;
    }

    // 鈍角三角形または直角三角形の場合
    if (pow($lines[$max_line_index], 2) >= pow($lines[$other_line_indexes[0]], 2) + pow($lines[$other_line_indexes[1]], 2)) {
      $radius = (float)$lines[$max_line_index] / 2.0;
      $center_position[0] = ($positions[$max_line_index][0] + $positions[($max_line_index + 1) % 3][0]) / 2.0;
      $center_position[1] = ($positions[$max_line_index][1] + $positions[($max_line_index + 1) % 3][1]) / 2.0;
    } else { // 鋭角三角形の場合
      $circle = $this->getCirclePassingThreePositions($positions);
      $radius = $circle['radius'];
      $center_position = $circle['center_position'];
    }
    return ['center_position' => $center_position, 'radius' => $radius];
  }

  // 引数：$positionsは2点の座標
  // 返値：距離
  public function dist($positions) {
    $x_dist = abs($positions[1][0] - $positions[0][0]);
    $y_dist = abs($positions[1][1] - $positions[0][1]);
    return sqrt(pow($x_dist, 2) + pow($y_dist, 2));
  }
}