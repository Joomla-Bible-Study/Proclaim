<?php

/**
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

defined('_JEXEC') or die;

/**
 * helper to get related studies to the current one
 *
 * @package BibleStudy.Site
 * @since 7.1.0
 */
class relatedStudies {

    /**
     *
     * @param object $row
     * @param object $params
     * @return boolean
     */
    function getRelated($row, $params) {
        $this->score = array();
        $keygo = true;
        $topicsgo = true;
        $registry = new JRegistry();
        $registry->loadJSON($row->params);
        $params = $registry;
        $keywords = $params->get('metakey');
        $topics = $row->topics_id;
        $topicslist = $this->getTopics();
        if (!$keywords) {
            if ($row->studyintro) {
                // @todo need to look if $introkeys var is needed.
                $introkeys = $this->removeCommonWords($row->studyintro);
                $keygo = true;
            } else {
                $keygo = false;
            }
        }

        if (!$topics) {
            $topicsgo = false;
        }
        if (!$keygo && !$topicsgo) {
            return false;
        }
        $studies = $this->getStudies();
        foreach ($studies as $study) {
            $registry = new JRegistry();
            $registry->loadJSON($study->params);
            $sparams = $registry;
            $compare = $sparams->get('metakey');

            if ($compare) {
                $keywordsresults = $this->parseKeys($keywords, $compare, $study->id);
            }
            if ($study->topics_id) {
                $topicsresults = $this->parseKeys($topics, $study->topics_id, $study->id);
            }
            if ($study->tp_id) {
                $studytopics = $this->parseKeys($topicslist, $study->tp_id, $study->id);
            }
        }


        if (!$this->score){return false;}
        $related = $this->getRelatedLinks($this->score, $params);
        return $related;
    }

    /**
     *
     * @param string $source
     * @param string $compare
     * @param int $id
     * @return boolean
     */
    function parseKeys($source, $compare, $id) {
        $sourceisarray = false;
        $compareisarray = false;
        $sourcearray = array();
        $comparearray = array();
        if (substr_count($source, ',')) {
            $sourcearray = explode(',', $source);
            $sourceisarray = true;
        }
        if (substr_count($compare, ',')) {
            $comparearray = explode(',', $compare);
            $compareisarray = true;
        }
        if ($sourceisarray && $compareisarray) {
            foreach ($sourcearray as $sarray) {
                if (in_array($sarray, $comparearray)) {
                    $this->score[] = $id;
                }
            }
        }
        if ($sourceisarray && !$compareisarray) {
            if (in_array($compare, $sourcearray)) {
                $this->score[] = $id;
            }
        }

        if (!$sourceisarray && $compareisarray) {
            if (in_array($source, $comparearray)) {
                $this->score[] = $id;
            }
        }

        if (!$sourceisarray && !$compareisarray) {
            if (strcmp($source, $compare)) {
                $this->score[] = $id;
            }
        }
        return true;
    }

    /**
     * Get Studies
     *
     * @return int
     */
    function getStudies() {
        $db = JFactory::getDBO();
        $query = $db->getQuery('true');
        $query->select('s.id, s.params, s.topics_id, s.access');
        $query->from('#__bsms_studies as s');
        $query->select('group_concat(stp.id separator ", ") AS tp_id');
        $query->join('LEFT', '#__bsms_studytopics as tp on s.id = tp.study_id');
        $query->join('LEFT', '#__bsms_topics as stp on stp.id = tp.topic_id');
        $query->group('s.id');
        $query->where('s.published = 1');
        $db->setQuery($query);
        //$db->query();
        $studies = $db->loadObjectList();
        //check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $count = count($studies);

        for ($i = 0; $i < $count; $i++) {

            if ($studies[$i]->access > 1) {
                if (!in_array($studies[$i]->access, $groups)) {
                    unset($studies[$i]);
                }
            }
        }

        return $studies;
    }

    /**
     * Look for Related Links.
     *
     * @param array $scored
     * @param array $params
     * @return string
     */
    function getRelatedLinks($scored, $params) {
        $db = JFactory::getDBO();
        $scored = array_count_values($scored);
        $sorted = arsort($scored);
        $output = array_slice($scored, 0, 20, true);
        $links = array();
        $studyrecords = array();
        $studyrecord = '';
        foreach ($output as $key => $value) {
            $links[] = $key;
        }
        foreach ($links as $link) {
            $query = $db->getQuery('true');
            $query->select('s.studytitle, s.alias, s.id, s.booknumber, s.chapter_begin');
            $query->from('#__bsms_studies as s');
            $query->select('b.id, b.bookname');
            $query->join('LEFT', '#__bsms_books as b on b.booknumber = s.booknumber');
            $query->where('s.id = ' . $link);
            $db->setQuery($query);
            //$db->query();
            $studyrecords[] = $db->loadObject();
        }

        $related = '<select onchange="goTo()" id="urlList"><option value="">' . JText::_('JBS_CMN_SELECT_RELATED_STUDY') . '</option>';
        foreach ($studyrecords as $studyrecord) {
            $related .= '<option value="' . JRoute::_('index.php?option=com_biblestudy&view=sermon&id=' . $studyrecord->id . '&t=' . JRequest::getInt('t', '1')) . '">' . $studyrecord->studytitle . ' - ' . JText::_($studyrecord->bookname) . ' ' . $studyrecord->chapter_begin . '</option>';
        }
        $related .= '</select>';

        $relatedlinks = '<div class="related"><form >' . $related . '</form></div>';
        return $relatedlinks;
    }

