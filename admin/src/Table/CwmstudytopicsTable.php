<?php

/**
 * Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;

/**
 * StudyTopics table class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmstudytopicsTable extends Table
{
    /**
     * ID
     *
     * @var int|null
     *
     * @since 9.0.0
     */
    public ?int $id = null;

    /**
     * Study ID
     *
     * @var int|null
     *
     * @since 9.0.0
     */
    public ?int $study_id = null;

    /**
     * Topic ID
     *
     * @var int|null
     *
     * @since 9.0.0
     */
    public ?int $topic_id = null;

    /**
     * Asset ID
     *
     * @var int|null
     * @since 9.0.0
     */
    public ?int $asset_id = null;

    /**
     * Access Level
     *
     * @var int|null
     * @since 9.0.0
     */
    public ?int $access = null;

    /**
     * Object constructor to set table and key fields.  In most cases this will
     * be overridden by child classes to explicitly set the table and key fields
     * for a particular database table.
     *
     * @param   DatabaseInterface  $db  JDatabase connector object.
     *
     * @since   11.1
     */
    public function __construct(&$db)
    {
        parent::__construct('#__bsms_studytopics', 'id', $db);

        // A link row between a study and a topic is not something anyone
        // permissions. Table enables asset tracking for any table carrying an
        // asset_id column, which minted an asset per link for a section
        // access.xml never declared.
        $this->_trackAssets = false;
    }

    /**
     * Method to store a row in the database from the Table instance properties.
     * If a primary key value is set the row with that primary key value will be
     * updated with the instance property values.  If no primary key value is set
     * a new row will be inserted into the database with the properties from the
     * Table instance.
     *
     * @param   bool  $updateNulls  True to update fields even if they are null.
     *
     * @return  bool  True on success.
     *
     * @link    https://docs.joomla.org/Table/store
     * @since   11.1
     */
    #[\Override]
    public function store($updateNulls = false): bool
    {
        $result = parent::store($updateNulls);

        // Unconditional: Table::store() runs its asset block even when the
        // INSERT threw, so a failed save leaves a com_proclaim.<section>.0 row
        // behind for a record that was never created.
        Cwmassets::stripEmptyAssetRow($this);

        return $result;
    }



}
