<?php
include("../../../connection.php");
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
$json = file_get_contents('php://input');
$data = json_decode($json, true);
echo "Data received!";

//parse the userID from the header
$headers = getallheaders();
$userID = $headers['userID'];

#we want to parse the calories burned and steps taken from the json for storage

$activeCalories = [];
$basalCalories = [];

# Loop through metrics
foreach ($data['data']['metrics'] as $metric) {
    if ($metric['name'] === 'active_energy') {
        foreach ($metric['data'] as $entry) {
            $date = substr($entry['date'], 0, 10); // keep only YYYY-MM-DD
            $kJ = $entry['qty'];
            $activeCalories[$date] = $kJ / 4.184; // convert to kcal
        }
    }

    if ($metric['name'] === 'basal_energy_burned') {
        foreach ($metric['data'] as $entry) {
            $date = substr($entry['date'], 0, 10);
            $kJ = $entry['qty'];
            $basalCalories[$date] = $kJ / 4.184;
        }
    }
}

// Combine totals per day
$dailyTotals = [];
$dailyActive = [];
foreach ($activeCalories as $date => $active) {
    $basal = $basalCalories[$date] ?? 0;
    $dailyActive[$date] = round($active, 2);
    $dailyTotals[$date] = round($active + $basal, 2);
}

// Print latest day
$latestDate = max(array_keys($dailyTotals));
echo "Latest date: $latestDate\n";
echo "Total calories burned: {$dailyTotals[$latestDate]} kcal\n";
echo "Active calories burned: {$dailyActive[$latestDate]} kcal\n";

#makes a new json with the daily totals only
$simplifiedData = [
    'latest_date' => $latestDate,
    'total_calories_burned' => $dailyTotals[$latestDate],
    'active_calories_burned' => $dailyActive[$latestDate]
];
$calories = json_encode($simplifiedData);


#Here we upload the json to the user
$sql = "UPDATE fitness SET healthData = ? WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $calories, $userID);
$stmt->execute();

?>
