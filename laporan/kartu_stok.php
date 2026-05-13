<?php
require '../config/init.php';

// ================================
// FILTER BULAN & TAHUN
// ================================
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$bulan = str_pad($bulan, 2, "0", STR_PAD_LEFT);

$tglAwal  = "$tahun-$bulan-01";
$tglAkhir = date("Y-m-t", strtotime($tglAwal));

// ================================
// QUERY LAPORAN
// ================================
$q = mysqli_query($conn, "
SELECT
    b.id_barang,
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

WHERE TRIM(LOWER(b.nama_barang)) != 'Repro Briket'

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
  AND md.id_supplier IS NOT NULL

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
    AND md.id_supplier IS NOT NULL
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
    AND md.id_supplier IS NOT NULL
    AND m.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
  GROUP BY md.id_supplier
) kl ON kl.id_supplier = s.id_supplier

/* =========================
   HANYA SUPPLIER EXTERNAL
========================= */
WHERE s.tipe = 'external'

ORDER BY COALESCE(s.id_supplier,'') ASC

");

$qPowder = mysqli_query($conn, "
SELECT 
  s.id_supplier,
  s.nama_supplier,
  s.alamat,

  /* =========================
     STOK AWAL (MUTASI + ATP - MIXER)
  ========================= */
  (
    IFNULL(sa.stok_awal,0)
    + IFNULL(atp_awal.atp_sebelum,0)
    - IFNULL(mx_awal.mixer_sebelum,0)
  ) AS stok_awal,

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
    + IFNULL(atp_awal.atp_sebelum,0)
    - IFNULL(mx_awal.mixer_sebelum,0)
    + IFNULL(ms.masuk,0)
    + IFNULL(atp.masuk,0)
    - IFNULL(kl.keluar,0)
    - IFNULL(mx.keluar,0)
  ) AS saldo

FROM supplier s

/* =========================
   STOK AWAL (MUTASI + ATP + MIXER)
========================= */
LEFT JOIN (
  SELECT 
    md.id_supplier,

    CASE 
      /* CEK ADA STOK AWAL BULAN INI DI MUTASI */
      WHEN SUM(
        CASE 
          WHEN (jm.tipe = 'STOKAWAL' OR m.jenis='AWAL')
          AND DATE_FORMAT(m.tanggal,'%Y-%m') = '$tahun-$bulan'
          THEN 1 ELSE 0
        END
      ) > 0

      THEN 
        /* PAKAI STOK AWAL DARI MUTASI */
        SUM(
          CASE 
            WHEN (jm.tipe = 'STOKAWAL' OR m.jenis='AWAL')
            AND DATE_FORMAT(m.tanggal,'%Y-%m') = '$tahun-$bulan'
            THEN md.jumlah
            ELSE 0
          END
        )

      ELSE 
        /* PAKAI SALDO SEBELUMNYA = MUTASI + ATP - MIXER */
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
    
    md.id_supplier AS join_id_supplier

  FROM mutasi m
  JOIN mutasi_detail md ON md.id_mutasi = m.id_mutasi
  JOIN barang b ON b.id_barang = md.id_barang
  LEFT JOIN jenis_mutasi jm ON jm.id_jenis = m.id_jenis

  WHERE b.nama_barang = 'Powder'

  GROUP BY md.id_supplier
) sa ON sa.id_supplier = s.id_supplier

/* =========================
   STOK AWAL TAMBAHAN ATP (sebelum bulan)
========================= */
LEFT JOIN (
  SELECT 
    a.id_supplier,
    SUM(a.atp) AS atp_sebelum
  FROM at_detail a
  WHERE a.tanggal < '$tglAwal'
  GROUP BY a.id_supplier
) atp_awal ON atp_awal.id_supplier = s.id_supplier

