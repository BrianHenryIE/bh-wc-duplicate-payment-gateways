/**
 * Run WP-CLI in the wp-env development site (the one the E2E tests run against).
 *
 * Set `WP_ENV_CONFIG` to the config file the environment was started with (CI uses `.wp-env.ci.json`);
 * wp-env identifies the environment by its config, so `wp-env run` against `.wp-env.json` would fail with
 * "Environment not initialized".
 */
import { execFileSync } from 'child_process';

export function wpCli( args: string[] ): string {
	const config = process.env.WP_ENV_CONFIG ? [ '--config', process.env.WP_ENV_CONFIG ] : [];
	return execFileSync( 'npx', [ 'wp-env', 'run', ...config, 'cli', 'wp', ...args ], {
		encoding: 'utf8',
		stdio: [ 'ignore', 'pipe', 'pipe' ],
	} );
}

/**
 * Run every due (or past-due) event for a WP-Cron hook.
 *
 * WP-Cron may already have spawned and run the event on an earlier page load, in which case there is nothing
 * to run and WP-CLI exits non-zero; that is not a failure for callers who then assert the event's effect.
 */
export function runDueCronHook( hook: string ): string {
	try {
		return wpCli( [ 'cron', 'event', 'run', hook ] );
	} catch ( error ) {
		const output = String( ( error as { stderr?: string } ).stderr ?? '' ) + String( ( error as { stdout?: string } ).stdout ?? '' );
		if ( ! /Invalid cron event|no.*events/i.test( output ) ) {
			throw error;
		}
		return output;
	}
}
