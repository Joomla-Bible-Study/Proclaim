import baseConfig from './libraries/vendor/cwm/build-tools/templates/eslint.config.base.mjs';

export default [
    ...baseConfig,
    {
        // Skip minified files (lint sources, not build output) and vendored
        // submodules (lib_cwmscripture is maintained in its own repo).
        ignores: [
            '**/*.min.js',
            '**/lib_cwmscripture/**',
            'plugins/content/scripturelinks/libraries/**',
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