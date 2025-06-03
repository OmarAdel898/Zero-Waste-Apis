<?php
$iotHubName = "smartbin-iot-hub";
$deviceId = "raspberrypi-bin";
$sasToken = "SharedAccessSignature sr=smartbin-iot-hub.azure-devices.net&sig=xV0K3pNXgbtzL5gDQyVg5ZF1eJE0rEINBdJS5n6hVyM%3D&se=1748742527&skn=iothubowner";

$url = "https://$iotHubName.azure-devices.net/devices/$deviceId/messages/devicebound?api-version=2020-09-30";

$message = json_encode(["command" => "turn-servo"]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: $sasToken",
    "Content-Type: application/json",
    "Content-Length: " . strlen($message)
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode == 204) {
    echo "Message sent successfully!".$message;
} else {
    echo "Failed to send message. HTTP status code: $httpCode\nResponse: $response";
}

curl_close($ch);
