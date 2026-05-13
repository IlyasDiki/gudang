<?php 
require_once __DIR__ . '/../config/init.php';

/* =========================
   FILTER
========================= */
$bulan = $_GET['bulan'] ?? '';
$id_barang_atp = $_GET['id_barang_atp'] ?? '';
$id_supplier   = $_GET['id_supplier'] ?? '';

$whereTanggal = "";

if (!empty($bulan)) {
    $awal  = $bulan . '-01';
    $akhir = date('Y-m-t', strtotime($awal));
    $whereTanggal = " AND p.tanggal BETWEEN '$awal' AND '$akhir'";
}

/* =========================
   BARANG ATP
========================= */
$qBarangATP = mysqli_query($conn, "
SELECT id_barang, nama_barang
FROM barang
WHERE id_kelompok = (
    SELECT id_kelompok 
    FROM kelompok_barang 
    WHERE nama_kelompok='Powder'
)
LIMIT 1
");

$dataBarangATP = mysqli_fetch_assoc($qBarangATP);
$idBarangATP   = $dataBarangATP['id_barang'];
$namaBarangATP = $dataBarangATP['nama_barang'];

/* =========================
   SUPPLIER
========================= */
$supplier = mysqli_query($conn, "
    SELECT id_supplier, nama_supplier
    FROM supplier
    WHERE tipe = 'external' OR nama_supplier = 'REPRO BRIKET'
    ORDER BY 
        (nama_supplier = 'REPRO BRIKET') ASC,
        nama_supplier ASC
");

/* =========================
   DATA PRODUKSI
========================= */
$qProduksi = mysqli_query($conn, "
    SELECT 
        p.id_produksi,
        p.tanggal,
        b.nama_barang,
        s.nama_supplier,
        IFNULL(SUM(pd.mixer),0) AS mixer,
        p.keterangan
    FROM produksi p
    JOIN barang b ON b.id_barang = p.id_barang_atp
    JOIN supplier s ON s.id_supplier = p.id_supplier AND s.tipe = 'external'
    LEFT JOIN produksi_detail pd ON pd.id_produksi = p.id_produksi
    WHERE 1=1
    $whereTanggal
    " . (!empty($id_barang_atp) ? " AND p.id_barang_atp='$id_barang_atp'" : "") . "
    " . (!empty($id_supplier) ? " AND p.id_supplier='$id_supplier'" : "") . "
    GROUP BY p.id_produksi
    ORDER BY p.tanggal ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Produksi</title>

    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <?php 
    $page='produksi';
    include "../layout/navbar.php"; 
    include "../layout/sidebar.php"; 
    ?>

    <div class="content-wrapper p-3">

        <!-- FORM -->
        <form method="post" action="produksi_simpan.php">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Input Produksi</h3>
                </div>

                <div class="card-body">

                    <input type="hidden" name="id_mutasi_detail" id="id_mutasi_detail">

                    <div class="row">
                        <div class="col-md-3">
                            <label>Tanggal</label>
                            <input type="date" name="tanggal" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-3">
                            <label>ATP</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($namaBarangATP) ?>" readonly>
                            <input type="hidden" name="id_barang_atp" id="id_barang_atp" value="<?= $idBarangATP ?>">
                        </div>

                        <div class="col-md-3">
                            <label>Supplier</label>
                            <select name="id_supplier" id="id_supplier" class="form-control form-control-sm" required>
                                <option value="">-- Pilih --</option>
                                <?php while($s=mysqli_fetch_assoc($supplier)): ?>
                                    <option value="<?= $s['id_supplier'] ?>">
                                        <?= htmlspecialchars($s['nama_supplier']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Sumber Stok</label>
                            <select id="sumber_stok" class="form-control form-control-sm" disabled>
                                <option>-- Pilih Supplier dulu --</option>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-3">
                            <label>Total ATP</label>
                            <input type="text" id="total_atp" class="form-control form-control-sm" value="0" readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Total Mixer</label>
                            <input type="text" id="total_mixer" class="form-control form-control-sm" value="0" readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Saldo ATP</label>
                            <input type="text" id="saldo_atp" class="form-control form-control-sm" value="0" readonly>
                        </div>
                    </div>

                    <hr>

                    <div id="formInput" style="display:none;">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Mixer</label>
                                <input type="number" step="0.01" name="mixer" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-6">
                                <label>Keterangan</label>
                                <input type="text" name="keterangan" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    <button class="btn btn-success">
                        <i class="fa fa-save"></i> Simpan
                    </button>
                </div>
            </div>
        </form>

        
        <!-- TABLE -->
        <div class="card">
            <div class="card-body table-responsive">

                <form method="GET" class="mb-3">
                    <input type="hidden" name="id_produksi" id="edit_id">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Bulan</label>
                            <input type="month" name="bulan" 
                                value="<?= htmlspecialchars($bulan) ?>"
                                class="form-control form-control-sm">
                        </div>

                        <div class="col-md-3">
                            <label>Supplier</label>
                            <select name="id_supplier" class="form-control form-control-sm">
                                <option value="">-- Semua Supplier --</option>
                                <?php 
                                mysqli_data_seek($supplier, 0);
                                while($s=mysqli_fetch_assoc($supplier)): ?>
                                <option value="<?= $s['id_supplier'] ?>"
                                    <?= ($id_supplier==$s['id_supplier']?'selected':'') ?>>
                                    <?= htmlspecialchars($s['nama_supplier']) ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary btn-sm mr-2">Filter</button>
                            <a href="produksi.php" class="btn btn-secondary btn-sm">Reset</a>
                        </div>
                    </div>
                </form>

                <?php if(empty($bulan) && empty($id_barang_atp) && empty($id_supplier)): ?>
                    <small class="text-muted">Menampilkan semua data produksi</small>
                <?php else: ?>
                    <small class="text-success">Filter aktif</small>
                <?php endif; ?>

                <table class="table table-bordered table-sm">
                    <thead class="bg-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th class="text-right">Mixer</th>
                            <th>Keterangan</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($r=mysqli_fetch_assoc($qProduksi)): ?>
                        <tr>
                            <td><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($r['nama_supplier'] ?? '-') ?></td>
                            <td>
                                <div class="view-mixer-<?= $r['id_produksi'] ?> text-right">
                                    <?= number_format($r['mixer'],2) ?>
                                </div>

                                <div class="edit-mixer-<?= $r['id_produksi'] ?>" style="display:none;">
                                    <input type="number"
                                        step="0.01"
                                        class="form-control form-control-sm mixer-input"
                                        value="<?= $r['mixer'] ?>">
                                </div>
                            </td>

                            <td><?= htmlspecialchars($r['keterangan'] ?? '') ?></td>
                            <td>

                            <!-- EDIT -->
                            <button
                                type="button"
                                class="btn btn-warning btn-xs btn-edit"
                                data-id="<?= $r['id_produksi'] ?>">
                                <i class="fa fa-edit"></i> Edit
                            </button>

                            <!-- UPDATE -->
                            <button
                                type="button"
                                class="btn btn-success btn-xs btn-update"
                                data-id="<?= $r['id_produksi'] ?>"
                                style="display:none;">
                                <i class="fa fa-save"></i> Update
                            </button>

                            <!-- HAPUS -->
                            <a href="produksi_hapus.php?id=<?= $r['id_produksi'] ?>"
                            class="btn btn-danger btn-xs"
                            onclick="return confirm('Hapus data?')">
                            <i class="fa fa-trash"></i>
                            </a>

                        </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <?php include "../footer.php"; ?>

</div>

<script src="../plugins/jquery/jquery.min.js"></script>
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../dist/js/adminlte.js"></script>

<script>
function loadSumberStok() {
    let supplier = $('#id_supplier').val();
    let barang   = $('#id_barang_atp').val();

    $('#sumber_stok').html('<option>-- Pilih Supplier dulu --</option>').prop('disabled', true);
    $('#formInput').hide();

    if (!supplier || !barang) return;

    fetch('ajax_get_atp_by_sumber.php?id_barang=' + barang + '&id_supplier=' + supplier)
    .then(res => res.json())
    .then(data => {

        if (data.stok.length === 0) {
            $('#sumber_stok').html('<option>Tidak ada stok</option>');
            return;
        }

        let html = `<option value="">-- Pilih Sumber Stok --</option>`;

        data.stok.forEach(row => {
            html += `
                <option value="${row.id}"
                    data-total_atp="${row.total_atp}"
                    data-total_mixer="${row.total_mixer}"
                    data-saldo_atp="${row.saldo_atp}">
                    ${row.label} | Sisa: ${parseFloat(row.saldo_atp).toFixed(2)} kg
                </option>
            `;
        });

        $('#sumber_stok').html(html).prop('disabled', false);
    });
}

$('#id_barang_atp, #id_supplier').change(loadSumberStok);

$('#sumber_stok').change(function () {
    let selected = $(this).find(':selected');

    let saldo = selected.data('saldo_atp');

    $('#id_mutasi_detail').val($(this).val());
    $('#total_atp').val(parseFloat(selected.data('total_atp') || 0).toFixed(2));
    $('#total_mixer').val(parseFloat(selected.data('total_mixer') || 0).toFixed(2));
    $('#saldo_atp').val(parseFloat(saldo || 0).toFixed(2));

    if (saldo > 0) {
        $('#formInput').show();
    }
});

$('.btn-edit').click(function(){

    let id = $(this).data('id');

    $('.view-mixer-'+id).hide();

    $('.edit-mixer-'+id).show();

    $(this).hide();

    $('.btn-update[data-id="'+id+'"]').show();

});

$('.btn-update').click(function(){

    let id = $(this).data('id');

    let mixer = $('.edit-mixer-'+id+' .mixer-input').val();

    $.post('produksi_update.php',{
        id_produksi:id,
        mixer:mixer
    },function(res){

        location.reload();

    });

});
</script>

</body>
</html>