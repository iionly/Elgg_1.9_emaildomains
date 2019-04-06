<?php
/**
 * Elgg email domains
 *
 * @package ElggEmailDomains
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Curverider Ltd
 * @copyright Curverider Ltd 2008
 * @link http://elgg.com/
 *
 * Updated for Elgg 1.8 and newer by iionly
 * iionly@gmx.de
 */

elgg_register_event_handler('init', 'system', 'emaildomains_init');

/**
 * Initialise the emaildomains tool
 *
 */
function emaildomains_init() {

	elgg_register_menu_item('page', [
		'name' => 'users:emaildomains',
		'href' => 'admin/users/emaildomains',
		'text' => elgg_echo('admin:users:emaildomains'),
		'context' => 'admin',
		'parent_name' => 'users',
		'section' => 'administer'
	]);

	// Register a hook to validate email for new users
	elgg_register_plugin_hook_handler('registeruser:validate:email', 'all', 'emaildomains_validate_email', 999);
}


/**
 * Validate email address against email domains.
 *
 * @param \Elgg\Hook $hook Hook
 * @return bool
 */
function emaildomains_validate_email(\Elgg\Hook $hook) {
	$site = elgg_get_config('site');
	$email = $hook->getParam('email', false);

	if (($site) && $email && (($site->emaildomains) || ($site->emaildomains_blocked))) {
		// Check whether an address is banned
		if ($site->emaildomains_blocked) {

			$domains_blocked = explode(',', $site->emaildomains_blocked);

			foreach($domains_blocked as $domain) {
				$domain = trim($domain);

				if (stripos($email, $domain) !== false) {
					return false;
				}
			}
		}

		// Check whether an address is permitted
		if ($site->emaildomains) {

			$domains = explode(',', $site->emaildomains);

			foreach($domains as $domain) {
				$domain = trim($domain);

				if (stripos($email, $domain) !== false) {
					return true;
				}
			}
		}

		// We got here so we need to check the logic
		// If no emaildomains have been provided, then we actually want to return true - since we want to allow for
		// allow from all except denied domains
		if (strcmp(trim($site->emaildomains),"") == 0) {
			return true;
		}

		return false;
	}

	return $hook->getValue();
}
