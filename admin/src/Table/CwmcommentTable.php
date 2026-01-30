<?php

/**
 * Part of Proclaim Package
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
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Table class for Comment
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmcommentTable extends Table
{
    /**
     * Primary Key
     *
     * @var int|null
     * @since 7.0.0
     */
    public ?int $id = null;

    /**
     * Published
     *
     * @var int
     * @since 7.0.0
     */
    public int $published = 1;

    /**
     * Study ID
     *
     * @var int|null
     * @since 7.0.0
     */
    public ?int $study_id = null;

    /**
     * User ID
     *
     * @var int|null
     * @since 7.0.0
     */
    public ?int $user_id = null;

    /**
     * Full Name
     *
     * @var string|null
     * @since 7.0.0
     */
    public ?string $full_name = null;

    /**
     * User Email
     *
     * @var string|null
     * @since 7.0.0
     */
    public ?string $user_email = null;

    /**
     * Comment Date
     *
     * @var string|null
     * @since 7.0.0
     */
    public ?string $comment_date = null;

    /**
     * Comment Text
     *
     * @var string|null
     * @since 7.0.0
     */
    public ?string $comment_text = null;

    /**
     * Asset ID
     *
     * @var int|null
     * @since 7.0.0
     */
    public ?int $asset_id = null;

    /**
     * Access Level
     *
     * @var int|null
     * @since 7.0.0
     */
    public ?int $access = null;

    /**
     * Language
     *
     * @var string|null
     * @since 7.0.0
     */
    public ?string $language = null;

    /**
     * Created date
     *
     * @var string|null
     * @since 10.0.0
     */
    public ?string $created = null;

    /**
     * Created by user ID
     *
     * @var int|null
     * @since 10.0.0
     */
    public ?int $created_by = null;

    /**
     * Created by alias
     *
     * @var string
     * @since 10.0.0
     */
    public string $created_by_alias = '';

    /**
     * Modified date
     *
     * @var string|null
     * @since 10.0.0
     */
    public ?string $modified = null;

    /**
     * Modified by user ID
     *
     * @var int|null
     * @since 10.0.0
     */
    public ?int $modified_by = null;

    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database connector object
     *
     * @since   7.0.0
     */
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__bsms_comments', 'id', $db);
    }

    /**
     * Method to store a row in the database from the Table instance properties.
     * If a primary key value is set, the row with that primary key value will be
     * updated with the instance property values.  If no primary key value is set
     * A new row will be inserted into the database with the properties from the
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
        if (!$this->_rules) {
            $this->setRules(
                '{"core.delete":[],"core.edit":[],"core.create":[],"core.edit.state":[],"core.edit.own":[]}'
            );
        }

        return parent::store($updateNulls);
    }

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form `table_name.id`
     * where id is the value of the primary key of the table.
     *
     * @return  string
     *
     * @since   1.6
     */
    #[\Override]
    protected function _getAssetName(): string
    {
        $k = $this->_tbl_key;

        return 'com_proclaim.comment.' . (int)$this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return  string
     *
     * @since   1.6
     */
    #[\Override]
    protected function _getAssetTitle(): string
    {
        return 'JBS Comment - id/date: ' . $this->user_id . ' - ' . $this->comment_date;
    }

    /**
     * Overloaded check function
     *
     * @return  bool  True on success, false on failure
     *
     * @throws \Exception
     * @since 10.1.0
     */
    #[\Override]
    public function check(): bool
    {
        // We check for null specifically to allow 0 (Guest) as a valid user_id
        if ($this->user_id === null) {
            $this->user_id = (int) Factory::getApplication()->getIdentity()->id;
        }

        return parent::check();
    }

    /**
     * Method to get the parent asset under which to register this one.
     * By default, all assets are registered to the ROOT node with ID 1.
     * The extended class can define a table and id to lookup.  If the
     * asset does not exist it will be created.
     *
     * @param   Table|null  $table  A Table object for the asset parent.
     * @param   int|null    $id     Id to look up
     *
     * @return  int
     *
     * @since   1.6
     */
    #[\Override]
    protected function _getAssetParentId(?Table $table = null, $id = null): int
    {
        // Get Proclaim Root ID
        return Cwmassets::parentId();
    }
}
