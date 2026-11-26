<?php

/*
 | Admin / manager navigation — single source of truth for the sidebar, the command palette (Ctrl/⌘ K),
 | quick actions and breadcrumbs. Read by App\Support\AdminNavigation.
 |
 | Groups are business areas (what people do), not code modules. Each item:
 |   label, icon (bootstrap-icons), route (or routes per role: ['admin' => .., 'manager' => ..]),
 |   active: route-name patterns that highlight this item (the most specific match wins),
 |   access: who may see it — must mirror the route's own protection (hiding a link is not security):
 |       admin   = role.admin routes            hall  = role.hall (admin + manager)
 |       manager = manager only                  store = admin, manager or storekeeper
 |       any     = every signed-in user
 |   keywords: extra words for search; section: optional sub-heading inside the group.
 | Pages reached from inside other pages (create/edit/show, tabs) are intentionally not listed.
 */
return [
    'dashboard' => [
        'label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'access' => 'hall',
        'routes' => ['admin' => 'admin.dashboard', 'manager' => 'manager.dashboard'],
        'active' => ['admin.dashboard', 'manager.dashboard'],
    ],

    'groups' => [
        'events' => [
            'label' => 'Events & Catering', 'icon' => 'bi-calendar2-event',
            'items' => [
                ['label' => 'Hall Bookings', 'icon' => 'bi-building', 'route' => 'hall.bookings.index', 'access' => 'hall',
                    'active' => ['hall.bookings.*'], 'keywords' => 'booking hall function wedding'],
                ['label' => 'Calendar', 'icon' => 'bi-calendar3', 'route' => 'hall.bookings.calendar', 'access' => 'hall',
                    'active' => ['hall.bookings.calendar'], 'keywords' => 'schedule availability'],
                ['label' => 'Event Requests', 'icon' => 'bi-inbox', 'route' => 'admin.event-requests.index', 'access' => 'hall',
                    'active' => ['admin.event-requests.*'], 'keywords' => 'enquiry customer quote'],
                ['label' => 'Kitchen Schedule', 'icon' => 'bi-fire', 'route' => 'hall.bookings.kitchen', 'access' => 'hall',
                    'active' => ['hall.bookings.kitchen'], 'keywords' => 'production upcoming events cooking'],
                ['label' => 'Hall Overview', 'icon' => 'bi-grid-1x2', 'route' => 'hall.dashboard', 'access' => 'hall',
                    'active' => ['hall.dashboard']],
                ['section' => 'Corporate meals', 'label' => 'Daily Meal Register', 'icon' => 'bi-clipboard-data', 'route' => 'meal-register.entries.index', 'access' => 'hall',
                    'active' => ['meal-register.entries.*'], 'keywords' => 'corporate meals daily count'],
                ['label' => 'Meal Clients', 'icon' => 'bi-buildings', 'route' => 'meal-register.clients.index', 'access' => 'hall',
                    'active' => ['meal-register.clients.*'], 'keywords' => 'corporate client company'],
            ],
        ],

        'menu' => [
            'label' => 'Menu & Kitchen', 'icon' => 'bi-journal-richtext',
            'items' => [
                ['label' => 'Dishes', 'icon' => 'bi-journal-text', 'route' => 'menu.items.index', 'access' => 'admin',
                    'active' => ['menu.items.*'], 'keywords' => 'menu items tamil dish master'],
                ['label' => 'Customer Menu & Prices', 'icon' => 'bi-tags', 'route' => 'admin.event-request-menu.items.index', 'access' => 'hall',
                    'active' => ['admin.event-request-menu.items.*'], 'keywords' => 'price per person event request menu'],
                ['label' => 'Meal Plans', 'icon' => 'bi-egg-fried', 'route' => 'hall.meal-plans.index', 'access' => 'hall',
                    'active' => ['hall.meal-plans.*'], 'keywords' => 'package veg non-veg plan'],
                ['label' => 'Recipes', 'icon' => 'bi-book', 'route' => 'kitchen.recipes.index', 'access' => 'hall',
                    'active' => ['kitchen.recipes.*'], 'keywords' => 'recipe tamil verify ai generator review'],
                ['label' => 'Recipe Standards', 'icon' => 'bi-sliders2', 'route' => 'inventory.standards.index', 'access' => 'admin',
                    'active' => ['inventory.standards.*'], 'keywords' => 'per person quantity standard'],
                ['label' => 'Menu Composer', 'icon' => 'bi-pencil-square', 'route' => 'menu.composer.index', 'access' => 'admin',
                    'active' => ['menu.composer.*', 'menu.drafts.*', 'menu.templates.*'], 'keywords' => 'print menu card templates'],
            ],
        ],

        'store' => [
            'label' => 'Store & Inventory', 'icon' => 'bi-box-seam',
            'items' => [
                ['label' => 'Store Overview', 'icon' => 'bi-grid', 'route' => 'inventory.home', 'access' => 'any',
                    'active' => ['inventory.home']],
                ['label' => 'Material Requests', 'icon' => 'bi-list-check', 'route' => 'inventory.material-requests.index', 'access' => 'any',
                    'active' => ['inventory.material-requests.*'], 'keywords' => 'issue kitchen request handover usage'],
                ['label' => 'Stock', 'icon' => 'bi-boxes', 'route' => 'inventory.stock', 'access' => 'store',
                    'active' => ['inventory.stock', 'admin.inventory.alerts.*'], 'keywords' => 'low stock out alerts movements'],
                ['section' => 'Buying', 'label' => 'Purchases', 'icon' => 'bi-bag-check', 'route' => 'inventory.purchases.index', 'access' => 'any',
                    'active' => ['inventory.purchases.*'], 'keywords' => 'purchase receive goods'],
                ['label' => 'Supplier Bills', 'icon' => 'bi-upc-scan', 'route' => 'admin.inventory.bills.index', 'access' => 'admin',
                    'active' => ['admin.inventory.bills.*'], 'keywords' => 'scan bill ocr invoice upload'],
                ['label' => 'Purchase Plans', 'icon' => 'bi-cart3', 'route' => 'admin.purchase-plans.index', 'access' => 'admin',
                    'active' => ['admin.purchase-plans.*'], 'keywords' => 'shopping list suggestions'],
                ['label' => 'Item Catalogue', 'icon' => 'bi-journal-bookmark', 'route' => 'admin.inventory.items.index', 'access' => 'admin',
                    'active' => ['admin.inventory.items.*'], 'keywords' => 'inventory items tamil alias unit'],
            ],
        ],

        'finance' => [
            'label' => 'Finance', 'icon' => 'bi-wallet2',
            'items' => [
                ['label' => 'Expense Requests', 'icon' => 'bi-receipt', 'access' => 'hall',
                    'routes' => ['admin' => 'admin.expense-requests.index', 'manager' => 'manager.expense-requests.index'],
                    'active' => ['admin.expense-requests.*', 'manager.expense-requests.*'], 'keywords' => 'expense claim approve reimbursement'],
                ['label' => 'Payments', 'icon' => 'bi-cash-coin', 'route' => 'admin.payments.index', 'access' => 'admin',
                    'active' => ['admin.payments.*'], 'keywords' => 'paid pay'],
                ['label' => 'Wallets', 'icon' => 'bi-wallet', 'route' => 'admin.wallets.index', 'access' => 'admin',
                    'active' => ['admin.wallets.*'], 'keywords' => 'balance top up'],
                ['label' => 'Daily Closing', 'icon' => 'bi-journal-check', 'route' => 'admin.daily-closings.index', 'access' => 'admin',
                    'active' => ['admin.daily-closings.*'], 'keywords' => 'cash close day'],
            ],
        ],

        'people' => [
            'label' => 'People & Payroll', 'icon' => 'bi-people',
            'items' => [
                ['label' => 'Employees', 'icon' => 'bi-person-badge', 'route' => 'admin.employees.index', 'access' => 'admin',
                    'active' => ['admin.employees.*'], 'exclude' => ['admin.employees.salaries.*'], 'keywords' => 'staff team'],
                ['label' => 'Attendance', 'icon' => 'bi-calendar-check', 'route' => 'admin.attendance.index', 'access' => 'admin',
                    'active' => ['admin.attendance.*']],
                ['label' => 'Attendance Corrections', 'icon' => 'bi-calendar2-plus', 'access' => 'hall',
                    'routes' => ['admin' => 'admin.attendance-regularizations.index', 'manager' => 'manager.attendance-regularizations.index'],
                    'active' => ['admin.attendance-regularizations.*', 'manager.attendance-regularizations.*'], 'keywords' => 'regularization regularisation'],
                ['label' => 'Leave', 'icon' => 'bi-calendar-x', 'access' => 'hall',
                    'routes' => ['admin' => 'admin.leave.requests.index', 'manager' => 'manager.leave.requests.index'],
                    'active' => ['admin.leave.*', 'manager.leave.*'], 'keywords' => 'leave requests overview balance'],
                ['section' => 'Payroll', 'label' => 'Overtime', 'icon' => 'bi-clock-history', 'access' => 'hall',
                    'routes' => ['admin' => 'admin.overtime.index', 'manager' => 'manager.overtime.index'],
                    'active' => ['admin.overtime.*', 'manager.overtime.*']],
                ['label' => 'Advances', 'icon' => 'bi-cash-stack', 'access' => 'hall',
                    'routes' => ['admin' => 'admin.advances.index', 'manager' => 'manager.advances.index'],
                    'active' => ['admin.advances.*', 'manager.advances.*'], 'keywords' => 'salary advance loan'],
                ['label' => 'Salaries', 'icon' => 'bi-currency-rupee', 'route' => 'admin.salaries.index', 'access' => 'admin',
                    'active' => ['admin.salaries.*', 'admin.employees.salaries.*'], 'keywords' => 'pay revision'],
                ['label' => 'Payroll', 'icon' => 'bi-calculator', 'route' => 'admin.payroll.index', 'access' => 'admin',
                    'active' => ['admin.payroll.*'], 'keywords' => 'payable days eligibility salary run'],
            ],
        ],

        'reports' => [
            'label' => 'Reports', 'icon' => 'bi-bar-chart-line',
            'items' => [
                ['label' => 'Analytics', 'icon' => 'bi-graph-up-arrow', 'route' => 'admin.analytics.index', 'access' => 'admin',
                    'active' => ['admin.analytics.index'], 'keywords' => 'charts trends'],
                ['label' => 'Expense Reports', 'icon' => 'bi-file-earmark-bar-graph', 'route' => 'admin.reports.index', 'access' => 'admin',
                    'active' => ['admin.reports.*'], 'keywords' => 'ledger vendor category monthly daily reimbursement'],
                ['label' => 'Store & Loss Reports', 'icon' => 'bi-shield-check', 'route' => 'inventory.reports.index', 'access' => 'hall',
                    'active' => ['inventory.reports.*'], 'keywords' => 'wastage consumption movements cost per guest loss'],
                ['label' => 'Inventory Analytics', 'icon' => 'bi-pie-chart', 'route' => 'admin.analytics.inventory', 'access' => 'admin',
                    'active' => ['admin.analytics.inventory']],
                ['label' => 'Hall Reports', 'icon' => 'bi-building-check', 'route' => 'hall.reports.index', 'access' => 'hall',
                    'active' => ['hall.reports.*'], 'keywords' => 'booking revenue'],
                ['label' => 'Meal Register Reports', 'icon' => 'bi-clipboard-data', 'route' => 'meal-register.reports.index', 'access' => 'hall',
                    'active' => ['meal-register.reports.*'], 'keywords' => 'corporate meals billing'],
            ],
        ],

        'settings' => [
            'label' => 'Settings', 'icon' => 'bi-gear',
            'items' => [
                ['label' => 'General Settings', 'icon' => 'bi-sliders', 'route' => 'admin.settings.index', 'access' => 'admin',
                    'active' => ['admin.settings.*'], 'keywords' => 'configuration overtime'],
                ['label' => 'Expense Categories', 'icon' => 'bi-tag', 'route' => 'admin.categories.index', 'access' => 'admin',
                    'active' => ['admin.categories.*']],
                ['label' => 'Vendors', 'icon' => 'bi-shop', 'route' => 'admin.vendors.index', 'access' => 'admin',
                    'active' => ['admin.vendors.*'], 'keywords' => 'supplier'],
                ['label' => 'Leave Types', 'icon' => 'bi-calendar-range', 'route' => 'admin.leave-types.index', 'access' => 'admin',
                    'active' => ['admin.leave-types.*']],
                ['label' => 'Leave Policies', 'icon' => 'bi-file-earmark-text', 'route' => 'admin.leave-policy-templates.index', 'access' => 'admin',
                    'active' => ['admin.leave-policy-templates.*'], 'keywords' => 'leave policy template allocation'],
                ['label' => 'Dish Categories', 'icon' => 'bi-collection', 'route' => 'admin.event-request-menu.categories.index', 'access' => 'hall',
                    'active' => ['admin.event-request-menu.categories.*'], 'keywords' => 'menu category'],
                ['label' => 'Inventory Categories', 'icon' => 'bi-diagram-3', 'route' => 'admin.inventory.categories.index', 'access' => 'admin',
                    'active' => ['admin.inventory.categories.*']],
                ['label' => 'Audit Log', 'icon' => 'bi-shield-lock', 'route' => 'admin.audit-logs.index', 'access' => 'admin',
                    'active' => ['admin.audit-logs.*'], 'keywords' => 'history who changed'],
            ],
        ],
    ],

    // Frequent "create" tasks: shown in the + New menu and the command palette instead of as sidebar links.
    'quick_actions' => [
        ['label' => 'New expense', 'icon' => 'bi-receipt', 'access' => 'hall',
            'routes' => ['admin' => 'admin.expense-requests.create', 'manager' => 'manager.expense-requests.create']],
        ['label' => 'Request materials', 'icon' => 'bi-basket', 'route' => 'inventory.material-requests.create', 'access' => 'any'],
        ['label' => 'New purchase', 'icon' => 'bi-bag-plus', 'route' => 'inventory.purchases.create', 'access' => 'any'],
        ['label' => 'New hall booking', 'icon' => 'bi-building-add', 'route' => 'hall.bookings.create', 'access' => 'hall'],
        ['label' => 'Meal register entry', 'icon' => 'bi-clipboard-plus', 'route' => 'meal-register.entries.create', 'access' => 'hall'],
        ['label' => 'New recipe', 'icon' => 'bi-journal-plus', 'route' => 'kitchen.recipes.create', 'access' => 'hall'],
        ['label' => 'Record overtime', 'icon' => 'bi-clock', 'route' => 'admin.overtime.create', 'access' => 'admin'],
        ['label' => 'Add employee', 'icon' => 'bi-person-plus', 'route' => 'admin.employees.create', 'access' => 'admin'],
        ['label' => 'Close the day', 'icon' => 'bi-journal-check', 'route' => 'admin.daily-closings.create', 'access' => 'admin'],
    ],
];
