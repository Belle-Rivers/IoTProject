<?php

$jsonFile = "sleeping_hours.json";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    // Load the JSON data
    if (file_exists($jsonFile)) {
        $jsonData = json_decode(file_get_contents($jsonFile), true);
    } else {
        $jsonData = [
            "start" => null,
            "end" => null,
            "deactivationcode" => null,
            "response" => "none",
            "deactivation" => "none",
            "codematch" => "none"
        ];
    }

    if ($action == "set_hours") {
        // Set Sleeping Hours and Deactivation Code
        $start = intval($_POST['start']);
        $end = intval($_POST['end']);
        $code = str_pad($_POST['deactivationcode'], 4, "0", STR_PAD_LEFT); // Ensure it's 4 digits

        $jsonData['start'] = $start;
        $jsonData['end'] = $end;
        $jsonData['deactivationcode'] = $code;
        $jsonData['response'] = "none";
        $jsonData['deactivation'] = "none";
        $jsonData['codematch'] = "none";

        file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));
        echo "Sleeping hours and deactivation code updated successfully!";
    } elseif ($action == "response") {
        // Update Movement Response
        $response = $_POST['response'];
        $jsonData['response'] = $response;

        if ($response == "yes") {
            // If the response is "yes", deactivate the system
            $jsonData['deactivation'] = "yes";
        }

        file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));
        echo "Response updated successfully!";
    } elseif ($action == "deactivation") {
        // Handle Deactivation Code Verification
        $inputCode = str_pad($_POST['code'], 4, "0", STR_PAD_LEFT);
        $storedCode = $jsonData['deactivationcode'];

        if ($inputCode === $storedCode) {
            // Successful Deactivation
            $jsonData['deactivation'] = "yes";
            $jsonData['codematch'] = "yes";
            file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));
            echo "System deactivated successfully!";
        } else {
            // Failed Deactivation
            $jsonData['codematch'] = "no";
            file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));
            echo "Wrong deactivation code, please re-enter.";
        }
    }
} else {
    // Load JSON data
    if (file_exists($jsonFile)) {
        $jsonData = json_decode(file_get_contents($jsonFile), true);
    } else {
        $jsonData = [
            "response" => "none",
            "deactivation" => "none",
            "codematch" => "none"
        ];
    }

    $response = $jsonData['response'];
    $deactivation = $jsonData['deactivation'];
    $codematch = $jsonData['codematch'];

    if ($response == "none") {
        // Prompt for Movement Response
        echo "<h1>There seems to be some movement in your house. Was this you?</h1>";
        echo "<form method='POST'>
                <input type='hidden' name='action' value='response'>
                <button name='response' value='yes'>Yes</button>
                <button name='response' value='no'>No</button>
              </form>";
    } elseif ($response == "no" && $deactivation == "none") {
        // Prompt for Deactivation Code
        echo "<h1>Do you want to deactivate the system?</h1>";
        echo "<form method='POST'>
                <input type='hidden' name='action' value='deactivation'>
                <label for='code'>Enter Deactivation Code:</label>
                <input type='text' id='code' name='code' maxlength='4' required>
                <br><br>";
        if ($codematch == "no") {
            echo "<p style='color: red;'>Wrong deactivation code, please re-enter.</p>";
        }
        echo "<button type='submit'>Submit</button>
              </form>";
    } else {
        // Display Sleeping Hours and Code Form
        echo "<h1>Set Your Sleeping Hours and Deactivation Code</h1>";
        echo "<form method='POST'>
                <input type='hidden' name='action' value='set_hours'>
                <label for='start'>Start Hour (0-23):</label>
                <input type='number' id='start' name='start' min='0' max='23' required>
                <br><br>
                <label for='end'>End Hour (0-23):</label>
                <input type='number' id='end' name='end' min='0' max='23' required>
                <br><br>
                <label for='deactivationcode'>Enter 4-Digit Deactivation Code:</label>
                <input type='text' id='deactivationcode' name='deactivationcode' maxlength='4' required>
                <br><br>
                <button type='submit'>Save</button>
              </form>";
    }
}
?>
