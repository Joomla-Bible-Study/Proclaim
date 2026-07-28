/**
 * build.properties access for the E2E suite.
 *
 * One parser shared by playwright.config.js and global-setup.js, with the
 * same two-layer resolution the PHP tooling uses:
 *   1. build.dist.properties — defaults (committed)
 *   2. build.properties      — local overrides for credentials (gitignored)
 *
 * Nothing here assumes what an install is named. Sites are discovered from
 * `builder.installs` and their `builder.<id>.role`, mirroring
 * PropertiesReader::installsFor() on the PHP side — someone whose test
 * install is called `trunk-test` gets exactly the same behaviour as one
 * whose install is called `j6-test`.
 */

const fs = require('fs');
const path = require('path');

function parseProperties(filePath) {
    const props = {};
    if (!fs.existsSync(filePath)) {
        return props;
    }
    const lines = fs.readFileSync(filePath, 'utf8').split('\n');
    for (const line of lines) {
        const trimmed = line.trim();
        if (!trimmed || trimmed.startsWith('#')) {
            continue;
        }
        const eq = trimmed.indexOf('=');
        if (eq === -1) {
            continue;
        }
        props[trimmed.slice(0, eq).trim()] = trimmed.slice(eq + 1).trim();
    }
    return props;
}

/**
 * @param {string} root Repo root.
 * @returns {object} Merged properties, build.properties winning.
 */
function loadProps(root) {
    const dist = parseProperties(path.join(root, 'build.dist.properties'));
    const local = parseProperties(path.join(root, 'build.properties'));
    return { ...dist, ...local };
}

/**
 * The install ids listed in builder.installs.
 *
 * @param {object} props
 * @returns {string[]}
 */
function installIds(props) {
    return (props['builder.installs'] || '')
        .split(',')
        .map((s) => s.trim())
        .filter(Boolean);
}

/**
 * Connection details for one named install.
 *
 * @param {object} props
 * @param {string} id
 * @returns {{id: string, role: string, url: string, username: string, password: string}}
 */
function installById(props, id) {
    return {
        id,
        role: props[`builder.${id}.role`] || '',
        url: props[`builder.${id}.url`] || '',
        username: props[`builder.${id}.username`] || props['builder.joomla_username'] || '',
        password: props[`builder.${id}.password`] || props['builder.joomla_password'] || '',
    };
}

/**
 * The first install marked with the given role, or null. The role=test
 * install is the one `composer test:install` provisions from the built
 * package; the API acceptance suite runs there and nowhere else.
 *
 * @param {object} props
 * @param {string} role
 * @returns {object|null}
 */
function installForRole(props, role) {
    const id = installIds(props).find((name) => (props[`builder.${name}.role`] || '') === role);

    return id ? installById(props, id) : null;
}

module.exports = { parseProperties, loadProps, installIds, installById, installForRole };
