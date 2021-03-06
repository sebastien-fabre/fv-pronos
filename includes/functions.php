<?php
/**
 * Define function json_decode if the json module is disabled
 */
if (!function_exists('json_decode'))
{
	function json_decode($content, $assoc=false)
	{
		if ($assoc)
			$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		else
			$json = new Services_JSON;
		return $json->decode($content);
	}
}

/**
 * Define function json_decode if the json module is disabled
 */
if (!function_exists('json_encode'))
{
	function json_encode($content)
	{
		$json = new Services_JSON;
		return $json->encode($content);
	}
}

function echoHTMLHead($title='')
{
	$url = APPLICATION_URL;

	echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<title>$title</title>
	
	<script type="text/javascript" src="$url/js/jquery-1.4.2.min.js"></script>
	<script type="text/javascript" src="$url/js/jquery-ui-1.8.custom.min.js"></script>
	<script type="text/javascript" src="$url/js/jquery.ui.datepicker-fr.js"></script>
	<script type="text/javascript" src="$url/js/jquery.simplemodal-1.3.4.min.js"></script>
	<script type="text/javascript" src="$url/js/jquery.form.js"></script>
	<script type="text/javascript" src="$url/js/jquery.qtip-1.0.min.js"></script>
	<script type="text/javascript" src="$url/js/jquery.curvycorners.packed.js"></script>
	<script type="text/javascript" src="$url/js/nyroModal-1.6.2/js/jquery.nyroModal-1.6.2.min.js"></script>
	<script type="text/javascript" src="$url/js/common.js"></script>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
					
	<link rel="stylesheet" href="$url/css/pronos.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="$url/js/nyroModal-1.6.2/styles/nyroModal.full.css" type="text/css" media="screen" />
					
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

	<!-- Latest compiled and minified JavaScript -->
	<!--script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script-->

	<link rel="stylesheet" href="$url/css/pronos.css" type="text/css" media="screen" />
	
</head>
HTML;
}

function echoMenu()
{
	require_once($GLOBALS['ROOTPATH'] . 'includes/header.php');
}

function echoHTMLFooter()
{
	Notification::clearAll();

	echo '<script type="text/javascript">';
	echo $GLOBALS['FooterJS'];
	echo '</script>';
}

/**
 * Checks the database migration version, creates the table if doesn't exist,
 * migrates if necessary, and if migrates puts the application in maintenance
 */
function checkMigrationVersion()
{
	// check database version
	$req = mysql_query('SELECT * FROM pr_migration_version');

	$currentVersion = 0;

	// create table if not present
	if (!$req)
	{
		mysql_query("CREATE TABLE `pr_migration_version` (`version` INT UNSIGNED NOT NULL DEFAULT  '1');");
		mysql_query('INSERT INTO pr_migration_version VALUES(0);');
	}
	else if (mysql_num_rows($req) == 0)
	{
		mysql_query('INSERT INTO pr_migration_version VALUES(0);');
	}
	else
	{
		$res = mysql_fetch_assoc($req);
		$currentVersion = $res['version'];
	}

	if (file_exists($GLOBALS['ROOTPATH'] . 'maintenance.txt'))
	{
		// FIXME : redirect to a real maintenance page
		header("HTTP/1.x 503 Temporary undisponible");
		header("Status:503 Temporary undisponible");
		die('maintenance');
	}

	Migration::migrate($currentVersion);
}

function echoNotifications()
{
	Notification::display();
}

/********************************
 * Retro-support of get_called_class()
 * Tested and works in PHP 5.2.4
 * http://www.sol1.com.au/
 ********************************/
if(!function_exists('get_called_class'))
{
	function get_called_class($bt = false,$l = 1)
	{
    if (!$bt) 
			$bt = debug_backtrace();

    if (!isset($bt[$l])) 
			throw new Exception("Cannot find called class -> stack level too deep.");

    if (!isset($bt[$l]['type']))
		{
        throw new Exception ('type not set');
    }
    else
		{
			switch ($bt[$l]['type'])
			{
        case '::':
					$lines = file($bt[$l]['file']);
					$i = 0;
					$callerLine = '';
					do
					{
							$i++;
							$callerLine = $lines[$bt[$l]['line']-$i] . $callerLine;
					}
					while (stripos($callerLine,$bt[$l]['function']) === false);

					preg_match('/([a-zA-Z0-9\_]+)::'.$bt[$l]['function'].'/',
											$callerLine,
											$matches);
					if (!isset($matches[1]))
					{
							// must be an edge case.
							throw new Exception ("Could not find caller class: originating method call is obscured.");
					}

					switch ($matches[1])
					{
						case 'self':
						case 'parent':
							return get_called_class($bt,$l+1);
						default:
							return $matches[1];
					}
				// won't get here.
        case '->': switch ($bt[$l]['function'])
				{
					case '__get':
							// edge case -> get class of calling object
							if (!is_object($bt[$l]['object']))
								throw new Exception ("Edge case fail. __get called on non object.");

							return get_class($bt[$l]['object']);
					default:
						return $bt[$l]['class'];
				}

        default:
					throw new Exception ("Unknown backtrace method type");
			}
		}
	}
}

function array_map_recursive()
{
	$args = func_get_args();
	$callback = array_shift($args);
	$fn = __FUNCTION__;

	$out = array();
	$max = count(max($args));
	for($i=0; $i<$max; $i++) {
		if(count($args)==1) {
			foreach($args[0] as $key=>$value) {
				if(is_array($value))
					$out[$key] = $fn($callback, $value);
				else
					$out[$key] = call_user_func($callback, $value);
			}
		} else {
			$is_array = false;
			$callbacks_args = array();
			foreach($args as $array) {
				$values = array_values($array);
				if(isset($values[$i]))
					$value = $values[$i];
				else
					$value = '';

				if(is_array($value)) {
					$is_array = true;
					$callbacks_args[] = $value;
				} else {
					$callbacks_args[] = $value;
				}
			}

			if($is_array) {
				$m = count(max($callbacks_args));
				$new_callback_args = array($callback);
				foreach($callbacks_args as $arg) {
					if(!is_array($arg))
						$new_callback_args[] = array_fill(0, $m, $arg);
					else
						$new_callback_args[] = $arg;
				}
				$out[] = call_user_func_array($fn, $new_callback_args);
			} else {
				$out[] = call_user_func_array($callback, $callbacks_args);
			}
		}
	}

	return $out;
}

function GETorPOST($name, $defaultValue = null)
{
	if (!empty($_POST[$name]))
	{
		if (!is_array($_POST[$name]))
			return stripslashes($_POST[$name]);
		else
			return array_map_recursive('stripslashes', $_POST[$name]);
	}

	if (!empty($_GET[$name]))
	{
		if (!is_array($_GET[$name]))
			return stripslashes($_GET[$name]);
		else
			return array_map_recursive('stripslashes', $_GET[$name]);
	}

	return $defaultValue;
}

function camelCaseToUnderscores($str)
{
	$str[0] = strtolower($str[0]);
	$func = create_function('$c', 'return "_" . strtolower($c[1]);');
	return preg_replace_callback('/([A-Z])/', $func, $str);
}
