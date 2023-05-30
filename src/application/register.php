<?php
    require_once(BASE_PATH . "/src/datalayer/database.php");
    require_once(BASE_PATH . "/src/utils/credentials.php");

    $database = new Database("webdev", "localhost", "mysql");
    $database->connect(Credentials::getDatabaseUser(), Credentials::getDatabasePassword());
    
    $stmt = $database->prepare("Select * from user");

    while ($row = $stmt->fetch()) {
        var_dump($row);
    }
?>