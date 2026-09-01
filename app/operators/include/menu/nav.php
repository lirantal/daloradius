<?php
/*
 *********************************************************************************************************
 * daloRADIUS - RADIUS Web Platform
 * Copyright (C) 2007 - Liran Tal <liran@lirantal.com> All Rights Reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *********************************************************************************************************
 *
 * Authors:    Liran Tal <liran@lirantal.com>
 *             Filippo Lauria <filippo.lauria@iit.cnr.it>
 *
 *********************************************************************************************************
 */

// prevent this file to be directly accessed
if (strpos($_SERVER['PHP_SELF'], '/include/menu/nav.php') !== false) {
    header("Location: ../../index.php");
    exit;
}

$nav = array(
                "home"   => array( 'Home', 'index.php', ),
                "mng"    => array( 'Managment', 'mng-main.php', ),
                "rep"    => array( 'Reports', 'rep-main.php', ),
                "acct"   => array( 'Accounting', 'acct-main.php', ),
                "bill"   => array( 'Billing', 'bill-main.php', ),
                "gis"    => array( 'Gis', 'gis-main.php', ),
                "graphs" => array( 'Graphs', 'graphs-main.php', ),
                "config" => array( 'Config', 'config-main.php', ),
                "help"   => array( 'Help', 'help-main.php', ),    
            );

// detect category from the PHP_SELF name
$basename = basename($_SERVER['PHP_SELF']);
$detect_category = substr($basename, 0, strpos($basename, '-'));
if (!in_array($detect_category, array_keys($nav))) {
    $detect_category = "home";
}

?>

<header class="app-header border-bottom">
    <div class="container-fluid px-2 px-lg-3">
        <div class="d-flex flex-wrap align-items-center gap-2 py-2">
            <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas" aria-label="Open menu">
                <i class="bi bi-list"></i>
            </button>

            <a href="index.php" class="app-brand d-flex align-items-center text-dark text-decoration-none">
                <img src="static/images/daloradius_small.png" alt="daloRADIUS">
            </a>
      
            <ul class="nav app-primary-nav flex-nowrap flex-lg-wrap overflow-auto mx-lg-2 me-lg-auto order-3 order-lg-0 w-100 w-lg-auto">
<?php
                foreach ($nav as $category => $arr) {
                    list($label, $href) = $arr;
                    
                    $class = ($detect_category === $category) ? 'link-active' : 'link-dark';
                    $label = htmlspecialchars(strip_tags(trim(t('menu', $label))), ENT_QUOTES, 'UTF-8');
                    
                    printf('<li><a class="nav-link px-2 %s" href="%s">%s</a></li>', $class, urlencode($href), $label);
                }
?>
            </ul>
      
            <form class="app-search col-12 col-lg-auto me-lg-3 order-4 order-lg-0" role="search" action="mng-search.php" method="GET">
                <div class="input-group">
                    <button class="input-group-text btn btn-outline-secondary" id="search-icon">
                        <i class="bi bi-search"></i>
                    </button>
                    <input name="username" type="search" class="form-control" placeholder="<?= t('button','SearchUsers') ?>" aria-label="Search"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" aria-describedby="search-icon"
                        data-bs-title="<?= strip_tags(t('Tooltip','Username') . '. ' . t('Tooltip','UsernameWildcard')) ?>">
                </div>
            </form>
            
            <div class="dropdown text-end dropstart ms-auto order-2 order-lg-0">
                <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle"></i>
                </a>
                
                <ul class="dropdown-menu text-small">
                    <li>
                        <span class="dropdown-item">
                            Welcome, <strong><?= htmlspecialchars($_SESSION['operator_user'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </span>
                    </li>
                    <li>
                        <span class="dropdown-item">
                            Location: <strong><?= htmlspecialchars($_SESSION['location_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php">Log out</a></li>
                </ul>
            </div><!-- -dropdown text-end -->
        </div>
    </div><!-- .container-fluid -->
</header>
