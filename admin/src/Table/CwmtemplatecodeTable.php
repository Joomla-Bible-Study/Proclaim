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
use Joomla\CMS\Access\Rule;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;

/**
 * TemplateCode table class
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class CwmtemplatecodeTable extends Table
{
    /**
     * ID of the record
     *
     * @var int|null
     *
     * @since 9.0.0
     */
    public ?int $id = null;

    /**
     * File Name
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $filename = null;

    /**
     * Location ID (multi-campus)
     *
     * @var int|null
     * @since 10.1.0
     */
    public ?int $location_id = null;

    /**
     * Type
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $type = null;

    /**
     * Template Code
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $templatecode = null;

    /**
     * Created date
     *
     * @var string|null
     * @since 10.1.0
     */
    public ?string $created = null;

    /**
     * Created by user ID
     *
     * @var int|null
     * @since 10.1.0
     */
    public ?int $created_by = null;

    /**
     * Created by alias
     *
     * @var string
     * @since 10.1.0
     */
    public ?string $created_by_alias = '';

    /**
     * Modified date
     *
     * @var string|null
     * @since 10.1.0
     */
    public ?string $modified = null;

    /**
     * Modified by user ID
     *
     * @var int|null
     * @since 10.1.0
     */
    public ?int $modified_by = null;

    /**
     * Checked out user ID
     *
     * @var int|null
     * @since 10.1.0
     */
    public ?int $checked_out = null;

    /**
     * Checked out time
     *
     * @var string|null
     * @since 10.1.0
     */
    public ?string $checked_out_time = null;

    /**
     * Where a record of each type writes its layout, relative to the site root.
     *
     * ⚠️ Lower case, because that is what the package ships and what Joomla
     * looks for. The folders were renamed in 2022 (50b3cd85e, "Renaming tmpl
     * folders as joomla wants them small case … Found this out because it
     * wouldn't work on Dreamhost") and these paths were not renamed with them.
     *
     * On a case-insensitive filesystem — macOS, most Windows — the old
     * capitalised spelling resolved to the same file and nothing looked wrong.
     * On a case-sensitive one, which is essentially all Linux hosting, writing
     * to `tmpl/Cwmsermons` created a second directory the front end never
     * reads: editing template code appeared to save and changed nothing.
     *
     * Kept in one place because there were three copies of this map — here in
     * store(), here again in delete(), and a third in
     * CwmbackupController::recreateTemplatecodeFiles() — and drifting apart is
     * how the rename came to be missed twice over.
     *
     * @var    array<int, string>
     * @since  __DEPLOY_VERSION__
     */
    public const array LAYOUT_DIRECTORIES = [
        1 => 'components/com_proclaim/tmpl/cwmsermons',
        2 => 'components/com_proclaim/tmpl/cwmsermon',
        3 => 'components/com_proclaim/tmpl/cwmteachers',
        4 => 'components/com_proclaim/tmpl/cwmteacher',
        5 => 'components/com_proclaim/tmpl/cwmseriesdisplays',
        6 => 'components/com_proclaim/tmpl/cwmseriesdisplay',
        7 => 'modules/mod_proclaim/tmpl',
    ];

    /**
     * The absolute path a record of this type and filename writes to.
     *
     * @param   int     $type      The record's template type
     * @param   string  $filename  The layout filename, including `default_`
     *
     * @return  string|null  Null for a type with no layout directory
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function layoutPath(int $type, string $filename): ?string
    {
        if (!isset(self::LAYOUT_DIRECTORIES[$type])) {
            return null;
        }

        return JPATH_ROOT . '/' . self::LAYOUT_DIRECTORIES[$type] . '/' . $filename;
    }

    /**
     * Delete the copy an older version wrote to the capitalised directory.
     *
     * ⚠️ Guarded on realpath rather than on the name. Where the two spellings
     * are the same file — every case-insensitive filesystem, which includes the
     * machine most of this is developed on — deleting the "stray" would delete
     * the layout just written. They are only ever different files on the hosts
     * that had the bug in the first place.
     *
     * @param   int     $type      The record's template type
     * @param   string  $filename  The layout filename
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function removeCapitalisedTwin(int $type, string $filename): void
    {
        $correct = self::layoutPath($type, $filename);

        if ($correct === null || !isset(self::LAYOUT_DIRECTORIES[$type])) {
            return;
        }

        $directory = self::LAYOUT_DIRECTORIES[$type];
        $base      = basename($directory);

        // mod_proclaim's `tmpl` has no capitalised variant to worry about.
        if (!str_starts_with($base, 'cwm')) {
            return;
        }

        $stray = JPATH_ROOT . '/' . \dirname($directory) . '/' . ucfirst($base) . '/' . $filename;

        if (!is_file($stray)) {
            return;
        }

        if (realpath($stray) === realpath($correct)) {
            return;
        }

        File::delete($stray);
    }

    /**
     * Constructor
     *
     * @param     $db  DatabaseInterface connector object
     *
     * @since 9.0.0
     */
    public function __construct(&$db)
    {
        parent::__construct('#__bsms_templatecode', 'id', $db);
    }

    /**
     * Perform pre-save checks on the table properties.
     *
     * @return  bool  True if checks pass.
     *
     * @throws  \UnexpectedValueException
     *
     * @since   10.1.0
     */
    #[\Override]
    public function check(): bool
    {
        if (trim($this->filename ?? '') === '') {
            throw new \UnexpectedValueException(Text::_('JBS_CMN_ERROR_FILENAME_REQUIRED'));
        }

        if (
            $this->filename === 'main' ||
            $this->filename === 'simple' ||
            $this->filename === 'custom' ||
            $this->filename === 'formheader' ||
            $this->filename === 'formfooter'
        ) {
            throw new \UnexpectedValueException(Text::_('JBS_STYLE_RESTRICTED_FILE_NAME'));
        }

        $type = (int) $this->type;

        if ($type < 1 || $type > 7) {
            throw new \UnexpectedValueException(Text::_('JBS_CMN_ERROR_INVALID_TEMPLATE_TYPE'));
        }

        return parent::check();
    }

    /**
     * Method to bind an associative array or object to the Table instance.This
     * method only binds properties that are publicly accessible and optionally
     * takes an array of properties to ignore when binding.
     *
     * @param   array|object  $src     An associative array or object to bind to the Table instance.
     * @param   array|string  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  bool  True on success.
     *
     * @link    http://docs.joomla.org/Table/bind
     * @since   11.1
     */
    #[\Override]
    public function bind($src, $ignore = ''): bool
    {
        // Bind the rules.
        if (isset($src['rules']) && \is_array($src['rules'])) {
            $rules = new Rule($src['rules']);
            $this->setRules($rules);
        }

        // Cast typed int properties to prevent PHP 8.3 TypeError when form posts strings
        foreach ([
            'id', 'location_id', 'created_by', 'modified_by', 'checked_out',
        ] as $field) {
            if (isset($src[$field])) {
                $src[$field] = $src[$field] !== '' ? (int) $src[$field] : null;
            }
        }

        return parent::bind($src, $ignore);
    }

    /**
     * Overridden Table::store to set modified data and user id.
     *
     * @param   bool  $updateNulls  True to update fields even if they are null.
     *
     * @return  bool  True on success.
     *
     * @throws \Exception
     * @since    1.6
     */
    #[\Override]
    public function store($updateNulls = false): bool
    {
        // Write the file
        $templateType = $this->type;
        $filename     = 'default_' . $this->filename . '.php';

        $file = self::layoutPath((int) $templateType, $filename);

        $templateCodeContent = $this->templatecode;

        // Check to see if there is the required code in the file
        $templateCheckString = "defined('_JEXEC') or die;";
        $required            = substr_count($templateCodeContent, $templateCheckString);

        if (!$required) {
            $templateCodeContent = $templateCheckString . $templateCodeContent;
        }

        if (!File::write($file, $templateCodeContent)) {
            Factory::getApplication()->enqueueMessage('JBS_STYLE_FILENAME_NOT_UNIQUE', 'error');

            return false;
        }

        // An older version of this method wrote to the capitalised directory.
        // On a case-sensitive host that copy is still sitting there, ignored by
        // the front end and confusing to anyone who finds it.
        self::removeCapitalisedTwin((int) $templateType, $filename);

        $result = parent::store($updateNulls);

        // Unconditional: Table::store() runs its asset block even when the
        // INSERT threw, so a failed save leaves a com_proclaim.<section>.0 row
        // behind for a record that was never created.
        Cwmassets::stripEmptyAssetRow($this);

        return $result;
    }

    /**
     * Method to delete a row from the database table by primary key value.
     *
     * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
     *
     * @return  bool  True on success.
     *
     * @throws \Exception
     * @since   11.1
     * @link    http://docs.joomla.org/Table/delete
     */
    #[\Override]
    public function delete($pk = null): bool
    {
        $filename     = 'default_' . $this->filename . '.php';
        $templateType = $this->type;

        $file = self::layoutPath((int) $templateType, $filename);

        if ($file !== null && file_exists($file) && !File::delete($file)) {
            Factory::getApplication()->enqueueMessage('JBS_STYLE_FILENAME_NOT_DELETED', 'error');

            return false;
        }

        // And the one an older version may have written beside it.
        self::removeCapitalisedTwin((int) $templateType, $filename);

        return parent::delete($pk);
    }

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form `table_name.id`
     * where id is the value of the primary key of the table.
     *
     * @return  string
     *
     * @since       1.6
     */
    #[\Override]
    protected function _getAssetName(): string
    {
        $k = $this->_tbl_key;

        return 'com_proclaim.templatecode.' . (int)$this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return      string
     *
     * @since       1.6
     */
    #[\Override]
    protected function _getAssetTitle(): string
    {
        return 'JBS Templatecode ' . $this->filename;
    }

    /**
     * Method to get the parent asset under which to register this one.
     * By default, all assets are registered to the ROOT node with ID 1.
     * The extended class can define a table and id to lookup.  If the
     * asset does not exist it will be created.
     *
     * @param   ?Table  $table  A Table object for the asset parent.
     * @param   null    $id     Id to look up
     *
     * @return  int
     *
     * @since   11.1
     */
    #[\Override]
    protected function _getAssetParentId(?Table $table = null, $id = null): int
    {
        // Parent to the section, so a rule on com_proclaim.templatecode reaches
        // this record's own asset instead of being bypassed by it.
        return Cwmassets::sectionParentId('templatecode');
    }
}
