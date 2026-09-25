<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Console;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Lib\Cwmcontentimporter;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\User\User;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * `php cli/joomla.php proclaim:import` — the only way to run
 * {@see Cwmcontentimporter} outside the admin UI or a PHPUnit test.
 *
 * Exists because `AdminModel::save()` needs a fully booted CMS application —
 * a real `bootComponent()`, a loaded identity, and resolvable content
 * plugins — none of which a raw build script (`build/seed-*.php`, all plain
 * PDO) can provide. This command is that entry point, for the E2E install
 * harness and anything else that needs to seed tracked content outside a
 * real HTTP request.
 *
 * @package  Proclaim.Admin
 * @since    __DEPLOY_VERSION__
 */
final class ImportCommand extends AbstractCommand
{
    /**
     * @var string
     * @since  __DEPLOY_VERSION__
     */
    protected static $defaultName = 'proclaim:import';

    /**
     * @param   Cwmcontentimporter  $importer  Constructed with a null factory in production, so it
     *                                         resolves models through the real bootComponent() — see
     *                                         the plugin registration this command is added from.
     * @param   DatabaseInterface   $db        Used only to resolve the default --user.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __construct(private readonly Cwmcontentimporter $importer, private readonly DatabaseInterface $db)
    {
        parent::__construct();
    }

    /**
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function configure(): void
    {
        $this->addArgument('payload', InputArgument::REQUIRED, 'Path to the JSON payload to import');
        $this->addArgument('tag', InputArgument::REQUIRED, 'Import tag — refused if already used');
        $this->addOption(
            'user',
            null,
            InputOption::VALUE_REQUIRED,
            'User id or username to import as. Defaults to the first active Super User.'
        );

        $this->setDescription('Import a tagged content set through Cwmcontentimporter.');
        $this->setHelp(
            <<<'HELP'
            <info>%command.name%</info> imports a JSON payload through Cwmcontentimporter,
            the same additive, tracked, manifest-recorded path the admin UI would use.

            Usage: <info>php %command.full_name% /path/to/payload.json my-tag</info>
                   <info>php %command.full_name% /path/to/payload.json my-tag --user=admin</info>
            HELP
        );
    }

    /**
     * @param   InputInterface   $input   Command input
     * @param   OutputInterface  $output  Command output
     *
     * @return  int  A Symfony Command::* exit code
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $app = $this->getApplication();

        // Identity and language loading are both CMS-application concerns
        // absent from the bare Joomla\Console\Application this command's own
        // unit test runs under — guarding on the real class, not just
        // catching the fatal, keeps that test able to exercise the rest of
        // this method without a real site.
        if ($app instanceof ConsoleApplication) {
            $app->getLanguage()->load('com_proclaim', JPATH_ADMINISTRATOR);
        }

        $payloadPath = (string) $input->getArgument('payload');
        $tag         = (string) $input->getArgument('tag');
        $userOption  = $input->getOption('user');

        if (!is_readable($payloadPath)) {
            $output->writeln(\sprintf('<error>Cannot read payload "%s".</error>', $payloadPath));

            return Command::INVALID;
        }

        try {
            $payload = json_decode((string) file_get_contents($payloadPath), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $output->writeln(\sprintf('<error>Payload is not valid JSON: %s</error>', $e->getMessage()));

            return Command::INVALID;
        }

        if (!\is_array($payload)) {
            $output->writeln('<error>Payload must decode to a JSON object.</error>');

            return Command::INVALID;
        }

        $userId = $userOption !== null
            ? $this->resolveNamedUser((string) $userOption)
            : $this->resolveDefaultSuperUser();

        if ($userId === null) {
            $output->writeln($userOption !== null
                ? \sprintf('<error>No active user matches "%s".</error>', $userOption)
                : '<error>No active Super User found — pass --user explicitly.</error>');

            return Command::INVALID;
        }

        if ($app instanceof ConsoleApplication) {
            $user           = new User();
            $user->id       = $userId;
            $app->loadIdentity($user);
        }

        $output->writeln(\sprintf('Importing "%s" as user #%d...', $tag, $userId));

        try {
            $summary = $this->importer->import($tag, $payload, \dirname($payloadPath));
        } catch (\Throwable $e) {
            $output->writeln(\sprintf('<error>%s: %s</error>', \get_class($e), $e->getMessage()));

            return Command::FAILURE;
        }

        $output->writeln(\sprintf(
            '<info>Imported</info> %d teacher(s), %d serie(s), %d message(s), %d file(s).',
            $summary['teachers'],
            $summary['series'],
            $summary['messages'],
            $summary['files']
        ));

        return Command::SUCCESS;
    }

    /**
     * @param   string  $identifier  A numeric user id, or a username.
     *
     * @return  int|null  The user's id, or null if no active user matches.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function resolveNamedUser(string $identifier): ?int
    {
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__users'))
            ->where($this->db->quoteName('block') . ' = 0');

        if (ctype_digit($identifier)) {
            $id = (int) $identifier;
            $query->where($this->db->quoteName('id') . ' = :id')->bind(':id', $id, ParameterType::INTEGER);
        } else {
            $query->where($this->db->quoteName('username') . ' = :username')
                ->bind(':username', $identifier, ParameterType::STRING);
        }

        $id = $this->db->setQuery($query)->loadResult();

        return $id !== null ? (int) $id : null;
    }

    /**
     * The first active user in a group that carries `core.admin`.
     *
     * @return  int|null
     *
     * @since  __DEPLOY_VERSION__
     */
    private function resolveDefaultSuperUser(): ?int
    {
        $groupIds = $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('id'))
                ->from($this->db->quoteName('#__usergroups'))
        )->loadColumn();

        // The (bool) cast is load-bearing: Access::checkGroup() can return
        // null, which fatals a strict-typed closure return without it.
        $superGroupIds = array_values(array_filter(
            $groupIds,
            static fn ($groupId): bool => (bool) Access::checkGroup((int) $groupId, 'core.admin')
        ));

        if ($superGroupIds === []) {
            return null;
        }

        $id = $this->db->setQuery(
            $this->db->createQuery()
                ->select('DISTINCT ' . $this->db->quoteName('u.id'))
                ->from($this->db->quoteName('#__users', 'u'))
                ->join(
                    'INNER',
                    $this->db->quoteName('#__user_usergroup_map', 'm'),
                    $this->db->quoteName('m.user_id') . ' = ' . $this->db->quoteName('u.id')
                )
                ->whereIn($this->db->quoteName('m.group_id'), array_map('intval', $superGroupIds))
                ->where($this->db->quoteName('u.block') . ' = 0')
                ->order($this->db->quoteName('u.id') . ' ASC')
                ->setLimit(1)
        )->loadResult();

        return $id !== null ? (int) $id : null;
    }
}