    /**
     * Get Topics for rendering.
     *
     * @return string
     */
    function getTopics() {
        $db = JFactory::getDBO();
        $query = $db->getQuery('true');
        $query->select('id');
        $query->from('#__bsms_topics');
        $query->where('published = 1');
        $db->setQuery($query);
        $topics = $db->loadObjectList();
        $topicslist = array();
        foreach ($topics as $key => $value) {
            foreach ($value as $v) {
                $topicslist[] = $v;
            }
        }
        $returntopics = implode(',', $topicslist);
        return $returntopics;
    }

    /**
     * Remove Common Words form render.
     *
     * @param string $input
     * @return array
     */
    function removeCommonWords($input) {

        $commonWords = array('a', 'able', 'about', 'above', 'abroad', 'according', 'accordingly', 'across', 'actually', 'adj', 'after', 'afterwards', 'again', 'against', 'ago', 'ahead', 'ain\'t', 'all', 'allow', 'allows', 'almost', 'alone', 'along', 'alongside', 'already', 'also', 'although', 'always', 'am', 'amid', 'amidst', 'among', 'amongst', 'an', 'and', 'another', 'any', 'anybody', 'anyhow', 'anyone', 'anything', 'anyway', 'anyways', 'anywhere', 'apart', 'appear', 'appreciate', 'appropriate', 'are', 'aren\'t', 'around', 'as', 'a\'s', 'aside', 'ask', 'asking', 'associated', 'at', 'available', 'away', 'awfully', 'b', 'back', 'backward', 'backwards', 'be', 'became', 'because', 'become', 'becomes', 'becoming', 'been', 'before', 'beforehand', 'begin', 'behind', 'being', 'below', 'beside', 'besides', 'best', 'better', 'between', 'beyond', 'both', 'brief', 'but', 'by', 'c', 'came', 'can', 'cannot', 'cant', 'can\'t', 'caption', 'cause', 'causes', 'certain', 'certainly', 'changes', 'clearly', 'c\'mon', 'co', 'co.', 'com', 'comes', 'concerning', 'consequently', 'consider', 'considering', 'contain', 'containing', 'contains', 'corresponding', 'could', 'couldn\'t', 'course', 'c\'s', 'currently', 'd', 'dare', 'daren\'t', 'definitely', 'described', 'despite', 'did', 'didn\'t', 'different', 'directly', 'do', 'does', 'doesn\'t', 'doing', 'done', 'don\'t', 'down', 'downwards', 'during', 'e', 'each', 'edu', 'eg', 'eight', 'eighty', 'either', 'else', 'elsewhere', 'end', 'ending', 'enough', 'entirely', 'especially', 'et', 'etc', 'even', 'ever', 'evermore', 'every', 'everybody', 'everyone', 'everything', 'everywhere', 'ex', 'exactly', 'example', 'except', 'f', 'fairly', 'far', 'farther', 'few', 'fewer', 'fifth', 'first', 'five', 'followed', 'following', 'follows', 'for', 'forever', 'former', 'formerly', 'forth', 'forward', 'found', 'four', 'from', 'further', 'furthermore', 'g', 'get', 'gets', 'getting', 'given', 'gives', 'go', 'goes', 'going', 'gone', 'got', 'gotten', 'greetings', 'h', 'had', 'hadn\'t', 'half', 'happens', 'hardly', 'has', 'hasn\'t', 'have', 'haven\'t', 'having', 'he', 'he\'d', 'he\'ll', 'hello', 'help', 'hence', 'her', 'here', 'hereafter', 'hereby', 'herein', 'here\'s', 'hereupon', 'hers', 'herself', 'he\'s', 'he', 'hi', 'him', 'himself', 'his', 'hither', 'hopefully', 'how', 'howbeit', 'however', 'hundred', 'i', 'i\'d', 'ie', 'if', 'ignored', 'i\'ll', 'i\'m', 'immediate', 'in', 'inasmuch', 'inc', 'inc.', 'indeed', 'indicate', 'indicated', 'indicates', 'inner', 'inside', 'insofar', 'instead', 'into', 'inward', 'is', 'isn\'t', 'it', 'it\'d', 'it\'ll', 'its', 'it\'s', 'itself', 'i\'ve', 'j', 'just', 'k', 'keep', 'keeps', 'kept', 'know', 'known', 'knows', 'l', 'last', 'lately', 'later', 'latter', 'latterly', 'least', 'less', 'lest', 'let', 'let\'s', 'like', 'liked', 'likely', 'likewise', 'little', 'look', 'looking', 'looks', 'low', 'lower', 'ltd', 'm', 'made', 'mainly', 'make', 'makes', 'many', 'may', 'maybe', 'mayn\'t', 'me', 'mean', 'meantime', 'meanwhile', 'merely', 'might', 'mightn\'t', 'mine', 'minus', 'miss', 'more', 'moreover', 'most', 'mostly', 'mr', 'mrs', 'much', 'must', 'mustn\'t', 'my', 'myself', 'n', 'name', 'namely', 'nd', 'near', 'nearly', 'necessary', 'need', 'needn\'t', 'needs', 'neither', 'never', 'neverf', 'neverless', 'nevertheless', 'new', 'next', 'nine', 'ninety', 'no', 'nobody', 'non', 'none', 'nonetheless', 'noone', 'no-one', 'nor', 'normally', 'not', 'nothing', 'notwithstanding', 'novel', 'now', 'nowhere', 'o', 'obviously', 'of', 'off', 'often', 'oh', 'ok', 'okay', 'old', 'on', 'once', 'one', 'ones', 'one\'s', 'only', 'onto', 'opposite', 'or', 'other', 'others', 'otherwise', 'ought', 'oughtn\'t', 'our', 'ours', 'ourselves', 'out', 'outside', 'over', 'overall', 'own', 'p', 'particular', 'particularly', 'past', 'per', 'perhaps', 'placed', 'please', 'plus', 'possible', 'presumably', 'probably', 'provided', 'provides', 'q', 'que', 'quite', 'qv', 'r', 'rather', 'rd', 're', 'really', 'reasonably', 'recent', 'recently', 'regarding', 'regardless', 'regards', 'relatively', 'respectively', 'right', 'round', 's', 'said', 'same', 'saw', 'say', 'saying', 'says', 'second', 'secondly', 'see', 'seeing', 'seem', 'seemed', 'seeming', 'seems', 'seen', 'self', 'selves', 'sensible', 'sent', 'serious', 'seriously', 'seven', 'several', 'shall', 'shan\'t', 'she', 'she\'d', 'she\'ll', 'she\'s', 'should', 'shouldn\'t', 'since', 'six', 'so', 'some', 'somebody', 'someday', 'somehow', 'someone', 'something', 'sometime', 'sometimes', 'somewhat', 'somewhere', 'soon', 'sorry', 'specified', 'specify', 'specifying', 'still', 'sub', 'such', 'sup', 'sure', 't', 'take', 'taken', 'taking', 'tell', 'tends', 'th', 'than', 'thank', 'thanks', 'thanx', 'that', 'that\'ll', 'thats', 'that\'s', 'that\'ve', 'the', 'their', 'theirs', 'them', 'themselves', 'then', 'thence', 'there', 'thereafter', 'thereby', 'there\'d', 'therefore', 'therein', 'there\'ll', 'there\'re', 'theres', 'there\'s', 'thereupon', 'there\'ve', 'these', 'they', 'they\'d', 'they\'ll', 'they\'re', 'they\'ve', 'thing', 'things', 'think', 'third', 'thirty', 'this', 'thorough', 'thoroughly', 'those', 'though', 'three', 'through', 'throughout', 'thru', 'thus', 'till', 'to', 'together', 'too', 'took', 'toward', 'towards', 'tried', 'tries', 'truly', 'try', 'trying', 't\'s', 'twice', 'two', 'u', 'un', 'under', 'underneath', 'undoing', 'unfortunately', 'unless', 'unlike', 'unlikely', 'until', 'unto', 'up', 'upon', 'upwards', 'us', 'use', 'used', 'useful', 'uses', 'using', 'usually', 'v', 'value', 'various', 'versus', 'very', 'via', 'viz', 'vs', 'w', 'want', 'wants', 'was', 'wasn\'t', 'way', 'we', 'we\'d', 'welcome', 'well', 'we\'ll', 'went', 'were', 'we\'re', 'weren\'t', 'we\'ve', 'what', 'whatever', 'what\'ll', 'what\'s', 'what\'ve', 'when', 'whence', 'whenever', 'where', 'whereafter', 'whereas', 'whereby', 'wherein', 'where\'s', 'whereupon', 'wherever', 'whether', 'which', 'whichever', 'while', 'whilst', 'whither', 'who', 'who\'d', 'whoever', 'whole', 'who\'ll', 'whom', 'whomever', 'who\'s', 'whose', 'why', 'will', 'willing', 'wish', 'with', 'within', 'without', 'wonder', 'won\'t', 'would', 'wouldn\'t', 'x', 'y', 'yes', 'yet', 'you', 'you\'d', 'you\'ll', 'your', 'you\'re', 'yours', 'yourself', 'yourselves', 'you\'ve', 'z', 'zero');
        $content = strip_tags($input);
        $content = strtolower($content);
        $content = preg_replace("/[^a-zA-Z 0-9]+/", " ", $content);
        $content = preg_replace('/\b(' . implode('|', $commonWords) . ')\b/', '', $content);
        $content = preg_replace('/\s\s+/', ' ', $content);
        $content = str_replace(' ', ',', $content);
        $content = substr($content, 1, strlen($content) - 1);
        $content = substr($content, 0, -1);
        $contentarray = explode(',', $content);

        return $contentarray;
    }

}