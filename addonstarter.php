<?php
/**
 * WHMCS Addon Module — Starter Boilerplate
 *
 * Rename this file to your module's name (lowercase, no spaces), e.g.
 * `myreporter.php`, and place the whole folder in /modules/addons/.
 * Then visit Setup → Addon Modules in the WHMCS admin to activate.
 *
 * Included:
 *  - addonstarter_config()      — settings shown on the activate screen
 *  - addonstarter_activate()    — runs once on activation (e.g. create tables)
 *  - addonstarter_deactivate()  — runs once on deactivation (never drop data here)
 *  - addonstarter_upgrade()     — version-to-version migrations
 *  - addonstarter_output()      — the admin-area page
 *  - addonstarter_sidebar()     — optional admin sidebar
 *  - addonstarter_clientarea()  — optional client-area page handler
 *
 * Conventions:
 *  - Replace every "addonstarter" with your module's lowercase file name.
 *  - Keep admin output XSS-safe: escape everything with $vars-safe helpers
 *    or htmlspecialchars(). Never trust $_REQUEST.
 *  - Use WHMCS's capsule() for DB access; prefix tables with tbladdon_...
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

use WHMCS\Database\Capsule;

require_once __DIR__ . '/lib/Admin/Controller.php';

/**
 * Module metadata + configurable fields (shown at activation time).
 */
function addonstarter_config()
{
    return [
        // Displayed in the module list.
        'name'        => 'Addon Starter',
        // Used by upgrade() to run version migrations.
        'version'     => '1.0.0',
        'author'      => 'Azan Umer',
        'description' => 'Boilerplate addon module: admin page, client area page, hooks, lang file.',
        'language'    => 'english',
        'fields'      => [
            'apikey' => [
                'FriendlyName' => 'API Key',
                'Type'         => 'password',
                'Size'         => '40',
                'Description'  => 'Stored encrypted; retrieved with decrypt().',
            ],
            'debug' => [
                'FriendlyName' => 'Debug Mode',
                'Type'         => 'yesno',
                'Description'  => 'Log module activity to the WHMCS activity log.',
            ],
        ],
    ];
}

/**
 * Runs once when the admin clicks Activate.
 * Create your tables here — idempotent (IF NOT EXISTS).
 */
function addonstarter_activate()
{
    try {
        if (!Capsule::schema()->hasTable('tbladdon_starter_log')) {
            Capsule::schema()->create('tbladdon_starter_log', function ($table) {
                $table->increments('id');
                $table->unsignedInteger('userid')->nullable();
                $table->string('action', 64);
                $table->text('detail')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index('userid');
            });
        }
        return ['status' => 'success', 'description' => 'Addon Starter activated.'];
    } catch (Exception $e) {
        return ['status' => 'error', 'description' => 'Activation failed: ' . $e->getMessage()];
    }
}

/**
 * Runs once when the admin clicks Deactivate.
 * Keep data — the admin may reactivate. Real cleanup belongs in an
 * explicit "Uninstall" action on your own page, if you offer one.
 */
function addonstarter_deactivate()
{
    return ['status' => 'success', 'description' => 'Addon Starter deactivated (data kept).'];
}

/**
 * Version migrations: called when the stored version < current version.
 * $vars['version'] is the currently installed version.
 */
function addonstarter_upgrade($vars)
{
    $version = $vars['version'];
    try {
        if (version_compare($version, '1.0.0', '<')) {
            // Example future migration:
            // Capsule::schema()->table('tbladdon_starter_log', function ($table) {
            //     $table->string('ip', 45)->nullable();
            // });
        }
        return ['status' => 'success'];
    } catch (Exception $e) {
        return ['status' => 'error', 'description' => $e->getMessage()];
    }
}

/**
 * Admin area page (Setup → Addons → Addon Starter).
 * $vars carries module settings + language strings.
 */
function addonstarter_output($vars)
{
    $controller = new AddonStarter\Admin\Controller($vars);
    echo $controller->render($_GET['action'] ?? 'index');
}

/**
 * Optional admin sidebar blocks.
 */
function addonstarter_sidebar($vars)
{
    return [
        'stats' => [
            'icon'  => 'fa-chart-bar',
            'title' => $vars['_lang']['sidebar_stats'] ?? 'Stats',
            'bodyHtml' => '<p>Total events: <strong>'
                . (int) Capsule::table('tbladdon_starter_log')->count()
                . '</strong></p>',
        ],
    ];
}

/**
 * Optional client-area page, e.g. clientarea.php?m=addonstarter.
 * Keep it simple: read $_GET['action'], render, and always escape output.
 */
function addonstarter_clientarea($vars)
{
    $action = $_GET['action'] ?? 'index';
    $controller = new AddonStarter\Admin\Controller($vars);

    if ($action === 'index') {
        return [
            'pagetitle'    => $vars['_lang']['client_pagetitle'] ?? 'Addon Starter',
            'breadcrumb'   => ['clientarea.php?m=addonstarter' => 'Addon Starter'],
            'templatefile' => 'templates/clientarea/index',
            'requirelogin' => true, // guests get the WHMCS login page
            'vars'         => ['message' => $controller->greeting()],
        ];
    }
    // Unknown action → WHMCS-friendly 404 inside the client area.
    return ['templatefile' => 'templates/clientarea/404', 'requirelogin' => true, 'vars' => []];
}
