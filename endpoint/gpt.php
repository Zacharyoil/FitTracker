<?php
include("../../../connection.php");
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Get userID from session
session_start();
$userID = $_SESSION['userID'];
$preferences = $_POST['preferences'] ?? '';

$apiKey = 'ENTER YOUR API KEY';
$url = 'https://api.perplexity.ai/chat/completions';
$payload = json_encode([
    'model' => 'sonar',
    'messages' => [
        [
            'role' => 'system',
            'content' => 'Please only answer back with a json list of daily objectives (3 objectivesper day) for a one week period to help the user lose weight. The following are the user preferences and the objectives should reflect this but again ONLY A JSON LIST IN THE FOLLOWING FORMAT {"day": "1", "objectives": [] },'
        ],
        [
            'role' => 'user',
            'content' => $preferences
        ]
    ]
]);

$maxRetries = 3;
$attempt = 0;
$data = null;

while ($attempt < $maxRetries) {
    $attempt++;

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($curl);
    $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if (curl_errno($curl)) {
        echo "Attempt $attempt: cURL Error: " . curl_error($curl) . "\n";
        continue; // retry
    }

    if ($http_status != 200) {
        echo "Attempt $attempt: HTTP Error $http_status\n";
        echo "Response: $response\n";
        continue; // retry
    }

    $decoded_response = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Attempt $attempt: JSON decode error: " . json_last_error_msg() . "\n";
        continue; // retry
    }

    // Extract the nested JSON content
    $jsonContent = $decoded_response['choices'][0]['message']['content'];
    $data = json_decode($jsonContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Attempt $attempt: Nested JSON decode error: " . json_last_error_msg() . "\n";
        continue; // retry
    }

    // If we get here, decoding succeeded
    break;
}

if ($data === null) {
    die("Failed to get valid data after $maxRetries attempts.\n");
}

#Here we upload the json to the user
$sql = "UPDATE fitness SET weeklyObj = ? WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $jsonContent, $userID);
$stmt->execute();

//we want to clear any completed objectives since we have new ones now
$sql = "DELETE FROM completed_objectives WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userID);
$stmt->execute();


//finally we redirect back to the objectives page
header("Location: ../account/objective.php");
exit();
?>
