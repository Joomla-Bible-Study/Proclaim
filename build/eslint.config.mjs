import { defineConfig } from 'eslint/config';
import js from '@eslint/js';
import globals from 'globals';

export default defineConfig([
    {
        ignores: ['**/node_modules/', '**/dist/', '**/reports/'],
    },

    // ESLint recommended base (no-unused-vars, no-undef, no-redeclare, etc.)
    js.configs.recommended,

    {
        files: ['**/*.js', '**/*.mjs', '**/*.es6.js'],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'module',
            globals: {
                ...globals.browser,
                Joomla: 'readonly',
                Proclaim: 'readonly',
                bootstrap: 'readonly',
                Sortable: 'readonly',
                intlTelInput: 'readonly',
                Chart: 'readonly',
            },
        },
        rules: {
            // --- Style ---
            indent: ['error', 4, { SwitchCase: 1 }],
            'max-len': ['error', 150, 2, {
                ignoreUrls: true,
                ignoreComments: false,
                ignoreRegExpLiterals: true,
                ignoreStrings: true,
                ignoreTemplateLiterals: true,
            }],

            // --- Modern JS ---
            'no-var': 'error',
            'prefer-const': 'error',
            eqeqeq: ['error', 'always', { null: 'ignore' }],

            // --- Code quality ---
            radix: 'error',
            'default-case': 'error',
            'no-shadow': 'error',
            'no-lonely-if': 'error',
            'no-prototype-builtins': 'error',
            'consistent-return': 'error',
            'no-param-reassign': ['error', { props: false }],
            'no-use-before-define': ['error', { functions: false, classes: true, variables: true }],
            'no-plusplus': ['error', { allowForLoopAfterthoughts: true }],
            'prefer-destructuring': ['error', { array: true, object: false }],

            // --- Restricted patterns ---
            'no-restricted-globals': [
                'error',
                'event',
                { name: 'isFinite', message: 'Use Number.isFinite instead.' },
                { name: 'isNaN', message: 'Use Number.isNaN instead.' },
            ],
            'no-restricted-syntax': [
                'error',
                {
                    selector: 'ForInStatement',
                    message: 'for..in iterates over the prototype chain. Use Object.{keys,values,entries} instead.',
                },
                {
                    selector: 'WithStatement',
                    message: '`with` is disallowed in strict mode.',
                },
            ],

            // --- Intentionally off for admin scripts ---
            'no-console': 'off',
            'no-alert': 'off',
        },
    },

    {
        files: ['tests/**/*.js', 'tests/**/*.mjs'],
        rules: {
            'no-undef': 'off',
            // Test files access mock.calls[n] frequently; nested destructuring hurts readability
            'prefer-destructuring': 'off',
        },
    },
]);
