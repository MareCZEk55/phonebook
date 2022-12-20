<!DOCTYPE html>
<html>

<head>
  <title>Uživatelé</title>
  <link rel="stylesheet" href="css/mystyle.css">
  <link type="image/png" sizes="16x16" rel="icon" href="icon/icon-phone-book-16.png">
	<link rel="shortcut icon" href="icon/icon-phone-book-16.png" type="image/x-icon">
</head>

<body>
  <?php

  session_start();

  require_once "config.php";
  require_once "topnavbar.php";

  $id = $_GET['id'];
  $self = $_SERVER['PHP_SELF'];
  $mode = $_GET['mode'];
  
  $password = $confirm_password = "";
  $password_err = $confirm_password_err = "";

  if (!$isAuthenticate || !$isAdmin) {
    header("location: address.php");
    exit;
  }

  if ($mode == "remove") {
    if (!$isAuthenticate || !$isAdmin) {
      header("location: address.php");
      exit;
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  }


  if ($mode == "edit") {
    $stmt = mysqli_prepare($conn, "SELECT username, lokalita, role FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $username, $lokalita, $role);
    mysqli_stmt_fetch($stmt);

    print '<h2>Edit Contact</h2> 
     <p> 
     <form action=';
    echo $self;
    print '
     method=POST>
     <table> 
     <tr><td>Username:</td><td><input type="text" value="';
    print $username;
    print '" name="username" /></td></tr> 
     <tr><td>Lokalita:</td><td><input type="text" value="';
    print $lokalita;
    print '" name="lokalita" /></td></tr> 
     <tr><td>Role:</td><td><input type="text" value="';
    print $role;
    print '" name="role" /></td></tr> ';
    print '<tr><td>Heslo:</td><td><input type="password" value="" name="password"/></td></tr>
     <tr><td>Heslo znovu:</td><td><input type="password" value="" name="confirm_password"/></td></tr>
     <tr><td colspan="2" align="center"><input type="submit" value="Potvrdit"/> 
     <input type="button" value="Zrušit" onClick="location.href=\'users.php\';" />
     </td></tr> 
     <input type=hidden name=mode value=edited> 
     <input type=hidden name=id value=';
    print $id;
    print '> ';
    if (!empty($_SESSION['password_error']) || !empty($_SESSION['confirm_password_error'])) {
      print '<tr><td colspan="2" align="center">';
      print empty($_SESSION['password_error']) ? $_SESSION['confirm_password_error'] : $_SESSION['password_error'];
      print '</td></tr>';
    }
    print '</table> </form> <p>';

    mysqli_stmt_close($stmt);
  }

  if ($mode == "edited" || trim($_POST["mode"]) == "edited") {
    if (!$isAuthenticate) {
      header("location: address.php");
      exit;
    }

    $zmena_hesla = !empty(trim($_POST["password"])) || !empty(trim($_POST["confirm_password"]));
    if ($zmena_hesla) {
      //validace hesel
      if (empty(trim($_POST["password"]))) {
        $password_err = "Vyplňte heslo";
      } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Heslo musí být aspoň 6 znaků dlouhé";
      } else {
        $password = trim($_POST["password"]);
      }

      // Validate confirm password
      if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Vyplňte heslo znovu.";
      } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
          $confirm_password_err = "Hesla nesouhlasí";
        }
      }


      if (!empty($password_err) || !empty($confirm_password_err)) {
        $_SESSION['password_error'] = $password_err;
        $_SESSION['confirm_password_error'] = $confirm_password_err;
        header("location: users.php" . "?id=" . trim($_POST["id"]) . "&mode=edit");
        exit;
      }

      $stmt = mysqli_prepare($conn, "UPDATE users SET username = ?, lokalita = ?, role = ?, password = ? WHERE id = ?");
      mysqli_stmt_bind_param($stmt, 'ssssi', $username_update, $lokalita_update, $role_update, $password_update, $id_update);
    } else {
      $stmt = mysqli_prepare($conn, "UPDATE users SET username = ?, lokalita = ?, role = ? WHERE id = ?");
      mysqli_stmt_bind_param($stmt, 'sssi', $username_update, $lokalita_update, $role_update, $id_update);
    }

    $username_update = trim($_POST["username"]);
    $lokalita_update = trim($_POST["lokalita"]);
    $role_update = trim($_POST["role"]);
    $id_update = trim($_POST["id"]);

    if ($zmena_hesla) {
      $password_update = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
    }


    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  }



  $data = mysqli_query($conn, "SELECT * FROM users ORDER BY id ASC")
    or die(mysqli_error($conn));

  print "<h2>Uživatelé</h2><br>";
  print  $_SESSION['mysql_error'];
  print "<h4><a href='adduser.php'>Přidat uživatele</a></h4>";
  print "<table>
<tbody>
<tr>
<th width=100>ID</th>
<th width=150>Username</th>
<th width=100>Lokalita</th>
<th width=100>Role</th>
<th width=100, colspan=2>Admin</th>
</tr>";

  while ($info = mysqli_fetch_array($data)) {
    print "<tr><td>" . $info['id'] . "</td> ";
    print "<td>" . $info['username'] . "</td> ";
    print "<td>" . $info['lokalita'] . "</td>";
    print "<td>" . $info['role'] . "</td>";
    print "<td><a href=" . $_SERVER['PHP_SELF'] . "?id=" . $info['id'] . "&mode=edit>Upravit</a></td>";
    print "<td><a onClick=\"javascript: return confirm('Opravdu smazat uživatele?');\"  href=" . $_SERVER['PHP_SELF'] . "?id=" . $info['id'] . "&mode=remove>Odstranit</a></td></tr>";
  }

  print "</tbody>
    </table>";

  ?>

</body>

</html>