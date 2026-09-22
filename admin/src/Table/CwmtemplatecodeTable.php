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
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
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
     * @since  10.6.0
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
     * The layouts the package itself ships, by template type.
     *
     * ⚠️ A record writes `default_<filename>.php` into its type's directory, so
     * a record named after one of these **overwrites a file the package ships**
     * -- and deleting that record then removes it. The shipped `default.php`
     * for each view calls these sublayouts unconditionally
     * (`cwmsermon/default.php:70` does `loadTemplate('footer')`), and Joomla
     * throws a 500 when a sublayout is missing. So the failure is not "an
     * update reverts my customization", it is the front end going down.
     *
     * Kept as a list rather than detected at runtime because a file on disk
     * cannot say whether the package put it there. `ShippedLayoutsTest` walks
     * the real directories and fails if this drifts from them in either
     * direction.
     *
     * @var    array<int, array<int, string>>
     * @since  __DEPLOY_VERSION__
     */
    public const array SHIPPED_LAYOUTS = [
        1 => ['formfooter', 'formheader', 'main', 'simple', 'simple2'],
        2 => ['commentsform', 'footer', 'footerlink', 'header', 'main', 'simple'],
        3 => ['main'],
        4 => ['cards', 'list', 'main'],
        5 => ['custom', 'main'],
        6 => ['custom', 'main'],
        7 => ['main', 'simple'],
    ];

    /**
     * Names refused for every type, whether or not the package ships one there.
     *
     * ⚠️ Kept as its own list on purpose. These five have always been refused
     * for all seven types, and several types ship no file of that name -- so
     * deriving the refusals from SHIPPED_LAYOUTS alone would *permit* around
     * twenty names that are refused today. Widening what is allowed is not what
     * this list is for.
     *
     * @var    array<int, string>
     * @since  __DEPLOY_VERSION__
     */
    private const array RESERVED_FILENAMES = ['main', 'simple', 'custom', 'formheader', 'formfooter'];

    /**
     * Whether the package ships a layout of this name for this type.
     *
     * @param   int          $type      The record's template type
     * @param   string|null  $filename  The record's filename
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function isShippedLayout(int $type, ?string $filename): bool
    {
        return \in_array(trim((string) $filename), self::SHIPPED_LAYOUTS[$type] ?? [], true);
    }

    /**
     * Whether a name may not be used for a new record of this type.
     *
     * @param   int          $type      The record's template type
     * @param   string|null  $filename  The record's filename
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function isReservedFilename(int $type, ?string $filename): bool
    {
        return \in_array(trim((string) $filename), self::RESERVED_FILENAMES, true)
            || self::isShippedLayout($type, $filename);
    }

    /**
     * The pattern a layout filename must match in full.
     *
     * ⚠️ The security property is the absence of a path separator, not the
     * absence of a dot. `default_` and `.php` are wrapped around this value, so
     * `..` is never a whole path segment and cannot walk anywhere on its own —
     * but `a/../../../x` is three real segments, and Joomla's `File::write()`
     * creates the intermediate directory for you, so the escape is reachable.
     * Dots stay legal because layout names in the wild carry them and rejecting
     * them would make an existing record unsavable without buying anything.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private const string FILENAME_PATTERN = '/^[A-Za-z0-9._-]+$/';

    /**
     * The guard every generated layout opens with.
     *
     * Written unconditionally and as real PHP. `?>` swallows the single newline
     * that follows it, so prepending this changes what the file *outputs* by
     * nothing at all, whatever the stored code happens to start with.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const string LAYOUT_GUARD = '<?php \defined(\'_JEXEC\') or die; ?>' . "\n";

    /**
     * Whether a filename may be composed into a layout path.
     *
     * @param   string|null  $filename  The record's filename, without `default_` or `.php`
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function isValidLayoutFilename(?string $filename): bool
    {
        $filename = trim((string) $filename);

        // A leading dot would be composed away by the `default_` prefix, but a
        // name that tries for one is not a layout name; say so rather than
        // quietly accepting it.
        if ($filename === '' || str_starts_with($filename, '.') || str_contains($filename, '..')) {
            return false;
        }

        return (bool) preg_match(self::FILENAME_PATTERN, $filename);
    }

    /**
     * The absolute path a record of this type and filename writes to.
     *
     * ⚠️ Takes the record's `filename` as stored — **not** the composed
     * `default_x.php`. Composing it here is the point: it was done at four call
     * sites, and the one that skipped this method is how the restore path came
     * to have no validation at all. Renamed from `layoutPath()` so that a
     * caller still passing a composed name fails to resolve instead of
     * type-checking clean and writing to `default_default_x.php.php`.
     *
     * @param   int          $type      The record's template type
     * @param   string|null  $filename  The record's filename
     *
     * @return  string|null  Null for an unknown type or a filename that cannot be composed safely
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function layoutPathForRecord(int $type, ?string $filename): ?string
    {
        if (!isset(self::LAYOUT_DIRECTORIES[$type]) || !self::isValidLayoutFilename($filename)) {
            return null;
        }

        $base = JPATH_ROOT . '/' . self::LAYOUT_DIRECTORIES[$type];
        $path = $base . '/default_' . trim((string) $filename) . '.php';

        // Defence in depth. With separators rejected above the composed path is
        // already inside $base; this catches the case that check cannot see —
        // a symlink standing where the layout directory should be.
        $realBase = realpath($base);
        $realDir  = realpath(\dirname($path));

        if ($realBase !== false && $realDir !== false && $realDir !== $realBase) {
            return null;
        }

        return $path;
    }

    /**
     * The bytes a layout file is written with.
     *
     * @param   string  $templateCode  The record's stored code
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function layoutFileContents(string $templateCode): string
    {
        return self::LAYOUT_GUARD . $templateCode;
    }

    /**
     * Write a record's layout file.
     *
     * The only place a layout is written. Both callers — a save through this
     * table and a rebuild after a restore — go through here, so the filename
     * constraint and the guard cannot be true of one and not the other.
     *
     * @param   int          $type          The record's template type
     * @param   string|null  $filename      The record's filename
     * @param   string       $templateCode  The record's stored code
     *
     * @return  bool  False if the path could not be composed or the write failed
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function writeLayout(int $type, ?string $filename, string $templateCode): bool
    {
        $file = self::layoutPathForRecord($type, $filename);

        if ($file === null) {
            return false;
        }

        return File::write($file, self::layoutFileContents($templateCode));
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
     * @param   int          $type      The record's template type
     * @param   string|null  $filename  The record's filename
     *
     * @return  void
     *
     * @since   10.6.0
     */
    private static function removeCapitalisedTwin(int $type, ?string $filename): void
    {
        $correct = self::layoutPathForRecord($type, $filename);

        if ($correct === null || !isset(self::LAYOUT_DIRECTORIES[$type])) {
            return;
        }

        $directory = self::LAYOUT_DIRECTORIES[$type];
        $base      = basename($directory);

        // mod_proclaim's `tmpl` has no capitalised variant to worry about.
        if (!str_starts_with($base, 'cwm')) {
            return;
        }

        $stray = JPATH_ROOT . '/' . \dirname($directory) . '/' . ucfirst($base)
            . '/default_' . trim((string) $filename) . '.php';

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
     * Whether this record already carries this name and type in the database.
     *
     * The test for "the user is editing something that already exists" rather
     * than creating a collision. Only the names added to the refusal list in
     * __DEPLOY_VERSION__ can reach it: the five in RESERVED_FILENAMES have
     * always been refused, so no stored row can hold one.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    private function keepsItsStoredFilename(): bool
    {
        if ((int) $this->id <= 0) {
            return false;
        }

        // ⚠️ A local, not $this->id. bind() holds the value by reference, and
        // a typed property bound by-ref is the documented hazard.
        $id = (int) $this->id;

        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName(['filename', 'type']))
            ->from($db->quoteName('#__bsms_templatecode'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);

        try {
            $stored = $db->setQuery($query)->loadObject();
        } catch (\Exception) {
            // ⚠️ Refuse on a failed read rather than grandfather blindly --
            // the permissive branch is the one that overwrites a shipped file.
            return false;
        }

        return $stored !== null
            && trim((string) $stored->filename) === trim((string) $this->filename)
            && (int) $stored->type === (int) $this->type;
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

        // ⚠️ Grandfathered, not waived. A record created before this rule
        // existed already overwrote the shipped layout, so its code is the only
        // copy at that path -- refusing the save would strand it with no way to
        // get the content back out. A new record of that name is refused.
        if (self::isReservedFilename((int) $this->type, $this->filename) && !$this->keepsItsStoredFilename()) {
            throw new \UnexpectedValueException(
                self::isShippedLayout((int) $this->type, $this->filename)
                    ? Text::sprintf('JBS_STYLE_SHIPPED_FILE_NAME', (string) $this->filename)
                    : Text::_('JBS_STYLE_RESTRICTED_FILE_NAME')
            );
        }

        // ⚠️ Rejected here as well as in the write path, so the user gets a
        // field error instead of a save that reports success and writes nothing.
        if (!self::isValidLayoutFilename($this->filename)) {
            throw new \UnexpectedValueException(
                Text::sprintf('JBS_CMN_ERROR_FILENAME_INVALID', $this->filename)
            );
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
        $templateType = (int) $this->type;

        // ⚠️ Named separately from the write failure below. store() is reached
        // without check() having run -- the template importer loads a row and
        // stores it to get the file written -- and there the only report of a
        // refused filename is whatever is said here.
        if (!self::isValidLayoutFilename($this->filename)) {
            $reason = Text::sprintf('JBS_CMN_ERROR_FILENAME_INVALID', (string) $this->filename);

            Factory::getApplication()->enqueueMessage($reason, 'error');
            Log::add(
                'Template code ' . (int) $this->id . ' not saved: ' . $reason,
                Log::WARNING,
                'com_proclaim'
            );

            return false;
        }

        if (!self::writeLayout($templateType, $this->filename, (string) $this->templatecode)) {
            Factory::getApplication()->enqueueMessage(Text::_('JBS_STYLE_FILENAME_NOT_WRITTEN'), 'error');
            Log::add(
                'Template code ' . (int) $this->id . ' not saved: layout file could not be written to '
                . var_export(self::layoutPathForRecord($templateType, $this->filename), true),
                Log::WARNING,
                'com_proclaim'
            );

            return false;
        }

        // An older version of this method wrote to the capitalised directory.
        // On a case-sensitive host that copy is still sitting there, ignored by
        // the front end and confusing to anyone who finds it.
        self::removeCapitalisedTwin($templateType, $this->filename);

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
        $templateType = (int) $this->type;

        $file = self::layoutPathForRecord($templateType, $this->filename);

        if (self::isShippedLayout($templateType, $this->filename)) {
            // ⚠️ The row goes, the file stays. This name can only belong to a
            // record that predates the refusal in check(), and the file at that
            // path is one the package ships: `default.php` calls it with
            // loadTemplate() unconditionally, and Joomla answers a missing
            // sublayout with a 500. Deleting it would take the front end down.
            Factory::getApplication()->enqueueMessage(
                Text::sprintf('JBS_STYLE_SHIPPED_FILE_KEPT', (string) $this->filename),
                'warning'
            );
            Log::add(
                'Template code ' . (int) $this->id . ' deleted, but ' . $file
                . ' was kept: it is a layout the package ships.',
                Log::WARNING,
                'com_proclaim'
            );

            return parent::delete($pk);
        }

        if ($file === null) {
            // The row is still removed — refusing to delete it would strand a
            // record nothing can edit. Say so, because a layout an older
            // version wrote under this name is being left behind on disk.
            Log::add(
                'Template code ' . (int) $this->id . ' has no composable layout path'
                . ' (type ' . $templateType . ', filename ' . var_export($this->filename, true) . ');'
                . ' any file it wrote is left in place.',
                Log::WARNING,
                'com_proclaim'
            );
        } elseif (file_exists($file) && !File::delete($file)) {
            Factory::getApplication()->enqueueMessage(Text::_('JBS_STYLE_FILENAME_NOT_DELETED'), 'error');

            return false;
        }

        // And the one an older version may have written beside it.
        self::removeCapitalisedTwin($templateType, $this->filename);

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
