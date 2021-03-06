<?php
	require_once('includes/init.php');

	$pathinfo = $_SERVER['PATH_INFO'];

  $matches = false;
  $match = preg_match('@^/day-([0-9]+)$@', $pathinfo, $matches);

	if (!isset($matches[1]))
		header('location: /days');

	$day = Day::find($matches[1]);

	if (!$day)
		header('location: /days');

	$season = $day->getSeason();

	$league = $season->getLeague();

	$matches = $day->getMatches();

	$teams = $season->getTeams();

	$pronos = $day->getPronos();

	$users = User::getAll('name asc');

	$pronosByUser = array();
	foreach ($pronos as $prono)
	{
		if (!array_key_exists($prono->pr_user_id, $pronosByUser))
			$pronosByUser[$prono->pr_user_id] = array();
		$pronosByUser[$prono->pr_user_id][$prono->pr_match_id] = $prono;
	}

	$isEditable = !empty($_SESSION['user']);

	echoHTMLHead('Liste des pronos');
?>

<body>
	<div class="container">
	<?php echoMenu(); ?>
		<h1>Liste des pronos</h1>
		<h2><?php echo $league->name ?> - <?php echo $season->label ?>, Journée n°<?php echo $day->number ?></h2>
		
		<p>
			<?php if ($isEditable) { ?>
				<button type="button" class="btn btn-primary nyroModal" href="<?=APPLICATION_URL?>ajax/parse_pronos.php?id=<?php echo $day->id ?>" rev="modal">Saisir l'ensemble des pronos</button>
				<?php if ($_SESSION['user']->role == 'admin') { ?>
					<button type="button" class="btn btn-primary nyroModal" href="<?=APPLICATION_URL?>ajax/advice_pronos.php?id=<?php echo $day->id ?>">Voir les pronos conseillés</button>
				<?php } ?>
			<?php } ?>
		</p>
				
		<div class="well"><?php echo count($pronosByUser) ?> joueurs ont pronostiqué cette journée</div>
		
		<div class="col-sm-6">
			<table class="table table-striped">
				<thead>
					<tr>
						<th class="col-sm-2">Nom</th>
						<?php if ($isEditable) { ?>
							<th class="col-sm-2">Modifier</th>
						<?php } ?>
						<th class="col-sm-2">Afficher</th>
					</tr>
				</thead>
				<tbody>
					<?php if (count($users) != 0): ?>
						<?php foreach ($users as $user): ?>
							<tr>
								<td><?php echo $user->name ?></td>
								<?php if ($isEditable) { ?>
									<td class="text-center"><a href="<?=APPLICATION_URL?>ajax/add_pronos.php?id=<?php echo $day->id ?>&user=<?php echo $user->id ?>" class="nyroModal"><img src="<?=APPLICATION_URL?>images/link-external.png" alt="[edit]" /> saisir les pronos</a></td>
								<?php } ?>
								<td class="tooltipped">

									<?php
									if (array_key_exists($user->id, $pronosByUser))
									{
										echo '<i>' . (count($pronosByUser[$user->id]) != $day->count_matches ? '<img src="' . APPLICATION_URL . 'images/warning.png" style="vertical-align:middle" /> ' : '') . count($pronosByUser[$user->id]) . ' pronos</i>';
										echo '<div class="hidden">';
										echo '<table class="noborder scoreTable" style="width: 100%">';
										foreach ($pronosByUser[$user->id] as $match => $prono)
										{
											if (!is_null($prono->home_goals) && !is_null($prono->away_goals))
											{
												if ($prono->home_goals > $prono->away_goals)
													echo '<tr><td class="right team"><b>' . $teams[$matches[$match]->pr_home_team_id]->name . '</b></td><td class="center"><b>' . $prono->home_goals . '</b> - ' . $prono->away_goals . '</td><td class="team">' . $teams[$matches[$match]->pr_away_team_id]->name . '</td></tr>';
												else if ($prono->home_goals < $prono->away_goals)
													echo '<tr><td class="right team">' . $teams[$matches[$match]->pr_home_team_id]->name . '</td><td class="center">' . $prono->home_goals . ' - <b>' . $prono->away_goals . '</b></td><td class="team"><b>' . $teams[$matches[$match]->pr_away_team_id]->name . '</b></td></tr>';
												else
													echo '<tr><td class="right team">' . $teams[$matches[$match]->pr_home_team_id]->name . '</td><td class="center">' . $prono->home_goals . ' - ' . $prono->away_goals . '</td><td class="team">' . $teams[$matches[$match]->pr_away_team_id]->name . '</td></tr>';
											}
											else
												echo '<tr><td class="right team">' . $teams[$matches[$match]->pr_home_team_id]->name . '</td><td class="center"> * - * </td><td class="team">' . $teams[$matches[$match]->pr_away_team_id]->name . '</td></tr>';
										}
										echo '</table>';
										echo '</div>';
									}
									else
									{
										echo '<i>aucun prono</i>';
									}
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else: ?>
						<tr><td colspan="2">Aucun résultat trouvé</td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>

	<script type="text/javascript">
		$(document).ready(function(){
			elems = $('.tooltipped');
			elems.each(function(i){
					console.log($(elems[i]));
				if ($(elems[i]).find('.hidden').length)
				{
					$(elems[i]).css('cursor', 'help');
					$(elems[i]).qtip({
						content: $(elems[i]).find('.hidden').html(),
						show: 'mouseover',
						hide: { delay: '10000', when: { event: 'mouseout' } },
						style: { name: 'blue', tip: true, 'text-align': 'center', width: 350 },
						show: { solo: true },
						position: {
							corner: { target: 'rightMiddle', tooltip: 'leftMiddle'}
						}
					});
				} 
			});
		});
	</script>

	<?php echoHTMLFooter();?>
</body>
</html>