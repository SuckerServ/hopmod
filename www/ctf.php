<?php

session_start();
include("includes/geoip.inc");
include("includes/hopmod.php");
if ( $_GET['querydate'] ) {
	if (! preg_match('(day|month|year|week)', $_GET['querydate']) ) { 
		$_SESSION['querydate'] = "start of month";
	} else {
		if ( $_GET['querydate'] == "week" ) {
			$_SESSION['querydate'] = "-7 days";
		} else {
			$_SESSION['querydate'] = "start of ".$_GET['querydate'];
		}
	}
} else { if (! $_SESSION['querydate'] ) { $_SESSION['querydate'] = "start of month";} }
$querydate = $_SESSION['querydate'];


if ( $_GET['page'] >= 2 ) {
	$paging = ( ($_GET['page'] * 100) - 100 +1 );
} else { $paging = 1; }



$orderby = "ASpG";

// Setup Geoip for location information.
$gi = geoip_open("/usr/local/share/GeoIP/GeoIP.dat",GEOIP_STANDARD);

// Pull Variables from Running Hopmod Server
$stats_db_filename = GetHop("value absolute_stats_db_filename");
$server_title = GetHop("value title");

// Setup statsdb and assign it to an object.
$dbh = setup_pdo_statsdb($stats_db_filename);