/* =========================
   STOK AWAL KURANG MIXER (sebelum bulan)
========================= */
LEFT JOIN (
  SELECT 
    p.id_supplier,
    SUM(pd.mixer) AS mixer_sebelum
  FROM produksi p
  JOIN produksi_detail pd ON pd.id_produksi = p.id_produksi
  WHERE p.tanggal < '$tglAwal'
  GROUP BY p.id_supplier
) mx_awal ON mx_awal.id_supplier = s.id_supplier

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
    AND b.nama_barang = 'Powder'
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
    AND b.nama_barang = 'Powder'
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
WHERE s.tipe = 'external'
ORDER BY s.id_supplier
");

$qReproBriket = mysqli_query($conn, " SELECT COALESCE(ad.id_supplier, 0) as id_supplier, 
COALESCE(s.nama_supplier, 'REPRO BRIKET') as nama_supplier, 
COALESCE(s.alamat, '') as alamat, 
COALESCE(SUM( 
CASE 
WHEN bm.tanggal < '$tglAwal' 
THEN ((bm.krg * 25) + bm.add_kg) 
ELSE 0 END ), 0) 
AS stok_awal, 
COALESCE(SUM(
    CASE
        WHEN bm.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
        AND bm.jenis = 'REPRO'
        THEN ((bm.krg * 25) + bm.add_kg)
        ELSE 0
    END
),0) AS masuk,
COALESCE(SUM(
    CASE
        WHEN ad.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
        THEN (
            ad.sortir +
            ad.ma +
            ad.aa +
            ad.b_mentah +
            ad.air +
            ad.atp
        )
        ELSE 0
    END
),0) AS keluar
FROM bkbriket_mutasi bm 
JOIN bkbriket b ON b.id_bk = bm.id_bk 
LEFT JOIN at_detail ad ON ad.id_mutasi_detail = bm.id_mutasi 
LEFT JOIN supplier s ON s.id_supplier = ad.id_supplier 
WHERE bm.jenis = 'REPRO' AND b.status = 'KARANTINA' 
GROUP BY ad.id_supplier 
ORDER BY ad.id_supplier ");

$wipData = [];
$qWIP = mysqli_query($conn, "
SELECT 
  b.id_barang_briket AS id_barang,
  br.nama_barang,
  b.status,

  /* STOK AWAL (SALDO SEBELUM BULAN) */
  COALESCE(
    SUM(
      CASE 
        WHEN bo.tanggal_bongkar < '$tglAwal'
        THEN (bo.krg * 25) + bo.add_kg
        ELSE 0
      END
    ),
    0
  ) AS stok_awal,

    /* MASUK (BONGKAR OVEN) */
    SUM(
      CASE 
        WHEN bo.tanggal_bongkar BETWEEN '$tglAwal' AND '$tglAkhir'
        THEN (bo.krg * 25) + bo.add_kg
        ELSE 0
      END
    ) AS masuk,

  /* KELUAR (MUTASI) */
  (
    SELECT COALESCE(SUM((m.krg*25)+m.add_kg),0)
    FROM bkbriket_mutasi m
    WHERE m.id_bk = b.id_bk
    AND m.tanggal BETWEEN '$tglAwal' AND '$tglAkhir'
  ) AS keluar

FROM bkbriket b
JOIN bkbriket_bongkar bo ON bo.id_bk = b.id_bk
JOIN barang br ON br.id_barang = b.id_barang_briket

WHERE bo.tanggal_bongkar <= '$tglAkhir'

GROUP BY b.id_bk
");

// ================================
// UNTUK LABEL BULAN
// ================================
$namaBulan = [
  "01"=>"Januari","02"=>"Februari","03"=>"Maret","04"=>"April",
  "05"=>"Mei","06"=>"Juni","07"=>"Juli","08"=>"Agustus",
  "09"=>"September","10"=>"Oktober","11"=>"November","12"=>"Desember"
];

$judulBulan = ($namaBulan[$bulan] ?? $bulan) . " " . $tahun;

?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Kartu Stok Bulanan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- kalau kamu sudah pakai bootstrap di template utama, ini bisa dihapus -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bbootstrap 4 -->
  <link rel="stylesheet" href="../plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="../plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style --> 
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="../plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker --> 
  <link rel="stylesheet" href="../plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="../plugins/summernote/summernote-bs4.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

  <style>
    body { background:#f5f5f5; }
    .card { border-radius:12px; }
    table th, table td { vertical-align: middle !important; }
    .header-kelompok td {
      background: #f0f0f0 !important;
      font-weight: bold;
    }
    .angka { text-align:right; }
  </style>
</head>

<body>

<div class="container mt-4 mb-5">

  <div class="card shadow-sm">
    <div class="card-body">
<a href="../index.php" 
            class="btn btn-info btn-sm">Back</a>
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h4 class="mb-0">Kartu Stok Bulanan</h4>
          <small class="text-muted">Periode: <b><?= $judulBulan ?></b></small>
        </div>
      </div>

      <hr>

<form method="GET" class="row g-2 mb-4">

  <div class="col-md-3">
    <label>Bulan</label>
    <select name="bulan" class="form-control">
      <?php foreach($namaBulan as $k => $v): ?>
        <option value="<?= $k ?>" <?= ($k==$bulan?'selected':'') ?>>
          <?= $v ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-3">
    <label>Tahun</label>
    <select name="tahun" class="form-control">
      <?php
        $thnNow = date('Y');
        for($t=$thnNow-3;$t<=$thnNow+1;$t++):?>
        <option value="<?= $t ?>" <?= ($t==$tahun?'selected':'') ?>>
          <?= $t ?>
        </option>
      <?php endfor; ?>
    </select>
  </div>

  <div class="col-md-6 d-flex align-items-end gap-2">

    <button type="submit" class="btn btn-primary mr-2">
      Tampilkan
    </button>

    <a href="kartu_stok_excel.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>"
       class="btn btn-success mr-2">
      Export Excel
    </a>

    <a href="kartu_stok_pdf.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>"
       class="btn btn-danger">
      Export PDF
    </a>

  </div>

</form>


      <!-- TABEL -->
      <div class="table-responsive">
<table class="table table-bordered table-striped table-sm">
  <thead class="table-dark">
    <tr>
      <th style="width:40px;">No</th>
      <th colspan="2">Kelompok Bahan Baku</th>
      <th class="angka">Stok Awal</th>
      <th class="angka">Masuk</th>
      <th class="angka">Keluar</th>
      <th class="angka">Stok Akhir</th>
      <th>Satuan</th>
      <th>Keterangan</th>
    </tr>
  </thead>
  <tbody>

<?php
$kelompokMap = [
    'Bahan Baku' => 'A',
    'Powder' => 'B',
    'Bahan Baku Pendukung' => 'C',
    'Work In Progress' => 'D',
    'Barang Jadi' => 'E'
];

$urutanKelompok = '';
$urutanSub = 0;
$no = 1;

function romawi($angka){
    $map = [
        1=>"I",2=>"II",3=>"III",4=>"IV",5=>"V",
        6=>"VI",7=>"VII",8=>"VIII",9=>"IX",10=>"X"
    ];
    return $map[$angka] ?? $angka;
}

$powderSudahTampil = false;
$arangSudahTampil  = false;

// ======================
// AMBIL DATA POWDER
// ======================
$powderData = [];
while($row = mysqli_fetch_assoc($qPowder)){
    $powderData[] = $row;
}

// ======================
// AMBIL DATA WIP
// ======================
while($w = mysqli_fetch_assoc($qWIP)){
    $id = $w['id_barang'];

    if(!isset($wipData[$id])){
        $wipData[$id] = [
            'stok_awal' => 0,
            'masuk' => 0,
            'keluar' => 0
        ];
    }

    $wipData[$id]['stok_awal'] += $w['stok_awal'] ?? 0;
    $wipData[$id]['masuk']     += $w['masuk'] ?? 0;
    $wipData[$id]['keluar']    += $w['keluar'] ?? 0;
}

// ======================
// TOTAL WIP
// ======================
$totalStokAwalWIP = 0;
$totalMasukWIP    = 0;
$totalKeluarWIP   = 0;
$totalAkhirWIP    = 0;

$lastParent = '';
$parentSebelumnya = '';
$lastKelompok = '';
while($r = mysqli_fetch_assoc($q)){

    $stokAkhir = $r['stok_awal'] + $r['masuk'] - $r['keluar'];

    $kelompokFix = $r['nama_kelompok'];

    if(
        strtolower(trim($r['nama_barang'])) == 'powder'
    ){
        $kelompokFix = 'Powder';
    }

    $currentParent = strtolower(trim($r['parent_barang']));
    
    if($lastParent == ''){
        $lastParent = $currentParent;
    }

// ======================
// DETEKSI PINDAH PARENT (DALAM WIP)
// ======================
if(
    $currentParent != $lastParent &&
    strtolower($kelompokFix) == 'work in progress'
){
    if(in_array($lastParent, ['hasil bongkar oven','hasil bongkar karantina'])){
        echo "
        <tr style='background:#fff3cd;font-weight:bold'>
            <td></td>
            <td></td>
            <td>JUMLAH ".ucwords($lastParent)."</td>
            <td class='angka'>".number_format($totalStokAwalWIP,2)."</td>
            <td class='angka'>".number_format($totalMasukWIP,0)."</td>
            <td class='angka'>".number_format($totalKeluarWIP,0)."</td>
            <td class='angka'>".number_format($totalAkhirWIP,2)."</td>
            <td>Kg</td>
            <td></td>
        </tr>
        ";
    }

    // reset total
    $totalStokAwalWIP = 0;
    $totalMasukWIP    = 0;
    $totalKeluarWIP   = 0;
    $totalAkhirWIP    = 0;
}
if($urutanKelompok != $kelompokFix){

    // 🔥 CETAK TOTAL WIP SEBELUM PINDAH KE KELOMPOK LAIN
    if(strtolower($urutanKelompok) == 'work in progress' && in_array($lastParent, ['hasil bongkar oven','hasil bongkar karantina'])){
        echo "
        <tr style='background:#fff3cd;font-weight:bold'>
            <td></td>
            <td></td>
            <td>JUMLAH ".ucwords($lastParent)."</td>
            <td class='angka'>".number_format($totalStokAwalWIP,2)."</td>
            <td class='angka'>".number_format($totalMasukWIP,0)."</td>
            <td class='angka'>".number_format($totalKeluarWIP,0)."</td>
            <td class='angka'>".number_format($totalAkhirWIP,2)."</td>
            <td>Kg</td>
            <td></td>
        </tr>
        ";
    }

    echo "
    <tr style='background:#dcdcdc;font-weight:bold'>
        <td colspan='10'>".$kelompokMap[$kelompokFix].". ".$kelompokFix."</td>
    </tr>
    ";

    $urutanKelompok = $kelompokFix;
    $urutanSub = 0;
    $parentSebelumnya = '';
}

    // ======================
    // POWDER
    // ======================
    if($kelompokFix == 'Powder'){

        if(!$powderSudahTampil){

            $totalStokAwalPowder = 0;
            $totalMasukPowder    = 0;
            $totalKeluarPowder   = 0;
            $totalAkhirPowder    = 0;

            $no = 1;

            foreach($powderData as $p){

                $stokAkhir = $p['stok_awal'] + $p['masuk'] - $p['keluar'];

                $totalStokAwalPowder += $p['stok_awal'];
                $totalMasukPowder    += $p['masuk'];
                $totalKeluarPowder   += $p['keluar'];
                $totalAkhirPowder    += $stokAkhir;

                echo "
                <tr>
                    <td></td>
                    <td>".$no.".</td>
                    <td> ".$p['nama_supplier']." - ".$p['alamat']."</td>
                    <td class='angka'>".number_format($p['stok_awal'],2)."</td>
                    <td class='angka'>".number_format($p['masuk'],0)."</td>
                    <td class='angka'>".number_format($p['keluar'],0)."</td>
                    <td class='angka'>".number_format($stokAkhir,2)."</td>
                    <td>Kg</td>
                    <td></td>
                </tr>
                ";

                $no++;
            }

            // REPRO BRIKET PER SUPPLIER
            $reproData = mysqli_fetch_assoc($qReproBriket);

if($reproData){
                $stokAkhirRepro = $reproData['stok_awal'] + $reproData['masuk'] - $reproData['keluar'];

                $totalStokAwalPowder += $reproData['stok_awal'];
                $totalMasukPowder    += $reproData['masuk'];
                $totalKeluarPowder   += $reproData['keluar'];
                $totalAkhirPowder    += $stokAkhirRepro;

                echo "
                <tr>
                    <td></td>
                    <td>".$no.".</td>
                    <td>".$reproData['nama_supplier']."</td>
                    <td class='angka'>".number_format($reproData['stok_awal'],2)."</td>
                    <td class='angka'>".number_format($reproData['masuk'],0)."</td>
                    <td class='angka'>".number_format($reproData['keluar'],0)."</td>
                    <td class='angka'>".number_format($stokAkhirRepro,2)."</td>
                    <td>Kg</td>
                    <td></td>
                </tr>
                ";

                $no++;
            }

            echo "
            <tr style='background:#fff3cd;font-weight:bold'>
                <td></td>
                <td></td>
                <td>JUMLAH POWDER</td>
                <td class='angka'>".number_format($totalStokAwalPowder,2)."</td>
                <td class='angka'>".number_format($totalMasukPowder,0)."</td>
                <td class='angka'>".number_format($totalKeluarPowder,0)."</td>
                <td class='angka'>".number_format($totalAkhirPowder,2)."</td>
                <td>Kg</td>
                <td></td>
            </tr>
            ";

            $powderSudahTampil = true;
        }

        continue;
    }

    // ======================
    // HEADER SUB
    // ======================
    if($parentSebelumnya != $r['parent_barang']){

        $urutanSub++;

        echo "
        <tr style='background:#efefef;font-weight:bold'>
            <td></td>
            <td colspan='2'>".romawi($urutanSub).". ".$r['parent_barang']."</td>
            <td colspan='7'></td>
        </tr>
        ";

        $parentSebelumnya = $r['parent_barang'];
        $no = 1;
    }

    // ======================
    // ARANG
    // ======================
    if(strtolower(trim($r['parent_barang'])) == 'arang tempurung kelapa'){

        if($arangSudahTampil){
            continue;
        }

        mysqli_data_seek($qArang, 0);

        $no = 1;

        $totalStokAwal = 0;
        $totalMasuk    = 0;
        $totalKeluar   = 0;
        $totalAkhir    = 0;

        while($ar = mysqli_fetch_assoc($qArang)){

            $stokAkhir = $ar['stok_awal'] + $ar['masuk'] - $ar['keluar'];

            $totalStokAwal += $ar['stok_awal'];
            $totalMasuk    += $ar['masuk'];
            $totalKeluar   += $ar['keluar'];
            $totalAkhir    += $stokAkhir;

            echo "
            <tr>
                <td></td>
                <td>".$no.".</td>
                <td>".$ar['nama_supplier']." - ".$ar['alamat']."</td>
                <td class='angka'>".number_format($ar['stok_awal'],2)."</td>
                <td class='angka'>".number_format($ar['masuk'],0)."</td>
                <td class='angka'>".number_format($ar['keluar'],0)."</td>
                <td class='angka'>".number_format($stokAkhir,2)."</td>
                <td>Kg</td>
                <td></td>
            </tr>
            ";

            $no++;
        }

        echo "
        <tr style='background:#fff3cd;font-weight:bold'>
            <td></td>
            <td></td>
            <td>JUMLAH ARANG TEMPURUNG</td>
            <td class='angka'>".number_format($totalStokAwal,2)."</td>
            <td class='angka'>".number_format($totalMasuk,0)."</td>
            <td class='angka'>".number_format($totalKeluar,0)."</td>
            <td class='angka'>".number_format($totalAkhir,2)."</td>
            <td>Kg</td>
            <td></td>
        </tr>
        ";

        $arangSudahTampil = true;
        continue;
    }

    // ======================
    // AKUMULASI WIP + TAMBAH DATA WIP
    // ======================
    if(
        strtolower($kelompokFix) == 'work in progress' &&
        in_array($currentParent, ['hasil bongkar oven','hasil bongkar karantina'])
    ){
        $idBarang = $r['id_barang'];

        // Tambahkan data WIP jika ada
        if(isset($wipData[$idBarang])){
            $stokAkhir = ($r['stok_awal'] + $wipData[$idBarang]['stok_awal']) + 
                         ($r['masuk'] + $wipData[$idBarang]['masuk']) - 
                         ($r['keluar'] + $wipData[$idBarang]['keluar']);
            
            $totalStokAwalWIP += $r['stok_awal'] + $wipData[$idBarang]['stok_awal'];
            $totalMasukWIP    += $r['masuk'] + $wipData[$idBarang]['masuk'];
            $totalKeluarWIP   += $r['keluar'] + $wipData[$idBarang]['keluar'];
            $totalAkhirWIP    += $stokAkhir;
        } else {
            $totalStokAwalWIP += $r['stok_awal'];
            $totalMasukWIP    += $r['masuk'];
            $totalKeluarWIP   += $r['keluar'];
            $totalAkhirWIP    += $stokAkhir;
        }
    }

    // ======================
    // BARANG NORMAL
    // ======================
    // Cek apakah ini WIP dan ada data tambahan
    $displayStokAwal = $r['stok_awal'];
    $displayMasuk = $r['masuk'];
    $displayKeluar = $r['keluar'];
    $displayStokAkhir = $stokAkhir;

    if(strtolower($kelompokFix) == 'work in progress' && in_array($currentParent, ['hasil bongkar oven','hasil bongkar karantina'])){
        $idBarang = $r['id_barang'];
        if(isset($wipData[$idBarang])){
            $displayStokAwal += $wipData[$idBarang]['stok_awal'];
            $displayMasuk += $wipData[$idBarang]['masuk'];
            $displayKeluar += $wipData[$idBarang]['keluar'];
            $displayStokAkhir = $displayStokAwal + $displayMasuk - $displayKeluar;
        }
    }

    echo "
    <tr>
        <td></td>
        <td>".$no.".</td>
        <td>".$r['nama_barang']."</td>
        <td class='angka'>".number_format($displayStokAwal,2)."</td>
        <td class='angka'>".number_format($displayMasuk,0)."</td>
        <td class='angka'>".number_format($displayKeluar,0)."</td>
        <td class='angka'>".number_format($displayStokAkhir,2)."</td>
        <td>".$r['satuan']."</td>
        <td></td>
    </tr>
    ";

    $no++;

    $lastParent = $currentParent;
$lastKelompok = $kelompokFix;
}

// ======================
// FINAL PRINT JIKA WIP TERAKHIR
// ======================
if(
    strtolower($lastKelompok) == 'work in progress' &&
    in_array($lastParent, ['hasil bongkar oven','hasil bongkar karantina'])
){
    echo "
    <tr style='background:#fff3cd;font-weight:bold'>
        <td></td>
        <td></td>
        <td>JUMLAH ".ucwords($lastParent)."</td>
        <td class='angka'>".number_format($totalStokAwalWIP,2)."</td>
        <td class='angka'>".number_format($totalMasukWIP,0)."</td>
        <td class='angka'>".number_format($totalKeluarWIP,0)."</td>
        <td class='angka'>".number_format($totalAkhirWIP,2)."</td>
        <td>Kg</td>
        <td></td>
    </tr>
    ";
}
?>

  </tbody>

  <tfoot>
  </tfoot>
</table>
      </div>

      <div class="mt-2">
        <small class="text-muted">
          Catatan: Stok Awal dihitung dari saldo mutasi sebelum tanggal <b><?= $tglAwal ?> </b>atau ditambah saat tanggal tersebut.
        </small>
      </div>

    </div>
  </div>

</div>
<!-- jQuery -->
<script src="../plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="../plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="../plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="../plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="../plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="../plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="../plugins/moment/moment.min.js"></script>
<script src="../plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="../plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="../dist/js/adminlte.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="../dist/js/pages/dashboard.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="../dist/js/demo.js"></script>
</body>

</html>
