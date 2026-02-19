import { FlatCompat } from '@eslint/eslintrc';
import js from '@eslint/js';
import globals from 'globals';
import path from 'path';
import { fileURLToPath } from 'url';

// eslint-disable-next-line no-underscore-dangle
const __filename = fileURLToPath(import.meta.url);
// eslint-disable-next-line no-underscore-dangle
const __dirname = path.dirname(__filename);

const compat = new FlatCompat({
    baseDirectory: __dirname,
    recommendedConfig: js.configs.recommended,
    allConfig: js.configs.all,
});

export default [
    {
        ignores: ['**/node_modules/', '**/dist/', '**/reports/'],
    },
    ...compat.extends('airbnb-base'),
    {
        files: ['**/*.js', '**/*.mjs', '**/*.es6.js'],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'module',
            globals: {
                ...globals.browser,
                ...globals.node,
                Joomla: 'readonly',
                Proclaim: 'readonly',
                bootstrap: 'readonly',
                Sortable: 'readonly',
                intlTelInput: 'readonly',
                Chart: 'readonly',
            },
        },
        rules: {
            // --- Style overrides ---
            indent: ['error', 4, { SwitchCase: 1 }],
            'max-len': ['error', 150, 2, {
                ignoreUrls: true,
                ignoreComments: false,
                ignoreRegExpLiterals: true,
                ignoreStrings: true,
                ignoreTemplateLiterals: true,
            }],

            // --- Import rules ---
            'import/extensions': 0,
            'import/prefer-default-export': 0,
            'import/no-extraneous-dependencies': ['error', { devDependencies: true }],

            // --- Rules relaxed for admin script patterns ---
            strict: 'off',
            'no-alert': 0,
            'no-param-reassign': ['error', { props: false }],

            // Allow ++ in for-loop afterthoughts (common and clear)
            'no-plusplus': ['error', { allowForLoopAfterthoughts: true }],

            // Underscore prefix is a common private-member convention
            'no-underscore-dangle': 'off',

            // Allow for...of (standard modern JS); keep for...in restricted
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
                {
                    selector: 'LabeledStatement',
                    message: 'Labeled statements are rarely needed and confuse readers.',
                },
            ],

            // Batch AJAX operations legitimately need await inside loops
            'no-await-in-loop': 'off',

            // Utility methods on classes that don't use `this` are fine
            'class-methods-use-this': 'off',

            // Continue is a valid early-exit pattern in loops
            'no-continue': 'off',

            // Nested ternaries are sometimes the clearest option
            'no-nested-ternary': 'off',

            // Allow void for intentional fire-and-forget
            'no-void': 'off',

            // Returning inside Promise executor is occasionally needed
            'no-promise-executor-return': 'off',

            // Admin scripts legitimately use console for debug output
            'no-console': 'off',

            // Allow confirm() and history (used by admin UI)
            'no-restricted-globals': [
                'error',
                'event',
                { name: 'isFinite', message: 'Use Number.isFinite instead.' },
                { name: 'isNaN', message: 'Use Number.isNaN instead.' },
            ],

            // Unnamed callback functions are common and acceptable
            'func-names': 'off',

            // new SomeClass(el) pattern is used for Joomla plugins (side-effect only)
            'no-new': 'off',

            // Functions used before definition are fine when hoisted; variables are not
            'no-use-before-define': ['error', { functions: false, classes: true, variables: true }],
        },
    },
    {
        files: ['tests/**/*.js', 'tests/**/*.mjs'],
        rules: {
            'no-undef': 'off',
            'import/no-extraneous-dependencies': 'off',
        },
    },
];