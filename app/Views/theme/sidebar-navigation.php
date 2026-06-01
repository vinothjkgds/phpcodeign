<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <li class="nav-item">
      <a class="nav-link" href="<?= base_url('dashboard') ?>">
        <i class="mdi mdi-home menu-icon"></i>
        <span class="menu-title"><?= lang('App.common.dashboard') ?></span>
      </a>
    </li>
    <!-- <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#nav_cms" aria-expanded="false" aria-controls="auth">
        <i class="mdi mdi-comment-alert menu-icon"></i>
        <span class="menu-title"><?= lang('App.sidebar.cms') ?></span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="nav_cms">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="<?= base_url('cms/addTicket') ?>"> <?= lang('App.sidebar.addTicket') ?> </a></li>
          <li class="nav-item"> <a class="nav-link" href="<?= base_url('cms/myTickets') ?>"> <?= lang('App.sidebar.myTickets') ?> </a></li>
        </ul>
      </div>
    </li> -->
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#nav_merchant" aria-expanded="<?= current_controller() === 'merchant' ? 'true' : 'false' ?>" aria-controls="nav_merchant">
        <i class="mdi mdi-account-multiple menu-icon"></i>
        <span class="menu-title"><?= lang('App.sidebar.merchants') ?></span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse <?= current_controller() === 'merchant' ? 'show' : '' ?>" id="nav_merchant">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item">
            <a class="nav-link <?= (current_controller() === 'merchant' && current_method() === 'add') ? 'active' : '' ?>" href="<?= base_url('merchant/add') ?>"><?= lang('App.sidebar.addMerchant') ?></a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= (current_controller() === 'merchant' && current_method() === 'index') ? 'active' : '' ?>" href="<?= base_url('merchant') ?>"><?= lang('App.sidebar.manageMerchant') ?></a>
          </li>
        </ul>
      </div>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#nav_employee" aria-expanded="<?= current_controller() === 'employee' ? 'true' : 'false' ?>" aria-controls="nav_employee">
        <i class="mdi mdi-account-group menu-icon"></i>
        <span class="menu-title"><?= lang('App.sidebar.employees') ?></span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse <?= current_controller() === 'employee' ? 'show' : '' ?>" id="nav_employee">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item">
            <a class="nav-link <?= (current_controller() === 'employee' && current_method() === 'add') ? 'active' : '' ?>" href="<?= base_url('employee/add') ?>"><?= lang('App.sidebar.addEmployee') ?></a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= (current_controller() === 'employee' && current_method() === 'index') ? 'active' : '' ?>" href="<?= base_url('employee') ?>"><?= lang('App.sidebar.manageEmployee') ?></a>
          </li>
        </ul>
      </div>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#nav_product" aria-expanded="<?= current_controller() === 'product' ? 'true' : 'false' ?>" aria-controls="nav_product">
        <i class="mdi mdi-cube-outline menu-icon"></i>
        <span class="menu-title"><?= lang('App.sidebar.products') ?></span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse <?= current_controller() === 'product' ? 'show' : '' ?>" id="nav_product">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item">
            <a class="nav-link <?= (current_controller() === 'product' && current_method() === 'add') ? 'active' : '' ?>" href="<?= base_url('product/add') ?>"><?= lang('App.sidebar.addProduct') ?></a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= (current_controller() === 'product' && current_method() === 'index') ? 'active' : '' ?>" href="<?= base_url('product') ?>"><?= lang('App.sidebar.manageProduct') ?></a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= (current_controller() === 'product' && current_method() === 'stockhistory') ? 'active' : '' ?>" href="<?= base_url('product/stock-history') ?>">Stock History</a>
          </li>
        </ul>
      </div>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#nav_salepurchase" aria-expanded="<?= current_controller() === 'salepurchase' ? 'true' : 'false' ?>" aria-controls="nav_salepurchase">
        <i class="mdi mdi-swap-horizontal menu-icon"></i>
        <span class="menu-title"><?= lang('App.sidebar.salePurchase') ?></span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse <?= current_controller() === 'salepurchase' ? 'show' : '' ?>" id="nav_salepurchase">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item">
            <a class="nav-link <?= (current_controller() === 'salepurchase' && current_method() === 'add') ? 'active' : '' ?>" href="<?= base_url('salepurchase/add') ?>"><?= lang('App.sidebar.addEntry') ?></a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= (current_controller() === 'salepurchase' && current_method() === 'index') ? 'active' : '' ?>" href="<?= base_url('salepurchase') ?>"><?= lang('App.sidebar.manageEntries') ?></a>
          </li>
        </ul>
      </div>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= current_controller() === 'shop' ? 'active' : '' ?>" href="<?= base_url('shop') ?>">
        <i class="mdi mdi-store menu-icon"></i>
        <span class="menu-title"><?= lang('App.sidebar.shopProfile') ?></span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="<?= base_url('logout') ?>">
        <i class="mdi mdi-comment-alert menu-icon"></i>
        <span class="menu-title"><?= lang('App.common.logout') ?></span>
      </a>
    </li>

    <!-- <li class="nav-item">
      <a class="nav-link" href="<?= base_url() ?>pages/widgets/widgets.html">
        <i class="mdi mdi-puzzle menu-icon"></i>
        <span class="menu-title">Widgets</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="<?= base_url() ?>pages/ui-features/popups.html">
        <i class="mdi mdi-comment-alert menu-icon"></i>
        <span class="menu-title">Popups</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="<?= base_url() ?>pages/ui-features/notifications.html">
        <i class="mdi mdi-bell menu-icon"></i>
        <span class="menu-title">Notifications</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="<?= base_url() ?>pages/apps/todo.html">
        <i class="mdi mdi-playlist-check menu-icon"></i>
        <span class="menu-title">Todo List</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="<?= base_url() ?>pages/apps/gallery.html">
        <i class="mdi mdi-image menu-icon"></i>
        <span class="menu-title">Gallery</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="<?= base_url() ?>pages/documentation/documentation.html">
        <i class="mdi mdi-file-document-outline menu-icon"></i>
        <span class="menu-title">Documentation</span>
      </a>
    </li> -->
  </ul>
</nav>