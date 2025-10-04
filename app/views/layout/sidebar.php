
<div class="sidebar d-flex flex-column p-3 bg-light vh-100 shadow-sm">
  <ul class="nav nav-pills flex-column">
    <li class="nav-item">
      <a href="index.php?page=dashboard" class="nav-link <?= $page == 'dashboard' ? 'active' : '' ?>">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard
      </a>
    </li>
    <li class="nav-item">
      <a href="index.php?page=devices" class="nav-link <?= $page == 'devices' ? 'active' : '' ?>">
        <i class="bi bi-phone me-2"></i> Devices
      </a>
    </li>
    <li class="nav-item">
      <a href="index.php?page=policy_create" class="nav-link <?= $page == 'policy_create' ? 'active' : '' ?>">
        <i class="bi bi-shield-lock me-2"></i> Policies
      </a>
    </li>
    <li class="nav-item">
      <a href="index.php?page=konfigurasi" class="nav-link <?= $page == 'konfigurasi' ? 'active' : '' ?>">
        <i class="bi bi-gear me-2"></i> Konfigurasi Policy
      </a>
    </li>
    <li class="nav-item">
      <a href="index.php?page=googleplay_manage" class="nav-link <?= $page == 'googleplay_manage' ? 'active' : '' ?>">
        <i class="bi bi-google-play me-2"></i> Google Play Manage
      </a>
    </li>
    <li class="nav-item">
      <a href="index.php?page=app_search" class="nav-link <?= $page == 'app_search' ? 'active' : '' ?>">
        <i class="bi bi-app-indicator me-2"></i> Google Play Apps
      </a>
    <li class="nav-item">
      <a href="index.php?page=enroll" class="nav-link <?= $page == 'enroll' ? 'active' : '' ?>">
        <i class="bi bi-person-plus me-2"></i> Enrollment
      </a>
    </li>

    <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
      <li class="nav-item">
        <a href="index.php?page=user_add" class="nav-link <?= $page == 'user_add' ? 'active' : '' ?>">
          <i class="bi bi-person-add me-2"></i> Manage Users
        </a>
      </li>
      <li class="nav-item">
        <a href="index.php?page=user_list" class="nav-link <?= $page == 'user_list' ? 'active' : '' ?>">
          <i class="bi bi-people me-2"></i> List Users
        </a>
      </li>
    <?php endif; ?>
  </ul>
</div>

<div class="content p-3 flex-grow-1">
