<?php
/**
 * Router sederhana: daftar rute → handler. Pola {id} = angka, {slug} = satu segmen path.
 */
declare(strict_types=1);

$GLOBALS['_routes'] = [];

function route(string $method, string $pattern, callable|string $handler): void
{
    $regex = preg_quote($pattern, '#');
    $regex = str_replace(['\{id\}', '\{slug\}'], ['(?P<id>\d+)', '(?P<slug>[^/]+)'], $regex);

    $GLOBALS['_routes'][] = [
        'method'  => strtoupper($method),
        'regex'   => '#^' . $regex . '$#',
        'handler' => $handler,
    ];
}

function dispatch(string $method, string $uriPath): void
{
    $path = rawurldecode($uriPath);

    // Hapus base path (mis. /platform-web-umkm/public) bila aplikasi berjalan di subfolder.
    $basePath = rtrim((string) parse_url(BASE_URL, PHP_URL_PATH), '/');
    if ($basePath !== '' && str_starts_with($path, $basePath)) {
        $path = substr($path, strlen($basePath));
    }
    if ($path === '' || $path === false) {
        $path = '/';
    }
    if ($path !== '/') {
        $path = rtrim($path, '/');
    }

    $method = strtoupper($method);
    foreach ($GLOBALS['_routes'] as $route) {
        if ($route['method'] !== $method || !preg_match($route['regex'], $path, $matches)) {
            continue;
        }
        $params = [];
        if (isset($matches['id'])) {
            $params[] = (int) $matches['id'];
        }
        if (isset($matches['slug'])) {
            $params[] = $matches['slug'];
        }
        ($route['handler'])(...$params);
        return;
    }

    // Rute admin tak dikenal: wajib login dulu (tidak membocorkan struktur), lalu 404 versi admin.
    if (preg_match('#^/admin(/|$)#', $path)) {
        require_admin();
        not_found(true);
    }
    not_found();
}

/**
 * Halaman 404 (publik atau versi admin) dengan status HTTP 404.
 */
function not_found(bool $admin = false): never
{
    if ($admin && current_admin() !== null) {
        render('admin/not_found', ['title' => 'Tidak ditemukan', 'active' => ''], 'admin', 404);
    } else {
        render('public/404', ['title' => 'Halaman tidak ditemukan — Portofolio UMKM'], 'public', 404);
    }
    exit;
}
