<?php
$authName = (string) (session()->get('auth_name') ?? 'User');
$authProfileImage = trim((string) (session()->get('auth_profile_image') ?? ''));
$profileInitial = strtoupper(function_exists('mb_substr') ? mb_substr($authName, 0, 1) : substr($authName, 0, 1));
if ($profileInitial === '') {
    $profileInitial = 'U';
}

$profileImageUrl = '';
if ($authProfileImage !== '') {
    if (preg_match('/^https?:\/\//i', $authProfileImage)) {
        $profileImageUrl = $authProfileImage;
    } else {
        $profileImageUrl = base_url(ltrim($authProfileImage, '/'));
    }
}
?>

<nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo" href="<?= base_url() ?>">Harini Jewellers</a>
        <a class="navbar-brand brand-logo-mini" href="<?= base_url() ?>"><img src="<?= base_url() ?>assets/images/logo-mini.svg"
            alt="logo" /></a>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-center">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
        <span class="mdi mdi-menu"></span>
        </button>
        <ul class="navbar-nav me-auto">
            <li class="nav-item nav-search d-none d-md-flex" id="navbarSearch">
                <a class="nav-link d-flex justify-content-center align-items-center" id="navbarSearchButton" href="#">
                <i class="mdi mdi-magnify mx-0"></i>
                </a>
                <input type="text" class="form-control" placeholder="Search..." id="navbarSearchInput">
            </li>
        </ul>
        <ul class="navbar-nav navbar-nav-right">
            <!-- <li class="nav-item dropdown me-1">
                <a class="nav-link count-indicator dropdown-toggle d-flex justify-content-center align-items-center"
                id="messageDropdown" href="#" data-bs-toggle="dropdown">
                <i class="mdi mdi-email mx-0"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="messageDropdown">
                <p class="mb-0 fw-normal float-start dropdown-header">Messages</p>
                <a class="dropdown-item preview-item">
                    <div class="preview-thumbnail">
                        <img src="<?= base_url() ?>assets/images/placeholder-36x36.svg" alt="image" class="profile-pic">
                    </div>
                    <div class="preview-item-content flex-grow">
                        <h6 class="preview-subject ellipsis fw-normal">David Grey
                        </h6>
                        <p class="fw-light small-text text-muted mb-0">
                            The meeting is cancelled
                        </p>
                    </div>
                </a>
                <a class="dropdown-item preview-item">
                    <div class="preview-thumbnail">
                        <img src="<?= base_url() ?>assets/images/placeholder-36x36.svg" alt="image" class="profile-pic">
                    </div>
                    <div class="preview-item-content flex-grow">
                        <h6 class="preview-subject ellipsis fw-normal">Tim Cook
                        </h6>
                        <p class="fw-light small-text text-muted mb-0">
                            New product launch
                        </p>
                    </div>
                </a>
                <a class="dropdown-item preview-item">
                    <div class="preview-thumbnail">
                        <img src="<?= base_url() ?>assets/images/placeholder-36x36.svg" alt="image" class="profile-pic">
                    </div>
                    <div class="preview-item-content flex-grow">
                        <h6 class="preview-subject ellipsis fw-normal"> Johnson
                        </h6>
                        <p class="fw-light small-text text-muted mb-0">
                            Upcoming board meeting
                        </p>
                    </div>
                </a>
                </div>
            </li>
            <li class="nav-item dropdown me-4">
                <a class="nav-link count-indicator dropdown-toggle d-flex align-items-center justify-content-center"
                id="notificationDropdown" href="#" data-bs-toggle="dropdown">
                <i class="mdi mdi-bell mx-0"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list"
                aria-labelledby="notificationDropdown">
                <p class="mb-0 fw-normal float-start dropdown-header">Notifications</p>
                <a class="dropdown-item preview-item">
                    <div class="preview-thumbnail">
                        <div class="preview-icon bg-success">
                            <i class="mdi mdi-information mx-0"></i>
                        </div>
                    </div>
                    <div class="preview-item-content">
                        <h6 class="preview-subject fw-normal">Application Error</h6>
                        <p class="fw-light small-text mb-0 text-muted">
                            Just now
                        </p>
                    </div>
                </a>
                <a class="dropdown-item preview-item">
                    <div class="preview-thumbnail">
                        <div class="preview-icon bg-warning">
                            <i class="mdi mdi-settings mx-0"></i>
                        </div>
                    </div>
                    <div class="preview-item-content">
                        <h6 class="preview-subject fw-normal">Settings</h6>
                        <p class="fw-light small-text mb-0 text-muted">
                            Private message
                        </p>
                    </div>
                </a>
                <a class="dropdown-item preview-item">
                    <div class="preview-thumbnail">
                        <div class="preview-icon bg-info">
                            <i class="mdi mdi-account-box mx-0"></i>
                        </div>
                    </div>
                    <div class="preview-item-content">
                        <h6 class="preview-subject fw-normal">New user registration</h6>
                        <p class="fw-light small-text mb-0 text-muted">
                            2 days ago
                        </p>
                    </div>
                </a>
                </div>
            </li> -->
            <li class="nav-item nav-profile dropdown me-0 me-sm-2">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" id="profileDropdown">
                <?php if ($profileImageUrl !== ''): ?>
                <img src="<?= esc($profileImageUrl, 'attr') ?>" alt="profile" />
                <?php else: ?>
                <span style="width:36px;height:36px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#4B49AC;color:#fff;font-weight:600;font-size:14px;line-height:1;"><?= esc($profileInitial) ?></span>
                <?php endif; ?>
                <span class="nav-profile-name"><?= session()->get('auth_name'); ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
                <div class="dropdown-item-text text-muted small"><?= lang('App.common.language') ?></div>
                <a class="dropdown-item" href="<?= site_url('lang/en') ?>">
                <i class="mdi mdi-translate text-primary"></i>
                <?= lang('App.common.english') ?>
                </a>
                <a class="dropdown-item" href="<?= site_url('lang/ta') ?>">
                <i class="mdi mdi-translate text-primary"></i>
                <?= lang('App.common.tamil') ?>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item">
                <i class="mdi mdi-settings text-primary"></i>
                <?= lang('App.common.settings') ?>
                </a>
                <a class="dropdown-item" href="<?= base_url('logout') ?>">
                <i class="mdi mdi-logout text-primary"></i>
                <?= lang('App.common.logout') ?>
                </a>
                </div>
            </li>
            <li class="nav-item nav-settings d-none d-lg-flex">
                <a class="nav-link" href="#">
                <i class="mdi mdi-dots-vertical"></i>
                </a>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
            data-bs-toggle="offcanvas">
        <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>