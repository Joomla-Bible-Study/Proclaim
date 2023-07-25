<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/**
 * helper to get related studies to the current one
 *
 * @package  Proclaim.Site
 * @since    7.1.0
 */
class CWMRelatedstudies
{
	/**
	 * Remove array declaration for php 7.3.x
	 *
	 * @var  array Score
	 *
	 * @since    7.2
	 */
	public array $score;

	/**
	 * Get Related
	 *
	 * @param   object    $row     JTable
	 * @param   Registry  $params  Item Params
	 *
	 * @return string|boolean
	 *
	 * @throws \Exception
	 * @since    7.2
	 */
	public function getRelated(object $row, Registry $params)
	{
		$this->score = [];
		$keygo       = true;
		$topicsgo    = true;
		$keywords    = $params->get('metakey');
		$topics      = $row->tp_id;
		$topicslist  = $this->getTopics();
		$compare     = null;

		if (!$keywords && !$row->studyintro)
		{
			$keygo = false;
		}

		if (!$topics)
		{
			$topicsgo = false;
		}

		if (!$keygo && !$topicsgo)
		{
			return false;
		}

		$studies = $this->getStudies();

		if ($studies)
		{
			foreach ($studies as $study)
			{
				if (is_string($study->params) && !empty($study->params) && $study->params !== "{}")
				{
					if (json_decode($study->params, false, 512, JSON_INVALID_UTF8_IGNORE))
					{
						$registry      = new Registry;
						$registry->loadString($study->params);
						$sparams = $registry;
						$compare = $sparams->get('metakey');
					}
				}

				if ($compare)
				{
					$this->parseKeys($keywords, $compare, $study->id);
				}

				if ($study->tp_id)
				{
					$this->parseKeys($topicslist, $study->tp_id, $study->id);
				}
			}
		}
		else
		{
			return false;
		}

		// Only one item in score here
		if (!$this->score)
		{
			return false;
		}

		return $this->getRelatedLinks($row->id);
	}

	/**
	 * Get Topics for rendering.
	 *
	 * @return string
	 *
	 * @since    7.2
	 */
	public function getTopics(): string
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery('true');
		$query->select('id');
		$query->from('#__bsms_topics');
		$query->where('published = 1');
		$db->setQuery($query);
		$topics     = $db->loadObjectList();
		$topicslist = [];

		foreach ($topics as $value)
		{
			foreach ($value as $v)
			{
				$topicslist[] = $v;
			}
		}

