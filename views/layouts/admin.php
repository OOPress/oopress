<!DOCTYPE html>
<html lang="<?= get_locale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title ?? 'Admin') ?> - OOPress Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <a href="/admin" class="admin-logo">
                    <span class="logo-oo">OO</span><span class="logo-press">Press</span>
                </a>
            </div>
            
            <nav class="admin-sidebar-nav">
                <ul>
                    <li><a href="/admin" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin') === 0 && !strpos($_SERVER['REQUEST_URI'], '/admin/posts') && !strpos($_SERVER['REQUEST_URI'], '/admin/pages') && !strpos($_SERVER['REQUEST_URI'], '/admin/users') && !strpos($_SERVER['REQUEST_URI'], '/admin/media') && !strpos($_SERVER['REQUEST_URI'], '/admin/categories') && !strpos($_SERVER['REQUEST_URI'], '/admin/tags') && !strpos($_SERVER['REQUEST_URI'], '/admin/comments') && !strpos($_SERVER['REQUEST_URI'], '/admin/settings') && !strpos($_SERVER['REQUEST_URI'], '/admin/plugins') && !strpos($_SERVER['REQUEST_URI'], '/admin/themes') && !strpos($_SERVER['REQUEST_URI'], '/admin/cache') ? 'active' : '' ?>">
                        <span class="nav-icon">📊</span>
                        <span class="nav-text"><?= __('Dashboard') ?></span>
                    </a></li>
                    
                    <li class="nav-divider"><?= __('Content') ?></li>
                    
                    <li><a href="/admin/posts" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/posts') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">📝</span>
                        <span class="nav-text"><?= __('Posts') ?></span>
                    </a></li>
                    
                    <li><a href="/admin/pages" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/pages') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">📄</span>
                        <span class="nav-text"><?= __('Pages') ?></span>
                    </a></li>
                    
                    <li><a href="/admin/media" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/media') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">🖼️</span>
                        <span class="nav-text"><?= __('Media') ?></span>
                    </a></li>
                    
                    <li class="nav-divider"><?= __('Organization') ?></li>
                    
                    <li><a href="/admin/categories" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/categories') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">📁</span>
                        <span class="nav-text"><?= __('Categories') ?></span>
                    </a></li>
                    
                    <li><a href="/admin/tags" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/tags') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">🏷️</span>
                        <span class="nav-text"><?= __('Tags') ?></span>
                    </a></li>
                    
                    <li class="nav-divider"><?= __('Users') ?></li>
                    
                    <li><a href="/admin/users" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">👥</span>
                        <span class="nav-text"><?= __('Users') ?></span>
                    </a></li>
                    
                    <li><a href="/admin/comments" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/comments') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">💬</span>
                        <span class="nav-text"><?= __('Comments') ?></span>
                    </a></li>
                    
                    <li class="nav-divider"><?= __('Appearance') ?></li>
                    
                    <li><a href="/admin/themes" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/themes') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">🎨</span>
                        <span class="nav-text"><?= __('Themes') ?></span>
                    </a></li>
                    
                    <li class="nav-divider"><?= __('System') ?></li>
                    
                    <li><a href="/admin/plugins" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/plugins') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">🔌</span>
                        <span class="nav-text"><?= __('Plugins') ?></span>
                    </a></li>
                    
                    <li><a href="/admin/cache" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/cache') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">⚡</span>
                        <span class="nav-text"><?= __('Cache') ?></span>
                    </a></li>
                    
                    <li><a href="/admin/settings" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">⚙️</span>
                        <span class="nav-text"><?= __('Settings') ?></span>
                    </a></li>
                </ul>
            </nav>
            
            <div class="admin-sidebar-footer">
                <a href="/">
                    <span class="nav-icon">🌐</span>
                    <span class="nav-text"><?= __('View Site') ?></span>
                </a>
                <a href="/logout">
                    <span class="nav-icon">🚪</span>
                    <span class="nav-text"><?= __('Logout') ?></span>
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-top-bar">
                <button class="sidebar-toggle" id="sidebar-toggle">☰</button>
                <div class="admin-user">
                    <span class="user-name"><?= $_SESSION['user_display_name'] ?? $_SESSION['user_username'] ?? 'Admin' ?></span>
                </div>
            </div>
            
            <div class="admin-content">
                <?= $this->section('content') ?>
            </div>
        </div>
    </div>
    
    <script>
        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const adminWrapper = document.querySelector('.admin-wrapper');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                adminWrapper.classList.toggle('sidebar-collapsed');
            });
        }
    </script>
</body>
</html>