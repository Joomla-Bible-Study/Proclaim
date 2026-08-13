import baseConfig from './libraries/vendor/cwm/build-tools/templates/eslint.config.base.mjs';

export default [
    ...baseConfig,
    {
        // Skip minified files (lint sources, not build output) and vendored
        // submodules — lib_cwmscripture and CWMScriptureLinks are maintained in
        // their own repositories and linted by their own CI.
        //
        // The whole scripturelinks submodule, not just its libraries/: it
        // carries an eslint.config.mjs of its own, and ESLint 10 looks up the
        // nearest config per file rather than using only the root one. That
        // config extends a base out of the submodule's own vendor/, which
        // Composer would have to install separately — so CI, which checks the
        // submodule out but never installs it, died with ERR_MODULE_NOT_FOUND.
        // Paired with --no-config-lookup in the lint:js script, which is what
        // makes these ignores authoritative rather than advisory.
        ignores: [
            '**/*.min.js',
            '**/lib_cwmscripture/**',
            'plugins/content/scripturelinks/**',
        ],
    },
    {
        files: ['**/*.js', '**/*.mjs', '**/*.es6.js'],
        languageOptions: {
            globals: {
                Proclaim: 'readonly',
                Sortable: 'readonly',
                intlTelInput: 'readonly',
                Chart: 'readonly',
                // Joomla 5+ editor API and legacy TinyMCE global
                JoomlaEditor: 'readonly',
                tinyMCE: 'readonly',
                // AddToAny social-share configuration object
                a2a_config: 'writable',
            },
        },
    },
    {
        files: ['tests/**/*.js', 'tests/**/*.mjs'],
        rules: {
            'no-undef': 'off',
            'prefer-destructuring': 'off',
        },
    },
];