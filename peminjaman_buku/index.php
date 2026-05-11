<?php
// ============================================================
//  Konfigurasi Koneksi Database
//  Sesuaikan host, user, password jika berbeda
// ============================================================
$host     = "localhost";
$user     = "root";
$password = "";
$dbname   = "db_perpustakaan";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("<div class='error'>Koneksi gagal: " . $conn->connect_error . "</div>");
}
$conn->set_charset("utf8");

// ============================================================
//  Helper: jalankan query & kembalikan array hasil
// ============================================================
function runQuery($conn, $sql) {
    $result = $conn->query($sql);
    $rows = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    return $rows;
}

// ============================================================
//  QUERY 1 – Bobot 15: Buku belum kembali
// ============================================================
$q1 = runQuery($conn, "
    SELECT
        p.id_peminjam           AS 'ID Peminjam',
        p.nama_peminjam         AS 'Nama Peminjam',
        p.alamat                AS 'Alamat',
        p.kode_buku             AS 'Kode Buku',
        b.nama_buku             AS 'Nama Buku',
        b.tahun_terbit          AS 'Tahun Terbit',
        p.lama_pinjam           AS 'Lama Pinjam (Hari)',
        p.tanggal_pinjam        AS 'Tanggal Pinjam',
        p.status_pengembalian   AS 'Status Pengembalian'
    FROM  peminjaman p
    JOIN  buku b ON p.kode_buku = b.kode_buku
    WHERE p.status_pengembalian = 'Belum'
");

// ============================================================
//  QUERY 2 – Bobot 10: Peminjam <= 4 hari
// ============================================================
$q2 = runQuery($conn, "
    SELECT
        id_peminjam     AS 'ID Peminjam',
        nama_peminjam   AS 'Nama Peminjam',
        alamat          AS 'Alamat',
        tanggal_pinjam  AS 'Tanggal Pinjam'
    FROM  peminjaman
    WHERE lama_pinjam <= 4
");

// ============================================================
//  QUERY 3 – Bobot 5: Peminjam di Jl. Sembilang
// ============================================================
$q3 = runQuery($conn, "
    SELECT
        id_peminjam     AS 'ID Peminjam',
        nama_peminjam   AS 'Nama Peminjam',
        alamat          AS 'Alamat'
    FROM  peminjaman
    WHERE alamat = 'Jl. Sembilang'
");

// ============================================================
//  QUERY 4 – Bobot 20: Biaya peminjam 5 hari
// ============================================================
$q4 = runQuery($conn, "
    SELECT
        p.id_peminjam                           AS 'ID Peminjam',
        p.nama_peminjam                         AS 'Nama Peminjam',
        p.alamat                                AS 'Alamat',
        p.kode_buku                             AS 'Kode Buku',
        b.nama_buku                             AS 'Nama Buku',
        p.tanggal_pinjam                        AS 'Tanggal Pinjam',
        p.lama_pinjam                           AS 'Lama Pinjam (Hari)',
        (p.lama_pinjam * b.harga_sewa_hari)     AS 'Biaya'
    FROM  peminjaman p
    JOIN  buku b ON p.kode_buku = b.kode_buku
    WHERE p.lama_pinjam = 5
");

$conn->close();

// ============================================================
//  Helper: render tabel HTML dari array rows
// ============================================================
function renderTable($rows, $badgeColor = "#3b82f6") {
    if (empty($rows)) {
        echo '<p class="empty">Tidak ada data ditemukan.</p>';
        return;
    }
    $headers = array_keys($rows[0]);
    echo '<div class="table-wrapper">';
    echo '<table>';
    echo '<thead><tr>';
    foreach ($headers as $h) {
        echo '<th>' . htmlspecialchars($h) . '</th>';
    }
    echo '</tr></thead><tbody>';
    foreach ($rows as $row) {
        echo '<tr>';
        foreach ($row as $key => $val) {
            // Format biaya sebagai Rupiah
            if (strtolower($key) === 'biaya') {
                $val = 'Rp ' . number_format((float)$val, 0, ',', '.');
            }
            echo '<td>' . htmlspecialchars($val ?? '-') . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sistem Perpustakaan – Laporan Data</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  /* ── Reset & Base ── */
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg:        #f5f2ec;
    --surface:   #ffffff;
    --ink:       #1a1612;
    --muted:     #6b6460;
    --border:    #e2ddd7;
    --accent1:   #c84b2f;   /* merah tanah – bobot 15 */
    --accent2:   #2563eb;   /* biru – bobot 10 */
    --accent3:   #059669;   /* hijau – bobot 5 */
    --accent4:   #7c3aed;   /* ungu – bobot 20 */
    --row-odd:   #faf8f5;
    --row-hover: #fff4f2;
    --shadow:    0 4px 24px rgba(0,0,0,.08);
  }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--ink);
    min-height: 100vh;
    padding: 0 0 60px;
  }

  /* ── Header ── */
  header {
    background: var(--ink);
    color: #fff;
    padding: 40px 48px 36px;
    position: relative;
    overflow: hidden;
  }
  header::after {
    content: '';
    position: absolute;
    right: -60px; bottom: -80px;
    width: 300px; height: 300px;
    border-radius: 50%;
    background: rgba(200,75,47,.15);
  }
  header .label {
    font-size: .75rem;
    font-weight: 600;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--accent1);
    margin-bottom: 8px;
  }
  header h1 {
    font-family: 'DM Serif Display', serif;
    font-size: clamp(1.8rem, 4vw, 2.8rem);
    line-height: 1.15;
    margin-bottom: 8px;
  }
  header p {
    font-size: .95rem;
    color: rgba(255,255,255,.55);
    font-weight: 300;
  }

  /* ── Container ── */
  .container { max-width: 1100px; margin: 0 auto; padding: 0 24px; }

  /* ── Section card ── */
  .section {
    background: var(--surface);
    border-radius: 16px;
    box-shadow: var(--shadow);
    margin-top: 36px;
    overflow: hidden;
    animation: fadeUp .4s ease both;
  }
  .section:nth-child(2) { animation-delay: .05s; }
  .section:nth-child(3) { animation-delay: .10s; }
  .section:nth-child(4) { animation-delay: .15s; }

  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(18px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* ── Section header ── */
  .sec-head {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 22px 28px;
    border-bottom: 1px solid var(--border);
  }
  .badge {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    padding: 4px 10px;
    border-radius: 999px;
    color: #fff;
    flex-shrink: 0;
  }
  .sec-head h2 {
    font-family: 'DM Serif Display', serif;
    font-size: 1.15rem;
    font-weight: 400;
  }
  .sec-head .bobot {
    margin-left: auto;
    font-size: .78rem;
    font-weight: 600;
    color: var(--muted);
    white-space: nowrap;
  }

  /* ── Colours per section ── */
  .s1 .badge { background: var(--accent1); }
  .s1 .sec-head { border-left: 4px solid var(--accent1); }
  .s2 .badge { background: var(--accent2); }
  .s2 .sec-head { border-left: 4px solid var(--accent2); }
  .s3 .badge { background: var(--accent3); }
  .s3 .sec-head { border-left: 4px solid var(--accent3); }
  .s4 .badge { background: var(--accent4); }
  .s4 .sec-head { border-left: 4px solid var(--accent4); }

  /* ── Table wrapper ── */
  .table-wrapper {
    overflow-x: auto;
    padding: 0 4px 4px;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    font-size: .875rem;
  }
  thead th {
    background: #f8f6f3;
    font-weight: 600;
    font-size: .75rem;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--muted);
    padding: 12px 18px;
    text-align: left;
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
  }
  tbody tr:nth-child(odd) { background: var(--row-odd); }
  tbody tr:hover { background: var(--row-hover); transition: background .15s; }
  tbody td {
    padding: 11px 18px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
  }
  tbody tr:last-child td { border-bottom: none; }

  /* Status pill */
  td:last-child:not(:only-child) { }

  /* Highlight Belum */
  .pill-belum {
    display: inline-block;
    background: #fee2e2;
    color: #b91c1c;
    font-size: .72rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 999px;
  }
  .pill-kembali {
    display: inline-block;
    background: #dcfce7;
    color: #15803d;
    font-size: .72rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 999px;
  }
  .biaya-cell {
    font-weight: 700;
    color: var(--accent4);
  }

  /* ── Empty state ── */
  .empty {
    padding: 32px;
    text-align: center;
    color: var(--muted);
    font-style: italic;
  }

  /* ── Error ── */
  .error {
    background: #fee2e2;
    color: #b91c1c;
    padding: 20px 28px;
    border-radius: 10px;
    margin-top: 20px;
    font-weight: 500;
  }

  /* ── Footer ── */
  footer {
    text-align: center;
    margin-top: 48px;
    font-size: .8rem;
    color: var(--muted);
  }
</style>
</head>
<body>

<header>
  <div class="container">
    <div class="label">db_perpustakaan</div>
    <h1>Laporan Data Peminjaman<br>Buku Perpustakaan</h1>
    <p>Menampilkan hasil 4 query – Total Bobot 50</p>
  </div>
</header>

<div class="container">

  <!-- ===== SECTION 1 – Bobot 15 ===== -->
  <div class="section s1">
    <div class="sec-head">
      <span class="badge">Query 1</span>
      <h2>Buku yang Belum Dikembalikan</h2>
      <span class="bobot">⭐ Bobot 15</span>
    </div>
    <?php
    // Tampilkan dengan pill status
    if (empty($q1)) {
        echo '<p class="empty">Tidak ada data ditemukan.</p>';
    } else {
        $headers = array_keys($q1[0]);
        echo '<div class="table-wrapper"><table>';
        echo '<thead><tr>';
        foreach ($headers as $h) echo '<th>' . htmlspecialchars($h) . '</th>';
        echo '</tr></thead><tbody>';
        foreach ($q1 as $row) {
            echo '<tr>';
            foreach ($row as $key => $val) {
                if ($key === 'Status Pengembalian') {
                    $cls = ($val === 'Belum') ? 'pill-belum' : 'pill-kembali';
                    echo '<td><span class="' . $cls . '">' . htmlspecialchars($val) . '</span></td>';
                } else {
                    echo '<td>' . htmlspecialchars($val ?? '-') . '</td>';
                }
            }
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    }
    ?>
  </div>

  <!-- ===== SECTION 2 – Bobot 10 ===== -->
  <div class="section s2">
    <div class="sec-head">
      <span class="badge">Query 2</span>
      <h2>Peminjam dengan Masa Pinjam ≤ 4 Hari</h2>
      <span class="bobot">⭐ Bobot 10</span>
    </div>
    <?php renderTable($q2); ?>
  </div>

  <!-- ===== SECTION 3 – Bobot 5 ===== -->
  <div class="section s3">
    <div class="sec-head">
      <span class="badge">Query 3</span>
      <h2>Peminjam yang Tinggal di Jl. Sembilang</h2>
      <span class="bobot">⭐ Bobot 5</span>
    </div>
    <?php renderTable($q3); ?>
  </div>

  <!-- ===== SECTION 4 – Bobot 20 ===== -->
  <div class="section s4">
    <div class="sec-head">
      <span class="badge">Query 4</span>
      <h2>Perhitungan Biaya – Peminjam 5 Hari</h2>
      <span class="bobot">⭐ Bobot 20</span>
    </div>
    <?php
    if (empty($q4)) {
        echo '<p class="empty">Tidak ada data ditemukan.</p>';
    } else {
        $headers = array_keys($q4[0]);
        echo '<div class="table-wrapper"><table>';
        echo '<thead><tr>';
        foreach ($headers as $h) echo '<th>' . htmlspecialchars($h) . '</th>';
        echo '</tr></thead><tbody>';
        foreach ($q4 as $row) {
            echo '<tr>';
            foreach ($row as $key => $val) {
                if ($key === 'Biaya') {
                    echo '<td class="biaya-cell">Rp ' . number_format((float)$val, 0, ',', '.') . '</td>';
                } else {
                    echo '<td>' . htmlspecialchars($val ?? '-') . '</td>';
                }
            }
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    }
    ?>
  </div>

  <footer>
    <p>db_perpustakaan &nbsp;·&nbsp; Sistem Informasi Perpustakaan</p>
  </footer>

</div><!-- /container -->
</body>
</html>