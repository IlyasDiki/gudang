<?php
require '../config/init.php';
require_once "../dompdf/autoload.inc.php";

use Dompdf\Dompdf;
use Dompdf\Options;

// ================================
// FILTER
// ================================
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$bulan = str_pad($bulan, 2, "0", STR_PAD_LEFT);

$tglAwal  = "$tahun-$bulan-01";
$tglAkhir = date("Y-m-t", strtotime($tglAwal));

// ================================
// NAMA BULAN
// ================================
$namaBulan = [
  "01"=>"Januari","02"=>"Februari","03"=>"Maret","04"=>"April",
  "05"=>"Mei","06"=>"Juni","07"=>"Juli","08"=>"Agustus",
  "09"=>"September","10"=>"Oktober","11"=>"November","12"=>"Desember"
];

$judulBulan = ($namaBulan[$bulan] ?? $bulan) . " " . $tahun;

// ================================
// QUERY (PAKAI PUNYA KAMU SAJA)
// ================================
$q = mysqli_query($conn, "
SELECT
    kb1.nama_kelompok AS nama_kelompok,
    kb2.nama_kelompok AS parent_barang,
    b.nama_barang,
    b.satuan,

    /* =========================
       STOK AWAL
    ========================= */
      IFNULL(
          SUM(
              CASE 
                  /* PRIORITAS 1: STOK AWAL BULAN INI */
                  WHEN jm.tipe = 'STOKAWAL'
                  AND DATE_FORMAT(m.tanggal,'%Y-%m') = '$tahun-$bulan'
                      THEN md.jumlah
                  ELSE 0
              END
          ),

          /* PRIORITAS 2: SALDO SEBELUM BULAN */
          SUM(
              CASE 
                  WHEN m.tanggal < '$tglAwal' 
                  AND m.arah='MASUK'
                      THEN md.jumlah

                  WHEN m.tanggal < '$tglAwal' 
                  AND m.arah='KELUAR'
                      THEN -md.jumlah

                  ELSE 0
              END
          )

      ) AS stok_awal,
    /* CEK ADA STOK AWAL ATAU TIDAK */
CASE 
    WHEN SUM(
        CASE 
            WHEN jm.tipe = 'STOKAWAL'
            AND DATE_FORMAT(m.tanggal,'%Y-%m') = '$tahun-$bulan'
            THEN 1 ELSE 0
        END
    ) > 0

    THEN 
        /* PAKAI STOK AWAL */
        SUM(
            CASE 
                WHEN jm.tipe = 'STOKAWAL'
                AND DATE_FORMAT(m.tanggal,'%Y-%m') = '$tahun-$bulan'
                THEN md.jumlah
                ELSE 0
            END
        )

    ELSE 
        /* PAKAI SALDO SEBELUMNYA */
        SUM(
            CASE 
                WHEN m.tanggal < '$tglAwal' 
                AND m.arah='MASUK'
                THEN md.jumlah

                WHEN m.tanggal < '$tglAwal' 
                AND m.arah='KELUAR'
                THEN -md.jumlah

                ELSE 0
            END
        )
END AS stok_awal,
    /* =========================
       MASUK
    ========================= */
    IFNULL(SUM(
        CASE 
            WHEN m.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
            AND m.arah='MASUK'
            AND (jm.tipe IS NULL OR jm.tipe != 'STOKAWAL')
            THEN md.jumlah
            ELSE 0
        END
    ),0) AS masuk,

    /* =========================
       KELUAR
    ========================= */
    IFNULL(SUM(
        CASE 
            WHEN m.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
            AND m.arah='KELUAR'
            THEN md.jumlah
            ELSE 0
        END
    ),0) AS keluar

FROM barang b

LEFT JOIN kelompok_barang kb2
    ON kb2.id_kelompok = b.id_kelompok

LEFT JOIN kelompok_barang kb1
    ON kb1.id_kelompok = kb2.parent_id

LEFT JOIN mutasi_detail md
    ON md.id_barang = b.id_barang

LEFT JOIN mutasi m
    ON m.id_mutasi = md.id_mutasi

LEFT JOIN jenis_mutasi jm
    ON jm.id_jenis = m.id_jenis

GROUP BY 
    kb1.nama_kelompok,
    kb2.id_kelompok,
    kb2.nama_kelompok,
    b.id_barang

ORDER BY 

-- 🔥 URUTAN KELOMPOK UTAMA (INI JANGAN DIUBAH)
CASE 
  WHEN b.nama_barang = 'Powder' THEN 2
  WHEN kb1.nama_kelompok = 'Bahan Baku' THEN 1
  WHEN kb1.nama_kelompok = 'Bahan Baku Pendukung' THEN 3
  WHEN kb1.nama_kelompok = 'Work In Progress' THEN 4
  WHEN kb1.nama_kelompok = 'Barang Jadi' THEN 5
  ELSE 99
END,

-- 🔥 SUB KELOMPOK LEVEL 1
CASE 
  WHEN kb2.nama_kelompok = 'Arang Tempurung Kelapa' THEN 1
  WHEN kb2.nama_kelompok = 'AFKIR' THEN 2
  WHEN kb2.nama_kelompok = 'Bahan Pendamping & Energi' THEN 3
  ELSE 99
END,

-- 🔥 KHUSUS BAHAN PENDUKUNG (biar tidak bentrok)
CASE 
  WHEN kb1.nama_kelompok = 'Bahan Baku Pendukung' THEN
    CASE 
      WHEN kb2.nama_kelompok = 'Inner Plastik' THEN 1
      WHEN kb2.nama_kelompok = 'Inner Box Bahan Baku Pendukung' THEN 2
      WHEN kb2.nama_kelompok = 'Master Box' THEN 3
      WHEN kb2.nama_kelompok = 'Lainnya' THEN 4
      ELSE 99
    END
  ELSE 0
END,

-- 🔥 KHUSUS WIP (INI KUNCI MASALAH KAMU)
CASE 
  WHEN kb1.nama_kelompok = 'Work In Progress' THEN
    CASE 
      WHEN kb2.nama_kelompok = 'Hasil Bongkar Oven' THEN 1
      WHEN kb2.nama_kelompok = 'Hasil Bongkar Karantina' THEN 2
      ELSE 99
    END
  ELSE 0
END,

-- 🔥 KHUSUS BARANG JADI
CASE 
  WHEN kb1.nama_kelompok = 'Barang Jadi' THEN
    CASE 
      WHEN kb2.nama_kelompok = 'Inner Plastik/Pack' THEN 1
      WHEN kb2.nama_kelompok = 'Master Box Barang Jadi' THEN 2
      WHEN kb2.nama_kelompok = 'Reject Packing' THEN 3
      WHEN kb2.nama_kelompok = 'Briket Riset' THEN 4
      ELSE 99
    END
  ELSE 0
END,

-- 🔥 TERAKHIR BARU NAMA BARANG
b.nama_barang
");

if(!$q){
    die("QUERY ERROR: " . mysqli_error($conn));
}

$qArang = mysqli_query($conn, "
SELECT 
  s.id_supplier,
  s.nama_supplier,
  s.alamat,

  /* STOK AWAL */
  IFNULL(sa.stok_awal,0) AS stok_awal,

  /* MASUK */
  IFNULL(ms.masuk,0) AS masuk,

  /* KELUAR */
  IFNULL(kl.keluar,0) AS keluar,

  (
    IFNULL(sa.stok_awal,0)
    + IFNULL(ms.masuk,0)
    - IFNULL(kl.keluar,0)
  ) AS saldo

FROM supplier s

/* =========================
   STOK AWAL
========================= */
LEFT JOIN (
  SELECT 
    md.id_supplier,

    CASE 
      /* CEK ADA STOK AWAL BULAN INI */
      WHEN SUM(
        CASE 
          WHEN m.jenis = 'AWAL'
          AND DATE_FORMAT(m.tanggal,'%Y-%m') = '$tahun-$bulan'
          THEN 1 ELSE 0
        END
      ) > 0

      THEN 
        /* PAKAI STOK AWAL */
        SUM(
          CASE 
            WHEN m.jenis = 'AWAL'
            AND DATE_FORMAT(m.tanggal,'%Y-%m') = '$tahun-$bulan'
            THEN md.jumlah
            ELSE 0
          END
        )

      ELSE 
        /* PAKAI SALDO SEBELUM BULAN */
        SUM(
          CASE 
            WHEN m.tanggal < '$tglAwal'
            AND m.arah='MASUK'
            THEN md.jumlah

            WHEN m.tanggal < '$tglAwal'
            AND m.arah='KELUAR'
            THEN -md.jumlah

            ELSE 0
          END
        )
    END AS stok_awal

  FROM mutasi m
  JOIN mutasi_detail md ON md.id_mutasi = m.id_mutasi
  JOIN barang b ON b.id_barang = md.id_barang
  JOIN kelompok_barang kb2 ON kb2.id_kelompok = b.id_kelompok

  WHERE kb2.nama_kelompok = 'Arang Tempurung Kelapa'

  GROUP BY md.id_supplier
) sa ON sa.id_supplier = s.id_supplier

/* =========================
   MASUK
========================= */
LEFT JOIN (
  SELECT 
    md.id_supplier,
    SUM(md.jumlah) AS masuk
  FROM mutasi m
  JOIN mutasi_detail md 
    ON md.id_mutasi = m.id_mutasi
  JOIN barang b ON b.id_barang = md.id_barang
  JOIN kelompok_barang kb2 ON kb2.id_kelompok = b.id_kelompok
  WHERE m.arah = 'MASUK'
    AND m.jenis != 'AWAL'
    AND kb2.nama_kelompok = 'Arang Tempurung Kelapa'
    AND m.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
  GROUP BY md.id_supplier
) ms ON ms.id_supplier = s.id_supplier

/* =========================
   KELUAR
========================= */
LEFT JOIN (
  SELECT 
    md.id_supplier,
    SUM(md.jumlah) AS keluar
  FROM mutasi m
  JOIN mutasi_detail md 
    ON md.id_mutasi = m.id_mutasi
  JOIN barang b ON b.id_barang = md.id_barang
  JOIN kelompok_barang kb2 ON kb2.id_kelompok = b.id_kelompok
  WHERE m.arah = 'KELUAR'
    AND kb2.nama_kelompok = 'Arang Tempurung Kelapa'
    AND m.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
  GROUP BY md.id_supplier
) kl ON kl.id_supplier = s.id_supplier

ORDER BY s.id_supplier
");

$qPowder = mysqli_query($conn, "
SELECT 
  s.id_supplier,
  s.nama_supplier,
  s.alamat,

  /* =========================
     STOK AWAL (MUTASI)
  ========================= */
  IFNULL(sa.stok_awal,0) AS stok_awal,

  /* =========================
     MASUK TOTAL
     = MUTASI + ATP
  ========================= */
  (
    IFNULL(ms.masuk,0)
    + IFNULL(atp.masuk,0)
  ) AS masuk,

  /* =========================
     KELUAR TOTAL
     = MUTASI + MIXER
  ========================= */
  (
    IFNULL(kl.keluar,0)
    + IFNULL(mx.keluar,0)
  ) AS keluar,

  /* =========================
     SALDO
  ========================= */
  (
    IFNULL(sa.stok_awal,0)
    + IFNULL(ms.masuk,0)
    + IFNULL(atp.masuk,0)
    - IFNULL(kl.keluar,0)
    - IFNULL(mx.keluar,0)
  ) AS saldo

FROM supplier s

/* =========================
   STOK AWAL (MUTASI)
========================= */
LEFT JOIN (
  SELECT 
    md.id_supplier,

    CASE 
      /* CEK ADA STOK AWAL */
      WHEN SUM(
        CASE 
          WHEN (jm.tipe = 'STOKAWAL' OR m.jenis='AWAL')
          AND DATE_FORMAT(m.tanggal,'%Y-%m') = '$tahun-$bulan'
          THEN 1 ELSE 0
        END
      ) > 0

      THEN 
        /* PAKAI STOK AWAL */
        SUM(
          CASE 
            WHEN (jm.tipe = 'STOKAWAL' OR m.jenis='AWAL')
            AND DATE_FORMAT(m.tanggal,'%Y-%m') = '$tahun-$bulan'
            THEN md.jumlah
            ELSE 0
          END
        )

      ELSE 
        /* PAKAI SALDO SEBELUMNYA */
        SUM(
          CASE 
            WHEN m.tanggal < '$tglAwal'
            AND m.arah='MASUK'
            THEN md.jumlah

            WHEN m.tanggal < '$tglAwal'
            AND m.arah='KELUAR'
            THEN -md.jumlah

            ELSE 0
          END
        )
    END AS stok_awal

  FROM mutasi m
  JOIN mutasi_detail md ON md.id_mutasi = m.id_mutasi
  JOIN barang b ON b.id_barang = md.id_barang
  LEFT JOIN jenis_mutasi jm ON jm.id_jenis = m.id_jenis

  WHERE b.nama_barang IN ('Powder','Repro Briket')

  GROUP BY md.id_supplier
) sa ON sa.id_supplier = s.id_supplier

/* =========================
   MASUK (MUTASI)
========================= */
LEFT JOIN (
  SELECT 
    md.id_supplier,
    SUM(md.jumlah) AS masuk
  FROM mutasi m
  JOIN mutasi_detail md ON md.id_mutasi = m.id_mutasi
  JOIN barang b ON b.id_barang = md.id_barang
  LEFT JOIN jenis_mutasi jm ON jm.id_jenis = m.id_jenis
  WHERE m.arah = 'MASUK'
    AND (jm.tipe IS NULL OR jm.tipe != 'STOKAWAL')
    AND b.nama_barang IN ('Powder','Repro Briket')
    AND m.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
  GROUP BY md.id_supplier
) ms ON ms.id_supplier = s.id_supplier

/* =========================
   KELUAR (MUTASI)
========================= */
LEFT JOIN (
  SELECT 
    md.id_supplier,
    SUM(md.jumlah) AS keluar
  FROM mutasi m
  JOIN mutasi_detail md ON md.id_mutasi = m.id_mutasi
  JOIN barang b ON b.id_barang = md.id_barang
  WHERE m.arah = 'KELUAR'
    AND b.nama_barang IN ('Powder','Repro Briket')
    AND m.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
  GROUP BY md.id_supplier
) kl ON kl.id_supplier = s.id_supplier

/* =========================
   MASUK (ATP)
========================= */
LEFT JOIN (
  SELECT 
    a.id_supplier,
    SUM(a.atp) AS masuk
  FROM at_detail a
  WHERE a.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
  GROUP BY a.id_supplier
) atp ON atp.id_supplier = s.id_supplier

/* =========================
   KELUAR (MIXER)
========================= */
LEFT JOIN (
  SELECT 
    p.id_supplier,
    SUM(pd.mixer) AS keluar
  FROM produksi p
  JOIN produksi_detail pd ON pd.id_produksi = p.id_produksi
  WHERE p.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
  GROUP BY p.id_supplier
) mx ON mx.id_supplier = s.id_supplier

ORDER BY s.id_supplier
");

$qRepro = mysqli_query($conn, "
SELECT 

CASE 
  /* CEK ADA STOK AWAL BULAN INI */
  WHEN SUM(
    CASE 
      WHEN m.jenis='AWAL'
      AND DATE_FORMAT(m.tanggal,'%Y-%m') = '$tahun-$bulan'
      THEN 1 ELSE 0
    END
  ) > 0

  THEN 
    /* PAKAI STOK AWAL */
    SUM(
      CASE 
        WHEN m.jenis='AWAL'
        AND DATE_FORMAT(m.tanggal,'%Y-%m') = '$tahun-$bulan'
        THEN md.jumlah
        ELSE 0
      END
    )

  ELSE 
    /* PAKAI SALDO SEBELUM BULAN */
    SUM(
      CASE 
        WHEN m.tanggal < '$tglAwal'
        AND m.arah='MASUK'
        THEN md.jumlah

        WHEN m.tanggal < '$tglAwal'
        AND m.arah='KELUAR'
        THEN -md.jumlah

        ELSE 0
      END
    )

END AS stok_awal

FROM mutasi m
JOIN mutasi_detail md ON md.id_mutasi = m.id_mutasi
JOIN barang b ON b.id_barang = md.id_barang

WHERE b.nama_barang='Repro Briket'
");

// ================================
// HELPER
// ================================
function romawi($angka){
    $map=[1=>"I",2=>"II",3=>"III",4=>"IV",5=>"V"];
    return $map[$angka] ?? $angka;
}

$kelompokMap = [
  'Bahan Baku'=>'A',
  'Powder'=>'B',
  'Bahan Baku Pendukung'=>'C',
  'Work In Progress'=>'D',
  'Barang Jadi'=>'E'
];

// ================================
// INIT
// ================================
$urutanKelompok = '';
$parentSebelumnya = '';
$urutanSub = 0;
$no = 1;

$powderSudah = false;
$arangSudah = false;

// total WIP
$totalAwalWIP=0;
$totalMasukWIP=0;
$totalKeluarWIP=0;
$totalAkhirWIP=0;

$lastParent = '';
$lastKelompok = '';
// ================================
// START BUFFER HTML
// ================================
ob_start();
?>

<style>
/* 1. Nama Perusahaan */
.nama-perusahaan {
  font-family: "Bernard MT Condensed", serif;
  font-size: 24px;
  font-weight: bold;
  letter-spacing: 2px;
}

/* 2. Alamat */
.alamat-perusahaan {
  font-family: "Monotype Corsiva", cursive;
  font-size: 14px;
  font-style: italic;
}

/* 3. Bagian Gudang */
.bagian-gudang {
  font-family: "Trebuchet MS", sans-serif;
  font-size: 10px;
  font-weight: bold;
}

/* 4. Judul Laporan */
.judul-laporan {
  font-family: "Britannic Bold", sans-serif;
  font-size: 18px;
  font-weight: bold;
  text-transform: uppercase;
}

/* 5. Isi Tabel */
.isi-tabel {
  font-family: "Calibri", sans-serif;
  font-size: 12px;
}

/* Header tabel */
.header-tabel {
  font-family: "Calibri", sans-serif;
  font-size: 12px;
  font-weight: bold;
  text-align: center;
  background: #f2e1d0;
}
</style>

<?php
$path = __DIR__ . '/logo.jpg';

if(file_exists($path)){
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
} else {
    $base64 = '';
}
?>

<table border="1" width="100%" cellspacing="0" cellpadding="5">

<tr>
  <!-- LOGO -->
  <td width="15%" align="center" rowspan="2">
    <img src="<?= $base64 ?>" width="100">
  </td>

  <!-- NAMA PERUSAHAAN -->
  <td align="center">
    <b style="font-size:24px; letter-spacing:2px;" class="nama-perusahaan">
      PT DIAN CIPTA SEJAHTERA
    </b>
  </td>

  <!-- BAGIAN -->
  <td width="15%" align="center" rowspan="2">
    <i class="bagian-gudang" style="font-size:24px;">Bagian</i><br>
    <b style="font-size:24px;" class="bagian-gudang">GUDANG</b>
  </td>
</tr>

<tr>
  <!-- ALAMAT -->
  <td align="center">
    <i class="alamat-perusahaan">
      Jl. Bantul Km 6,5 No.158 Nyemengan, Tirtonirmolo,
      Kasihan, Bantul, Yogyakarta
    </i>
  </td>
</tr>

</table>

<table border="1" width="100%" cellpadding="5" cellspacing="0">
<tr>
  <th colspan="9" style="font-size:16px;" class="judul-laporan">LAPORAN STOK AKHIR BULAN</th>
</tr>
<tr>
  <th colspan="9">Bulan: <?= $judulBulan ?></th>
</tr>

<tr style="background:#ddd;font-weight:bold" class="header-tabel">
  <th>No</th>
  <th colspan="2">Kelompok Bahan Baku</th>
  <th>Stok Awal</th>
  <th>Masuk</th>
  <th>Keluar</th>
  <th>Stok Akhir</th>
  <th>Satuan</th>
  <th>Keterangan</th>
</tr>

<?php while($r = mysqli_fetch_assoc($q)): 

$stokAkhir = $r['stok_awal'] + $r['masuk'] - $r['keluar'];

// skip repro
if(strtolower($r['nama_barang'])=='repro briket') continue;

// mapping powder
$kelompokFix = $r['nama_kelompok'];
if(strtolower($r['nama_barang'])=='powder'){
  $kelompokFix = 'Powder';
}

$currentParent = strtolower(trim($r['parent_barang']));
?>
<?php if($urutanKelompok != $kelompokFix): ?>

    <!-- PRINT TOTAL WIP SEBELUM PINDAH -->
    <?php
    if(strtolower($urutanKelompok)=='work in progress' 
       && in_array($lastParent, ['hasil bongkar oven','hasil bongkar karantina'])){
    ?>
    <tr style="background:#fff3cd;font-weight:bold" class="isi-tabel">
      <td></td><td></td>
      <td>JUMLAH <?= ucwords($lastParent) ?></td>
      <td align="right"><?= number_format($totalAwalWIP,2) ?></td>
      <td align="right"><?= number_format($totalMasukWIP,0) ?></td>
      <td align="right"><?= number_format($totalKeluarWIP,0) ?></td>
      <td align="right"><?= number_format($totalAkhirWIP,2) ?></td>
      <td>Kg</td><td></td>
    </tr>
    <?php 
      $totalAwalWIP=$totalMasukWIP=$totalKeluarWIP=$totalAkhirWIP=0;
    } 
    ?>

    <!-- HEADER KELOMPOK -->
    <tr style="background:#dcdcdc;font-weight:bold" class="isi-tabel">
      <td colspan="9">
        <?= $kelompokMap[$kelompokFix] ?>. <?= $kelompokFix ?>
      </td>
    </tr>

<?php 
    $urutanKelompok = $kelompokFix;
    $parentSebelumnya = '';
    $urutanSub = 0;
    $lastParent = '';
endif; 
?>

<?php
// ================= DETEKSI PINDAH PARENT (DALAM WIP) =================
if(
    $currentParent != $lastParent &&
    strtolower($kelompokFix) == 'work in progress'
){
    if(in_array($lastParent, ['hasil bongkar oven','hasil bongkar karantina'])){
?>
<tr style="background:#fff3cd;font-weight:bold" class="isi-tabel">
  <td></td><td></td>
  <td>JUMLAH <?= ucwords($lastParent) ?></td>
  <td align="right"><?= number_format($totalAwalWIP,2) ?></td>
  <td align="right"><?= number_format($totalMasukWIP,0) ?></td>
  <td align="right"><?= number_format($totalKeluarWIP,0) ?></td>
  <td align="right"><?= number_format($totalAkhirWIP,2) ?></td>
  <td>Kg</td><td></td>
</tr>
<?php
    }
    $totalAwalWIP=$totalMasukWIP=$totalKeluarWIP=$totalAkhirWIP=0;
}

// ================= SUB KELOMPOK =================
$currentParent = strtolower(trim($r['parent_barang']));
$prevParent    = strtolower(trim($parentSebelumnya));

if($prevParent !== $currentParent && strtolower($kelompokFix) != 'powder'):
$urutanSub++;
$no = 1;
?>

<tr style="background:#efefef;font-weight:bold" class="isi-tabel">
  <td></td>
  <td colspan="2"><?= romawi($urutanSub) ?>. <?= $r['parent_barang'] ?></td>
  <td colspan="6"></td>
</tr>

<?php
$parentSebelumnya = $currentParent;
endif;

// ================= ARANG =================
if($currentParent=='arang tempurung kelapa' && !$arangSudah):

$totalAwal=0;$totalMasuk=0;$totalKeluar=0;$totalAkhir=0;
$no=1;

while($ar=mysqli_fetch_assoc($qArang)):
$akhir=$ar['stok_awal']+$ar['masuk']-$ar['keluar'];
$totalAwal+=$ar['stok_awal'];
$totalMasuk+=$ar['masuk'];
$totalKeluar+=$ar['keluar'];
$totalAkhir+=$akhir;
?>
<tr class="isi-tabel">
<td></td><td><?= $no++ ?>. </td>
<td> <?= $ar['nama_supplier'] ?></td>
<td align="right"><?= number_format($ar['stok_awal'],2) ?></td>
<td align="right"><?= number_format($ar['masuk'],0) ?></td>
<td align="right"><?= number_format($ar['keluar'],0) ?></td>
<td align="right"><?= number_format($akhir,2) ?></td>
<td>Kg</td><td></td>
</tr>
<?php endwhile; ?>

<tr style="background:#fff3cd;font-weight:bold" class="isi-tabel">
<td></td><td></td>
<td>JUMLAH ARANG</td>
<td align="right"><?= number_format($totalAwal,2) ?></td>
<td align="right"><?= number_format($totalMasuk,0) ?></td>
<td align="right"><?= number_format($totalKeluar,0) ?></td>
<td align="right"><?= number_format($totalAkhir,2) ?></td>
<td>Kg</td><td></td>
</tr>

<?php
$arangSudah=true;
continue;
endif;

// ================= WIP TOTAL =================
if(
    strtolower($kelompokFix) == 'work in progress' &&
    in_array($currentParent, ['hasil bongkar oven','hasil bongkar karantina'])
){
$totalAwalWIP+=$r['stok_awal'];
$totalMasukWIP+=$r['masuk'];
$totalKeluarWIP+=$r['keluar'];
$totalAkhirWIP+=$stokAkhir;
}
if($kelompokFix == 'Powder' && !$powderSudah):

    $no = 1;
    $totalAwal=0;$totalMasuk=0;$totalKeluar=0;$totalAkhir=0;

    while($p = mysqli_fetch_assoc($qPowder)):

        $akhir = $p['stok_awal'] + $p['masuk'] - $p['keluar'];

        $totalAwal += $p['stok_awal'];
        $totalMasuk += $p['masuk'];
        $totalKeluar += $p['keluar'];
        $totalAkhir += $akhir;
?>
<tr class="isi-tabel">
<td></td><td><?= $no++ ?>. </td>
<td><?= $p['nama_supplier'] ?></td>
<td align="right"><?= number_format($p['stok_awal'],2) ?></td>
<td align="right"><?= number_format($p['masuk'],0) ?></td>
<td align="right"><?= number_format($p['keluar'],0) ?></td>
<td align="right"><?= number_format($akhir,2) ?></td>
<td>Kg</td><td></td>
</tr>
<?php endwhile; ?>

<tr style="background:#fff3cd;font-weight:bold" class="isi-tabel">
<td></td><td></td>
<td>JUMLAH POWDER</td>
<td align="right"><?= number_format($totalAwal,2) ?></td>
<td align="right"><?= number_format($totalMasuk,0) ?></td>
<td align="right"><?= number_format($totalKeluar,0) ?></td>
<td align="right"><?= number_format($totalAkhir,2) ?></td>
<td>Kg</td><td></td>
</tr>

<?php
$powderSudah = true;
continue;
endif;
// ================= DATA NORMAL =================
?>
<tr class="isi-tabel">
<td></td>
<td><?= $no++ ?>. </td>
<td> <?= $r['nama_barang'] ?></td>
<td align="right"><?= number_format($r['stok_awal'],2) ?></td>
<td align="right"><?= number_format($r['masuk'],0) ?></td>
<td align="right"><?= number_format($r['keluar'],0) ?></td>
<td align="right"><?= number_format($stokAkhir,2) ?></td>
<td><?= $r['satuan'] ?></td>
<td></td>
</tr>

<?php
$lastParent=$currentParent;
$lastKelompok=$kelompokFix;
endwhile;

// ================= FINAL PRINT JIKA WIP TERAKHIR =================
if(
    strtolower($lastKelompok) == 'work in progress' &&
    in_array($lastParent, ['hasil bongkar oven','hasil bongkar karantina'])
){
?>
<tr style="background:#fff3cd;font-weight:bold" class="isi-tabel">
  <td></td><td></td>
  <td>JUMLAH <?= ucwords($lastParent) ?></td>
  <td align="right"><?= number_format($totalAwalWIP,2) ?></td>
  <td align="right"><?= number_format($totalMasukWIP,0) ?></td>
  <td align="right"><?= number_format($totalKeluarWIP,0) ?></td>
  <td align="right"><?= number_format($totalAkhirWIP,2) ?></td>
  <td>Kg</td><td></td>
</tr>
<?php
}
?>

</table>

<?php
$html = ob_get_clean();

// ================================
// DOMPDF CONFIG
// ================================
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'potrait');
$dompdf->render();

// ================================
// OUTPUT PDF
// ================================
$dompdf->stream("Kartu_Stok_$bulan-$tahun.pdf", ["Attachment"=>0]);