		return implode(',', $topicslist);
	}

	/**
	 * Remove Common Words form render.
	 *
	 * @param   string  $input  Home
	 *
	 * @return array
	 *
	 * @since    7.2
	 */
	public function removeCommonWords(string $input): array
	{
		$commonWords = array(
			'a',
			'able',
			'about',
			'above',
			'abroad',
			'according',
			'accordingly',
			'across',
			'actually',
			'adj',
			'after',
			'afterwards',
			'again',
			'against',
			'ago',
			'ahead',
			'ain\'t',
			'all',
			'allow',
			'allows',
			'almost',
			'alone',
			'along',
			'alongside',
			'already',
			'also',
			'although',
			'always',
			'am',
			'amid',
			'amidst',
			'among',
			'amongst',
			'an',
			'and',
			'another',
			'any',
			'anybody',
			'anyhow',
			'anyone',
			'anything',
			'anyway',
			'anyways',
			'anywhere',
			'apart',
			'appear',
			'appreciate',
			'appropriate',
			'are',
			'aren\'t',
			'around',
			'as',
			'a\'s',
			'aside',
			'ask',
			'asking',
			'associated',
			'at',
			'available',
			'away',
			'awfully',
			'b',
			'back',
			'backward',
			'backwards',
			'be',
			'became',
			'because',
			'become',
			'becomes',
			'becoming',
			'been',
			'before',
			'beforehand',
			'begin',
			'behind',
			'being',
			'below',
			'beside',
			'besides',
			'best',
			'better',
			'between',
			'beyond',
			'both',
			'brief',
			'but',
			'by',
			'c',
			'came',
			'can',
			'cannot',
			'cant',
			'can\'t',
			'caption',
			'cause',
			'causes',
			'certain',
			'certainly',
			'changes',
			'clearly',
			'c\'mon',
			'co',
			'co.',
			'com',
			'comes',
			'concerning',
			'consequently',
			'consider',
			'considering',
			'contain',
			'containing',
			'contains',
			'corresponding',
			'could',
			'couldn\'t',
			'course',
			'c\'s',
			'currently',
			'd',
			'dare',
			'daren\'t',
			'definitely',
			'described',
			'despite',
			'did',
			'didn\'t',
			'different',
			'directly',
			'do',
			'does',
			'doesn\'t',
			'doing',
			'done',
			'don\'t',
			'down',
			'downwards',
			'during',
			'e',
			'each',
			'edu',
			'eg',
			'eight',
			'eighty',
			'either',
			'else',
			'elsewhere',
			'end',
			'ending',
			'enough',
			'entirely',
			'especially',
			'et',
			'etc',
			'even',
			'ever',
			'evermore',
			'every',
			'everybody',
			'everyone',
			'everything',
			'everywhere',
			'ex',
			'exactly',
			'example',
			'except',
			'f',
			'fairly',
			'far',
			'farther',
			'few',
			'fewer',
			'fifth',
			'first',
			'five',
			'followed',
			'following',
			'follows',
			'for',
			'forever',
			'former',
			'formerly',
			'forth',
			'forward',
			'found',
			'four',
			'from',
			'further',
			'furthermore',
			'g',
			'get',
			'gets',
			'getting',
			'given',
			'gives',
			'go',
			'goes',
			'going',
			'gone',
			'got',
			'gotten',
			'greetings',
			'h',
			'had',
			'hadn\'t',
			'half',
			'happens',
			'hardly',
			'has',
			'hasn\'t',
			'have',
			'haven\'t',
			'having',
			'he',
			'he\'d',
			'he\'ll',
			'hello',
			'help',
			'hence',
			'her',
			'here',
			'hereafter',
			'hereby',
			'herein',
			'here\'s',
			'hereupon',
			'hers',
			'herself',
			'he\'s',
			'he',
			'hi',
			'him',
			'himself',
			'his',
			'hither',
			'hopefully',
			'how',
			'howbeit',
			'however',
			'hundred',
			'i',
			'i\'d',
			'ie',
			'if',
			'ignored',
			'i\'ll',
			'i\'m',
			'immediate',
			'in',
			'inasmuch',
			'inc',
			'inc.',
			'indeed',
			'indicate',
			'indicated',
			'indicates',
			'inner',
			'inside',
			'insofar',
			'instead',
			'into',
			'inward',
			'is',
			'isn\'t',
			'it',
			'it\'d',
			'it\'ll',
			'its',
			'it\'s',
			'itself',
			'i\'ve',
			'j',
			'just',
			'k',
			'keep',
			'keeps',
			'kept',
			'know',
			'known',
			'knows',
			'l',
			'last',
			'lately',
			'later',
			'latter',
			'latterly',
			'least',
			'less',
			'lest',
			'let',
			'let\'s',
			'like',
			'liked',
			'likely',
			'likewise',
			'little',
			'look',
			'looking',
			'looks',
			'low',
			'lower',
			'ltd',
			'm',
			'made',
			'mainly',
			'make',
			'makes',
			'many',
			'may',
			'maybe',
			'mayn\'t',
			'me',
			'mean',
			'meantime',
			'meanwhile',
			'merely',
			'might',
			'mightn\'t',
			'mine',
			'minus',
			'miss',
			'more',
			'moreover',
			'most',
			'mostly',
			'mr',
			'mrs',
			'much',
			'must',
			'mustn\'t',
			'my',
			'myself',
			'n',
			'name',
			'namely',
			'nd',
			'near',
			'nearly',
			'necessary',
			'need',
			'needn\'t',
			'needs',
			'neither',
			'never',
			'neverf',
			'neverless',
			'nevertheless',
			'new',
			'next',
			'nine',
			'ninety',
			'no',
			'nobody',
			'non',
			'none',
			'nonetheless',
			'noone',
			'no-one',
			'nor',
			'normally',
			'not',
			'nothing',
			'notwithstanding',
			'novel',
			'now',
			'nowhere',
			'o',
			'obviously',
			'of',
			'off',
			'often',
			'oh',
			'ok',
			'okay',
			'old',
			'on',
			'once',
			'one',
			'ones',
			'one\'s',
			'only',
			'onto',
			'opposite',
			'or',
			'other',
			'others',
			'otherwise',
			'ought',
			'oughtn\'t',
			'our',
			'ours',
			'ourselves',
			'out',
			'outside',
			'over',
			'overall',
			'own',
			'p',
			'particular',
			'particularly',
			'past',
			'per',
			'perhaps',
			'placed',
			'please',
			'plus',
			'possible',
			'presumably',
			'probably',
			'provided',
			'provides',
			'q',
			'que',
			'quite',
			'qv',
			'r',
			'rather',
			'rd',
			're',
			'really',
			'reasonably',
			'recent',
			'recently',
			'regarding',
			'regardless',
			'regards',
			'relatively',
			'respectively',
			'right',
			'round',
			's',
			'said',
			'same',
			'saw',
			'say',
			'saying',
			'says',
			'second',
			'secondly',
			'see',
			'seeing',
			'seem',
			'seemed',
			'seeming',
			'seems',
			'seen',
			'self',
			'selves',
			'sensible',
			'sent',
			'serious',
			'seriously',
			'seven',
			'several',
			'shall',
			'shan\'t',
			'she',
			'she\'d',
			'she\'ll',
			'she\'s',
			'should',
			'shouldn\'t',
			'since',
			'six',
			'so',
			'some',
			'somebody',
			'someday',
			'somehow',
			'someone',
			'something',
			'sometime',
			'sometimes',
			'somewhat',
			'somewhere',
			'soon',
			'sorry',
			'specified',
			'specify',
			'specifying',
			'still',
			'sub',
			'such',
			'sup',
			'sure',
			't',
			'take',
			'taken',
			'taking',
			'tell',
			'tends',
			'th',
			'than',
			'thank',
			'thanks',
			'thanx',
			'that',
			'that\'ll',
			'thats',
			'that\'s',
			'that\'ve',
			'the',
			'their',
			'theirs',
			'them',
			'themselves',
			'then',
			'thence',
			'there',
			'thereafter',
			'thereby',
			'there\'d',
			'therefore',
			'therein',
			'there\'ll',
			'there\'re',
			'theres',
			'there\'s',
			'thereupon',
			'there\'ve',
			'these',
			'they',
			'they\'d',
			'they\'ll',
			'they\'re',
			'they\'ve',
			'thing',
			'things',
			'think',
			'third',
			'thirty',
			'this',
			'thorough',
			'thoroughly',
			'those',
			'though',
			'three',
			'through',
			'throughout',
			'thru',
			'thus',
			'till',
			'to',
			'together',
			'too',
			'took',
			'toward',
			'towards',
			'tried',
			'tries',
			'truly',
			'try',
			'trying',
			't\'s',
			'twice',
			'two',
			'u',
			'un',
			'under',
			'underneath',
			'undoing',
			'unfortunately',
			'unless',
			'unlike',
			'unlikely',
			'until',
			'unto',
			'up',
			'upon',
			'upwards',
			'us',
			'use',
			'used',
			'useful',
			'uses',
			'using',
			'usually',
			'v',
			'value',
			'various',
			'versus',
			'very',
			'via',
			'viz',
			'vs',
			'w',
			'want',
			'wants',
			'was',
			'wasn\'t',
			'way',
			'we',
			'we\'d',
			'welcome',
			'well',
			'we\'ll',
			'went',
			'were',
			'we\'re',
			'weren\'t',
			'we\'ve',
			'what',
			'whatever',
			'what\'ll',
			'what\'s',
			'what\'ve',
			'when',
			'whence',
			'whenever',
			'where',
			'whereafter',
			'whereas',
			'whereby',
			'wherein',
			'where\'s',
			'whereupon',
			'wherever',
			'whether',
			'which',
			'whichever',
			'while',
			'whilst',
			'whither',
			'who',
			'who\'d',
			'whoever',
			'whole',
			'who\'ll',
			'whom',
			'whomever',
			'who\'s',
			'whose',
			'why',
			'will',
			'willing',
			'wish',
			'with',
			'within',
			'without',
			'wonder',
			'won\'t',
			'would',
			'wouldn\'t',
			'x',
			'y',
			'yes',
			'yet',
			'you',
			'you\'d',
			'you\'ll',
			'your',
			'you\'re',
			'yours',
			'yourself',
			'yourselves',
			'you\'ve',
			'z',
			'zero'
		);
		$content     = strip_tags($input);
		$content     = strtolower($content);
		$content     = preg_replace("/[^a-zA-Z 0-9]+/", " ", $content);
		$content     = preg_replace('/\b(' . implode('|', $commonWords) . ')\b/', '', $content);
		$content     = preg_replace('/\s\s+/', ' ', $content);
		$content     = str_replace(' ', ',', $content);
		$content     = substr($content, 1, strlen($content) - 1);
		$content     = substr($content, 0, -1);

		return explode(',', $content);
	}

	/**
	 * Get Studies
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since    7.2
	 */
	public function getStudies(): array
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery('true');
		$query->select('s.id, s.params, s.access');
		$query->from('#__bsms_studies as s');
		$query->select('group_concat(stp.id separator ", ") AS tp_id');
		$query->join('LEFT', '#__bsms_studytopics as tp on s.id = tp.study_id');
		$query->join('LEFT', '#__bsms_topics as stp on stp.id = tp.topic_id');
		$query->group('s.id');
		$query->where('s.published = 1');
		$db->setQuery($query);
		$studies = $db->loadObjectList();

		// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
		$user     = Factory::getApplication()->getIdentity();
		$groups = $user->getAuthorisedViewLevels();

		foreach ($studies as $i => $iValue)
		{
			if (($iValue->access > 1) && !in_array($iValue->access, $groups, true))
			{
				unset($studies[$i]);
			}
		}

		return $studies;
	}

	/**
	 * Parse keys
	 *
	 * @param   string  $source   String of source
	 * @param   string  $compare  String to compare
	 * @param   int     $id       ID of study
	 *
	 * @return boolean
	 *
	 * @since    7.2
	 */
	public function parseKeys(string $source, string $compare, int $id): bool
	{
		$sourceisarray  = false;
		$compareisarray = false;
		$sourcearray    = [];
		$comparearray   = [];

		if (substr_count($source, ','))
		{
			$sourcearray   = explode(',', $source);
			$sourceisarray = true;
		}

		if (substr_count($compare, ','))
		{
			$comparearray   = explode(',', $compare);
			$compareisarray = true;
		}

		if ($sourceisarray && $compareisarray)
		{
			foreach ($sourcearray as $sarray)
			{
				if (in_array($sarray, $comparearray, true))
				{
					$this->score[] = $id;
				}
			}
		}

		if (($sourceisarray && !$compareisarray && in_array($compare, $sourcearray, true))
			|| (!$sourceisarray && $compareisarray && in_array($source, $comparearray, true))
			|| (!$sourceisarray && !$compareisarray && strcmp($source, $compare))
		)
		{
			$this->score[] = $id;
		}

		return true;
	}

	/**
	 * Look for Related Links.
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @var int $id ID of the related
	 *
	 * @since    7.2
	 */
	public function getRelatedLinks(int $id): string
	{
		$db           = Factory::getContainer()->get('DatabaseDriver');
		$studyrecords = [];

		foreach ($this->score as $link)
		{
			$query = $db->getQuery('true');
			$query->select('s.studytitle, s.alias, s.id, s.booknumber, s.chapter_begin');
			$query->from('#__bsms_studies as s');
			$query->select('b.bookname');
			$query->join('LEFT', '#__bsms_books as b on b.booknumber = s.booknumber');
			$query->where('s.id = ' . (int) $link);
			$query->where('s.id != ' . $id);
			$db->setQuery($query);
			$study = $db->loadObject();

			if ($study)
			{
				$studyrecords[] = $study;
			}
		}

		$related = '<select onchange="goTo()" id="urlList"><option value="">' . Text::_('JBS_CMN_SELECT_RELATED_STUDY') . '</option>';
		$input   = Factory::getApplication()->input;

		foreach ($studyrecords as $studyrecord)
		{
			$related .= '<option value="'
				. Route::_('index.php?option=com_proclaim&view=cwmsermon&id=' . (int) $studyrecord->id . '&t=' . $input->get('t', '1', 'int'))
				. '">' . $studyrecord->studytitle;

			if (!empty($studyrecord->bookname))
			{
				$related .= ' - ' . Text::_($studyrecord->bookname)
					. ' ' . $studyrecord->chapter_begin;
			}

			$related .= '</option>';
		}

		$related .= '</select>';

		return '<div class="related"><form action="#">' . $related . '</form></div>';
	}
}
