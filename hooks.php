<?php
/**
 * hooks.php — example WHMCS hooks for the Addon Starter module.
 *
 * Copy into /includes/hooks/ as e.g. addonstarter_hooks.php.
 * Remove the ones you don't need. Hook priorities: lower runs first.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

/**
 * Log every successful client login into the module's table.
 */
add_hook('ClientLogin', 10, function ($vars) {
    try {
        Capsule::table('tbladdon_starter_log')->insert([
            'userid' => (int) ($vars['userid'] ?? 0),
            'action' => 'client_login',
        ]);
    } catch (Exception $e) {
        logActivity('AddonStarter hook error: ' . $e->getMessage());
    }
});

/**
 * Add a link to the client-area secondary navbar.
 */
add_hook('ClientAreaSecondaryNavbar', 10, function ($menu) {
    if ($nav = $menu->getChild('Home')) {
        $nav->addChild('addon-starter', [
            'label' => 'Addon Starter',
            'uri'   => 'clientarea.php?m=addonstarter',
            'order' => 99,
        ]);
    }
});
