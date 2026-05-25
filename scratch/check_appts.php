<?php
include "config/database.php";
$res = $conn->query("SELECT * FROM appointments ORDER BY id DESC LIMIT 3");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
