<?php

session_start();

// Include config file
require_once "config.php";

$old_password = $password = $confirm_password = "";
$old_password_err = $password_err = $confirm_password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate password
    if (empty(trim($_POST["old_password"]))) {
        $old_password_err = "Vyplňte aktuální heslo";
    } elseif (strlen(trim($_POST["old_password"])) < 6) {
        $old_password_err = "Heslo musí mít alespoň 6 znaků.";
    } else {
        $sql = "SELECT password from users where username = ?";

        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);

            $param_username = trim($_POST["username"]);

            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_bind_result($stmt, $hashed_password);
                mysqli_stmt_fetch($stmt);

                if(!password_verify(trim($_POST["old_password"]), $hashed_password)){
                    $old_password_err = "Aktuální heslo je špatně zadané";
                }else{
                    $old_password = trim($_POST["old_password"]);
                }
            }else{
                echo "Něco se pokazilo, opakujte akci";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Vyplňte heslo";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Heslo musí mít alespoň 6 znaků.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Vyplňte heslo znovu";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Hesla nesouhlasí";
        }
    }

    // Check input errors before inserting in database
    if (empty($old_password_err) && empty($password_err) && empty($confirm_password_err)) {

        // Prepare an insert statement
        $sql = "UPDATE users set password = ? where username = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $param_password, $param_username);

            // Set parameters
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_username = trim($_POST["username"]);

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to login page
                header("location: address.php");
            } else {
                echo "Něco se pokazilo, zkuste akci znovu.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font: 14px sans-serif;
        }

        .wrapper {
            width: 360px;
            padding: 20px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <h2>Sign Up</h2>
        <p>Please fill this form to create an account.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Uživ. jméno</label>
                <input type="text" name="username" class="form-control" value="<?php echo $_SESSION['username'] ?>" readonly>
            </div>
            <div class="form-group">
                <label>Aktuální heslo</label>
                <input type="password" name="old_password" class="form-control <?php echo (!empty($old_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $old_password; ?>">
                <span class="invalid-feedback"><?php echo $old_password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Nové heslo</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Nové heslo znovu</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
            </div>
        </form>
    </div>
</body>

</html>