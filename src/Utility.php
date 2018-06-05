<?php
namespace Solt9029;

class Utility {
  // 引数：$positionsは任意の数の座標
  // 返値：全ての点を含む最小円の半径と中心点
  public static function getCircleContainingAllPositions($positions) {
    $center_position = [];
    $radius = 0;

    if (count($positions) === 1) {
      $radius = 0;
      $center_position = $positions[0];
    } else if (count($positions) === 2) {
      $radius = self::dist($positions) / 2.0;
      $center_position = [(float)($positions[0][0] + $positions[1][0]) / 2.0, (float)($positions[0][1] + $positions[1][1]) / 2.0];
    } else {
      for ($first_index = 0; $first_index < count($positions); $first_index++) {
        for ($second_index = $first_index + 1; $second_index < count($positions); $second_index++) {
          for ($third_index = $second_index + 1; $third_index < count($positions); $third_index++) {
            $circle = self::getCircleContainingThreePositions([$positions[$first_index], $positions[$second_index], $positions[$third_index]]);
            $is_containing_all_positions = true;
            for ($index = 0; $index < count($positions); $index++) {
              if (!self::isWithinCircle($circle['center_position'], $circle['radius'], $positions[$index])) {
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
  public static function isWithinCircle($center_position, $radius, $target_position) {
    if (self::dist([$target_position, $center_position]) <= $radius) {
      return true;
    }
    return false;
  }

  // 引数：$positionsは3点の座標
  // 返値：3点を通る最小円の半径と中心点
  public static function getCirclePassingThreePositions($positions) {
    $center_positions = [];
    $radius = 0;

    $t = (float)(($positions[2][0] - $positions[1][0]) * ($positions[2][0] - $positions[0][0]) - ($positions[0][1] - $positions[2][1]) * ($positions[2][1] - $positions[1][1]))
      / (float)(($positions[1][1] - $positions[0][1]) * ($positions[2][0] - $positions[0][0]) - ($positions[0][1] - $positions[2][1]) * ($positions[0][0] - $positions[1][0]))
      / 2.0;
    $center_position[0] = (float)($positions[0][0] + $positions[1][0]) / 2.0 + $t * (float)($positions[1][1] - $positions[0][1]);
    $center_position[1] = (float)($positions[0][1] + $positions[1][1]) / 2.0 - $t * (float)($positions[1][0] - $positions[0][0]);
    $radius = self::dist([$positions[0], $center_position]);

    return ['center_position' => $center_position, 'radius' => $radius];
  }

  // 引数：$positionsは3点の座標
  // 返値：3点を含む最小円の半径と中心点
  public static function getCircleContainingThreePositions($positions) {
    $center_positions = [];
    $radius = 0;

    $lines = [self::dist([$positions[0], $positions[1]]), self::dist([$positions[1], $positions[2]]), self::dist([$positions[2], $positions[0]])];

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
      $circle = self::getCirclePassingThreePositions($positions);
      $radius = $circle['radius'];
      $center_position = $circle['center_position'];
    }
    return ['center_position' => $center_position, 'radius' => $radius];
  }

  // 引数：$positionsは2点の座標
  // 返値：距離
  public static function dist($positions) {
    $x_dist = abs($positions[1][0] - $positions[0][0]);
    $y_dist = abs($positions[1][1] - $positions[0][1]);
    return sqrt(pow($x_dist, 2) + pow($y_dist, 2));
  }

  // 引数：$dpiは1インチ当たりのドット数，$precisionは計測誤差（度），
  // $distanceは目からディスプレイまでの距離（cm），$flickは固視微動（度），
  // $min_durationは注視したとする最低時間（ミリ秒），$filenameは読み込むCSVファイル（1列目X座標2列目Y座標3列目タイムスタンプ（ミリ秒））
  public static function getFixationCount($dpi, $precision, $distance, $flick, $min_duration, $filename) {
    $fixations = self::getFixations($dpi, $precision, $distance, $flick, $min_duration, $filename);
    return count($fixations);
  }

  // 引数：$dpiは1インチ当たりのドット数，$precisionは計測誤差（度），
  // $distanceは目からディスプレイまでの距離（cm），$flickは固視微動（度），
  // $min_durationは注視したとする最低時間（ミリ秒），$filenameは読み込むCSVファイル（1列目X座標2列目Y座標3列目タイムスタンプ（ミリ秒））
  public static function getInitialFixation($dpi, $precision, $distance, $flick, $min_duration, $filename) {
    $fixations = self::getFixations($dpi, $precision, $distance, $flick, $min_duration, $filename);
    if (count($fixations) === 0) {
      return null;
    }
    return $fixations[0];
  }

  // 引数：$dpiは1インチ当たりのドット数，$precisionは計測誤差（度），
  // $distanceは目からディスプレイまでの距離（cm），$flickは固視微動（度），
  // $min_durationは注視したとする最低時間（ミリ秒），$filenameは読み込むCSVファイル（1列目X座標2列目Y座標3列目タイムスタンプ（ミリ秒））
  public static function getFixations($dpi, $precision, $distance, $flick, $min_duration, $filename) {
    $dpc = $dpi / 2.54; // 1センチ当たりのドット数
    $precision_error_range_cm = $distance * tan(deg2rad($precision)); // 計測誤差範囲（cm）
    $flick_range_cm = $distance * tan(deg2rad($flick)); // 固視微動範囲（cm）
    $range_cm = $precision_error_range_cm + $flick_range_cm; // 計測誤差範囲+固視微動範囲（cm）
    $range_px = $range_cm * $dpc;
  
    $file = fopen($filename, 'r');
    $recordings = [];
    while ($line = fgetcsv($file)) {
      // 同時刻のものは1つだけ格納する
      if (count($recordings) > 0) {
        if ($recordings[count($recordings) - 1][2] === $line[2]) {
          continue;
        }
      }
      $recordings[] = $line;
    }
  
    $fixations = [];
  
    for ($first_index = 0; $first_index < count($recordings); $first_index++) {
      $recording_stack = [];
      for ($second_index = $first_index; $second_index < count($recordings); $second_index++) {
        $recording_stack[] = $recordings[$second_index];
        $circle = self::getCircleContainingAllPositions($recording_stack);
    
        // 範囲外になった場合
        if ($circle['radius'] > $range_px) {
          array_pop($recording_stack);
          $duration = $recording_stack[count($recording_stack) - 1][2] - $recording_stack[0][2];
          if ($duration > MIN_DURATION) {
            $circle = self::getCircleContainingAllPositions($recording_stack);
            $fixations[] = [
              'center_position' => $circle['center_position'],
              'start_time' => $recording_stack[0][2],
              'end_time' => $recording_stack[count($recording_stack) - 1][2],
              'duration' => $duration
            ];
            $first_index = $second_index - 1;
          }
          break;
        }
    
        // 最終行の記録の場合
        if ($second_index + 1 >= count($recordings)) {
          $duration = $recording_stack[count($recording_stack) - 1][2] - $recording_stack[0][2];
          if ($duration > MIN_DURATION) {
            $fixations[] = [
              'center_position' => $circle['center_position'],
              'start_time' => $recording_stack[0][2],
              'end_time' => $recording_stack[count($recording_stack) - 1][2],
              'duration' => $duration
            ];
            $first_index = $second_index;
          }
          break;
        }
      }
    }
  
    return $fixations;
  }
}