// Setup main sqlite query.
$sql = "select name,
                ipaddr,
                sum(pickups) as TotalPickups,
                sum(drops) as TotalDrops,
                sum(scored) as TotalScored,
                sum(teamkills) as TotalTeamkills,
                sum(defended) as TotalDefended,
                max(frags) as MostFrags,
                sum(frags) as TotalFrags,
                sum(deaths) as TotalDeaths,
                count(name) as TotalMatches,
                round((0.0+sum(hits))/(sum(hits)+sum(misses))*100) as Accuracy,
                round((0.0+sum(frags))/sum(deaths),2) as Kpd,
                round((0.0+(sum(scored)+sum(pickups)))/count(name),2) as ASpG,
                round((0.0+(sum(defended)+sum(returns)))/count(name),2) as ADpG
        from players
                inner join matches on players.match_id=matches.id
                inner join ctfplayers on players.id=ctfplayers.player_id
        where matches.datetime > date(\"now\",\"$querydate\") group by name order by $orderby desc limit $paging,100";

$count = $dbh->query("select COUNT(*) from (SELECT name
from players
                inner join matches on players.match_id=matches.id
                inner join ctfplayers on players.id=ctfplayers.player_id
        where matches.datetime > date(\"now\",\"$querydate\") group by name
)");
$rows = $count->fetchColumn();

?>
<html>
<head>
	<title><?php print $server_title; ?> scoreboard</title>
	<script type="text/javascript" src="js/overlib.js"><!-- overLIB (c) Erik Bosrup --></script>
	<script type="text/javascript" src="js/jquery-latest.js"></script>
	<script type="text/javascript" src="js/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="js/jquery.uitablefilter.js"></script>
	<script type="text/javascript" src="js/hopstats.js"></script>
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<noscript><div class="error">This page uses JavaScript for table column sorting and producing an enhanced tooltip display.</div></noscript>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000"></div>
<h1><?php print "$server_title "; print "$month"; ?> Scoreboard</h1>

<div id="filter-panel">
<span class="filter-form">

Limit to this [ <a href="ctf.php?querydate=day" <?php if ( $_SESSION['querydate'] == "start of day" ) { print "class=selected"; } ?>>DAY</a> | 
<a href="ctf.php?querydate=week" <?php if ( $_SESSION['querydate'] == "-7 days" ) { print "class=selected"; } ?>>WEEK</a> | 
<a href="ctf.php?querydate=month" <?php if ( $_SESSION['querydate'] == "start of month" ) { print "class=selected"; } ?> >MONTH</a> | 
<a href="ctf.php?querydate=year" <?php if ( $_SESSION['querydate'] == "start of year" ) { print "class=selected"; } ?>>YEAR</a> ]</span>


<span class="filter-form"><form id="filter-form">Name Filter: <input name="filter" id="filter" value="" maxlength="30" size="30" type="text"></form></span>
</div>

<div id="pagebar" >
<?php

$pages = ( round($rows / 100 + 1) );
if ( ! $_GET['page'] ) { $_GET['page'] = 1; }
if ( $_GET['page'] <= "1" or $_GET['page'] > $pages ) {
        print "<a>Prev &#187;</a>";
	$_GET['page'] == "1";
} else {
        $nextpage = ($_GET['page'] - 1);
        print "<a href=\"?page=$nextpage\" >Prev &#171;</a>";
}

for ( $counter = 1; $counter <= $pages; $counter++) {
	?>

	<a href="?page=<?php print $counter ?>" <?php if ($counter == $_GET['page']) { print " class=selected";} ?> ><?php print $counter ?></a>

	<?php
}
if ( $_GET['page'] >= $pages or $_GET['page'] < "1" ) { 
	print "<a>Next &#187;</a>";
	$_GET['page'] == $pages;
} else {
	$nextpage = ($_GET['page'] + 1);
	print "<a href=\"?page=$nextpage\" >Next &#187;</a>";
}
?>
</div>

<table align="center" cellpadding="0" cellspacing="0" id="hopstats" class="tablesorter">
	<thead>
	<tr>
		<th><?php overlib("Player Name") ?>Name</a></th>
		<th><?php overlib("Players Country") ?>Country</a></th>
		<th><?php overlib("Average Scores per Game + Average flag Pickups") ?>Agressor Rating</a></th>
		<th><?php overlib("Average Defends(kill flag carrier) per Game + Average flag returns") ?>Defender Rating</a></th>
		<th><?php overlib("Flages Defended") ?>Flags Defended</a></th>
		<th><?php overlib("Highest Frags Recorded for 1 game") ?>Frags Record</a></th>
		<th><?php overlib("Total Frags Ever Recorded") ?>Total Frags</a></th>
		<th><?php overlib("Total Deaths") ?>Total Deaths</a></th>
		<th><?php overlib("Accuracy %") ?>Accuracy (%)</a></th>
		<th><?php overlib("Kills Per Death") ?>Kpd</a></th>
		<th><?php overlib("Team Kills") ?>TK</a></th>
		<th><?php overlib("Total Number of Games Played") ?>Games</a></th>
	</tr>
	</thead>
	<tbody>
<?php
//Build table data

$result = $dbh->query($sql); 
#echo "-------Offset $paging----Page".$_GET['page']." ---Pages $pages----Rows $rows ------- Querydate $querydate ------$orderby";


foreach ($result as $row)
{
		$country = geoip_country_name_by_addr($gi, $row['ipaddr']);
		$code = geoip_country_code_by_addr($gi, $row['ipaddr']);
		if ($code) {
			$code = strtolower($code) . ".png";
			$flag_image = "<img src=images/flags/$code />";
		}
        	print "
        		<tr onmouseover=\"this.className='highlight'\" onmouseout=\"this.className=''\">
				<td>$row[name]</td>
				";
				?>
				<td><?php overlib($country); print $flag_image ?></a></td>
				<?php

		print "
				<td>$row[ASpG]</td>
				<td>$row[ADpG]</td>
				<td>$row[TotalDefended]</td>
				<td>$row[MostFrags]</td>
				<td>$row[TotalFrags]</td>
				<td>$row[TotalDeaths]</td>
				<td>$row[Accuracy]</td>
				<td>$row[Kpd]</td>
				<td>$row[TotalTeamkills]</td>
				<td>$row[TotalMatches]</td>
        		</tr>";
	$flag_image ="";
}
sqlite_close($dbh);
?>
</tbody>
</table>
<div class="footer">
<span id="cdate">This page was last updated <?php print date("F j, Y, g:i a"); ?> .</span> | <a href="http://www.sauerbraten.org">Sauerbraten.org</a> | <a href="http://hopmod.e-topic.info">Hopmod</a>
</div>

</body>
</html>
