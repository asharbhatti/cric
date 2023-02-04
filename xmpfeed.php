<?php
	set_time_limit(1200);
	ini_set('date.timezone', 'UTC');
//	ini_set("display_errors", "On");
	require_once "inc/db.class.php";
	$user = $_GET['user'];
	$pass = $_GET['pass'];
	$cmd = isset($_GET["cmd"])?$_GET["cmd"]:"current";
	if($cmd == 'current'){
		$cmd = 'live';
	}
	$date = isset($_GET['date'])?$_GET['date']:date("Y-m-d");
	$timestamp = isset($_GET['timestamp'])?$_GET['timestamp']:date("Y-m-d-H-i-s", time() - 3600);
	$fromdate = isset($_GET['fromdate'])?$_GET['fromdate']:date("Y-m-d");
	$todate = isset($_GET['todate'])?$_GET['todate']:date("Y-m-d");
	$tournament = isset($_GET['tournament'])?$_GET['tournament']:null;
	$category = isset($_GET['category'])?$_GET['category']:"";
	$match = isset($_GET['match'])?$_GET['match']:null;
	$battingline = isset($_GET['battingline'])?$_GET['battingline']:1;
	$bowlingline = isset($_GET['bowlingline'])?$_GET['bowlingline']:1;
	$squad = isset($_GET['squad'])?$_GET['squad']:0;
	$lineup = isset($_GET['lineup'])?$_GET['lineup']:1;
	$fallofwicket = isset($_GET['fallofwicket'])?$_GET['fallofwicket']:1;
	$partnership = isset($_GET['partnership'])?$_GET['partnership']:1;
	$commentary = isset($_GET['commentary'])?$_GET['commentary']:0;
	$activeonly = isset($_GET['activeonly'])?$_GET['activeonly']:0;
	$cmlimit = isset($_GET["cmlimit"])?$_GET["cmlimit"]:30;
	$cover = isset($_GET['cover'])?$_GET['cover']:0;
	$id = isset($_GET['id'])?$_GET['id']:0;

	$team_id = isset($_GET['team_id'])?$_GET['team_id']:0;
	$match_type = isset($_GET['match_type'])?$_GET['match_type']:'odi';
    $page = isset($_GET['page']) ? $_GET['page'] : 1;   // for pagination

	require_once "inc/functions.util.php";
	$DB = new DB;
	$DB->open();
	header("Content-type: text/xml");	// header section
	if($cmd != 'match_info' && $cmd != 'tournament_match' && $cmd != 'match_commentary' && $cmd != 'match_fixture' && $cmd != 'team_flags' && $cmd != "tournamentlist"){
		echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n<live version=\"1.0\" timestamp=\"".date("Y-m-d H:i:s")."\">";
	}
	$customer = $data = $DB->select("select id, active from customer where login='$user' and password='$pass'");        
	if(isset($data[0]) && $data[0]['active'] == 1){
//		$sql = "insert into xml_logs (user_id, url, ip) values ('".$data[0]["id"]."', '".$_SERVER["REQUEST_URI"]."', '".$_SERVER["REMOTE_ADDR"]."')";
//		$DB->insert($sql);
	} else {

		echo "<message text=\"authentication failed\" />";
		if($cmd != 'match_info' && $cmd != 'tournament_match' && $cmd != 'match_commentary' && $cmd != 'match_fixture' && $cmd != 'team_flags' && $cmd != "tournamentlist"){
				echo "</live>";
		}
		exit;
	}
	$extra_sql = "";

	//match info

	if($cmd == 'team_flags'){
		$data = $DB->select("select participant.id, participant.name, country.name country, gender.name gender, participant.en from participant, systemdata country, systemdata gender where participant.country = country.id and participant.gender=gender.id and gender.systemdata_type='gender' and country.systemdata_type='country' and participant_type='team' and participant.active='1'");

		$xml ="<teamflags>";
		for($rc =0; $rc < sizeof($data); $rc++){
			$image_exit=file_exists("teamflags/".$data[$rc]["id"].".png")?"found":"not found";
			//$image_exit="";

			$xml .= '<team  id="'.$data[$rc]["id"].'" name="'.str_replace("&","&amp;", $data[$rc]["name"]).'"  flage="'.$image_exit.'"/>';
		}
		$xml .="</teamflags>";
		echo $xml;
	}



	if($cmd == 'match_fixture'){
		$xml = '<xml>';

		$xml .= '<HIERARCHY>/liveSport/football/index/filtered/24.com</HIERARCHY>';
			$xml .= '<TITLE CATEGORY="Sport" SUBCATEGORY="Cricket" TYPE="LiveIndex" LABEL="Live Cricket Index - Next 7 days - filtered for 24.com subscription"/>';
			$xml .= '<DATAFORMAT CODE="LiveCricketIndex" TABLE="SoccerLiveMatchDetails" DESCRIPTION="Cricket Index - Filtered"/>';
		$date = date('Y-m-d'); //today date
		$day_name = date('l'); //today date
		$date_display = date('Y/m/d'); //today date
		$date_named = date(" jS F Y "); //today date
		// like
//		$tournaments = $DB->select("select DISTINCT e.id, e.tournament from event as e  where e.gt like '$date%'  ");
                //--------Tempraroy changes by Mudassar sb----- roll back later --- will be handeld by client 
//                $tournaments = $DB->select("SELECT DISTINCT e.id, e.tournament FROM `event` AS e  WHERE (e.gt LIKE '$date%') OR ((e.gt LIKE '".date('Y-m-d', strtotime('-1 day', strtotime($date)))."%' OR e.gt LIKE '".date('Y-m-d', strtotime('-2 day', strtotime($date)))."%' OR e.gt LIKE '".date('Y-m-d', strtotime('-3 day', strtotime($date)))."%' OR e.gt LIKE '".date('Y-m-d', strtotime('-4 day', strtotime($date)))."%') AND e.`status` IN (2,3,4,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,36,37,38,41,44,45,46,47,48,49,50,51,52,54,55,56,57,58,59) AND e.`match_type` NOT IN (1,7,8,10,39,43))");
                $whitelist = whitelist($customer[0]['id']);
                $whitelist_string = ($whitelist)? " AND e.tournament IN (".$whitelist.")" : "";
                $query33 = "SELECT DISTINCT e.id, e.tournament FROM `event` AS e  WHERE ((e.gt LIKE '$date%') OR ((e.gt LIKE '".date('Y-m-d', strtotime('-1 day', strtotime($date)))."%' OR e.gt LIKE '".date('Y-m-d', strtotime('-2 day', strtotime($date)))."%' OR e.gt LIKE '".date('Y-m-d', strtotime('-3 day', strtotime($date)))."%' OR e.gt LIKE '".date('Y-m-d', strtotime('-4 day', strtotime($date)))."%') AND (e.`finished_ut` IS NULL OR e.`finished_ut` = '0000-00-00 00:00:00' OR e.`finished_ut` > (NOW() - INTERVAL 30 MINUTE)) AND e.`match_type` NOT IN (1,7,8,10,39,43)))" . $whitelist_string;
                $tournaments = $DB->select($query33);

                $xml .= '<DAY DATE="'.$date_display.'" DOW="'.$day_name.'" NAMED="'.$date_named.'">';
		for($t =0; $t < count($tournaments); $t++){

				// print_r($tournaments[$t]['id']);exit;
				$tour = new tournament($tournaments[$t]['tournament']);
				$tour->load();

				$xml .= '<COMPETITION ID="'.$tournaments[$t]['tournament'].'" NAME="'.textf($tour->name).'">'; 

					//get matches
					$matchId = $tournaments[$t]['id'];
					$matches = $DB->select("select e.id, e.gt,e.LiveStreamEmbedcode,e.highlights, e.ut, e.finished_ut,e.finished_date, e.match_type, e.match_no, e.ht_id, e.at_id, e.time_difference, e.tournament,
					e.status, e.stadium, e.match_no, e.season, e.umpire1, e.umpire2, e.umpiretv, e.referee, e.match_time, e.comment, e.toss_win, e.winner,
					e.elected, e.live, e.followon, e.live, e.manofmatch, e.manofmatch2, e.condition_wind, e.condition_humidity, e.condition_pressure, e.condition_dewpoint, e.condition_visibility, e.temperature_current, e.temperature_overnight, e.active, e.attendance, e.batsman_of_match, e.bowler_of_match, e.batsman_of_match_id, e.bowler_of_match_id, e.betway_id, e.LiveStreamEmbedcode from event e join participant p on e.tournament=p.id and p.participant_type='tournament'
					where e.id = '$matchId' ");
						for($m =0; $m < count($matches); $m++){
							$hteam = new team($matches[$m]['ht_id']);
								$hteam->load();
								$ateam = new team($matches[$m]['at_id']);
								$ateam->load();

								if($tour->categ == 'intl_test'){
											$cate = 'Test';
											$competion_name = 'International Test';
								}elseif ($tour->categ == 'intl_odi') {
									$cate = 'ODI';
									$competion_name = 'One Day International ';
								}elseif ($tour->categ == 'intl_t20') {
									$cate = 'T20';
										$competion_name = 'International T20';
								}
								 $date_time = explode(" ",$matches[$m]['gt']);


								$xml .= '<MATCH ID="'.$matches[$m]['id'].'" HOMETEAM="'.textf($hteam->name).'" HOMECODE="'.$hteam->code.'" HOMETEAMID="'.$hteam->id.'" AWAYTEAM="'.textf($ateam->name).'" AWAYCODE="'.$ateam->code.'" AWAYTEAMID="'.$ateam->id.'" FXDATE="'.$date_display.'" FXTIME="'.$date_time[1].'" TIMEZONE="GMT'.$matches[$m]['time_difference'].'"/>';
						}
					//get matches end


				$xml .= '</COMPETITION>';
				 // commentory
			}

	$xml .= '</DAY>';


			for($i =1; $i <= 6; $i++){
			    $date = date('Y-m-d', strtotime('+1 day', strtotime($date)));

					$day_name = date('l',strtotime($date)); //today date
					$date_display = date('Y/m/d',strtotime($date)); //today date
					$date_named = date(" jS F Y ",strtotime($date)); //today date
					//for other days
								$tournaments = $DB->select("select DISTINCT e.id, e.tournament from event as e  where e.gt like '$date%'" . $whitelist_string);
								$xml .= '<DAY DATE="'.$date_display.'" DOW="'.$day_name.'" NAMED="'.$date_named.'">';
							for($t =0; $t < count($tournaments); $t++){
									$tour = new tournament($tournaments[$t]['tournament']);
									$tour->load();

									$xml .= '<COMPETITION ID="'.$tournaments[$t]['tournament'].'" NAME="'.textf($tour->name).'">';

										//get matches
										$matchId = $tournaments[$t]['id'];
										$matches = $DB->select("select e.id, e.gt,e.LiveStreamEmbedcode,e.highlights, e.ut, e.finished_ut,e.finished_date, e.match_type, e.match_no, e.ht_id, e.at_id, e.time_difference, e.tournament,
										e.status, e.stadium, e.match_no, e.season, e.umpire1, e.umpire2, e.umpiretv, e.referee, e.match_time, e.comment, e.toss_win, e.winner,
										e.elected, e.live, e.followon, e.live, e.manofmatch, e.manofmatch2, e.condition_wind, e.condition_humidity, e.condition_pressure, e.condition_dewpoint, e.condition_visibility, e.temperature_current, e.temperature_overnight, e.active, e.attendance, e.batsman_of_match, e.bowler_of_match, e.batsman_of_match_id, e.bowler_of_match_id, e.betway_id, e.LiveStreamEmbedcode from event e join participant p on e.tournament=p.id and p.participant_type='tournament'
										where e.id = '$matchId' ");
											for($m =0; $m < count($matches); $m++){
												$hteam = new team($matches[$m]['ht_id']);
													$hteam->load();
													$ateam = new team($matches[$m]['at_id']);
													$ateam->load();

													if($tour->categ == 'intl_test'){
																$cate = 'Test';
																$competion_name = 'International Test';
													}elseif ($tour->categ == 'intl_odi') {
														$cate = 'ODI';
														$competion_name = 'One Day International ';
													}elseif ($tour->categ == 'intl_t20') {
														$cate = 'T20';
															$competion_name = 'International T20';
													}
													 $date_time = explode(" ",$matches[$m]['gt']);


													$xml .= '<MATCH ID="'.$matches[$m]['id'].'" HOMETEAM="'.textf($hteam->name).'" HOMECODE="'.$hteam->code.'" HOMETEAMID="'.$hteam->id.'" AWAYTEAM="'.textf($ateam->name).'" AWAYCODE="'.$ateam->code.'" AWAYTEAMID="'.$ateam->id.'" FXDATE="'.$date_display.'" FXTIME="'.$date_time[1].'" TIMEZONE="GMT'.$matches[$m]['time_difference'].'"/>';
											}
										//get matches end


									$xml .= '</COMPETITION>';
									 // commentory
								}

						$xml .= '</DAY>';

					//for other days end


			}
			//print_r($weekOfdays);exit;
			$xml .= '</xml>';
			echo $xml;


	}
// SOHAIL ADD
if($cmd == "tournament_match") {

	 $tournament = isset($_GET['tournament'])?$_GET['tournament']:0;
	 $limit = isset($_GET['limit'])?$_GET['limit']:5;
	 // echo "<cricket>";
	 $tournamentObject = $DB->select("SELECT
							 participant.id,
							 participant.name,
							 country.id country_id,
							 country.name country,
							 gender.name gender,
							 international,
							 participant.se,
							 participant.active,
							 season.name season
						 FROM
							 participant,
							 systemdata country,
							 systemdata gender,
							 systemdata season
						 WHERE participant.country = country.id
							 AND participant.gender = gender.id
							 AND gender.systemdata_type = 'gender'
							 AND country.systemdata_type = 'country'
							 AND participant_type = 'tournament'
							 AND participant.season = season.id
							 AND season.systemdata_type = 'season'
							 AND participant.id = $tournament"
	 );
	 $tournamentObject = isset($tournamentObject[0]) ? $tournamentObject[0] : null;
	 echo "<tournament id=\"" . $tournamentObject['id'] . "\" name=\"" . textf($tournamentObject['name']) . "\" country=\"" . $tournamentObject['country'] . "\" gender=\"" . $tournamentObject['gender'] . "\" category=\"" . $tournamentObject['se'] . "\" active=\"" . $tournamentObject['active'] . "\" season=\"" . $tournamentObject['season'] . "\" international=\"" . $tournamentObject['international'] . "\" >";
	 //-----------------------------------------------------------
	 $tournamentid = $tournamentObject['id'];
	 $team_id = 0;
	 $teams=[];
	 $games = $DB->select("
		 select
		 e.*,
		 e.tournament,
		 e.stadium,
		 e.match_type,
		 tt.teamid,
		 stadium.name as stadium_name,
		 stadium.city as stadium_city,
		 stadium.city as stadium_city,
		 country.name as country_name,

		 team.name as team_name,
		 team.history as team_history

		 from event as e

			 left Join tournament_team as tt on (tt.tourid = e.tournament)

			 left Join systemdatac as stadium on (stadium.id = e.stadium and stadium.systemdata_type='stadium')

			 left Join participant as team on (team.id = tt.teamid and team.participant_type='team' and team.name != 'TBC')

			 left Join participant as country on (country.country = stadium.country and country.participant_type='team')

			 where
			 (CASE
				 WHEN ($team_id != 0) THEN e.tournament = '$tournamentid' and tt.teamid =  '$team_id'

				 ELSE e.tournament = '$tournamentid' END
			 )
			 group by e.id

	 ");
	 foreach($games as $game){
			 $localtime=  date("Y-m-d H:i:s", strtotime($game['gt']) + ($game['time_difference']*3600));
			 // $coverage = $game['live']?:''
				 echo "<game
									 id=\"" . $game['id'] . "\"
									 gmt_datetime=\"" . $game['gt'] . "\"
									 local_datetime=\"" . $localtime . "\"
									 updated=\"" . $game['ut'] . "\"
									 finished_ut=\"" . $game['finished_ut'] . "\"
									 finished_date=\"" . $game['finished_date'] . "\"
									 status=\"" . $game['status'] . "\"
									 matchtype=\"" . getName("match_type", $game['match_type']) . "\"
									 matchno=\"" . getName("match_no", $game['match_no']) . "\"
									 season=\"" . getName("season", $game['season']). "\"

									 season_id=\"" . $game['season']. "\"

									 lights=\"" . getName("match_time",$game['match_time']). "\"

									 comment=\"" . str_replace("&", "&amp;", $game['comment']). "\"
									 coverage=\"" . textf(str_replace("&", "&amp;", getName("live", $game['live']))). "\"

									 manofmatch=\"" . str_replace("&", "&amp;", $game['manofmatch']). "\"
									 manofmatch_id=\"" . $game["manofmatch"]. "\"

									 batsman_of_match=\"" . str_replace("&", "&amp;", $game["batsman_of_match"]). "\"
									 bowler_of_match=\"" . str_replace("&", "&amp;", $game["bowler_of_match"]). "\"
									 batsman_of_match_id=\"" . $game["batsman_of_match_id"]. "\"
									 bowler_of_match_id=\"" . $game["bowler_of_match_id"]. "\"
									 active=\"" . $game["active"]. "\"
									 winner=\"" . $game["winner"]. "\"
									 bw_id=\"" . $game["betway_id"]. "\"

						 >";

						 $home_team = new team($game['ht_id']);
							$home_team->load();
						 if($home_team->name != 'TBC'){
									 echo"<team
											 id=\"" .$home_team->id. "\"
											 name=\"" .$home_team->name. "\"
											 country=\"" .$home_team->country. "\"
											 code=\"" .$home_team->code. "\"
										 />";
						 }
						 $away_team = new team($game['at_id']);
							$away_team->load();
						 if($away_team->name != 'TBC'){
									 echo"<team
											 id=\"" .$away_team->id. "\"
											 name=\"" .$away_team->name. "\"
											 country=\"" .$away_team->country. "\"
											 code=\"" .$away_team->code. "\"
										 />";
						 }

						echo "</game>";


	 }
	 foreach($teams as $team){
		 $team_name = new team($team['teamid']);
			$team_name->load();
		 if($team_name->name != 'TBC'){
		 echo "<team id=\"" . $team['teamid'] . "\"  name=\"" . textf($team_name->name) . "\"   >";
		 $teamid = $team['teamid'];
			 $most_runs = $DB->select("
					 SELECT
					 SUM(bat.score) AS most_runs,
					 TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
					 p.country,
					 m.`tournament`,linup.playerid
					 FROM `event` m, `event_playerex` linup, `event_batsman` bat ,player p
					 WHERE m.`status` IN (40,7) AND m.tournament = $tournament and linup.teamid = '$teamid'
					 AND linup.`eventid` = m.`id`
					 AND bat.`event_playerFK` = linup.`id`
					 AND p.`id` = linup.`playerid`
					 GROUP BY linup.`playerid`
					 ORDER BY most_runs DESC
					 LIMIT $limit
			 "); //leading runs for a tournament

			 $most_sixes = $DB->select("
					 SELECT
					 SUM(bat.s6) AS most_sixes,
					 TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
					 m.`tournament`,linup.playerid,p.country
					 FROM `event` m, `event_playerex` linup, `event_batsman` bat ,player p
					 WHERE m.`status` IN (40,7) AND m.tournament = $tournament and linup.teamid = '$teamid'
					 AND linup.`eventid` = m.`id`
					 AND bat.`event_playerFK` = linup.`id`
					 AND p.`id` = linup.`playerid`
					 GROUP BY linup.`playerid`
					 ORDER BY most_sixes DESC
					 LIMIT $limit
			 "); //leading sixes for a tournament

			 //---------------------------------------------------
			 $top_inning_score = $DB->select("
					 SELECT
					 (bat.score) AS inning_score,
					 TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
					 m.id,m.`tournament`,linup.playerid,p.country
					 FROM `event` m, `event_playerex` linup, `event_batsman` bat ,player p
					 WHERE
					 m.`status` IN (40,7) AND m.tournament = $tournament and linup.teamid = '$teamid'
					 AND linup.`eventid` = m.`id`
					 AND bat.`event_playerFK` = linup.`id`
					 AND p.`id` = linup.`playerid`
					 ORDER BY inning_score DESC
					 LIMIT $limit
			 "); //highest innings for a tournament
			 //---------------------------------------------------
			 $most_wickets = $DB->select("
					 SELECT
					 SUM(bowl.wkt) AS most_wkts,
					 TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
					 m.`tournament`,linup.playerid,p.country
					 FROM `event` m, `event_playerex` linup, `event_bowler` bowl ,player p
					 WHERE m.`status` IN (40,7) AND m.tournament = $tournament and linup.teamid = '$teamid'
					 AND linup.`eventid` = m.`id`
					 AND bowl.`event_playerFK` = linup.`id`
					 AND p.`id` = linup.`playerid`
					 GROUP BY linup.`playerid`
					 ORDER BY most_wkts DESC
					 LIMIT $limit
			 "); // most_wickets for a tournament
			 //---------------------------------------------------
			 $leading_bowler = $DB->select("
					 SELECT
					 bowl.wkt,
					 bowl.`run` ,
					 TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
					 m.`tournament`,linup.playerid,p.country
					 FROM `event` m, `event_playerex` linup, `event_bowler` bowl ,player p
					 WHERE m.`status` IN (40,7) AND m.tournament = $tournament and linup.teamid = '$teamid'
					 AND linup.`eventid` = m.`id`
					 AND bowl.`event_playerFK` = linup.`id`
					 AND p.`id` = linup.`playerid`
					 ORDER BY bowl.wkt DESC, bowl.run
					 LIMIT $limit
			 "); // best bowling figures for a tournament
			 //---------------------------------------------------
			 $best_economy = $DB->select("
					 SELECT
					 (bowl.`run` / bowl.`over`) AS economy,
					 bowl.`run`,
					 bowl.`over`,
					 TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
					 m.`tournament`,linup.playerid,p.country
					 FROM `event` m, `event_playerex` linup, `event_bowler` bowl ,player p
					 WHERE m.`status` IN (40,7) AND m.tournament = $tournament and linup.teamid = '$teamid'
					 AND linup.`eventid` = m.`id`
					 AND bowl.`event_playerFK` = linup.`id`
					 AND p.`id` = linup.`playerid`
					 AND bowl.`over` > 0
                                         AND bowl.`active` = 1
					 ORDER BY economy
					 LIMIT $limit
			 "); // best bowling economy for a tournament
			 //---------------------------------------------------
			 echo "<leading_runs_scorer>";
					 for ($rc = 0; $rc < sizeof($most_runs); $rc++) {
							 echo "<player position=\"" . ($rc + 1) . "\" runs=\"" . $most_runs[$rc]["most_runs"] . "\"   player_id=\"" . $most_runs[$rc]["playerid"] . "\"   player_name=\"" . $most_runs[$rc]["player_name"] . "\"     />";
					 }
			 echo "</leading_runs_scorer>";
			 //---------------------------------------------------
			 echo "<highest_inning_scorer>";
					 for ($rc = 0; $rc < sizeof($top_inning_score); $rc++) {
							 echo "<player position=\"" . ($rc + 1) . "\" inning_score=\"" . $top_inning_score[$rc]["inning_score"] . "\"   player_id=\"" . $top_inning_score[$rc]["playerid"] . "\"   player_name=\"" . $top_inning_score[$rc]["player_name"] . "\"   />";
					 }
			 echo "</highest_inning_scorer>";
			 //---------------------------------------------------
			 echo "<leading_sixes>";
					 for ($rc = 0; $rc < sizeof($most_sixes); $rc++) {
							 echo "<player position=\"" . ($rc + 1) . "\" sixes=\"" . $most_sixes[$rc]["most_sixes"] . "\"   player_id=\"" . $most_sixes[$rc]["playerid"] . "\"   player_name=\"" . $most_sixes[$rc]["player_name"] . "\"   />";
					 }
			 echo "</leading_sixes>";
			 //---------------------------------------------------
			 echo "<leading_wickets>";
					 for ($rc = 0; $rc < sizeof($most_wickets); $rc++) {
							 echo "<player position=\"" . ($rc + 1) . "\" wickets=\"" . $most_wickets[$rc]["most_wkts"] . "\"   player_id=\"" . $most_wickets[$rc]["playerid"] . "\"   player_name=\"" . $most_wickets[$rc]["player_name"] . "\"   />";
					 }
			 echo "</leading_wickets>";
			 //---------------------------------------------------
			 echo "<best_bowling_figures>";
					 for ($rc = 0; $rc < sizeof($leading_bowler); $rc++) {
							 echo "<player position=\"" . ($rc + 1) . "\" bowling_figures=\"" . $leading_bowler[$rc]["wkt"] . "/" .  $leading_bowler[$rc]["run"] . "\"   player_id=\"" . $leading_bowler[$rc]["playerid"] . "\"   player_name=\"" . $leading_bowler[$rc]["player_name"] . "\"   />";
					 }
			 echo "</best_bowling_figures>";
			 //---------------------------------------------------
			 echo "<best_economy_bowler>";
					 for ($rc = 0; $rc < sizeof($best_economy); $rc++) {
							 echo "<player position=\"" . ($rc + 1) . "\" economy=\"" . number_format($best_economy[$rc]["economy"],2)  . "\"    runs=\"" . $best_economy[$rc]["run"]  . "\"    overs=\"" . $best_economy[$rc]["over"]  . "\"   player_id=\"" . $best_economy[$rc]["playerid"] . "\"   player_name=\"" . $best_economy[$rc]["player_name"] . "\"    />";
					 }
			 echo "</best_economy_bowler>";
			 //---------------------------------------------------



		 echo "</team >";
	 }

 }
	 // $most_runs = $DB->select("
	 //     SELECT
	 //     SUM(bat.score) AS most_runs,
	 //     TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
	 // 		p.country,
	 //     m.`tournament`,linup.playerid
	 //     FROM `event` m, `event_playerex` linup, `event_batsman` bat ,player p
	 //     WHERE m.`status` IN (40,7) AND m.tournament = $tournament and linup =
	 //     AND linup.`eventid` = m.`id`
	 //     AND bat.`event_playerFK` = linup.`id`
	 //     AND p.`id` = linup.`playerid`
	 //     GROUP BY linup.`playerid`
	 //     ORDER BY most_runs DESC
	 //     LIMIT $limit
	 // "); //leading runs for a tournament
	 //---------------------------------------------------
	 // $most_sixes = $DB->select("
	 //     SELECT
	 //     SUM(bat.s6) AS most_sixes,
	 //     TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
	 //     m.`tournament`,linup.playerid,p.country
	 //     FROM `event` m, `event_playerex` linup, `event_batsman` bat ,player p
	 //     WHERE m.`status` IN (40,7) AND m.tournament = $tournament
	 //     AND linup.`eventid` = m.`id`
	 //     AND bat.`event_playerFK` = linup.`id`
	 //     AND p.`id` = linup.`playerid`
	 //     GROUP BY linup.`playerid`
	 //     ORDER BY most_sixes DESC
	 //     LIMIT $limit
	 // "); //leading sixes for a tournament
	 //---------------------------------------------------
	 // $top_inning_score = $DB->select("
	 //     SELECT
	 //     (bat.score) AS inning_score,
	 //     TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
	 //     m.id,m.`tournament`,linup.playerid,p.country
	 //     FROM `event` m, `event_playerex` linup, `event_batsman` bat ,player p
	 //     WHERE
	 //     m.`status` IN (40,7) AND m.tournament = $tournament
	 //     AND linup.`eventid` = m.`id`
	 //     AND bat.`event_playerFK` = linup.`id`
	 //     AND p.`id` = linup.`playerid`
	 //     ORDER BY inning_score DESC
	 //     LIMIT $limit
	 // "); //highest innings for a tournament
	 //---------------------------------------------------
	 // $most_wickets = $DB->select("
	 //     SELECT
	 //     SUM(bowl.wkt) AS most_wkts,
	 //     TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
	 //     m.`tournament`,linup.playerid,p.country
	 //     FROM `event` m, `event_playerex` linup, `event_bowler` bowl ,player p
	 //     WHERE m.`status` IN (40,7) AND m.tournament = $tournament
	 //     AND linup.`eventid` = m.`id`
	 //     AND bowl.`event_playerFK` = linup.`id`
	 //     AND p.`id` = linup.`playerid`
	 //     GROUP BY linup.`playerid`
	 //     ORDER BY most_wkts DESC
	 //     LIMIT $limit
	 // "); // most_wickets for a tournament
	 //---------------------------------------------------
	 // $leading_bowler = $DB->select("
	 //     SELECT
	 //     bowl.wkt,
	 //     bowl.`run` ,
	 //     TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
	 //     m.`tournament`,linup.playerid,p.country
	 //     FROM `event` m, `event_playerex` linup, `event_bowler` bowl ,player p
	 //     WHERE m.`status` IN (40,7) AND m.tournament = $tournament
	 //     AND linup.`eventid` = m.`id`
	 //     AND bowl.`event_playerFK` = linup.`id`
	 //     AND p.`id` = linup.`playerid`
	 //     ORDER BY bowl.wkt DESC, bowl.run
	 //     LIMIT $limit
	 // "); // best bowling figures for a tournament
	 //---------------------------------------------------
	 // $best_economy = $DB->select("
	 //     SELECT
	 //     (bowl.`run` / bowl.`over`) AS economy,
	 //     bowl.`run`,
	 //     bowl.`over`,
	 //     TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
	 //     m.`tournament`,linup.playerid,p.country
	 //     FROM `event` m, `event_playerex` linup, `event_bowler` bowl ,player p
	 //     WHERE m.`status` IN (40,7) AND m.tournament = $tournament
	 //     AND linup.`eventid` = m.`id`
	 //     AND bowl.`event_playerFK` = linup.`id`
	 //     AND p.`id` = linup.`playerid`
	 //     AND bowl.`over` > 0
	 //     ORDER BY economy
	 //     LIMIT $limit
	 // "); // best bowling economy for a tournament
	 //---------------------------------------------------
	 // echo "<leading_runs_scorer>";
	 //     for ($rc = 0; $rc < sizeof($most_runs); $rc++) {
	 //         echo "<player position=\"" . ($rc + 1) . "\" runs=\"" . $most_runs[$rc]["most_runs"] . "\"   player_id=\"" . $most_runs[$rc]["playerid"] . "\"   player_name=\"" . $most_runs[$rc]["player_name"] . "\"   team_id=\"" .$most_runs[$rc]['country']. "\"  />";
	 //     }
	 // echo "</leading_runs_scorer>";
	 // //---------------------------------------------------
	 // echo "<highest_inning_scorer>";
	 //     for ($rc = 0; $rc < sizeof($top_inning_score); $rc++) {
	 //         echo "<player position=\"" . ($rc + 1) . "\" inning_score=\"" . $top_inning_score[$rc]["inning_score"] . "\"   player_id=\"" . $top_inning_score[$rc]["playerid"] . "\"   player_name=\"" . $top_inning_score[$rc]["player_name"] . "\" team_id=\"" .$top_inning_score[$rc]['country']. "\"  />";
	 //     }
	 // echo "</highest_inning_scorer>";
	 // //---------------------------------------------------
	 // echo "<leading_sixes>";
	 //     for ($rc = 0; $rc < sizeof($most_sixes); $rc++) {
	 //         echo "<player position=\"" . ($rc + 1) . "\" sixes=\"" . $most_sixes[$rc]["most_sixes"] . "\"   player_id=\"" . $most_sixes[$rc]["playerid"] . "\"   player_name=\"" . $most_sixes[$rc]["player_name"] . "\" team_id=\"" .$most_sixes[$rc]['country']. "\"  />";
	 //     }
	 // echo "</leading_sixes>";
	 // //---------------------------------------------------
	 // echo "<leading_wickets>";
	 //     for ($rc = 0; $rc < sizeof($most_wickets); $rc++) {
	 //         echo "<player position=\"" . ($rc + 1) . "\" wickets=\"" . $most_wickets[$rc]["most_wkts"] . "\"   player_id=\"" . $most_wickets[$rc]["playerid"] . "\"   player_name=\"" . $most_wickets[$rc]["player_name"] . "\" team_id=\"" .$most_wickets[$rc]['country']. "\"  />";
	 //     }
	 // echo "</leading_wickets>";
	 // //---------------------------------------------------
	 // echo "<best_bowling_figures>";
	 //     for ($rc = 0; $rc < sizeof($leading_bowler); $rc++) {
	 //         echo "<player position=\"" . ($rc + 1) . "\" bowling_figures=\"" . $leading_bowler[$rc]["wkt"] . "/" .  $leading_bowler[$rc]["run"] . "\"   player_id=\"" . $leading_bowler[$rc]["playerid"] . "\"   player_name=\"" . $leading_bowler[$rc]["player_name"] . "\" team_id=\"" .$leading_bowler[$rc]['country']. "\"  />";
	 //     }
	 // echo "</best_bowling_figures>";
	 // //---------------------------------------------------
	 // echo "<best_economy_bowler>";
	 //     for ($rc = 0; $rc < sizeof($best_economy); $rc++) {
	 //         echo "<player position=\"" . ($rc + 1) . "\" economy=\"" . number_format($best_economy[$rc]["economy"],2)  . "\"    runs=\"" . $best_economy[$rc]["run"]  . "\"    overs=\"" . $best_economy[$rc]["over"]  . "\"   player_id=\"" . $best_economy[$rc]["playerid"] . "\"   player_name=\"" . $best_economy[$rc]["player_name"] . "\"  team_id=\"" .$best_economy[$rc]['country']. "\"  />";
	 //     }
	 // echo "</best_economy_bowler>";
	 //---------------------------------------------------
	 echo "</tournament>";


}
//SOHAIL ADD END

	if($cmd == 'match_commentary'){
			$match = isset($_GET['match'])?$_GET['match']:0;
                        $whitelist = whitelist($customer[0]['id']);
                        $whitelist_string = ($whitelist)? " AND e.tournament IN (".$whitelist.")" : "";
                        
			$matches = $DB->select("select e.id, e.gt,e.LiveStreamEmbedcode,e.highlights, e.ut, e.finished_ut,e.finished_date, e.match_type, e.match_no, e.ht_id, e.at_id, e.time_difference, e.tournament,
			e.status, e.stadium, e.match_no, e.season, e.umpire1, e.umpire2, e.umpiretv, e.referee, e.match_time, e.comment, e.toss_win, e.winner,
			e.elected, e.live, e.followon, e.live, e.manofmatch, e.manofmatch2, e.condition_wind, e.condition_humidity, e.condition_pressure, e.condition_dewpoint, e.condition_visibility, e.temperature_current, e.temperature_overnight, e.active, e.attendance, e.batsman_of_match, e.bowler_of_match, e.batsman_of_match_id, e.bowler_of_match_id, e.betway_id, e.LiveStreamEmbedcode from event e join participant p on e.tournament=p.id and p.participant_type='tournament'
			where e.id = '$match'" . $whitelist_string);
	$xmll = '<xml>';
			for($m =0; $m < count($matches); $m++){

                            if(($matches[$m]['toss_win'] == "A" && $matches[$m]['elected'] == 1) || ($matches[$m]['toss_win'] == "B" && $matches[$m]['elected'] == 2))
                                    $bat_first = "A";
                            else
                                    $bat_first = "B";


				if($matches[$m]["live"] == 2 && $matches[$m]['status'] == 1){
					$status = "Result only";
				} else {
					$status = getName("status", $matches[$m]['status']);
				}
				$current_ing = 0;
				$current_xing = 0;
				$current_team = 0;
				$xres = $DB->select("select inning, data from stats_settings where eventid=".$matches[$m]["id"]." and stats_type='batting_team' and data <> '0' order by stats_settings.inning desc");
				if(!empty($xres)){
					$current_ing = $xres[0]["inning"];
					$current_team = $xres[0]["data"];
					if($current_ing == 1 || $current_ing == 2){
						$current_xing = 1;
					} else {
						$current_xing = 2;
					}
				}


				$cate = '';
				$competion_name = '';
				$tour = new tournament($matches[$m]['tournament']);
				$tour->load();

				$hteam = new team($matches[$m]['ht_id']);
					$hteam->load();
					$ateam = new team($matches[$m]['at_id']);
					$ateam->load();

					if($tour->categ == 'intl_test'){
								$cate = 'Test';
								$competion_name = 'International Test';
					}elseif ($tour->categ == 'intl_odi') {
						$cate = 'ODI';
						$competion_name = 'One Day International ';
					}elseif ($tour->categ == 'intl_t20') {
						$cate = 'T20';
							$competion_name = 'International T20';
					}

					 $hteam_name = getNamep("team", $matches[$m]['ht_id']);

					 $ateam_name = getNamep("team", $matches[$m]['at_id']);
					 // commentory
					  $index = 1;
						 $xml_com = '';
					 $Ascore = $DB->select("select inning, score, wicket, overs, ing_declare from event_team_result where eventid=".$matches[$m]['id']." and teamid=".$hteam->id." order by inning");
					 // echo count($Ascore); exit;
					 for($s =0; $s < count($Ascore); $s++){

						 if($Ascore[$s]["ing_declare"] == ""){
							 $Ascore[$s]["ing_declare"] = 0;
						 }
						 if($bat_first == "A"){
							 if($Ascore[$s]['inning'] == 2){
								 $ing = 3;
							 } elseif($Ascore[$s]['inning'] == 3){
								 $ing = 5;
							 } else {
								 $ing = 1;
							 }
						 } else {
							 if($Ascore[$s]['inning'] == 2){
								 $ing = 4;
							 } elseif($Ascore[$s]['inning'] == 3){
								 $ing = 6;
							 } else {
								 $ing = 2;
							 }
						 }
						 if($Ascore[$s]['inning'] == 1)
							 $caption = "1ST INN";
						 elseif($Ascore[$s]['inning'] == 2)
							 $caption = "2ND INN";
						 else
							 $caption = "SO INN";
						 $cbowler =0;
						 $cbatsman = 0;
						 $a1data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='bowler'");
						 if(!empty($a1data))
							 $cbowler = $a1data[0][0];
						 $a2data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='cbatsman'");
						 if(!empty($a2data))
							 $cbatsman = $a2data[0][0];
				 /*		if($Ascore[$s]['inning'] == 3)
							 $ing_num = "Super";
						 else*/
							 $ing_num = $Ascore[$s]['inning'];
						 $ing_order = $ing;
						 if($matches[$m]["followon"] == "1"){
							 if($ing_order == 3)
								 $ing_order = 4;
							 elseif($ing_order == 4)
								 $ing_order = 3;
						 }
						 if($Ascore[$s]['overs'] > 0)
							 $runrate = round(($Ascore[$s]["score"] / $Ascore[$s]['overs'])*100)/100;
						 else
							 $runrate = 0;
						 $tscore = $Ascore[$s]['score'];
						 $tover = $Ascore[$s]['overs'];

						 // $data = $DB->select("select id, over_ball, batsman, bowler, scoreFK, comment from stats_comment where eventid=".$matches[$m]['id']." and active='1' and inning='".$ing_order."' order by over_ball desc, id desc ORDER BY id limit 0, $cmlimit");
						 // $data = $DB->select("select id, over_ball, batsman, bowler, scoreFK, comment from stats_comment where eventid=".$matches[$m]['id']." and active='1' group BY id order by over_ball desc, id desc limit 0, $cmlimit");
						 //
						 //
						 // for($rc =0; $rc < count($data); $rc++){
							//  $commentary_flage = true;
							//  	 $bowler_flag = getplayer($data[$rc]['bowler'])? getplayer($data[$rc]['bowler']).' to '.getplayer($data[$rc]['batsman']).'. ':'';
							//  $pdata = $DB->select("select name, es, en, de, dk, se, no from systemdata where systemdata_type='stats_score' and id=".$data[$rc]['scoreFK']);
							//  $sum_tmp = $pdata[0][2] + $pdata[0][3] + $pdata[0][4] + $pdata[0][5] + $pdata[0][6];
							//  $xml_com .= '<LINE COUNT="'.$index.'">';
							//  	$xml_com .= '<OVER>'.$data[$rc]['over_ball'].'</OVER>';
							//  	$xml_com .= '<TEXT>';
							//  	$xml_com .= '<![CDATA[';
							// 		$xml_com .= $data[$rc]['over_ball'].' - '.str_replace("&","&amp;",$pdata[0][0]).'!  '.$bowler_flag.textf($data[$rc]['comment']);
						 //
							// 	$xml_com .= ']]>';
						 //
							// 	$xml_com .= '</TEXT>';
							//  $xml_com .= '</LINE>';
							//  $index = $index+1;
						 //
						 // }

					 }

					 $Ascore = $DB->select("select inning, score, wicket, overs, ing_declare from event_team_result where eventid=".$matches[$m]['id']." and teamid=".$ateam->id." order by inning");
					 // echo count($Ascore); exit;
					 for($s =0; $s < count($Ascore); $s++){

						 if($Ascore[$s]["ing_declare"] == ""){
							 $Ascore[$s]["ing_declare"] = 0;
						 }
						 if($bat_first == "A"){
							 if($Ascore[$s]['inning'] == 2){
								 $ing = 3;
							 } elseif($Ascore[$s]['inning'] == 3){
								 $ing = 5;
							 } else {
								 $ing = 1;
							 }
						 } else {
							 if($Ascore[$s]['inning'] == 2){
								 $ing = 4;
							 } elseif($Ascore[$s]['inning'] == 3){
								 $ing = 6;
							 } else {
								 $ing = 2;
							 }
						 }
						 if($Ascore[$s]['inning'] == 1)
							 $caption = "1ST INN";
						 elseif($Ascore[$s]['inning'] == 2)
							 $caption = "2ND INN";
						 else
							 $caption = "SO INN";
						 $cbowler =0;
						 $cbatsman = 0;
						 $a1data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='bowler'");
						 if(!empty($a1data))
							 $cbowler = $a1data[0][0];
						 $a2data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='cbatsman'");
						 if(!empty($a2data))
							 $cbatsman = $a2data[0][0];
				 /*		if($Ascore[$s]['inning'] == 3)
							 $ing_num = "Super";
						 else*/
							 $ing_num = $Ascore[$s]['inning'];
						 $ing_order = $ing;
						 if($matches[$m]["followon"] == "1"){
							 if($ing_order == 3)
								 $ing_order = 4;
							 elseif($ing_order == 4)
								 $ing_order = 3;
						 }
						 if($Ascore[$s]['overs'] > 0)
							 $runrate = round(($Ascore[$s]["score"] / $Ascore[$s]['overs'])*100)/100;
						 else
							 $runrate = 0;
						 $tscore = $Ascore[$s]['score'];
						 $tover = $Ascore[$s]['overs'];

						 // $data = $DB->select("select id, over_ball, batsman, bowler, scoreFK, comment from stats_comment where eventid=".$matches[$m]['id']." and active='1' group by id order by over_ball desc, id desc  limit 0, $cmlimit");
						 // // $data = $DB->select("select id, over_ball, batsman, bowler, scoreFK, comment from stats_comment where eventid=".$matches[$m]['id']." and active='1' and inning='".$ing_order."' order by over_ball desc, id desc group by id  limit 0, $cmlimit");
						 //
						 //
						 // for($rc =0; $rc < count($data); $rc++){
							//  $commentary_flage = true;
							//  $bowler_flag = getplayer($data[$rc]['bowler'])? getplayer($data[$rc]['bowler']).' to '.getplayer($data[$rc]['batsman']).'. ':'';
							//  $pdata = $DB->select("select name, es, en, de, dk, se, no from systemdata where systemdata_type='stats_score' and id=".$data[$rc]['scoreFK']);
							//  $sum_tmp = $pdata[0][2] + $pdata[0][3] + $pdata[0][4] + $pdata[0][5] + $pdata[0][6];
							//  $xml_com .= '<LINE COUNT="'.$index.'">';
							//  	$xml_com .= '<OVER>'.$data[$rc]['over_ball'].'</OVER>';
							//  	$xml_com .= '<TEXT>';
							//  	$xml_com .= '<![CDATA[';
							// 		$xml_com .= $data[$rc]['over_ball'].' - '.str_replace("&","&amp;",$pdata[0][0]).'!  '.$bowler_flag.textf($data[$rc]['comment']);
						 //
							// 	$xml_com .= ']]>';
						 //
							// 	$xml_com .= '</TEXT>';
							//  $xml_com .= '</LINE>';
							//  $index = $index+1;
						 //
						 // }

					 }



					 $data = $DB->select("select id, over_ball, batsman, bowler, scoreFK, comment from stats_comment where eventid=".$matches[$m]['id']." and active='1' group BY id order by  id desc limit 0, $cmlimit");


					 for($rc =0; $rc < count($data); $rc++){
						 $commentary_flage = true;
							 $bowler_flag = getplayer($data[$rc]['bowler'])? getplayer($data[$rc]['bowler']).' to '.getplayer($data[$rc]['batsman']).'. ':'';
						 $pdata = $DB->select("select name, es, en, de, dk, se, no from systemdata where systemdata_type='stats_score' and id=".$data[$rc]['scoreFK']);
						 $sum_tmp = $pdata[0][2] + $pdata[0][3] + $pdata[0][4] + $pdata[0][5] + $pdata[0][6];
						 $xml_com .= '<LINE COUNT="'.$index.'">';
							$xml_com .= '<OVER>'.$data[$rc]['over_ball'].'</OVER>';
							$xml_com .= '<TEXT>';
							$xml_com .= '<![CDATA[';
								$xml_com .= $data[$rc]['over_ball'].' - '.str_replace("&","&amp;",$pdata[0][0]).'!  '.$bowler_flag.textf($data[$rc]['comment']);

							$xml_com .= ']]>';

							$xml_com .= '</TEXT>';
						 $xml_com .= '</LINE>';
						 $index = $index+1;

					 }


					 // commentory end







					$xmll .= '<HIERARCHY>/liveSport/cricket/'.$cate.'</HIERARCHY>';
	 				$xmll .= '<TITLE CATEGORY="Sport" SUBCATEGORY="Cricket" TYPE="Comments" LABEL="'.$cate.', '.$hteam_name.' v '.$ateam_name.'"/>';
	 				$xmll .= '<COMPETITION UID="'.$matches[$m]['tournament'].'" NAME="'.$competion_name.'"/>';
	 				$xmll .= '<ROUND CPID="1" NAME="'.getName("match_no", $matches[$m]['match_no']).'"/>';
	 				$xmll .= '<MATCH MATCHID="'.$matches[$m]['id'].'"/>';
	 				$xmll .= '<COMMENTARY TYPE="Match" TITLE="'.$status.': '.$hteam_name.' v '.$ateam_name.'" TIMESTAMP="202006080841" TIMEZONE="+02:00">';
					$xmll .= $xml_com;
					$xmll .= '</COMMENTARY>';

			}

			$xmll .= '</xml>';
			echo $xmll;
			// exit;
	}
	if($cmd == 'match_commentary_old'){
			$match = isset($_GET['match'])?$_GET['match']:0;
			$matches = $DB->select("select e.id, e.gt,e.LiveStreamEmbedcode,e.highlights, e.ut, e.finished_ut,e.finished_date, e.match_type, e.match_no, e.ht_id, e.at_id, e.time_difference, e.tournament,
			e.status, e.stadium, e.match_no, e.season, e.umpire1, e.umpire2, e.umpiretv, e.referee, e.match_time, e.comment, e.toss_win, e.winner,
			e.elected, e.live, e.followon, e.live, e.manofmatch, e.manofmatch2, e.condition_wind, e.condition_humidity, e.condition_pressure, e.condition_dewpoint, e.condition_visibility, e.temperature_current, e.temperature_overnight, e.active, e.attendance, e.batsman_of_match, e.bowler_of_match, e.batsman_of_match_id, e.bowler_of_match_id, e.betway_id, e.LiveStreamEmbedcode from event e join participant p on e.tournament=p.id and p.participant_type='tournament'
			where e.id = '$match'");
	$xmll = '<xml>';
			for($m =0; $m < count($matches); $m++){

                            if(($matches[$m]['toss_win'] == "A" && $matches[$m]['elected'] == 1) || ($matches[$m]['toss_win'] == "B" && $matches[$m]['elected'] == 2))
                                    $bat_first = "A";
                            else
                                    $bat_first = "B";



				if($matches[$m]["live"] == 2 && $matches[$m]['status'] == 1){
					$status = "Result only";
				} else {
					$status = getName("status", $matches[$m]['status']);
				}
				$current_ing = 0;
				$current_xing = 0;
				$current_team = 0;
				$xres = $DB->select("select inning, data from stats_settings where eventid=".$matches[$m]["id"]." and stats_type='batting_team' and data <> '0' order by stats_settings.inning desc");
				if(!empty($xres)){
					$current_ing = $xres[0]["inning"];
					$current_team = $xres[0]["data"];
					if($current_ing == 1 || $current_ing == 2){
						$current_xing = 1;
					} else {
						$current_xing = 2;
					}
				}


				$cate = '';
				$competion_name = '';
				$tour = new tournament($matches[$m]['tournament']);
				$tour->load();

				$hteam = new team($matches[$m]['ht_id']);
					$hteam->load();
					$ateam = new team($matches[$m]['at_id']);
					$ateam->load();

					if($tour->categ == 'intl_test'){
								$cate = 'Test';
								$competion_name = 'International Test';
					}elseif ($tour->categ == 'intl_odi') {
						$cate = 'ODI';
						$competion_name = 'One Day International ';
					}elseif ($tour->categ == 'intl_t20') {
						$cate = 'T20';
							$competion_name = 'International T20';
					}

					 $hteam_name = getNamep("team", $matches[$m]['ht_id']);

					 $ateam_name = getNamep("team", $matches[$m]['at_id']);
					 // commentory
					  $index = 1;
						 $xml_com = '';
					 $Ascore = $DB->select("select inning, score, wicket, overs, ing_declare from event_team_result where eventid=".$matches[$m]['id']." and teamid=".$hteam->id." order by inning");
					 // echo count($Ascore); exit;
					 for($s =0; $s < count($Ascore); $s++){

						 if($Ascore[$s]["ing_declare"] == ""){
							 $Ascore[$s]["ing_declare"] = 0;
						 }
						 if($bat_first == "A"){
							 if($Ascore[$s]['inning'] == 2){
								 $ing = 3;
							 } elseif($Ascore[$s]['inning'] == 3){
								 $ing = 5;
							 } else {
								 $ing = 1;
							 }
						 } else {
							 if($Ascore[$s]['inning'] == 2){
								 $ing = 4;
							 } elseif($Ascore[$s]['inning'] == 3){
								 $ing = 6;
							 } else {
								 $ing = 2;
							 }
						 }
						 if($Ascore[$s]['inning'] == 1)
							 $caption = "1ST INN";
						 elseif($Ascore[$s]['inning'] == 2)
							 $caption = "2ND INN";
						 else
							 $caption = "SO INN";
						 $cbowler =0;
						 $cbatsman = 0;
						 $a1data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='bowler'");
						 if(!empty($a1data))
							 $cbowler = $a1data[0][0];
						 $a2data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='cbatsman'");
						 if(!empty($a2data))
							 $cbatsman = $a2data[0][0];
				 /*		if($Ascore[$s]['inning'] == 3)
							 $ing_num = "Super";
						 else*/
							 $ing_num = $Ascore[$s]['inning'];
						 $ing_order = $ing;
						 if($matches[$m]["followon"] == "1"){
							 if($ing_order == 3)
								 $ing_order = 4;
							 elseif($ing_order == 4)
								 $ing_order = 3;
						 }
						 if($Ascore[$s]['overs'] > 0)
							 $runrate = round(($Ascore[$s]["score"] / $Ascore[$s]['overs'])*100)/100;
						 else
							 $runrate = 0;
						 $tscore = $Ascore[$s]['score'];
						 $tover = $Ascore[$s]['overs'];

						 $data = $DB->select("select id, over_ball, batsman, bowler, scoreFK, comment from stats_comment where eventid=".$matches[$m]['id']." and active='1' and inning='".$ing_order."' order by over_ball desc, id desc limit 0, $cmlimit");


						 for($rc =0; $rc < count($data); $rc++){
							 $commentary_flage = true;
							 $pdata = $DB->select("select name, es, en, de, dk, se, no from systemdata where systemdata_type='stats_score' and id=".$data[$rc]['scoreFK']);
							 $sum_tmp = $pdata[0][2] + $pdata[0][3] + $pdata[0][4] + $pdata[0][5] + $pdata[0][6];
							 $xml_com .= '<LINE COUNT="'.$index.'">';
							 	$xml_com .= '<OVER>'.$data[$rc]['over_ball'].'</OVER>';
							 	$xml_com .= '<TEXT>';
							 	$xml_com .= '<![CDATA[';
									$xml_com .= $data[$rc]['over_ball'].' - '.str_replace("&","&amp;",$pdata[0][0]).'!  '.getplayer($data[$rc]['batsman']).' to '.getplayer($data[$rc]['bowler']).'. '.textf($data[$rc]['comment']);

								$xml_com .= ']]>';

								$xml_com .= '</TEXT>';
							 $xml_com .= '</LINE>';
							 $index = $index+1;

						 }

					 }

					 $Ascore = $DB->select("select inning, score, wicket, overs, ing_declare from event_team_result where eventid=".$matches[$m]['id']." and teamid=".$ateam->id." order by inning");
					 // echo count($Ascore); exit;
					 for($s =0; $s < count($Ascore); $s++){

						 if($Ascore[$s]["ing_declare"] == ""){
							 $Ascore[$s]["ing_declare"] = 0;
						 }
						 if($bat_first == "A"){
							 if($Ascore[$s]['inning'] == 2){
								 $ing = 3;
							 } elseif($Ascore[$s]['inning'] == 3){
								 $ing = 5;
							 } else {
								 $ing = 1;
							 }
						 } else {
							 if($Ascore[$s]['inning'] == 2){
								 $ing = 4;
							 } elseif($Ascore[$s]['inning'] == 3){
								 $ing = 6;
							 } else {
								 $ing = 2;
							 }
						 }
						 if($Ascore[$s]['inning'] == 1)
							 $caption = "1ST INN";
						 elseif($Ascore[$s]['inning'] == 2)
							 $caption = "2ND INN";
						 else
							 $caption = "SO INN";
						 $cbowler =0;
						 $cbatsman = 0;
						 $a1data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='bowler'");
						 if(!empty($a1data))
							 $cbowler = $a1data[0][0];
						 $a2data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='cbatsman'");
						 if(!empty($a2data))
							 $cbatsman = $a2data[0][0];
				 /*		if($Ascore[$s]['inning'] == 3)
							 $ing_num = "Super";
						 else*/
							 $ing_num = $Ascore[$s]['inning'];
						 $ing_order = $ing;
						 if($matches[$m]["followon"] == "1"){
							 if($ing_order == 3)
								 $ing_order = 4;
							 elseif($ing_order == 4)
								 $ing_order = 3;
						 }
						 if($Ascore[$s]['overs'] > 0)
							 $runrate = round(($Ascore[$s]["score"] / $Ascore[$s]['overs'])*100)/100;
						 else
							 $runrate = 0;
						 $tscore = $Ascore[$s]['score'];
						 $tover = $Ascore[$s]['overs'];

						 $data = $DB->select("select id, over_ball, batsman, bowler, scoreFK, comment from stats_comment where eventid=".$matches[$m]['id']." and active='1' and inning='".$ing_order."' order by over_ball desc, id desc limit 0, $cmlimit");


						 for($rc =0; $rc < count($data); $rc++){
							 $commentary_flage = true;
							 $pdata = $DB->select("select name, es, en, de, dk, se, no from systemdata where systemdata_type='stats_score' and id=".$data[$rc]['scoreFK']);
							 $sum_tmp = $pdata[0][2] + $pdata[0][3] + $pdata[0][4] + $pdata[0][5] + $pdata[0][6];
							 $xml_com .= '<LINE COUNT="'.$index.'">';
							 	$xml_com .= '<OVER>'.$data[$rc]['over_ball'].'</OVER>';
							 	$xml_com .= '<TEXT>';
							 	$xml_com .= '<![CDATA[';
									$xml_com .= $data[$rc]['over_ball'].' - '.str_replace("&","&amp;",$pdata[0][0]).'!  '.getplayer($data[$rc]['batsman']).' to '.getplayer($data[$rc]['bowler']).'. '.textf($data[$rc]['comment']);

								$xml_com .= ']]>';

								$xml_com .= '</TEXT>';
							 $xml_com .= '</LINE>';
							 $index = $index+1;

						 }

					 }



					 // commentory end







					$xmll .= '<HIERARCHY>/liveSport/cricket/'.$cate.'</HIERARCHY>';
	 				$xmll .= '<TITLE CATEGORY="Sport" SUBCATEGORY="Cricket" TYPE="Comments" LABEL="'.$cate.', '.$hteam_name.' v '.$ateam_name.'"/>';
	 				$xmll .= '<COMPETITION UID="'.$matches[$m]['tournament'].'" NAME="'.$competion_name.'"/>';
	 				$xmll .= '<ROUND CPID="1" NAME="'.getName("match_no", $matches[$m]['match_no']).'"/>';
	 				$xmll .= '<MATCH MATCHID="'.$matches[$m]['id'].'"/>';
	 				$xmll .= '<COMMENTARY TYPE="Match" TITLE="'.$status.': '.$hteam_name.' v '.$ateam_name.'" TIMESTAMP="202006080841" TIMEZONE="+02:00">';
					$xmll .= $xml_com;
					$xmll .= '</COMMENTARY>';

			}

			$xmll .= '</xml>';
			echo $xmll;
			// exit;
	}

	if($cmd == 'match_info'){
		$match = isset($_GET['match'])?$_GET['match']:0;
                $whitelist = whitelist($customer[0]['id']);
                $whitelist_string = ($whitelist)? " AND e.tournament IN (".$whitelist.")" : "";
                
		$matches = $DB->select("select e.id, e.gt,e.LiveStreamEmbedcode,e.highlights, e.ut, e.finished_ut,e.finished_date, e.match_type, e.match_no, e.ht_id, e.at_id, e.time_difference, e.tournament,
		e.status, e.stadium, e.match_no, e.season, e.umpire1, e.umpire2, e.umpiretv, e.referee, e.match_time, e.comment, e.toss_win, e.winner,
		e.elected, e.live, e.followon, e.live, e.manofmatch, e.manofmatch2, e.condition_wind, e.condition_humidity, e.condition_pressure, e.condition_dewpoint, e.condition_visibility, e.temperature_current, e.temperature_overnight, e.active, e.attendance, e.batsman_of_match, e.bowler_of_match, e.batsman_of_match_id, e.bowler_of_match_id, e.betway_id, e.LiveStreamEmbedcode from event e join participant p on e.tournament=p.id and p.participant_type='tournament'
		where e.id = '$match'" . $whitelist_string);

		$xml = '<xml>';
		$xml .= '<HIERARCHY>liveSport/cricketodi</HIERARCHY>';
		for($m =0; $m < count($matches); $m++){
			if($matches[$m]["live"] == 2 && $matches[$m]['status'] == 1){
				$status = "Result only";
			} else {
				$status = getName("status", $matches[$m]['status']);
			}
			$current_ing = 0;
			$current_xing = 0;
			$current_team = 0;
			$xres = $DB->select("select inning, data from stats_settings where eventid=".$matches[$m]["id"]." and stats_type='batting_team' and data <> '0' order by stats_settings.inning desc");
			if(!empty($xres)){
				$current_ing = $xres[0]["inning"];
				$current_team = $xres[0]["data"];
				if($current_ing == 1 || $current_ing == 2){
					$current_xing = 1;
				} else {
					$current_xing = 2;
				}
			}
			$std = new stadium($matches[$m]['stadium']);
			$std->load();
			$tour = new tournament($matches[$m]['tournament']);
			$tour->load();
			$cate = '';
				if($tour->categ == 'intl_test'){
							$cate = 'Test';
				}elseif ($tour->categ == 'intl_odi') {
					$cate = 'ODI';
				}elseif ($tour->categ == 'intl_t20') {
					$cate = 'T20';
				}



				$ht_summary = array();
					$at_summary = array();
					$score_summary = $DB->select("select inning, teamid, score, wicket, overs, ing_declare from event_team_result where eventid=".$matches[$m]['id']." order by inning");
					for($ss =0; $ss < sizeof($score_summary); $ss++){
						$tmp_sum = $score_summary[$ss]["score"]."/".$score_summary[$ss]["wicket"];//." (".$score_summary[$ss]["overs"].")";
						if($score_summary[$ss]["teamid"] == $matches[$m]["ht_id"]){
							$ht_summary[] = $tmp_sum;
						} else {
							$at_summary[] = $tmp_sum;
						}
					}


					if($matches[$m]['toss_win'] == "A")
					$ts_team = getNamep("team", $matches[$m]['ht_id']);
					else
					$ts_team = getNamep("team", $matches[$m]['at_id']);

				$home_team = getNamep("team", $matches[$m]['ht_id']);
				$away_team = getNamep("team", $matches[$m]['at_id']);
				$xml .= '<TITLE CATEGORY="Sport" SUBCATEGORY="Cricket" TYPE="'.$status.'" LABEL=" '.str_replace("&","&amp;", $tour->name).' "/>';
				$xml .= '<DATAFORMAT CODE="LiveCricket" TABLE="CricketLiveMatchDetails" DESCRIPTION="Cricket - Standard"/>';
				$xml .=  '<MATCHSUMMARY SPORTID="2" UID="'.$matches[$m]['tournament'].'" COMPETITIONNAME="'.$cate.'" CPID="1" ROUNDNAME="'.getName("match_no", $matches[$m]['match_no']).'" MATCHID="'.$matches[$m]['id'].'" MATCHTITLE="'.str_replace("&","&amp;", $tour->name).' at '.$std->name.' ('.getName("match_time",$matches[$m]['match_time']).'), '.$cate.'" FXDATE="'.$matches[$m]['gt'].'" ACTIVE="'.((time() >= (strtotime($matches[$m]['gt']) - 1800) && !in_array($matches[$m]['status'],array(8,10,39,62)) && ( (!$matches[$m]['finished_ut'] || $matches[$m]['finished_ut'] == '0000-00-00 00:00:00') || time() <= (strtotime($matches[$m]['finished_ut']) + 1800) ))? "YES" :"NO").'">';
					$xml .= '<HOMETEAMNAME TEAMID="'.$matches[$m]['ht_id'].'" TEAMCODE="">'.$home_team.'</HOMETEAMNAME>';
					$xml .= '<HOMETEAMSCORE>'. implode(" &amp; ", $ht_summary).'</HOMETEAMSCORE>';
					$xml .= '<AWAYTEAMNAME  TEAMID="'.$matches[$m]['at_id'].'" TEAMCODE="">'.$away_team.'</AWAYTEAMNAME >';
					$xml .= '<AWAYTEAMSCORE>'. implode(" &amp; ", $at_summary).'</AWAYTEAMSCORE>';
					$xml .= '<MATCHSTATUS STATUS="'.str_replace("&", "&amp;", $matches[$m]['comment']).'" TOSS="'.str_replace("&","&amp;",$ts_team).' , elected to '.getName("elected", $matches[$m]['elected']).'" MANOFMATCH="'.str_replace("&", "&amp;", $matches[$m]['manofmatch2']).'"/>';

					$xml .= '<MATCHOFFICIALS>';

								$umpire1 = new player($matches[$m]['umpire1']);
								$umpire1->load();
								$umpire2 = new player($matches[$m]['umpire2']);
								$umpire2->load();
								$umpiretv = new player($matches[$m]['umpiretv']);
								$umpiretv->load();
								$referee = new player($matches[$m]['referee']);
								$referee->load();
						$xml .=	'<UMPIRE1>'.$umpire1->name.'</UMPIRE1>';
						$xml .=	'<UMPIRE2>'.$umpire2->name.'</UMPIRE2>';
						$xml .=	'<UMPIRETV>'.$umpiretv->name.'</UMPIRETV>';
						$xml .=	'<REFEREE>'.$referee->name.'</REFEREE>';
					$xml .= '</MATCHOFFICIALS>';
					$xml .= '<MATCHDETAIL VENUENAME="'.$std->name.' ('.getName("match_time",$matches[$m]['match_time']).')" ATTENDANCE=""/>';
				$xml .= '</MATCHSUMMARY>';
				$xml .= '<SCORECARD>';

				$hteam = new team($matches[$m]['ht_id']);
					$hteam->load();
					$ateam = new team($matches[$m]['at_id']);
					$ateam->load();

				$Ascore = $DB->select("select inning, score, wicket, overs, ing_declare from event_team_result where eventid=".$matches[$m]['id']." and teamid=".$hteam->id." order by inning");
				for($s =0; $s < count($Ascore); $s++){
					if($Ascore[$s]["ing_declare"] == ""){
						$Ascore[$s]["ing_declare"] = 0;
					}
					if($bat_first == "B"){
						if($Ascore[$s]['inning'] == 2){
							$ing = 3;
						} elseif($Ascore[$s]['inning'] == 3){
							$ing = 5;
						} else {
							$ing = 1;
						}
					} else {
						if($Ascore[$s]['inning'] == 2){
							$ing = 4;
						} elseif($Ascore[$s]['inning'] == 3){
							$ing = 6;
						} else {
							$ing = 2;
						}
					}
					if($Ascore[$s]['inning'] == 1)
						$caption = "1ST INN";
					elseif($Ascore[$s]['inning'] == 2)
						$caption = "2ND INN";
					else
						$caption = "SO INN";
					$cbowler =0;
					$cbatsman = 0;
					$a1data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='bowler'");
					if(!empty($a1data))
						$cbowler = $a1data[0][0];
					$a2data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='cbatsman'");
					if(!empty($a2data))
						$cbatsman = $a2data[0][0];
			/*		if($Ascore[$s]['inning'] == 3)
						$ing_num = "Super";
					else*/
						$ing_num = $Ascore[$s]['inning'];
					$ing_order = $ing;
					if($matches[$m]["followon"] == "1"){
						if($ing_order == 3)
							$ing_order = 4;
						elseif($ing_order == 4)
							$ing_order = 3;
					}
					if($Ascore[$s]['overs'] > 0)
						$runrate = round(($Ascore[$s]["score"] / $Ascore[$s]['overs'])*100)/100;
					else
						$runrate = 0;
					$tscore = $Ascore[$s]['score'];
					$tover = $Ascore[$s]['overs'];
					$active_inn = ($ing == $current_ing)?"Yes":"No";
					$extra = $DB->select("select wide, noball, bye, legbye, penalty from event_extra where eventid=".$matches[$m]['id']." and inning=".$Ascore[$s]['inning']." and teamid=".$hteam->id);

					$extra_sum = $extra[0]['wide'] + $extra[0]['noball'] + $extra[0]['bye'] + $extra[0]['legbye']+$extra[0]['penalty'];
					$xml .= '<INNINGS ID="'.$ing_num.'" CURRENT="'.$active_inn.'" NAME=" '.str_replace("&", "&amp;", $hteam->name).' Innings" TOTAL="'.$Ascore[$s]['score'].'" WICKETS="'.$Ascore[$s]['wicket'].'" OVERS="'.$Ascore[$s]['overs'].'" EXTRAS="'.$extra_sum.'" EXTRASDETAIL="b '.$extra[0]['bye'].' p '.$extra[0]['penalty'].' nb '.$extra[0]['noball'].' lb '.$extra[0]['legbye'].', w '.$extra[0]['wide'].')" RPO="'.$runrate.'">';
						$xml .='<BATTING>';
					$hs_batsman = array(0,0);
						$inning = $DB->select("select event_playerex.id, event_playerex.playerid, score, balls, s4, s6,
						sortorder, wicket_type, bowledby, catchby from event_playerex, event_batsman where
						event_playerex.id=event_batsman.event_playerFK and
						event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$hteam->id." and
						event_playerex.inning=".$Ascore[$s]['inning']." and event_playerex.active='1' and
						event_batsman.active='1' and event_batsman.inning=".$Ascore[$s]['inning']." order by sortorder");
						for($i=0; $i < count($inning); $i++){
							$battingline_flage = true;
							if($inning[$i]['playerid'] == $cbatsman) $act=1; else $act = 0;
									$xml .= '<BATSMAN PLNAME="'.getPlayer($inning[$i]['playerid']).'" PLID="'.$inning[$i]['playerid'].'" BATSMANNO="'.$inning[$i]['sortorder'].'" HOWOUT="'.getName("wicket", $inning[$i]['wicket_type']).'" RUNS="'.$inning[$i]['score'].'" BALLS="'.$inning[$i]['balls'].'" X4="'.$inning[$i]['s4'].'" X6="'.$inning[$i]['s6'].'"/>';

							if($inning[$i]['score'] > $hs_batsman[0]){
								$hs_batsman[0] = $inning[$i]['score'];
								$hs_batsman[1] = $inning[$i]['playerid'];
							}
						}

						$xml .= '</BATTING>';
						$xml .= '<FALLOFWICKETS >';
						$fall = $DB->select("select fow_score, fow_over, playerid, bowledby, wicket_type from event_playerex, event_batsman where
							event_playerex.id=event_batsman.event_playerFK and
							event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$hteam->id." and
							event_playerex.inning=".$Ascore[$s]['inning']." and event_playerex.active='1' and
							event_batsman.active='1' and event_batsman.inning=".$Ascore[$s]['inning']." and (fow_score > 0 or fow_over > 0) order by fow_score, fow_over");
							for($f=0; $f < count($fall); $f++){
							$fallofwicket_flage = true;
							$fow_text_rs = $DB->select("select comment from stats_comment where eventid=".$matches[$m]['id']." and scoreFK != 91 and scoreFK IN (15, 21, 22, 23, 24, 25, 26, 33, 35, 39,40,41, 46, 50,55,56,57,58,59,60,61,62,63,64,65,36, 67,68,69,70,72,73,74,75,76,77,78,79,81,82,83,84,85,86,87,88,92,93,98,100,106,109) and over_ball like '".$fall[$f]['fow_over']."' and inning=".$ing_order);
							if(empty($fow_text_rs)){
							$fow_text = "";
							} else {
							$fow_text = textf($fow_text_rs[0]["comment"]);
							}
							//echo "<fallofwicket playerid=\"".$fall[$f]['playerid']."\"  name=\"".getPlayer($fall[$f]['playerid'])."\" score=\"".$fall[$f]['fow_score']."\" bowler=\"".getPlayer($fall[$f]['bowledby'])."\" bowler_id=\"".$fall[$f]['bowledby']."\" wicket=\"".getName("wicket", $fall[$f]["wicket_type"])."\" ball=\"".$fall[$f]['fow_over']."\" text=\"".$fow_text."\" number=\"".($f+1)."\" />";
							$xml .= ($f+1).'-'.$fall[$f]['fow_score'].'('.getPlayer($fall[$f]['playerid']).', '.$fall[$f]['fow_over'].' ov)'.',';
							}
						$xml .= '</FALLOFWICKETS>';
						$xml .= '<BOWLING>';
						$hs_bowler = array(0,0,0);
								$inning = $DB->select("select event_playerex.playerid, over, mdn, run , wkt, wide, noball, sortorder
								from event_playerex, event_bowler  where
								event_playerex.id=event_bowler.event_playerFK and
								event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$ateam->id." and
								event_playerex.inning=".$Ascore[$s]['inning']." and event_playerex.active='1' and
								event_bowler.active='1' and event_bowler.inning=".$Ascore[$s]['inning']." order by sortorder");
								for($i=0; $i < count($inning); $i++){
									$bowlingline_flage = true;
									if($inning[$i]['playerid'] == $cbowler) $act=1; else $act = 0;
									$runrate = runrate($inning[$i]['run'], $inning[$i]['over']);
									// echo "<bowler playerid=\"".$inning[$i]['playerid']."\" name=\"".getPlayer($inning[$i]['playerid'])."\" overs=\"".$inning[$i]['over']."\" mdns=\"".$inning[$i]['mdn']."\" score=\"".$inning[$i]['run']."\" wickets=\"".$inning[$i]['wkt']."\" runrate=\"".$runrate."\" wide=\"".$inning[$i]['wide']."\" noball=\"".$inning[$i]['noball']."\" order=\"".$inning[$i]['sortorder']."\" active=\"$act\" />";
										$xml .= '<BOWLER PLNAME="'.getPlayer($inning[$i]['playerid']).'" PLID="'.$inning[$i]['playerid'].'" BOWLERNO="'.$inning[$i]['sortorder'].'" OVERS="'.$inning[$i]['over'].'" MAIDENS="'.$inning[$i]['mdn'].'" RUNS="'.$inning[$i]['run'].'" WICKETS="'.$inning[$i]['wkt'].'" ECON="'.$runrate.'"/>';
									if($inning[$i]['wkt'] > $hs_bowler[0]){
										$hs_bowler[0] = $inning[$i]['wkt'];
										$hs_bowler[1] = $inning[$i]['playerid'];
										$hs_bowler[2] = $runrate;
									}elseif($inning[$i]['wkt'] == $hs_bowler[0] && $runrate < $hs_bowler[2]){
										$hs_bowler[0] = $inning[$i]['wkt'];
										$hs_bowler[1] = $inning[$i]['playerid'];
										$hs_bowler[2] = $runrate;
									}
								}
								if($hs_bowler[0] > 0){
									//echo "<best_bowler playerid=\"".$hs_bowler[1]."\" name=\"".getPlayer($hs_bowler[1])."\" wickets=\"".$hs_bowler[0]."\" runrate=\"".$hs_bowler[2]."\" />";
								}




						$xml .= '</BOWLING>';
					$xml .= '</INNINGS>';

				}

				$Ascore = $DB->select("select inning, score, wicket, overs, ing_declare from event_team_result where eventid=".$matches[$m]['id']." and teamid=".$ateam->id." order by inning");
				for($s =0; $s < count($Ascore); $s++){
					if($Ascore[$s]["ing_declare"] == ""){
						$Ascore[$s]["ing_declare"] = 0;
					}
					if($bat_first == "B"){
						if($Ascore[$s]['inning'] == 2){
							$ing = 3;
						} elseif($Ascore[$s]['inning'] == 3){
							$ing = 5;
						} else {
							$ing = 1;
						}
					} else {
						if($Ascore[$s]['inning'] == 2){
							$ing = 4;
						} elseif($Ascore[$s]['inning'] == 3){
							$ing = 6;
						} else {
							$ing = 2;
						}
					}
					if($Ascore[$s]['inning'] == 1)
						$caption = "1ST INN";
					elseif($Ascore[$s]['inning'] == 2)
						$caption = "2ND INN";
					else
						$caption = "SO INN";
					$cbowler =0;
					$cbatsman = 0;
					$a1data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='bowler'");
					if(!empty($a1data))
						$cbowler = $a1data[0][0];
					$a2data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='cbatsman'");
					if(!empty($a2data))
						$cbatsman = $a2data[0][0];
			/*		if($Ascore[$s]['inning'] == 3)
						$ing_num = "Super";
					else*/
						$ing_num = $Ascore[$s]['inning'];
					$ing_order = $ing;
					if($matches[$m]["followon"] == "1"){
						if($ing_order == 3)
							$ing_order = 4;
						elseif($ing_order == 4)
							$ing_order = 3;
					}
					if($Ascore[$s]['overs'] > 0)
						$runrate = round(($Ascore[$s]["score"] / $Ascore[$s]['overs'])*100)/100;
					else
						$runrate = 0;
					$tscore = $Ascore[$s]['score'];
					$tover = $Ascore[$s]['overs'];
					$active_inn = ($ing == $current_ing)?"Yes":"No";
					$extra = $DB->select("select wide, noball, bye, legbye, penalty from event_extra where eventid=".$matches[$m]['id']." and inning=".$Ascore[$s]['inning']." and teamid=".$ateam->id);

					$extra_sum = $extra[0]['wide'] + $extra[0]['noball'] + $extra[0]['bye'] + $extra[0]['legbye']+$extra[0]['penalty'];
					$xml .= '<INNINGS ID="'.$ing_num.'" CURRENT="'.$active_inn.'" NAME=" '.str_replace("&", "&amp;", $ateam->name).' Innings" TOTAL="'.$Ascore[$s]['score'].'" WICKETS="'.$Ascore[$s]['wicket'].'" OVERS="'.$Ascore[$s]['overs'].'" EXTRAS="'.$extra_sum.'" EXTRASDETAIL="b '.$extra[0]['bye'].' p '.$extra[0]['penalty'].' nb '.$extra[0]['noball'].' lb '.$extra[0]['legbye'].', w '.$extra[0]['wide'].')" RPO="'.$runrate.'">';
						$xml .='<BATTING>';
					$hs_batsman = array(0,0);
						$inning = $DB->select("select event_playerex.id, event_playerex.playerid, score, balls, s4, s6,
						sortorder, wicket_type, bowledby, catchby from event_playerex, event_batsman where
						event_playerex.id=event_batsman.event_playerFK and
						event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$ateam->id." and
						event_playerex.inning=".$Ascore[$s]['inning']." and event_playerex.active='1' and
						event_batsman.active='1' and event_batsman.inning=".$Ascore[$s]['inning']." order by sortorder");
						for($i=0; $i < count($inning); $i++){
							$battingline_flage = true;
							if($inning[$i]['playerid'] == $cbatsman) $act=1; else $act = 0;
									$xml .= '<BATSMAN PLNAME="'.getPlayer($inning[$i]['playerid']).'" PLID="'.$inning[$i]['playerid'].'" BATSMANNO="'.$inning[$i]['sortorder'].'" HOWOUT="'.getName("wicket", $inning[$i]['wicket_type']).'" RUNS="'.$inning[$i]['score'].'" BALLS="'.$inning[$i]['balls'].'" X4="'.$inning[$i]['s4'].'" X6="'.$inning[$i]['s6'].'"/>';

							if($inning[$i]['score'] > $hs_batsman[0]){
								$hs_batsman[0] = $inning[$i]['score'];
								$hs_batsman[1] = $inning[$i]['playerid'];
							}
						}

						$xml .= '</BATTING>';
						$xml .= '<FALLOFWICKETS >';
						$fall = $DB->select("select fow_score, fow_over, playerid, bowledby, wicket_type from event_playerex, event_batsman where
							event_playerex.id=event_batsman.event_playerFK and
							event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$ateam->id." and
							event_playerex.inning=".$Ascore[$s]['inning']." and event_playerex.active='1' and
							event_batsman.active='1' and event_batsman.inning=".$Ascore[$s]['inning']." and (fow_score > 0 or fow_over > 0) order by fow_score, fow_over");
							for($f=0; $f < count($fall); $f++){
							$fallofwicket_flage = true;
							$fow_text_rs = $DB->select("select comment from stats_comment where eventid=".$matches[$m]['id']." and scoreFK != 91 and scoreFK IN (15, 21, 22, 23, 24, 25, 26, 33, 35, 39,40,41, 46, 50,55,56,57,58,59,60,61,62,63,64,65,36, 67,68,69,70,72,73,74,75,76,77,78,79,81,82,83,84,85,86,87,88,92,93,98,100,106,109) and over_ball like '".$fall[$f]['fow_over']."' and inning=".$ing_order);
							if(empty($fow_text_rs)){
							$fow_text = "";
							} else {
							$fow_text = textf($fow_text_rs[0]["comment"]);
							}
							//echo "<fallofwicket playerid=\"".$fall[$f]['playerid']."\"  name=\"".getPlayer($fall[$f]['playerid'])."\" score=\"".$fall[$f]['fow_score']."\" bowler=\"".getPlayer($fall[$f]['bowledby'])."\" bowler_id=\"".$fall[$f]['bowledby']."\" wicket=\"".getName("wicket", $fall[$f]["wicket_type"])."\" ball=\"".$fall[$f]['fow_over']."\" text=\"".$fow_text."\" number=\"".($f+1)."\" />";
							$xml .= ($f+1).'-'.$fall[$f]['fow_score'].'('.getPlayer($fall[$f]['playerid']).', '.$fall[$f]['fow_over'].' ov)'.',';
							}
						$xml .= '</FALLOFWICKETS>';
						$xml .= '<BOWLING>';
						$hs_bowler = array(0,0,0);
								$inning = $DB->select("select event_playerex.playerid, over, mdn, run , wkt, wide, noball, sortorder
								from event_playerex, event_bowler  where
								event_playerex.id=event_bowler.event_playerFK and
								event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$hteam->id." and
								event_playerex.inning=".$Ascore[$s]['inning']." and event_playerex.active='1' and
								event_bowler.active='1' and event_bowler.inning=".$Ascore[$s]['inning']." order by sortorder");
								for($i=0; $i < count($inning); $i++){
									$bowlingline_flage = true;
									if($inning[$i]['playerid'] == $cbowler) $act=1; else $act = 0;
									$runrate = runrate($inning[$i]['run'], $inning[$i]['over']);
									// echo "<bowler playerid=\"".$inning[$i]['playerid']."\" name=\"".getPlayer($inning[$i]['playerid'])."\" overs=\"".$inning[$i]['over']."\" mdns=\"".$inning[$i]['mdn']."\" score=\"".$inning[$i]['run']."\" wickets=\"".$inning[$i]['wkt']."\" runrate=\"".$runrate."\" wide=\"".$inning[$i]['wide']."\" noball=\"".$inning[$i]['noball']."\" order=\"".$inning[$i]['sortorder']."\" active=\"$act\" />";
										$xml .= '<BOWLER PLNAME="'.getPlayer($inning[$i]['playerid']).'" PLID="'.$inning[$i]['playerid'].'" BOWLERNO="'.$inning[$i]['sortorder'].'" OVERS="'.$inning[$i]['over'].'" MAIDENS="'.$inning[$i]['mdn'].'" RUNS="'.$inning[$i]['run'].'" WICKETS="'.$inning[$i]['wkt'].'" ECON="'.$runrate.'"/>';
									if($inning[$i]['wkt'] > $hs_bowler[0]){
										$hs_bowler[0] = $inning[$i]['wkt'];
										$hs_bowler[1] = $inning[$i]['playerid'];
										$hs_bowler[2] = $runrate;
									}elseif($inning[$i]['wkt'] == $hs_bowler[0] && $runrate < $hs_bowler[2]){
										$hs_bowler[0] = $inning[$i]['wkt'];
										$hs_bowler[1] = $inning[$i]['playerid'];
										$hs_bowler[2] = $runrate;
									}
								}
								if($hs_bowler[0] > 0){
									//echo "<best_bowler playerid=\"".$hs_bowler[1]."\" name=\"".getPlayer($hs_bowler[1])."\" wickets=\"".$hs_bowler[0]."\" runrate=\"".$hs_bowler[2]."\" />";
								}




						$xml .= '</BOWLING>';
					$xml .= '</INNINGS>';

				}



				$xml .= '</SCORECARD>';

		}

		$xml .= '</xml>';
			 echo $xml;
		//echo "<pre>";print_r($matches); exit;

	}

	//Team info
	if($cmd == 'team_info'){
		$tournamentid = isset($_GET['tournamentid'])?$_GET['tournamentid']:0;
		$team_id = isset($_GET['teamid'])?$_GET['teamid']:0;
		$teams = $DB->select("
			select
			e.tournament,
			e.stadium,
			tt.teamid,
			stadium.name as stadium_name,
			stadium.city as stadium_city,
			stadium.city as stadium_city,
			country.name as country_name,

			team.name as team_name,
			team.history as team_history

			from event as e

				JOIN tournament_team as tt on (tt.tourid = e.tournament)

				JOIN systemdatac as stadium on (stadium.id = e.stadium and stadium.systemdata_type='stadium')

				JOIN participant as team on (team.id = tt.teamid and team.participant_type='team')

				JOIN participant as country on (country.country = stadium.country and country.participant_type='team')

				where
				(CASE
					WHEN ($team_id != 0) THEN e.tournament = '$tournamentid' and tt.teamid =  '$team_id'

					ELSE e.tournament = '$tournamentid' END
				)
				group by tt.teamid

		");

		  // print_r($teams); exit;

	$stadiumid = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $teams[0][stadium]);

	$stadium_name = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $teams[0][stadium_name]);
	$stadium_city = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $teams[0][stadium_city]);
	$stadium_country = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $teams[0][country_name]);
	 $tournaments = "<tournament id= '$tournamentid' > ";
		$tournaments .= "<stadium id= '$stadiumid' name='$stadium_name' city='$stadium_city' country='$stadium_country'/>";

		foreach($teams as $team){
			$tournaments .= "<team id= '$team[teamid]' name='$team[team_name]' history='$team[team_history]'>";

			$team_players = $DB->select("
				select CONCAT(p.firstname,' ',p.lastname) as playername,p.id as player_id,
				player_type.name as player_role
				from player as p
				Join tournament_team_squad as tts On (p.id = tts.player_id and tts.team_id=$team[teamid] and tts.tour_id=$tournamentid)
				Join playerteam as pt On (pt.playerid = p.id)

				Join systemdata as player_type on (player_type.id=p.player_type and player_type.systemdata_type = 'player_type')
				where tts.team_id = '$team[teamid]'
				group by p.id

			");

			foreach($team_players as $team_player){
				// print_r($team_player);exit;
				$role_p = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $team_player[player_role]);
				$tournaments .= "<player id='$team_player[player_id]' name='$team_player[playername]' role='$role_p'/>";

				// $tournaments .= "</player>";
			}



			// // print_r($teams);exit;
			$tournaments .= "</team>";
		}
	 $tournaments .= "</tournament>";

	 echo $tournaments;
	}

     if($cmd == "squad_detail"){
			$match_type = textf($match_type);

			$tournamentid = isset($_GET['tournamentid'])?$_GET['tournamentid']:0;
		$team_id = isset($_GET['teamid'])?$_GET['teamid']:0;
		if($tournamentid){


		$teams = $DB->select("
			select
			e.tournament,
			e.stadium,
			e.match_type,
			tt.teamid,
			stadium.name as stadium_name,
			stadium.city as stadium_city,
			stadium.city as stadium_city,
			
                        (SELECT NAME FROM systemdata sd WHERE systemdata_type = 'country' AND id = (SELECT country FROM participant tc WHERE tc.participant_type = 'tournament' AND tc.id = e.tournament)) AS country_name,

			team.name as team_name,
			team.history as team_history

			from event as e

				JOIN tournament_team as tt on (tt.tourid = e.tournament)

				JOIN systemdatac as stadium on (stadium.id = e.stadium and stadium.systemdata_type='stadium')

				JOIN participant as team on (team.id = tt.teamid and team.participant_type='team')

				

				where
				(CASE
					WHEN ($team_id != 0) THEN e.tournament = '$tournamentid' and tt.teamid =  '$team_id'

					ELSE e.tournament = '$tournamentid' END
				)
				group by tt.teamid

		");




			$stadiumid = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $teams[0][stadium]);

	$stadium_name = textf($teams[0][stadium_name]);
	$stadium_city = textf($teams[0][stadium_city]);
	$stadium_country =textf($teams[0][country_name]);
	 $team = "<tournament id= '$tournamentid' > ";
		$team .= "<stadium id=\"$stadiumid\" name=\"$stadium_name\" city=\"$stadium_city\" country=\"$stadium_country\"/>";

		foreach($teams as $team_s){
			//$tournaments .= "<team id= '$team[teamid]' name='$team[team_name]' history='$team[team_history]'>";

			$team_players = $DB->select("
            SELECT
			CONCAT(p.firstname,' ',p.lastname) as playername,p.id as player_id,
			player_type.name as player_role

				from player as p
			Join tournament_team_squad as tts On (p.id = tts.player_id and tts.team_id=$team_s[teamid] and tts.tour_id=$tournamentid)
			Join playerteam as pt On (pt.playerid = p.id)

			Join participant as prticpnt On (prticpnt.id = pt.teamid)
			left Join systemdata as player_type on (player_type.id=p.player_type and player_type.systemdata_type = 'player_type')
			

			where
				 tts.team_id = '$team_s[teamid]'

					And
				prticpnt.participant_type = 'team'

				group by p.id
			". paginationSQL($page,20) ."
			"

			);

			 // print_r($team_players);exit;
			$team_name = getNamep("team", $team_s[teamid]);
			$team .="<team id='$team_s[teamid]' name='$team_name'>";
				foreach($team_players as $team_player){
				$team .= "<player id=\"".textf($team_player['player_id'])."\" name=\"".textf($team_player['playername'])."\">";
					$team .= "<role name=\"".textf($team_player['player_role'])."\"/>";
					// $team .= "<batingstyle name='$team_player[batting_hand]'/>";
					// $team .= "<nationality name='$team_player[player_nationality]'/>";
					// $team .= "<bowlingstyle  name='$team_player[bowling_style]'/>";
					//match type test
					// if($team_s['match_type']==2||$team_s['match_type']==3 || $team_s['match_type']==5 || $team_s['match_type']==7||$team_s['match_type']==12||$team_s['match_type']==13||$team_s['match_type']==14||$team_s['match_type']==15||$team_s['match_type']==33||$team_s['match_type']==36){


					// $team .= "<statistics match_type='test'>";
						// $team .= "<matches number='$team_player[matches_test]'/>";
						// $team .= "<notOuts number='$team_player[notouts_test]'/>";
						// $team .= "<runs number='$team_player[runs_test]'/>";
						// $team .= "<highestscores number='$team_player[highestscores_test]'/>";
						// $team .= "<battingavg number='$team_player[battingavg_test]'/>";
						// $team .= "<batting_strikerate number='$team_player[batting_strikerate_test]'/>";
						// $team .= "<ballsfaced number='$team_player[ballsfaced_test]'/>";
						// $team .= "<centuries number='$team_player[centuries_test]'/>";
						// $team .= "<fifties number='$team_player[fifties_test]'/>";
						// $team .= "<fours number='$team_player[fours_test]'/>";
						// $team .= "<sixes number='$team_player[sixes_test]'/>";
						// $team .= "<catches number='$team_player[catches_test]'/>";
						// $team .= "<stumps number='$team_player[stumps_test]'/>";

					// $team .= "</statistics >";
					// }
					// if($team_s['match_type']==1|| $team_s['match_type']==23|| $team_s['match_type']==29 || $team_s['match_type']==35){
					// //match type odi
					// $team .= "<statistics match_type='odi'>";
						// $team .= "<matches number='$team_player[matches]'/>";
						// $team .= "<notOuts number='$team_player[notouts]'/>";
						// $team .= "<runs number='$team_player[runs]'/>";
						// $team .= "<highestscores number='$team_player[highestscores]'/>";
						// $team .= "<battingavg number='$team_player[battingavg]'/>";
						// $team .= "<batting_strikerate number='$team_player[batting_strikerate]'/>";
						// $team .= "<ballsfaced number='$team_player[ballsfaced]'/>";
						// $team .= "<centuries number='$team_player[centuries]'/>";
						// $team .= "<fifties number='$team_player[fifties]'/>";
						// $team .= "<fours number='$team_player[fours]'/>";
						// $team .= "<sixes number='$team_player[sixes]'/>";
						// $team .= "<catches number='$team_player[catches]'/>";
						// $team .= "<stumps number='$team_player[stumps]'/>";

					// $team .= "</statistics >";
					// }
					// if($team_s['match_type']==4 || $team_s['match_type']==6 ||$team_s['match_type']==25 || $team_s['match_type']==26 || $team_s['match_type']==27 || $team_s['match_type']==28 || $team_s['match_type']==31 || $team_s['match_type']==32){


						// // match type t20i

						// $team .= "<statistics match_type='t20i'>";
							// $team .= "<matches number='$team_player[matches_t20i]'/>";
							// $team .= "<notOuts number='$team_player[notouts_t20i]'/>";
							// $team .= "<runs number='$team_player[runs_t20i]'/>";
							// $team .= "<highestscores number='$team_player[highestscores_t20i]'/>";
							// $team .= "<battingavg number='$team_player[battingavg_t20i]'/>";
							// $team .= "<batting_strikerate number='$team_player[batting_strikerate_t20i]'/>";
							// $team .= "<ballsfaced number='$team_player[ballsfaced_t20i]'/>";
							// $team .= "<centuries number='$team_player[centuries_t20i]'/>";
							// $team .= "<fifties number='$team_player[fifties_t20i]'/>";
							// $team .= "<fours number='$team_player[fours_t20i]'/>";
							// $team .= "<sixes number='$team_player[sixes_t20i]'/>";
							// $team .= "<catches number='$team_player[catches_t20i]'/>";
							// $team .= "<stumps number='$team_player[stumps_t20i]'/>";

						// $team .= "</statistics >";


					// }


					// //match type t20

					// // $team .= "<statistics match_type='t20'>";
						// // $team .= "<matches number='$team_player[matches_t20]'/>";
						// // $team .= "<notOuts number='$team_player[notouts_t20]'/>";
						// // $team .= "<runs number='$team_player[runs_t20]'/>";
						// // $team .= "<highestscores number='$team_player[highestscores_t20]'/>";
						// // $team .= "<battingavg number='$team_player[battingavg_t20]'/>";
						// // $team .= "<batting_strikerate number='$team_player[batting_strikerate_t20]'/>";
						// // $team .= "<ballsfaced number='$team_player[ballsfaced_t20]'/>";
						// // $team .= "<centuries number='$team_player[centuries_t20]'/>";
						// // $team .= "<fifties number='$team_player[fifties_t20]'/>";
						// // $team .= "<fours number='$team_player[fours_t20]'/>";
						// // $team .= "<sixes number='$team_player[sixes_t20]'/>";
						// // $team .= "<catches number='$team_player[catches_t20]'/>";
						// // $team .= "<stumps number='$team_player[stumps_t20]'/>";

					// // $team .= "</statistics >";

					// //match type firstclass

					// // $team .= "<statistics match_type='firstclass'>";
						// // $team .= "<matches number='$team_player[matches_firstclass]'/>";
						// // $team .= "<notOuts number='$team_player[notouts_firstclass]'/>";
						// // $team .= "<runs number='$team_player[runs_firstclass]'/>";
						// // $team .= "<highestscores number='$team_player[highestscores_firstclass]'/>";
						// // $team .= "<battingavg number='$team_player[battingavg_firstclass]'/>";
						// // $team .= "<batting_strikerate number='$team_player[batting_strikerate_firstclass]'/>";
						// // $team .= "<ballsfaced number='$team_player[ballsfaced_firstclass]'/>";
						// // $team .= "<centuries number='$team_player[centuries_firstclass]'/>";
						// // $team .= "<fifties number='$team_player[fifties_firstclass]'/>";
						// // $team .= "<fours number='$team_player[fours_firstclass]'/>";
						// // $team .= "<sixes number='$team_player[sixes_firstclass]'/>";
						// // $team .= "<catches number='$team_player[catches_firstclass]'/>";
						// // $team .= "<stumps number='$team_player[stumps_firstclass]'/>";

					// // $team .= "</statistics >";

					// // match type lista

					// // $team .= "<statistics match_type='lista'>";
						// // $team .= "<matches number='$team_player[matches_lista]'/>";
						// // $team .= "<notOuts number='$team_player[notouts_lista]'/>";
						// // $team .= "<runs number='$team_player[runs_lista]'/>";
						// // $team .= "<highestscores number='$team_player[highestscores_lista]'/>";
						// // $team .= "<battingavg number='$team_player[battingavg_lista]'/>";
						// // $team .= "<batting_strikerate number='$team_player[batting_strikerate_lista]'/>";
						// // $team .= "<ballsfaced number='$team_player[ballsfaced_lista]'/>";
						// // $team .= "<centuries number='$team_player[centuries_lista]'/>";
						// // $team .= "<fifties number='$team_player[fifties_lista]'/>";
						// // $team .= "<fours number='$team_player[fours_lista]'/>";
						// // $team .= "<sixes number='$team_player[sixes_lista]'/>";
						// // $team .= "<catches number='$team_player[catches_lista]'/>";
						// // $team .= "<stumps number='$team_player[stumps_lista]'/>";

					// // $team .= "</statistics >";

				$team .= "</player>";
			}
			 $team .="</team>";


		}



		$team .="</tournament>";


	}elseif($team_id){

		$team_players = $DB->select("
            SELECT
			CONCAT(p.firstname,' ',p.lastname) as playername,p.id as player_id,
			player_type.name as player_role,
			batingstyle.name as batting_hand,
			bowlingstyle.name as bowling_style,
			nationality.name as player_nationality,

			batting_states_test.notouts as notouts_test,
			batting_states_test.highestscores as highestscores_test,
			batting_states_test.battingavg as battingavg_test,
			batting_states_test.ballsfaced as ballsfaced_test,
			batting_states_test.strikerate as batting_strikerate_test,
			batting_states_test.centuries as centuries_test,
			batting_states_test.fifties as fifties_test,
			batting_states_test.fours as fours_test,
			batting_states_test.sixes as sixes_test ,
			batting_states_test.catches as catches_test,
			batting_states_test.stumps as stumps_test,
			batting_states_test.matches as matches_test,
			batting_states_test.runs as runs_test,

			batting_states_t20i.notouts as notouts_t20i,
			batting_states_t20i.highestscores as highestscores_t20i,
			batting_states_t20i.battingavg as battingavg_t20i,
			batting_states_t20i.ballsfaced as ballsfaced_t20i,
			batting_states_t20i.strikerate as batting_strikerate_t20i,
			batting_states_t20i.centuries as centuries_t20i,
			batting_states_t20i.fifties as fifties_t20i,
			batting_states_t20i.fours as fours_t20i,
			batting_states_t20i.sixes as sixes_t20i ,
			batting_states_t20i.catches as catches_t20i,
			batting_states_t20i.stumps as stumps_t20i,
			batting_states_t20i.matches as matches_t20i,
			batting_states_t20i.runs as runs_t20i,

			batting_states_t20.notouts as notouts_t20,
			batting_states_t20.highestscores as highestscores_t20,
			batting_states_t20.battingavg as battingavg_t20,
			batting_states_t20.ballsfaced as ballsfaced_t20,
			batting_states_t20.strikerate as batting_strikerate_t20,
			batting_states_t20.centuries as centuries_t20,
			batting_states_t20.fifties as fifties_t20,
			batting_states_t20.fours as fours_t20,
			batting_states_t20.sixes as sixes_t20 ,
			batting_states_t20.catches as catches_t20,
			batting_states_t20.stumps as stumps_t20,
			batting_states_t20.matches as matches_t20,
			batting_states_t20.runs as runs_t20,

			batting_states_firstclass.notouts as notouts_firstclass,
			batting_states_firstclass.highestscores as highestscores_firstclass,
			batting_states_firstclass.battingavg as battingavg_firstclass,
			batting_states_firstclass.ballsfaced as ballsfaced_firstclass,
			batting_states_firstclass.strikerate as batting_strikerate_firstclass,
			batting_states_firstclass.centuries as centuries_firstclass,
			batting_states_firstclass.fifties as fifties_firstclass,
			batting_states_firstclass.fours as fours_firstclass,
			batting_states_firstclass.sixes as sixes_firstclass ,
			batting_states_firstclass.catches as catches_firstclass,
			batting_states_firstclass.stumps as stumps_firstclass,
			batting_states_firstclass.matches as matches_firstclass,
			batting_states_firstclass.runs as runs_firstclass,

			batting_states_lista.notouts as notouts_lista,
			batting_states_lista.highestscores as highestscores_lista,
			batting_states_lista.battingavg as battingavg_lista,
			batting_states_lista.ballsfaced as ballsfaced_lista,
			batting_states_lista.strikerate as batting_strikerate_lista,
			batting_states_lista.centuries as centuries_lista,
			batting_states_lista.fifties as fifties_lista,
			batting_states_lista.fours as fours_lista,
			batting_states_lista.sixes as sixes_lista ,
			batting_states_lista.catches as catches_lista,
			batting_states_lista.stumps as stumps_lista,
			batting_states_lista.matches as matches_lista,
			batting_states_lista.runs as runs_lista,

			batting_states.notouts,
			batting_states.highestscores,
			batting_states.battingavg,
			batting_states.ballsfaced,
			batting_states.strikerate as batting_strikerate,
			batting_states.centuries,
			batting_states.fifties,
			batting_states.fours,
			batting_states.sixes,
			batting_states.catches,
			batting_states.stumps,
			batting_states.matches,
			batting_states.runs

				from player as p

			Join playerteam as pt On (pt.playerid = p.id)

			Join participant as prticpnt On (prticpnt.id = pt.teamid)
			Join systemdata as player_type on (player_type.id=p.player_type and player_type.systemdata_type = 'player_type')
			Join systemdata as batingstyle on (batingstyle.id=p.direction and batingstyle.systemdata_type = 'batting_style')
			Join systemdata as bowlingstyle on (bowlingstyle.id=p.direction and bowlingstyle.systemdata_type = 'bowling_style')

			left Join systemdata as nationality on (nationality.id=p.country and nationality.systemdata_type = 'country')

			left Join player_batting_stats as batting_states on (batting_states.player_id=p.id and batting_states.type = 'odi' )

			left Join player_batting_stats as batting_states_test on (batting_states_test.player_id=p.id and batting_states_test.type = 'test' )

			left Join player_batting_stats as batting_states_t20i on (batting_states_t20i.player_id=p.id and batting_states_t20i.type = 't20i' )

			left Join player_batting_stats as batting_states_t20 on (batting_states_t20.player_id=p.id and batting_states_t20.type = 't20' )

			left Join player_batting_stats as batting_states_firstclass on (batting_states_firstclass.player_id=p.id and batting_states_firstclass.type = 'firstclass' )

			left Join player_batting_stats as batting_states_lista on (batting_states_lista.player_id=p.id and batting_states_lista.type = 'lista' )
			where
				 pt.teamid = '$team_id'

					And
				prticpnt.participant_type = 'team'

				group by p.id
			". paginationSQL($page,20) ."
			"

			);

		$team_name = getNamep("team", $team_id);
			$team ="<team id='$team_id' name='$team_name'>";
				foreach($team_players as $team_player){
				$team .= "<player id='$team_player[player_id]' name='$team_player[playername]'>";
					$team .= "<role name='$team_player[player_role]'/>";
					// $team .= "<batingstyle name='$team_player[batting_hand]'/>";
					// $team .= "<nationality name='$team_player[player_nationality]'/>";
					// $team .= "<bowlingstyle  name='$team_player[bowling_style]'/>";
					// //match type test
					// // if($team_s['match_type']==2||$team_s['match_type']==3 || $team_s['match_type']==5 || $team_s['match_type']==7||$team_s['match_type']==12||$team_s['match_type']==13||$team_s['match_type']==14||$team_s['match_type']==15||$team_s['match_type']==33||$team_s['match_type']==36){


					// $team .= "<statistics match_type='test'>";
						// $team .= "<matches number='$team_player[matches_test]'/>";
						// $team .= "<notOuts number='$team_player[notouts_test]'/>";
						// $team .= "<runs number='$team_player[runs_test]'/>";
						// $team .= "<highestscores number='$team_player[highestscores_test]'/>";
						// $team .= "<battingavg number='$team_player[battingavg_test]'/>";
						// $team .= "<batting_strikerate number='$team_player[batting_strikerate_test]'/>";
						// $team .= "<ballsfaced number='$team_player[ballsfaced_test]'/>";
						// $team .= "<centuries number='$team_player[centuries_test]'/>";
						// $team .= "<fifties number='$team_player[fifties_test]'/>";
						// $team .= "<fours number='$team_player[fours_test]'/>";
						// $team .= "<sixes number='$team_player[sixes_test]'/>";
						// $team .= "<catches number='$team_player[catches_test]'/>";
						// $team .= "<stumps number='$team_player[stumps_test]'/>";

					// $team .= "</statistics >";
					// // }
					// // if($team_s['match_type']==1|| $team_s['match_type']==23|| $team_s['match_type']==29 || $team_s['match_type']==35){
					// //match type odi
					// $team .= "<statistics match_type='odi'>";
						// $team .= "<matches number='$team_player[matches]'/>";
						// $team .= "<notOuts number='$team_player[notouts]'/>";
						// $team .= "<runs number='$team_player[runs]'/>";
						// $team .= "<highestscores number='$team_player[highestscores]'/>";
						// $team .= "<battingavg number='$team_player[battingavg]'/>";
						// $team .= "<batting_strikerate number='$team_player[batting_strikerate]'/>";
						// $team .= "<ballsfaced number='$team_player[ballsfaced]'/>";
						// $team .= "<centuries number='$team_player[centuries]'/>";
						// $team .= "<fifties number='$team_player[fifties]'/>";
						// $team .= "<fours number='$team_player[fours]'/>";
						// $team .= "<sixes number='$team_player[sixes]'/>";
						// $team .= "<catches number='$team_player[catches]'/>";
						// $team .= "<stumps number='$team_player[stumps]'/>";

					// $team .= "</statistics >";
					// // }
					// // if($team_s['match_type']==4 || $team_s['match_type']==6 ||$team_s['match_type']==25 || $team_s['match_type']==26 || $team_s['match_type']==27 || $team_s['match_type']==28 || $team_s['match_type']==31 || $team_s['match_type']==32){


						// // match type t20i

						// $team .= "<statistics match_type='t20i'>";
							// $team .= "<matches number='$team_player[matches_t20i]'/>";
							// $team .= "<notOuts number='$team_player[notouts_t20i]'/>";
							// $team .= "<runs number='$team_player[runs_t20i]'/>";
							// $team .= "<highestscores number='$team_player[highestscores_t20i]'/>";
							// $team .= "<battingavg number='$team_player[battingavg_t20i]'/>";
							// $team .= "<batting_strikerate number='$team_player[batting_strikerate_t20i]'/>";
							// $team .= "<ballsfaced number='$team_player[ballsfaced_t20i]'/>";
							// $team .= "<centuries number='$team_player[centuries_t20i]'/>";
							// $team .= "<fifties number='$team_player[fifties_t20i]'/>";
							// $team .= "<fours number='$team_player[fours_t20i]'/>";
							// $team .= "<sixes number='$team_player[sixes_t20i]'/>";
							// $team .= "<catches number='$team_player[catches_t20i]'/>";
							// $team .= "<stumps number='$team_player[stumps_t20i]'/>";

						// $team .= "</statistics >";


					// // }


					// //match type t20

					// $team .= "<statistics match_type='t20'>";
						// $team .= "<matches number='$team_player[matches_t20]'/>";
						// $team .= "<notOuts number='$team_player[notouts_t20]'/>";
						// $team .= "<runs number='$team_player[runs_t20]'/>";
						// $team .= "<highestscores number='$team_player[highestscores_t20]'/>";
						// $team .= "<battingavg number='$team_player[battingavg_t20]'/>";
						// $team .= "<batting_strikerate number='$team_player[batting_strikerate_t20]'/>";
						// $team .= "<ballsfaced number='$team_player[ballsfaced_t20]'/>";
						// $team .= "<centuries number='$team_player[centuries_t20]'/>";
						// $team .= "<fifties number='$team_player[fifties_t20]'/>";
						// $team .= "<fours number='$team_player[fours_t20]'/>";
						// $team .= "<sixes number='$team_player[sixes_t20]'/>";
						// $team .= "<catches number='$team_player[catches_t20]'/>";
						// $team .= "<stumps number='$team_player[stumps_t20]'/>";

					// $team .= "</statistics >";

					// //match type firstclass

					// $team .= "<statistics match_type='firstclass'>";
						// $team .= "<matches number='$team_player[matches_firstclass]'/>";
						// $team .= "<notOuts number='$team_player[notouts_firstclass]'/>";
						// $team .= "<runs number='$team_player[runs_firstclass]'/>";
						// $team .= "<highestscores number='$team_player[highestscores_firstclass]'/>";
						// $team .= "<battingavg number='$team_player[battingavg_firstclass]'/>";
						// $team .= "<batting_strikerate number='$team_player[batting_strikerate_firstclass]'/>";
						// $team .= "<ballsfaced number='$team_player[ballsfaced_firstclass]'/>";
						// $team .= "<centuries number='$team_player[centuries_firstclass]'/>";
						// $team .= "<fifties number='$team_player[fifties_firstclass]'/>";
						// $team .= "<fours number='$team_player[fours_firstclass]'/>";
						// $team .= "<sixes number='$team_player[sixes_firstclass]'/>";
						// $team .= "<catches number='$team_player[catches_firstclass]'/>";
						// $team .= "<stumps number='$team_player[stumps_firstclass]'/>";

					// $team .= "</statistics >";

					// // match type lista

					// $team .= "<statistics match_type='lista'>";
						// $team .= "<matches number='$team_player[matches_lista]'/>";
						// $team .= "<notOuts number='$team_player[notouts_lista]'/>";
						// $team .= "<runs number='$team_player[runs_lista]'/>";
						// $team .= "<highestscores number='$team_player[highestscores_lista]'/>";
						// $team .= "<battingavg number='$team_player[battingavg_lista]'/>";
						// $team .= "<batting_strikerate number='$team_player[batting_strikerate_lista]'/>";
						// $team .= "<ballsfaced number='$team_player[ballsfaced_lista]'/>";
						// $team .= "<centuries number='$team_player[centuries_lista]'/>";
						// $team .= "<fifties number='$team_player[fifties_lista]'/>";
						// $team .= "<fours number='$team_player[fours_lista]'/>";
						// $team .= "<sixes number='$team_player[sixes_lista]'/>";
						// $team .= "<catches number='$team_player[catches_lista]'/>";
						// $team .= "<stumps number='$team_player[stumps_lista]'/>";

					// $team .= "</statistics >";

				$team .= "</player>";
			}
			 $team .="</team>";


	}




		echo $team;

		//echo "<player data='sohail' />";

	}
		//sohail changes
		else
		    if($cmd == "player_data"){
					$match_type = textf($match_type);

					$tournamentid = isset($_GET['tournamentid'])?$_GET['tournamentid']:0;
				$team_id = isset($_GET['teamid'])?$_GET['teamid']:0;
				if($tournamentid){


				$teams = $DB->select("
					select
					e.tournament,
					e.stadium,
					e.match_type,
					tt.teamid,
					stadium.name as stadium_name,
					stadium.city as stadium_city,
					stadium.city as stadium_city,
					country.name as country_name,

					team.name as team_name,
					team.history as team_history

					from event as e

						left Join tournament_team as tt on (tt.tourid = e.tournament)

						left Join systemdatac as stadium on (stadium.id = e.stadium and stadium.systemdata_type='stadium')

						left Join participant as team on (team.id = tt.teamid and team.participant_type='team')

						left Join participant as country on (country.country = stadium.country and country.participant_type='team')

						where
						(CASE
							WHEN ($team_id != 0) THEN e.tournament = '$tournamentid' and tt.teamid =  '$team_id'

							ELSE e.tournament = '$tournamentid' END
						)
						group by tt.teamid

				");




					$stadiumid = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $teams[0][stadium]);

			$stadium_name = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $teams[0][stadium_name]);
			$stadium_city = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $teams[0][stadium_city]);
			$stadium_country = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $teams[0][country_name]);
			 $team = "<tournament id= \"".$tournamentid."\" > ";
				$team .= "<stadium id= \"".$stadiumid."\" name=\"".$stadium_name."\" city=\"".$stadium_city."\" country=\"".$stadium_country."\"/>";

				foreach($teams as $team_s){
					//$tournaments .= "<team id= '$team[teamid]' name='$team[team_name]' history='$team[team_history]'>";

					$team_players = $DB->select("
		            SELECT
					CONCAT(p.firstname,' ',p.lastname) as playername,p.id as player_id,
					player_type.name as player_role,
					batingstyle.name as batting_hand,
					bowlingstyle.name as bowling_style,
					nationality.name as player_nationality,

					batting_states_test.notouts as notouts_test,
					batting_states_test.highestscores as highestscores_test,
					batting_states_test.battingavg as battingavg_test,
					batting_states_test.ballsfaced as ballsfaced_test,
					batting_states_test.strikerate as batting_strikerate_test,
					batting_states_test.centuries as centuries_test,
					batting_states_test.fifties as fifties_test,
					batting_states_test.fours as fours_test,
					batting_states_test.sixes as sixes_test ,
					batting_states_test.catches as catches_test,
					batting_states_test.stumps as stumps_test,
					batting_states_test.matches as matches_test,
					batting_states_test.runs as runs_test,

					batting_states_t20i.notouts as notouts_t20i,
					batting_states_t20i.highestscores as highestscores_t20i,
					batting_states_t20i.battingavg as battingavg_t20i,
					batting_states_t20i.ballsfaced as ballsfaced_t20i,
					batting_states_t20i.strikerate as batting_strikerate_t20i,
					batting_states_t20i.centuries as centuries_t20i,
					batting_states_t20i.fifties as fifties_t20i,
					batting_states_t20i.fours as fours_t20i,
					batting_states_t20i.sixes as sixes_t20i ,
					batting_states_t20i.catches as catches_t20i,
					batting_states_t20i.stumps as stumps_t20i,
					batting_states_t20i.matches as matches_t20i,
					batting_states_t20i.runs as runs_t20i,

					batting_states_t20.notouts as notouts_t20,
					batting_states_t20.highestscores as highestscores_t20,
					batting_states_t20.battingavg as battingavg_t20,
					batting_states_t20.ballsfaced as ballsfaced_t20,
					batting_states_t20.strikerate as batting_strikerate_t20,
					batting_states_t20.centuries as centuries_t20,
					batting_states_t20.fifties as fifties_t20,
					batting_states_t20.fours as fours_t20,
					batting_states_t20.sixes as sixes_t20 ,
					batting_states_t20.catches as catches_t20,
					batting_states_t20.stumps as stumps_t20,
					batting_states_t20.matches as matches_t20,
					batting_states_t20.runs as runs_t20,

					batting_states_firstclass.notouts as notouts_firstclass,
					batting_states_firstclass.highestscores as highestscores_firstclass,
					batting_states_firstclass.battingavg as battingavg_firstclass,
					batting_states_firstclass.ballsfaced as ballsfaced_firstclass,
					batting_states_firstclass.strikerate as batting_strikerate_firstclass,
					batting_states_firstclass.centuries as centuries_firstclass,
					batting_states_firstclass.fifties as fifties_firstclass,
					batting_states_firstclass.fours as fours_firstclass,
					batting_states_firstclass.sixes as sixes_firstclass ,
					batting_states_firstclass.catches as catches_firstclass,
					batting_states_firstclass.stumps as stumps_firstclass,
					batting_states_firstclass.matches as matches_firstclass,
					batting_states_firstclass.runs as runs_firstclass,

					batting_states_lista.notouts as notouts_lista,
					batting_states_lista.highestscores as highestscores_lista,
					batting_states_lista.battingavg as battingavg_lista,
					batting_states_lista.ballsfaced as ballsfaced_lista,
					batting_states_lista.strikerate as batting_strikerate_lista,
					batting_states_lista.centuries as centuries_lista,
					batting_states_lista.fifties as fifties_lista,
					batting_states_lista.fours as fours_lista,
					batting_states_lista.sixes as sixes_lista ,
					batting_states_lista.catches as catches_lista,
					batting_states_lista.stumps as stumps_lista,
					batting_states_lista.matches as matches_lista,
					batting_states_lista.runs as runs_lista,

					batting_states.notouts,
					batting_states.highestscores,
					batting_states.battingavg,
					batting_states.ballsfaced,
					batting_states.strikerate as batting_strikerate,
					batting_states.centuries,
					batting_states.fifties,
					batting_states.fours,
					batting_states.sixes,
					batting_states.catches,
					batting_states.stumps,
					batting_states.matches,
					batting_states.runs

						from player as p
					Join tournament_team_squad as tts On (p.id = tts.player_id and tts.team_id=$team_s[teamid] and tts.tour_id=$tournamentid)
					Join playerteam as pt On (pt.playerid = p.id)

					Join participant as prticpnt On (prticpnt.id = pt.teamid)
					Join systemdata as player_type on (player_type.id=p.player_type and player_type.systemdata_type = 'player_type')
					Join systemdata as batingstyle on (batingstyle.id=p.direction and batingstyle.systemdata_type = 'batting_style')
					Join systemdata as bowlingstyle on (bowlingstyle.id=p.direction and bowlingstyle.systemdata_type = 'bowling_style')

					left Join systemdata as nationality on (nationality.id=p.country and nationality.systemdata_type = 'country')

					left Join player_batting_stats as batting_states on (batting_states.player_id=p.id and batting_states.type = 'odi' )

					left Join player_batting_stats as batting_states_test on (batting_states_test.player_id=p.id and batting_states_test.type = 'test' )

					left Join player_batting_stats as batting_states_t20i on (batting_states_t20i.player_id=p.id and batting_states_t20i.type = 't20i' )

					left Join player_batting_stats as batting_states_t20 on (batting_states_t20.player_id=p.id and batting_states_t20.type = 't20' )

					left Join player_batting_stats as batting_states_firstclass on (batting_states_firstclass.player_id=p.id and batting_states_firstclass.type = 'firstclass' )

					left Join player_batting_stats as batting_states_lista on (batting_states_lista.player_id=p.id and batting_states_lista.type = 'lista' )
					where
						 tts.team_id = '$team_s[teamid]'

							And
						prticpnt.participant_type = 'team'

						group by p.id
					". paginationSQL($page,20) ."
					"

					);

					 // print_r($team_players);exit;
					$team_name = getNamep("team", $team_s[teamid]);
					$team .="<team id=\"".$team_s[teamid]."\" name=\"".$team_name."\">";
						foreach($team_players as $team_player){
						$team .= "<player id=\"".$team_player[player_id]."\" name=\"".$team_player[playername]."\">";
							$team .= "<role name=\"".$team_player[player_role]."\"/>";
							$team .= "<batingstyle name=\"".$team_player[batting_hand]."\"/>";
							$team .= "<nationality name=\"".$team_player[player_nationality]."\"/>";
							$team .= "<bowlingstyle  name=\"".$team_player[bowling_style]."\"/>";

								$playerid = $team_player['player_id'];
								$teamid = $team_s['teamid'];
						//-----------------------------------------------------------


							$most_runs = $DB->select("
									SELECT ev_p.id , ev.tournament,ev.id as eventid, ev_p.playerid, sum(ev_bat.score) as score, ev_bat.event_playerFK
									 from event as ev
									 left Join event_playerex as ev_p on(ev.id = ev_p.eventid)
									 left Join event_batsman as ev_bat on(ev_bat.event_playerFK = ev_p.id AND ev_bat.super_over = 0)
									 where ev.tournament = '$tournamentid'
									 and  ev_p.teamid = '$teamid'
									 and ev_p.playerid = '$playerid'


							");


							$no_outs = $DB->select("
									SELECT ev_p.id , ev.tournament,ev.id as eventid, ev_p.playerid, count(ev_bat.wicket_type) as outs, ev_bat.event_playerFK
									 from event as ev
									 left Join event_playerex as ev_p on(ev.id = ev_p.eventid)
									 left Join event_batsman as ev_bat on(ev_bat.event_playerFK = ev_p.id)
									 where ev.tournament = '$tournamentid'
									 and  ev_p.teamid = '$teamid'
									 and ev_p.playerid = '$playerid'
									 and ev_bat.wicket_type != 0
									 ");

							$no_match = $DB->select("
									SELECT ev_p.id , ev.tournament,ev.id as eventid, ev_p.playerid, count(ev_bat.inning) as matches, ev_bat.event_playerFK
									 from event as ev
									 left Join event_playerex as ev_p on(ev.id = ev_p.eventid)
									 left Join event_batsman as ev_bat on(ev_bat.event_playerFK = ev_p.id)
									 where ev.tournament = '$tournamentid'
									 and  ev_p.teamid = '$teamid'
									 and ev_p.playerid = '$playerid'
									 and ev_bat.inning = 1
									 ");

							$no_balls = $DB->select("
									SELECT ev_p.id , ev.tournament,ev.id as eventid, ev_p.playerid, sum(ev_bat.balls) as balls, ev_bat.event_playerFK
									 from event as ev
									 left Join event_playerex as ev_p on(ev.id = ev_p.eventid)
									 left Join event_batsman as ev_bat on(ev_bat.event_playerFK = ev_p.id)
									 where ev.tournament = '$tournamentid'
									 and  ev_p.teamid = '$teamid'
									 and ev_p.playerid = '$playerid'

									 ");

							$no_fours = $DB->select("
									SELECT ev_p.id , ev.tournament,ev.id as eventid, ev_p.playerid, sum(ev_bat.s4) as fours, ev_bat.event_playerFK
									 from event as ev
									 left Join event_playerex as ev_p on(ev.id = ev_p.eventid)
									 left Join event_batsman as ev_bat on(ev_bat.event_playerFK = ev_p.id)
									 where ev.tournament = '$tournamentid'
									 and  ev_p.teamid = '$teamid'
									 and ev_p.playerid = '$playerid'

									 ");

							$no_six = $DB->select("
									SELECT ev_p.id , ev.tournament,ev.id as eventid, ev_p.playerid, sum(ev_bat.s6) as sixes, ev_bat.event_playerFK
									 from event as ev
									 left Join event_playerex as ev_p on(ev.id = ev_p.eventid)
									 left Join event_batsman as ev_bat on(ev_bat.event_playerFK = ev_p.id)
									 where ev.tournament = '$tournamentid'
									 and  ev_p.teamid = '$teamid'
									 and ev_p.playerid = '$playerid'

									 ");

							$no_fifties = $DB->select("
									SELECT ev_p.id , ev.tournament,ev.id as eventid, ev_p.playerid, count(ev_bat.score) as fifties, ev_bat.event_playerFK
									 from event as ev
									 left Join event_playerex as ev_p on(ev.id = ev_p.eventid)
									 left Join event_batsman as ev_bat on(ev_bat.event_playerFK = ev_p.id)
									 where ev.tournament = '$tournamentid'
									 and  ev_p.teamid = '$teamid'
									 and ev_p.playerid = '$playerid'
									 and ev_bat.score >= 50 and ev_bat.score < 100

									 ");

							$most_hundreds = $DB->select("
									SELECT ev_p.id , ev.tournament,ev.id as eventid, ev_p.playerid, count(ev_bat.score) as hundreds, ev_bat.event_playerFK
									 from event as ev
									 left Join event_playerex as ev_p on(ev.id = ev_p.eventid)
									 left Join event_batsman as ev_bat on(ev_bat.event_playerFK = ev_p.id)
									 where ev.tournament = '$tournamentid'
									 and  ev_p.teamid = '$teamid'
									 and ev_p.playerid = '$playerid'
									 and ev_bat.score >= 100

									 ");



							$highest_score = $DB->select("
									SELECT ev_p.id , ev.tournament,ev.id as eventid, ev_p.playerid, max(ev_bat.score) as score, ev_bat.event_playerFK
									 from event as ev
									 left Join event_playerex as ev_p on(ev.id = ev_p.eventid)
									 left Join event_batsman as ev_bat on(ev_bat.event_playerFK = ev_p.id)
									 where ev.tournament = '$tournamentid'
									 and  ev_p.teamid = '$teamid'
									 and ev_p.playerid = '$playerid'


									 ");

							$catchouts = $DB->select("
									SELECT ev_p.id , ev.tournament,ev.id as eventid, ev_p.playerid, count(ev_bat.wicket_type) as catchouts, ev_bat.event_playerFK
									 from event as ev
									 left Join event_playerex as ev_p on(ev.id = ev_p.eventid)
									 left Join event_batsman as ev_bat on(ev_bat.event_playerFK = ev_p.id)
									 where ev.tournament = '$tournamentid'
									 and  ev_p.teamid = '$teamid'
									 and ev_p.playerid = '$playerid'
									 and ev_bat.wicket_type = 3

									 ");



							$stumps = $DB->select("
									SELECT ev_p.id , ev.tournament,ev.id as eventid, ev_p.playerid, count(ev_bat.wicket_type) as stumps, ev_bat.event_playerFK
									 from event as ev
									 left Join event_playerex as ev_p on(ev.id = ev_p.eventid)
									 left Join event_batsman as ev_bat on(ev_bat.event_playerFK = ev_p.id)
									 where ev.tournament = '$tournamentid'
									 and  ev_p.teamid = '$teamid'
									 and ev_p.playerid = '$playerid'
									 and ev_bat.wicket_type = 1

									 ");
							$notouts = $DB->select("
									SELECT ev_p.id , ev.tournament,ev.id as eventid, ev_p.playerid, count(ev_bat.wicket_type) as notouts, ev_bat.event_playerFK
									 from event as ev
									 left Join event_playerex as ev_p on(ev.id = ev_p.eventid)
									 left Join event_batsman as ev_bat on(ev_bat.event_playerFK = ev_p.id)
									 where ev.tournament = '$tournamentid'
									 and  ev_p.teamid = '$teamid'
									 and ev_p.playerid = '$playerid'
									 and ev_bat.wicket_type = 0

									 ");




			       //---------------------------------------------------

						//-----------------------------------------------------------
			        // $most_runs = $DB->select("
			        //     SELECT
			        //     * from event_batsman where event_playerFK = '' and
			        // "); //leading runs for a tournament
			       //---------------------------------------------------
						 $team .= "<statistics>";
						 $matches = $no_match[0]["matches"]?$no_match[0]["matches"]:0;
						 $notOuts = $notouts[0]["notouts"]?$notouts[0]["notouts"]:0;
						 $runs = $most_runs[0]["score"]?$most_runs[0]["score"]:0;
						 $highestscores = $highest_score[0]["score"]?$highest_score[0]["score"]:0;
						 $ballsfaced = $no_balls[0]["balls"]?$no_balls[0]["balls"]:0;

						 $battingavg = ($matches >0 )?$runs/$matches:0;
						 $batting_strikerate = ($ballsfaced>0)?($runs/$ballsfaced)*100:0;
						 $centuries = $most_hundreds[0]["hundreds"]?$most_hundreds[0]["hundreds"]:0;
						 $fifties = $no_fifties[0]["fifties"]?$no_fifties[0]["fifties"]:0;
						 $fours = $no_fours[0]["fours"]?$no_fours[0]["fours"]:0;
						 $sixes = $no_six[0]["sixes"]?$no_six[0]["sixes"]:0;
						 $stumps = $stumps[0]["stumps"]?$stumps[0]["stumps"]:0;
						 $catches = $catchouts[0]["catchouts"]?$catchouts[0]["catchouts"]:0;
						 	$team .= "<matches number=\"".$matches."\"/>";

								$team .= "<notOuts number=\"".$notOuts."\"/>";
								$team .= "<runs number=\"".$runs."\"/>";
								$team .= "<highestscores number=\"".$highestscores."\"/>";
								$team .= "<battingavg number=\"".$battingavg."\"/>";
								$team .= "<batting_strikerate number=\"".$batting_strikerate."\"/>";
								$team .= "<ballsfaced number=\"".$ballsfaced."\"/>";
								$team .= "<centuries number=\"".$centuries."\"/>";
								$team .= "<fifties number=\"".$fifties."\"/>";
								$team .= "<fours number=\"".$fours."\"/>";
								$team .= "<sixes number=\"".$sixes."\"/>";
								$team .= "<catches number=\"".$catches."\"/>";
								$team .= "<stumps number=\"".$stumps."\"/>";


						 $team .= "</statistics>";


							//match type test


						$team .= "</player>";
					}
					 $team .="</team>";


				}



				$team .="</tournament>";


			}
			elseif($team_id){

				$team_players = $DB->select("
		            SELECT
					CONCAT(p.firstname,' ',p.lastname) as playername,p.id as player_id,
					player_type.name as player_role,
					batingstyle.name as batting_hand,
					bowlingstyle.name as bowling_style,
					nationality.name as player_nationality,

					batting_states_test.notouts as notouts_test,
					batting_states_test.highestscores as highestscores_test,
					batting_states_test.battingavg as battingavg_test,
					batting_states_test.ballsfaced as ballsfaced_test,
					batting_states_test.strikerate as batting_strikerate_test,
					batting_states_test.centuries as centuries_test,
					batting_states_test.fifties as fifties_test,
					batting_states_test.fours as fours_test,
					batting_states_test.sixes as sixes_test ,
					batting_states_test.catches as catches_test,
					batting_states_test.stumps as stumps_test,
					batting_states_test.matches as matches_test,
					batting_states_test.runs as runs_test,

					batting_states_t20i.notouts as notouts_t20i,
					batting_states_t20i.highestscores as highestscores_t20i,
					batting_states_t20i.battingavg as battingavg_t20i,
					batting_states_t20i.ballsfaced as ballsfaced_t20i,
					batting_states_t20i.strikerate as batting_strikerate_t20i,
					batting_states_t20i.centuries as centuries_t20i,
					batting_states_t20i.fifties as fifties_t20i,
					batting_states_t20i.fours as fours_t20i,
					batting_states_t20i.sixes as sixes_t20i ,
					batting_states_t20i.catches as catches_t20i,
					batting_states_t20i.stumps as stumps_t20i,
					batting_states_t20i.matches as matches_t20i,
					batting_states_t20i.runs as runs_t20i,

					batting_states_t20.notouts as notouts_t20,
					batting_states_t20.highestscores as highestscores_t20,
					batting_states_t20.battingavg as battingavg_t20,
					batting_states_t20.ballsfaced as ballsfaced_t20,
					batting_states_t20.strikerate as batting_strikerate_t20,
					batting_states_t20.centuries as centuries_t20,
					batting_states_t20.fifties as fifties_t20,
					batting_states_t20.fours as fours_t20,
					batting_states_t20.sixes as sixes_t20 ,
					batting_states_t20.catches as catches_t20,
					batting_states_t20.stumps as stumps_t20,
					batting_states_t20.matches as matches_t20,
					batting_states_t20.runs as runs_t20,

					batting_states_firstclass.notouts as notouts_firstclass,
					batting_states_firstclass.highestscores as highestscores_firstclass,
					batting_states_firstclass.battingavg as battingavg_firstclass,
					batting_states_firstclass.ballsfaced as ballsfaced_firstclass,
					batting_states_firstclass.strikerate as batting_strikerate_firstclass,
					batting_states_firstclass.centuries as centuries_firstclass,
					batting_states_firstclass.fifties as fifties_firstclass,
					batting_states_firstclass.fours as fours_firstclass,
					batting_states_firstclass.sixes as sixes_firstclass ,
					batting_states_firstclass.catches as catches_firstclass,
					batting_states_firstclass.stumps as stumps_firstclass,
					batting_states_firstclass.matches as matches_firstclass,
					batting_states_firstclass.runs as runs_firstclass,

					batting_states_lista.notouts as notouts_lista,
					batting_states_lista.highestscores as highestscores_lista,
					batting_states_lista.battingavg as battingavg_lista,
					batting_states_lista.ballsfaced as ballsfaced_lista,
					batting_states_lista.strikerate as batting_strikerate_lista,
					batting_states_lista.centuries as centuries_lista,
					batting_states_lista.fifties as fifties_lista,
					batting_states_lista.fours as fours_lista,
					batting_states_lista.sixes as sixes_lista ,
					batting_states_lista.catches as catches_lista,
					batting_states_lista.stumps as stumps_lista,
					batting_states_lista.matches as matches_lista,
					batting_states_lista.runs as runs_lista,

					batting_states.notouts,
					batting_states.highestscores,
					batting_states.battingavg,
					batting_states.ballsfaced,
					batting_states.strikerate as batting_strikerate,
					batting_states.centuries,
					batting_states.fifties,
					batting_states.fours,
					batting_states.sixes,
					batting_states.catches,
					batting_states.stumps,
					batting_states.matches,
					batting_states.runs

						from player as p

					Join playerteam as pt On (pt.playerid = p.id)

					Join participant as prticpnt On (prticpnt.id = pt.teamid)
					Join systemdata as player_type on (player_type.id=p.player_type and player_type.systemdata_type = 'player_type')
					Join systemdata as batingstyle on (batingstyle.id=p.direction and batingstyle.systemdata_type = 'batting_style')
					Join systemdata as bowlingstyle on (bowlingstyle.id=p.direction and bowlingstyle.systemdata_type = 'bowling_style')

					left Join systemdata as nationality on (nationality.id=p.country and nationality.systemdata_type = 'country')

					left Join player_batting_stats as batting_states on (batting_states.player_id=p.id and batting_states.type = 'odi' )

					left Join player_batting_stats as batting_states_test on (batting_states_test.player_id=p.id and batting_states_test.type = 'test' )

					left Join player_batting_stats as batting_states_t20i on (batting_states_t20i.player_id=p.id and batting_states_t20i.type = 't20i' )

					left Join player_batting_stats as batting_states_t20 on (batting_states_t20.player_id=p.id and batting_states_t20.type = 't20' )

					left Join player_batting_stats as batting_states_firstclass on (batting_states_firstclass.player_id=p.id and batting_states_firstclass.type = 'firstclass' )

					left Join player_batting_stats as batting_states_lista on (batting_states_lista.player_id=p.id and batting_states_lista.type = 'lista' )
					where
						 pt.teamid = '$team_id'

							And
						prticpnt.participant_type = 'team'

						group by p.id
					". paginationSQL($page,20) ."
					"

					);

				$team_name = getNamep("team", $team_id);
					$team ="<team id='$team_id' name='$team_name'>";
						foreach($team_players as $team_player){
						$team .= "<player id='$team_player[player_id]' name='$team_player[playername]'>";
							$team .= "<role name='$team_player[player_role]'/>";
							$team .= "<batingstyle name='$team_player[batting_hand]'/>";
							$team .= "<nationality name='$team_player[player_nationality]'/>";
							$team .= "<bowlingstyle  name='$team_player[bowling_style]'/>";
							//match type test
							// if($team_s['match_type']==2||$team_s['match_type']==3 || $team_s['match_type']==5 || $team_s['match_type']==7||$team_s['match_type']==12||$team_s['match_type']==13||$team_s['match_type']==14||$team_s['match_type']==15||$team_s['match_type']==33||$team_s['match_type']==36){


							$team .= "<statistics match_type='test'>";
								$team .= "<matches number='$team_player[matches_test]'/>";
								$team .= "<notOuts number='$team_player[notouts_test]'/>";
								$team .= "<runs number='$team_player[runs_test]'/>";
								$team .= "<highestscores number='$team_player[highestscores_test]'/>";
								$team .= "<battingavg number='$team_player[battingavg_test]'/>";
								$team .= "<batting_strikerate number='$team_player[batting_strikerate_test]'/>";
								$team .= "<ballsfaced number='$team_player[ballsfaced_test]'/>";
								$team .= "<centuries number='$team_player[centuries_test]'/>";
								$team .= "<fifties number='$team_player[fifties_test]'/>";
								$team .= "<fours number='$team_player[fours_test]'/>";
								$team .= "<sixes number='$team_player[sixes_test]'/>";
								$team .= "<catches number='$team_player[catches_test]'/>";
								$team .= "<stumps number='$team_player[stumps_test]'/>";

							$team .= "</statistics >";
							// }
							// if($team_s['match_type']==1|| $team_s['match_type']==23|| $team_s['match_type']==29 || $team_s['match_type']==35){
							//match type odi
							$team .= "<statistics match_type='odi'>";
								$team .= "<matches number='$team_player[matches]'/>";
								$team .= "<notOuts number='$team_player[notouts]'/>";
								$team .= "<runs number='$team_player[runs]'/>";
								$team .= "<highestscores number='$team_player[highestscores]'/>";
								$team .= "<battingavg number='$team_player[battingavg]'/>";
								$team .= "<batting_strikerate number='$team_player[batting_strikerate]'/>";
								$team .= "<ballsfaced number='$team_player[ballsfaced]'/>";
								$team .= "<centuries number='$team_player[centuries]'/>";
								$team .= "<fifties number='$team_player[fifties]'/>";
								$team .= "<fours number='$team_player[fours]'/>";
								$team .= "<sixes number='$team_player[sixes]'/>";
								$team .= "<catches number='$team_player[catches]'/>";
								$team .= "<stumps number='$team_player[stumps]'/>";

							$team .= "</statistics >";
							// }
							// if($team_s['match_type']==4 || $team_s['match_type']==6 ||$team_s['match_type']==25 || $team_s['match_type']==26 || $team_s['match_type']==27 || $team_s['match_type']==28 || $team_s['match_type']==31 || $team_s['match_type']==32){


								// match type t20i

								$team .= "<statistics match_type='t20i'>";
									$team .= "<matches number='$team_player[matches_t20i]'/>";
									$team .= "<notOuts number='$team_player[notouts_t20i]'/>";
									$team .= "<runs number='$team_player[runs_t20i]'/>";
									$team .= "<highestscores number='$team_player[highestscores_t20i]'/>";
									$team .= "<battingavg number='$team_player[battingavg_t20i]'/>";
									$team .= "<batting_strikerate number='$team_player[batting_strikerate_t20i]'/>";
									$team .= "<ballsfaced number='$team_player[ballsfaced_t20i]'/>";
									$team .= "<centuries number='$team_player[centuries_t20i]'/>";
									$team .= "<fifties number='$team_player[fifties_t20i]'/>";
									$team .= "<fours number='$team_player[fours_t20i]'/>";
									$team .= "<sixes number='$team_player[sixes_t20i]'/>";
									$team .= "<catches number='$team_player[catches_t20i]'/>";
									$team .= "<stumps number='$team_player[stumps_t20i]'/>";

								$team .= "</statistics >";


							// }


							//match type t20

							$team .= "<statistics match_type='t20'>";
								$team .= "<matches number='$team_player[matches_t20]'/>";
								$team .= "<notOuts number='$team_player[notouts_t20]'/>";
								$team .= "<runs number='$team_player[runs_t20]'/>";
								$team .= "<highestscores number='$team_player[highestscores_t20]'/>";
								$team .= "<battingavg number='$team_player[battingavg_t20]'/>";
								$team .= "<batting_strikerate number='$team_player[batting_strikerate_t20]'/>";
								$team .= "<ballsfaced number='$team_player[ballsfaced_t20]'/>";
								$team .= "<centuries number='$team_player[centuries_t20]'/>";
								$team .= "<fifties number='$team_player[fifties_t20]'/>";
								$team .= "<fours number='$team_player[fours_t20]'/>";
								$team .= "<sixes number='$team_player[sixes_t20]'/>";
								$team .= "<catches number='$team_player[catches_t20]'/>";
								$team .= "<stumps number='$team_player[stumps_t20]'/>";

							$team .= "</statistics >";

							//match type firstclass

							$team .= "<statistics match_type='firstclass'>";
								$team .= "<matches number='$team_player[matches_firstclass]'/>";
								$team .= "<notOuts number='$team_player[notouts_firstclass]'/>";
								$team .= "<runs number='$team_player[runs_firstclass]'/>";
								$team .= "<highestscores number='$team_player[highestscores_firstclass]'/>";
								$team .= "<battingavg number='$team_player[battingavg_firstclass]'/>";
								$team .= "<batting_strikerate number='$team_player[batting_strikerate_firstclass]'/>";
								$team .= "<ballsfaced number='$team_player[ballsfaced_firstclass]'/>";
								$team .= "<centuries number='$team_player[centuries_firstclass]'/>";
								$team .= "<fifties number='$team_player[fifties_firstclass]'/>";
								$team .= "<fours number='$team_player[fours_firstclass]'/>";
								$team .= "<sixes number='$team_player[sixes_firstclass]'/>";
								$team .= "<catches number='$team_player[catches_firstclass]'/>";
								$team .= "<stumps number='$team_player[stumps_firstclass]'/>";

							$team .= "</statistics >";

							// match type lista

							$team .= "<statistics match_type='lista'>";
								$team .= "<matches number='$team_player[matches_lista]'/>";
								$team .= "<notOuts number='$team_player[notouts_lista]'/>";
								$team .= "<runs number='$team_player[runs_lista]'/>";
								$team .= "<highestscores number='$team_player[highestscores_lista]'/>";
								$team .= "<battingavg number='$team_player[battingavg_lista]'/>";
								$team .= "<batting_strikerate number='$team_player[batting_strikerate_lista]'/>";
								$team .= "<ballsfaced number='$team_player[ballsfaced_lista]'/>";
								$team .= "<centuries number='$team_player[centuries_lista]'/>";
								$team .= "<fifties number='$team_player[fifties_lista]'/>";
								$team .= "<fours number='$team_player[fours_lista]'/>";
								$team .= "<sixes number='$team_player[sixes_lista]'/>";
								$team .= "<catches number='$team_player[catches_lista]'/>";
								$team .= "<stumps number='$team_player[stumps_lista]'/>";

							$team .= "</statistics >";

						$team .= "</player>";
					}
					 $team .="</team>";


			}


				echo $team;

				//echo "<player data='sohail' />";

			}
			else
				if($cmd == "team_detail_info"){
					// $page =0 ;
						$team_id = isset($_GET['teamid'])?$_GET['teamid']:0;
						$page = isset($_GET['page'])?$_GET['page']:0;
						$team = $DB->select("
								select *from participant where participant_type = 'team' and id = '$team_id'
						");
		 // echo "<pre>";print_r($team); exit;
						$team_xmll =" <team id= \"".$team_id. "\"> ";
						foreach ($team as $team_s) {
						// 	 // $history=preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $team_s["history"]);
							$history = textf($team_s["history"]);
							$stadiumid = $team_s["stadium"];
							$team_date_found = $team_s["team_date_found"];
							$std = new stadium($team_s["stadium"]);
							$std->load();
							$country = getName("country",$std->country);

								$team_xmll .=" <stadium id=\"".$stadiumid. "\" name=\"".textf($std->name)."\"  /> ";
								$team_xmll .=" <location city=\"".$std->city."\" country = \"".textf($country)."\" /> ";
								$team_xmll .=" <histrory  text=\"" . textf($history) . "\" /> ";
								$team_xmll .=" <DateFound text=\"".$team_date_found."\" /> ";
									$team_xmll .=" <tournaments>";
								$tournaments = $DB->select("
										select tournament.name as tournament_name,tournament.id as tournament_id from participant as tournament
											join tournament_team as tt on (tt.tourid = tournament.id and tournament.participant_type = 'tournament')
										 where tt.teamid = '$team_id'
										 ". paginationSQL($page,20) ."
								");
						//
								foreach ($tournaments as $tournament) {
										$tournamentid = $tournament["tournament_id"];
										$tournament_name = textf($tournament["tournament_name"]);
										$team_xmll .=" <tournament id=\"".$tournamentid."\" name=\"".$tournament_name."\" > ";
						//
										$team_players = $DB->select("
													SELECT
										CONCAT(p.firstname,' ',p.lastname) as playername,p.id as player_id,
										player_type.name as player_role,
										batingstyle.name as batting_hand,
										bowlingstyle.name as bowling_style,
										nationality.name as player_nationality


											from player as p
										LEFT JOIN tournament_team_squad as tts On (p.id = tts.player_id and tts.team_id='$team_id' and tts.tour_id='$tournamentid')
										LEFT JOIN playerteam as pt On (pt.playerid = p.id)

										LEFT JOIN participant as prticpnt On (prticpnt.id = pt.teamid)
										LEFT JOIN systemdata as player_type on (player_type.id=p.player_type and player_type.systemdata_type = 'player_type')
										LEFT JOIN systemdata as batingstyle on (batingstyle.id=p.direction and batingstyle.systemdata_type = 'batting_style')
										LEFT JOIN systemdata as bowlingstyle on (bowlingstyle.id=p.direction and bowlingstyle.systemdata_type = 'bowling_style')

										LEFT JOIN systemdata as nationality on (nationality.id=p.country and nationality.systemdata_type = 'country')


										where
											 tts.team_id = '$team_id'



											group by p.id

										"

										);
						//
										 // echo "<pre>";print_r($team_players);echo "</pre>"; exit;
						//
										$team_xmll .= "<squad>";
						// 				if(sizeof($team_players)>0){
											foreach($team_players as $team_player){
												$bowlingstyle= $team_player[bowling_style]?$team_player[bowling_style]:'';
												$nationality= $team_player[player_nationality]?$team_player[player_nationality]:'';
												$batting_hand= $team_player[batting_hand]?$team_player[batting_hand]:'';
												$player_role= $team_player[player_role]?$team_player[player_role]:'';
												$player_id= $team_player[player_id]?$team_player[player_id]:'';
												$playername= $team_player[playername]?$team_player[playername]:'';
												$team_xmll .= "<player id=\"".$player_id."\" name=\"".textf($playername)."\">";
													$team_xmll .= "<role name=\"".textf($player_role)."\"/>";
													$team_xmll .= "<batingstyle name=\"".textf($batting_hand)."\"/>";
													$team_xmll .= "<nationality name=\"".textf($nationality)."\"/>";
													$team_xmll .= "<bowlingstyle  name=\"".textf($bowlingstyle)."\"/>";
													$team_xmll .= "</player >";
											}
						// 				}
						//
										$team_xmll .= "</squad>";
						//
						// 				// $team .= "<player id='$team_player[player_id]' name='$team_player[playername]'>";
						// 				// 	$team .= "<role name='$team_player[player_role]'/>";
						// 				// 	$team .= "<batingstyle name='$team_player[batting_hand]'/>";
						// 				// 	$team .= "<nationality name='$team_player[player_nationality]'/>";
						// 				// 	$team .= "<bowlingstyle  name='$team_player[bowling_style]'/>";
						//
								$team_xmll .=" </tournament> ";
						//
						//
						//
								}
							$team_xmll .=" </tournaments>";
						//
						}
						$team_xmll .=" </team > ";

						echo $team_xmll;
				}


		//sohail changes end
     if($cmd == "player_data_old"){
			$match_type = textf($match_type);

			$tournamentid = isset($_GET['tournamentid'])?$_GET['tournamentid']:0;
		$team_id = isset($_GET['teamid'])?$_GET['teamid']:0;
		if($tournamentid){


		$teams = $DB->select("
			select
			e.tournament,
			e.stadium,
			e.match_type,
			tt.teamid,
			stadium.name as stadium_name,
			stadium.city as stadium_city,
			stadium.city as stadium_city,
			country.name as country_name,

			team.name as team_name,
			team.history as team_history

			from event as e

				JOIN tournament_team as tt on (tt.tourid = e.tournament)

				JOIN systemdatac as stadium on (stadium.id = e.stadium and stadium.systemdata_type='stadium')

				JOIN participant as team on (team.id = tt.teamid and team.participant_type='team')

				JOIN participant as country on (country.country = stadium.country and country.participant_type='team')

				where
				(CASE
					WHEN ($team_id != 0) THEN e.tournament = '$tournamentid' and tt.teamid =  '$team_id'

					ELSE e.tournament = '$tournamentid' END
				)
				group by tt.teamid

		");




			$stadiumid = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $teams[0][stadium]);

	$stadium_name = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $teams[0][stadium_name]);
	$stadium_city = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $teams[0][stadium_city]);
	$stadium_country = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $teams[0][country_name]);
	 $team = "<tournament id= '$tournamentid' > ";
		$team .= "<stadium id= '$stadiumid' name='$stadium_name' city='$stadium_city' country='$stadium_country'/>";

		foreach($teams as $team_s){
			//$tournaments .= "<team id= '$team[teamid]' name='$team[team_name]' history='$team[team_history]'>";

			$team_players = $DB->select("
            SELECT
			CONCAT(p.firstname,' ',p.lastname) as playername,p.id as player_id,
			player_type.name as player_role,
			batingstyle.name as batting_hand,
			bowlingstyle.name as bowling_style,
			nationality.name as player_nationality,

			batting_states_test.notouts as notouts_test,
			batting_states_test.highestscores as highestscores_test,
			batting_states_test.battingavg as battingavg_test,
			batting_states_test.ballsfaced as ballsfaced_test,
			batting_states_test.strikerate as batting_strikerate_test,
			batting_states_test.centuries as centuries_test,
			batting_states_test.fifties as fifties_test,
			batting_states_test.fours as fours_test,
			batting_states_test.sixes as sixes_test ,
			batting_states_test.catches as catches_test,
			batting_states_test.stumps as stumps_test,
			batting_states_test.matches as matches_test,
			batting_states_test.runs as runs_test,

			batting_states_t20i.notouts as notouts_t20i,
			batting_states_t20i.highestscores as highestscores_t20i,
			batting_states_t20i.battingavg as battingavg_t20i,
			batting_states_t20i.ballsfaced as ballsfaced_t20i,
			batting_states_t20i.strikerate as batting_strikerate_t20i,
			batting_states_t20i.centuries as centuries_t20i,
			batting_states_t20i.fifties as fifties_t20i,
			batting_states_t20i.fours as fours_t20i,
			batting_states_t20i.sixes as sixes_t20i ,
			batting_states_t20i.catches as catches_t20i,
			batting_states_t20i.stumps as stumps_t20i,
			batting_states_t20i.matches as matches_t20i,
			batting_states_t20i.runs as runs_t20i,

			batting_states_t20.notouts as notouts_t20,
			batting_states_t20.highestscores as highestscores_t20,
			batting_states_t20.battingavg as battingavg_t20,
			batting_states_t20.ballsfaced as ballsfaced_t20,
			batting_states_t20.strikerate as batting_strikerate_t20,
			batting_states_t20.centuries as centuries_t20,
			batting_states_t20.fifties as fifties_t20,
			batting_states_t20.fours as fours_t20,
			batting_states_t20.sixes as sixes_t20 ,
			batting_states_t20.catches as catches_t20,
			batting_states_t20.stumps as stumps_t20,
			batting_states_t20.matches as matches_t20,
			batting_states_t20.runs as runs_t20,

			batting_states_firstclass.notouts as notouts_firstclass,
			batting_states_firstclass.highestscores as highestscores_firstclass,
			batting_states_firstclass.battingavg as battingavg_firstclass,
			batting_states_firstclass.ballsfaced as ballsfaced_firstclass,
			batting_states_firstclass.strikerate as batting_strikerate_firstclass,
			batting_states_firstclass.centuries as centuries_firstclass,
			batting_states_firstclass.fifties as fifties_firstclass,
			batting_states_firstclass.fours as fours_firstclass,
			batting_states_firstclass.sixes as sixes_firstclass ,
			batting_states_firstclass.catches as catches_firstclass,
			batting_states_firstclass.stumps as stumps_firstclass,
			batting_states_firstclass.matches as matches_firstclass,
			batting_states_firstclass.runs as runs_firstclass,

			batting_states_lista.notouts as notouts_lista,
			batting_states_lista.highestscores as highestscores_lista,
			batting_states_lista.battingavg as battingavg_lista,
			batting_states_lista.ballsfaced as ballsfaced_lista,
			batting_states_lista.strikerate as batting_strikerate_lista,
			batting_states_lista.centuries as centuries_lista,
			batting_states_lista.fifties as fifties_lista,
			batting_states_lista.fours as fours_lista,
			batting_states_lista.sixes as sixes_lista ,
			batting_states_lista.catches as catches_lista,
			batting_states_lista.stumps as stumps_lista,
			batting_states_lista.matches as matches_lista,
			batting_states_lista.runs as runs_lista,

			batting_states.notouts,
			batting_states.highestscores,
			batting_states.battingavg,
			batting_states.ballsfaced,
			batting_states.strikerate as batting_strikerate,
			batting_states.centuries,
			batting_states.fifties,
			batting_states.fours,
			batting_states.sixes,
			batting_states.catches,
			batting_states.stumps,
			batting_states.matches,
			batting_states.runs

				from player as p
			Join tournament_team_squad as tts On (p.id = tts.player_id and tts.team_id=$team_s[teamid] and tts.tour_id=$tournamentid)
			Join playerteam as pt On (pt.playerid = p.id)

			Join participant as prticpnt On (prticpnt.id = pt.teamid)
			Join systemdata as player_type on (player_type.id=p.player_type and player_type.systemdata_type = 'player_type')
			Join systemdata as batingstyle on (batingstyle.id=p.direction and batingstyle.systemdata_type = 'batting_style')
			Join systemdata as bowlingstyle on (bowlingstyle.id=p.direction and bowlingstyle.systemdata_type = 'bowling_style')

			left Join systemdata as nationality on (nationality.id=p.country and nationality.systemdata_type = 'country')

			left Join player_batting_stats as batting_states on (batting_states.player_id=p.id and batting_states.type = 'odi' )

			left Join player_batting_stats as batting_states_test on (batting_states_test.player_id=p.id and batting_states_test.type = 'test' )

			left Join player_batting_stats as batting_states_t20i on (batting_states_t20i.player_id=p.id and batting_states_t20i.type = 't20i' )

			left Join player_batting_stats as batting_states_t20 on (batting_states_t20.player_id=p.id and batting_states_t20.type = 't20' )

			left Join player_batting_stats as batting_states_firstclass on (batting_states_firstclass.player_id=p.id and batting_states_firstclass.type = 'firstclass' )

			left Join player_batting_stats as batting_states_lista on (batting_states_lista.player_id=p.id and batting_states_lista.type = 'lista' )
			where
				 tts.team_id = '$team_s[teamid]'

					And
				prticpnt.participant_type = 'team'

				group by p.id
			". paginationSQL($page,20) ."
			"

			);

			 // print_r($team_players);exit;
			$team_name = getNamep("team", $team_s[teamid]);
			$team .="<team id='$team_s[teamid]' name='$team_name'>";
				foreach($team_players as $team_player){
				$team .= "<player id='$team_player[player_id]' name='$team_player[playername]'>";
					$team .= "<role name='$team_player[player_role]'/>";
					$team .= "<batingstyle name='$team_player[batting_hand]'/>";
					$team .= "<nationality name='$team_player[player_nationality]'/>";
					$team .= "<bowlingstyle  name='$team_player[bowling_style]'/>";
					//match type test
					if($team_s['match_type']==2||$team_s['match_type']==3 || $team_s['match_type']==5 || $team_s['match_type']==7||$team_s['match_type']==12||$team_s['match_type']==13||$team_s['match_type']==14||$team_s['match_type']==15||$team_s['match_type']==33||$team_s['match_type']==36){


					$team .= "<statistics match_type='test'>";
						$team .= "<matches number='$team_player[matches_test]'/>";
						$team .= "<notOuts number='$team_player[notouts_test]'/>";
						$team .= "<runs number='$team_player[runs_test]'/>";
						$team .= "<highestscores number='$team_player[highestscores_test]'/>";
						$team .= "<battingavg number='$team_player[battingavg_test]'/>";
						$team .= "<batting_strikerate number='$team_player[batting_strikerate_test]'/>";
						$team .= "<ballsfaced number='$team_player[ballsfaced_test]'/>";
						$team .= "<centuries number='$team_player[centuries_test]'/>";
						$team .= "<fifties number='$team_player[fifties_test]'/>";
						$team .= "<fours number='$team_player[fours_test]'/>";
						$team .= "<sixes number='$team_player[sixes_test]'/>";
						$team .= "<catches number='$team_player[catches_test]'/>";
						$team .= "<stumps number='$team_player[stumps_test]'/>";

					$team .= "</statistics >";
					}
					if($team_s['match_type']==1|| $team_s['match_type']==23|| $team_s['match_type']==29 || $team_s['match_type']==35){
					//match type odi
					$team .= "<statistics match_type='odi'>";
						$team .= "<matches number='$team_player[matches]'/>";
						$team .= "<notOuts number='$team_player[notouts]'/>";
						$team .= "<runs number='$team_player[runs]'/>";
						$team .= "<highestscores number='$team_player[highestscores]'/>";
						$team .= "<battingavg number='$team_player[battingavg]'/>";
						$team .= "<batting_strikerate number='$team_player[batting_strikerate]'/>";
						$team .= "<ballsfaced number='$team_player[ballsfaced]'/>";
						$team .= "<centuries number='$team_player[centuries]'/>";
						$team .= "<fifties number='$team_player[fifties]'/>";
						$team .= "<fours number='$team_player[fours]'/>";
						$team .= "<sixes number='$team_player[sixes]'/>";
						$team .= "<catches number='$team_player[catches]'/>";
						$team .= "<stumps number='$team_player[stumps]'/>";

					$team .= "</statistics >";
					}
					if($team_s['match_type']==4 || $team_s['match_type']==6 ||$team_s['match_type']==25 || $team_s['match_type']==26 || $team_s['match_type']==27 || $team_s['match_type']==28 || $team_s['match_type']==31 || $team_s['match_type']==32){


						// match type t20i

						$team .= "<statistics match_type='t20i'>";
							$team .= "<matches number='$team_player[matches_t20i]'/>";
							$team .= "<notOuts number='$team_player[notouts_t20i]'/>";
							$team .= "<runs number='$team_player[runs_t20i]'/>";
							$team .= "<highestscores number='$team_player[highestscores_t20i]'/>";
							$team .= "<battingavg number='$team_player[battingavg_t20i]'/>";
							$team .= "<batting_strikerate number='$team_player[batting_strikerate_t20i]'/>";
							$team .= "<ballsfaced number='$team_player[ballsfaced_t20i]'/>";
							$team .= "<centuries number='$team_player[centuries_t20i]'/>";
							$team .= "<fifties number='$team_player[fifties_t20i]'/>";
							$team .= "<fours number='$team_player[fours_t20i]'/>";
							$team .= "<sixes number='$team_player[sixes_t20i]'/>";
							$team .= "<catches number='$team_player[catches_t20i]'/>";
							$team .= "<stumps number='$team_player[stumps_t20i]'/>";

						$team .= "</statistics >";


					}


					//match type t20

					// $team .= "<statistics match_type='t20'>";
						// $team .= "<matches number='$team_player[matches_t20]'/>";
						// $team .= "<notOuts number='$team_player[notouts_t20]'/>";
						// $team .= "<runs number='$team_player[runs_t20]'/>";
						// $team .= "<highestscores number='$team_player[highestscores_t20]'/>";
						// $team .= "<battingavg number='$team_player[battingavg_t20]'/>";
						// $team .= "<batting_strikerate number='$team_player[batting_strikerate_t20]'/>";
						// $team .= "<ballsfaced number='$team_player[ballsfaced_t20]'/>";
						// $team .= "<centuries number='$team_player[centuries_t20]'/>";
						// $team .= "<fifties number='$team_player[fifties_t20]'/>";
						// $team .= "<fours number='$team_player[fours_t20]'/>";
						// $team .= "<sixes number='$team_player[sixes_t20]'/>";
						// $team .= "<catches number='$team_player[catches_t20]'/>";
						// $team .= "<stumps number='$team_player[stumps_t20]'/>";

					// $team .= "</statistics >";

					//match type firstclass

					// $team .= "<statistics match_type='firstclass'>";
						// $team .= "<matches number='$team_player[matches_firstclass]'/>";
						// $team .= "<notOuts number='$team_player[notouts_firstclass]'/>";
						// $team .= "<runs number='$team_player[runs_firstclass]'/>";
						// $team .= "<highestscores number='$team_player[highestscores_firstclass]'/>";
						// $team .= "<battingavg number='$team_player[battingavg_firstclass]'/>";
						// $team .= "<batting_strikerate number='$team_player[batting_strikerate_firstclass]'/>";
						// $team .= "<ballsfaced number='$team_player[ballsfaced_firstclass]'/>";
						// $team .= "<centuries number='$team_player[centuries_firstclass]'/>";
						// $team .= "<fifties number='$team_player[fifties_firstclass]'/>";
						// $team .= "<fours number='$team_player[fours_firstclass]'/>";
						// $team .= "<sixes number='$team_player[sixes_firstclass]'/>";
						// $team .= "<catches number='$team_player[catches_firstclass]'/>";
						// $team .= "<stumps number='$team_player[stumps_firstclass]'/>";

					// $team .= "</statistics >";

					// match type lista

					// $team .= "<statistics match_type='lista'>";
						// $team .= "<matches number='$team_player[matches_lista]'/>";
						// $team .= "<notOuts number='$team_player[notouts_lista]'/>";
						// $team .= "<runs number='$team_player[runs_lista]'/>";
						// $team .= "<highestscores number='$team_player[highestscores_lista]'/>";
						// $team .= "<battingavg number='$team_player[battingavg_lista]'/>";
						// $team .= "<batting_strikerate number='$team_player[batting_strikerate_lista]'/>";
						// $team .= "<ballsfaced number='$team_player[ballsfaced_lista]'/>";
						// $team .= "<centuries number='$team_player[centuries_lista]'/>";
						// $team .= "<fifties number='$team_player[fifties_lista]'/>";
						// $team .= "<fours number='$team_player[fours_lista]'/>";
						// $team .= "<sixes number='$team_player[sixes_lista]'/>";
						// $team .= "<catches number='$team_player[catches_lista]'/>";
						// $team .= "<stumps number='$team_player[stumps_lista]'/>";

					// $team .= "</statistics >";

				$team .= "</player>";
			}
			 $team .="</team>";


		}



		$team .="</tournament>";


	}elseif($team_id){

		$team_players = $DB->select("
            SELECT
			CONCAT(p.firstname,' ',p.lastname) as playername,p.id as player_id,
			player_type.name as player_role,
			batingstyle.name as batting_hand,
			bowlingstyle.name as bowling_style,
			nationality.name as player_nationality,

			batting_states_test.notouts as notouts_test,
			batting_states_test.highestscores as highestscores_test,
			batting_states_test.battingavg as battingavg_test,
			batting_states_test.ballsfaced as ballsfaced_test,
			batting_states_test.strikerate as batting_strikerate_test,
			batting_states_test.centuries as centuries_test,
			batting_states_test.fifties as fifties_test,
			batting_states_test.fours as fours_test,
			batting_states_test.sixes as sixes_test ,
			batting_states_test.catches as catches_test,
			batting_states_test.stumps as stumps_test,
			batting_states_test.matches as matches_test,
			batting_states_test.runs as runs_test,

			batting_states_t20i.notouts as notouts_t20i,
			batting_states_t20i.highestscores as highestscores_t20i,
			batting_states_t20i.battingavg as battingavg_t20i,
			batting_states_t20i.ballsfaced as ballsfaced_t20i,
			batting_states_t20i.strikerate as batting_strikerate_t20i,
			batting_states_t20i.centuries as centuries_t20i,
			batting_states_t20i.fifties as fifties_t20i,
			batting_states_t20i.fours as fours_t20i,
			batting_states_t20i.sixes as sixes_t20i ,
			batting_states_t20i.catches as catches_t20i,
			batting_states_t20i.stumps as stumps_t20i,
			batting_states_t20i.matches as matches_t20i,
			batting_states_t20i.runs as runs_t20i,

			batting_states_t20.notouts as notouts_t20,
			batting_states_t20.highestscores as highestscores_t20,
			batting_states_t20.battingavg as battingavg_t20,
			batting_states_t20.ballsfaced as ballsfaced_t20,
			batting_states_t20.strikerate as batting_strikerate_t20,
			batting_states_t20.centuries as centuries_t20,
			batting_states_t20.fifties as fifties_t20,
			batting_states_t20.fours as fours_t20,
			batting_states_t20.sixes as sixes_t20 ,
			batting_states_t20.catches as catches_t20,
			batting_states_t20.stumps as stumps_t20,
			batting_states_t20.matches as matches_t20,
			batting_states_t20.runs as runs_t20,

			batting_states_firstclass.notouts as notouts_firstclass,
			batting_states_firstclass.highestscores as highestscores_firstclass,
			batting_states_firstclass.battingavg as battingavg_firstclass,
			batting_states_firstclass.ballsfaced as ballsfaced_firstclass,
			batting_states_firstclass.strikerate as batting_strikerate_firstclass,
			batting_states_firstclass.centuries as centuries_firstclass,
			batting_states_firstclass.fifties as fifties_firstclass,
			batting_states_firstclass.fours as fours_firstclass,
			batting_states_firstclass.sixes as sixes_firstclass ,
			batting_states_firstclass.catches as catches_firstclass,
			batting_states_firstclass.stumps as stumps_firstclass,
			batting_states_firstclass.matches as matches_firstclass,
			batting_states_firstclass.runs as runs_firstclass,

			batting_states_lista.notouts as notouts_lista,
			batting_states_lista.highestscores as highestscores_lista,
			batting_states_lista.battingavg as battingavg_lista,
			batting_states_lista.ballsfaced as ballsfaced_lista,
			batting_states_lista.strikerate as batting_strikerate_lista,
			batting_states_lista.centuries as centuries_lista,
			batting_states_lista.fifties as fifties_lista,
			batting_states_lista.fours as fours_lista,
			batting_states_lista.sixes as sixes_lista ,
			batting_states_lista.catches as catches_lista,
			batting_states_lista.stumps as stumps_lista,
			batting_states_lista.matches as matches_lista,
			batting_states_lista.runs as runs_lista,

			batting_states.notouts,
			batting_states.highestscores,
			batting_states.battingavg,
			batting_states.ballsfaced,
			batting_states.strikerate as batting_strikerate,
			batting_states.centuries,
			batting_states.fifties,
			batting_states.fours,
			batting_states.sixes,
			batting_states.catches,
			batting_states.stumps,
			batting_states.matches,
			batting_states.runs

				from player as p

			Join playerteam as pt On (pt.playerid = p.id)

			Join participant as prticpnt On (prticpnt.id = pt.teamid)
			Join systemdata as player_type on (player_type.id=p.player_type and player_type.systemdata_type = 'player_type')
			Join systemdata as batingstyle on (batingstyle.id=p.direction and batingstyle.systemdata_type = 'batting_style')
			Join systemdata as bowlingstyle on (bowlingstyle.id=p.direction and bowlingstyle.systemdata_type = 'bowling_style')

			left Join systemdata as nationality on (nationality.id=p.country and nationality.systemdata_type = 'country')

			left Join player_batting_stats as batting_states on (batting_states.player_id=p.id and batting_states.type = 'odi' )

			left Join player_batting_stats as batting_states_test on (batting_states_test.player_id=p.id and batting_states_test.type = 'test' )

			left Join player_batting_stats as batting_states_t20i on (batting_states_t20i.player_id=p.id and batting_states_t20i.type = 't20i' )

			left Join player_batting_stats as batting_states_t20 on (batting_states_t20.player_id=p.id and batting_states_t20.type = 't20' )

			left Join player_batting_stats as batting_states_firstclass on (batting_states_firstclass.player_id=p.id and batting_states_firstclass.type = 'firstclass' )

			left Join player_batting_stats as batting_states_lista on (batting_states_lista.player_id=p.id and batting_states_lista.type = 'lista' )
			where
				 pt.teamid = '$team_id'

					And
				prticpnt.participant_type = 'team'

				group by p.id
			". paginationSQL($page,20) ."
			"

			);

		$team_name = getNamep("team", $team_id);
			$team ="<team id='$team_id' name='$team_name'>";
				foreach($team_players as $team_player){
				$team .= "<player id='$team_player[player_id]' name='$team_player[playername]'>";
					$team .= "<role name='$team_player[player_role]'/>";
					$team .= "<batingstyle name='$team_player[batting_hand]'/>";
					$team .= "<nationality name='$team_player[player_nationality]'/>";
					$team .= "<bowlingstyle  name='$team_player[bowling_style]'/>";
					//match type test
					// if($team_s['match_type']==2||$team_s['match_type']==3 || $team_s['match_type']==5 || $team_s['match_type']==7||$team_s['match_type']==12||$team_s['match_type']==13||$team_s['match_type']==14||$team_s['match_type']==15||$team_s['match_type']==33||$team_s['match_type']==36){


					$team .= "<statistics match_type='test'>";
						$team .= "<matches number='$team_player[matches_test]'/>";
						$team .= "<notOuts number='$team_player[notouts_test]'/>";
						$team .= "<runs number='$team_player[runs_test]'/>";
						$team .= "<highestscores number='$team_player[highestscores_test]'/>";
						$team .= "<battingavg number='$team_player[battingavg_test]'/>";
						$team .= "<batting_strikerate number='$team_player[batting_strikerate_test]'/>";
						$team .= "<ballsfaced number='$team_player[ballsfaced_test]'/>";
						$team .= "<centuries number='$team_player[centuries_test]'/>";
						$team .= "<fifties number='$team_player[fifties_test]'/>";
						$team .= "<fours number='$team_player[fours_test]'/>";
						$team .= "<sixes number='$team_player[sixes_test]'/>";
						$team .= "<catches number='$team_player[catches_test]'/>";
						$team .= "<stumps number='$team_player[stumps_test]'/>";

					$team .= "</statistics >";
					// }
					// if($team_s['match_type']==1|| $team_s['match_type']==23|| $team_s['match_type']==29 || $team_s['match_type']==35){
					//match type odi
					$team .= "<statistics match_type='odi'>";
						$team .= "<matches number='$team_player[matches]'/>";
						$team .= "<notOuts number='$team_player[notouts]'/>";
						$team .= "<runs number='$team_player[runs]'/>";
						$team .= "<highestscores number='$team_player[highestscores]'/>";
						$team .= "<battingavg number='$team_player[battingavg]'/>";
						$team .= "<batting_strikerate number='$team_player[batting_strikerate]'/>";
						$team .= "<ballsfaced number='$team_player[ballsfaced]'/>";
						$team .= "<centuries number='$team_player[centuries]'/>";
						$team .= "<fifties number='$team_player[fifties]'/>";
						$team .= "<fours number='$team_player[fours]'/>";
						$team .= "<sixes number='$team_player[sixes]'/>";
						$team .= "<catches number='$team_player[catches]'/>";
						$team .= "<stumps number='$team_player[stumps]'/>";

					$team .= "</statistics >";
					// }
					// if($team_s['match_type']==4 || $team_s['match_type']==6 ||$team_s['match_type']==25 || $team_s['match_type']==26 || $team_s['match_type']==27 || $team_s['match_type']==28 || $team_s['match_type']==31 || $team_s['match_type']==32){


						// match type t20i

						$team .= "<statistics match_type='t20i'>";
							$team .= "<matches number='$team_player[matches_t20i]'/>";
							$team .= "<notOuts number='$team_player[notouts_t20i]'/>";
							$team .= "<runs number='$team_player[runs_t20i]'/>";
							$team .= "<highestscores number='$team_player[highestscores_t20i]'/>";
							$team .= "<battingavg number='$team_player[battingavg_t20i]'/>";
							$team .= "<batting_strikerate number='$team_player[batting_strikerate_t20i]'/>";
							$team .= "<ballsfaced number='$team_player[ballsfaced_t20i]'/>";
							$team .= "<centuries number='$team_player[centuries_t20i]'/>";
							$team .= "<fifties number='$team_player[fifties_t20i]'/>";
							$team .= "<fours number='$team_player[fours_t20i]'/>";
							$team .= "<sixes number='$team_player[sixes_t20i]'/>";
							$team .= "<catches number='$team_player[catches_t20i]'/>";
							$team .= "<stumps number='$team_player[stumps_t20i]'/>";

						$team .= "</statistics >";


					// }


					//match type t20

					$team .= "<statistics match_type='t20'>";
						$team .= "<matches number='$team_player[matches_t20]'/>";
						$team .= "<notOuts number='$team_player[notouts_t20]'/>";
						$team .= "<runs number='$team_player[runs_t20]'/>";
						$team .= "<highestscores number='$team_player[highestscores_t20]'/>";
						$team .= "<battingavg number='$team_player[battingavg_t20]'/>";
						$team .= "<batting_strikerate number='$team_player[batting_strikerate_t20]'/>";
						$team .= "<ballsfaced number='$team_player[ballsfaced_t20]'/>";
						$team .= "<centuries number='$team_player[centuries_t20]'/>";
						$team .= "<fifties number='$team_player[fifties_t20]'/>";
						$team .= "<fours number='$team_player[fours_t20]'/>";
						$team .= "<sixes number='$team_player[sixes_t20]'/>";
						$team .= "<catches number='$team_player[catches_t20]'/>";
						$team .= "<stumps number='$team_player[stumps_t20]'/>";

					$team .= "</statistics >";

					//match type firstclass

					$team .= "<statistics match_type='firstclass'>";
						$team .= "<matches number='$team_player[matches_firstclass]'/>";
						$team .= "<notOuts number='$team_player[notouts_firstclass]'/>";
						$team .= "<runs number='$team_player[runs_firstclass]'/>";
						$team .= "<highestscores number='$team_player[highestscores_firstclass]'/>";
						$team .= "<battingavg number='$team_player[battingavg_firstclass]'/>";
						$team .= "<batting_strikerate number='$team_player[batting_strikerate_firstclass]'/>";
						$team .= "<ballsfaced number='$team_player[ballsfaced_firstclass]'/>";
						$team .= "<centuries number='$team_player[centuries_firstclass]'/>";
						$team .= "<fifties number='$team_player[fifties_firstclass]'/>";
						$team .= "<fours number='$team_player[fours_firstclass]'/>";
						$team .= "<sixes number='$team_player[sixes_firstclass]'/>";
						$team .= "<catches number='$team_player[catches_firstclass]'/>";
						$team .= "<stumps number='$team_player[stumps_firstclass]'/>";

					$team .= "</statistics >";

					// match type lista

					$team .= "<statistics match_type='lista'>";
						$team .= "<matches number='$team_player[matches_lista]'/>";
						$team .= "<notOuts number='$team_player[notouts_lista]'/>";
						$team .= "<runs number='$team_player[runs_lista]'/>";
						$team .= "<highestscores number='$team_player[highestscores_lista]'/>";
						$team .= "<battingavg number='$team_player[battingavg_lista]'/>";
						$team .= "<batting_strikerate number='$team_player[batting_strikerate_lista]'/>";
						$team .= "<ballsfaced number='$team_player[ballsfaced_lista]'/>";
						$team .= "<centuries number='$team_player[centuries_lista]'/>";
						$team .= "<fifties number='$team_player[fifties_lista]'/>";
						$team .= "<fours number='$team_player[fours_lista]'/>";
						$team .= "<sixes number='$team_player[sixes_lista]'/>";
						$team .= "<catches number='$team_player[catches_lista]'/>";
						$team .= "<stumps number='$team_player[stumps_lista]'/>";

					$team .= "</statistics >";

				$team .= "</player>";
			}
			 $team .="</team>";


	}




		echo $team;

		//echo "<player data='sohail' />";

	}


//tournament stats new
elseif($cmd == "tournament_stats_new") {

	 $tournament = isset($_GET['tournament'])?$_GET['tournament']:0;
	 $limit = isset($_GET['limit'])?$_GET['limit']:5;
	 echo "<cricket>";
	 $tournamentObject = $DB->select("SELECT
							 participant.id,
							 participant.name,
							 country.id country_id,
							 country.name country,
							 gender.name gender,
							 international,
							 participant.se,
							 participant.active,
							 season.name season
						 FROM
							 participant,
							 systemdata country,
							 systemdata gender,
							 systemdata season
						 WHERE participant.country = country.id
							 AND participant.gender = gender.id
							 AND gender.systemdata_type = 'gender'
							 AND country.systemdata_type = 'country'
							 AND participant_type = 'tournament'
							 AND participant.season = season.id
							 AND season.systemdata_type = 'season'
							 AND participant.id = $tournament"
	 );
	 $tournamentObject = isset($tournamentObject[0]) ? $tournamentObject[0] : null;
	 echo "<tournament id=\"" . $tournamentObject['id'] . "\" name=\"" . textf($tournamentObject['name']) . "\" country=\"" . $tournamentObject['country'] . "\" gender=\"" . $tournamentObject['gender'] . "\" category=\"" . $tournamentObject['se'] . "\" active=\"" . $tournamentObject['active'] . "\" season=\"" . $tournamentObject['season'] . "\" international=\"" . $tournamentObject['international'] . "\" >";
	 //-----------------------------------------------------------
	 $tournamentid = $tournamentObject['id'];
	 $team_id = 0;
	 $teams = $DB->select("
		 select
		 e.tournament,
		 e.stadium,
		 e.match_type,
		 tt.teamid,
		 stadium.name as stadium_name,
		 stadium.city as stadium_city,
		 stadium.city as stadium_city,
		 country.name as country_name,

		 team.name as team_name,
		 team.history as team_history

		 from event as e

			 left Join tournament_team as tt on (tt.tourid = e.tournament)

			 left Join systemdatac as stadium on (stadium.id = e.stadium and stadium.systemdata_type='stadium')

			 left Join participant as team on (team.id = tt.teamid and team.participant_type='team' and team.name != 'TBC')

			 left Join participant as country on (country.country = stadium.country and country.participant_type='team')

			 where
			 (CASE
				 WHEN ($team_id != 0) THEN e.tournament = '$tournamentid' and tt.teamid =  '$team_id'

				 ELSE e.tournament = '$tournamentid' END
			 )
			 group by tt.teamid

	 ");
	 foreach($teams as $team){
		 $team_name = new team($team['teamid']);
			$team_name->load();
		 if($team_name->name != 'TBC'){
		 echo "<team id=\"" . $team['teamid'] . "\"  name=\"" . textf($team_name->name) . "\"   >";
		 $teamid = $team['teamid'];
			 $most_runs = $DB->select("
					 SELECT
					 SUM(bat.score) AS most_runs,
					 TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
					 p.country,
					 m.`tournament`,linup.playerid
					 FROM `event` m, `event_playerex` linup, `event_batsman` bat ,player p
					 WHERE m.`status` IN (40,7) AND m.tournament = $tournament and linup.teamid = '$teamid'
					 AND linup.`eventid` = m.`id`
					 AND bat.`event_playerFK` = linup.`id`
					 AND p.`id` = linup.`playerid`
					 GROUP BY linup.`playerid`
					 ORDER BY most_runs DESC
					 LIMIT $limit
			 "); //leading runs for a tournament

			 $most_sixes = $DB->select("
					 SELECT
					 SUM(bat.s6) AS most_sixes,
					 TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
					 m.`tournament`,linup.playerid,p.country
					 FROM `event` m, `event_playerex` linup, `event_batsman` bat ,player p
					 WHERE m.`status` IN (40,7) AND m.tournament = $tournament and linup.teamid = '$teamid'
					 AND linup.`eventid` = m.`id`
					 AND bat.`event_playerFK` = linup.`id`
					 AND p.`id` = linup.`playerid`
					 GROUP BY linup.`playerid`
					 ORDER BY most_sixes DESC
					 LIMIT $limit
			 "); //leading sixes for a tournament

			 //---------------------------------------------------
			 $top_inning_score = $DB->select("
					 SELECT
					 (bat.score) AS inning_score,
					 TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
					 m.id,m.`tournament`,linup.playerid,p.country
					 FROM `event` m, `event_playerex` linup, `event_batsman` bat ,player p
					 WHERE
					 m.`status` IN (40,7) AND m.tournament = $tournament and linup.teamid = '$teamid'
					 AND linup.`eventid` = m.`id`
					 AND bat.`event_playerFK` = linup.`id`
					 AND p.`id` = linup.`playerid`
					 ORDER BY inning_score DESC
					 LIMIT $limit
			 "); //highest innings for a tournament
			 //---------------------------------------------------
			 $most_wickets = $DB->select("
					 SELECT
					 SUM(bowl.wkt) AS most_wkts,
					 TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
					 m.`tournament`,linup.playerid,p.country
					 FROM `event` m, `event_playerex` linup, `event_bowler` bowl ,player p
					 WHERE m.`status` IN (40,7) AND m.tournament = $tournament and linup.teamid = '$teamid'
					 AND linup.`eventid` = m.`id`
					 AND bowl.`event_playerFK` = linup.`id`
					 AND p.`id` = linup.`playerid`
					 GROUP BY linup.`playerid`
					 ORDER BY most_wkts DESC
					 LIMIT $limit
			 "); // most_wickets for a tournament
			 //---------------------------------------------------
			 $leading_bowler = $DB->select("
					 SELECT
					 bowl.wkt,
					 bowl.`run` ,
					 TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
					 m.`tournament`,linup.playerid,p.country
					 FROM `event` m, `event_playerex` linup, `event_bowler` bowl ,player p
					 WHERE m.`status` IN (40,7) AND m.tournament = $tournament and linup.teamid = '$teamid'
					 AND linup.`eventid` = m.`id`
					 AND bowl.`event_playerFK` = linup.`id`
					 AND p.`id` = linup.`playerid`
					 ORDER BY bowl.wkt DESC, bowl.run
					 LIMIT $limit
			 "); // best bowling figures for a tournament
			 //---------------------------------------------------
			 $best_economy = $DB->select("
					 SELECT
					 (bowl.`run` / bowl.`over`) AS economy,
					 bowl.`run`,
					 bowl.`over`,
					 TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
					 m.`tournament`,linup.playerid,p.country
					 FROM `event` m, `event_playerex` linup, `event_bowler` bowl ,player p
					 WHERE m.`status` IN (40,7) AND m.tournament = $tournament and linup.teamid = '$teamid'
					 AND linup.`eventid` = m.`id`
					 AND bowl.`event_playerFK` = linup.`id`
					 AND p.`id` = linup.`playerid`
					 AND bowl.`over` > 0
                                         AND bowl.`active` = 1
					 ORDER BY economy
					 LIMIT $limit
			 "); // best bowling economy for a tournament
			 //---------------------------------------------------
			 echo "<leading_runs_scorer>";
					 for ($rc = 0; $rc < sizeof($most_runs); $rc++) {
							 echo "<player position=\"" . ($rc + 1) . "\" runs=\"" . $most_runs[$rc]["most_runs"] . "\"   player_id=\"" . $most_runs[$rc]["playerid"] . "\"   player_name=\"" . $most_runs[$rc]["player_name"] . "\"     />";
					 }
			 echo "</leading_runs_scorer>";
			 //---------------------------------------------------
			 echo "<highest_inning_scorer>";
					 for ($rc = 0; $rc < sizeof($top_inning_score); $rc++) {
							 echo "<player position=\"" . ($rc + 1) . "\" inning_score=\"" . $top_inning_score[$rc]["inning_score"] . "\"   player_id=\"" . $top_inning_score[$rc]["playerid"] . "\"   player_name=\"" . $top_inning_score[$rc]["player_name"] . "\"   />";
					 }
			 echo "</highest_inning_scorer>";
			 //---------------------------------------------------
			 echo "<leading_sixes>";
					 for ($rc = 0; $rc < sizeof($most_sixes); $rc++) {
							 echo "<player position=\"" . ($rc + 1) . "\" sixes=\"" . $most_sixes[$rc]["most_sixes"] . "\"   player_id=\"" . $most_sixes[$rc]["playerid"] . "\"   player_name=\"" . $most_sixes[$rc]["player_name"] . "\"   />";
					 }
			 echo "</leading_sixes>";
			 //---------------------------------------------------
			 echo "<leading_wickets>";
					 for ($rc = 0; $rc < sizeof($most_wickets); $rc++) {
							 echo "<player position=\"" . ($rc + 1) . "\" wickets=\"" . $most_wickets[$rc]["most_wkts"] . "\"   player_id=\"" . $most_wickets[$rc]["playerid"] . "\"   player_name=\"" . $most_wickets[$rc]["player_name"] . "\"   />";
					 }
			 echo "</leading_wickets>";
			 //---------------------------------------------------
			 echo "<best_bowling_figures>";
					 for ($rc = 0; $rc < sizeof($leading_bowler); $rc++) {
							 echo "<player position=\"" . ($rc + 1) . "\" bowling_figures=\"" . $leading_bowler[$rc]["wkt"] . "/" .  $leading_bowler[$rc]["run"] . "\"   player_id=\"" . $leading_bowler[$rc]["playerid"] . "\"   player_name=\"" . $leading_bowler[$rc]["player_name"] . "\"   />";
					 }
			 echo "</best_bowling_figures>";
			 //---------------------------------------------------
			 echo "<best_economy_bowler>";
					 for ($rc = 0; $rc < sizeof($best_economy); $rc++) {
							 echo "<player position=\"" . ($rc + 1) . "\" economy=\"" . number_format($best_economy[$rc]["economy"],2)  . "\"    runs=\"" . $best_economy[$rc]["run"]  . "\"    overs=\"" . $best_economy[$rc]["over"]  . "\"   player_id=\"" . $best_economy[$rc]["playerid"] . "\"   player_name=\"" . $best_economy[$rc]["player_name"] . "\"    />";
					 }
			 echo "</best_economy_bowler>";
			 //---------------------------------------------------



		 echo "</team >";
	 }

 }

	 echo "</tournament>";


}
//tournament stats new end








     elseif($cmd == "tournament_stats") {

        $tournament = isset($_GET['tournament'])?$_GET['tournament']:0;
        $limit = isset($_GET['limit'])?$_GET['limit']:5;
        echo "<cricket>";
        $tournamentObject = $DB->select("SELECT
                    participant.id,
                    participant.name,
                    country.id country_id,
                    country.name country,
                    gender.name gender,
                    international,
                    participant.se,
                    participant.active,
                    season.name season
                  FROM
                    participant,
                    systemdata country,
                    systemdata gender,
                    systemdata season
                  WHERE participant.country = country.id
                    AND participant.gender = gender.id
                    AND gender.systemdata_type = 'gender'
                    AND country.systemdata_type = 'country'
                    AND participant_type = 'tournament'
                    AND participant.season = season.id
                    AND season.systemdata_type = 'season'
                    AND participant.id = $tournament"
        );
        $tournamentObject = isset($tournamentObject[0]) ? $tournamentObject[0] : null;
        echo "<tournament id=\"" . $tournamentObject['id'] . "\" name=\"" . textf($tournamentObject['name']) . "\" country=\"" . $tournamentObject['country'] . "\" gender=\"" . $tournamentObject['gender'] . "\" category=\"" . $tournamentObject['se'] . "\" active=\"" . $tournamentObject['active'] . "\" season=\"" . $tournamentObject['season'] . "\" international=\"" . $tournamentObject['international'] . "\" >";
        //-----------------------------------------------------------
        $most_runs = $DB->select("
            SELECT
            SUM(bat.score) AS most_runs,
            TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
            m.`tournament`,linup.playerid
            FROM `event` m, `event_playerex` linup, `event_batsman` bat ,player p
            WHERE m.`status` IN (40,7) AND m.tournament = $tournament AND bat.score > 0
            AND linup.`eventid` = m.`id`
            AND bat.`event_playerFK` = linup.`id`
            AND p.`id` = linup.`playerid`
            GROUP BY linup.`playerid`
            ORDER BY most_runs DESC
            LIMIT $limit
        "); //leading runs for a tournament
        //---------------------------------------------------
        $most_sixes = $DB->select("
            SELECT
            SUM(bat.s6) AS most_sixes,
            TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
            m.`tournament`,linup.playerid
            FROM `event` m, `event_playerex` linup, `event_batsman` bat ,player p
            WHERE m.`status` IN (40,7) AND m.tournament = $tournament AND bat.s6 > 0
            AND linup.`eventid` = m.`id`
            AND bat.`event_playerFK` = linup.`id`
            AND p.`id` = linup.`playerid`
            GROUP BY linup.`playerid`
            ORDER BY most_sixes DESC
            LIMIT $limit
        "); //leading sixes for a tournament
        //---------------------------------------------------
        $top_inning_score = $DB->select("
            SELECT
            (bat.score) AS inning_score,
            TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
            m.id,m.`tournament`,linup.playerid
            FROM `event` m, `event_playerex` linup, `event_batsman` bat ,player p
            WHERE
            m.`status` IN (40,7) AND m.tournament = $tournament AND bat.score > 0
            AND linup.`eventid` = m.`id`
            AND bat.`event_playerFK` = linup.`id`
            AND p.`id` = linup.`playerid`
            ORDER BY inning_score DESC
            LIMIT $limit
        "); //highest innings for a tournament
        //---------------------------------------------------
        $most_wickets = $DB->select("
            SELECT
            SUM(bowl.wkt) AS most_wkts,
            TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
            m.`tournament`,linup.playerid
            FROM `event` m, `event_playerex` linup, `event_bowler` bowl ,player p
            WHERE m.`status` IN (40,7) AND m.tournament = $tournament AND bowl.wkt > 0
            AND linup.`eventid` = m.`id`
            AND bowl.`event_playerFK` = linup.`id`
            AND p.`id` = linup.`playerid`
            GROUP BY linup.`playerid`
            ORDER BY most_wkts DESC
            LIMIT $limit
        "); // most_wickets for a tournament
        //---------------------------------------------------
        $leading_bowler = $DB->select("
            SELECT
            bowl.wkt,
            bowl.`run` ,
            TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
            m.`tournament`,linup.playerid
            FROM `event` m, `event_playerex` linup, `event_bowler` bowl ,player p
            WHERE m.`status` IN (40,7) AND m.tournament = $tournament
            AND linup.`eventid` = m.`id`
            AND bowl.`event_playerFK` = linup.`id`
            AND p.`id` = linup.`playerid`
            ORDER BY bowl.wkt DESC, bowl.run
            LIMIT $limit
        "); // best bowling figures for a tournament
        //---------------------------------------------------
        $best_economy = $DB->select("
            SELECT
            (bowl.`run` / bowl.`over`) AS economy,
            bowl.`run`,
            bowl.`over`,
            TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
            m.`tournament`,linup.playerid
            FROM `event` m, `event_playerex` linup, `event_bowler` bowl ,player p
            WHERE m.`status` IN (40,7) AND m.tournament = $tournament
            AND linup.`eventid` = m.`id`
            AND bowl.`event_playerFK` = linup.`id`
            AND p.`id` = linup.`playerid`
            AND bowl.`over` > 0
            AND bowl.`active` = 1
            ORDER BY economy
            LIMIT $limit
        "); // best bowling economy for a tournament
        //---------------------------------------------------
        echo "<leading_runs_scorer>";
            for ($rc = 0; $rc < sizeof($most_runs); $rc++) {
                echo "<player position=\"" . ($rc + 1) . "\" runs=\"" . $most_runs[$rc]["most_runs"] . "\"   player_id=\"" . $most_runs[$rc]["playerid"] . "\"   player_name=\"" . $most_runs[$rc]["player_name"] . "\"   />";
            }
        echo "</leading_runs_scorer>";
        //---------------------------------------------------
        echo "<highest_inning_scorer>";
            for ($rc = 0; $rc < sizeof($top_inning_score); $rc++) {
                echo "<player position=\"" . ($rc + 1) . "\" inning_score=\"" . $top_inning_score[$rc]["inning_score"] . "\"   player_id=\"" . $top_inning_score[$rc]["playerid"] . "\"   player_name=\"" . $top_inning_score[$rc]["player_name"] . "\"   />";
            }
        echo "</highest_inning_scorer>";
        //---------------------------------------------------
        echo "<leading_sixes>";
            for ($rc = 0; $rc < sizeof($most_sixes); $rc++) {
                echo "<player position=\"" . ($rc + 1) . "\" sixes=\"" . $most_sixes[$rc]["most_sixes"] . "\"   player_id=\"" . $most_sixes[$rc]["playerid"] . "\"   player_name=\"" . $most_sixes[$rc]["player_name"] . "\"   />";
            }
        echo "</leading_sixes>";
        //---------------------------------------------------
        echo "<leading_wickets>";
            for ($rc = 0; $rc < sizeof($most_wickets); $rc++) {
                echo "<player position=\"" . ($rc + 1) . "\" wickets=\"" . $most_wickets[$rc]["most_wkts"] . "\"   player_id=\"" . $most_wickets[$rc]["playerid"] . "\"   player_name=\"" . $most_wickets[$rc]["player_name"] . "\"   />";
            }
        echo "</leading_wickets>";
        //---------------------------------------------------
        echo "<best_bowling_figures>";
            for ($rc = 0; $rc < sizeof($leading_bowler); $rc++) {
                echo "<player position=\"" . ($rc + 1) . "\" bowling_figures=\"" . $leading_bowler[$rc]["wkt"] . "/" .  $leading_bowler[$rc]["run"] . "\"   player_id=\"" . $leading_bowler[$rc]["playerid"] . "\"   player_name=\"" . $leading_bowler[$rc]["player_name"] . "\"   />";
            }
        echo "</best_bowling_figures>";
        //---------------------------------------------------
        echo "<best_economy_bowler>";
            for ($rc = 0; $rc < sizeof($best_economy); $rc++) {
                echo "<player position=\"" . ($rc + 1) . "\" economy=\"" . number_format($best_economy[$rc]["economy"],2)  . "\"    runs=\"" . $best_economy[$rc]["run"]  . "\"    overs=\"" . $best_economy[$rc]["over"]  . "\"   player_id=\"" . $best_economy[$rc]["playerid"] . "\"   player_name=\"" . $best_economy[$rc]["player_name"] . "\"    />";
            }
        echo "</best_economy_bowler>";
        //---------------------------------------------------
        echo "</tournament>";


    }else if($cmd == "h2h") {    //get head to head teams matches with winning Results
    $h_team = isset($_GET['team1']) ? $_GET['team1'] : 0;
    $a_team = isset($_GET['team2']) ? $_GET['team2'] : 0;
    $match_type = isset($_GET['match_type']) ? $_GET['match_type'] : 'odi';

    echo "<cricket>";
    $data = $DB->select_v2("
        SELECT
        matches.id,matches.gt,
        (CASE
            WHEN (matches.winner = 1) THEN matches.ht_id
            WHEN (matches.winner = 2) THEN matches.at_id
            ELSE 0 END
        ) AS winner,
        h_teams.id h_team_id,h_teams.name h_team_name,
        a_teams.id a_team_id,a_teams.name a_team_name,
        sd.name match_type
        FROM
        (
            SELECT *
            FROM `event` m
            WHERE
            m.`status` IN (40,7)
            AND m.`winner` <> 0
            AND m.`match_type` IN (" . implode(',', getMatchTypeIdArray($match_type)) . ")
            AND ((m.`ht_id` = $h_team AND m.`at_id` = $a_team) OR (m.`ht_id` = $a_team AND m.`at_id` = $h_team))
            ORDER BY m.gt DESC
            " . paginationSQL($page,20) . "
        ) matches
        JOIN participant h_teams ON ht_id = h_teams.id AND h_teams.participant_type = 'team'
        JOIN participant a_teams ON at_id = a_teams.id AND a_teams.participant_type = 'team'
        JOIN systemdata sd ON matches.match_type = sd.id AND sd.systemdata_type = 'match_type'
        ORDER BY matches.gt DESC
        ");
    echo "<games>";
    for ($rc = 0; $rc < sizeof($data); $rc++) {
        echo "<game id=\"" . $data[$rc]["id"] . "\" gmt_datetime=\"" . $data[$rc]["gt"] . "\" winner_team_id=\"" . $data[$rc]["winner"] . "\" matchformat=\"" . $data[$rc]["match_type"] . "\" >";
            echo "<teams>";
                echo "<team id=\"" . $data[$rc]["h_team_id"] . "\" name=\"" . str_replace("&","&amp;",$data[$rc]["h_team_name"]) . "\" type='home'>";
                echo "<inngings>";
                foreach(getMatchInnings($data[$rc]["id"], $data[$rc]["h_team_id"]) as $inning){
                    echo "<innging number=\"" . $inning["inning"] . "\" score=\"" . $inning["score"] . "\" overs=\"" . $inning["overs"] . "\" wicket=\"" . $inning["wicket"] . "\" />";
                }
                echo "</inngings>";
                echo "</team>";
                //---------------------------
                echo "<team id=\"" . $data[$rc]["a_team_id"] . "\" name=\"" . str_replace("&","&amp;",$data[$rc]["a_team_name"]) . "\" type='away' >";
                echo "<inngings>";
                foreach(getMatchInnings($data[$rc]["id"], $data[$rc]["a_team_id"]) as $inning){
                    echo "<innging number=\"" . $inning["inning"] . "\" score=\"" . $inning["score"] . "\" overs=\"" . $inning["overs"] . "\" wicket=\"" . $inning["wicket"] . "\" />";
                }
                echo "</inngings>";
                echo "</team>";
                //---------------------------
            echo "</teams>";
        echo "</game>";
    }
    echo "</games>";
} elseif($cmd == "countrylist"){

	echo "<cricket>";
	echo "<countries>";
	$data = $DB->select("select systemdata.id, systemdata.name from systemdata where systemdata_type='country' and active='1'");
	for($rc =0; $rc < sizeof($data); $rc++){
		echo "<country id=\"".$data[$rc]["id"]."\" name=\"".str_replace("&","&amp;", $data[$rc]["name"])."\" />";
	}
	echo "</countries>";
}elseif($cmd == "stats_score"){
	echo "<cricket>";
	echo "<list>";
	$data = $DB->select("select systemdata.id, systemdata.name from systemdata where systemdata_type='stats_score' and active='1'");
	for($rc =0; $rc < sizeof($data); $rc++){
		echo "<item id=\"".$data[$rc]["id"]."\" name=\"".str_replace("&","&amp;", $data[$rc]["name"])."\" />";
	}
	echo "</list>";
} elseif($cmd == "player"){
	$p = $DB->select("select * from player where id=".$id);
	echo "<cricket>";
	echo "<player_profile id=\"".$p[0]["id"]."\" name=\"".trim($p[0]["firstname"]." ".$p[0]["lastname"])."\" country=\"".getName("country", $p[0]["country"])."\">";
	if(isset($_GET["debug"]) && $_GET["debug"]){
		echo "<!-- ";
		print_r($p[0]);
		echo " -->";
	}
	if($p[0]["player_type"] != 15){
		if($p[0]["date_birth"]){
			echo "<player_info type=\"Born\" value=\"".date("d M Y", strtotime($p[0]["date_birth"]))."\" />";
			echo "<player_info type=\"Age\" value=\"".ageP($p[0]["date_birth"])."\" />";
		}
		$teams = array();
		$data = $DB->select("select teamid from playerteam where playerid=".$id." and active='1'");
		for($rc =0;$rc< count($data); $rc++){
			$teams[] = getNamep("team",$data[$rc][0]);
		}
		echo "<player_info type=\"Major Teams\" value=\"".implode(", ", $teams)."\" />";
		if($p[0]["weight"])
			echo "<player_info type=\"Weight\" value=\"".$p[0]["weight"]."\" />";
		if($p[0]["height"])
			echo "<player_info type=\"Height\" value=\"".$p[0]["height"]." cm\" />";
		if($p[0]["direction"]){
			echo "<player_info type=\"Batting Style\" value=\"".getName("direction", $p[0]["direction"])."\" />";
		}
		if($p[0]["bowling_direction"]){
			echo "<player_info type=\"Bowling Style\" value=\"".getName("bowling_direction", $p[0]["bowling_direction"])."\" />";
		}
		if($p[0]["best_bat_test"]){
			echo "<player_info type=\"HS in Test\" value=\"".$p[0]["best_bat_test"]."\" />";
		}
		if($p[0]["best_bat_odi"]){
			echo "<player_info type=\"HS in ODI\" value=\"".$p[0]["best_bat_odi"]."\" />";
		}
		if($p[0]["best_bat_t20"]){
			echo "<player_info type=\"HS in T20\" value=\"".$p[0]["best_bat_t20"]."\" />";
		}
		if($p[0]["best_bowl_test"]){
			echo "<player_info type=\"BB in Test\" value=\"".$p[0]["best_bowl_test"]."\" />";
		}
		if($p[0]["best_bowl_odi"]){
			echo "<player_info type=\"BB in ODI\" value=\"".$p[0]["best_bowl_odi"]."\" />";
		}
		if($p[0]["best_bowl_t20"]){
			echo "<player_info type=\"BB in T20\" value=\"".$p[0]["best_bowl_t20"]."\" />";
		}
		if($p[0]["odi_played"]){
			echo "<player_info type=\"ODIs played\" value=\"".$p[0]["odi_played"]."\" />";
		}
		if($p[0]["odi_innings"]){
			echo "<player_info type=\"ODI Innings\" value=\"".$p[0]["odi_innings"]."\" />";
		}
		if($p[0]["odi_runs_scored"]){
			echo "<player_info type=\"Runs Scored\" value=\"".$p[0]["odi_runs_scored"]."\" />";
		}
		if($p[0]["odi_highest_score"]){
			echo "<player_info type=\"Highest Score\" value=\"".$p[0]["odi_highest_score"]."\" />";
		}
		if($p[0]["odi_strike_rate"]){
			echo "<player_info type=\"Strike Rate\" value=\"".$p[0]["odi_strike_rate"]."\" />";
		}
		if($p[0]["odi_batting_avg"]){
			echo "<player_info type=\"Batting Average\" value=\"".$p[0]["odi_batting_avg"]."\" />";
		}
		if($p[0]["odi_catches"]){
			echo "<player_info type=\"Catches\" value=\"".$p[0]["odi_catches"]."\" />";
		}
		if($p[0]["odi_wickets"]){
			echo "<player_info type=\"Wickets\" value=\"".$p[0]["odi_wickets"]."\" />";
		}
		if($p[0]["odi_bowling_average"]){
			echo "<player_info type=\"Bowling Average\" value=\"".$p[0]["odi_bowling_average"]."\" />";
		}
		if(is_file("players/".$id.".png")){
			echo "<player_info type=\"image\" value=\"http://cric.rent2code.dk/players/$id.png\" />";
		}
	}
	echo "</player_profile>";
}

elseif($cmd == "standings"){
	echo "<cricket>";
	$string = "";
	$name = $DB->select("select name, table_schema from participant where participant_type='tournament' and id=".$id);
	$sch = array();
	$schema = $name[0][1];
	$fields = array();
	if(substr($schema,0,1) == 1){ $sch['played'] = 1; $fields[] = 'played';} else $sch['played'] = 0;
	if(substr($schema,1,1) == 1){ $sch['win'] = 1; $fields[] = 'win';}  else $sch['win'] = 0;
	if(substr($schema,2,1) == 1){ $sch['loss'] = 1; $fields[] = 'loss';}  else $sch['loss'] = 0;
	if(substr($schema,3,1) == 1){ $sch['draw'] = 1; $fields[] = 'draw';}  else $sch['draw'] = 0;
	if(substr($schema,4,1) == 1){ $sch['nr'] = 1; $fields[] = 'nr';}  else $sch['nr'] = 0;
	if(substr($schema,5,1) == 1){ $sch['abandon'] = 1; $fields[] = 'abandon';}  else $sch['abandon'] = 0;
	if(substr($schema,6,1) == 1){ $sch['runrate'] = 1; $fields[] = 'runrate';}  else $sch['runrate'] = 0;
	if(substr($schema,7,1) == 1){ $sch['score_f'] = 1; $fields[] = 'score_f';}  else $sch['score_f'] = 0;
	if(substr($schema,8,1) == 1){ $sch['score_a'] = 1; $fields[] = 'score_a';}  else $sch['score_a'] = 0;
	if(substr($schema,9,1) == 1){ $sch['points'] = 1; $fields[] = 'points';}  else $sch['points'] = 0;
	$word = array('played'=>'Mat','win'=>'Won','loss'=>'Lost','draw'=>'Tied','nr'=>'N/R','abandon'=>'Abandon','runrate'=>'Net RR','score_f'=>'For','score_a'=>'Against', 'points'=>'Pts');
	$grp = $DB->select("select distinct groupFK from standings, standing_group where groupFK=standing_group.id and tourid=".$id." and groupFK != '0' and active='1' order by sortorder");
	if(sizeof($grp) > 0){
		$string .= "<league id=\"".$id."\" name=\"".str_replace("&","&amp;", $name[0][0])."\" group=\"1\">";
		for($g=0; $g < sizeof($grp); $g++){
			$string .= "<group id=\"".$grp[$g]["groupFK"]."\" Name=\"".getGroupT($grp[$g]["groupFK"])."\">";
			$string .= "<team id=\"-\" name=\"Team\" rank=\"Pos\" ";
			for($f=0;$f < sizeof($fields); $f++){
				$string .= " c$f=\"".$word[$fields[$f]]."\"";
			}
			$string .= " />";
			$data = $DB->select("select * from standings where tourid=".$id." and active='1' and groupFK=".$grp[$g]["groupFK"]." order by rank");
			for($m =0; $m < count($data); $m++){
				$string .= "<team id=\"".$data[$m]["teamid"]."\" name=\"".getNamep("team", $data[$m]["teamid"])."\" rank=\"".$data[$m]["rank"]."\" ";
				$data[$m]["points"] = str_replace(".00","", $data[$m]["points"]);
				for($f=0;$f < sizeof($fields); $f++){
					$string .= " c$f=\"".$data[$m][$fields[$f]]."\"";
				}
				$string .= " />\n";
			}
			$string .= "</group>";
		}
		$string .= "</league>";
	} else {
		$string .= "<league id=\"".$id."\" name=\"".str_replace("&","&amp;", $name[0][0])."\" group=\"0\"><group>";
		$string .= "<team id=\"-\" name=\"Team\" rank=\"Pos\" ";
		for($f=0;$f < sizeof($fields); $f++){
			$string .= " c$f=\"".$word[$fields[$f]]."\"";
		}
		$string .= " />";
		$data = $DB->select("select * from standings where tourid=".$id." and active='1' order by rank");
		for($m =0; $m < count($data); $m++){
			$string .= "<team id=\"".$data[$m]["teamid"]."\" name=\"".getNamep("team", $data[$m]["teamid"])."\" rank=\"".$data[$m]["rank"]."\" ";
			$data[$m]["points"] = str_replace(".00","", $data[$m]["points"]);
			for($f=0;$f < sizeof($fields); $f++){
				$string .= " c$f=\"".$data[$m][$fields[$f]]."\"";
			}
			$string .= " />\n";
		}
		$string .= "</group></league>";
	}
	echo $string;
}

//sohail changes

elseif($cmd == "tournamentlist"){
	// echo "<cricket>";
	echo "<tournaments>";
	$year = $_GET["year"];
	$nextt_year = substr($_GET["year"],2);
	$prev_year = $year-1;
	$next_year = $nextt_year+1;

	$season_next_year = $year.'/'.$next_year;
	$season_pre_year = $prev_year.'/'.$nextt_year;

	$extra_sql = "";
	if(isset($_GET["country"])){
		$extra_sql = " and participant.country=".$_GET["country"];
	}
	if($category) {
		$extra_sql .= " and participant.se like '$category'";
	}
	$data_season = $DB->select("select *from systemdata where systemdata_type='season' and (name = '$season_next_year' or name = '$season_pre_year' or name = '$year')");

 for($rcc =0; $rcc < sizeof($data_season); $rcc++){
	 $seasonid= $data_season[$rcc]['id'];
	 $data = $DB->select("select participant.id,participant.history,participant.name, participant.format,participant.champion_team, country.id country_id, country.name country, gender.name gender, international, participant.se, participant.active, season.name season from participant, systemdata country, systemdata gender, systemdata season where participant.country = country.id and participant.gender=gender.id and gender.systemdata_type='gender' and country.systemdata_type='country' and participant_type='tournament' and participant.season=season.id and season.systemdata_type='season' $extra_sql and participant.season='$seasonid' order by season.name desc, participant.name");
 	for($rc =0; $rc < sizeof($data); $rc++){

		$tourid = $data[$rc]["id"];
		$tournament_teams = $DB->select("select *from tournament_team where tourid ='$tourid' and teamid != '52' and teamid != '124' ");
		// $stadium = $DB->select("select *from event where tournament ='$tourid' ");
		$stadium = $DB->select("select *from event where tournament ='$tourid' group by stadium");
		// $stadium = $DB->select("select *from event where tournament ='$tourid' group by stadium");

		$team = new team($data[$rc]["champion_team"]);
		 $team->load();
		 $team_name = $team->name;
		 $format=getName("match_type",$data[$rc]["format"]);
 		echo "<tournament id=\"".$data[$rc]["id"]."\" name=\"".str_replace("&","&amp;", $data[$rc]["name"])."\" country=\"".$data[$rc]["country_id"]."\" gender=\"".$data[$rc]["gender"]."\" category=\"".$data[$rc]["se"]."\" season=\"".str_replace("&","&amp;", $data[$rc]["season"])."\" active=\"".$data[$rc]["active"]."\" format=\"".$format."\" Champion=\"".$team_name."\" teams=\"".sizeof($tournament_teams)."\" >";
 		echo "<description text=\"".$data[$rc]["history"]."\" />";

		$tournamentid = $data[$rc]["id"];
		//most wicket
		$most_wickets = $DB->select("
				SELECT
				SUM(bowl.wkt) AS most_wkts,
				TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
				m.`tournament`,linup.playerid,p.country
				FROM `event` m, `event_playerex` linup, `event_bowler` bowl ,player p
				WHERE m.`status` IN (40,7) AND m.tournament = '$tournamentid'
				AND linup.`eventid` = m.`id`
				AND bowl.`event_playerFK` = linup.`id`
				AND p.`id` = linup.`playerid`
				GROUP BY linup.`playerid`
				ORDER BY most_wkts DESC
				LIMIT 1
		"); // most_wickets for a tournament
		//most wicket end

		for ($rc_most_wickets = 0; $rc_most_wickets < sizeof($most_wickets); $rc_most_wickets++) {
				echo "<mostwickets wickets=\"" . $most_wickets[$rc_most_wickets]["most_wkts"] . "\"   player_id=\"" . $most_wickets[$rc_most_wickets]["playerid"] . "\"   player_name=\"" . $most_wickets[$rc_most_wickets]["player_name"] . "\" team_id=\"" .$most_wickets[$rc_most_wickets]['country']. "\"  />";
		}

		$most_runs = $DB->select("
				SELECT
				SUM(bat.score) AS most_runs,
				TRIM(CONCAT(p.`firstname`, ' ', p.`lastname`)) AS player_name,
				p.country,
				m.`tournament`,linup.playerid
				FROM `event` m, `event_playerex` linup, `event_batsman` bat ,player p
				WHERE m.`status` IN (40,7) AND m.tournament = $tournamentid
				AND linup.`eventid` = m.`id`
				AND bat.`event_playerFK` = linup.`id`
				AND p.`id` = linup.`playerid`
				GROUP BY linup.`playerid`
				ORDER BY most_runs DESC
				LIMIT 1
		"); //leading runs for a tournament
		//
		for ($rc_most_runs = 0; $rc_most_runs < sizeof($most_runs); $rc_most_runs++) {
				echo "<mostruns runs=\"" . $most_runs[$rc_most_runs]["most_runs"] . "\"   player_id=\"" . $most_runs[$rc_most_runs]["playerid"] . "\"   player_name=\"" . $most_runs[$rc_most_runs]["player_name"] . "\" team_id=\"" .$most_runs[$rc_most_runs]['country']. "\"  />";
		}
		echo "<stadiums>";
		for($rc_t =0; $rc_t < sizeof($stadium); $rc_t++){
			$std = new stadium($stadium[$rc_t]['stadium']);
			$std->load();

			$h_team = new team($stadium[$rc_t]['ht_id']);
			 $h_team->load();
			 $h_team_name = $h_team->name;

			$a_team = new team($stadium[$rc_t]['at_id']);
			 $a_team->load();
			 $a_team_name = $a_team->name;

				// echo "<stadium name=\"".$std->name."\" home_team=\"".textf($h_team->name)."\" />";
				echo "<stadium name=\"".$std->name."\" matchId=\"".$stadium[$rc_t]['id']."\" home_team=\"".textf($h_team->name)."\"  home_team_id=\"".$stadium[$rc_t]['ht_id']."\"  away_team=\"".textf($a_team->name)."\"  away_team_id=\"".$stadium[$rc_t]['at_id']."\" />";
		}
		echo "</stadiums>";
		echo "</tournament>";
 	}
 }

	echo "</tournaments>";
}

//sohail changes end

elseif($cmd == "tournamentlist_old"){
	echo "<cricket>";
	echo "<tournaments>";
	$extra_sql = "";
	if(isset($_GET["country"])){
		$extra_sql = " and participant.country=".$_GET["country"];
	}
	if($category) {
		$extra_sql .= " and participant.se like '$category'";
	}
	$data = $DB->select("select participant.id, participant.name, country.id country_id, country.name country, gender.name gender, international, participant.se, participant.active, season.name season from participant, systemdata country, systemdata gender, systemdata season where participant.country = country.id and participant.gender=gender.id and gender.systemdata_type='gender' and country.systemdata_type='country' and participant_type='tournament' and participant.season=season.id and season.systemdata_type='season' $extra_sql order by season.name desc, participant.name");
	for($rc =0; $rc < sizeof($data); $rc++){
		echo "<tournament id=\"".$data[$rc]["id"]."\" name=\"".str_replace("&","&amp;", $data[$rc]["name"])."\" country=\"".$data[$rc]["country_id"]."\" gender=\"".$data[$rc]["gender"]."\" category=\"".$data[$rc]["se"]."\" season=\"".str_replace("&","&amp;", $data[$rc]["season"])."\" active=\"".$data[$rc]["active"]."\" />";
	}
	echo "</tournaments>";
} elseif($cmd == "teamlist"){
	echo "<cricket>";
	echo "<teams>";
	if($_GET["country"]) $extra_sql = " and participant.country=".$_GET["country"];
	$data = $DB->select("select participant.id, participant.name, country.name country, gender.name gender, participant.en from participant, systemdata country, systemdata gender where participant.country = country.id and participant.gender=gender.id and gender.systemdata_type='gender' and country.systemdata_type='country' and participant_type='team' and participant.active='1' $extra_sql");
	for($rc =0; $rc < sizeof($data); $rc++){
		echo "<team id=\"".$data[$rc]["id"]."\" name=\"".str_replace("&","&amp;", $data[$rc]["name"])."\" country=\"".str_replace("&","&amp;", $data[$rc]["country"])."\"  code=\"".str_replace("&","&amp;", $data[$rc]["en"])."\" />";
	}
	echo "</teams>";
} elseif($cmd == "playerlist"){
	echo "<cricket>";
	echo "<players>";
	$extra_sql = "";
	if(isset($_GET["country"])) $extra_sql .= " and player.country=".$_GET["country"];
	if(isset($_GET["team"])) $extra_sql .= " and teamid=".$_GET["team"];
	if(isset($_GET["tour"])){
		$extra_sql .= " and tts.tour_id=".$_GET["tour"];
		$data = $DB->select("select distinct player.id, player.firstname, player.lastname, country.name country, gender.name gender, player_type,
		date_birth, weight, height, direction, playerteam.teamid from player, systemdata country, systemdata gender , playerteam, tournament_team_squad tts where playerid=player.id and playerteam.active=1
		and player.country = country.id and player.gender=gender.id and gender.systemdata_type='gender' and country.systemdata_type='country' and player.active='1' and player.id=tts.player_id and playerteam.teamid=tts.team_id
		$extra_sql order by firstname");
	} else {
		$data = $DB->select("select distinct player.id, player.firstname, player.lastname, country.name country, gender.name gender, player_type, date_birth,
		weight, height, direction, playerteam.teamid from player, systemdata country, systemdata gender , playerteam where playerid=player.id and playerteam.active=1 and
		player.country = country.id and player.gender=gender.id and gender.systemdata_type='gender' and country.systemdata_type='country' and player.active='1'
		$extra_sql order by firstname");
	}
	for($rc =0; $rc < sizeof($data); $rc++){
		echo "<player id=\"".$data[$rc]["id"]."\" name=\"".trim($data[$rc]["firstname"]." ".$data[$rc]["lastname"])."\" country=\"".str_replace("&","&amp;", $data[$rc]["country"])."\" gender=\"".$data[$rc]["gender"]."\" type=\"".getName("player_type",$data[$rc]["player_type"])."\" dob=\"".$data[$rc]["date_birth"]."\" team_id=\"".$data[$rc]["teamid"]."\" />";
	}
	echo "</players>";
} elseif($cmd == "stadiumlist"){
	echo "<cricket>";
	echo "<stadiumlist>";
	if($_GET["country"]) $extra_sql = " and  systemdatac.country=".$_GET["country"];
	$data = $DB->select("select systemdatac.id, systemdatac.name, country.name country, city , crowd_capacity, floodlight, end1, end2, date_firsttest, date_firstodi from systemdatac, systemdata country where systemdatac.country = country.id and country.systemdata_type='country' and systemdatac.systemdata_type='stadium' and systemdatac.active='1' $extra_sql");
	for($rc =0; $rc < sizeof($data); $rc++){
		echo "<stadium id=\"".$data[$rc]["id"]."\" name=\"".str_replace("&","&amp;", $data[$rc]["name"])."\" city=\"".str_replace("&","&amp;", $data[$rc]["city"])."\" country=\"".str_replace("&","&amp;", $data[$rc]["country"])."\" end1=\"".str_replace("&","&amp;", $data[$rc]["end1"])."\" end2=\"".str_replace("&","&amp;", $data[$rc]["end2"])."\" capacity=\"".$data[$rc]["crowd_capacity"]."\" floodlight=\"".$data[$rc]["floodlight"]."\" />";
	}
	echo "</stadiumlist>";
} elseif($cmd == "players_detail"){
	$recentlyupdated = isset($_GET["recentlyupdated"]) ? $_GET["recentlyupdated"] : false;
	echo "<cricket>";
	echo "<players>";
	$sql = "select p.id,firstname,lastname,concat(firstname, ' ',lastname) as name, country, country.name as country_name, gender.name as gender_name, p.active, date(date_birth) as dob, type.name as player_type, weight, height, batting.name as batting_style, bowling.name as bowling_style, nick, place_birth, education from player p left join systemdata country on country.id = p.country and country.systemdata_type = 'country' left join systemdata gender on p.gender = gender.id and gender.systemdata_type = 'gender' left join systemdata type on p.player_type = type.id and type.systemdata_type = 'player_type' left join systemdata batting on p.direction = batting.id and batting.systemdata_type = 'batting_style' left join systemdata bowling on p.bowling_direction = bowling.id and bowling.systemdata_type = 'bowling_style'";
	if($recentlyupdated !== false && is_numeric($recentlyupdated)){
		$sql .= " WHERE p.ut >= DATE_SUB(CURDATE(), INTERVAL ".$recentlyupdated." DAY)";
	}
//        else{
//            $sql .= paginationSQL($page, 50);
//        }
	$players = $DB->select_v2($sql);
	foreach($players as $p){
		$data = $DB->select("select distinct teamid, p.name as team_name from event_playerex left join participant p on p.id = event_playerex.teamid where p.participant_type = 'team' AND event_playerex.playerid=".$p[id]." and event_playerex.active='1'");
		$teams = array_column($data, 'team_name');
                $player = "<player id=\"" . textf($p[id]) ."\" firstname=\"" . textf($p[firstname]) ."\" lastname=\"" . textf($p[lastname]) ."\" name=\"" . textf($p[name]) ."\" country_id=\"$p[country]\" country_name=\"" . textf($p[country_name]) ."\" gender=\"" . textf($p[gender_name]) ."\" active=\"$p[active]\" dob=\"" . textf($p[dob]) ."\"  player_type=\"" . textf($p[player_type]) ."\" weight=\"$p[weight]\" height=\"$p[height]\" batting_style=\"" . textf($p[batting_style]) ."\" bowling_style=\"" . textf($p[bowling_style]) ."\" nick=\"" . textf($p[nick]) ."\" birth_place=\"" . textf($p[place_birth]) ."\" education=\"" . textf($p[education]) ."\" >";
                
                $playerBattingStats = $DB->select_v2("Select * from player_batting_stats where player_id = " . $p[id]);
                $player .= "<batting_stats>";
                foreach($playerBattingStats as $pBatStat){
                    $player .= "<stats 
                        type=\"" . textf($pBatStat['type']) ."\"
                        matches=\"" . textf($pBatStat['matches']) ."\"
                        innings=\"" . textf($pBatStat['innings']) ."\"
                        notouts=\"" . textf($pBatStat['notouts']) ."\"
                        runs=\"" . textf($pBatStat['runs']) ."\"
                        highestscores=\"" . textf($pBatStat['highestscores']) ."\"
                        battingavg=\"" . textf($pBatStat['battingavg']) ."\"
                        ballsfaced=\"" . textf($pBatStat['ballsfaced']) ."\"
                        strikerate=\"" . textf($pBatStat['strikerate']) ."\"
                        centuries=\"" . textf($pBatStat['centuries']) ."\"
                        fifties=\"" . textf($pBatStat['fifties']) ."\"
                        fours=\"" . textf($pBatStat['fours']) ."\"
                        sixes=\"" . textf($pBatStat['sixes']) ."\"
                        catches=\"" . textf($pBatStat['catches']) ."\"
                        stumps=\"" . textf($pBatStat['stumps']) ."\"
                        ></stats>";
                }
                $player .= "</batting_stats>";
                
                $playerBowlingStats = $DB->select_v2("Select * from player_bowling_stats where player_id = " . $p[id]);
                $player .= "<bowling_stats>";
                foreach($playerBowlingStats as $pBowlStat){
                    $player .= "<stats 
                        type=\"" . textf($pBowlStat['type']) ."\"
                        matches=\"" . textf($pBowlStat['matches']) ."\"
                        innings=\"" . textf($pBowlStat['innings']) ."\"
                        balls=\"" . textf($pBowlStat['balls']) ."\"
                        runs=\"" . textf($pBowlStat['runs']) ."\"
                        wickets=\"" . textf($pBowlStat['wickets']) ."\"
                        bestballing=\"" . textf($pBowlStat['bestballing']) ."\"
                        bestballmatch=\"" . textf($pBowlStat['bestballmatch']) ."\"
                        bowlingavg=\"" . textf($pBowlStat['bowlingavg']) ."\"
                        economyrate=\"" . textf($pBowlStat['economyrate']) ."\"
                        bowlingstrikerate=\"" . textf($pBowlStat['bowlingstrikerate']) ."\"
                        wkt4=\"" . textf($pBowlStat['wkt4']) ."\"
                        wkt5=\"" . textf($pBowlStat['wkt5']) ."\"
                        wkt10=\"" . textf($pBowlStat['wkt10']) ."\"
                        ></stats>";
                }
                $player .= "</bowling_stats>";
                        
                $player .= "<played_teams>";
                foreach($data as $team){
                    $player .= "<team 
                        id=\"" . textf($team['teamid']) ."\"
                        name=\"" . textf($team['team_name']) ."\"
                        ></team>";
                }
                $player .= "</played_teams>";
                
                $player .= "</player>";
                
		echo $player;
	}
	echo "</players>";
} elseif($cmd == "ranking"){
	$rankId = isset($_GET["rank_id"]) ? $_GET["rank_id"] : 6;
        $rankingData = $DB->select("select * from ranking_data where rankFK=".$rankId." and active='1' order by rank");
	echo "<cricket>";
	echo "<ranking title=\"".getName("ranking", $rankId)."\" id='$rankId' >";
	foreach($rankingData as $ranking){
            if(in_array($rankId, [3,6,22,30,31])){
                echo "<team no=\"".$ranking["rank"]."\" id='$ranking[participantFK]' name=\"".getNamep("team",$ranking[participantFK])."\" points=\"".$ranking["points"]."\" rating=\"".$ranking["rating"]."\" matches=\"".$ranking["best"]."\" />";
            }else{
                echo "<player no=\"".$ranking["rank"]."\" id='$ranking[participantFK]' name=\"".getplayer($ranking[participantFK])."\" points=\"".$ranking["points"]."\" best=\"".$ranking["best"]."\" rating=\"".$ranking["rating"]."\" />";
            }
		
	}
	echo "</ranking>";
} elseif ($cmd == "current_players") {
    $recentlyupdated = isset($_GET["recentlyupdated"]) ? $_GET["recentlyupdated"] : false;
    echo "<cricket>";
    echo "<currentPlayers>";
    $sql = "SELECT cp.player_id, CONCAT(p.firstname,' ',p.lastname) AS playerName, cp.team_id, pr.name AS teamName, cp.type , cp.is_active FROM current_players AS cp LEFT JOIN player AS p ON p.id = cp.player_id LEFT JOIN participant AS pr ON pr.id = cp.team_id WHERE pr.participant_type = 'team'";
    if ($_GET['team_id']) {
        $andwhere = ' AND cp.team_id= ' . $_GET['team_id'];
        $sql .= $andwhere;
    }
    if ($_GET['type']) {
        $andwhere = ' AND cp.type= "' . $_GET['type'] . '"';
        $sql .= $andwhere;
    }
    if ($recentlyupdated !== false && is_numeric($recentlyupdated)) {
        $sql .= " AND cp.created_at >= DATE_SUB(CURDATE(), INTERVAL " . $recentlyupdated . " DAY)";
    }
    $current_players = $DB->select($sql);
    foreach ($current_players as $cp) {
        $current_player = "<current_player player_id=\"" . textf($cp[player_id])."\" player_name=\"" . textf($cp[playerName])."\" team_id=\"" . textf($cp[team_id])."\" team_name=\"" . textf($cp[teamName])."\" type=\"" . textf($cp[type])."\" active=\"" . textf($cp['is_active'])."\" />";
        echo $current_player;
    }
    echo "</currentPlayers>";
} elseif ($cmd != 'player_data' && $cmd != 'team_info' && $cmd != 'team_detail_info' && $cmd != 'match_info' && $cmd != 'tournament_match' && $cmd != 'match_commentary' && $cmd != 'match_fixture' && $cmd != 'team_flags' && $cmd != 'squad_detail' && $cmd != 'tournamentlist' ) {
	$block_string = ""; // means allowed tournament list
	$block = blocklist($data[0]['id']);
	if($block){
		$block_string = " and tournament NOT IN (".$block.")";
	}
        $whitelist = whitelist($data[0]['id']);
        if($whitelist){
		$block_string .= " and tournament IN (".$whitelist.")";            
        }
                
	if($cmd == "period"){
		$date_string = "((gt between '$fromdate 00:00:00' and '$todate 23:59:59') or
		(match_type IN (2,33) and '$fromdate' between gt and date_add(gt, interval +5 day)) or
		(match_type IN (3,12,36) and '$fromdate' between gt and date_add(gt, interval +2 day)) or
		(match_type IN (5,13,42,52) and '$fromdate' between gt and date_add(gt, interval +3 day)) or
		(match_type=12 and '$fromdate' between gt and date_add(gt, interval +2 day)) or
		(match_type=37 and '$fromdate' between gt and date_add(gt, interval +1 day))) $block_string";
	} elseif($cmd == "since"){
		$date_string = " e.ut > '$timestamp' $block_string";
	} elseif($cmd == "live"){
		$date_string = " (left(gt,10) = '$date' or
                ('".date("Y-m-d H:i:s")."' between gt and date_add(gt, interval +1 day)) or
		(match_type IN (2,33) and '$date' between gt and date_add(gt, interval +5 day)) or
		(match_type IN (3,12,36) and '$date' between gt and date_add(gt, interval +2 day)) or
		(match_type IN (5,13,42,52) and '$date' between gt and date_add(gt, interval +3 day)) or
		(match_type=12 and '$date' between gt and date_add(gt, interval +2 day)) or
		(match_type=37 and '$date' between gt and date_add(gt, interval +1 day))) $block_string and (status NOT IN (1,7,8,39) OR (status = 1 AND toss_win <> '') OR finished_ut > '".date("Y-m-d H:i:s", time()-1800)."')";
	} else {
		$date_string = " (left(gt,10) = '$date' or
		(match_type IN (2,33) and '$date' between gt and date_add(gt, interval +5 day)) or
		(match_type IN (3,12,36) and '$date' between gt and date_add(gt, interval +2 day)) or
		(match_type IN (5,13,42,52) and '$date' between gt and date_add(gt, interval +3 day)) or
		(match_type=12 and '$date' between gt and date_add(gt, interval +2 day)) or
		(match_type=37 and '$date' between gt and date_add(gt, interval +1 day))) $block_string";
	}

	echo "<cricket>";
	$xwhere = '';
	if($user == "cricket365"){
		$xwhere = " p.se NOT IN ('pakistan','west_indies','australia','england','new_zealand') and ";
	}
	if($tournament){
		$matches = $DB->select("select e.id, e.gt, e.ut, e.finished_ut,e.finished_date,  e.match_type, e.match_no, e.ht_id, e.at_id, e.time_difference, e.tournament,
		e.status, e.stadium, e.match_no, e.season, e.umpire1, e.umpire2, e.umpiretv, e.referee, e.match_time, e.comment, e.toss_win, e.winner, e.cricinfo_url, e.crawler,e.pitch_batting,e.pitch_bowling,
		e.elected, e.live, e.followon, e.live, e.manofmatch, e.LiveStreamEmbedcode,e.highlights,e.manofmatch2, e.condition_wind, e.condition_humidity, e.condition_pressure, e.condition_dewpoint, e.condition_visibility, e.temperature_current, e.temperature_overnight, e.active, e.attendance, e.batsman_of_match, e.bowler_of_match, e.batsman_of_match_id, e.bowler_of_match_id, e.betway_id from event e join participant p on e.tournament=p.id and p.participant_type='tournament'
		where tournament IN ($tournament) and $xwhere ".$date_string);
	} elseif($match) {
		$matches = $DB->select("select e.id, e.gt,e.LiveStreamEmbedcode,e.highlights, e.ut, e.finished_ut,e.finished_date, e.match_type, e.match_no, e.ht_id, e.at_id, e.time_difference, e.tournament,
		e.status, e.stadium, e.match_no, e.season, e.umpire1, e.umpire2, e.umpiretv, e.referee, e.match_time, e.comment, e.toss_win, e.winner, e.cricinfo_url,e.crawler,e.pitch_batting,e.pitch_bowling,
		e.elected, e.live, e.followon, e.live, e.manofmatch, e.manofmatch2, e.condition_wind, e.condition_humidity, e.condition_pressure, e.condition_dewpoint, e.condition_visibility, e.temperature_current, e.temperature_overnight, e.active, e.attendance, e.batsman_of_match, e.bowler_of_match, e.batsman_of_match_id, e.bowler_of_match_id, e.betway_id, e.LiveStreamEmbedcode from event e join participant p on e.tournament=p.id and p.participant_type='tournament'
		where $xwhere e.id IN ($match)");
	} else {
		$matches = $DB->select("select e.id, e.gt, e.LiveStreamEmbedcode,e.highlights,e.ut, e.finished_ut,e.finished_date, e.match_type, e.match_no, e.ht_id, e.at_id, e.time_difference, e.tournament,
		e.status, e.stadium, e.match_no, e.season, e.umpire1, e.umpire2, e.umpiretv, e.referee, e.match_time, e.comment, e.toss_win, e.winner, e.cricinfo_url,e.crawler,e.pitch_batting,e.pitch_bowling,
		e.elected, e.live, e.followon, e.live, e.manofmatch, e.manofmatch2, e.condition_wind, e.condition_humidity, e.condition_pressure, e.condition_dewpoint, e.condition_visibility, e.temperature_current, e.temperature_overnight, e.active, e.attendance, e.batsman_of_match, e.bowler_of_match, e.batsman_of_match_id, e.bowler_of_match_id, e.betway_id from event e join participant p on e.tournament=p.id and p.participant_type='tournament'
		where $xwhere ".$date_string);
	}
	for($m =0; $m < count($matches); $m++){
		if($matches[$m]["live"] == 2 && $matches[$m]['status'] == 1){
			$status = "Result only";
		} else {
			$status = getName("status", $matches[$m]['status']);
		}
		$current_ing = 0;
		$current_xing = 0;
		$current_team = 0;
                $matchFormat = getMatchFormat(getName("match_type", $matches[$m]['match_type']));
                $is100BallMatch = ($matchFormat == "100balls");
                $ballsPerOver = $is100BallMatch ? 5 : 6;
                
		$xres = $DB->select("select inning, data from stats_settings where eventid=".$matches[$m]["id"]." and stats_type='batting_team' and data <> '0' order by stats_settings.inning desc");
		if(!empty($xres)){
			$current_ing = $xres[0]["inning"];
			$current_team = $xres[0]["data"];
			if($current_ing == 1 || $current_ing == 2){
				$current_xing = 1;
			} else {
				$current_xing = 2;
			}
		}
		$std = new stadium($matches[$m]['stadium']);
		$std->load();
		$tour = new tournament($matches[$m]['tournament']);
		$tour->load();
		if($matches[$m]["active"] == 0){ ?>
<game id="<?php echo $matches[$m]['id'] ?>" gmt_datetime="<?php echo $matches[$m]['gt'] ?>" local_datetime="<?php echo date("Y-m-d H:i:s", strtotime($matches[$m]['gt']) + ($matches[$m]['time_difference']*3600)) ?>" updated="<?php echo $matches[$m]['ut'] ?>" finished_ut="<?php echo $matches[$m]['finished_ut'] ?>"  finished_date="<?php echo $matches[$m]['finished_date'] ?>" status="<?php echo $status ?>" matchtype="<?php echo getName("match_type", $matches[$m]['match_type']) ?>" matchno="<?php echo getName("match_no", $matches[$m]['match_no']) ?>" season="<?php echo getName("season", $matches[$m]['season']) ?>" season_id="<?php echo $matches[$m]['season'] ?>" lights="<?php echo getName("match_time",$matches[$m]['match_time']) ?>" comment="This Event has been Deleted" coverage="<?php echo getName("live", $matches[$m]['live']) ?>" manofmatch="" manofmatch_id="" batsman_of_match="" bowler_of_match="" active="0" attendance="0" winner="0" ht_score="" at_score="" bw_id="0" crawler="<?php echo $matches[$m]['crawler']; ?>" pitch_batting="<?php echo $matches[$m]['pitch_batting']; ?>" pitch_bowling="<?php echo $matches[$m]['pitch_bowling']; ?>" format="<?php echo $matchFormat; ?>">
<tournament id="<?php echo $matches[$m]['tournament'] ?>" name="<?php echo str_replace("&","&amp;", $tour->name) ?>" country="<?php echo getInt("tournament",$matches[$m]['tournament']) ?>" gender="<?php echo $tour->gender ?>" category="<?php echo $tour->categ ?>" />
<?php	} else {
	$ht_summary = array();
	$at_summary = array();
	$score_summary = $DB->select("select inning, teamid, score, wicket, overs, ing_declare from event_team_result where eventid=".$matches[$m]['id']." order by inning");
	for($ss =0; $ss < sizeof($score_summary); $ss++){
		$tmp_sum = $score_summary[$ss]["score"]."/".$score_summary[$ss]["wicket"];//." (".$score_summary[$ss]["overs"].")";
		if($score_summary[$ss]["teamid"] == $matches[$m]["ht_id"]){
			$ht_summary[] = $tmp_sum;
		} else {
			$at_summary[] = $tmp_sum;
		}
	}
?>
<game id="<?php echo $matches[$m]['id'] ?>" gmt_datetime="<?php echo $matches[$m]['gt'] ?>" local_datetime="<?php echo date("Y-m-d H:i:s", strtotime($matches[$m]['gt']) + ($matches[$m]['time_difference']*3600)) ?>" updated="<?php echo $matches[$m]['ut'] ?>" finished_ut="<?php echo $matches[$m]['finished_ut'] ?>" finished_date="<?php echo $matches[$m]['finished_date'] ?>" status="<?php echo $status ?>" matchtype="<?php echo getName("match_type", $matches[$m]['match_type']) ?>" matchno="<?php echo getName("match_no", $matches[$m]['match_no']) ?>" season="<?php echo getName("season", $matches[$m]['season']) ?>" season_id="<?php echo $matches[$m]['season'] ?>" lights="<?php echo getName("match_time",$matches[$m]['match_time']) ?>" comment="<?php echo str_replace("&", "&amp;", $matches[$m]['comment']) ?>" coverage="<?php echo getName("live", $matches[$m]['live']) ?>" manofmatch="<?php echo str_replace("&", "&amp;", $matches[$m]['manofmatch2']) ?>" manofmatch_id="<?php echo $matches[$m]["manofmatch"] ?>" batsman_of_match="<?php echo str_replace("&", "&amp;", $matches[$m]["batsman_of_match"]) ?>" bowler_of_match="<?php echo str_replace("&", "&amp;", $matches[$m]["bowler_of_match"]) ?>" batsman_of_match_id="<?php echo $matches[$m]["batsman_of_match_id"] ?>" bowler_of_match_id="<?php echo $matches[$m]["bowler_of_match_id"] ?>" active="<?php echo $matches[$m]["active"] ?>" attendance="<?php echo $matches[$m]['attendance'] ?>" winner="<?php echo $matches[$m]['winner'] ?>" ht_score="<?php echo implode(" &amp; ", $ht_summary) ?>" at_score="<?php echo implode(" &amp; ", $at_summary) ?>" bw_id="<?php echo $matches[$m]["betway_id"] ?>" crawler="<?php echo $matches[$m]['crawler']; ?>"  pitch_batting="<?php echo $matches[$m]['pitch_batting']; ?>" pitch_bowling="<?php echo $matches[$m]['pitch_bowling']; ?>" format="<?php echo $matchFormat; ?>">
<tournament id="<?php echo $matches[$m]['tournament'] ?>" name="<?php echo str_replace("&","&amp;", $tour->name) ?>" country="<?php echo getInt("tournament",$matches[$m]['tournament']) ?>" gender="<?php echo $tour->gender ?>" category="<?php echo $tour->categ ?>" />
<stadium id="<?php echo $std->id ?>" name="<?php echo textf($std->name) ?>" city="<?php echo $std->city ?>" country="<?php echo getName("country", $std->country) ?>" end1="<?php echo $std->end1 ?>" end2="<?php echo $std->end2 ?>" capacity="<?php echo $std->crowd_capacity ?>" established="<?php echo $std->established ?>" />
<weather condition_wind="<?php echo str_replace("&", "&amp;", $matches[$m]['condition_wind']) ?>" condition_humidity="<?php echo str_replace("&", "&amp;", $matches[$m]['condition_humidity']) ?>" condition_pressure="<?php echo str_replace("&", "&amp;", $matches[$m]['condition_pressure']) ?>" condition_dewpoint="<?php echo str_replace("&", "&amp;", $matches[$m]['condition_dewpoint']) ?>" condition_visibility="<?php echo str_replace("&", "&amp;", $matches[$m]['condition_visibility']) ?>" temperature_current="<?php echo str_replace("&", "&amp;", $matches[$m]['temperature_current']) ?>" temperature_overnight="<?php echo str_replace("&", "&amp;", $matches[$m]['temperature_overnight']) ?>" />
<?php
	$dl = $DB->select("select active, method, comment, ing1_over, ing1_score, ing2_over, ing2_score from event_daylight where event_id=".$matches[$m]["id"]);
	if(empty($dl)){
		echo "<rpc active=\"0\" method=\"\" comment=\"\"></rpc>";
	} else {
		if(!$dl[0]["active"]){
			echo "<rpc active=\"0\" method=\"\" comment=\"\"></rpc>";
		} else {
			echo "<rpc active=\"1\" method=\"".$dl[0]["method"]."\" comment=\"".$dl[0]["comment"]."\">";
			if($dl[0]["ing1_over"]){
				if($dl[0]["ing1_score"])
					echo "<inn number=\"1\" overs=\"".( $is100BallMatch ? oversToBalls($dl[0]["ing1_over"]) : $dl[0]["ing1_over"])."\" scores=\"".$dl[0]["ing1_score"]."\" />";
				else
					echo "<inn number=\"1\" overs=\"".( $is100BallMatch ? oversToBalls($dl[0]["ing1_over"]) : $dl[0]["ing1_over"])."\" />";
			}
			if($dl[0]["ing2_over"]){
				if($dl[0]["ing2_score"])
					echo "<inn number=\"2\" overs=\"".( $is100BallMatch ? oversToBalls($dl[0]["ing2_over"]) : $dl[0]["ing2_over"])."\" target=\"".$dl[0]["ing2_score"]."\" />";
				else
					echo "<inn number=\"2\" overs=\"".( $is100BallMatch ? oversToBalls($dl[0]["ing2_over"]) : $dl[0]["ing2_over"])."\" />";
			}
			echo "</rpc>";
		}
	}
	if($matches[$m]['elected']){
		if($matches[$m]['toss_win'] == "A")
			$ts_team = getNamep("team", $matches[$m]['ht_id']);
		else
			$ts_team = getNamep("team", $matches[$m]['at_id']);
	?>
<toss win="<?php echo str_replace("&","&amp;",$ts_team) ?>" elected="<?php echo getName("elected", $matches[$m]['elected']) ?>" />

<?php
	if(($matches[$m]['toss_win'] == "A" && $matches[$m]['elected'] == 1) || ($matches[$m]['toss_win'] == "B" && $matches[$m]['elected'] == 2))
		$bat_first = "A";
	else
		$bat_first = "B";

	}
	?>

	<?php $code=$matches[$m]['LiveStreamEmbedcode'];
	$highlight = ($user == "planetcricket" || $user == "shinmafia") ? $matches[$m]['highlights'] : ''; //only show highlights to this customers

		echo "<LiveStreamEmbedcode  code=\"".textf($code)."\" />";
		echo "<highlights  code=\"".textf($highlight)."\" />";

	?>

<umpires>



<?php
	if(isset($matches[$m]['umpire1']) && $matches[$m]['umpire1']){
		$umpire1 = new player($matches[$m]['umpire1']);
		$umpire1->load();
	?>
<umpire id="<?php echo $matches[$m]['umpire1'] ?>" position="first" name="<?php echo $umpire1->name ?>" country="<?php echo getName("country", $umpire1->country) ?>" />
<?php	} ?>
<?php
	if(isset($matches[$m]['umpire2']) && $matches[$m]['umpire2']){
		$umpire2 = new player($matches[$m]['umpire2']);
		$umpire2->load();
	?>
<umpire id="<?php echo $matches[$m]['umpire2'] ?>" position="second" name="<?php echo $umpire2->name ?>" country="<?php echo getName("country", $umpire2->country) ?>" />
<?php	} ?>
<?php
	if(isset($matches[$m]['umpiretv']) && $matches[$m]['umpiretv']){
		$umpiretv = new player($matches[$m]['umpiretv']);
		$umpiretv->load();
	?>
<umpire id="<?php echo $matches[$m]['umpiretv'] ?>" position="tv_umpire" name="<?php echo $umpiretv->name ?>" country="<?php echo getName("country", $umpiretv->country) ?>" />
<?php	} ?>
<?php
	if(isset($matches[$m]['referee']) && $matches[$m]['referee']){
		$referee = new player($matches[$m]['referee']);
		$referee->load();
	?>
<umpire id="<?php echo $matches[$m]['referee'] ?>" position="referee" name="<?php echo $referee->name ?>" country="<?php echo getName("country", $referee->country) ?>" />
<?php	}
	$hteam = new team($matches[$m]['ht_id']);
	$hteam->load();
	$ateam = new team($matches[$m]['at_id']);
	$ateam->load();
	?>
</umpires>
<teams>
<team id="<?php echo $hteam->id ?>" name="<?php echo str_replace("&", "&amp;",$hteam->name) ?>" country="<?php echo getName("country",$hteam->country) ?>" code="<?php echo str_replace("&", "&amp;",str_replace("&amp;", "&",$hteam->code)) ?>">

<?php

$lineup_flage=false;
	$battingline_flage=false;
	$bowlingline_flage = false;
	$fallofwicket_flage = false;
	$parnerships_flage = false;
	$commentary_flage= false;

?>



<?php
	// $arrplayer = array();
	// $data3 = $DB->select("select distinct playerid, player_type from event_playerex where eventid=".$matches[$m]['id']." and teamid=".$hteam->id." and active='1'");
	// if(count($data3) > 0){
	// 	$lineup_flage = true;
	// }
	 ?>


	 <?php
// 	$Ascore = $DB->select("select inning, score, wicket, overs, ing_declare from event_team_result where eventid=".$matches[$m]['id']." and teamid=".$hteam->id." order by inning");
// 	for($s =0; $s < count($Ascore); $s++){
// 		if($Ascore[$s]["ing_declare"] == ""){
// 			$Ascore[$s]["ing_declare"] = 0;
// 		}
// 		if($bat_first == "A"){
// 			if($Ascore[$s]['inning'] == 2){
// 				$ing = 3;
// 			} elseif($Ascore[$s]['inning'] == 3){
// 				$ing = 5;
// 			} else {
// 				$ing = 1;
// 			}
// 		} else {
// 			if($Ascore[$s]['inning'] == 2){
// 				$ing = 4;
// 			} elseif($Ascore[$s]['inning'] == 3){
// 				$ing = 6;
// 			} else {
// 				$ing = 2;
// 			}
// 		}
// 		if($Ascore[$s]['inning'] == 1)
// 			$caption = "1ST INN";
// 		elseif($Ascore[$s]['inning'] == 2)
// 			$caption = "2ND INN";
// 		else
// 			$caption = "SO INN";
// 		$cbowler =0;
// 		$cbatsman = 0;
// 		$a1data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='bowler'");
// 		if(!empty($a1data))
// 			$cbowler = $a1data[0][0];
// 		$a2data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='cbatsman'");
// 		if(!empty($a2data))
// 			$cbatsman = $a2data[0][0];
// /*		if($Ascore[$s]['inning'] == 3)
// 			$ing_num = "Super";
// 		else*/
// 			$ing_num = $Ascore[$s]['inning'];
// 		$ing_order = $ing;
// 		if($matches[$m]["followon"] == "1"){
// 			if($ing_order == 3)
// 				$ing_order = 4;
// 			elseif($ing_order == 4)
// 				$ing_order = 3;
// 		}
// 		if($Ascore[$s]['overs'] > 0)
// 			$runrate = round(($Ascore[$s]["score"] / $Ascore[$s]['overs'])*100)/100;
// 		else
// 			$runrate = 0;
// 		$tscore = $Ascore[$s]['score'];
// 		$tover = $Ascore[$s]['overs'];
	?>
<?php
	// $extra = $DB->select("select wide, noball, bye, legbye, penalty from event_extra where eventid=".$matches[$m]['id']." and inning=".$Ascore[$s]['inning']." and teamid=".$hteam->id);
	//
	// if($ing == $current_ing || $activeonly == 0){

?>







	 <?php
	// $hs_batsman = array(0,0);
	// $inning = $DB->select("select event_playerex.id, event_playerex.playerid, score, balls, s4, s6,
	// sortorder, wicket_type, bowledby, catchby from event_playerex, event_batsman where
	// event_playerex.id=event_batsman.event_playerFK and
	// event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$hteam->id." and
	// event_playerex.inning=".$Ascore[$s]['inning']." and event_playerex.active='1' and
	// event_batsman.active='1' and event_batsman.inning=".$Ascore[$s]['inning']." order by sortorder");
	// for($i=0; $i < count($inning); $i++){
	// 	if($inning[$i]['playerid'] == $cbatsman) $act = 1; else $act = 0;
	// 	// echo "<batsman playerid=\"".$inning[$i]['playerid']."\" name=\"".getPlayer($inning[$i]['playerid'])."\" score=\"".$inning[$i]['score']."\" balls=\"".$inning[$i]['balls']."\" four=\"".$inning[$i]['s4']."\" six=\"".$inning[$i]['s6']."\" runrate=\"".($inning[$i]['balls']?number_format($inning[$i]['score']/$inning[$i]['balls']*100,2):0)."\" order=\"".$inning[$i]['sortorder']."\" active=\"$act\">";
	// 	// echo "<wicket type=\"".getName("wicket", $inning[$i]['wicket_type'])."\" bowler_id=\"".$inning[$i]['bowledby']."\"  bowler_name=\"".getPlayer($inning[$i]['bowledby'])."\" fielder_id=\"".$inning[$i]['catchby']."\"  fielder_name=\"".getPlayer($inning[$i]['catchby'])."\" />";
	// 	// echo "</batsman>";
	// 	if($inning[$i]['score'] > $hs_batsman[0]){
	// 		$hs_batsman[0] = $inning[$i]['score'];
	// 		$hs_batsman[1] = $inning[$i]['playerid'];
	// 	}
	// }
	// if(count($inning)){
	// 	$battingline_flage= true;
	// }
	// if($hs_batsman[0] > 0){
	// 	// echo "<best_batsman playerid=\"".$hs_batsman[1]."\" name=\"".getPlayer($hs_batsman[1])."\" score=\"".$hs_batsman[0]."\" />";
	// }
	?>





<?php
// 	$hs_bowler = array(0,0,0);
// 	$inning = $DB->select("select event_playerex.playerid, over, mdn, run , wkt, wide, noball, sortorder
// 	 from event_playerex, event_bowler  where
// 	event_playerex.id=event_bowler.event_playerFK and
// 	event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$ateam->id." and
// 	event_playerex.inning=".$Ascore[$s]['inning']." and event_playerex.active='1' and
// 	event_bowler.active='1' and event_bowler.inning=".$Ascore[$s]['inning']." order by sortorder");
// 	for($i=0; $i < count($inning); $i++){
// 		if($inning[$i]['playerid'] == $cbowler) $act=1; else $act = 0;
// 		$runrate = runrate($inning[$i]['run'], $inning[$i]['over']);
// 		// echo "<bowler playerid=\"".$inning[$i]['playerid']."\" name=\"".getPlayer($inning[$i]['playerid'])."\" overs=\"".$inning[$i]['over']."\" mdns=\"".$inning[$i]['mdn']."\" score=\"".$inning[$i]['run']."\" wickets=\"".$inning[$i]['wkt']."\" runrate=\"".$runrate."\" wide=\"".$inning[$i]['wide']."\" noball=\"".$inning[$i]['noball']."\" order=\"".$inning[$i]['sortorder']."\" active=\"$act\" />";
// 		$bowlingline_flage= true;
// 		if($inning[$i]['wkt'] > $hs_bowler[0]){
// 			$hs_bowler[0] = $inning[$i]['wkt'];
// 			$hs_bowler[1] = $inning[$i]['playerid'];
// 			$hs_bowler[2] = $runrate;
// 		}elseif($inning[$i]['wkt'] == $hs_bowler[0] && $runrate < $hs_bowler[2]){
// 			$hs_bowler[0] = $inning[$i]['wkt'];
// 			$hs_bowler[1] = $inning[$i]['playerid'];
// 			$hs_bowler[2] = $runrate;
// 		}
// /*		if($inning[$i]['playerid'] == $cbowler) $act = 1; else $act = 0;
// 		echo "<bowler playerid=\"".$inning[$i]['playerid']."\" name=\"".getPlayer($inning[$i]['playerid'])."\" overs=\"".$inning[$i]['over']."\" mdns=\"".$inning[$i]['mdn']."\" score=\"".$inning[$i]['run']."\" wickets=\"".$inning[$i]['wkt']."\" runrate=\"".runrate($inning[$i]['run'], $inning[$i]['over'])."\" wide=\"".$inning[$i]['wide']."\" noball=\"".$inning[$i]['noball']."\" order=\"".$inning[$i]['sortorder']."\" active=\"$act\" />";*/
// 	}
// 	if($hs_bowler[0] > 0){
// 		// // echo "<best_bowler playerid=\"".$hs_bowler[1]."\" name=\"".getPlayer($hs_bowler[1])."\" wickets=\"".$hs_bowler[0]."\" runrate=\"".$hs_bowler[2]."\" />";
// 	}
?>

<?php
	// $fall = $DB->select("select fow_score, fow_over, playerid, bowledby, wicket_type from event_playerex, event_batsman where
	// event_playerex.id=event_batsman.event_playerFK and
	// event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$hteam->id." and
	// event_playerex.inning=".$Ascore[$s]['inning']." and event_playerex.active='1' and
	// event_batsman.active='1' and event_batsman.inning=".$Ascore[$s]['inning']." and (fow_score > 0 or fow_over <> '') order by fow_score, fow_over");
	// for($f=0; $f < count($fall); $f++){
	// 	$fallofwicket_flage = true;
	// 	$fow_text_rs = $DB->select("select comment from stats_comment where eventid=".$matches[$m]['id']." and scoreFK != 91 and scoreFK IN (15, 21, 22, 23, 24, 25, 26, 33, 35, 39,40,41, 46, 50,55,56,57,58,59,60,61,62,63,64,65,36, 67,68,69,70,72,73,74,75,76,77,78,79,81,82,83,84,85,86,87,88,92,93,98,100,106,109) and over_ball like '".$fall[$f]['fow_over']."' and inning=".$ing_order);
	// 	if(empty($fow_text_rs)){
	// 		$fow_text = "";
	// 	} else {
	// 		$fow_text = textf($fow_text_rs[0]["comment"]);
	// 	}
	// 	// echo "<fallofwicket playerid=\"".$fall[$f]['playerid']."\"  name=\"".getPlayer($fall[$f]['playerid'])."\" score=\"".$fall[$f]['fow_score']."\" bowler=\"".getPlayer($fall[$f]['bowledby'])."\" bowler_id=\"".$fall[$f]['bowledby']."\" wicket=\"".getName("wicket", $fall[$f]["wicket_type"])."\" ball=\"".$fall[$f]['fow_over']."\" text=\"".$fow_text."\" number=\"".($f+1)."\" />";
	// }
	?>

	<?php
	// if($partnership) {
	// 	// echo '<parnerships>';
	// 	if($matches[$m]["live"] == 3){
	// 		$partner = array();
	// 		$cscore = 0;
	// 		$cballs = 0;
	// 		$temp_ing = $Ascore[$s]['inning'];
	// 		$data3 = $DB->select("select event_playerex.playerid, score, balls, sortorder, fow_score, fow_over from event_playerex, event_batsman where
	// 		event_playerex.id = event_playerFK and event_playerex.eventid=".$matches[$m]['id']." and teamid=".$hteam->id." and event_playerex.active='1' and event_batsman.active='1'
	// 		and event_playerex.inning='$temp_ing' and event_batsman.inning='$temp_ing' and wicket_type NOT IN(8,9) and fow_over <> '' order by fow_score");
	// 		for($e =0; $e < count($data3); $e++){
	// 			$partner[] = array($data3[$e]['fow_score']-$cscore, $data3[$e]['fow_over'], overtoball($data3[$e]['fow_over'])-$cballs, $data3[$e]['playerid']);
	// 			$cscore = $data3[$e]['fow_score'];
	// 			$cballs = overtoball($data3[$e]['fow_over']);
	// 		}
	// 		if(sizeof($partner) < 10){
	// 			$partner[] = array($tscore-$cscore, $tover, overtoball($tover)-$cballs, 0);
	// 		}
	// 		$outp = array();
	// 		$partnership_data = array();
	// 		for($f =0; $f < count($partner); $f++){
	// 			$temp = array($partner[$f][0], $partner[$f][1], $partner[$f][2]);
	// 			if(sizeof($outp) > 0){
	// 				$sql = "select event_playerex.playerid from event_playerex, event_batsman where event_playerex.id = event_playerFK and event_playerex.eventid=".$matches[$m]['id']." and
	// 				teamid=".$hteam->id." and event_playerex.active='1' and event_batsman.active='1' and event_playerex.inning='$temp_ing' and
	// 				event_batsman.inning='$temp_ing' and playerid NOT IN(".implode(",",$outp).") and sortorder <= '".($f+2)."' order by sortorder";
	// 			} else {
	// 				$sql = "select event_playerex.playerid from event_playerex, event_batsman where event_playerex.id = event_playerFK and event_playerex.eventid=".$matches[$m]['id']." and
	// 				teamid=".$hteam->id." and event_playerex.active='1' and event_batsman.active='1' and event_playerex.inning='$temp_ing' and
	// 				event_batsman.inning='$temp_ing' and sortorder <= '".($f+2)."' order by sortorder";
	// 			}
	// 			$data4= $DB->select($sql);
	// 			for($g=0; $g < count($data4);$g++){
	// 				$temp[] = $data4[$g][0];
	// 			}
	// 			$partnership_data[] = $temp;
	// 			$outp[] = $partner[$f][3];
	// 		}
	// 		$partnership_data_string = "";
	// 		$extra = array(16,18,20,28,34,35,37,38,67,68,69,70);
	// 		for($i=0;$i < count($partnership_data); $i++){
	// 			$score = 0;
	// 			$balls = 0;
	// 			$parnerships_flage=true;
	// 			if(isset($partnership_data[$i][3]) && isset($partnership_data[$i][4])){
	// 				if(isset($partnership_data[$i-1]))
	// 					$last_ps_ball = $partnership_data[$i-1][1]; //$last_ps_ball = $partnership_data[$i-1][1] - .1;
	// 				else
	// 					$last_ps_ball = 0;
	// 				$data = $DB->select("select scoreFK, en from stats_comment, systemdata where systemdata_type='stats_score' and stats_comment.scoreFK=systemdata.id
	// 				and eventid=".$matches[$m]['id']." and inning=".$ing." and stats_comment.active=1 and batsman=".$partnership_data[$i][3]." and
	// 				over_ball > '".$last_ps_ball."' and over_ball < '".($partnership_data[$i][1]+.1)."' and (player1='' or player1=batsman)");
	// 				for($j=0; $j < count($data); $j++){
	// 					$score +=  $data[$j][1];
	// 					if(!in_array($data[$j][0], $extra))
	// 						$balls++;
	// 				}
	// 				$partnership_data[$i][5] = $score;
	// 				$partnership_data[$i][6] = $balls;
	// 				$score = 0;
	// 				$balls = 0;
	// 				if(isset($partnership_data[$i-1]))
	// 					$last_ps_ball = $partnership_data[$i-1][1];
	// 				else
	// 					$last_ps_ball = 0;
	// 				$data = $DB->select("select scoreFK, en from stats_comment, systemdata where systemdata_type='stats_score' and stats_comment.scoreFK=systemdata.id
	// 				and eventid=".$matches[$m]['id']." and inning=".$ing." and stats_comment.active=1 and batsman=".$partnership_data[$i][4]." and
	// 				over_ball > '".$last_ps_ball."' and over_ball < '".($partnership_data[$i][1]+.1)."' and (player1='' or player1=batsman)");
	// 				for($j=0; $j < count($data); $j++){
	// 					$score +=  $data[$j][1];
	// 					if(!in_array($data[$j][0], $extra))
	// 						$balls++;
	// 				}
	// 				$partnership_data[$i][7] = $score;
	// 				$partnership_data[$i][8] = $balls;
	// 				$ex = $partnership_data[$i][0] - $partnership_data[$i][5] - $partnership_data[$i][7];
	// 				if($ex < 0){
	// 					$ex = 0;
	// 					$partnership_data[$i][0] = $partnership_data[$i][5] + $partnership_data[$i][7];
	// 				}
	// 				if($partnership_data[$i][2] < 0) $partnership_data[$i][2] = 0;
	// 				// echo '<partnetship player1="'.getPlayerLF($partnership_data[$i][3]).'" player2="'.getPlayerLF($partnership_data[$i][4]).'" player1_id="'.$partnership_data[$i][3].'" player2_id="'.$partnership_data[$i][4].'" p1_run="'.$partnership_data[$i][5].'" p1_ball="'.$partnership_data[$i][6].'" p2_run="'.$partnership_data[$i][7].'" p2_ball="'.$partnership_data[$i][8].'" total_run="'.$partnership_data[$i][0].'" total_ball="'.$partnership_data[$i][2].'" extra="'.$ex.'" />';
	// 			}
	// 		}
	// 	}
	// 	// echo '</parnerships>';
	// }
	// if($commentary){
	// 	$xml = "";
	// 	$data = $DB->select("select id, over_ball, batsman, bowler, scoreFK, comment from stats_comment where eventid=".$matches[$m]['id']." and active='1' and inning='".$ing_order."' order by over_ball desc, id desc limit 0, $cmlimit");
	// 	for($rc =0; $rc < count($data); $rc++){
	// 		$commentary_flage = true;
	// 		$pdata = $DB->select("select name, es, en, de, dk, se, no from systemdata where systemdata_type='stats_score' and id=".$data[$rc]['scoreFK']);
	// 		$sum_tmp = $pdata[0][2] + $pdata[0][3] + $pdata[0][4] + $pdata[0][5] + $pdata[0][6];
	// 		$xml .= "<com id=\"".$data[$rc]['id']."\" over=\"".$data[$rc]['over_ball']."\" bowler=\"".getplayer($data[$rc]['bowler'])."\" batsman=\"".getplayer($data[$rc]['batsman'])."\" bowler_id=\"".$data[$rc]['bowler']."\" batsman_id=\"".$data[$rc]['batsman']."\" score=\"".str_replace("&", "&amp;", $pdata[0][0])."\" d_score=\"".$pdata[0][1]."\" text=\"".textf($data[$rc]['comment'])."\" sc=\"".$sum_tmp."\" type_id=\"".$data[$rc]['scoreFK']."\" />";
	// 	}
	// 	// echo "<commentary>";
	// 	// echo $xml;
	// 	// echo "</commentary>";
	// }

	?>


<?php
// }}
?>



		<?php
		// echo "<datacoverage>";
			// if($lineup_flage){
			// 	echo"<lineupflage flage ='true' />";
			// }else{
			// 	echo"<lineupflage flage ='false' />";
			// }
			//
			// if($commentary_flage){
			// 	echo"<commentariesFlage flage ='true' />";
			// }else{
			// 	echo"<commentariesFlage flage ='false' />";
			// }
			//
			// if($parnerships_flage){
			// 	echo"<partnershipsFlage flage ='true' />";
			// }else{
			// 	echo"<partnershipsFlage flage ='false' />";
			// }
			//
			// if($battingline_flage){
			// 	echo"<battingLineStatisticsFlage flage ='true' />";
			// }else{
			// 	echo"<battingLineStatisticsFlage flage ='false' />";
			// }
			//
			// if($bowlingline_flage){
			// 	echo"<bowlingLineStatisticsFlage flage ='true' />";
			// }else{
			// 	echo"<bowlingLineStatisticsFlage flage ='false' />";
			// }
			//
			// if($fallofwicket_flage){
			// 	echo"<fallOfWicketsFlage flage ='true' />";
			// }else{
			// 	echo"<fallOfWicketsFlage flage ='false' />";
			// }
// echo "</datacoverage>";
		?>



<?php


		if($squad == 1){
		echo "<squad>";
		$tmp = $DB->select("select player.id, trim(concat(firstname, ' ', lastname)) as name, pt.name player_type from tournament_team_squad tts join player on tts.player_id=player.id join systemdata pt on pt.systemdata_type = 'player_type' and pt.id = player.player_type where tour_id=".$matches[$m]["tournament"]." and team_id=".$matches[$m]["ht_id"]." order by name");
		for($ctmp=0; $ctmp < sizeof($tmp); $ctmp++){
			echo "<player id=\"".$tmp[$ctmp]["id"]."\" name=\"".$tmp[$ctmp]["name"]."\" position=\"".str_replace("&","&amp;",$tmp[$ctmp]["player_type"])."\" />";
		}
		echo "</squad>";
	}
	if($lineup){
	?><lineups><?php
	$arrplayer = array();
	$data3 = $DB->select("select distinct event_playerex.playerid, event_playerex.player_type,systemdata.name as batting_style  from event_playerex
		LEFT JOIN player ON (player.id = event_playerex.playerid)
		LEFT JOIN systemdata ON (systemdata.id = player.direction and systemdata.systemdata_type='batting_style')
	where event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$hteam->id." and event_playerex.active='1'");
	if(count($data3) > 0){
		$lineup_flage = true;
		for($rc =0; $rc < count($data3); $rc++){
			$pname = getplayer($data3[$rc]["playerid"]);
			if(!in_array($pname, $arrplayer)){
				$arrplayer[] = $pname;
				if($data3[$rc]["player_type"] > 0){
					$extraI = " (".getName("p_type", $data3[$rc]["player_type"]).")";
				} else{
					$extraI = "";
				}
					$bating_style = $data3[$rc]["batting_style"]?$data3[$rc]["batting_style"]:'null';
				echo "<lineup name=\"".$pname.$extraI."\" id=\"".$data3[$rc]["playerid"]."\" batting_style=\"".$bating_style."\" />";
			}
		}
	} ?></lineups><?php	}	?>
<innings>
<?php
	$Ascore = $DB->select("select inning, score, wicket, overs, ing_declare from event_team_result where eventid=".$matches[$m]['id']." and teamid=".$hteam->id." order by inning");
	for($s =0; $s < count($Ascore); $s++){
		if($Ascore[$s]["ing_declare"] == ""){
			$Ascore[$s]["ing_declare"] = 0;
		}
		if($bat_first == "A"){
			if($Ascore[$s]['inning'] == 2){
				$ing = 3;
			}
			elseif($Ascore[$s]['inning'] == 3){
				$ing = 6;
			}
			elseif($Ascore[$s]['inning'] == 7){
				$ing = 7;
			}
			else {
				$ing = 1;
			}
		} else {
			if($Ascore[$s]['inning'] == 2){
				$ing = 4;
			}
			elseif($Ascore[$s]['inning'] == 3){
				$ing = 5;
			}
			elseif($Ascore[$s]['inning'] == 7){
				$ing = 8;
			}
			else {
				$ing = 2;
			}
		}
		if($Ascore[$s]['inning'] == 1)
			$caption = "1ST INN";
		elseif($Ascore[$s]['inning'] == 2)
			$caption = "2ND INN";
		else
			$caption = "SO INN";
		$cbowler =0;
		$cbatsman = 0;
		$a1data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='bowler'");
		if(!empty($a1data))
			$cbowler = $a1data[0][0];
		$a2data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='cbatsman'");
		if(!empty($a2data))
			$cbatsman = $a2data[0][0];
/*		if($Ascore[$s]['inning'] == 3)
			$ing_num = "Super";
		else*/
			$ing_num = $Ascore[$s]['inning'];
		$ing_order = $ing;
		if($matches[$m]["followon"] == "1"){
			if($ing_order == 3)
				$ing_order = 4;
			elseif($ing_order == 4)
				$ing_order = 3;
		}
		if($Ascore[$s]['overs'] > 0){
		$n = $Ascore[$s]['overs'];
		$whole = floor($n);      // 1
		$fraction = ($n - $whole)*10; // .25

		$total_balls = ($whole*6)+$fraction;

			$runrate = round(($Ascore[$s]["score"] / $total_balls)*$ballsPerOver,2);}
			// $runrate = round(($Ascore[$s]["score"] / $Ascore[$s]['overs'])*100)/100;
		else
		{	$runrate = 0;}
		$tscore = $Ascore[$s]['score'];
		$tover = $Ascore[$s]['overs'];
	?><inning number="<?php echo $ing_num ?>" caption="<?php echo str_replace("&", "&amp;", $hteam->code)." ".$caption ?>" order="<?php echo $ing_order ?>" active="<?php echo ($ing == $current_ing)?"1":"0"; ?>" score="<?php echo $Ascore[$s]['score'] ?>" overs="<?php echo ( $is100BallMatch ? oversToBalls($Ascore[$s]['overs'],true) : $Ascore[$s]['overs']) ?>" wickets="<?php echo $Ascore[$s]['wicket'] ?>" declare="<?php echo $Ascore[$s]["ing_declare"] ?>" runrate="<?php echo $runrate ?>" >
<?php
	$extra = $DB->select("select wide, noball, bye, legbye, penalty from event_extra where eventid=".$matches[$m]['id']." and inning=".$Ascore[$s]['inning']." and teamid=".$hteam->id);
	if(!empty($extra))
		echo "<extra sum=\"".($extra[0]['wide'] + $extra[0]['noball'] + $extra[0]['bye'] + $extra[0]['legbye']+$extra[0]['penalty'])."\" wide=\"".$extra[0]['wide']."\" noball=\"".$extra[0]['noball']."\" bye=\"".$extra[0]['bye']."\" legbye=\"".$extra[0]['legbye']."\" penalty=\"".$extra[0]['penalty']."\"/>";
	else
		echo "<extra sum=\"0\" wide=\"0\" noball=\"0\" bye=\"0\" legbye=\"0\" penalty=\"0\"/>";
	if($ing == $current_ing || $activeonly == 0){
		if($battingline){
?>
<battingline>
<?php
	$hs_batsman = array(0,0);
	$inning = $DB->select("select event_playerex.id, event_playerex.playerid, score, balls, s4, s6,
	sortorder, wicket_type, bowledby, catchby from event_playerex, event_batsman where
	event_playerex.id=event_batsman.event_playerFK and
	event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$hteam->id." and
	event_playerex.inning=".$Ascore[$s]['inning']." and event_playerex.active='1' and
	event_batsman.active='1' and event_batsman.inning=".$Ascore[$s]['inning']." order by sortorder");
	for($i=0; $i < count($inning); $i++){
		if($inning[$i]['playerid'] == $cbatsman) $act = 1; else $act = 0;
		echo "<batsman playerid=\"".$inning[$i]['playerid']."\" name=\"".getPlayer($inning[$i]['playerid'])."\" score=\"".$inning[$i]['score']."\" balls=\"".$inning[$i]['balls']."\" four=\"".$inning[$i]['s4']."\" six=\"".$inning[$i]['s6']."\" runrate=\"".($inning[$i]['balls'] ? number_format($inning[$i]['score']/$inning[$i]['balls']*100,2):0)."\" order=\"".$inning[$i]['sortorder']."\" active=\"$act\">";
		echo "<wicket type=\"".getName("wicket", $inning[$i]['wicket_type'])."\" bowler_id=\"".$inning[$i]['bowledby']."\"  bowler_name=\"".getPlayer($inning[$i]['bowledby'])."\" fielder_id=\"".$inning[$i]['catchby']."\"  fielder_name=\"".getPlayer($inning[$i]['catchby'])."\" />";
		echo "</batsman>";
		if($inning[$i]['score'] > $hs_batsman[0]){
			$hs_batsman[0] = $inning[$i]['score'];
			$hs_batsman[1] = $inning[$i]['playerid'];
		}
	}
	if(count($inning)){
		$battingline_flage= true;
	}
	if($hs_batsman[0] > 0){
		echo "<best_batsman playerid=\"".$hs_batsman[1]."\" name=\"".getPlayer($hs_batsman[1])."\" score=\"".$hs_batsman[0]."\" />";
	}
	?>
</battingline>
<?php
	}
	if($bowlingline){
	?>
<bowlingline>
<?php
	$hs_bowler = array(0,0,0);
	$inning = $DB->select("select event_playerex.playerid, over, mdn, run , wkt, wide, noball, sortorder
	 from event_playerex, event_bowler  where
	event_playerex.id=event_bowler.event_playerFK and
	event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$ateam->id." and
	event_playerex.inning=".$Ascore[$s]['inning']." and event_playerex.active='1' and
	event_bowler.active='1' and event_bowler.inning=".$Ascore[$s]['inning']." order by sortorder");
	for($i=0; $i < count($inning); $i++){
		if($inning[$i]['playerid'] == $cbowler) $act=1; else $act = 0;
		$runrate = runrate($inning[$i]['run'], ($is100BallMatch ? convertTo5BallOvers($inning[$i]['over']): $inning[$i]['over']));
		echo "<bowler playerid=\"".$inning[$i]['playerid']."\" name=\"".getPlayer($inning[$i]['playerid'])."\" overs=\"".( $is100BallMatch ? oversToBalls($inning[$i]['over']) : $inning[$i]['over'])."\" mdns=\"".$inning[$i]['mdn']."\" score=\"".$inning[$i]['run']."\" wickets=\"".$inning[$i]['wkt']."\" runrate=\"".$runrate."\" wide=\"".$inning[$i]['wide']."\" noball=\"".$inning[$i]['noball']."\" order=\"".$inning[$i]['sortorder']."\" active=\"$act\" />";
		$bowlingline_flage= true;
		if($inning[$i]['wkt'] > $hs_bowler[0]){
			$hs_bowler[0] = $inning[$i]['wkt'];
			$hs_bowler[1] = $inning[$i]['playerid'];
			$hs_bowler[2] = $runrate;
		}elseif($inning[$i]['wkt'] == $hs_bowler[0] && $runrate < $hs_bowler[2]){
			$hs_bowler[0] = $inning[$i]['wkt'];
			$hs_bowler[1] = $inning[$i]['playerid'];
			$hs_bowler[2] = $runrate;
		}
/*		if($inning[$i]['playerid'] == $cbowler) $act = 1; else $act = 0;
		echo "<bowler playerid=\"".$inning[$i]['playerid']."\" name=\"".getPlayer($inning[$i]['playerid'])."\" overs=\"".$inning[$i]['over']."\" mdns=\"".$inning[$i]['mdn']."\" score=\"".$inning[$i]['run']."\" wickets=\"".$inning[$i]['wkt']."\" runrate=\"".runrate($inning[$i]['run'], $inning[$i]['over'])."\" wide=\"".$inning[$i]['wide']."\" noball=\"".$inning[$i]['noball']."\" order=\"".$inning[$i]['sortorder']."\" active=\"$act\" />";*/
	}
	if($hs_bowler[0] > 0){
		echo "<best_bowler playerid=\"".$hs_bowler[1]."\" name=\"".getPlayer($hs_bowler[1])."\" wickets=\"".$hs_bowler[0]."\" runrate=\"".$hs_bowler[2]."\" />";
	}
?>
</bowlingline>
<?php
	}
	if($fallofwicket){
	?>
<fallofwickets>
<?php
	$fall = $DB->select("select fow_score, fow_over, playerid, bowledby, wicket_type from event_playerex, event_batsman where
	event_playerex.id=event_batsman.event_playerFK and
        event_batsman.wicket_type <> 10 and
	event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$hteam->id." and
	event_playerex.inning=".$Ascore[$s]['inning']." and event_playerex.active='1' and
	event_batsman.active='1' and event_batsman.inning=".$Ascore[$s]['inning']." and (fow_score > 0 or fow_over > 0) order by fow_score, fow_over");
	for($f=0; $f < count($fall); $f++){
		$fallofwicket_flage = true;
		$fow_text_rs = $DB->select("select comment from stats_comment where eventid=".$matches[$m]['id']." and scoreFK != 91 and scoreFK IN (15, 21, 22, 23, 24, 25, 26, 33, 35, 39,40,41, 46, 50,55,56,57,58,59,60,61,62,63,64,65,36, 67,68,69,70,72,73,74,75,76,77,78,79,81,82,83,84,85,86,87,88,92,93,98,100,106,109) and over_ball like '".$fall[$f]['fow_over']."' and inning=".$ing_order);
		if(empty($fow_text_rs)){
			$fow_text = "";
		} else {
			$fow_text = textf($fow_text_rs[0]["comment"]);
		}
		echo "<fallofwicket playerid=\"".$fall[$f]['playerid']."\"  name=\"".getPlayer($fall[$f]['playerid'])."\" score=\"".$fall[$f]['fow_score']."\" bowler=\"".getPlayer($fall[$f]['bowledby'])."\" bowler_id=\"".$fall[$f]['bowledby']."\" wicket=\"".getName("wicket", $fall[$f]["wicket_type"])."\" ball=\"".( $is100BallMatch ? oversToBalls($fall[$f]['fow_over'],true) : $fall[$f]['fow_over'])."\" text=\"".$fow_text."\" number=\"".($f+1)."\" />";
	}
	?>
</fallofwickets>
<?php
	}
	if($bat_first == "A"){
		if($Ascore[$s]['inning'] == 2){
			$ing = 3;
		} elseif($Ascore[$s]['inning'] == 3){
			$ing = 5;
		} else {
			$ing = 1;
		}
	} else {
		if($Ascore[$s]['inning'] == 2){
			$ing = 4;
		} elseif($Ascore[$s]['inning'] == 3){
			$ing = 6;
		} else {
			$ing = 2;
		}
	}
	if($partnership) {
		echo '<parnerships>';
		if($matches[$m]["live"] == 3){
			$partner = array();
			$cscore = 0;
			$cballs = 0;
			$temp_ing = $Ascore[$s]['inning'];
			$data3 = $DB->select("select event_playerex.playerid, score, balls, sortorder, fow_score, fow_over from event_playerex, event_batsman where
			event_playerex.id = event_playerFK and event_playerex.eventid=".$matches[$m]['id']." and teamid=".$hteam->id." and event_playerex.active='1' and event_batsman.active='1'
			and event_playerex.inning='$temp_ing' and event_batsman.inning='$temp_ing' and wicket_type NOT IN(8,9) and fow_over > 0 order by fow_score");
			for($e =0; $e < count($data3); $e++){
				$partner[] = array($data3[$e]['fow_score']-$cscore, $data3[$e]['fow_over'], overtoball($data3[$e]['fow_over'])-$cballs, $data3[$e]['playerid']);
				$cscore = $data3[$e]['fow_score'];
				$cballs = overtoball($data3[$e]['fow_over']);
			}
			if(sizeof($partner) < 10){
				$partner[] = array($tscore-$cscore, $tover, overtoball($tover)-$cballs, 0);
			}
			$outp = array();
			$partnership_data = array();
			for($f =0; $f < count($partner); $f++){
				$temp = array($partner[$f][0], $partner[$f][1], $partner[$f][2]);
				if(sizeof($outp) > 0){
					$sql = "select event_playerex.playerid from event_playerex, event_batsman where event_playerex.id = event_playerFK and event_playerex.eventid=".$matches[$m]['id']." and
					teamid=".$hteam->id." and event_playerex.active='1' and event_batsman.active='1' and event_playerex.inning='$temp_ing' and
					event_batsman.inning='$temp_ing' and playerid NOT IN(".implode(",",$outp).") and sortorder <= '".($f+2)."' order by sortorder";
				} else {
					$sql = "select event_playerex.playerid from event_playerex, event_batsman where event_playerex.id = event_playerFK and event_playerex.eventid=".$matches[$m]['id']." and
					teamid=".$hteam->id." and event_playerex.active='1' and event_batsman.active='1' and event_playerex.inning='$temp_ing' and
					event_batsman.inning='$temp_ing' and sortorder <= '".($f+2)."' order by sortorder";
				}
				$data4= $DB->select($sql);
				for($g=0; $g < count($data4);$g++){
					$temp[] = $data4[$g][0];
				}
				$partnership_data[] = $temp;
				$outp[] = $partner[$f][3];
			}
			$partnership_data_string = "";
			$extra = array(16,18,20,28,34,35,37,38,67,68,69,70);
			for($i=0;$i < count($partnership_data); $i++){
				$score = 0;
				$balls = 0;
				$parnerships_flage=true;
				if(isset($partnership_data[$i][3]) && isset($partnership_data[$i][4])){
					if(isset($partnership_data[$i-1]))
						$last_ps_ball = $partnership_data[$i-1][1]; //$last_ps_ball = $partnership_data[$i-1][1] - .1;
					else
						$last_ps_ball = 0;
					$data = $DB->select("select scoreFK, en from stats_comment, systemdata where systemdata_type='stats_score' and stats_comment.scoreFK=systemdata.id
					and eventid=".$matches[$m]['id']." and inning=".$ing." and stats_comment.active=1 and batsman=".$partnership_data[$i][3]." and
					over_ball > '".$last_ps_ball."' and over_ball < '".($partnership_data[$i][1]+.1)."' and (player1='' or player1=batsman)");
					for($j=0; $j < count($data); $j++){
						$score +=  $data[$j][1];
						if(!in_array($data[$j][0], $extra))
							$balls++;
					}
					$partnership_data[$i][5] = $score;
					$partnership_data[$i][6] = $balls;
					$score = 0;
					$balls = 0;
					if(isset($partnership_data[$i-1]))
						$last_ps_ball = $partnership_data[$i-1][1]; //$last_ps_ball = $partnership_data[$i-1][1] - .1;
					else
						$last_ps_ball = 0;
					$data = $DB->select("select scoreFK, en from stats_comment, systemdata where systemdata_type='stats_score' and stats_comment.scoreFK=systemdata.id
					and eventid=".$matches[$m]['id']." and inning=".$ing." and stats_comment.active=1 and batsman=".$partnership_data[$i][4]." and
					over_ball > '".$last_ps_ball."' and over_ball < '".($partnership_data[$i][1]+.1)."' and (player1='' or player1=batsman)");
					for($j=0; $j < count($data); $j++){
						$score +=  $data[$j][1];
						if(!in_array($data[$j][0], $extra))
							$balls++;
					}
					$partnership_data[$i][7] = $score;
					$partnership_data[$i][8] = $balls;
					$ex = $partnership_data[$i][0] - $partnership_data[$i][5] - $partnership_data[$i][7];
					if($ex < 0){
						$ex = 0;
						$partnership_data[$i][0] = $partnership_data[$i][5] + $partnership_data[$i][7];
					}
					if($partnership_data[$i][2] < 0) $partnership_data[$i][2] = 0;
					echo '<partnetship player1="'.getPlayerLF($partnership_data[$i][3]).'" player2="'.getPlayerLF($partnership_data[$i][4]).'" player1_id="'.$partnership_data[$i][3].'" player2_id="'.$partnership_data[$i][4].'" p1_run="'.$partnership_data[$i][5].'" p1_ball="'.$partnership_data[$i][6].'" p2_run="'.$partnership_data[$i][7].'" p2_ball="'.$partnership_data[$i][8].'" total_run="'.$partnership_data[$i][0].'" total_ball="'.$partnership_data[$i][2].'" extra="'.$ex.'" />';
				}
			}
		}
		echo '</parnerships>';
	}
	if($commentary){
		$xml = "";
		$data = $DB->select("select id, over_ball, batsman, bowler, scoreFK, comment, position, ball_track_pitch from stats_comment where eventid=".$matches[$m]['id']." and active='1' and inning='".$ing_order."' order by over_ball desc, id desc limit 0, $cmlimit");
		for($rc =0; $rc < count($data); $rc++){
			$commentary_flage = true;
			$pdata = getCache("stats_score_".$data[$rc]['scoreFK']) ?: setCache("stats_score_".$data[$rc]['scoreFK'], $DB->select("select name, es, en, de, dk, se, no from systemdata where systemdata_type='stats_score' and id=".$data[$rc]['scoreFK']), 600);
			$sum_tmp = $pdata[0][2] + $pdata[0][3] + $pdata[0][4] + $pdata[0][5] + $pdata[0][6];
			$xml .= "<com id=\"".$data[$rc]['id']."\" over=\"".( $is100BallMatch ? oversToBalls($data[$rc]['over_ball'],true) : $data[$rc]['over_ball'])."\" bowler=\"".getplayer($data[$rc]['bowler'])."\" batsman=\"".getplayer($data[$rc]['batsman'])."\" bowler_id=\"".$data[$rc]['bowler']."\" batsman_id=\"".$data[$rc]['batsman']."\" score=\"".str_replace("&", "&amp;", $pdata[0][0])."\" d_score=\"".$pdata[0][1]."\" text=\"".textf($data[$rc]['comment'])."\" sc=\"".$sum_tmp."\" type_id=\"".$data[$rc]['scoreFK']."\"  position=\"".$data[$rc]['position']."\"  ball_track_pitch='".$data[$rc]['ball_track_pitch']."' />";
		}
		echo "<commentary>";
		echo $xml;
		echo "</commentary>";
	}
	if($cover){
		echo "<current_over>";
		$graph_data = array();
		$max_sc =0;
		$max_ov = 0;
		$wicketid = array(15, 21, 22, 23, 24, 25, 26, 33, 35, 39,40,41, 46, 50,55,56,57,58,59,60,61,62,63,64,65,36, 67,68,69,70,72,73,74,75,76,77,78,79,81,82,83,84,85,86,87,92,93,98);
		$data = $DB->select("select max(over_ball) as ob from stats_comment where eventid=".$matches[$m]['id']." and active='1' and inning='$ing' and scoreFK != 91");
		if(!empty($data)){
			list ($ov, $bl) = explode(".", $data[0]["ob"]);
			$odata = $DB->select("select over_ball, scoreFK, bowler, player1 from stats_comment where eventid=".$matches[$m]['id']." and active='1' and inning='$ing' and over_ball like '$ov.%' and scoreFK != 91 order by over_ball, id");
			for($rc =0; $rc < count($odata); $rc++){
				list ($overa, $balla) = explode('.', $odata[$rc]['over_ball']);
				$pdata = $DB->select("select en, de, dk, se, no, it, es from systemdata where systemdata_type='stats_score' and id=".$odata[$rc]['scoreFK']);
				$graph_data[$overa]["score"] += ($pdata[0]["en"] + $pdata[0]["de"] + $pdata[0]["dk"] + $pdata[0]["se"] + $pdata[0]["no"] + $pdata[0]["it"]);
				$graph_data[$overa]["extra"] += ($pdata[0]["de"] + $pdata[0]["dk"] + $pdata[0]["se"] + $pdata[0]["no"] + $pdata[0]["it"]);
				if($pdata[0][1] == "W"){
					$pdata[0][1] = "WKT";
				}
				$graph_data[$overa]["txt"][] = $pdata[0][1];
				echo '<current_over_ball number="'.$balla.'" symbol="'.$pdata[0]['es'].'" score="'.($pdata[0]["en"] + $pdata[0]["de"] + $pdata[0]["dk"] + $pdata[0]["se"] + $pdata[0]["no"] + $pdata[0]["it"]).'" />';
			}
			if($balla < 6){
				for($cl=($balla+1); $cl < 7;$cl++){
					echo '<current_over_ball number="'.$cl.'" symbol="" score="" />';
				}
			}
			echo '<current_over_score score="'.$graph_data[$overa]["score"].'" extra="'.$graph_data[$overa]["extra"].'" />';
		}
		echo "</current_over>";
	}
}
	?>
	<datacoverage>
			<?php
				if($lineup_flage){
					echo"<lineupflage flage ='true' />";
				}else{
					echo"<lineupflage flage ='false' />";
				}

				if($commentary_flage){
					echo"<commentariesFlage flage ='true' />";
				}else{
					echo"<commentariesFlage flage ='false' />";
				}

				if($parnerships_flage){
					echo"<partnershipsFlage flage ='true' />";
				}else{
					echo"<partnershipsFlage flage ='false' />";
				}

				if($battingline_flage){
					echo"<battingLineStatisticsFlage flage ='true' />";
				}else{
					echo"<battingLineStatisticsFlage flage ='false' />";
				}

				if($bowlingline_flage){
					echo"<bowlingLineStatisticsFlage flage ='true' />";
				}else{
					echo"<bowlingLineStatisticsFlage flage ='false' />";
				}

				if($fallofwicket_flage){
					echo"<fallOfWicketsFlage flage ='true' />";
				}else{
					echo"<fallOfWicketsFlage flage ='false' />";
				}

			?>
		</datacoverage>
</inning>
<?php
	}
	?>
</innings>



</team>
<team id="<?php echo $ateam->id ?>" name="<?php echo str_replace("&", "&amp;",$ateam->name) ?>" country="<?php echo getName("country",$ateam->country) ?>" code="<?php echo str_replace("&", "&amp;",str_replace("&amp;", "&",$ateam->code)) ?>">
<?php
	$lineup_flage=false;
	$battingline_flage=false;
	$bowlingline_flage= false;
	$fallofwicket_flage= false;
	$parnerships_flage= false;
	$commentary_flage=false;

?>


<?php
// 	$Ascore = $DB->select("select inning, score, wicket, overs, ing_declare from event_team_result where eventid=".$matches[$m]['id']." and teamid=".$ateam->id." order by inning");
// 	for($s =0; $s < count($Ascore); $s++){
// 		if($Ascore[$s]["ing_declare"] == ""){
// 			$Ascore[$s]["ing_declare"] = 0;
// 		}
// 		if($bat_first == "B"){
// 			if($Ascore[$s]['inning'] == 2){
// 				$ing = 3;
// 			} elseif($Ascore[$s]['inning'] == 3){
// 				$ing = 5;
// 			} else {
// 				$ing = 1;
// 			}
// 		} else {
// 			if($Ascore[$s]['inning'] == 2){
// 				$ing = 4;
// 			} elseif($Ascore[$s]['inning'] == 3){
// 				$ing = 6;
// 			} else {
// 				$ing = 2;
// 			}
// 		}
// 		if($Ascore[$s]['inning'] == 1)
// 			$caption = "1ST INN";
// 		elseif($Ascore[$s]['inning'] == 2)
// 			$caption = "2ND INN";
// 		else
// 			$caption = "SO INN";
// 		$cbowler =0;
// 		$cbatsman = 0;
// 		$a1data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='bowler'");
// 		if(!empty($a1data))
// 			$cbowler = $a1data[0][0];
// 		$a2data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='cbatsman'");
// 		if(!empty($a2data))
// 			$cbatsman = $a2data[0][0];
// /*		if($Ascore[$s]['inning'] == 3)
// 			$ing_num = "Super";
// 		else*/
// 			$ing_num = $Ascore[$s]['inning'];
// 		$ing_order = $ing;
// 		if($matches[$m]["followon"] == "1"){
// 			if($ing_order == 3)
// 				$ing_order = 4;
// 			elseif($ing_order == 4)
// 				$ing_order = 3;
// 		}
// 		if($Ascore[$s]['overs'] > 0)
// 			$runrate = round(($Ascore[$s]["score"] / $Ascore[$s]['overs'])*100)/100;
// 		else
// 			$runrate = 0;
// 		$tscore = $Ascore[$s]['score'];
// 		$tover = $Ascore[$s]['overs'];
		?>

<?php
	// $extra = $DB->select("select wide, noball, bye, legbye, penalty from event_extra where eventid=".$matches[$m]['id']." and inning=".$Ascore[$s]['inning']." and teamid=".$ateam->id);
	//
	// if($ing == $current_ing || $activeonly == 0){

?>

<?php
// for data coverage

	// $data3 = $DB->select("select distinct playerid, player_type from event_playerex where eventid=".$matches[$m]['id']." and teamid=".$ateam->id." and active='1'");
	// if(count($data3) > 0){
	//
	// 	if(count($data3)>0){
	// 		$lineup_flage = true;
	// 	}
	//
	// }

	?>




<?php
	// $hs_batsman = array(0,0);
	// $inning = $DB->select("select event_playerex.id, event_playerex.playerid, score, balls, s4, s6,
	// sortorder, wicket_type, bowledby, catchby from event_playerex, event_batsman where
	// event_playerex.id=event_batsman.event_playerFK and
	// event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$ateam->id." and
	// event_playerex.inning=".$Ascore[$s]['inning']." and event_playerex.active='1' and
	// event_batsman.active='1' and event_batsman.inning=".$Ascore[$s]['inning']." order by sortorder");
	// for($i=0; $i < count($inning); $i++){
	// 	$battingline_flage = true;
	// 	if($inning[$i]['playerid'] == $cbatsman) $act=1; else $act = 0;
	// 	// echo "<batsman playerid=\"".$inning[$i]['playerid']."\" name=\"".getPlayer($inning[$i]['playerid'])."\" score=\"".$inning[$i]['score']."\" balls=\"".$inning[$i]['balls']."\" four=\"".$inning[$i]['s4']."\" six=\"".$inning[$i]['s6']."\" runrate=\"".($inning[$i]['balls']?number_format($inning[$i]['score']/$inning[$i]['balls']*100,2):0)."\" order=\"".$inning[$i]['sortorder']."\" active=\"$act\"><wicket type=\"".getName("wicket", $inning[$i]['wicket_type'])."\" bowler_id=\"".$inning[$i]['bowledby']."\"  bowler_name=\"".getPlayer($inning[$i]['bowledby'])."\" fielder_id=\"".$inning[$i]['catchby']."\"  fielder_name=\"".getPlayer($inning[$i]['catchby'])."\" /></batsman>";
	// 	if($inning[$i]['score'] > $hs_batsman[0]){
	// 		$hs_batsman[0] = $inning[$i]['score'];
	// 		$hs_batsman[1] = $inning[$i]['playerid'];
	// 	}
	// }
	// if($hs_batsman[0] > 0){
	// 	// echo "<best_batsman playerid=\"".$hs_batsman[1]."\" name=\"".getPlayer($hs_batsman[1])."\" score=\"".$hs_batsman[0]."\" />";
	// }
	?>

<?php
	// $hs_bowler = array(0,0,0);
	// $inning = $DB->select("select event_playerex.playerid, over, mdn, run , wkt, wide, noball, sortorder
	// from event_playerex, event_bowler  where
	// event_playerex.id=event_bowler.event_playerFK and
	// event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$hteam->id." and
	// event_playerex.inning=".$Ascore[$s]['inning']." and event_playerex.active='1' and
	// event_bowler.active='1' and event_bowler.inning=".$Ascore[$s]['inning']." order by sortorder");
	// for($i=0; $i < count($inning); $i++){
	// 	$bowlingline_flage = true;
	// 	if($inning[$i]['playerid'] == $cbowler) $act=1; else $act = 0;
	// 	$runrate = runrate($inning[$i]['run'], $inning[$i]['over']);
	// 	// echo "<bowler playerid=\"".$inning[$i]['playerid']."\" name=\"".getPlayer($inning[$i]['playerid'])."\" overs=\"".$inning[$i]['over']."\" mdns=\"".$inning[$i]['mdn']."\" score=\"".$inning[$i]['run']."\" wickets=\"".$inning[$i]['wkt']."\" runrate=\"".$runrate."\" wide=\"".$inning[$i]['wide']."\" noball=\"".$inning[$i]['noball']."\" order=\"".$inning[$i]['sortorder']."\" active=\"$act\" />";
	// 	if($inning[$i]['wkt'] > $hs_bowler[0]){
	// 		$hs_bowler[0] = $inning[$i]['wkt'];
	// 		$hs_bowler[1] = $inning[$i]['playerid'];
	// 		$hs_bowler[2] = $runrate;
	// 	}elseif($inning[$i]['wkt'] == $hs_bowler[0] && $runrate < $hs_bowler[2]){
	// 		$hs_bowler[0] = $inning[$i]['wkt'];
	// 		$hs_bowler[1] = $inning[$i]['playerid'];
	// 		$hs_bowler[2] = $runrate;
	// 	}
	// }
	// if($hs_bowler[0] > 0){
	// 	// echo "<best_bowler playerid=\"".$hs_bowler[1]."\" name=\"".getPlayer($hs_bowler[1])."\" wickets=\"".$hs_bowler[0]."\" runrate=\"".$hs_bowler[2]."\" />";
	// }
?>


<?php
	// $fall = $DB->select("select fow_score, fow_over, playerid, bowledby, wicket_type from event_playerex, event_batsman where
	// event_playerex.id=event_batsman.event_playerFK and
	// event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$ateam->id." and
	// event_playerex.inning=".$Ascore[$s]['inning']." and event_playerex.active='1' and
	// event_batsman.active='1' and event_batsman.inning=".$Ascore[$s]['inning']." and (fow_score > 0 or fow_over <> '') order by fow_score, fow_over");
	// for($f=0; $f < count($fall); $f++){
	// 	$fallofwicket_flage = true;
	// 	$fow_text_rs = $DB->select("select comment from stats_comment where eventid=".$matches[$m]['id']." and scoreFK != 91 and scoreFK IN (15, 21, 22, 23, 24, 25, 26, 33, 35, 39,40,41, 46, 50,55,56,57,58,59,60,61,62,63,64,65,36, 67,68,69,70,72,73,74,75,76,77,78,79,81,82,83,84,85,86,87,88,92,93,98,100,106,109) and over_ball like '".$fall[$f]['fow_over']."' and inning=".$ing_order);
	// 	if(empty($fow_text_rs)){
	// 		$fow_text = "";
	// 	} else {
	// 		$fow_text = textf($fow_text_rs[0]["comment"]);
	// 	}
	// 	// echo "<fallofwicket playerid=\"".$fall[$f]['playerid']."\"  name=\"".getPlayer($fall[$f]['playerid'])."\" score=\"".$fall[$f]['fow_score']."\" bowler=\"".getPlayer($fall[$f]['bowledby'])."\" bowler_id=\"".$fall[$f]['bowledby']."\" wicket=\"".getName("wicket", $fall[$f]["wicket_type"])."\" ball=\"".$fall[$f]['fow_over']."\" text=\"".$fow_text."\" number=\"".($f+1)."\" />";
	// }


	// if($partnership) {
	// 	// echo '<parnerships>';
	// 	if($matches[$m]["live"] == 3){
	// 		$partner = array();
	// 		$cscore = 0;
	// 		$cballs = 0;
	// 		$temp_ing = $Ascore[$s]['inning'];
	// 		$data3 = $DB->select("select event_playerex.playerid, score, balls, sortorder, fow_score, fow_over from event_playerex, event_batsman where
	// 		event_playerex.id = event_playerFK and event_playerex.eventid=".$matches[$m]['id']." and teamid=".$ateam->id." and event_playerex.active='1' and event_batsman.active='1'
	// 		and event_playerex.inning='$temp_ing' and event_batsman.inning='$temp_ing' and wicket_type NOT IN(8,9) and fow_over <> '' order by fow_score");
	// 		for($e =0; $e < count($data3); $e++){
	// 			$partner[] = array($data3[$e]['fow_score']-$cscore, $data3[$e]['fow_over'], overtoball($data3[$e]['fow_over'])-$cballs, $data3[$e]['playerid']);
	// 			$cscore = $data3[$e]['fow_score'];
	// 			$cballs = overtoball($data3[$e]['fow_over']);
	// 		}
	// 		if(sizeof($partner) < 10){
	// 			$partner[] = array($tscore-$cscore, $tover, overtoball($tover)-$cballs, 0);
	// 		}
	// 		$outp = array();
	// 		$partnership_data = array();
	// 		for($f =0; $f < count($partner); $f++){
	// 			$temp = array($partner[$f][0], $partner[$f][1], $partner[$f][2]);
	// 			if(sizeof($outp) > 0){
	// 				$sql = "select event_playerex.playerid from event_playerex, event_batsman where event_playerex.id = event_playerFK and event_playerex.eventid=".$matches[$m]['id']." and
	// 				teamid=".$ateam->id." and event_playerex.active='1' and event_batsman.active='1' and event_playerex.inning='$temp_ing' and
	// 				event_batsman.inning='$temp_ing' and playerid NOT IN(".implode(",",$outp).") and sortorder <= '".($f+2)."' order by sortorder";
	// 			} else {
	// 				$sql = "select event_playerex.playerid from event_playerex, event_batsman where event_playerex.id = event_playerFK and event_playerex.eventid=".$matches[$m]['id']." and
	// 				teamid=".$ateam->id." and event_playerex.active='1' and event_batsman.active='1' and event_playerex.inning='$temp_ing' and
	// 				event_batsman.inning='$temp_ing' and sortorder <= '".($f+2)."' order by sortorder";
	// 			}
	// 			$data4= $DB->select($sql);
	// 			for($g=0; $g < count($data4);$g++){
	// 				$temp[] = $data4[$g][0];
	// 			}
	// 			$partnership_data[] = $temp;
	// 			$outp[] = $partner[$f][3];
	// 		}
	// 		$partnership_data_string = "";
	// 		$extra = array(16,18,20,28,34,35,37,38,67,68,69,70);
	// 		for($i=0;$i < count($partnership_data); $i++){
	// 			$parnerships_flage = true;
	// 			$score = 0;
	// 			$balls = 0;
	// 				if(isset($partnership_data[$i-1]))
	// 					$last_ps_ball = $partnership_data[$i-1][1];
	// 				else
	// 					$last_ps_ball = 0;
	// 			if(isset($partnership_data[$i][3]) && isset($partnership_data[$i][4])){
	// 				$data = $DB->select("select scoreFK, en from stats_comment, systemdata where systemdata_type='stats_score' and stats_comment.scoreFK=systemdata.id
	// 				and eventid=".$matches[$m]['id']." and inning=".$ing." and stats_comment.active=1 and batsman=".$partnership_data[$i][3]." and
	// 				over_ball > '".$last_ps_ball."' and over_ball < '".($partnership_data[$i][1]+.1)."' and (player1='' or player1=batsman)");
	// 				for($j=0; $j < count($data); $j++){
	// 					$score +=  $data[$j][1];
	// 					if(!in_array($data[$j][0], $extra))
	// 						$balls++;
	// 				}
	// 				$partnership_data[$i][5] = $score;
	// 				$partnership_data[$i][6] = $balls;
	// 				$score = 0;
	// 				$balls = 0;
	// 				if(isset($partnership_data[$i-1]))
	// 					$last_ps_ball = $partnership_data[$i-1][1];
	// 				else
	// 					$last_ps_ball = 0;
	// 				$data = $DB->select("select scoreFK, en from stats_comment, systemdata where systemdata_type='stats_score' and stats_comment.scoreFK=systemdata.id
	// 				and eventid=".$matches[$m]['id']." and inning=".$ing." and stats_comment.active=1 and batsman=".$partnership_data[$i][4]." and
	// 				over_ball > '".$last_ps_ball."' and over_ball < '".($partnership_data[$i][1]+.1)."' and (player1='' or player1=batsman)");
	// 				for($j=0; $j < count($data); $j++){
	// 					$score +=  $data[$j][1];
	// 					if(!in_array($data[$j][0], $extra))
	// 						$balls++;
	// 				}
	// 				$partnership_data[$i][7] = $score;
	// 				$partnership_data[$i][8] = $balls;
	// 				$ex = $partnership_data[$i][0] - $partnership_data[$i][5] - $partnership_data[$i][7];
	// 				if($ex < 0){
	// 					$ex = 0;
	// 					$partnership_data[$i][0] = $partnership_data[$i][5] + $partnership_data[$i][7];
	// 				}
	// 				if($partnership_data[$i][2] < 0) $partnership_data[$i][2] = 0;
	// 				// echo '<partnetship player1="'.getPlayerLF($partnership_data[$i][3]).'" player2="'.getPlayerLF($partnership_data[$i][4]).'" player1_id="'.$partnership_data[$i][3].'" player2_id="'.$partnership_data[$i][4].'" p1_run="'.$partnership_data[$i][5].'" p1_ball="'.$partnership_data[$i][6].'" p2_run="'.$partnership_data[$i][7].'" p2_ball="'.$partnership_data[$i][8].'" total_run="'.$partnership_data[$i][0].'" total_ball="'.$partnership_data[$i][2].'" extra="'.$ex.'" />';
	// 			}
	// 		}
	// 	}
	// 	// echo '</parnerships>';
	// }
	// if($commentary){
	// 	$xml = "";
	// 	$data = $DB->select("select id, over_ball, batsman, bowler, scoreFK, comment from stats_comment where eventid=".$matches[$m]['id']." and active='1' and inning='".$ing_order."' order by over_ball desc, id desc limit 0, $cmlimit");
	// 	for($rc =0; $rc < count($data); $rc++){
	// 		$commentary_flage = true;
	// 		// $pdata = $DB->select("select name, es, en, de, dk, se, no from systemdata where systemdata_type='stats_score' and id=".$data[$rc]['scoreFK']);
	// 		// $sum_tmp = $pdata[0][2] + $pdata[0][3] + $pdata[0][4] + $pdata[0][5] + $pdata[0][6];
	// 		// $xml .= "<com id=\"".$data[$rc]['id']."\" over=\"".$data[$rc]['over_ball']."\" bowler=\"".getplayer($data[$rc]['bowler'])."\" batsman=\"".getplayer($data[$rc]['batsman'])."\" bowler_id=\"".$data[$rc]['bowler']."\" batsman_id=\"".$data[$rc]['batsman']."\" score=\"".str_replace("&","&amp;",$pdata[0][0])."\" d_score=\"".$pdata[0][1]."\" text=\"".textf($data[$rc]['comment'])."\" sc=\"".$sum_tmp."\" type_id=\"".$data[$rc]['scoreFK']."\" />";
	// 	}
	// 	// echo "<commentary>";
	// 	// echo $xml;
	// 	// echo "</commentary>";
	// }





	?>

<?php

	// }}
?>





		<?php
		// echo "<datacoverage>";
			// if($lineup_flage){
			// 	echo"<lineupflage flage ='true' />";
			// }else{
			// 	echo"<lineupflage flage ='false' />";
			// }
			//
			// if($commentary_flage){
			// 	echo"<commentariesFlage flage ='true' />";
			// }else{
			// 	echo"<commentariesFlage flage ='false' />";
			// }
			//
			// if($parnerships_flage){
			// 	echo"<partnershipsFlage flage ='true' />";
			// }else{
			// 	echo"<partnershipsFlage flage ='false' />";
			// }
			//
			// if($battingline_flage){
			// 	echo"<battingLineStatisticsFlage flage ='true' />";
			// }else{
			// 	echo"<battingLineStatisticsFlage flage ='false' />";
			// }
			//
			// if($bowlingline_flage){
			// 	echo"<bowlingLineStatisticsFlage flage ='true' />";
			// }else{
			// 	echo"<bowlingLineStatisticsFlage flage ='false' />";
			// }
			//
			// if($fallofwicket_flage){
			// 	echo"<fallOfWicketsFlage flage ='true' />";
			// }else{
			// 	echo"<fallOfWicketsFlage flage ='false' />";
			// }
			// echo "</datacoverage> ";
		?>



<?php

	if($squad == 1){
		echo "<squad>";
		$tmp = $DB->select("select player.id, trim(concat(firstname, ' ', lastname)) as name, pt.name player_type from tournament_team_squad tts join player on tts.player_id=player.id join systemdata pt on pt.systemdata_type = 'player_type' and pt.id = player.player_type where tour_id=".$matches[$m]["tournament"]." and team_id=".$matches[$m]["at_id"]." order by name");
		for($ctmp=0; $ctmp < sizeof($tmp); $ctmp++){
			echo "<player id=\"".$tmp[$ctmp]["id"]."\" name=\"".$tmp[$ctmp]["name"]."\" position=\"".str_replace("&","&amp;",$tmp[$ctmp]["player_type"])."\" />";
		}
		echo "</squad>";
	}
	if($lineup){ ?><lineups><?php
	// $data3 = $DB->select("select distinct playerid, player_type from event_playerex where eventid=".$matches[$m]['id']." and teamid=".$ateam->id." and active='1'");
	$data3 = $DB->select("select distinct event_playerex.playerid, event_playerex.player_type,systemdata.name as batting_style  from event_playerex
		LEFT JOIN player ON (player.id = event_playerex.playerid)
		LEFT JOIN systemdata ON (systemdata.id = player.direction and systemdata.systemdata_type='batting_style')
	where event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$ateam->id." and event_playerex.active='1'");
	if(count($data3) > 0){

		for($rc =0; $rc < count($data3); $rc++){
			$lineup_flage = true;
			if($data3[$rc]["player_type"] > 0){
				$extraI = " (".getName("p_type", $data3[$rc]["player_type"]).")";
			} else{
				$extraI = "";
			}
			$bating_style = $data3[$rc]["batting_style"]?$data3[$rc]["batting_style"]:'null';
			echo "<lineup name=\"".getplayer($data3[$rc]["playerid"]).$extraI."\" id=\"".$data3[$rc]["playerid"]."\"  batting_style=\"".$bating_style."\"  />";
		}
	} ?></lineups><?php	}	?>
<innings>
<?php
	$Ascore = $DB->select("select inning, score, wicket, overs, ing_declare from event_team_result where eventid=".$matches[$m]['id']." and teamid=".$ateam->id." order by inning");
	for($s =0; $s < count($Ascore); $s++){
		if($Ascore[$s]["ing_declare"] == ""){
			$Ascore[$s]["ing_declare"] = 0;
		}
		if($bat_first == "B"){
			if($Ascore[$s]['inning'] == 2){
				$ing = 3;
			}
			elseif($Ascore[$s]['inning'] == 3){
				$ing = 6;
			}
			elseif($Ascore[$s]['inning'] == 7){
				$ing = 7;
			}
			else {
				$ing = 1;
			}
		} else {
			if($Ascore[$s]['inning'] == 2){
				$ing = 4;
			}
			elseif($Ascore[$s]['inning'] == 3){
				$ing = 5;
			}
			elseif($Ascore[$s]['inning'] == 7){
				$ing = 8;
			}
			else {
				$ing = 2;
			}
		}
		if($Ascore[$s]['inning'] == 1)
			$caption = "1ST INN";
		elseif($Ascore[$s]['inning'] == 2)
			$caption = "2ND INN";
		else
			$caption = "SO INN";
		$cbowler =0;
		$cbatsman = 0;
		$a1data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='bowler'");
		if(!empty($a1data))
			$cbowler = $a1data[0][0];
		$a2data = $DB->select("select data from stats_settings where eventid=".$matches[$m]['id']." and inning=$ing and stats_type='cbatsman'");
		if(!empty($a2data))
			$cbatsman = $a2data[0][0];
/*		if($Ascore[$s]['inning'] == 3)
			$ing_num = "Super";
		else*/
			$ing_num = $Ascore[$s]['inning'];
		$ing_order = $ing;
		if($matches[$m]["followon"] == "1"){
			if($ing_order == 3)
				$ing_order = 4;
			elseif($ing_order == 4)
				$ing_order = 3;
		}
		if($Ascore[$s]['overs'] > 0)
			// $runrate = round(($Ascore[$s]["score"] / $Ascore[$s]['overs'])*100)/100;
			{$n = $Ascore[$s]['overs'];
			$whole = floor($n);      // 1
			$fraction = ($n - $whole)*10; // .25

			$total_balls = ($whole*6)+$fraction;

				$runrate = round(($Ascore[$s]["score"] / $total_balls)*$ballsPerOver,2) ;}
		else{
			$runrate = 0;}
		$tscore = $Ascore[$s]['score'];
		$tover = $Ascore[$s]['overs'];
		?>
<inning number="<?php echo $ing_num ?>" caption="<?php echo str_replace("&", "&amp;", $ateam->code)." ".$caption ?>" order="<?php echo $ing_order ?>" active="<?php echo ($ing == $current_ing)?"1":"0"; ?>" score="<?php echo $Ascore[$s]['score'] ?>" overs="<?php echo ( $is100BallMatch ? oversToBalls($Ascore[$s]['overs'],true) : $Ascore[$s]['overs']) ?>" wickets="<?php echo $Ascore[$s]['wicket'] ?>" declare="<?php echo $Ascore[$s]["ing_declare"] ?>" runrate="<?php echo $runrate ?>">
<?php
	$extra = $DB->select("select wide, noball, bye, legbye, penalty from event_extra where eventid=".$matches[$m]['id']." and inning=".$Ascore[$s]['inning']." and teamid=".$ateam->id);
	if(!empty($extra))
		echo "<extra sum=\"".($extra[0]['wide'] + $extra[0]['noball'] + $extra[0]['bye'] + $extra[0]['legbye']+$extra[0]['penalty'])."\" wide=\"".$extra[0]['wide']."\" noball=\"".$extra[0]['noball']."\" bye=\"".$extra[0]['bye']."\" legbye=\"".$extra[0]['legbye']."\" penalty=\"".$extra[0]['penalty']."\"/>";
	else
		echo "<extra sum=\"0\" wide=\"0\" noball=\"0\" bye=\"0\" legbye=\"0\" penalty=\"0\"/>";
	if($ing == $current_ing || $activeonly == 0){
	if($battingline){
?>
<battingline>
<?php
	$hs_batsman = array(0,0);
	$inning = $DB->select("select event_playerex.id, event_playerex.playerid, score, balls, s4, s6,
	sortorder, wicket_type, bowledby, catchby from event_playerex, event_batsman where
	event_playerex.id=event_batsman.event_playerFK and
	event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$ateam->id." and
	event_playerex.inning=".$Ascore[$s]['inning']." and event_playerex.active='1' and
	event_batsman.active='1' and event_batsman.inning=".$Ascore[$s]['inning']." order by sortorder");
	for($i=0; $i < count($inning); $i++){
		$battingline_flage = true;
		if($inning[$i]['playerid'] == $cbatsman) $act=1; else $act = 0;
		echo "<batsman playerid=\"".$inning[$i]['playerid']."\" name=\"".getPlayer($inning[$i]['playerid'])."\" score=\"".$inning[$i]['score']."\" balls=\"".$inning[$i]['balls']."\" four=\"".$inning[$i]['s4']."\" six=\"".$inning[$i]['s6']."\" runrate=\"".($inning[$i]['balls']?number_format($inning[$i]['score']/$inning[$i]['balls']*100,2):0)."\" order=\"".$inning[$i]['sortorder']."\" active=\"$act\"><wicket type=\"".getName("wicket", $inning[$i]['wicket_type'])."\" bowler_id=\"".$inning[$i]['bowledby']."\"  bowler_name=\"".getPlayer($inning[$i]['bowledby'])."\" fielder_id=\"".$inning[$i]['catchby']."\"  fielder_name=\"".getPlayer($inning[$i]['catchby'])."\" /></batsman>";
		if($inning[$i]['score'] > $hs_batsman[0]){
			$hs_batsman[0] = $inning[$i]['score'];
			$hs_batsman[1] = $inning[$i]['playerid'];
		}
	}
	if($hs_batsman[0] > 0){
		echo "<best_batsman playerid=\"".$hs_batsman[1]."\" name=\"".getPlayer($hs_batsman[1])."\" score=\"".$hs_batsman[0]."\" />";
	}
	?>
</battingline>
<?php
	}
	if($bowlingline){
		?>
<bowlingline>
<?php
	$hs_bowler = array(0,0,0);
	$inning = $DB->select("select event_playerex.playerid, over, mdn, run , wkt, wide, noball, sortorder
	from event_playerex, event_bowler  where
	event_playerex.id=event_bowler.event_playerFK and
	event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$hteam->id." and
	event_playerex.inning=".$Ascore[$s]['inning']." and event_playerex.active='1' and
	event_bowler.active='1' and event_bowler.inning=".$Ascore[$s]['inning']." order by sortorder");
	for($i=0; $i < count($inning); $i++){
		$bowlingline_flage = true;
		if($inning[$i]['playerid'] == $cbowler) $act=1; else $act = 0;
		$runrate = runrate($inning[$i]['run'], ($is100BallMatch ? convertTo5BallOvers($inning[$i]['over']): $inning[$i]['over']));
		echo "<bowler playerid=\"".$inning[$i]['playerid']."\" name=\"".getPlayer($inning[$i]['playerid'])."\" overs=\"".( $is100BallMatch ? oversToBalls($inning[$i]['over']) : $inning[$i]['over'])."\" mdns=\"".$inning[$i]['mdn']."\" score=\"".$inning[$i]['run']."\" wickets=\"".$inning[$i]['wkt']."\" runrate=\"".$runrate."\" wide=\"".$inning[$i]['wide']."\" noball=\"".$inning[$i]['noball']."\" order=\"".$inning[$i]['sortorder']."\" active=\"$act\" />";
		if($inning[$i]['wkt'] > $hs_bowler[0]){
			$hs_bowler[0] = $inning[$i]['wkt'];
			$hs_bowler[1] = $inning[$i]['playerid'];
			$hs_bowler[2] = $runrate;
		}elseif($inning[$i]['wkt'] == $hs_bowler[0] && $runrate < $hs_bowler[2]){
			$hs_bowler[0] = $inning[$i]['wkt'];
			$hs_bowler[1] = $inning[$i]['playerid'];
			$hs_bowler[2] = $runrate;
		}
	}
	if($hs_bowler[0] > 0){
		echo "<best_bowler playerid=\"".$hs_bowler[1]."\" name=\"".getPlayer($hs_bowler[1])."\" wickets=\"".$hs_bowler[0]."\" runrate=\"".$hs_bowler[2]."\" />";
	}
?>
</bowlingline>
<?php
	}
	if($fallofwicket){
		?>
<fallofwickets>
<?php
	$fall = $DB->select("select fow_score, fow_over, playerid, bowledby, wicket_type from event_playerex, event_batsman where
	event_playerex.id=event_batsman.event_playerFK and
        event_batsman.wicket_type <> 10 and
	event_playerex.eventid=".$matches[$m]['id']." and event_playerex.teamid=".$ateam->id." and
	event_playerex.inning=".$Ascore[$s]['inning']." and event_playerex.active='1' and
	event_batsman.active='1' and event_batsman.inning=".$Ascore[$s]['inning']." and (fow_score > 0 or fow_over > 0) order by fow_score, fow_over");
	for($f=0; $f < count($fall); $f++){
		$fallofwicket_flage = true;
		$fow_text_rs = $DB->select("select comment from stats_comment where eventid=".$matches[$m]['id']." and scoreFK != 91 and scoreFK IN (15, 21, 22, 23, 24, 25, 26, 33, 35, 39,40,41, 46, 50,55,56,57,58,59,60,61,62,63,64,65,36, 67,68,69,70,72,73,74,75,76,77,78,79,81,82,83,84,85,86,87,88,92,93,98,100,106,109) and over_ball like '".$fall[$f]['fow_over']."' and inning=".$ing_order);
		if(empty($fow_text_rs)){
			$fow_text = "";
		} else {
			$fow_text = textf($fow_text_rs[0]["comment"]);
		}
		echo "<fallofwicket playerid=\"".$fall[$f]['playerid']."\"  name=\"".getPlayer($fall[$f]['playerid'])."\" score=\"".$fall[$f]['fow_score']."\" bowler=\"".getPlayer($fall[$f]['bowledby'])."\" bowler_id=\"".$fall[$f]['bowledby']."\" wicket=\"".getName("wicket", $fall[$f]["wicket_type"])."\" ball=\"".( $is100BallMatch ? oversToBalls($fall[$f]['fow_over'],true) : $fall[$f]['fow_over'])."\" text=\"".$fow_text."\" number=\"".($f+1)."\" />";
	}
	?>
</fallofwickets>
<?php
	}
	if($bat_first == "B"){
		if($Ascore[$s]['inning'] == 2){
			$ing = 3;
		} else {
			$ing = 1;
		}
	} else {
		if($Ascore[$s]['inning'] == 2){
			$ing = 4;
		} else {
			$ing = 2;
		}
	}
	if($partnership) {
		echo '<parnerships>';
		if($matches[$m]["live"] == 3){
			$partner = array();
			$cscore = 0;
			$cballs = 0;
			$temp_ing = $Ascore[$s]['inning'];
			$data3 = $DB->select("select event_playerex.playerid, score, balls, sortorder, fow_score, fow_over from event_playerex, event_batsman where
			event_playerex.id = event_playerFK and event_playerex.eventid=".$matches[$m]['id']." and teamid=".$ateam->id." and event_playerex.active='1' and event_batsman.active='1'
			and event_playerex.inning='$temp_ing' and event_batsman.inning='$temp_ing' and wicket_type NOT IN(8,9) and fow_over > 0 order by fow_score");
			for($e =0; $e < count($data3); $e++){
				$partner[] = array($data3[$e]['fow_score']-$cscore, $data3[$e]['fow_over'], overtoball($data3[$e]['fow_over'])-$cballs, $data3[$e]['playerid']);
				$cscore = $data3[$e]['fow_score'];
				$cballs = overtoball($data3[$e]['fow_over']);
			}
			if(sizeof($partner) < 10){
				$partner[] = array($tscore-$cscore, $tover, overtoball($tover)-$cballs, 0);
			}
			$outp = array();
			$partnership_data = array();
			for($f =0; $f < count($partner); $f++){
				$temp = array($partner[$f][0], $partner[$f][1], $partner[$f][2]);
				if(sizeof($outp) > 0){
					$sql = "select event_playerex.playerid from event_playerex, event_batsman where event_playerex.id = event_playerFK and event_playerex.eventid=".$matches[$m]['id']." and
					teamid=".$ateam->id." and event_playerex.active='1' and event_batsman.active='1' and event_playerex.inning='$temp_ing' and
					event_batsman.inning='$temp_ing' and playerid NOT IN(".implode(",",$outp).") and sortorder <= '".($f+2)."' order by sortorder";
				} else {
					$sql = "select event_playerex.playerid from event_playerex, event_batsman where event_playerex.id = event_playerFK and event_playerex.eventid=".$matches[$m]['id']." and
					teamid=".$ateam->id." and event_playerex.active='1' and event_batsman.active='1' and event_playerex.inning='$temp_ing' and
					event_batsman.inning='$temp_ing' and sortorder <= '".($f+2)."' order by sortorder";
				}
				$data4= $DB->select($sql);
				for($g=0; $g < count($data4);$g++){
					$temp[] = $data4[$g][0];
				}
				$partnership_data[] = $temp;
				$outp[] = $partner[$f][3];
			}
			$partnership_data_string = "";
			$extra = array(16,18,20,28,34,35,37,38,67,68,69,70);
			for($i=0;$i < count($partnership_data); $i++){
				$parnerships_flage = true;
				$score = 0;
				$balls = 0;
					if(isset($partnership_data[$i-1]))
						$last_ps_ball = $partnership_data[$i-1][1]; //$last_ps_ball = $partnership_data[$i-1][1] - .1;
					else
						$last_ps_ball = 0;
				if(isset($partnership_data[$i][3]) && isset($partnership_data[$i][4])){
					$data = $DB->select("select scoreFK, en from stats_comment, systemdata where systemdata_type='stats_score' and stats_comment.scoreFK=systemdata.id
					and eventid=".$matches[$m]['id']." and inning=".$ing." and stats_comment.active=1 and batsman=".$partnership_data[$i][3]." and
					over_ball > '".$last_ps_ball."' and over_ball < '".($partnership_data[$i][1]+.1)."' and (player1='' or player1=batsman)");
					for($j=0; $j < count($data); $j++){
						$score +=  $data[$j][1];
						if(!in_array($data[$j][0], $extra))
							$balls++;
					}
					$partnership_data[$i][5] = $score;
					$partnership_data[$i][6] = $balls;
					$score = 0;
					$balls = 0;
					if(isset($partnership_data[$i-1]))
						$last_ps_ball = $partnership_data[$i-1][1];
					else
						$last_ps_ball = 0;
					$data = $DB->select("select scoreFK, en from stats_comment, systemdata where systemdata_type='stats_score' and stats_comment.scoreFK=systemdata.id
					and eventid=".$matches[$m]['id']." and inning=".$ing." and stats_comment.active=1 and batsman=".$partnership_data[$i][4]." and
					over_ball > '".$last_ps_ball."' and over_ball < '".($partnership_data[$i][1]+.1)."' and (player1='' or player1=batsman)");
					for($j=0; $j < count($data); $j++){
						$score +=  $data[$j][1];
						if(!in_array($data[$j][0], $extra))
							$balls++;
					}
					$partnership_data[$i][7] = $score;
					$partnership_data[$i][8] = $balls;
					$ex = $partnership_data[$i][0] - $partnership_data[$i][5] - $partnership_data[$i][7];
					if($ex < 0){
						$ex = 0;
						$partnership_data[$i][0] = $partnership_data[$i][5] + $partnership_data[$i][7];
					}
					if($partnership_data[$i][2] < 0) $partnership_data[$i][2] = 0;
					echo '<partnetship player1="'.getPlayerLF($partnership_data[$i][3]).'" player2="'.getPlayerLF($partnership_data[$i][4]).'" player1_id="'.$partnership_data[$i][3].'" player2_id="'.$partnership_data[$i][4].'" p1_run="'.$partnership_data[$i][5].'" p1_ball="'.$partnership_data[$i][6].'" p2_run="'.$partnership_data[$i][7].'" p2_ball="'.$partnership_data[$i][8].'" total_run="'.$partnership_data[$i][0].'" total_ball="'.$partnership_data[$i][2].'" extra="'.$ex.'" />';
				}
			}
		}
		echo '</parnerships>';
	}
	if($commentary){
		$xml = "";
		$data = $DB->select("select id, over_ball, batsman, bowler, scoreFK, comment, position, ball_track_pitch from stats_comment where eventid=".$matches[$m]['id']." and active='1' and inning='".$ing_order."' order by over_ball desc, id desc limit 0, $cmlimit");
		for($rc =0; $rc < count($data); $rc++){
			$commentary_flage = true;
                        $pdata = getCache("stats_score_".$data[$rc]['scoreFK']) ?: setCache("stats_score_".$data[$rc]['scoreFK'], $DB->select("select name, es, en, de, dk, se, no from systemdata where systemdata_type='stats_score' and id=".$data[$rc]['scoreFK']), 600);
			$sum_tmp = $pdata[0][2] + $pdata[0][3] + $pdata[0][4] + $pdata[0][5] + $pdata[0][6];
			$xml .= "<com id=\"".$data[$rc]['id']."\" over=\"".( $is100BallMatch ? oversToBalls($data[$rc]['over_ball'],true) : $data[$rc]['over_ball'])."\" bowler=\"".getplayer($data[$rc]['bowler'])."\" batsman=\"".getplayer($data[$rc]['batsman'])."\" bowler_id=\"".$data[$rc]['bowler']."\" batsman_id=\"".$data[$rc]['batsman']."\" score=\"".str_replace("&","&amp;",$pdata[0][0])."\" d_score=\"".$pdata[0][1]."\" text=\"".textf($data[$rc]['comment'])."\" sc=\"".$sum_tmp."\" type_id=\"".$data[$rc]['scoreFK']."\"  position=\"".$data[$rc]['position']."\"  ball_track_pitch='".$data[$rc]['ball_track_pitch']."' />";
		}
		echo "<commentary>";
		echo $xml;
		echo "</commentary>";
	}
	if($cover){
		echo "<current_over>";
		$graph_data = array();
		$max_sc =0;
		$max_ov = 0;
		$wicketid = array(15, 21, 22, 23, 24, 25, 26, 33, 35, 39,40,41, 46, 50,55,56,57,58,59,60,61,62,63,64,65,36, 67,68,69,70,72,73,74,75,76,77,78,79,81,82,83,84,85,86,87,92,93,98);
		$data = $DB->select("select max(over_ball) as ob from stats_comment where eventid=".$matches[$m]['id']." and active='1' and inning='$ing' and scoreFK != 91");
		if(!empty($data)){
			list ($ov, $bl) = explode(".", $data[0]["ob"]);
			$odata = $DB->select("select over_ball, scoreFK, bowler, player1 from stats_comment where eventid=".$matches[$m]['id']." and active='1' and inning='$ing' and over_ball like '$ov.%' and scoreFK != 91 order by over_ball, id");
			for($rc =0; $rc < count($odata); $rc++){
				list ($overa, $balla) = explode('.', $odata[$rc]['over_ball']);
				$pdata = $DB->select("select en, de, dk, se, no, it, es from systemdata where systemdata_type='stats_score' and id=".$odata[$rc]['scoreFK']);
				$graph_data[$overa]["score"] += ($pdata[0]["en"] + $pdata[0]["de"] + $pdata[0]["dk"] + $pdata[0]["se"] + $pdata[0]["no"] + $pdata[0]["it"]);
				$graph_data[$overa]["extra"] += ($pdata[0]["de"] + $pdata[0]["dk"] + $pdata[0]["se"] + $pdata[0]["no"] + $pdata[0]["it"]);
				if($pdata[0][1] == "W"){
					$pdata[0][1] = "WKT";
				}
				$graph_data[$overa]["txt"][] = $pdata[0][1];
				echo '<current_over_ball number="'.$balla.'" symbol="'.$pdata[0]['es'].'" score="'.($pdata[0]["en"] + $pdata[0]["de"] + $pdata[0]["dk"] + $pdata[0]["se"] + $pdata[0]["no"] + $pdata[0]["it"]).'" />';
			}
			if($balla < 6){
				for($cl=($balla+1); $cl < 7;$cl++){
					echo '<current_over_ball number="'.$cl.'" symbol="" score="" />';
				}
			}
			echo '<current_over_score score="'.$graph_data[$overa]["score"].'" extra="'.$graph_data[$overa]["extra"].'" />';
		}
		echo "</current_over>";
	}
}
	?>

	<datacoverage>
		<?php
			if($lineup_flage){
				echo"<lineupflage flage ='true' />";
			}else{
				echo"<lineupflage flage ='false' />";
			}

			if($commentary_flage){
				echo"<commentariesFlage flage ='true' />";
			}else{
				echo"<commentariesFlage flage ='false' />";
			}

			if($parnerships_flage){
				echo"<partnershipsFlage flage ='true' />";
			}else{
				echo"<partnershipsFlage flage ='false' />";
			}

			if($battingline_flage){
				echo"<battingLineStatisticsFlage flage ='true' />";
			}else{
				echo"<battingLineStatisticsFlage flage ='false' />";
			}

			if($bowlingline_flage){
				echo"<bowlingLineStatisticsFlage flage ='true' />";
			}else{
				echo"<bowlingLineStatisticsFlage flage ='false' />";
			}

			if($fallofwicket_flage){
				echo"<fallOfWicketsFlage flage ='true' />";
			}else{
				echo"<fallOfWicketsFlage flage ='false' />";
			}

		?>
	</datacoverage>
</inning>
<?php
	}
	?>
</innings>



</team></teams>
<?php	}	?>
</game>
<?php
	}
}
if($cmd !='team_info' && $cmd !='match_info' && $cmd != 'tournament_match' && $cmd !='match_commentary' && $cmd != 'match_fixture' && $cmd != 'team_flags' && $cmd != "player_data" && $cmd != 'team_detail_info' &&  $cmd != "squad_detail" && $cmd != "tournamentlist"){ ?>
</cricket>
<?php } ?>
<?php if($cmd != 'match_info'  && $cmd != 'tournament_match' && $cmd != 'match_commentary' && $cmd != 'match_fixture' && $cmd != 'team_flags' && $cmd != 'tournamentlist'){ ?>
</live>
<?php } ?>
<?php
$DB->close();
function textf($val){
	return str_replace("<", "&lt;", str_replace("\"", "&quot;", str_replace("&", "&amp;", str_replace("&amp;", "&", $val))));
}
function getMatchTypeIdArray($match_type){
	$match_types = array(
            'odi' => array(
                1, 34, 35
            ),
            't20i' => array(
                25, 27, 32
            ),
            't20' => array(
                4, 6, 9, 26
            ),
            'test' => array(
                2, 33
            ),
            'firstclass' => array(
                3, 5, 7, 13, 23, 36, 37
            ),
            'lista' => array(
                8, 10, 11, 24, 29, 30, 31, 38,
            ),
        );
        return isset($match_types[$match_type]) ? $match_types[$match_type] : $match_types['odi'];
}
function paginationSQL($page = 1, $limit = 5){
    return " LIMIT $limit OFFSET ".(($page - 1) * $limit) . " ";
}
function getMatchInnings($matchId = 0,$teamId = 0){
    global $DB;
    return $DB->select_v2("
        SELECT *
        FROM event_team_result rs
        where rs.eventid = $matchId AND rs.teamid = $teamId
        ORDER BY rs.inning
        ");
}
function getMatchFormat($matchType) {
    if ((stripos($matchType, '1 day') !== FALSE || stripos($matchType, 'odi')) !== FALSE) {
        return "odi";
    } else if ((stripos($matchType, 'day') !== FALSE || stripos($matchType, 'test')) !== FALSE) {
        return "test";
    } else if (stripos($matchType, 't20') !== FALSE) {
        return "t20";
    } else if (stripos($matchType, '100') !== FALSE) {
        return "100balls";
    } else if (stripos($matchType, '10 ') !== FALSE) {
        return "t10";
    } else {
        return "odi";
    }
}
function oversToBalls($overs,$is5BallsPerOver = false){     //convert traditional Overs to Balls and if 2nd param true, convert to Balls as per 5 Balls per Over method
    $overs_list = explode(".", $overs);
    return (@$overs_list[0] * ($is5BallsPerOver ? 5 : 6)) + @$overs_list[1];
}

?>
