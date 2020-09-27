<?php
include("iCalcreator.php");

function dprint_r($var) {
	echo "<pre>";
	print_r($var);
	echo "</pre>";
}

function getGym($id) {
    $gymURL = "https://hvberlin-handball.liga.nu/cgi-bin/WebObjects/nuLigaHBDE.woa/wa/courtInfo?federation=HVBerlin&location=";
    $gym = file_get_contents($gymURL.$id);
    $gym = explode("<div", $gym);
    $gym = explode("Hallenspielplan", $gym[44]);
    $gym[0] = trim(strip_tags(str_replace("id=\"content-col1\">", "", $gym[0])));
    $gym[1] = explode("<p>", $gym[1]);
    $gym[1] = explode("<br />", $gym[1][1]);
    $gym[2] = str_replace("\n", "", $gym[1][1]);
    $gym[2] = trim(preg_replace('/\s\s+/', ' ', $gym[2]));
    $gym[1] = trim($gym[1][0]);
    return $gym;
}

$URL = "https://hvberlin-handball.liga.nu/cgi-bin/WebObjects/nuLigaHBDE.woa/wa/teamPortrait?teamtable=";

$_TEAMS['D3']['ID'] = 1733117;
$_TEAMS['D3']['League'] = "Stadtliga Frauen - Staffel A";
$_TEAMS['D3']['Name'] = "SG OSC-Schöneberg-Friedenau 3. Frauen";

$team = "D3";


$config = array("unique_id" => "st-werkstatt.de", "TZID" => "Europe/Berlin", "filename" => trim($_TEAMS[$team]['Name'])."-full.ics", "name" => $_TEAMS[$team]['Name'], "description" => $_TEAMS[$team]['League']);
$vcalendar = new vcalendar($config);
$vcalendar->setProperty("X-WR-TIMEZONE", "Europe/Berlin" );

$games = file_get_contents($URL.$_TEAMS[$team]['ID']);
$games = explode("<div", $games);

//    var_dump($games); 

$games = explode("<tr>", $games[44]);

//    var_dump($games[44]); 

  for($i=2; $i<count($games); $i++) {
	$games[$i] = trim($games[$i]);
    $games[$i] = str_replace(" nowrap=\"nowrap\"", "", $games[$i]);
	$games[$i] = trim($games[$i]);
	$games[$i] = str_replace(" alt=\"Heimrecht getauscht\" title=\"Heimrecht getauscht\"", "", $games[$i]);
	$games[$i] = trim($games[$i]);
    $games[$i] = explode("<td>", $games[$i]);
	$games[$i][3] = str_replace("t", "", $games[$i][3]);


//   var_dump($games[$i]); 

    for($j=0; $j<count($games[$i]); $j++) {
		if($j==4) {
//   var_dump($games[$i][4]); 
			$games[$i][$j] = explode("\"", $games[$i][$j]);
//   var_dump($games[$i][4]); 
			$games[$i][$j] = explode("location=", $games[$i][$j][5]);
//   var_dump($games[$i][4]); 
			$games[$i][$j] = getGym($games[$i][$j][1]);
		}
		else {
    		$games[$i][$j] = trim(str_replace("&nbsp;", "", (strip_tags($games[$i][$j]))));
		}
    }

	$date = new DateTime($games[$i][2]." ".$games[$i][3]);
    $start = $date->format("Ymd\THis");
	$date->modify('+2 hour');
    $end = $date->format("Ymd\THis");

	$vevent = new vevent();
	$vevent->setProperty('DTSTART', $start);
	$vevent->setProperty('DTEND', $end);
	$vevent->setProperty('SUMMARY', $games[$i][6]." - ".$games[$i][7]." | ".$_TEAMS[$team]['League']);
	$vevent->setProperty('LOCATION', $games[$i][4][0].", ".$games[$i][4][1].", ".$games[$i][4][2]);
    $vevent->setProperty('DESCRIPTION', "Spiel-Nr.: ".$games[$i][5]);

	$vcalendar->setComponent($vevent);
}
#dprint_r($games);
#dprint_r($vcalendar);


$vcalendar->returnCalendar();
?>
