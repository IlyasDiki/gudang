<?php
require '../config/init.php';

/* FILTER */
$tgl_awal  = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$id_supplier = $_GET['id_supplier'] ?? '';

$where = "WHERE 1=1";

if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $where .= " AND DATE(d.tanggal) BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

if (!empty($id_supplier)) {
    $where .= " AND d.id_supplier='$id_supplier'";
}

/* SUPPLIER */
$qSupplier = mysqli_query($conn, "
    SELECT id_supplier, nama_supplier
    FROM supplier
    ORDER BY nama_supplier
");

/* DATA PEMAKAIAN AT */
$q = mysqli_query($conn, "
    SELECT 
        d.id_at,
        d.tanggal,
        d.sortir,
        d.ma,
        d.aa,
        d.b_mentah,
        d.air,
        d.atp,
        d.id_supplier,
        s.nama_supplier,
        d.status
    FROM at_detail d
    LEFT JOIN supplier s ON s.id_supplier = d.id_supplier
    $where
    ORDER BY d.tanggal DESC
");


?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Kelola Pemakaian AT</title>

<link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="../dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php include "../layout/navbar.php"; ?>
<?php include "../layout/sidebar.php"; ?>

<div class="content-wrapper p-3">

<section class="content-header">
    <h1>Kelola / Koreksi Pemakaian AT</h1>
</section>

<section class="content">
<div class="card">

<div class="card-header">
    <h3 class="card-title">Data Pemakaian AT</h3>

    <a href="pemakaian_at.php" class="btn btn-secondary btn-sm float-right">
        ← Kembali
    </a>
</div>

<!-- FILTER -->
<form method="GET" class="p-3 border-bottom">
<div class="row">

    <div class="col-md-3">
        <label>Tanggal Awal</label>
        <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="form-control form-control-sm">
    </div>

    <div class="col-md-3">
        <label>Tanggal Akhir</label>
        <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="form-control form-control-sm">
    </div>

    <div class="col-md-3">
        <label>Supplier</label>
        <select name="id_supplier" class="form-control form-control-sm">
            <option value="">-- Semua Supplier --</option>
            <?php while ($s = mysqli_fetch_assoc($qSupplier)) { ?>
                <option value="<?= $s['id_supplier'] ?>"
                    <?= $id_supplier == $s['id_supplier'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['nama_supplier']) ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <button class="btn btn-primary btn-sm">Tampilkan</button>
    </div>

</div>
</form>

<div class="card-body table-responsive">
<table class="table table-bordered table-striped table-sm">
<thead class="bg-light">
<tr>
    <th>No</th>
    <th>Tanggal</th>
    <th>Supplier</th>
    <th>Sortir</th>
    <th>MA</th>
    <th>AA</th>
    <th>B Mentah</th>
    <th>Air</th>
    <th>ATP</th>
    <th>Total</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>
</thead>

<tbody>

<?php 
$no = 1;
while ($row = mysqli_fetch_assoc($q)):

$total = $row['sortir'] + $row['ma'] + $row['aa'] + 
         $row['b_mentah'] + $row['air'] + $row['atp'];
?>

<tr>
    <td><?= $no++ ?></td>
    <td><?= $row['tanggal'] ?></td>
    <td><?= htmlspecialchars($row['nama_supplier']) ?></td>
    <td><?= $row['sortir'] ?></td>
    <td><?= $row['ma'] ?></td>
    <td><?= $row['aa'] ?></td>
    <td><?= $row['b_mentah'] ?></td>
    <td><?= $row['air'] ?></td>
    <td><?= $row['atp'] ?></td>
    <td><b><?= $total ?></b></td>

    <td>
        <?php if (($row['status'] ?? 'aktif') == 'aktif'): ?>
            <span class="badge badge-success">Aktif</span>
        <?php else: ?>
            <span class="badge badge-danger">Dikoreksi</span>
        <?php endif; ?>
    </td>

    <td>
        <?php if (($row['status'] ?? 'aktif') == 'aktif'): ?>

            <button class="btn btn-warning btn-xs btn-edit"
                data-id="<?= $row['id_at'] ?>"
                data-sortir="<?= $row['sortir'] ?>"
                data-ma="<?= $row['ma'] ?>"
                data-aa="<?= $row['aa'] ?>"
                data-bm="<?= $row['b_mentah'] ?>"
                data-air="<?= $row['air'] ?>"
                data-atp="<?= $row['atp'] ?>">
                Edit
            </button>

        <?php else: ?>
            <span class="text-muted">-</span>
        <?php endif; ?>
    </td>

</tr>

<?php endwhile; ?>

</tbody>
</table>
</div>

</div>

<div class="modal fade" id="modalEdit">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Pemakaian AT</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="post" action="edit_pemakaian_at_proses.php">
        <div class="modal-body">
          <input type="hidden" name="id_at" id="edit_id">
          
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="edit_sortir">Sortir</label>
                <input type="number" name="sortir" id="edit_sortir" class="form-control" step="0.01" min="0" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="edit_ma">MA</label>
                <input type="number" name="ma" id="edit_ma" class="form-control" step="0.01" min="0" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="edit_aa">AA</label>
                <input type="number" name="aa" id="edit_aa" class="form-control" step="0.01" min="0" required>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="edit_bm">B Mentah</label>
                <input type="number" name="b_mentah" id="edit_bm" class="form-control" step="0.01" min="0" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="edit_air">Air</label>
                <input type="number" name="air" id="edit_air" class="form-control" step="0.01" min="0" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="edit_atp">ATP</label>
                <input type="number" name="atp" id="edit_atp" class="form-control" step="0.01" min="0" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

</section>
</div>

<?php include "../footer.php"; ?>

</div>

<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../dist/js/adminlte.js"></script>
<script>
    $('.btn-edit').click(function () {
    $('#edit_id').val($(this).data('id'));
    $('#edit_sortir').val($(this).data('sortir'));
    $('#edit_ma').val($(this).data('ma'));
    $('#edit_aa').val($(this).data('aa'));
    $('#edit_bm').val($(this).data('bm'));
    $('#edit_air').val($(this).data('air'));
    $('#edit_atp').val($(this).data('atp'));

    $('#modalEdit').modal('show');
});
</script>
</body>
</html>