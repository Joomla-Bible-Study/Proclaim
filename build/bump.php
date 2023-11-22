<?php

/**
 * Script used to make a version bump
 * Updates all versions xmls and version.php
 *
 * Usage: php build/bump.php -v <version> -c <codename>
 *
 * Examples:
 * - php build/bump.php -v 3.6.0-dev
 * - php build/bump.php -v 3.6.0-beta1
 * - php build/bump.php -v 3.6.0-beta1-dev
 * - php build/bump.php -v 3.6.0-beta2
 * - php build/bump.php -v 3.6.0-rc1
 * - php build/bump.php -v 3.6.0
 * - php build/bump.php -v 3.6.0 -d "2015-05-12 16:00"
 * - php build/bump.php -v 3.6.0 -c Unicorn
 * - php build/bump.php -v 3.6.0 -c "Custom Codename"
 * - /usr/bin/php /path/to/joomla-cms/build/bump.php -v 3.7.0
 *
 * @package        Proclaim.Bump
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @since          10.0.0
 */

// Functions.
function usage($command): void
{
    echo PHP_EOL;
    echo 'Usage: php ' . $command . ' [options]' . PHP_EOL;
    echo PHP_TAB . '[options]:' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '-v <version>:' . PHP_TAB . PHP_TAB . 'Version (ex: 3.6.0-dev, 3.6.0-beta1, 3.6.0-beta1-dev, 3.6.0-rc1, 3.6.0)' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '-c <codename>:' . PHP_TAB . PHP_TAB . 'Codename [optional] (ex: Unicorn)' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '-d <release date>:' . PHP_TAB . 'Release Date in ISO 8601 format [optional] (ex: "2015-05-12 16:00")' . PHP_EOL;
    echo PHP_EOL;
}

// Constants.
const PHP_TAB = "\t";

// File paths.
$versionFile = '/libraries/src/Version.php';

$coreXmlFiles = [
    '/proclaim.xml',
];

$languageXmlFiles = [
    '/language/en-GB/langmetadata.xml',
    '/language/en-GB/install.xml',
    '/administrator/language/en-GB/langmetadata.xml',
    '/administrator/language/en-GB/install.xml',
    '/installation/language/en-GB/langmetadata.xml',
    '/api/language/en-GB/langmetadata.xml',
    '/api/language/en-GB/install.xml',
];

$languagePackXmlFile = '/administrator/manifests/packages/pkg_en-GB.xml';

$antJobFile = '/build.xml';

$packageJsonFile = '/package.json';

$readMeFiles = [
    '/README.md',
    '/README.txt',
];

/*
 * Change copyright date exclusions.
 * Some systems may try to scan the .git directory, exclude it.
 * Also exclude build resources such as the packaging space or the API documentation build
 * as well as external libraries.
 */
$directoryLoopExcludeDirectories = [
    '/.git',
    '/build/api/',
    '/build/coverage/',
    '/build/tmp/',
    '/libraries/vendor/',
    '/libraries/php-encryption/',
    '/libraries/phpass/',
];

$directoryLoopExcludeFiles = [];

// Check arguments (exit if incorrect cli arguments).
$opts = getopt("v:c:d:");

if (empty($opts['v'])) {
    usage($argv[0]);
    die();
}

// Check version string (exit if not correct).
$versionParts = explode('-', $opts['v']);

if (!preg_match('#^[0-9]+\.[0-9]+\.[0-9]+$#', $versionParts[0])) {
    usage($argv[0]);
    die();
}

if (isset($versionParts[1]) && !preg_match('#(dev|alpha|beta|rc)[0-9]*#', $versionParts[1])) {
    usage($argv[0]);
    die();
}

if (isset($versionParts[2]) && $versionParts[2] !== 'dev') {
    usage($argv[0]);
    die();
}

// Make sure we use the correct language and timezone.
setlocale(LC_ALL, 'en_GB');
date_default_timezone_set('Europe/London');

// Make sure file and folder permissions are set correctly.
umask(022);

// Get version dev status.
$dev_status = 'Stable';

if (!isset($versionParts[1])) {
    $versionParts[1] = '';
} else {
    if (preg_match('#^dev#', $versionParts[1])) {
        $dev_status = 'Development';
    } elseif (preg_match('#^alpha#', $versionParts[1])) {
        $dev_status = 'Alpha';
    } elseif (preg_match('#^beta#', $versionParts[1])) {
        $dev_status = 'Beta';
    } elseif (preg_match('#^rc#', $versionParts[1])) {
        $dev_status = 'Release Candidate';
    }
}

if (!isset($versionParts[2])) {
    $versionParts[2] = '';
} else {
    $dev_status = 'Development';
}

// Set version properties.
$versionSubParts = explode('.', $versionParts[0]);

// The release date
$date = new DateTime($opts['d'] ?? 'now');

