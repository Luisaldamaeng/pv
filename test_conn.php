<?php
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli("127.0.0.1", "root", "", "pv");
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error . "\n";
    $conn2 = @new mysqli("localhost", "root", "", "pv");
    if ($conn2->connect_error) {
        echo "Connection via localhost also failed: " . $conn2->connect_error . "\n";
    } else {
        echo "Connection via localhost SUCCESSFUL!\n";
    }
} else {
    echo "Connection to 127.0.0.1 SUCCESSFUL!\n";
}
?>