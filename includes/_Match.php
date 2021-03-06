<?php
/**
 * Description of _Match
 *
 * @author arteau
 */

$GLOBALS["classes"]["Match"] = array("classname" => "_Match", "tablename" => "pr_match");

class _Match extends ArtObject
{
	protected $_data = array('id' => null, 'pr_day_id' => null, 'pr_away_team_id' => null, 'pr_home_team_id' => null, 'home_goals' => null, 'away_goals' => null, 'limit_date' => null);

	protected $_editedFields = array();

	/**
	 * @return Match
	 */
	public static function find($id)
	{
		return parent::find("Match", $id);
	}

	/**
	 * @return array
	 */
	public static function getAll($order='')
	{
		return parent::search("Match", array(), $order);
	}

	/**
	 * @return Match
	 */
	public static function findBy($field, $value)
	{
		return parent::findBy("Match", $field, $value);
	}

	/**
	 * @return array
	 */
	public static function search($criteria = array(), $order="", $limit=false, $onlyCount=false)
	{
		return parent::search("Match", $criteria, $order, $limit, $onlyCount);
	}

	public function save()
	{
		if (!parent::save("Match"))
			return false;
		
		return $this->getDay()->getSeason()->save();
	}

	/**
	 * @return int
	 */
	public static function count($criteria = array())
	{
		return parent::count("Match", $criteria, "", 1, true);
	}

	public function delete()
	{
		parent::delete("Match");
	}

	/**
	 * @return boolean
	 */
	public static function isUnique($field, $value, $id=false)
	{
		return parent::isUnique("Match", $field, $value, $id);
	}
}
