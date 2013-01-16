<?php
/**
 * BibleStudy Debug File
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

$biblestudy_db = JFactory::getDBO();

// Debugging helpers
// First lets set some assertion settings for the code
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 1);
assert_options(ASSERT_BAIL, 1);
assert_options(ASSERT_CALLBACK, 'debug_assert_callback');

/**
 *
 */
class JBSDebug
{
	/**
	 * Default assert call back funtion
	 * If certain things fail hard we MUST know about it
	 *
	 * @param   string  $script   Script
	 * @param   string  $line     Line
	 * @param   string  $message  Message
	 *
	 * @return null
	 */
	public function debug_assert_callback($script, $line, $message)
	{
		echo "<h1>Assertion failed!</h1><br />
        Script: <strong>$script</strong><br />
        Line: <strong>$line</strong><br />
        Condition: <br /><pre>$message</pre>";

		// Now display the call stack
		echo debug_callstackinfo();
	}

	/**
	 * Production error handling
	 *
	 * @param   string  $text  Text
	 * @param   int     $back  ID
	 *
	 * @return  null
	 */
	public function trigger_dberror($text = '', $back = 0)
	{
		$biblestudy_db = JFactory::getDBO();
		$dberror       = $biblestudy_db->stderr(true);
		echo debug_callstackinfo($back + 1);

		include_once BIBLESTUDY_PATH_LIB . DIRECTORY_SEPARATOR . 'biblestudy.version.php';
		$biblestudyVersion      = BiblestudyVersion::version();
		$biblestudyPHPVersion   = BiblestudyVersion::PHPVersion();
		$biblestudyMySQLVersion = BiblestudyVersion::MySQLVersion();
		?>
    <!-- Version Info -->
    <div class="fbfooter">
        Installed version:
		<?php echo $biblestudyVersion; ?>
        | php
		<?php echo $biblestudyPHPVersion; ?>
        | mysql
		<?php echo $biblestudyMySQLVersion; ?>
    </div>
    <!-- /Version Info -->
	<?php
		biblestudy_error($text . '<br /><br />' . $dberror, E_USER_ERROR, $back + 1);
	}

	/**
	 * System to Check for DB errors
	 *
	 * @param   string  $text  Text to Disaply
	 * @param   int     $back  ID to return
	 *
	 * @return null
	 */
	public function check_dberror($text = '', $back = 0)
	{
		$biblestudy_db = & JFactory::getDBO();

		if ($biblestudy_db->getErrorNum() != 0)
		{
			trigger_dberror($text, $back + 1);
		}
	}

	/**
	 * Check DB Warning
	 *
	 * @param   string  $text  Text
	 *
	 * @return null
	 */
	public function Check_dbwarning($text = '')
	{
		$biblestudy_db = & JFactory::getDBO();

		if ($biblestudy_db->getErrorNum() != 0)
		{
			trigger_dbwarning($text);
		}
	}

	/**
	 * Trigger DB Warning
	 *
	 * @param   string  $text  Text
	 *
	 * @return  null
	 */
	public function Trigger_dbwarning($text = '')
	{
		$biblestudy_db = JFactory::getDBO();
		biblestudy_error($text . '<br />' . $biblestudy_db->stderr(true), E_USER_WARNING);
	}

	/**
	 * Little helper to created a formatted output of variables
	 *
	 * @param   array  $varlist  List ov Var
	 *
	 * @return string
	 */
	public function Debug_vars($varlist)
	{
		$output = '<table class="table" border=1><tr> <th>variable</th> <th>value</th> </tr>';

		foreach ($varlist as $key => $value)
		{

			if (is_array($value))
			{
				$output .= '<tr><td>$' . $key . '</td><td>';

				if (count($value) > 0)
				{
					$output .= '"<table class="table" border=1><tr> <th>key</th> <th>value</th> </tr>';

					foreach ($value as $skey => $svalue)
					{
						if (is_array($svalue))
						{
							$output .= '<tr><td>[' . $skey . ']</td><td>Nested Array</td></tr>';
						}
						else
						{
							if (is_object($svalue))
							{
								$objvarlist = get_object_vars($svalue);

								// Recursive function call
								debug_vars($objvarlist);
							}
							else
							{
								$output .= '<tr><td>$' . $skey . '</td><td>"' . $svalue . '"</td></tr>';
							}
						}
					}
					$output .= '</table>"';
				}
				else
				{
					$output .= 'EMPTY';
				}
				$output .= '</td></tr>';
			}
			else
			{
				if (is_object($value))
				{
					$objvarlist = get_object_vars($value);

					// Recursive function call
					debug_vars($objvarlist);
				}
				else
				{
					$output .= '<tr><td>$' . $key . '</td><td>"' . $value . '"</td></tr>';
				}
			}
		}
		$output .= '</table>';

		return $output;
	}

	/**
	 * Show the callstackinfo to this point in a decent format
	 *
	 * @param   int  $back  Back info
	 *
	 * @return string
	 */
	public function Debug_callstackinfo($back = 1)
	{
		$trace = array_slice(debug_backtrace(), $back);

		return debug_vars($trace);
	}

	/**
	 * System to get BibleStudy Error
	 *
	 * @param   string  $message  Message to display
	 * @param   int     $level    Lever of Notice
	 * @param   int     $back     Id to return
	 *
	 * @return null
	 */
	public function Biblestudy_error($message, $level = E_USER_NOTICE, $back = 1)
	{
		$trace  = debug_backtrace();
		$caller = $trace[$back];
		trigger_error(
			$message . ' in <strong>' . $caller['function'] . '()</strong> called from <strong>' .
				$caller['file'] . '</strong> on line <strong>' . $caller['line'] . '</strong>' .
				"\n<br /><br />Error reported",
			$level
		);
	}
}