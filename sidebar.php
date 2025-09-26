<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!-- main menu-->
<div data-scroll-to-active="true" class="main-menu menu-fixed menu-dark menu-accordion menu-shadow">
    <!-- main menu content-->
    <div class="main-menu-content">
        <ul id="main-menu-navigation" data-menu="menu-navigation" class="navigation navigation-main">
            <li class="nav-item<?php if ($currentPage == 'index.php') echo ' active'; ?>">
                <a href="/index.php">
                    <i class="icon-home3"></i>
                    <span data-i18n="nav.dash.main" class="menu-title">Dashboard</span>
                </a>
            </li>
            <li class="nav-item<?php if ($currentPage == 'constitution.php') echo ' active'; ?>">
                <a href="/constitution.php">
                    <i class="icon-book"></i>
                    <span data-i18n="nav.dash.main" class="menu-title">Constitution</span>
                </a>
            </li>
            <li class="nav-item<?php if ($currentPage == 'profile.php') echo ' active'; ?>"><a href="#"><i class="icon-profile"></i><span data-i18n="nav.page_layouts.main"
                        class="menu-title">User Profiles</span></a>
                <ul class="menu-content">
                    <li><a href="/profile.php?id=Ben" data-i18n="nav.page_layouts.1_column" class="menu-item">Ben</a></li>
                    <li><a href="/profile.php?id=Andy" data-i18n="nav.page_layouts.1_column" class="menu-item">Andy</a></li>
                    <li><a href="/profile.php?id=Gavin" data-i18n="nav.page_layouts.1_column" class="menu-item">Gavin</a></li>
                    <li><a href="/profile.php?id=Tyler" data-i18n="nav.page_layouts.1_column" class="menu-item">Tyler</a></li>
                    <li><a href="/profile.php?id=Justin" data-i18n="nav.page_layouts.1_column" class="menu-item">Justin</a></li>
                    <li><a href="/profile.php?id=Cameron" data-i18n="nav.page_layouts.1_column" class="menu-item">Cameron</a></li>
                    <li><a href="/profile.php?id=Matt" data-i18n="nav.page_layouts.1_column" class="menu-item">Matt</a></li>
                    <li><a href="/profile.php?id=AJ" data-i18n="nav.page_layouts.1_column" class="menu-item">AJ</a></li>
                    <li><a href="/profile.php?id=Cole" data-i18n="nav.page_layouts.1_column" class="menu-item">Cole</a></li>
                    <li><a href="/profile.php?id=Everett" data-i18n="nav.page_layouts.1_column" class="menu-item">Everett</a></li>
                </ul>
            </li>
            <li class="nav-item<?php if ($currentPage == 'seasonRecaps.php') echo ' active'; ?>">
                <a href="/seasonRecaps.php">
                    <i class="icon-map"></i>
                    <span data-i18n="nav.dash.main" class="menu-title">Season Recaps</span>
                </a>
            </li>
            <li class="nav-item<?php if ($currentPage == 'regularSeason.php') echo ' active'; ?>">
                <a href="/regularSeason.php">
                    <i class="icon-flag"></i>
                    <span data-i18n="nav.dash.main" class="menu-title">Regular Season</span>
                </a>
            </li>
            <li class="nav-item<?php if ($currentPage == 'postseason.php') echo ' active'; ?>">
                <a href="/postseason.php">
                    <i class="icon-point-up"></i>
                    <span data-i18n="nav.dash.main" class="menu-title">Postseason</span>
                </a>
            </li>
            <li class="nav-item<?php if ($currentPage == 'awards.php') echo ' active'; ?>">
                <a href="/awards.php">
                    <i class="icon-trophy"></i>
                    <span data-i18n="nav.dash.main" class="menu-title">Awards</span>
                </a>
            </li>
            <li class="nav-item<?php if ($currentPage == 'records.php') echo ' active'; ?>">
                <a href="/records.php">
                    <i class="icon-bar-chart"></i>
                    <span data-i18n="nav.dash.main" class="menu-title">Records</span>
                </a>
            </li>
            <li class="nav-item<?php if ($currentPage == 'draft.php') echo ' active'; ?>">
                <a href="/draft.php">
                    <i class="icon-table"></i>
                    <span data-i18n="nav.dash.main" class="menu-title">Draft</span>
                </a>
            </li>
            <li class="nav-item<?php if ($currentPage == 'rosters.php') echo ' active'; ?>">
                <a href="/rosters.php">
                    <i class="icon-clipboard"></i>
                    <span data-i18n="nav.dash.main" class="menu-title">Rosters</span>
                </a>
            </li>
            <li class="nav-item<?php if ($currentPage == 'players.php') echo ' active'; ?>">
                <a href="/players.php">
                    <i class="icon-user"></i>
                    <span data-i18n="nav.dash.main" class="menu-title">Players</span>
                </a>
            </li>
            <li class="nav-item<?php if ($currentPage == 'schedule.php') echo ' active'; ?>">
                <a href="/schedule.php">
                    <i class="icon-calendar"></i>
                    <span data-i18n="nav.dash.main" class="menu-title">Schedule</span>
                </a>
            </li>
            <li class="nav-item<?php if ($currentPage == 'currentSeason.php') echo ' active'; ?>">
                <a href="/currentSeason.php">
                    <i class="icon-clock"></i>
                    <span data-i18n="nav.dash.main" class="menu-title">Current Season</span>
                </a>
            </li>
            <li class="nav-item<?php if ($currentPage == 'newsletter.php') echo ' active'; ?>">
                <a href="/newsletter.php">
                    <i class="icon-pencil2"></i>
                    <span data-i18n="nav.dash.main" class="menu-title">Newsletter</span>
                </a>
            </li>
        </ul>
    </div>
</div>
