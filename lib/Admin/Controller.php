<?php

namespace AddonStarter\Admin;

use WHMCS\Database\Capsule;

/**
 * Thin controller behind addonstarter_output() / addonstarter_clientarea().
 *
 * Keep business logic here so the module's hook functions stay one-liners.
 * Every piece of user-controlled output goes through e() before printing.
 */
class Controller
{
    private array $vars;

    public function __construct(array $vars)
    {
        $this->vars = $vars;
    }

    public function render(string $action): string
    {
        switch ($action) {
            case 'log':
                return $this->renderLog();
            case 'index':
            default:
                return $this->renderIndex();
        }
    }

    /** Small helper used by the client-area example page. */
    public function greeting(): string
    {
        return 'Hello from the Addon Starter module.';
    }

    /** Escape helper — use it for every echo of dynamic data. */
    public static function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private function lang(string $key, string $fallback = ''): string
    {
        return $this->vars['_lang'][$key] ?? $fallback;
    }

    private function debug(string $message): void
    {
        if (!empty($this->vars['debug'])) {
            logActivity("AddonStarter: {$message}");
        }
    }

    private function record(string $action, ?string $detail = null): void
    {
        Capsule::table('tbladdon_starter_log')->insert([
            'userid'  => isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : null,
            'action'  => substr($action, 0, 64),
            'detail'  => $detail,
        ]);
    }

    private function renderIndex(): string
    {
        $count = (int) Capsule::table('tbladdon_starter_log')->count();
        $this->debug('Admin index viewed');

        return '<h2>' . self::e($this->lang('admin_title', 'Addon Starter')) . '</h2>'
            . '<p>Events logged: <strong>' . $count . '</strong></p>'
            . '<p><a class="btn btn-default" href="addonmodules.php?module=addonstarter&action=log">'
            . self::e($this->lang('admin_viewlog', 'View log')) . '</a></p>'
            . '<p class="text-muted">API key is '
            . (empty($this->vars['apikey']) ? 'not set' : 'set')
            . ' (decrypt it with <code>decrypt()</code> when you need it).</p>';
    }

    private function renderLog(): string
    {
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 25;
        $rows    = Capsule::table('tbladdon_starter_log')
            ->orderBy('id', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $html = '<h2>' . self::e($this->lang('admin_log', 'Event log')) . '</h2>'
              . '<table class="table table-striped"><thead><tr>'
              . '<th>ID</th><th>User</th><th>Action</th><th>Detail</th><th>At</th>'
              . '</tr></thead><tbody>';

        foreach ($rows as $row) {
            $html .= '<tr><td>' . (int) $row->id . '</td>'
                   . '<td>' . ($row->userid ? (int) $row->userid : '—') . '</td>'
                   . '<td>' . self::e($row->action) . '</td>'
                   . '<td>' . self::e($row->detail ?? '') . '</td>'
                   . '<td>' . self::e($row->created_at) . '</td></tr>';
        }

        $html .= '</tbody></table>'
               . '<p><a href="addonmodules.php?module=addonstarter">&laquo; Back</a></p>';

        return $html;
    }
}
