<style>
  .main-sidebar {
    background-color: #1E1E1E !important; /* charcoal */

}

.nav-sidebar .nav-link.active {
    background-color: #E67E22 !important; /* orange bara */
    color: #ffffff !important;
}

.nav-sidebar .nav-link:hover {
    background-color: #2C2C2C !important;
}

.brand-link {
    background-color: #E67E22 !important;
    border-bottom: 1px solid rgba(0,0,0,0.2);
    height: 57px;
    display: flex;
    align-items: center;
}

.brand-link .brand-text {
    color: white !important;
}

  /* Custom scrollbar styling */
  nav.sidebar-nav::-webkit-scrollbar {
    width: 6px;
  }
  nav.sidebar-nav::-webkit-scrollbar-track {
    background: transparent;
  }
  nav.sidebar-nav::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
  }
  nav.sidebar-nav::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
  }
</style>

<aside class="main-sidebar sidebar-dark-primary elevation-4">

  <a href="index.php" class="brand-link">
    <img src="<?= BASE_URL ?>assets/logo.png" height="60"><strong>Gudang</strong>
  </a>

  <div class="sidebar">
    <nav class="mt-2 sidebar-nav" style="max-height: calc(100vh - 120px); overflow-y: auto; overflow-x: hidden;">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

        <!-- DASHBOARD -->
        <li class="nav-item">
          <a href="<?= BASE_URL ?>index.php" class="nav-link">
            <i class="nav-icon fas fa-home"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <!-- MASTER (Collapsible) -->
        <li class="nav-item has-treeview <?= in_array($page, ['barang','kelompok_barang','supplier']) ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= in_array($page, ['barang','kelompok_barang','supplier']) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-cube"></i>
            <p>Master <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= BASE_URL ?>master/barang.php"
                 class="nav-link <?= ($page == 'barang') ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Barang</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>master/kelompok_barang.php"
                 class="nav-link <?= ($page == 'kelompok_barang') ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Kelompok Barang</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>master/supplier.php"
                 class="nav-link <?= ($page == 'supplier') ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Supplier</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- TRANSAKSI (Collapsible) -->
        <li class="nav-item has-treeview <?= in_array($page, ['stok_awal','transaksi','mutasi','pemakaian_at','produksi','bkbriket']) ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= in_array($page, ['stok_awal','transaksi','mutasi','pemakaian_at','produksi','bkbriket']) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-exchange-alt"></i>
            <p>Transaksi <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= BASE_URL ?>transaksi/stok_awal.php"
                 class="nav-link <?= ($page == 'stok_awal') ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Stok Awal</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>pembelian/transaksi.php"
                 class="nav-link <?= ($page == 'transaksi') ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Transaksi</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>transaksi/mutasi.php"
                 class="nav-link <?= ($page == 'mutasi') ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Mutasi</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>transaksi/pemakaian_at.php"
                class="nav-link <?= ($page=='pemakaian_at')?'active':'' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Pemakaian AT</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>transaksi/produksi.php"
                class="nav-link <?= ($page=='produksi')?'active':'' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Produksi AT</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>briket/bkbriket.php"
                class="nav-link <?= ($page=='bkbriket')?'active':'' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Produksi Briket</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Optional/Tambahan (Collapsible) -->
        <li class="nav-item has-treeview <?= in_array($page, ['tambahan_pemakaian_at','stok_fisik_at']) ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= in_array($page, ['tambahan_pemakaian_at','stok_fisik_at']) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-plus-square"></i>
            <p>Optional <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= BASE_URL ?>transaksi/tambahan_pemakaian_at.php"
                class="nav-link <?= ($page=='tambahan_pemakaian_at')?'active':'' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Tambahan Pemakaian AT</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>transaksi/stok_fisik_at.php"
                class="nav-link <?= ($page=='stok_fisik_at')?'active':'' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Stok Fisik AT(Habis)</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Laporan (Collapsible) -->
        <li class="nav-item has-treeview <?= in_array($page, ['kartu_stok','pemakaian_at_ringkasan','bkbriket_ringkasan']) ? 'menu-open' : '' ?>">
          <a href="#" class="nav-link <?= in_array($page, ['kartu_stok','pemakaian_at_ringkasan','bkbriket_ringkasan']) ? 'active' : '' ?>">
            <i class="nav-icon fas fa-file-alt"></i>
            <p>Laporan <i class="right fas fa-angle-left"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= BASE_URL ?>laporan/kartu_stok.php"
                 class="nav-link <?= ($page == 'kartu_stok') ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Kartu Stok</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>laporan/pemakaian_at_ringkasan.php"
                 class="nav-link <?= ($page == 'pemakaian_at_ringkasan') ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>AT & Powder</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= BASE_URL ?>laporan/bkbriket_ringkasan.php"
                 class="nav-link <?= ($page == 'bkbriket_ringkasan') ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Briket</p>
              </a>
            </li>
          </ul>
        </li>

      </ul>
    </nav>
  </div>
</aside>