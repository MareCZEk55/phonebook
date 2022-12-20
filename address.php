<!DOCTYPE html>
<html>
<?php session_start(); ?>

<head>
	<title>Telefonní seznam</title>

	<link type="image/png" sizes="16x16" rel="icon" href="icon/icon-phone-book-16.png">
	<link rel="shortcut icon" href="icon/icon-phone-book-16.png" type="image/x-icon">
	<link rel="stylesheet" href="css/mystyle.css">
	<script src="https://www.kryogenix.org/code/browser/sorttable/sorttable.js"></script>
	<script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<meta charset="UTF-8">
	<meta name="description" content="Telefonní seznam OUN">
	<meta name="author" content="Marek Přikryl">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<script>
		function filterAdressBookForm() {
			// Declare variables
			var input, filter, table, tr, td, i, txtValue, count;
			input = document.getElementById("hledani_adresare");
			filter = input.value.toUpperCase();
			table = document.getElementById("tabulka_adresare");
			tr = table.getElementsByTagName("tr");

			//Var filter oddeleni
			var dropdown, filterDropdown, oddeleni, tdOddeleni;
			dropdown = document.getElementById("hledani_oddeleni");
			filterDropdown = dropdown.value;

			//Var filter typ zarizeni
			var typDropdown, typFilterDropdown, typZarizeni, tdTypZarizeni;
			typDropdown = document.getElementById("hledani_typ_zarizeni");
			typFilterDropdown = typDropdown.value;

			// Loop through all table rows, and hide those who don't match the search query
			for (i = 0, count = 0; i < tr.length; i++) {
				td = tr[i].getElementsByTagName("td")[1];
				tdPhone = tr[i].getElementsByTagName("td")[2];
				tdTagy = tr[i].getElementsByTagName("td")[4];
				if (td || tdPhone || tdTagy) {
					txtValue = td.textContent || td.innerText;
					txtValue2 = tdPhone.textContent || tdPhone.innerText;
					txtValueTagy = tdTagy.textContent || tdTagy.innerText;
					if (txtValue.toUpperCase().indexOf(filter) > -1 || txtValue2.toUpperCase().indexOf(filter) > -1 || txtValueTagy.toUpperCase().indexOf(filter) > -1) {
						tr[i].style.display = "";
					} else {
						tr[i].style.display = "none";
						continue;
					}
				}

				//Filtruj oddeleni
				tdOddeleni = tr[i].getElementsByTagName("td")[0];
				oddeleni = tdOddeleni || null;
				if (filterDropdown === "All" || !oddeleni || (filterDropdown === oddeleni.textContent)) {
						tr[i].style.display = "";
				} else {
					tr[i].style.display = "none";
					continue;
				}

				//Filtruj typ zarizeni
				tdTypZarizeni = tr[i].getElementsByTagName("td")[3];
				typZarizeni = tdTypZarizeni || null;
				if (typFilterDropdown === "All" || !typZarizeni || (typFilterDropdown === typZarizeni.textContent)) {
						tr[i].style.display = "";
				} else {
					tr[i].style.display = "none";
					continue;
				}
			}

			for (i = 0, count = 0; i < tr.length; i++) {
				if (tr[i].style.display != "none" && count++ % 2 == 0) {
					tr[i].style.background = "#dddddd";
				} else {
					tr[i].style.background = "#FFFFFF";
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
                    width: 10
                },
                {
                    width: 40
                },
                {
                    width: 10
                }
            ];
            XLSX.utils.sheet_add_aoa(ws, [["Vytvořeno " + new Date().toLocaleString()]], {origin:"E1"});
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
	<div style="display:flex;justify-content:space-between;">
		<div class="content">
        <?php
        //session_start();
			
			
			require_once "config.php";
			require_once "topnavbar.php";

			$mode = $_GET['mode'];
			$jmeno = $_GET['jmeno'];
			$telefon = $_GET['telefon'];
			$lokalita = $_GET['lokalita'];
			$typ_zarizeni = $_GET['typZarizeni'];
			$oddeleni = $_GET['oddeleni'];
			$id = $_GET['id'];
			$self = $_SERVER['PHP_SELF'];
			$tagy = $_GET['tagy'];

			$typ_zarizeni_querry = mysqli_query($conn, "SELECT nazev FROM typ_zarizeni order by nazev");
			$typ_zarizeni_data = mysqli_fetch_all($typ_zarizeni_querry, MYSQLI_ASSOC);

			$nazev_oddeleni_querry = mysqli_query($conn, "SELECT oddeleni from departments order by oddeleni");
			$nazev_oddeleni_data = mysqli_fetch_all($nazev_oddeleni_querry, MYSQLI_ASSOC);
			
			if ($mode == "add") {

				print '<h2>Přidat kontakt</h2>
			<p> 
			<form name="pridatZaznamForm" action=';
				echo htmlspecialchars($self);
				print '
			method=GET> 
			<table>
			<tr><td>Jméno:</td><td><input type="text" name="jmeno" required/></td></tr> 
			<tr><td>Telefon:</td><td><input type="text" name="telefon" required minlength="4" maxlength="4" pattern="[0-9]+" title="Vyplňte 4 místné číslo"/> </td></tr> ';
			
			print '<tr><td>Oddělení</td><td><select name="lokalita" id=zkratka_oddeleni">';
			foreach($nazev_oddeleni_data as $nazev_oddeleni){
				print '<option value="' . $nazev_oddeleni['oddeleni'] . '">'.$nazev_oddeleni['oddeleni'].'</option>';
			}
			print '</select></td></tr>';

			print '<tr><td>Typ zařízení:</td><td>
			<select name="typZarizeni" id="typZarizeni">';
			foreach($typ_zarizeni_data as $typ_zarizeni){
				print '<option value="'. $typ_zarizeni['nazev'] .'">'.$typ_zarizeni['nazev'].'</option>';
			}

			print '</select>';
			print '</td></tr>';
			print '<tr><td>Tagy:</td><td><input type="text" name="tagy" title="Vyplňte tagy pro dané číslo" /></td></tr>';
			print '
			<tr><td colspan="2" align="center"><input type="submit" value="Vytvořit" />
			<input type="button" value="Zrušit" onClick="location.href=\'' . $self . '\';" /></td></tr> 
			<input type=hidden name=mode value=added>
			</table> 
			</form> <p>';
			}

			if ($mode == "added") {
				if (!$isAuthenticate) {
					header("location: address.php");
					exit;
				}

				$stmt_oddeleni = mysqli_prepare($conn, "select id from departments where oddeleni= ?");
				mysqli_stmt_bind_param($stmt_oddeleni, 's', $zkratka_oddeleni);
				$zkratka_oddeleni = $lokalita;
				mysqli_stmt_execute($stmt_oddeleni);
				mysqli_stmt_bind_result($stmt_oddeleni, $oddeleni_id);
				mysqli_stmt_fetch($stmt_oddeleni);
				mysqli_stmt_close($stmt_oddeleni);

				$stmt_typ_zarizeni = mysqli_prepare($conn, "select id from typ_zarizeni where nazev= ?");
				mysqli_stmt_bind_param($stmt_typ_zarizeni, 's', $typ_zarizeni_param);
				$typ_zarizeni_param = $typ_zarizeni;
				mysqli_stmt_execute($stmt_typ_zarizeni);
				mysqli_stmt_bind_result($stmt_typ_zarizeni, $typ_zarizeni_id);
				mysqli_stmt_fetch($stmt_typ_zarizeni);
				mysqli_stmt_close($stmt_typ_zarizeni);

				$stmt = mysqli_prepare($conn, "INSERT INTO phone_book(jmeno, telefon, department_id, typ_zarizeni_id, tagy) VALUES (?, ?, ?, ?, ?)");
				mysqli_stmt_bind_param($stmt, 'sssis', $jmeno_insert, $telefon_insert, $lokalita_insert, $typ_zarizeni_insert, $tagy_insert);

				$jmeno_insert = $jmeno;
				$telefon_insert = $telefon;
				$lokalita_insert = $oddeleni_id;
				$typ_zarizeni_insert = $typ_zarizeni_id;
				$tagy_insert = $tagy;

				if (!mysqli_stmt_execute($stmt)) {
					print mysqli_stmt_error($stmt);
				}
				mysqli_stmt_close($stmt);
			}

			if ($mode == "edit") {

				$stmt = mysqli_prepare($conn, "SELECT p.jmeno, p.telefon, d.oddeleni, t.nazev as typ_zarizeni, p.tagy 
												FROM phone_book p 
												join typ_zarizeni t on t.id = p.typ_zarizeni_id 
												join departments d on d.id = p.department_id
												WHERE p.id = ?");
				mysqli_stmt_bind_param($stmt, 'i', $id);

				if (!mysqli_stmt_execute($stmt)) {
					print mysqli_stmt_error($stmt);
				}

				mysqli_stmt_bind_result($stmt, $jmeno, $telefon, $lokalita, $typ_zarizeni, $tagy);
				mysqli_stmt_fetch($stmt);

				print '<h2>Edit Contact</h2> 
			<p> 
			<form action=';
				echo $self;
				print '
			method=POST>
			<table> 
			<tr><td>Jméno:</td><td><input type="text" value="';
				print $jmeno;
				print '" name="jmeno" /></td></tr> 
 			<tr><td>Telefon:</td><td><input type="text" value="';
				print $telefon;
				print '" name="telefon" /></td></tr> 
 			<tr><td>Lokalita:</td><td><input type="text" value="';
				print $lokalita;
				print '" name="lokalita" /></td></tr> 
			<tr><td>Typ zařízení:</td><td>
			<select name="typZarizeni" id="typZarizeni">';
				
			foreach($typ_zarizeni_data as $typ_zarizeni_nazev){
				print '<option value="'. $typ_zarizeni_nazev['nazev'] .'"';
				if($typ_zarizeni_nazev['nazev'] === $typ_zarizeni){
					print ' selected';
				}
				print '>'.$typ_zarizeni_nazev['nazev'].'</option>';
			}

			print '</select>';
			print '</td></tr>';
			print '<tr><td>Tagy:</td><td><input type="text" value="';
				print $tagy;
				print '" name="tagy" /></td></tr>';
			print '
			<tr><td colspan="2" align="center"><input type="submit" value="Upravit" /> 
			<input type="button" value="Zrušit" onClick="location.href=\'' . $self . '\';" />
			</td></tr> 
			<input type=hidden name=mode value=edited> 
			<input type=hidden name=id value=';
				print $id;
				print '> 
			</table> 
			</form> <p>';

				mysqli_stmt_close($stmt);
			}

			if ($mode == "edited" || trim($_POST["mode"]) == "edited") {
				if (!$isAuthenticate) {
					header("location: address.php");
					exit;
				}

				$stmt_oddeleni = mysqli_prepare($conn, "select id from departments where oddeleni= ?");
				mysqli_stmt_bind_param($stmt_oddeleni, 's', $zkratka_oddeleni);
				$zkratka_oddeleni = trim($_POST["lokalita"]);
				mysqli_stmt_execute($stmt_oddeleni);
				mysqli_stmt_bind_result($stmt_oddeleni, $oddeleni_id);
				mysqli_stmt_fetch($stmt_oddeleni);
				mysqli_stmt_close($stmt_oddeleni);

				$stmt_typ_zarizeni = mysqli_prepare($conn, "select id from typ_zarizeni where nazev= ?");
				mysqli_stmt_bind_param($stmt_typ_zarizeni, 's', $nazev_zarizeni);
				$nazev_zarizeni = trim($_POST["typZarizeni"]);
				mysqli_stmt_execute($stmt_typ_zarizeni);
				mysqli_stmt_bind_result($stmt_typ_zarizeni, $typ_zarizeni_id);
				mysqli_stmt_fetch($stmt_typ_zarizeni);
				mysqli_stmt_close($stmt_typ_zarizeni);

				$stmt = mysqli_prepare($conn, "UPDATE phone_book SET jmeno = ?, telefon = ?, department_id = ?, typ_zarizeni_id = ?, tagy = ? WHERE id = ?");
				mysqli_stmt_bind_param($stmt, 'ssiisi', $jmeno_update, $telefon_update, $lokalita_update, $typ_zarizeni_update, $tagy_update, $id_update);

				$jmeno_update = trim($_POST["jmeno"]);
				$telefon_update = trim($_POST["telefon"]);
				$lokalita_update = $oddeleni_id;
				$typ_zarizeni_update = $typ_zarizeni_id;
				$tagy_update = trim($_POST["tagy"]);
				$id_update = trim($_POST["id"]);

				mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);
			}

			if ($mode == "remove") {
				if (!$isAuthenticate) {
					header("location: address.php");
					exit;
				}
				$stmt = mysqli_prepare($conn, "DELETE FROM phone_book WHERE id = ?");
				mysqli_stmt_bind_param($stmt, 'i', $id);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_close($stmt);

				//print "Záznam byl smazán <p>";
				header("location: address.php");
			}


			$data = mysqli_query($conn, "SELECT p.id as id, p.jmeno, p.telefon, d.oddeleni, t.nazev as typ_zarizeni, p.tagy
											FROM phone_book p 
											join departments d on d.id = p.department_id  
											join typ_zarizeni t on t.id = p.typ_zarizeni_id
											ORDER BY d.oddeleni, p.jmeno ASC")
				or die(mysqli_error($conn));

			//Vrat seznam oddelenich
			$filter_data = mysqli_query($conn, "SELECT oddeleni FROM departments order by oddeleni");

			//Vrat seznamy typu typ zarizeni
			$fitlter_typ_zarizeni = mysqli_query($conn, "SELECT nazev from typ_zarizeni order by nazev");

			print "<h2>Telefonní seznam</h2>";

			/*if (!$isAuthenticate) {
			print '<div class="dropdown" style="float:right">';
			print "<a href='login.php' class='dropbtn'>Přihlásit se</a></div>";
		} else {
			print '<div class="dropdown" style="float:right">
		<button class="dropbtn">' . $_SESSION["username"] . '</button>
		<div class="dropdown-content">
		  <a href="password_reset.php">Změna hesla</a>';
			if ($isAdmin) {
				print "<a href='users.php'>Uživatelé</a>";
			}
			print '<a href="logout.php">Odhlásit se</a>
				</div>
			</div> ';
		}*/

			//Tabulka s adresarem
			print '<div>';
			print '<input type="text" id="hledani_adresare" onkeyup="filterAdressBookForm()" placeholder="Hledat jméno">';
			print '<button style="float:right;align-items:center;display:flex;" onclick="ExportToExcel(\'xlsx\', \'tabulka_adresare\', \'telefony-seznam.xlsx\')">
			<img src="./icon/download_icon.png" style="width:20px;height=20px">Stáhnout excel
			</button>';
			print '</div>';
			if ($isAuthenticate) {
				print '<div class="dropdown" style="float:right; position:relative; top:2px; right:5px ">';
				print "<a href=" . $_SERVER['PHP_SELF'] . "?mode=add class='dropbtn'>Přidat kontakt</a></div>";
			}

			print '<div class="dropdown_filtry">';
			//Filtr pro oddělení
			print '<div class=dropdown_filtry_oddeleni>';
			print '<span class="filterText"><br>Oddělení: </span><select id="hledani_oddeleni" oninput="filterAdressBookForm()">';
			print '<option value="All">Vše</option>';
			while ($filter_info = mysqli_fetch_array($filter_data)) {
				print '<option value="' . $filter_info['oddeleni'] . '"> ' . $filter_info['oddeleni'] . '  </option>';
			}
			print '</select>';
			print '</div>';

			//Filtr pro typ zařízení
			print '<div class=dropdown_filtry_typ_zarizeni>';
			print '<span class="filterText"><br>Typ zařízení: </span><select id="hledani_typ_zarizeni" oninput="filterAdressBookForm()">';
			print '<option value="All">Vše</option>';
			while ($filter_info = mysqli_fetch_array($fitlter_typ_zarizeni)) {
				print '<option value="' . $filter_info['nazev'] . '"> ' . $filter_info['nazev'] . '  </option>';
			}
			print '</select>';
			print '</div>';

			print '</div>';

			print "<br><table border cellpadding=3 id='tabulka_adresare' class='sortable'>";
			print "<tr><th width=120>Oddělení</th><th width=400>Jméno</th><th width=120>Telefon</th><th>Typ zařízení</th>";

			if ($isAuthenticate) {
				print "<th width=130>Tagy</th>";
				print "<th width=150 colspan=2 style='text-align:center'>Úpravy</th></tr>";
			}else{
				print "</tr>";
			}

			while ($info = mysqli_fetch_array($data)) {
				print "<tr><td>" . $info['oddeleni'] . "</td>";
				print "<td>" . $info['jmeno'] . "</td> ";
				print "<td>" . $info['telefon'] . "</td> ";
				print "<td>" . $info['typ_zarizeni'] . "</td> ";
				print "<td style='display:none'>" . $info['tagy'] . "</td> ";
				if ($isAuthenticate) {
					print "<td>" . $info['tagy'] . "</td> ";
				}else{
					print "<td style='display:none'>" . $info['tagy'] . "</td> ";
				}

				if ($isAuthenticate) {
					print "<td style='text-align: center;'><a href=" . $_SERVER['PHP_SELF'] . "?id=" . $info['id'] . "&mode=edit>Upravit</a></td>";
					print "<td style='text-align: center;'><a onClick=\"javascript: return confirm('Opravdu smazat uživatele?');\" href=" . $_SERVER['PHP_SELF'] . "?id=" . $info['id'] . "&mode=remove>Ostranit</a></td></tr>";
				}
			}
			print "</table>";
			?>

			<p></p>
		</div>

		<!-- Hodiny -->
		<div style="float:right;position:sticky;top:50px;">
			<iframe src="../hodiny/hodiny.html" style="width:600px;height:800px;position:sticky;top:5px;margin-top:100px"></iframe>
		</div>
	</div>
</body>
<?php require_once "footer.php"; ?>
</html>