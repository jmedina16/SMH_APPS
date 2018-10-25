<?php

$year = date('Y');
$start_year = $year . '-01-01';
$datetime = new DateTime($start_year);
$startn = (int) date_format($datetime, "n");
$current_month = date('n');
$date = date_format($datetime, "F");

$months = array();
array_push($months, $date);
$datetime = new DateTime($start_year);
if ($current_month == 1) {
    for ($x = 1; $x < 12; $x++) {
        $datetime->add(new DateInterval('P1M'));
        $newMonth = date_format($datetime, "F");
        array_push($months, $newMonth);
    }
} else {
    for ($startn; $startn <= $current_month - 2; $startn++) {
        $datetime->add(new DateInterval('P1M'));
        $newMonth = date_format($datetime, "F");
        array_push($months, $newMonth);
    }
}

print_r($months);

$previous_year = date("Y", strtotime("-1 years"));
print_r($previous_year);
?>