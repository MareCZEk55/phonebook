<?php
/* Database credentials */
define('DB_SERVER', '');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');
define('DB_NAME', '');
 
/* Attempt to connect to MySQL database */
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$isAuthenticate = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$isAdmin = isset($_SESSION["role"]) && $_SESSION["role"] == "admin";


function addLog($db_conn, $username, $message){
    $stmt = mysqli_prepare($db_conn, "insert into logs(id_user, log) values(?, ?)");
    mysqli_stmt_bind_param($stmt, 'is', $id_user, $log);

    $log = $message;

    $sql = "select id from users where username='" . $username . "'";
    $data = mysqli_query($db_conn, $sql);
    $row = mysqli_fetch_assoc($data);
    $id_user = $row["id"];
    mysqli_free_result($data);

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

?>
