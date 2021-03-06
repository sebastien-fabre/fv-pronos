<?php
	require_once('../includes/init.php');

	function sign($x)
	{
		if (is_null($x->home_goals) || is_null($x->away_goals))
			return -5;
		if ($x->home_goals == $x->away_goals)
			return 0;
		if ($x->home_goals > $x->away_goals)
			return 1;
		return -1;
	}

	$day = Day::find(GETorPOST('pr_day_id'));

	$season = $day->getSeason();
	$league = $season->getLeague();

	$teams = Team::getAll();//$season->getTeams();

	$matches = $day->getMatches();

	$users = User::getAll();

	$pronos = Prono::findByDay($day->id);
	$tmp = array();
	foreach ($pronos as $prono)
		$tmp [$prono->pr_user_id][$prono->pr_match_id] = $prono;
	$pronos = $tmp;

	echo '
			<h4 class="well">' . $league->name . ' - ' . $season->label . ', ' . ($day->number ? $day->number . '<sup>e</sup> journée' : $day->label) . '</h4>
		<div class="panel-body">
		<textarea class="form-control" rows="10">[center]';

	foreach ($matches as $match)
	{
		$homeTeam = $teams[$match->pr_home_team_id];
		$awayTeam = $teams[$match->pr_away_team_id];

		echo $homeTeam->name . ' - ' . $awayTeam->name;
		if (strlen($match->home_goals) > 0 && strlen($match->away_goals) > 0)
			echo ' : ' . $match->home_goals . '-' . $match->away_goals;
		echo "\n";
	}

	echo "\n\n" . '***************************************************' . "\n\n";

	foreach ($pronos as $userId => $scores)
	{
		$user = $users[$userId];

		if ($user->pr_team_id)
		{
			$userTeam = $teams[$user->pr_team_id];
			if ($userTeam->has_logo)
			echo '[img]' . $userTeam->getLogoUrl() . '[/img] ';
		}
		echo '[b]' . stripslashes($user->name) . '[/b] :arrow: ';
		$points = 0;
		$str = '';

		foreach ($scores as $matchId => $prono)
		{
			$match = $matches[$matchId];
			$homeTeam = $teams[$match->pr_home_team_id];
			$awayTeam = $teams[$match->pr_away_team_id];

			$str .= $homeTeam->name . ' - ' . $awayTeam->name;
			if (!is_null($prono->home_goals) && !is_null($prono->away_goals))
				$str .= ' : ' . $prono->home_goals . '-' . $prono->away_goals;

			if (strlen($match->home_goals) > 0 && strlen($match->away_goals) > 0)
			{
				if (sign($match) == sign($prono))
				{
					if ($match->home_goals == $prono->home_goals && $match->away_goals == $prono->away_goals)
					{
						$str .=  ' :arrow: 3 pts :&#33;:';
						$points += 3;
					}
					else
					{
						$str .= ' :arrow: 1 pt';
						$points += 1;
					}
				}
				else if (!is_null($match->home_goals) && !is_null($match->away_goals))
				{
					$str .= ' :arrow: 0 pt';
				}
			}
			$str .= "\n";
		}

		echo $points . ' pts' . "\n\n" . $str;

		echo "\n\n" . '***************************************************' . "\n\n";
	}
	
	echo '[/center]</textarea>'
		. '<button type="button" class="btn btn-default btn-sm nyroModalClose">Fermer</button>'
		. '</div>';
