<?php
	require_once('../includes/init.php');
	
	$day = Day::find(GETorPOST('id'));
	
	$home_goals = GETorPOST('home_goals');
	$away_goals = GETorPOST('away_goals');
	
	$digits = array(0,1,2,3,4,5,6,7,8,9);
	
	// check positive int values
	foreach ($home_goals as $goals)
		if ($goals && !ctype_digit($goals))
		{
			echo json_encode(array('success' => 0, 'message' => 'Nombre de buts invalide.'));
			exit;
		}
	foreach ($away_goals as $goals)
		if ($goals && !ctype_digit($goals))
		{
			echo json_encode(array('success' => 0, 'message' => 'Nombre de buts invalide.'));
			exit;
		}
	
	$matches = $day->getMatches();
	
	foreach ($matches as $match)
	{
		if (!array_key_exists($match->id, $home_goals))
			continue;
		$match->home_goals = $home_goals[$match->id];
		$match->away_goals = $away_goals[$match->id];
		$match->save();
	}
	echo json_encode(array('success' => 1));
?>