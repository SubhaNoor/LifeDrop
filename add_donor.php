<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['donor_name'];
    $phone = $_POST['donor_phone'];
    $area = $_POST['donor_area'];
    $blood = $_POST['donor_blood'];

    $sql = "INSERT INTO donors (name, phone, blood_group, area) VALUES ('$name', '$phone', '$blood', '$area')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Thank you for registering as a donor!'); window.location.href='home.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>