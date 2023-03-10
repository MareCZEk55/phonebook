<?php

session_start();

// Include config file
require_once "config.php";

$username = $password = "";
$username_err = $password_err = $login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    //Kontrola už. jména
    if(empty(trim($_POST["username"]))){
        $username_err = "Vyplňte uživ. jméno";
    }else{
        $username = trim($_POST["username"]);
    }

    //Kontrola hesla
    if(empty(trim($_POST["password"]))){
        $password_err = "Vyplňte heslo";
    } else{
        $password = trim($_POST["password"]);
    }

    //Validace údajů
    if(empty($username_err) && empty($password_err)){
        $sql = "SELECT u.id, u.username, u.password, r.name as role 
                FROM users u
                JOIN roles r on r.id = u.role_id
                WHERE u.username = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, 's', $param_username);

            $param_username = $username;

            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);

                if(mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $role);

                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            session_start();

                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $role;

                            header("location: address.php");
                        } else{
                            //Spatne heslo/už. jméno
                            $login_err = "Špatné uživatelské jméno nebo heslo <br>";
                        }
                    }
                } else {
                    $login_err = "Špatné uživatelské jméno nebo heslo";
                }
            } else{
                echo "Něco se pokazilo. Zkuste znovu";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Přihlásit se</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Přihlásit se</h2>
        <p>Vyplňte přihlašovací údaje.</p>

        <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Uživ. jméno</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Heslo</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Přihlásit">
            </div>
        </form>
    </div>
</body>
</html>