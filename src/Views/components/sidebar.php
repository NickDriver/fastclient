<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$user = auth();
?>
<aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-64 transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:flex lg:flex-col bg-white border-r border-gray-200">
    <!-- Logo -->
    <div class="flex h-16 shrink-0 items-center px-6 border-b border-gray-200">
        <a href="/dashboard" class="text-xl font-bold text-gray-900">
            FastClient
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 space-y-1 px-3 py-4">
        <a href="/dashboard" class="sidebar-link <?= $currentPath === '/' || $currentPath === '/dashboard' ? 'sidebar-link-active' : '' ?>">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            Dashboard
        </a>

        <a href="/customers" class="sidebar-link <?= str_starts_with($currentPath, '/customers') ? 'sidebar-link-active' : '' ?>">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
            Customers
        </a>
    </nav>

    <!-- User section -->
    <div class="border-t border-gray-200 p-4">
        <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-full bg-gray-200 flex items-center justify-center">
                <span class="text-sm font-medium text-gray-600">
                    <?= e(strtoupper(substr($user?->name ?? 'U', 0, 1))) ?>
                </span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">
                    <?= e($user?->name ?? 'User') ?>
                </p>
                <p class="text-xs text-gray-500 truncate">
                    <?= e($user?->email ?? '') ?>
                </p>
            </div>
            <form action="/logout" method="POST" class="shrink-0">
                <?= csrf_field() ?>
                <button type="submit" class="text-gray-400 hover:text-gray-600" title="Logout">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
