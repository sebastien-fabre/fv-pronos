<?php
	session_start();

	require_once('mysql_connexion.php');
	require_once('includes.php');

	$teams = Team::getAll();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Liste des équipes</title>
	<link rel="stylesheet" href="/pronos/css/screen.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="/pronos/css/pronos.css" type="text/css" media="screen" />
	<script type="text/javascript" src="/pronos/js/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="/pronos/js/jquery.simplemodal-1.2.3.js"></script>
	<script type="text/javascript" src="/pronos/js/jquery.form-2.24.js"></script>
	<script type="text/javascript" src="/pronos/js/jquery.qtip-1.0.0-rc3.min.js"></script>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF8" />
</head>

<body>
	<?php include('header.php'); ?>
	<div id="content">
		<h1>Liste des équipes</h1>
		<div class="add"><a href="javascript:;" onclick="openPopup(-1)">Ajouter une équipe</a></div>
		<table>
			<thead>
				<tr>
					<th>Nom</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php if (count($teams) != 0): ?>
					<?php $i=0 ?>
					<?php foreach ($teams as $team): ?>
						<tr>
							<td><?php echo $team->name ?></td>
							<td class="center"><a href="javascript:;" onclick="openPopup(<?php echo $team->id ?>)"><img src="/pronos/images/edit.png" alt="[edit]" /></a></td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr><td colspan="3">Aucun résultat trouvé</td></tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<div id="popup"><div id="popup_message"></div><div id="popup_content"></div></div>

	<script type="text/javascript">
		function openPopup(id)
		{
			$('#loading').modal({close: false});
			$.ajax({
				url: '/pronos/ajax/add_team.php',
				data: {id: id},
				success: function (response) {
					$.modal.close();
					$('#popup_content').html(response);
					$('#popup').modal({close: false});
					$('#popup input[type=text]').focus();
					$('#popup form').ajaxForm({
						url: '/pronos/ajax/save_team.php',
						dataType: 'json',
						success: function (response) {
							if (response.success == 1)
								window.location.reload();
							else
								$('#popup_message').html(response.message);
						}
					});
				}
			});
		}

		$(document).ready(function(){

		});
	</script>

	<div id="loading">
		<div id="subloading">Chargement</div>
	</div>
</body>
</html>