// The version data
$version = [
    'main'       => $versionSubParts[0] . '.' . $versionSubParts[1],
    'major'      => $versionSubParts[0],
    'minor'      => $versionSubParts[1],
    'patch'      => $versionSubParts[2],
    'extra'      => (!empty($versionParts[1]) ? $versionParts[1] : '') . (!empty($versionParts[2]) ? (!empty($versionParts[1]) ? '-' : '') . $versionParts[2] : ''),
    'release'    => $versionSubParts[0] . '.' . $versionSubParts[1] . '.' . $versionSubParts[2],
    'dev_devel'  => $versionSubParts[2] . (!empty($versionParts[1]) ? '-' . $versionParts[1] : '') . (!empty($versionParts[2]) ? '-' . $versionParts[2] : ''),
    'dev_status' => $dev_status,
    'build'      => '',
    'reldate'    => $date->format('j-F-Y'),
    'reltime'    => $date->format('H:i'),
    'reltz'      => 'GMT',
    'credate'    => $date->format('Y-m'),
];

// Version Codename.
if (!empty($opts['c'])) {
    $version['codename'] = trim($opts['c']);
}

// Prints version information.
echo PHP_EOL;
echo 'Version data:' . PHP_EOL;
echo '- Main:' . PHP_TAB . PHP_TAB . PHP_TAB . $version['main'] . PHP_EOL;
echo '- Release:' . PHP_TAB . PHP_TAB . $version['release'] . PHP_EOL;
echo '- Full:' . PHP_TAB . PHP_TAB . PHP_TAB . $version['main'] . '.' . $version['dev_devel'] . PHP_EOL;
echo '- Build:' . PHP_TAB . PHP_TAB . $version['build'] . PHP_EOL;
echo '- Dev Level:' . PHP_TAB . PHP_TAB . $version['dev_devel'] . PHP_EOL;
echo '- Dev Status:' . PHP_TAB . PHP_TAB . $version['dev_status'] . PHP_EOL;
echo '- Release date:' . PHP_TAB . PHP_TAB . $version['reldate'] . PHP_EOL;
echo '- Release time:' . PHP_TAB . PHP_TAB . $version['reltime'] . PHP_EOL;
echo '- Release timezone:' . PHP_TAB . $version['reltz'] . PHP_EOL;
echo '- Creation date:' . PHP_TAB . $version['credate'] . PHP_EOL;

if (!empty($version['codename'])) {
    echo '- Codename:' . PHP_TAB . PHP_TAB . $version['codename'] . PHP_EOL;
}

echo PHP_EOL;

$rootPath = \dirname(__DIR__);

// Updates the version in version class.
if (file_exists($rootPath . $versionFile)) {
    $fileContents = file_get_contents($rootPath . $versionFile);
    $fileContents = preg_replace("#MAJOR_VERSION\s*=\s*[^;]*#", "MAJOR_VERSION = " . $version['major'], $fileContents);
    $fileContents = preg_replace("#MINOR_VERSION\s*=\s*[^;]*#", "MINOR_VERSION = " . $version['minor'], $fileContents);
    $fileContents = preg_replace("#PATCH_VERSION\s*=\s*[^;]*#", "PATCH_VERSION = " . $version['patch'], $fileContents);
    $fileContents = preg_replace(
        "#EXTRA_VERSION\s*=\s*'[^\']*'#",
        "EXTRA_VERSION = '" . $version['extra'] . "'",
        $fileContents
    );
    $fileContents = preg_replace("#RELEASE\s*=\s*'[^\']*'#", "RELEASE = '" . $version['main'] . "'", $fileContents);
    $fileContents = preg_replace(
        "#DEV_LEVEL\s*=\s*'[^\']*'#",
        "DEV_LEVEL = '" . $version['dev_devel'] . "'",
        $fileContents
    );
    $fileContents = preg_replace(
        "#DEV_STATUS\s*=\s*'[^\']*'#",
        "DEV_STATUS = '" . $version['dev_status'] . "'",
        $fileContents
    );
    $fileContents = preg_replace("#BUILD\s*=\s*'[^\']*'#", "BUILD = '" . $version['build'] . "'", $fileContents);
    $fileContents = preg_replace("#RELDATE\s*=\s*'[^\']*'#", "RELDATE = '" . $version['reldate'] . "'", $fileContents);
    $fileContents = preg_replace("#RELTIME\s*=\s*'[^\']*'#", "RELTIME = '" . $version['reltime'] . "'", $fileContents);
    $fileContents = preg_replace("#RELTZ\s*=\s*'[^\']*'#", "RELTZ = '" . $version['reltz'] . "'", $fileContents);

    if (!empty($version['codename'])) {
        $fileContents = preg_replace(
            "#CODENAME\s*=\s*'[^\']*'#",
            "CODENAME = '" . $version['codename'] . "'",
            $fileContents
        );
    }

    file_put_contents($rootPath . $versionFile, $fileContents);
}

// Updates the version and creation date in core xml files.
foreach ($coreXmlFiles as $coreXmlFile) {
    if (file_exists($rootPath . $coreXmlFile)) {
        $fileContents = file_get_contents($rootPath . $coreXmlFile);
        $fileContents = preg_replace(
            '#<version>[^<]*</version>#',
            '<version>' . $version['main'] . '.' . $version['dev_devel'] . '</version>',
            $fileContents
        );
        $fileContents = preg_replace(
            '#<creationDate>[^<]*</creationDate>#',
            '<creationDate>' . $version['credate'] . '</creationDate>',
            $fileContents
        );
        file_put_contents($rootPath . $coreXmlFile, $fileContents);
    }
}

