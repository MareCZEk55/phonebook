<!DOCTYPE html>
<html>
<?php session_start(); ?>

<head>
	<title>Telefonní seznam</title>

	<link type="image/png" sizes="16x16" rel="icon" href="icon/icon-phone-book-16.png">
	<link rel="shortcut icon" href="icon/icon-phone-book-16.png" type="image/x-icon">
	<link rel="stylesheet" href="css/mystyle.css">
	<script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<meta charset="UTF-8">
	<meta name="description" content="Telefonní seznam OUN">
	<meta name="author" content="Marek Přikryl">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<script>
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
</head>

<body>
		<div class="content" style="width: 1400px">
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

			$data = mysqli_query($conn, "SELECT p.telefon
											FROM phone_book p 
											ORDER BY p.telefon ASC")
				or die(mysqli_error($conn));

			print "<h2>Volné linky</h2>";

			//Tabulka s adresarem
			print '<div>';
			print '<button style="float:right;align-items:center;display:flex;" onclick="ExportToExcel(\'xlsx\', \'tabulka_adresare\', \'telefony-seznam.xlsx\')">
			<img src="./icon/download_icon.png" style="width:20px;height=20px">Stáhnout excel
			</button>';
			print '</div>';

			print '<div id=pocet_linek></div>';
			print '<div id=pocet_obsazenych></div>';

			print "<br><table border cellpadding=3 id='tabulka_adresare' class='sortable' style='display: table'>";

			$obsazene_linky = array();
			while($info = mysqli_fetch_assoc($data)){
				$obsazene_linky[] = $info["telefon"];
			}

			print "<tr>";
			for ($i=8000; $i<9000; $i+=100){
				print "<th id=".$i.">" . $i . " - " . ($i + 99) . "</th>";
			}
			print "</tr>";

			$pocetCisel = 0;
			for ($i=8000; $i < 9000; $i++) { 
				$jeVolna = true;
				
				if(($i % 100) == 0){
					print "<tr style='display: table-cell; vertical-align: top; background-color: white;'>";
				}

				for($j=0; $j < count($obsazene_linky); $j++){
					if($i == $obsazene_linky[$j]){
						$jeVolna = false;
						break;
					}
				}

				if($jeVolna){
					print "<td style='display: block; background-color: white;'>". $i ."</td>";
					$pocetCisel++;
				}
				
				if(($i + 1) % 100 == 0){
					print "</tr>";

					echo '
					<script type="text/javascript">
						var index = ' . (($i + 1) - 100) . ' ;
						var nadpis = document.getElementById(index).innerText;
						var text = nadpis + " " + "(' . $pocetCisel . ')";
						console.log(text);
						document.getElementById(index).innerHTML = text;
					</script>';
					$pocetCisel = 0;
				}
			}

			print "</table>";
			?>
			<script>
				var pocet = <?php echo count($obsazene_linky); ?>;
				var pocetVolne = 1000 - pocet;
				var pocetObsazene = pocet;
				document.getElementById("pocet_linek").innerHTML = "<p><b>Počet volných linek: " + pocetVolne + "</b></p>";
				document.getElementById("pocet_obsazenych").innerHTML = "<p><b>Počet obsazených linek: " + pocetObsazene + "</b></p>";
			</script>
			<p></p>
		</div>
</body>
<?php require_once "footer.php"; ?>
</html>