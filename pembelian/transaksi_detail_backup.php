<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require "../config/init.php";

$id = $_GET["id"] ?? null;
if (!$id) {
    die("ID tidak ditemukan");
}

// LIST BARANG
$barang = mysqli_query(
    $conn,
    "
SELECT 
    b.id_barang,
    b.nama_barang,
    b.id_kelompok,
    b.pakai_supplier,
    k.nama_kelompok,
    k.parent_id
FROM barang b
JOIN kelompok_barang k 
ON k.id_kelompok = b.id_kelompok
ORDER BY k.nama_kelompok, b.nama_barang
"
);

$supplier = mysqli_query(
    $conn,
    "
SELECT 
  id_supplier,
  nama_supplier
FROM supplier
ORDER BY nama_supplier
"
);

// DETAIL TRANSAKSI
$detail = mysqli_query(
    $conn,
    "
SELECT 
  d.id_detail,
  d.jumlah,
  b.nama_barang,
  s.nama_supplier
FROM transaksi_detail d
JOIN barang b 
  ON b.id_barang = d.id_barang
LEFT JOIN supplier s
  ON s.id_supplier = d.id_supplier
WHERE d.id_transaksi = '$id'
ORDER BY d.id_detail DESC
"
);

// HEADER TRANSAKSI
$trx = mysqli_query(
    $conn,
    "
SELECT 
  t.tanggal_terima,
  t.id_kelompok,
  kb.nama_kelompok,
  jt.nama_jenis AS jenis_transaksi
FROM transaksi t
JOIN jenis_transaksi jt 
  ON jt.id_jenist = t.jenis_transaksi
JOIN kelompok_barang kb
  ON kb.id_kelompok = t.id_kelompok
WHERE t.id_transaksi = '$id'
"
);

$trx = mysqli_fetch_assoc($trx);

$idKelompokUtama = $trx["id_kelompok"];

$subKelompok = mysqli_query(
    $conn,
    "
SELECT *
FROM kelompok_barang
WHERE parent_id = '$idKelompokUtama'
ORDER BY nama_kelompok
"
);
$adaSub = mysqli_num_rows($subKelompok);
?>
<div class="alert alert-info mb-3">
  <strong>Jenis Transaksi:</strong> <?= $trx["jenis_transaksi"] ?><br>
  <strong>Kelompok Barang:</strong> <?= $trx["nama_kelompok"] ?><br>
  <strong>Tanggal:</strong> <?= date(
      "d-m-Y",
      strtotime($trx["tanggal_terima"])
  ) ?>
</div>

<form method="post" action="transaksi_detail_simpan.php" id="formDetail">
<input type="hidden" name="id_transaksi" value="<?= $id ?>">

<div class="card p-3 mb-3">

  <div class="row align-items-end">

    <?php if ($adaSub > 0): ?>
    <div class="col-md-3">
      <label class="form-label">Jenis Barang</label>
      <select id="sub_kelompok" class="form-control">
        <option value="">-- Pilih Jenis --</option>
        <?php while ($s = mysqli_fetch_assoc($subKelompok)): ?>
          <option value="<?= $s["id_kelompok"] ?>">
            <?= $s["nama_kelompok"] ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <?php endif; ?>

    <div class="col-md-4">
      <label class="form-label">Barang</label>
      <select name="id_barang" id="barang" class="form-control" required>
        <option value="">-- Pilih Barang --</option>
        <?php while ($b = mysqli_fetch_assoc($barang)): ?>
          <option 
            value="<?= $b["id_barang"] ?>"
            data-kelompok="<?= $b["id_kelompok"] ?>"
            data-supplier="<?= $b["pakai_supplier"] ?>"
          >
            <?= $b["nama_barang"] ?>
          </option>
        <?php endwhile; ?>
      </select>
      <small id="infoBarang" class="text-danger" style="display:none;">
        Tidak ada barang pada kelompok ini
      </small>
    </div>

    <div class="col-md-2" id="supplierBox" style="display:none;">
      <label class="form-label">Supplier</label>
      <select id="supplier" name="id_supplier" class="form-control">
        <option value="">-- Pilih Supplier --</option>
        <?php while ($s = mysqli_fetch_assoc($supplier)): ?>
          <option value="<?= $s["id_supplier"] ?>">
            <?= $s["nama_supplier"] ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="col-md-2">
      <label class="form-label">Jumlah</label>
      <input type="number" name="jumlah" id="jumlah" class="form-control" required>
    </div>

    <div class="col-md-1">
      <button class="btn btn-success w-100">
        +
      </button>
    </div>

  </div>

</div>
</form>


<div class="card">
  <div class="card-body p-0">
    <table class="table table-bordered table-striped table-sm mb-0">
      <thead class="text-center bg-light">
        <tr>
          <th>Barang</th>
          <th>Supplier</th>
          <th style="width:120px;">Jumlah</th>
          <th style="width:100px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($d = mysqli_fetch_assoc($detail)): ?>
        <tr>
          <td><?= $d["nama_barang"] ?></td>
          <td><?= $d["nama_supplier"] ?? "-" ?></td>
          <td class="text-center"><?= number_format($d["jumlah"],2) ?></td>
          <td class="text-center">
            <a href="transaksi_detail_hapus.php?id=<?= $d["id_detail"] ?>" 
               class="btn btn-danger btn-sm px-3"
               onclick="return confirm('Hapus data?')">
              Hapus
            </a>  
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
// FILTER BARANG BERDASARKAN JENIS
document.getElementById("sub_kelompok")?.addEventListener("change", function() {

    let kelompok = this.value;
    let barangSelect = document.getElementById("barang");
    let info = document.getElementById("infoBarang");

    let options = barangSelect.options;
    let jumlah = 0;

    for (let i = 0; i < options.length; i++) {
        let opt = options[i];
        let idKelompok = opt.getAttribute("data-kelompok");

        if (!idKelompok) {
            opt.hidden = false;
            continue;
        }

        if (idKelompok == kelompok) {
            opt.hidden = false;
            jumlah++;
        } else {
            opt.hidden = true;
        }
    }

    barangSelect.value = "";

    if (jumlah == 0) {
        info.style.display = "block";
    } else {
        info.style.display = "none";
    }
});


// TAMPILKAN SUPPLIER JIKA DIPERLUKAN
document.getElementById('barang').addEventListener('change', function() {
    let supplierBox = document.getElementById('supplierBox');
    let selected = this.options[this.selectedIndex];
    let pakaiSupplier = selected.getAttribute("data-supplier");

    if (pakaiSupplier == "1") {
        supplierBox.style.display = "block";
    } else {
        supplierBox.style.display = "none";
    }
});
</script>

<script>
document.getElementById("formDetail").addEventListener("submit", function(e) {
    e.preventDefault(); // ⛔ stop reload

    let form = this;
    let formData = new FormData(form);

    fetch("transaksi_detail_simpan.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {

            alert(data.message);

            // 🔥 ambil ulang isi modal
            $("#isiDetail").load("transaksi_detail.php?id=<?= $id ?>");

        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        alert("Terjadi error!");
        console.log(err);
    });
});
</script>