<?php 
require '../config/init.php';

/* ========================= AMBIL BARANG ========================= */
$qBarang = mysqli_query($conn, "
    SELECT b.id_barang, b.nama_barang
    FROM barang b
    JOIN kelompok_barang k ON k.id_kelompok = b.id_kelompok
    WHERE k.nama_kelompok = 'Arang Tempurung Kelapa'
    LIMIT 1
");

$barang   = mysqli_fetch_assoc($qBarang);
$idBarang = $barang['id_barang'] ?? 0;

/* ========================= AMBIL SUPPLIER ========================= */
$qSupplier = mysqli_query($conn, "
    SELECT id_supplier, nama_supplier
    FROM supplier
    ORDER BY nama_supplier
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pemakaian AT</title>

    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php 
$page = 'pemakaian_at';
include "../layout/navbar.php";
include "../layout/sidebar.php";
?>

<div class="content-wrapper p-3">

<section class="content-header">
    <h1>Pemakaian AT</h1>
</section>

<section class="content">
<div class="card">

<div class="card-header">
    <h3 class="card-title">Input Pemakaian AT</h3>

    <a href="../laporan/pemakaian_at_laporan.php">
        <button class="btn btn-primary btn-sm float-right">
            📊 Lihat Laporan
        </button>
    </a>
</div>

<form method="post" action="pemakaian_at_simpan.php">

<div class="card-body">
<input type="hidden" name="id_mutasi_detail" id="id_mutasi_detail">
<!-- ================= HEADER ================= -->
<div class="row">
    <div class="col-md-3">
        <label>Tanggal</label>
        <input type="date" name="tanggal" class="form-control form-control-sm" required>
    </div>

    <div class="col-md-3">
        <label>Barang</label>
        <input type="text" class="form-control form-control-sm"
               value="<?= htmlspecialchars($barang['nama_barang']) ?>" readonly>
        <input type="hidden" name="id_barang" value="<?= $idBarang ?>">
    </div>

    <div class="col-md-3">
        <label>Supplier</label>
        <select name="id_supplier" id="id_supplier"
                class="form-control form-control-sm" required>
            <option value="">-- Pilih Supplier --</option>
            <?php while ($s = mysqli_fetch_assoc($qSupplier)) { ?>
                <option value="<?= $s['id_supplier'] ?>">
                    <?= htmlspecialchars($s['nama_supplier']) ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="col-md-3">
        <label>Sumber Stok</label>
        <select id="sumber_stok" class="form-control form-control-sm" disabled>
            <option value="">-- Pilih Supplier dulu --</option>
        </select>
    </div>
</div>

<!-- ================= SALDO ================= -->
<div class="row mt-2">
    <div class="col-md-3">
        <label>Saldo Powder</label>
        <input type="text" id="saldo"
               class="form-control form-control-sm"
               value="0" readonly>
    </div>
</div>

<hr>

<!-- ================= FORM PEMAKAIAN ================= -->
<div id="formPemakaian" style="display:none;">

    <div class="row">
        <div class="col-md-2">
            <label>Sortir</label>
            <input type="number" step="0.01" name="sortir" value="0"
                   class="form-control form-control-sm">
        </div>

        <div class="col-md-2">
            <label>MA</label>
            <input type="number" step="0.01" name="ma" value="0"
                   class="form-control form-control-sm">
        </div>

        <div class="col-md-2">
            <label>AA</label>
            <input type="number" step="0.01" name="aa" value="0"
                   class="form-control form-control-sm">
        </div>

        <div class="col-md-2">
            <label>B Mentah</label>
            <input type="number" step="0.01" name="b_mentah" value="0"
                   class="form-control form-control-sm">
        </div>

        <div class="col-md-2">
            <label>Air</label>
            <input type="number" step="0.01" name="air" value="0"
                   class="form-control form-control-sm">
        </div>

        <div class="col-md-2">
            <label>ATP</label>
            <input type="number" step="0.01" name="atp" value="0"
                   class="form-control form-control-sm">
        </div>
    </div>

    <div class="form-group mt-3">
        <label>Keterangan</label>
        <textarea name="keterangan" rows="2"
                  class="form-control form-control-sm"
                  placeholder="Opsional"></textarea>
    </div>

</div>

</div>

<div class="card-footer">
    <button class="btn btn-success">
        <i class="fa fa-save"></i> Simpan
    </button>

    <a href="pemakaian_at.php" class="btn btn-secondary">Batal</a>
</div>

</form>
</div>
</section>
</div>

<?php include "../footer.php"; ?>

</div>

<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../dist/js/adminlte.js"></script>

<script>
/* ========================= PILIH SUPPLIER ========================= */
$('#id_supplier').change(function () {

    let supplier = $(this).val();

    $('#sumber_stok').prop('disabled', true);
    $('#formPemakaian').hide();
    $('#saldo').val(0);

    if (!supplier) return;

    fetch('ajax_get_saldo_at.php?id_barang=<?= $idBarang ?>&id_supplier=' + supplier)
        .then(res => res.json())
        .then(data => {

            if (data.status !== 'ok') return;

            let html = '<option value="">-- Pilih Sumber Stok --</option>';

            data.stok.forEach(row => {
                html += `<option value="${row.id}" data-saldo="${row.saldo}">
                            ${row.label} (${row.saldo} kg)
                         </option>`;
            });

            $('#sumber_stok').html(html);
            $('#sumber_stok').prop('disabled', false);
        });
});


$('#sumber_stok').change(function () {

    let id = $(this).val();
    let saldo = $(this).find(':selected').data('saldo');

    $('#id_mutasi_detail').val(id);

    if (!id) {
        $('#formPemakaian').hide();
        $('#saldo').val(0);
        return;
    }

    $('#saldo').val(saldo);
    $('#formPemakaian').show();
});
</script>

</body>
</html>