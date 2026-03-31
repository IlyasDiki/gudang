<style>
.main-header {
    background-color: #E67E22  !important;
    height: 57px;
}
</style>
<!-- NAVBAR -->
<nav class="main-header navbar navbar-expand navbar-dark">
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#">
        <i class="fas fa-bars"></i>
      </a>
    </li>
  </ul>

  <ul class="navbar-nav ml-auto">
    <li class="nav-item">
      <span class="nav-link">
        Login sebagai: 
        <b><?= htmlspecialchars($_SESSION['nama']) ?></b>
      </span>
    </li>

    <li class="nav-item">
      <a href="<?= BASE_URL ?>auth/logout.php" class="nav-link text-white">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </li>
  </ul>
</nav>