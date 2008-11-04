<?php

$stats_db_filename = exec("wget -o /dev/null -O /dev/stdout --timeout=5 --header \"Content-type: text/cubescript\" --post-data=\"value absolute_stats_db_filename\" http://127.0.0.1:7894/serverexec", $return);
if ( $return = 0  ) { echo "<font color=red>Error connecting to server for value absolute_stats_db_filename. Is the server running? Contact the administrator</font>"; }
try {
	$dbh = new PDO("sqlite:$stats_db_filename");
}
catch(PDOException $e)
{
	echo $e->getMessage();
}
$month = date("F");
$server_title = exec("wget -o /dev/null -O /dev/stdout --timeout=5 --header \"Content-type: text/cubescript\" --post-data=\"value title\" http://127.0.0.1:7894/serverexec");


?>

<html>
<head>
<title><?php print $server_title; ?> scoreboard</title>
<script type="text/javascript" src="overlib.js"><!-- overLIB (c) Erik Bosrup --></script>
<style type="text/css">
body
{
	background-color: #000000;
	color: #f0f0f0;
	text-align:center;
	font-family: arial;
}
a{color:#FF9900;}
a:visited{font-style:italic;}
table
{
	border-collapse:collapse;
}
table td,table th{border:solid 1px  #304860;}
table th{background-color:#183048;}
table th{padding:20px;}
table td{padding:5px; color:#ffffff;}
.highlight td{background-color: #302c28;}
.footer
{
	margin-top:20px;
	color: #a0a0a0;
	font-size:small;
}
</style>

</head>
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<h1><?php print "$server_title "; print "$month"; ?> Scoreboard</h1>
<table align="center" cellpadding="0" cellspacing="0">
	<th><a href="javascript:void(0);" onmouseover="return overlib('Player Name');" onmouseout="return nd();">Name</a></th>
	<th><a href="javascript:void(0);" onmouseover="return overlib('Average Scores per Game');" onmouseout="return nd();">ASpG</a></th>
	<th><a href="javascript:void(0);" onmouseover="return overlib('Average Defends per Game');" onmouseout="return nd();">ADpG</a></th>
	<th><a href="javascript:void(0);" onmouseover="return overlib('Flages Defended');" onmouseout="return nd();">Flags Defended</a></th>
	<th><a href="javascript:void(0);" onmouseover="return overlib('Highest Frags Recorded for 1 game');" onmouseout="return nd();">Frags Record</a></th>
	<th><a href="javascript:void(0);" onmouseover="return overlib('Total Frags Ever Recorded');" onmouseout="return nd();">Total Frags</a></th>
	<th><a href="javascript:void(0);" onmouseover="return overlib('Total Deaths');" onmouseout="return nd();">Total Deaths</a></th>
	<th><a href="javascript:void(0);" onmouseover="return overlib('Accuracy %');" onmouseout="return nd();">Accuracy (%)</a></th>
	<th><a href="javascript:void(0);" onmouseover="return overlib('Kills Per Death');" onmouseout="return nd();">Kpd</a></th>
	<th><a href="javascript:void(0);" onmouseover="return overlib('Team Kills');" onmouseout="return nd();">TK</a></th>
	<th><a href="javascript:void(0);" onmouseover="return overlib('Total Number of Games Played');" onmouseout="return nd();">Games</a></th>

<?php
$sql = "select name,
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
	where matches.datetime > date(\"now\",\"start of year\") group by name order by ASpG desc limit 300";

foreach ($dbh->query($sql) as $row)
{
	if ( $row[TotalFrags] > 50 & $row[name] != "unnamed") {
        	print "
        		<tr onmouseover=\"this.className=\'highlight\'\" onmouseout=\"this.className=\'\'\">
				<td>$row[name]</td>
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
	}
}
?>

</table>




<div class="footer">
<span id="cdate">This page was last updated <?php print date("F j, Y, g:i a"); ?> .</span> | <a href="http://www.sauerbraten.org">Sauerbraten.org</a> | <a href="http://hopmod.e-topic.info">Hopmod</a>
</div>


</body>
</html>
