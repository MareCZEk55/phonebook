<?php
session_start();

$topNavArray = array(
    "address.php" => "Telefonní seznam",
    "shorts_phone_book.php" => "Zkrácenky",
    "users.php" => "Uživatelé",
    "freelines.php" => "Volné linky"
);
$currentUri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

print '<div class="topnav">';
foreach ($topNavArray as $adresa => $nazev) {
    if(!$isAdmin && $adresa == "users.php"){
        continue;
    }
    if(!$isAdmin && $adresa == "freelines.php"){
        continue;
    }

    $classactive = "";
    if(strpos($currentUri, $adresa) !== false){
        $classactive = 'class="active"';
    }

    print '<a '.$classactive.' href="' . $adresa . '">' . $nazev . '</a>';
}

if (!$isAuthenticate) {
    print '<div class="dropdown" style="float:right">';
    print "<a href='login.php' class='dropbtn' style=top:0px>Přihlásit se</a></div>";
} else {
    print '<div class="dropdown" style="float:right; position:inherit;">
        <button class="dropbtn">' . $_SESSION["username"] . ' <i class="fa fa-caret-down"></i></button>
        <div class="dropdown-content">
        <a href="password_reset.php">Změna hesla</a><br>';
    if ($isAdmin) {
        print "<a href='users.php'>Uživatelé</a><br>";
    }
    print '<a href="logout.php">Odhlásit se</a>
        </div>
    </div> ';
}
print '</div>';
