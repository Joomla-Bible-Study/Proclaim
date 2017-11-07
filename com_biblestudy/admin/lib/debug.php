<?php
/**
 * @package    BibleStudy
 * @copyright  2007 - 2017 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Debugging helpers
// First lets set some assertion settings for the code
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 1);
assert_options(ASSERT_BAIL, 1);
assert_options(ASSERT_CALLBACK, 'debug_assert_callback');

/**
 * Class JBSMDebug
 *
 * @package     Proclaim
 * @since       8.0.0
 *
 * @deprecated  9.0.0 JBSM
 */
Class JBSMDebug
{
	/**
	 * Default assert call back function
	 * If certain things fail hard we MUST know about it
	 *
	 * @param   string  $script   ?
	 * @param   int     $line     ?
	 * @param   string  $message  ?
	 *
	 * @return void
	 *
	 * @deprecated  9.0.0 JBSM
	 *
	 * @since       7.0.0
	 */
	public function Debug_Assert_callback($script, $line, $message)
	{
		echo "<h1>Assertion failed!</h1><br />
        Script: <strong>$script</strong><br />
        Line: <strong>$line</strong><br />
        Condition: <br /><pre>$message</pre>";

		// Now display the call stack
		echo self::Debug_Call_Stack_info();
	}

	/**
	 * Production error handling
	 *
	 * @param   string  $text  ?
	 * @param   int     $back  ?
	 *
	 * @return void
	 *
	 * @deprecated  9.0.0 JBSM
	 *
	 * @since       7.0.0
	 */
	public function Trigger_Db_error($text = '', $back = 0)
	{
		$db      = JFactory::getDbo();
		$dberror = $db->stderr(true);
		echo self::Debug_Call_Stack_info($back + 1);

		$CBiblestudyVersion     = new JBSMBiblestudyVersion;
		$biblestudyVersion      = $CBiblestudyVersion->version();
		$biblestudyPHPVersion   = $CBiblestudyVersion->PHPVersion();
		$biblestudyMySQLVersion = $CBiblestudyVersion->MySQLVersion();
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
		self::biblestudy_error($text . '<br /><br />' . $dberror, E_USER_ERROR, $back + 1);
	}

	/**
	 * Check db Error
	 *
	 * @param   string  $text  ?
	 * @param   int     $back  ?
	 *
	 * @return void
	 *
	 * @deprecated  9.0.0 JBSM
	 *
	 * @since       7.0.0
	 */
	public function Check_Db_error($text = '', $back = 0)
	{
	}

	/**
	 * Check db warning
	 *
	 * @param   string  $text  ?
	 *
	 * @return void
	 *
	 * @deprecated  9.0.0 JBSM
	 *
	 * @since       7.0.0
	 */
	public function Check_Db_warning($text = '')
	{
	}

	/**
	 * DB Warning
	 *
	 * @param   string  $text  ?
	 *
	 * @return void
	 *
	 * @deprecated  9.0.0 JBSM
	 *
	 * @since       7.0.0
	 */
	public function Trigger_Db_warning($text = '')
	{
	}

	/**
	 * Little helper to created a formatted output of variables
	 *
	 * @param   array  $varlist  ?
	 *
	 * @return string
	 *
	 * @deprecated  9.0.0 JBSM
	 *
	 * @since       7.0.0
	 */
	public function Debug_vars($varlist)
	{
		$output = '<table border="1"><tr> <th>variable</th> <th>value</th> </tr>';

		foreach ($varlist as $key => $value)
		{
			if (is_array($value))
			{
				$output .= '<tr><td>$' . $key . '</td><td>';

				if (count($value) > 0)
				{
					$output .= '"<table border=1><tr> <th>key</th> <th>value</th> </tr>';

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
								$this->debug_vars($objvarlist);
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
					$this->debug_vars($objvarlist);
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
	 * Show the callstack to this point in a decent format
	 *
	 * @param   int  $back  ?
	 *
	 * @return object
	 *
	 * @deprecated  9.0.0 JBSM
	 *
	 * @since       7.0.0
	 */
	public function Debug_Call_Stack_info($back = 1)
	{
		$trace = array_slice(debug_backtrace(), $back);

		return self::debug_vars($trace);
	}

	/**
	 * Trigger JBS Errors
	 *
	 * @param   string  $message  ?
	 * @param   int     $level    ?
	 * @param   int     $back     ?
	 *
	 * @return void
	 *
	 * @deprecated  9.0.0 JBSM
	 *
	 * @since       7.0.0
	 */
	public function Biblestudy_error($message, $level = E_USER_NOTICE, $back = 1)
	{
		$trace  = debug_backtrace();
		$caller = $trace[$back];
		trigger_error(
			$message . ' in <strong>' . $caller['function'] . '()</strong> called from <strong>' . $caller['file']
				. '</strong> on line <strong>' . $caller['line'] . '</strong>' . "\n<br /><br />Error reported", $level
		);
	}
}
