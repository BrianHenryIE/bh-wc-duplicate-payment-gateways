/**
 * External dependencies
 */
import { defineConfig, devices } from '@playwright/test';
import * as path from 'path';

/**
 * Read environment variables from file.
 * https://github.com/motdotla/dotenv
 */
require( 'dotenv' ).config();

const WP_BASE_URL =
  process.env.BASEURL ||
  process.env.WP_BASE_URL ||
  'http://localhost:8890';

// Set WP_BASE_URL so @wordpress/e2e-test-utils-playwright uses the same base URL.
process.env.WP_BASE_URL = WP_BASE_URL;

const STORAGE_STATE_PATH =
  process.env.STORAGE_STATE_PATH ||
  path.join( process.cwd(), 'artifacts/storage-states/admin.json' );

/**
 * See https://playwright.dev/docs/test-configuration.
 */
export default defineConfig( {
  testDir: './tests/e2e-pw/specs',
  testIgnore: '**/helpers-tests/**',
  globalSetup: require.resolve( './tests/e2e-pw/global-setup.ts' ),
  /* The duplicates are all saved in one wp_option, so tests creating/deleting them in parallel overwrite each other. */
  fullyParallel: false,
  /* Fail the build on CI if you accidentally left test.only in the source code. */
  forbidOnly: !! process.env.CI,
  /* Retry on CI only */
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  /* Reporter to use. See https://playwright.dev/docs/test-reporters */
  reporter: [ [ 'list' ], [ 'html', { open: 'never' } ] ],
  // https://playwright.dev/docs/test-timeouts
  // timeout is 30 seconds by default
  // expect.timeout is 5 seconds by default
  timeout: 30_000,
  /* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
  use: {
    /* Base URL to use in actions like `await page.goto('/')`. */
    baseURL: WP_BASE_URL,

    storageState: STORAGE_STATE_PATH,

    /* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
    trace: 'on-first-retry',
  },

  /* Configure projects for major browsers */
  projects: [
    {
      name: 'chromium',
      use: { ...devices[ 'Desktop Chrome' ] },
    },


    /* Test against mobile viewports. */
    // {
    //   name: 'Mobile Chrome',
    //   use: { ...devices['Pixel 5'] },
    // },
    // {
    //   name: 'Mobile Safari',
    //   use: { ...devices['iPhone 12'] },
    // },

    /* Test against branded browsers. */
    // {
    //   name: 'Microsoft Edge',
    //   use: { ...devices['Desktop Edge'], channel: 'msedge' },
    // },
    // {
    //   name: 'Google Chrome',
    //   use: { ...devices['Desktop Chrome'], channel: 'chrome' },
    // },
  ],

  /* Run your local dev server before starting the tests */
  // webServer: {
  //   command: 'npm run start',
  //   url: 'http://127.0.0.1:3000',
  //   reuseExistingServer: !process.env.CI,
  // },
} );
