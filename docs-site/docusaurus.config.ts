import {themes as prismThemes} from 'prism-react-renderer';
import type {Config} from '@docusaurus/types';
import type * as Preset from '@docusaurus/preset-classic';

// This runs in Node.js - Don't use client-side code here (browser APIs, JSX...)

const config: Config = {
  title: 'StructCRM',
  tagline:
    'Multi-tenant construction project management and CRM, built with Laravel and Livewire',
  favicon: 'img/favicon.svg',

  // Future flags, see https://docusaurus.io/docs/api/docusaurus-config#future
  // `v4: true` would enable Docusaurus Faster, whose native modules (rspack, swc,
  // lightningcss) do not load on every Node version. Keep the v4 semantics but build
  // with the standard webpack pipeline.
  future: {
    v4: true,
    faster: {
      rspackBundler: false,
      rspackPersistentCache: false,
      swcJsLoader: false,
      swcJsMinimizer: false,
      swcHtmlMinimizer: false,
      lightningCssMinimizer: false,
      mdxCrossCompilerCache: true,
      ssgWorkerThreads: true,
      gitEagerVcs: true,
    },
  },

  url: 'https://mannysoft.github.io',
  // Served from https://mannysoft.github.io/structcrm/
  baseUrl: '/structcrm/',

  // GitHub Pages deployment config.
  organizationName: 'mannysoft',
  projectName: 'structcrm',
  trailingSlash: false,

  onBrokenLinks: 'throw',

  i18n: {
    defaultLocale: 'en',
    locales: ['en'],
  },

  presets: [
    [
      'classic',
      {
        docs: {
          sidebarPath: './sidebars.ts',
          editUrl: 'https://github.com/mannysoft/structcrm/tree/main/docs-site/',
          showLastUpdateTime: true,
        },
        blog: false,
        theme: {
          customCss: './src/css/custom.css',
        },
      } satisfies Preset.Options,
    ],
  ],

  themeConfig: {
    image: 'img/social-card.svg',
    metadata: [
      {
        name: 'keywords',
        content:
          'construction CRM, project management, Laravel, Livewire, multi-tenant, open source, BOQ, progress billing',
      },
      {
        name: 'description',
        content:
          'Documentation for StructCRM: installation, user guide, developer guide, REST API and operations.',
      },
    ],
    colorMode: {
      defaultMode: 'light',
      respectPrefersColorScheme: true,
    },
    navbar: {
      title: 'StructCRM',
      logo: {
        alt: 'StructCRM logo',
        src: 'img/logo.svg',
      },
      items: [
        {
          type: 'docSidebar',
          sidebarId: 'docsSidebar',
          position: 'left',
          label: 'Documentation',
        },
        {
          type: 'doc',
          docId: 'user-guide/overview',
          position: 'left',
          label: 'User Guide',
        },
        {
          type: 'doc',
          docId: 'developer-guide/architecture',
          position: 'left',
          label: 'Developer Guide',
        },
        {
          type: 'doc',
          docId: 'api/authentication',
          position: 'left',
          label: 'API',
        },
        {
          href: 'https://github.com/mannysoft/structcrm',
          label: 'GitHub',
          position: 'right',
        },
      ],
    },
    footer: {
      style: 'dark',
      links: [
        {
          title: 'Docs',
          items: [
            {label: 'Getting Started', to: '/docs/getting-started/installation'},
            {label: 'User Guide', to: '/docs/user-guide/overview'},
            {label: 'Developer Guide', to: '/docs/developer-guide/architecture'},
            {label: 'REST API', to: '/docs/api/authentication'},
          ],
        },
        {
          title: 'Project',
          items: [
            {label: 'GitHub', href: 'https://github.com/mannysoft/structcrm'},
            {
              label: 'Issues',
              href: 'https://github.com/mannysoft/structcrm/issues',
            },
            {
              label: 'Releases',
              href: 'https://github.com/mannysoft/structcrm/releases',
            },
            {label: 'MIT License', href: 'https://opensource.org/licenses/MIT'},
          ],
        },
        {
          title: 'Community',
          items: [
            {label: 'Contributing', to: '/docs/contributing'},
            {label: 'Code of Conduct', to: '/docs/contributing/code-of-conduct'},
            {label: 'Security Policy', to: '/docs/contributing/security'},
          ],
        },
      ],
      copyright: `StructCRM is open source under the MIT license. Built with Docusaurus. Copyright © ${new Date().getFullYear()}.`,
    },
    prism: {
      theme: prismThemes.github,
      darkTheme: prismThemes.dracula,
      additionalLanguages: [
        'bash',
        'php',
        'json',
        'yaml',
        'sql',
        'ini',
        'diff',
        'http',
      ],
    },
    tableOfContents: {
      minHeadingLevel: 2,
      maxHeadingLevel: 3,
    },
  } satisfies Preset.ThemeConfig,
};

export default config;
