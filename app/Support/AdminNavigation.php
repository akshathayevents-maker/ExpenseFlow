<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Builds the admin / manager navigation from config/admin_navigation.php for one user:
 * filters by access (mirrors route middleware — hiding a link is never the security boundary),
 * resolves per-role routes, works out the single active item (most specific pattern wins) and
 * exposes the same data to the sidebar, the command palette, quick actions and breadcrumbs.
 */
class AdminNavigation
{
    private ?array $built = null;

    public function __construct(private User $user, private ?string $current = null)
    {
        $this->current ??= Route::currentRouteName();
    }

    public static function for(?User $user = null): self
    {
        return new self($user ?? auth()->user());
    }

    public function allows(string $access): bool
    {
        $u = $this->user;

        return match ($access) {
            'admin' => $u->isAdmin(),
            'manager' => $u->isManager(),
            'hall' => $u->isAdmin() || $u->isManager(),
            'store' => $u->isAdmin() || $u->isManager() || $u->isStorekeeper(),
            'any' => (bool) $u->is_active,
            default => false,
        };
    }

    private function routeFor(array $item): ?string
    {
        $name = isset($item['routes']) ? ($item['routes'][$this->user->role] ?? null) : ($item['route'] ?? null);

        return $name && Route::has($name) ? $name : null;
    }

    /** specificity of the best matching pattern for the current route (0 = no match) */
    private function score(array $item): int
    {
        if (! $this->current) {
            return 0;
        }
        foreach ($item['exclude'] ?? [] as $x) {
            if (Str::is($x, $this->current)) {
                return 0;
            }
        }
        $best = 0;
        foreach ($item['active'] ?? [] as $p) {
            if (Str::is($p, $this->current)) {
                $best = max($best, $p === $this->current ? 1000 : strlen(rtrim($p, '*')));
            }
        }

        return $best;
    }

    private function resolve(array $item, ?string $group = null): ?array
    {
        if (! $this->allows($item['access'] ?? 'admin') || ! ($route = $this->routeFor($item))) {
            return null;
        }

        return [
            'label' => $item['label'], 'icon' => $item['icon'] ?? 'bi-dot', 'route' => $route, 'url' => route($route),
            'section' => $item['section'] ?? null, 'keywords' => $item['keywords'] ?? '', 'group' => $group,
            'score' => $this->score($item), 'active' => false,
        ];
    }

    /** @return array{dashboard: ?array, groups: array<string, array{label:string, icon:string, items:list<array>, active:bool}>} */
    public function build(): array
    {
        if ($this->built) {
            return $this->built;
        }
        $cfg = config('admin_navigation');
        $dashboard = $this->resolve($cfg['dashboard']);
        $groups = [];
        foreach ($cfg['groups'] as $key => $g) {
            $items = array_values(array_filter(array_map(fn ($i) => $this->resolve($i, $g['label']), $g['items'])));
            if ($items) {
                $groups[$key] = ['label' => $g['label'], 'icon' => $g['icon'], 'items' => $items, 'active' => false];
            }
        }
        // exactly one active item: the most specific match across the whole menu
        $best = $dashboard['score'] ?? 0;
        $at = $best > 0 ? ['dashboard'] : null;
        foreach ($groups as $k => $g) {
            foreach ($g['items'] as $n => $it) {
                if ($it['score'] > $best) {
                    [$best, $at] = [$it['score'], [$k, $n]];
                }
            }
        }
        if ($at === ['dashboard']) {
            $dashboard['active'] = true;
        } elseif ($at) {
            $groups[$at[0]]['items'][$at[1]]['active'] = true;
            $groups[$at[0]]['active'] = true;
        }

        return $this->built = ['dashboard' => $dashboard, 'groups' => $groups];
    }

    /** @return list<array> quick "create" actions this user may use */
    public function quickActions(): array
    {
        return array_values(array_filter(array_map(fn ($a) => $this->resolve($a, 'Quick action'), config('admin_navigation.quick_actions', []))));
    }

    /** Flat list for the command palette: pages + quick actions (only what the user can open). */
    public function searchIndex(): array
    {
        $nav = $this->build();
        $pages = [];
        if ($nav['dashboard']) {
            $pages[] = ['t' => 'Dashboard', 'g' => '', 'u' => $nav['dashboard']['url'], 'i' => $nav['dashboard']['icon'], 'k' => 'home'];
        }
        foreach ($nav['groups'] as $g) {
            foreach ($g['items'] as $it) {
                $pages[] = ['t' => $it['label'], 'g' => $g['label'], 'u' => $it['url'], 'i' => $it['icon'], 'k' => $it['keywords']];
            }
        }
        foreach ($this->quickActions() as $a) {
            $pages[] = ['t' => $a['label'], 'g' => 'Create', 'u' => $a['url'], 'i' => $a['icon'], 'k' => 'new add create'];
        }

        return $pages;
    }

    /** Group › Page for the current route, or [] when the page is not in the menu. */
    public function breadcrumbs(): array
    {
        $nav = $this->build();
        foreach ($nav['groups'] as $g) {
            foreach ($g['items'] as $it) {
                if ($it['active']) {
                    return [['label' => $g['label'], 'url' => null], ['label' => $it['label'], 'url' => $it['url']]];
                }
            }
        }

        return [];
    }
}
