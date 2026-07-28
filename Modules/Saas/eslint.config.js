import vue from 'eslint-plugin-vue';

export default [
    {
        ignores: ['node_modules/**', 'dist/**'],
    },
    ...vue.configs['flat/recommended'],
    {
        files: ['resources/assets/js/**/*.{js,vue}'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: {
                document: 'readonly',
                window: 'readonly',
                HTMLElement: 'readonly',
                matchMedia: 'readonly',
                fetch: 'readonly',
                FormData: 'readonly',
                URLSearchParams: 'readonly',
                AbortController: 'readonly',
            },
        },
        rules: {
            'vue/multi-word-component-names': 'off',
        },
    },
];