// Updates the version and creation date in language xml files.
foreach ($languageXmlFiles as $languageXmlFile) {
    if (file_exists($rootPath . $languageXmlFile)) {
        $fileContents = file_get_contents($rootPath . $languageXmlFile);
        $fileContents = preg_replace(
            '#<version>[^<]*</version>#',
            '<version>' . $version['release'] . '</version>',
            $fileContents
        );
        $fileContents = preg_replace(
            '#<creationDate>[^<]*</creationDate>#',
            '<creationDate>' . $version['credate'] . '</creationDate>',
            $fileContents
        );
        file_put_contents($rootPath . $languageXmlFile, $fileContents);
    }
}

// Updates the version and creation date in language package xml file.
if (file_exists($rootPath . $languagePackXmlFile)) {
    $fileContents = file_get_contents($rootPath . $languagePackXmlFile);
    $fileContents = preg_replace(
        '#<version>[^<]*</version>#',
        '<version>' . $version['release'] . '.1</version>',
        $fileContents
    );
    $fileContents = preg_replace(
        '#<creationDate>[^<]*</creationDate>#',
        '<creationDate>' . $version['credate'] . '</creationDate>',
        $fileContents
    );
    file_put_contents($rootPath . $languagePackXmlFile, $fileContents);
}

// Updates the version for the `phpdoc` task in the Ant job file.
if (file_exists($rootPath . $antJobFile)) {
    $fileContents = file_get_contents($rootPath . $antJobFile);
    $fileContents = preg_replace(
        '#<arg value="CWM Proclaim [^ ]* API" />#',
        '<arg value="CWM Proclaim ' . $version['main'] . ' API" />',
        $fileContents
    );
    file_put_contents($rootPath . $antJobFile, $fileContents);
}

// Updates the version in the package.json file.
if (file_exists($rootPath . $packageJsonFile)) {
    $package          = json_decode(file_get_contents($rootPath . $packageJsonFile), false, 512, JSON_THROW_ON_ERROR);
    $package->version = $version['release'];

    // @todo use a native formatter whenever https://github.com/php/php-src/issues/8864 is resolved
    file_put_contents(
        $rootPath . $packageJsonFile,
        str_replace(
            '    ',
            '  ',
            json_encode($package, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        )
    );
}

// Updates the version in readme files.
foreach ($readMeFiles as $readMeFile) {
    if (file_exists($rootPath . $readMeFile)) {
        $fileContents = file_get_contents($rootPath . $readMeFile);
        $fileContents = preg_replace(
            '#Proclaim [0-9]+\.[0-9]+ (|\[)version#',
            'Proclaim ' . $version['main'] . ' $1version',
            $fileContents
        );
        $fileContents = preg_replace(
            '#Proclaim_[0-9]+\.[0-9]+_version#',
            'Proclaim_' . $version['main'] . '_version',
            $fileContents
        );
        file_put_contents($rootPath . $readMeFile, $fileContents);
    }
}

$changedFilesSinceVersion = 0;
$year                     = date('Y');
$directory                = new RecursiveDirectoryIterator($rootPath);
$iterator                 = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);

foreach ($iterator as $file) {
    if ($file->isFile()) {
        $filePath     = $file->getPathname();
        $relativePath = str_replace($rootPath, '', $filePath);

        // Exclude certain extensions.
        if (preg_match('#\.(png|jpeg|jpg|gif|bmp|ico|webp|svg|woff|woff2|ttf|eot)$#', $filePath)) {
            continue;
        }

        // Exclude certain files.
        if (\in_array($relativePath, $directoryLoopExcludeFiles, true)) {
            continue;
        }

        // Exclude certain directories.
        $continue = true;

        foreach ($directoryLoopExcludeDirectories as $excludeDirectory) {
            if (preg_match('#^' . preg_quote($excludeDirectory) . '#', $relativePath)) {
                $continue = false;
                break;
            }
        }

        if ($continue) {
            $changeSinceVersion = false;

            // Load the file.
            $fileContents = file_get_contents($filePath);

            // Check if need to change the since version.
            if ($relativePath !== '/build/bump.php' && str_contains($fileContents, "__DEPLOY_VERSION__")) {
                $changeSinceVersion = true;
                $fileContents       = str_replace("__DEPLOY_VERSION__", $version['release'], $fileContents);
                $changedFilesSinceVersion++;
            }

            // Save the file.
            if ($changeSinceVersion) {
                file_put_contents($filePath, $fileContents);
            }
        }
    }
}

if ($changedFilesSinceVersion > 0) {
    echo '- Since Version changed in ' . $changedFilesSinceVersion . ' files.' . PHP_EOL;
    echo PHP_EOL;
}

echo 'Version bump complete!' . PHP_EOL;
