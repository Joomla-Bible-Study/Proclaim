import baseConfig from './libraries/vendor/cwm/build-tools/templates/eslint.config.base.mjs';

export default [
    ...baseConfig,
    {
        files: ['**/*.js', '**/*.mjs', '**/*.es6.js'],
        languageOptions: {
            globals: {
                Proclaim: 'readonly',
                Sortable: 'readonly',
                intlTelInput: 'readonly',
                Chart: 'readonly',
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