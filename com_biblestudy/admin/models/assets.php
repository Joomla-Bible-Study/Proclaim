<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

defined('_JEXEC') or die;

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_biblestudy/api.php';

if (file_exists($api))
{
	require_once $api;
}

/**
 * class Assets model
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 */
class BibleStudyModelAssets extends JModelLegacy
{
	public $parent_id = null;

	/** @var int Total numbers of Versions
	 * @since 7.0 */
	public $totalSteps = 0;

	/** @var int Numbers of Versions already processed
	 * @since 7.0 */
	public $doneSteps = 0;

	public $step = null;

	public $assets = array();

	/** @var float The time the process started
	 * @since 7.0 */
	private $startTime = null;

	/** @var array The pre versions to process
	 * @since 7.0 */
	private $versionStack = array();

	/** @var array The pre versions sub sql array to process
	 * @since 7.0 */
	private $allupdates = array();

	/** @var string Version of BibleStudy
	 * @since 7.0 */
	private $versionSwitch = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since 7.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->name = 'assets';
	}

	/**
	 * Start Looking though the Versions
	 *
	 * @return bool
	 *
	 * @since 7.0
	 */
	public function startScanning()
	{
		$this->resetStack();
		$this->resetTimer();
		$this->getSteps();

		if (empty($this->versionStack))
		{
			$this->versionStack = array();
		}

		ksort($this->versionStack);

		$this->saveStack();

		if (!$this->haveEnoughTime())
		{
			return true;
		}
		else
		{
			return $this->run(false);
		}
	}

	/**
	 * Starts or resets the internal timer
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	private function resetTimer()
	{
		$this->startTime = $this->microtime_float();
	}

	/**
	 * Returns the current timestamps in decimal seconds
	 *
	 * @return string
	 *
	 * @since 7.0
	 */
	private function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());

		return ((float) $usec + (float) $sec);
	}

	/**
	 * Get migrate versions of DB after import/copy has finished.
	 *
	 * @return boolean
	 *
	 * @since 7.0
	 */
	private function getSteps()
	{
		$fix = new JBSMAssets;
		$fix->build();
		$this->versionStack = $fix->query;
		$this->totalSteps = $fix->count;

		return true;
	}

	/**
	 *  Run the Migration will there is time.
	 *
	 * @param   bool  $resetTimer  If the time must be reset
	 *
	 * @return bool
	 *
	 * @since 7.0
	 */
	public function run($resetTimer = true)
	{
		if ($resetTimer)
		{
			$this->resetTimer();
		}

		$this->loadStack();

		$result = true;

		while ($result && $this->haveEnoughTime())
		{
			$result = $this->RealRun();
		}

		$this->saveStack();

		return $result;
	}

	/**
	 * Saves the Versions/SQL/After stack in the session
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	private function saveStack()
	{
		$stack = array(
				'version'    => $this->versionStack,
				'step'       => $this->step,
				'switch'     => $this->versionSwitch,
				'allupdates' => $this->allupdates,
				'total'      => $this->totalSteps,
				'done'       => $this->doneSteps,
		);
		$stack = json_encode($stack);

		if (function_exists('base64_encode') && function_exists('base64_decode'))
		{
			if (function_exists('gzdeflate') && function_exists('gzinflate'))
			{
				$stack = gzdeflate($stack, 9);
			}

			$stack = base64_encode($stack);
		}

		$session = JFactory::getSession();
		$session->set('asset_stack', $stack, 'JBSM');
	}

	/**
	 * Resets the Versions/SQL/After stack saved in the session
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	private function resetStack()
	{
		$session = JFactory::getSession();
		$session->set('asset_stack', '', 'JBSM');
		$this->versionStack   = array();
		$this->versionSwitch  = null;
		$this->allupdates     = array();
		$this->step           = null;
		$this->totalSteps     = 0;
		$this->doneSteps      = 0;
	}

	/**
	 * Loads the Versions/SQL/After stack from the session
	 *
	 * @return bool
	 *
	 * @since 7.0
	 */
	private function loadStack()
	{
		$session = JFactory::getSession();
		$stack = $session->get('asset_stack', '', 'JBSM');

		if (empty($stack))
		{
			$this->versionStack   = array();
			$this->versionSwitch  = null;
			$this->allupdates     = array();
			$this->step           = null;
			$this->totalSteps     = 0;
			$this->doneSteps      = 0;

			return false;
		}

		if (function_exists('base64_encode') && function_exists('base64_decode'))
		{
			$stack = base64_decode($stack);

			if (function_exists('gzdeflate') && function_exists('gzinflate'))
			{
				$stack = gzinflate($stack);
			}
		}

		$stack = json_decode($stack, true);

		$this->versionStack   = $stack['version'];
		$this->versionSwitch  = $stack['switch'];
		$this->allupdates     = $stack['allupdates'];
		$this->step           = $stack['step'];
		$this->totalSteps     = $stack['total'];
		$this->doneSteps      = $stack['done'];

		return true;
	}

	/**
	 * Makes sure that no more than 5 seconds since the start of the timer have elapsed
	 *
	 * @return bool
	 *
	 * @since 7.0
	 */
	private function haveEnoughTime()
	{
		$now     = $this->microtime_float();
		$elapsed = abs($now - $this->startTime);

		return $elapsed < 2;
	}

	/**
	 * Start the Run through the Pre Versions then SQL files then After PHP functions.
	 *
	 * @return bool
	 *
	 * @since 7.0
	 */
	private function RealRun()
	{
		if (!empty($this->versionStack))
		{
			krsort($this->versionStack);

			while (!empty($this->versionStack) && $this->haveEnoughTime())
			{
				$this->step = key($this->versionStack);

				if (isset($this->versionStack[$this->step]) && @!empty($this->versionStack[$this->step]))
				{
					$version = array_pop($this->versionStack[$this->step]);
					$this->doneSteps++;
					$asset = new JBSMAssets;
					$asset->fixAssets($this->step, $version);
				}
				else
				{
					unset($this->versionStack[$this->step]);
				}
			}
		}

		if (empty($this->versionStack))
		{
			// Just finished
			$this->resetStack();

			return false;
		}

		// If we have more Versions or SQL files, continue in the next step
		return true;
	}

	/**
	 * Set Parent ID
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	public function parentid()
	{
		$fix = new JBSMAssets;
		$this->parent_id = $fix->parentid();
	}

	/**
	 * Table list Array.
	 *
	 * @return array
	 *
	 * @since 7.0
	 */
	protected function getassetObjects()
	{
		$objects = array(
			array(
				'name'       => '#__bsms_servers',
				'titlefield' => 'server_name',
				'assetname'  => 'server',
				'realname'   => 'JBS_CMN_SERVERS'
			),
			array(
				'name'       => '#__bsms_studies',
				'titlefield' => 'studytitle',
				'assetname'  => 'message',
				'realname'   => 'JBS_CMN_STUDIES'
			),
			array(
				'name'       => '#__bsms_comments',
				'titlefield' => 'comment_date',
				'assetname'  => 'comment',
				'realname'   => 'JBS_CMN_COMMENTS'
			),
			array(
				'name'       => '#__bsms_locations',
				'titlefield' => 'location_text',
				'assetname'  => 'location',
				'realname'   => 'JBS_CMN_LOCATIONS'
			),
			array(
				'name'       => '#__bsms_mediafiles',
				'titlefield' => 'filename',
				'assetname'  => 'mediafile',
				'realname'   => 'JBS_CMN_MEDIA_FILES'
			),
			array(
				'name'       => '#__bsms_message_type',
				'titlefield' => 'message_type',
				'assetname'  => 'messagetype',
				'realname'   => 'JBS_CMN_MESSAGETYPES'
			),
			array(
				'name'       => '#__bsms_podcast',
				'titlefield' => 'title',
				'assetname'  => 'podcast',
				'realname'   => 'JBS_CMN_PODCASTS'
			),
			array(
				'name'       => '#__bsms_series',
				'titlefield' => 'series_text',
				'assetname'  => 'serie',
				'realname'   => 'JBS_CMN_SERIES'
			),
			array(
				'name'       => '#__bsms_teachers',
				'titlefield' => 'teachername',
				'assetname'  => 'teacher',
				'realname'   => 'JBS_CMN_TEACHERS'
			),
			array(
				'name'       => '#__bsms_templates',
				'titlefield' => 'title',
				'assetname'  => 'template',
				'realname'   => 'JBS_CMN_TEMPLATES'
			),
			array(
				'name'       => '#__bsms_topics',
				'titlefield' => 'topic_text',
				'assetname'  => 'topic',
				'realname'   => 'JBS_CMN_TOPICS'
			),
			array(
				'name'       => '#__bsms_templatecode',
				'titlefield' => 'filename',
				'assetname'  => 'templatecode',
				'realname'   => 'JBS_CMN_TEMPLATECODE'
			),
			array(
				'name'       => '#__bsms_admin',
				'titlefield' => 'id',
				'assetname'  => 'admin',
				'realname'   => 'JBS_CMN_ADMINISTRATION'
			)
		);

		return $objects;
	}

	/**
	 * Check Assets
	 *
	 * @return array
	 *
	 * @since 7.0
	 */
	public function checkAssets()
	{
		$return = array();
		$db     = JFactory::getDbo();
		$result = new stdClass;

		// First get the new parent_id
		if (!$this->parent_id)
		{
			$this->parentid();
		}

		// Get the names of the JBS tables
		$objects = $this->getassetObjects();

		// Run through each table
		foreach ($objects as $object)
		{
			// Put the table into the return array
			// Get the total number of rows and collect the table into a query
			$query = $db->getQuery(true);
			$query->select('j.id as jid, j.asset_id as jasset_id, a.id as aid, a.rules as arules, a.parent_id')
				->from($db->qn($object['name']) . ' as j')
				->leftJoin('#__assets as a ON (a.id = j.asset_id)');
			$db->setQuery($query);
			$results     = $db->loadObjectList();
			$nullrows    = 0;
			$matchrows   = 0;
			$arulesrows  = 0;
			$nomatchrows = 0;
			$numrows     = count($results);

			// Now go through each record to test it for asset id
			foreach ($results as $result)
			{
				// If there is no jasset_id it means that this has not been set and should be
				if (!$result->jasset_id)
				{
					$nullrows++;
				}
				// If there is a jasset_id but no match to the parent_id then a mismatch has occurred
				if ($this->parent_id != $result->parent_id && $result->jasset_id)
				{
					$nomatchrows++;
				}
				// If $parent_id and $result->parent_id match and the Asset rules are not blank then everything is okay
				if ($this->parent_id == $result->parent_id && $result->arules !== "")
				{
					$matchrows++;
				}
				// If $parent_id and $result->parent_id match and the Asset rules is blank we need to fix
				if ($this->parent_id == $result->parent_id && $result->arules === "")
				{
					$arulesrows++;
				}
			}

			$return[] = array(
				'realname'         => $object['realname'],
				'numrows'          => $numrows,
				'nullrows'         => $nullrows,
				'matchrows'        => $matchrows,
				'arulesrows'       => $arulesrows,
				'nomatchrows'      => $nomatchrows,
				'parent_id'        => $this->parent_id,
				'result_parent_id' => $result->parent_id,
				'id'               => $result->jid,
				'assetid'          => $result->jasset_id
			);
		}

		return $return;
	}
}
