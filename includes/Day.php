<?php
/**
 * Description of Day
 *
 * @author arteau
 */

$GLOBALS["classes"]["Day"] = array("classname" => "Day", "tablename" => "pr_day");
	
class Day extends _Day
{
	// FIXME : redo with the refactoring of the search criteria (joins)
	public function hasCompletedMatches()
	{
		$req = mysql_query('SELECT COUNT(1) c FROM pr_match WHERE home_goals IS NOT NULL AND away_goals IS NOT NULL AND pr_day_id=' . $this->id);
		$res = mysql_fetch_array($req);
		return $res['c'] != 0;
	}

	// FIXME : redo with the refactoring of the search criteria (joins)
	public function hasPronos()
	{
		$req = mysql_query('SELECT COUNT(1) as count FROM pr_prono INNER JOIN pr_match ON pr_match.id=pr_match_id WHERE pr_day_id=' . $this->id);
		$res = mysql_fetch_array($req);

		return $res['count'];
	}

	// FIXME : redo with the refactoring of the search criteria (joins)
	public function getPronos()
	{
		$results = array();

		$req = mysql_query('SELECT DISTINCT pr_prono.* FROM pr_prono INNER JOIN pr_match ON pr_match.id=pr_match_id WHERE pr_day_id=' . $this->id);
		while ($res = mysql_fetch_array($req))
			$results [$res['id']] = new Prono($res);

		return $results;
	}

	public function getMatches()
	{
		return $this->getMatchs();
	}

	public function isEditable()
	{
		return !$this->hasPronos() && !$this->hasCompletedMatches() && !empty($_SESSION['user']);
	}
}
	