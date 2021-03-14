<?php
$configs = include('remote-data-config.php');

// define variables and set to empty values
$upkey = $data = "";

if (($_SERVER["REQUEST_METHOD"] == "POST") && (trim(trim($_POST["upkey"],"'"),'"') == $configs['upkey'])) {
  $upkey = filter_input($_POST["upkey"]);
  $json = filter_input($_POST["data"]);

  $written = file_put_contents('data'.date('m-Y').'.txt', $json.PHP_EOL.PHP_EOL , FILE_APPEND | LOCK_EX);

  echo $json, "\n";
  echo $written, "\n\n";

  if ($configs['relayUrl']) { // relay to next data collection service
    $data = json_decode($json, true);
    $data["stationId"] = 'CCW1';
    $result = send_data($data, $configs['relayUrl']);
  }

  // todo - save to a MySQL DB and add a getter service to display data

} elseif($_GET["test-upload"] == 'y' && $configs['testUrl']) { // send test message to other data collection services
	$json = '{"Time": "2019-4-4T4:00:08", "A0": 284.77, "A1": 378, "A2": 381, "A3": 390, "A4": 365, "A5": 340, "A6": 385, "A7": 372, "A8": 347, "A9": 359, "A10": 374, "A11": 366, "A12": 356, "A13": 380, "A14": 382, "A15": 358, "D2": 0, "D3": 1, "D4": 1, "D5": 1, "D6": 1, "D7": 1, "D8": 1, "D9": 1, "D10": 1, "D11": 1, "D12": 1, "D13": 1, "D14": 0, "D15": 0, "D16": 0, "D17": 0, "D18": 0, "D19": 0, "D20": 1, "D21": 1, "D22": 0, "D23": 0, "D24": 0, "D25": 0, "D26": 0, "D27": 0, "D28": 0, "D29": 0, "D30": 0, "D31": 0, "D32": 0, "D33": 0, "D34": 0, "D35": 0, "D36": 0, "D37": 0, "D38": 0, "D39": 0, "D40": 0, "D41": 0, "D42": 0, "D43": 0, "D44": 0, "D45": 0, "D46": 0, "D47": 0, "D48": 0, "D49": 0, "D50": 0, "D51": 0, "D52": 0, "D53": 0}';
	$data = json_decode($json, true);
	$data["stationId"] = 'TEST1';
	$result = send_data($data, $configs['testUrl']);
	echo $result;
} else {
  echo "Error";
  $data = filter_input($_POST["data"]);
  $written = file_put_contents('error.txt', $_SERVER["REQUEST_METHOD"].PHP_EOL.$_POST["upkey"].PHP_EOL.$data.PHP_EOL.PHP_EOL , FILE_APPEND | LOCK_EX);
}

function filter_input($data) {
  $data = trim($data);
  //$data = stripslashes($data);
  //$data = htmlspecialchars($data);
  return $data;
}

function send_data($data, $url) {
  $ch = curl_init($url); // Create a new cURL resource

  // Setup request to send json via POST
  $payload = json_encode($data);

  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload); // Attach encoded JSON string to the POST fields
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json')); // Set the content type to application/json
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response instead of outputting

  $result = curl_exec($ch); // Execute the POST request
  curl_close($ch); // Close cURL resource

  return $result;
}
