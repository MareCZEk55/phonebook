<!DOCTYPE html>

<head>
    <title>Telefonní seznam</title>

    <link type="image/png" sizes="16x16" rel="icon" href="icon/icon-phone-book-16.png">
    <link rel="shortcut icon" href="icon/icon-phone-book-16.png" type="image/x-icon">
    <link rel="stylesheet" href="css/mystyle.css">
    <link rel="stylesheet" href="css/myForm.css">
    <script src="https://www.kryogenix.org/code/browser/sorttable/sorttable.js"></script>
    <script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <meta charset="UTF-8">
    <meta name="description" content="Telefonní seznam OUN">
    <meta name="author" content="Marek Přikryl">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script>
        function hledatFilter() {
            // Declare variables
            var input, filter, table, tr, td, i, txtValue, count;
            input = document.getElementById("hledani_adresare");
            filter = input.value.toUpperCase();
            table = document.getElementById("tabulka_adresare");
            tr = table.getElementsByTagName("tr");

            // Loop through all table rows, and hide those who don't match the search query
            for (i = 0, count = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0];
                tdPhone = tr[i].getElementsByTagName("td")[1];
                tdodbornost = tr[i].getElementsByTagName("td")[4];
                if (td || tdPhone || tdodbornost) {
                    txtValue = td.textContent || td.innerText;
                    txtValue2 = tdPhone.textContent || tdPhone.innerText;
                    txtValue3 = tdodbornost.textContent || tdodbornost.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1 || txtValue2.toUpperCase().indexOf(filter) > -1 || txtValue3.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        if (count++ % 2 == 0) {
                            tr[i].style.background = "#dddddd";
                        } else {
                            tr[i].style.background = "#FFFFFF";
                        }
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        function ExportToExcel(type, tableIdName, fn, dl) {
            var elt = document.getElementById(tableIdName);
            var wb = XLSX.utils.book_new();
            wb.SheetNames.push("List1");
            var ws = XLSX.utils.table_to_sheet(elt);
            wb.Sheets["List1"] = ws;
            var wscols = [{
                    width: 30
                },
                {
                    width: 10
                },
                {
                    width: 15
                },
                {
                    width: 40
                },
                {
                    width: 25
                },
                {
                    width: 20
                }
            ];
            XLSX.utils.sheet_add_aoa(ws, [["Vytvořeno " + new Date().toLocaleString()]], {origin:"H1"});
            ws["!cols"] = wscols;
            return dl ?
                XLSX.write(wb, {
                    bookType: type,
                    bookSST: true,
                    type: 'base64'
                }) :
                XLSX.writeFile(wb, fn || ('MySheetName.' + (type || 'xlsx')));
        }
    </script>

    <style>
        table th:hover {
            cursor: pointer;
        }

        table.sortable th:not(.sorttable_sorted):not(.sorttable_sorted_reverse):not(.sorttable_nosort):after {
            content: " \25B4\25BE"
        }
    </style>
</head>

<body>
    <div class="content-zkracenky">
        <?php
        session_start();

        require_once "config.php";

        $mode = $_GET['mode'];
        $id = $_GET['id'];

        require_once "topnavbar.php";

        print "<h2>Zkrácenky</h2>";

        if ($mode == "add") {
            include "addEditForm.php";
        }

        if (trim($_POST["mode"]) == "added") {
            if (!$isAuthenticate) {
                header("location: shorts_phone_book.php");
                exit;
            }
            $stmt = mysqli_prepare($conn, "insert into shorts_phone_book (zkracena_volba,telefonni_cislo,misto,odbornost,jmeno,mesto,show_telefon_cislo) 
            values(?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'isssssi', $zkracena_volba_insert, $telefonni_cislo_insert, $misto_insert, $odbornost_insert, $jmeno_insert, $mesto_insert, $show_telefon_cislo_insert);

            $zkracena_volba_insert = (is_numeric(trim($_POST["cisloZkracenky"])) ? (int)(trim($_POST["cisloZkracenky"])) : 0);
            $telefonni_cislo_insert = trim($_POST["telefon"]);
            $misto_insert = trim($_POST["misto"]);
            $odbornost_insert = trim($_POST["odbornost"]);
            $jmeno_insert = trim($_POST["jmenoZkracenky"]);
            $mesto_insert = trim($_POST["mesto"]);
            if (trim($_POST["zobrazitTel"]) == "ano") {
                $show_telefon_cislo_insert = 1;
            } else {
                $show_telefon_cislo_insert = 0;
            }

            if (!mysqli_stmt_execute($stmt)) {
                print mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }

        if ($mode == "edit") {
            $stmt = mysqli_prepare($conn, "SELECT jmeno, zkracena_volba, telefonni_cislo, misto, odbornost, show_telefon_cislo, mesto
                FROM shorts_phone_book WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);

            if (!mysqli_stmt_execute($stmt)) {
                print mysqli_stmt_error($stmt);
            }

            mysqli_stmt_bind_result($stmt, $jmeno, $zkracena_volba, $telefonni_cislo, $misto, $odbornost, $show_telefon_cislo, $mesto);
            mysqli_stmt_fetch($stmt);

            $upravitForm = true;
            include "addEditForm.php";
            mysqli_stmt_close($stmt);
        }


        if (trim($_POST["mode"]) == "edited") {
            if (!$isAuthenticate) {
                header("location: shorts_phone_book.php");
                exit;
            }
            $stmt = mysqli_prepare($conn, "UPDATE shorts_phone_book SET jmeno = ?, zkracena_volba = ?, telefonni_cislo = ?,  misto = ?, 
                                    odbornost = ?, show_telefon_cislo = ?, mesto = ?
                                    WHERE id = ?");
            mysqli_stmt_bind_param(
                $stmt,
                'sisssssi',
                $jmeno_update,
                $zkracena_volba_update,
                $telefonni_cislo_update,
                $misto_update,
                $odbornost_update,
                $show_telefon_cislo_update,
                $mesto_update,
                $id_update
            );


            $zkracena_volba_update = (is_numeric(trim($_POST["cisloZkracenky"])) ? (int)(trim($_POST["cisloZkracenky"])) : 0);
            $telefonni_cislo_update = trim($_POST["telefon"]);
            $misto_update = trim($_POST["misto"]);
            $odbornost_update = trim($_POST["odbornost"]);
            $jmeno_update = trim($_POST["jmenoZkracenky"]);
            $mesto_update = trim($_POST["mesto"]);
            if (trim($_POST["zobrazitTel"]) == "ano") {
                $show_telefon_cislo_update = 1;
            } else {
                $show_telefon_cislo_update = 0;
            }
            $id_update = trim($_POST["id"]);

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        if ($mode == "remove") {
            if (!$isAuthenticate) {
                header("location: shorts_phone_book.php");
                exit;
            }

            $stmt = mysqli_prepare($conn, "DELETE FROM shorts_phone_book WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            header("location: shorts_phone_book.php");
        }


        $data = mysqli_query($conn, "SELECT id, jmeno, zkracena_volba, telefonni_cislo, misto, odbornost, show_telefon_cislo, mesto, garant FROM shorts_phone_book ORDER BY zkracena_volba ASC")
            or die(mysqli_error($conn));

        //Tabulka s adresarem
        print '<input type="text" id="hledani_adresare" onkeyup="hledatFilter()" placeholder="Hledat">';
        print '<button style="float:right;align-items:center;display:flex;" onclick="ExportToExcel(\'xlsx\', \'tabulka_adresare\', \'zkracenky-seznam.xlsx\')">
        <img src="./icon/download_icon.png" style="width:20px;height=20px">Stáhnout excel
        </button>';
        if ($isAuthenticate) {
            print '<div class="dropdown" style="float:right; top:2px; right:5px ">';
            print "<a href=" . $_SERVER['PHP_SELF'] . "?mode=add class='dropbtn'>Přidat kontakt</a></div>";
        }

        print "<br><br><table border cellpadding=4 id='tabulka_adresare' class='sortable'>";
        print "<tr><th width=250>Jméno</th><th width=120>Zkrácenka</th><th width=100>Telefon</th><th width=300>Místo</th><th width=150>Odbornost</th><th width=150>Město</th><th>Garant</th>";

        if ($isAuthenticate) {
            print "<th width=100 colspan=2 style='text-align:center'>Úpravy</th></tr>";
        } else {
            print "</tr>";
        }

        while ($info = mysqli_fetch_array($data)) {
            print "<tr><td>" . $info['jmeno'] . "</td>";
            print "<td>" . "<span>&#10033;</span>" . $info['zkracena_volba'] . "</td> ";
            print "<td>";
            if ($info['show_telefon_cislo'] == 1) {
                print $info['telefonni_cislo'];
            }
            print "</td>";
            print "<td>" . $info['misto'] . "</td> ";
            print "<td>" . $info['odbornost'] . "</td> ";
            print "<td>" . $info['mesto'] . "</td> ";
            print "<td>" . $info['garant'] . "</td> ";

            if ($isAuthenticate) {
                print "<td><a href=" . $_SERVER['PHP_SELF'] . "?id=" . $info['id'] . "&mode=edit>Upravit</a></td>";
                print "<td><a onClick=\"javascript: return confirm('Opravdu smazat uživatele?');\" href=" . $_SERVER['PHP_SELF'] . "?id=" . $info['id'] . "&mode=remove>Ostranit</a></td></tr>";
            }
        }
        print "</table>";
        ?>
    </div>
</body>

<?php require_once "footer.php"; ?>

</html>