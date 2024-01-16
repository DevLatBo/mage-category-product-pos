<?php

namespace Devlat\CategoryProductPos\Model;

class Validator
{

    public function checkInputs(array $inputs): array
    {
        $flag = false;
        // Counts how many inputs are empty.
        $emptyCounter = array_sum(array_map(function($element) { return empty($element);}, $inputs));
        if ($emptyCounter === 0) {
            $flag = true;
        }

        // Change the positions sign based on the mode value.
        if ($flag) {
            $positions = $inputs['jump'];
            $isNum = is_numeric($positions) ?? false;
            if($isNum) {
                $positions = intval($positions);
                if ($inputs['mode'] === 'ASC') {
                    $positions *= -1;
                }
            }
            $inputs['jump'] = $positions;
            $flag = $isNum;
        }
        return [$flag, $inputs];
    }
}
