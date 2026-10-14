* Use plugin at path `/Users/brianhenry/Sites/juiced-venmo-gateway-pro` for development environment modern practices, i.e. copy its wp-env, Playwright, Typescript, lintitng etc.
* Always run `phpcbf` + `phpcs` + `phpstan` on new and edited code
* Prefer Mockery in unit tests.
* Bugfixes should always have failing unit/wpunit tests
* UI changes should have Playwright tests
* Use `declare(strict_types=1);` in all PHP files
* Don't add PhpDoc return type when it is the same as the PHP function signature return type
* Playwright E2E tests should use REST and WP CLI to arrange the test and only use UI for the minimal part being tested. The assertion should preferably be via REST but UI is reasonable if the page loaded shows the result. Custom REST endpoints for arranging tests can be added in development-plugin/rest. Do not use REST endpoints when a setting does not need to be changed during a test, instead use tests/_wp-env/initialize-internal.sh.
* For UI changes, PRs should contain screenshots of changes
* All actions/filters should use underscores in their name (never hyphens); when a hook name is built from the plugin slug or post type name, pass it through `str_hyphens_to_underscores()`. WordPress core hook names (e.g. `wp_ajax_query-attachments`) are the only exception.
* Prefer instance methods; allow static methods with no side effects
* UI changes should have Playwright tests
* PRs with UI changes (web / email) should have screenshots
* Do not auto-commit unless explicitly requested by the user
* Run `composer dump-autoload` after creating new classes or changing namespaces
* Methods that are hooked to WordPress actions and filters should have PhpDoc `@hooked` annotation with the name and `@see` annotation linking to the call site
* API methods should return simple objects and not arrays unless the array is a simple list of items
* Prefer PSR LoggerInterface for logging, almost never use `error_log`.
