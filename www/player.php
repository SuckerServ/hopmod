<?php

session_start();
include("includes/geoip.inc");
include("includes/hopmod.php");
startbench();
$rows_per_page = "15";
$querydate = "month";
if ( $_GET['querydate'] ) {
	if ($_GET['querydate'] != "day" | "week" | "month" | "year") { $querydate = "month";} else { $querydate = $_GET['querydate']; }
}
if ( $_GET['showprofile'] ) {
	$profile_name = "and name='".sqlite_escape_string($_GET['showprofile'])."' ";
}
if ( $_GET['name'] ) {
}
check_get();
// Setup Geoip for location information.
$gi = geoip_open("/usr/local/share/GeoIP/GeoIP.dat",GEOIP_STANDARD);

// Pull Variables from Running Hopmod Server
$stats_db_filename = GetHop("value absolute_stats_db_filename");
$server_title = GetHop("value title");
if ( ! $stats_db_filename ) { $stats_db_filename = "../scripts/stats/data/stats.db"; }
if ( ! $server_title ) { $server_title = "HOPMOD Server";}
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
                round((0.0+(sum(scored)+sum(pickups)))/count(ctfplayers.player_id),2) as ASpG,
                round((0.0+(sum(defended)+sum(returns)))/count(ctfplayers.player_id),2) as ADpG
        from players
                inner join matches on players.match_id=matches.id
                outer join ctfplayers on players.id=ctfplayers.player_id
        where matches.datetime > date(\"now\",\"start of year\") and name = '".$_SESSION['name']."' group by name";

$last_10 = "
select matches.id as id,datetime,gamemode,mapname,duration,players
        from matches
                inner join players on players.match_id=matches.id

        where matches.datetime > date(\"now\",\"start of year\") and name = '".$_SESSION['name']."' order by datetime desc limit ".$_SESSION['paging'].",".$rows_per_page." 
";
$pager_query = "
select count(*) from 
(select matches.id as id,datetime,gamemode,mapname,duration,players
        from matches
                inner join players on players.match_id=matches.id

        where matches.datetime > date(\"now\",\"start of year\") and name = '".$_SESSION['name']."')
";

?>
<html>
<head>
	<title><?php print $server_title; ?> scoreboard</title>
	<script type="text/javascript" src="js/overlib.js"><!-- overLIB (c) Erik Bosrup --></script>
	<script type="text/javascript" src="js/jquery-latest.js"></script>
	<script type="text/javascript" src="js/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="js/jquery.uitablefilter.js"></script>
	<script type="text/javascript" src="js/hopstats.js"></script>
	<link rel="stylesheet" type="text/css" href="css/style.css" />
</head>
<body>
<noscript><div class="error">This page uses JavaScript for table column sorting and producing an enhanced tooltip display.</div></noscript>



<div id="content-container">
<div id="content"><div style="">
<h1><?php print $_SESSION['name'] ?>'s profile</h1>

<div class="box" style="position:absolute">
<table class="navbar" cellpadding="0" cellspacing="1">
<?php
//Build table data
foreach ($dbh->query($sql) as $row)
{
		$country = geoip_country_name_by_addr($gi, $row['ipaddr']);
		$code = geoip_country_code_by_addr($gi, $row['ipaddr']);
		if ($code) {
			$code = strtolower($code) . ".png";
			$flag_image = "<img src=images/flags/$code />";
		}
        	print "
				<tr>
					<td style=\"width:100px;\" class=\"headcol\">Name</td>
					<td align=\"center\">$row[name]</td>
				</tr>
				";
				?>
				<tr>
					<td style="width:100px;" class="headcol">Country</td>
					<td align="center"><?php overlib($country); print $flag_image ?></a></td>
				</tr>
				<?php

		print "
				<tr>
				<td style=\"width:100px;\" class=\"headcol\">ASpG</td>
				<td align=\"center\">$row[ASpG]</td>
				</tr>
				<tr>
				<td style=\"width:100px;\" class=\"headcol\">ADpG</td>
				<td align=\"center\">$row[ADpG]</td>
				</tr>
				<tr>
                                <td style=\"width:100px;\" class=\"headcol\">Flags Defended</td>
				<td align=\"center\">$row[TotalDefended]</td>
				</tr>
				<tr>
                                <td style=\"width:100px;\" class=\"headcol\">Most Frags</td>
				<td align=\"center\">$row[MostFrags]</td>
				</tr>
				<tr>
                                <td style=\"width:100px;\" class=\"headcol\">Total Frags</td>
				<td align=\"center\">$row[TotalFrags]</td>
				</tr>
				<tr>
                                <td style=\"width:100px;\" class=\"headcol\">Total Deaths</td>
				<td align=\"center\">$row[TotalDeaths]</td>
				</tr>
				<tr>
                                <td style=\"width:100px;\" class=\"headcol\">Accuracy</td>
				<td align=\"center\">$row[Accuracy]</td>	
				</tr>
				<tr>
                                <td style=\"width:100px;\" class=\"headcol\">KpD</td>
				<td align=\"center\">$row[Kpd]</td>
				</tr>
				<tr>
                                <td style=\"width:100px;\" class=\"headcol\">Team Kills</td>
				<td align=\"center\">$row[TotalTeamkills]</td>	
				</tr>
				<tr>
                                <td style=\"width:100px;\" class=\"headcol\">Total Matches</td>
				<td align=\"center\">$row[TotalMatches]</td>
				</tr>
        		";
	$flag_image ="";
}
?>
</table>
</div>






<div style="margin-left:300px">

<a name="gm"></a>

<h2>Match history</h2>

<ul class="entrylist">

<?php



foreach ($dbh->query($last_10) as $row){
$datetime = new DateTime($row['datetime']);
$date = $datetime->format(' g:i A | jS M Y , ');

?>


<li class="entrylist">

<a href="match.php?id=<?php print $row['id'] ?>">
    <span><?php print $date ?></span>
    <span><?php print $row['mapname'] ?></span>
    <span><?php print $row['players'] ?></span>
    </a>

</li><li class="">

<?php 

} 


?>

</ul>

<div style="margin-left:10%;width:600px; overflow: hidden" id="pagebar">
<?php build_pager($_GET['page'],$pager_query,$rows_per_page); //Generate Pager Bar ?>
</div>







</div>

</div>

</div>

<div id="footer">



</div>

</div>

<?php stopbench(); ?>

</body>
</html>
