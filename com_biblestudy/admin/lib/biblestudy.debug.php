<?php
/**
* @version $Id: biblestudy.debug.php 1284 2011-01-04 07:57:59Z genu $
* Bible Study Component
* @package Bible Study
*
* @Copyright (C) 2008 - 2010 Joomla Bible Study Team
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*
* Install based on Kunena Component
*
* @link http://www.JoomlaBibleStudy.org
**/

defined( '_JEXEC' ) or die('Restricted access');

$biblestudy_db = &JFactory::getDBO();

// Debugging helpers

// First lets set some assertion settings for the code
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 1);
assert_options(ASSERT_BAIL, 1);
assert_options(ASSERT_CALLBACK, 'debug_assert_callback');

// Default assert call back funtion
// If certain things fail hard we MUST know about it
function debug_assert_callback($script, $line, $message) {
    echo "<h1>Assertion failed!</h1><br />
        Script: <strong>$script</strong><br />
        Line: <strong>$line</strong><br />
        Condition: <br /><pre>$message</pre>";
    // Now display the call stack
    echo debug_callstackinfo();
}

// Production error handling
function trigger_dberror($text = '', $back=0)
{
	$biblestudy_db = &JFactory::getDBO();
	$dberror = $biblestudy_db->stderr(true);
	echo debug_callstackinfo($back+1);

	require_once (BIBLESTUDY_PATH_LIB .DS. 'biblestudy.version.php');
	$biblestudyVersion = CBiblestudyVersion::version();
	$biblestudyPHPVersion = CBiblestudyVersion::PHPVersion();
	$biblestudyMySQLVersion = CBiblestudyVersion::MySQLVersion();
?>
 <!-- Version Info -->
<div class="fbfooter">
Installed version:  <?php echo $biblestudyVersion; ?> | php <?php echo $biblestudyPHPVersion; ?> | mysql <?php echo $biblestudyMySQLVersion; ?>
</div>
<!-- /Version Info -->
<?php

	biblestudy_error($text.'<br /><br />'.$dberror, E_USER_ERROR, $back+1);
}

function check_dberror($text='', $back=0)
{
	$biblestudy_db = &JFactory::getDBO();
	if ($biblestudy_db->getErrorNum() != 0)
	{
		trigger_dberror($text, $back+1);
	}
}

function check_dbwarning($text='')
{
	$biblestudy_db = &JFactory::getDBO();
	if ($biblestudy_db->getErrorNum() != 0)
	{
		trigger_dbwarning($text);
	}
}

function trigger_dbwarning($text = '')
{
	$biblestudy_db = &JFactory::getDBO();
	biblestudy_error($text.'<br />'.$biblestudy_db->stderr(true), E_USER_WARNING);
}

// Little helper to created a formated output of variables
function debug_vars($varlist)
{
	$output =  '<table border=1><tr> <th>variable</th> <th>value</th> </tr>';

	foreach( $varlist as $key => $value)
	{
	    if (is_array ($value) )
	    {
	        $output .= '<tr><td>$'.$key .'</td><td>';
	        if ( sizeof($value)>0 )
	        {
		        $output .= '"<table border=1><tr> <th>key</th> <th>value</th> </tr>';
		        foreach ($value as $skey => $svalue)
		        {
		        	if (is_array ($svalue) )
		        	{
		        		$output .= '<tr><td>[' . $skey .']</td><td>Nested Array</td></tr>';
		        	}
				    else if (is_object($svalue))
				    {
				    	$objvarlist = get_object_vars($svalue);

				    	// recursive function call
				    	debug_vars($objvarlist);
				    }
				    else
				    {
				    	$output .= '<tr><td>$' . $skey .'</td><td>"'. $svalue .'"</td></tr>';
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
	    else if (is_object($value))
	    {
	    	$objvarlist = get_object_vars($value);

	    	// recursive function call
	    	debug_vars($objvarlist);
	    }
	    else
	    {
	    	$output .= '<tr><td>$' . $key .'</td><td>"'. $value .'"</td></tr>';
	    }
	}
	$output .= '</table>';

	return $output;
}

// Show the callstack to this point in a decent format
function debug_callstackinfo($back=1)
{
	$trace = array_slice(debug_backtrace(), $back);
	return debug_vars($trace);
}

function biblestudy_error($message, $level=E_USER_NOTICE, $back=1) {
	$trace = debug_backtrace();
	$caller = $trace[$back];
	trigger_error($message.' in <strong>'.$caller['function'].'()</strong> called from <strong>'.$caller['file'].'</strong> on line <strong>'.$caller['line'].'</strong>'."\n<br /><br />Error reported", $level);
}
?>
