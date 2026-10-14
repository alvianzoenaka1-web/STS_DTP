
<?php
// ============================================
// 1. KONEKSI DATABASE
// ============================================
$host = "localhost";
$db   = "db_laporan_fasilitas";
$user = "root";
$pass = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Mengamankan output HTML
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$pesan = "";

// ============================================
// 2. CREATE - MENYIMPAN LAPORAN
// ============================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST["nama_pelapor"] ?? "");
    $fasilitas = trim($_POST["nama_fasilitas"] ?? "");
    $lokasi = trim($_POST["lokasi"] ?? "");
    $kategori = filter_var(
        $_POST["id_kategori"] ?? "",
        FILTER_VALIDATE_INT
    );
    $deskripsi = trim($_POST["deskripsi"] ?? "");
    $prioritas = $_POST["prioritas"] ?? "";

    if (
        $nama === "" ||
        $fasilitas === "" ||
        $lokasi === "" ||
        !$kategori ||
        $deskripsi === "" ||
        !in_array($prioritas, ["Rendah", "Sedang", "Tinggi"], true)
    ) {
        $pesan = "Semua data wajib diisi dengan benar.";
    } else {
        $sql = "INSERT INTO laporan_kerusakan
                (nama_pelapor, nama_fasilitas, lokasi,
                 id_kategori, deskripsi, prioritas)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nama,
            $fasilitas,
            $lokasi,
            $kategori,
            $deskripsi,
            $prioritas
        ]);

        header("Location: index.php?berhasil=1");
        exit;
    }
}

if (isset($_GET["berhasil"])) {
    $pesan = "Laporan berhasil disimpan!";
}

// ============================================
// 3. READ - MENGAMBIL DATA DARI DATABASE
// ============================================
$sql = "SELECT
            l.id_laporan,
            l.nama_pelapor,
            l.nama_fasilitas,
            l.lokasi,
            l.deskripsi,
            l.prioritas,
            l.status,
            l.tanggal_laporan,
            k.nama_kategori
        FROM laporan_kerusakan AS l
        INNER JOIN kategori_fasilitas AS k
            ON l.id_kategori = k.id_kategori
        ORDER BY l.id_laporan ASC";

$stmt = $pdo->query($sql);
$laporan = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pelaporan Kerusakan Fasilitas Sekolah</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    
<style>
    body {
        background-color: #f4f4f4;
        font-family: Arial, sans-serif;
        color: #333;
    }

    header {
        background-color: #07c6c6;
        color: white;
        padding: 25px 0;
    }

    .card {
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .form-control,
    .form-select {
        padding: 10px;
    }

    .table th {
        background-color: #7da2c7;
    }

    footer {
        color: #666;
        padding: 20px 0;
    }
</style>

</head>

<body>

<!-- HEADER -->
<header>
    <div class="container">
        <h1 class="fw-bold">
            Sistem Pelaporan Kerusakan Fasilitas Sekolah
        </h1>

        <p>Catat dan pantau laporan kerusakan fasilitas sekolah.</p>

        <a href="#form-laporan" class="btn btn-light me-2">
            Buat Laporan
        </a>

        <a href="#daftar-laporan" class="btn btn-outline-light">
            Daftar Laporan
        </a>
    </div>
</header>

<main class="container py-5">

    <!-- PESAN NOTIFIKASI -->
    <?php if ($pesan !== ""): ?>
        <div class="alert <?= isset($_GET["berhasil"])
            ? "alert-success" : "alert-danger" ?>">
            <?= e($pesan) ?>
        </div>
    <?php endif; ?>

    <!-- FORM CREATE -->
    <section id="form-laporan" class="card p-4 mb-5">

        <h2 class="h4 fw-bold">Form Pelaporan Kerusakan</h2>
        <p class="text-secondary">
            Lengkapi data berikut untuk melaporkan fasilitas yang rusak.
        </p>

        <form action="index.php" method="post">
            <div class="row">

                <div class="col-md-6 mb-3">
                    <label for="nama_pelapor" class="form-label">
                        Nama Pelapor
                    </label>
                    <input type="text" id="nama_pelapor"
                           name="nama_pelapor" class="form-control"
                           maxlength="100" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="nama_fasilitas" class="form-label">
                        Nama Fasilitas
                    </label>
                    <input type="text" id="nama_fasilitas"
                           name="nama_fasilitas" class="form-control"
                           maxlength="100" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="lokasi" class="form-label">
                        Lokasi Kerusakan
                    </label>
                    <input type="text" id="lokasi" name="lokasi"
                           class="form-control" maxlength="100" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="id_kategori" class="form-label">
                        Kategori Fasilitas
                    </label>
                    <select id="id_kategori" name="id_kategori"
                            class="form-select" required>
                        <option value="">-- Pilih kategori --</option>
                        <option value="1">Elektronik</option>
                        <option value="2">Furnitur</option>
                        <option value="3">Jaringan</option>
                        <option value="4">Peralatan Kelas</option>
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label for="deskripsi" class="form-label">
                        Deskripsi Kerusakan
                    </label>
                    <textarea id="deskripsi" name="deskripsi"
                              class="form-control" rows="4"
                              required></textarea>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="prioritas" class="form-label">
                        Prioritas
                    </label>
                    <select id="prioritas" name="prioritas"
                            class="form-select" required>
                        <option value="">-- Pilih prioritas --</option>
                        <option value="Rendah">Rendah</option>
                        <option value="Sedang">Sedang</option>
                        <option value="Tinggi">Tinggi</option>
                    </select>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        Kirim Laporan
                    </button>
                </div>

            </div>
        </form>
    </section>

    <!-- READ - DAFTAR LAPORAN -->
    <section id="daftar-laporan" class="card p-4">

        <h2 class="h4 fw-bold">Daftar Laporan Kerusakan</h2>

        <p class="text-secondary">
            Seluruh laporan yang tersimpan di database.
        </p>

        <div class="mb-3">
            <span class="badge text-bg-primary">
                Total laporan: <?= count($laporan) ?>
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">

                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Fasilitas</th>
                        <th>Lokasi</th>
                        <th>Pelapor</th>
                        <th>Prioritas</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($laporan) > 0): ?>

                        <?php $no = 1; ?>

                        <?php foreach ($laporan as $row): ?>

                            <tr>
                                <td><?= $no++ ?></td>

                                <td>
                                    <strong>
                                        <?= e($row["nama_fasilitas"]) ?>
                                    </strong>
                                    <br>
                                    <small class="text-secondary">
                                        <?= e($row["nama_kategori"]) ?>
                                    </small>
                                </td>

                                <td><?= e($row["lokasi"]) ?></td>

                                <td><?= e($row["nama_pelapor"]) ?></td>

                                <td>
                                    <?php
                                    $warna = [
                                        "Rendah" => "success",
                                        "Sedang" => "warning",
                                        "Tinggi" => "danger"
                                    ];
                                    ?>

                                    <span class="badge text-bg-<?= $warna[$row["prioritas"]] ?>">
                                        <?= e($row["prioritas"]) ?>
                                    </span>
                                </td>

                                <td>
                                    <?php
                                    $warnaStatus = [
                                        "Menunggu" => "secondary",
                                        "Diproses" => "primary",
                                        "Selesai" => "success"
                                    ];
                                    ?>

                                    <span class="badge text-bg-<?= $warnaStatus[$row["status"]] ?>">
                                        <?= e($row["status"]) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= e(date(
                                        "d-m-Y H:i",
                                        strtotime($row["tanggal_laporan"])
                                    )) ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" class="text-center py-4">
                                Belum ada data laporan.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>
            </table>
        </div>
    </section>

</main>

<footer class="container text-center py-4">
    <p>
        &copy; <?= date("Y") ?>
        Sistem Pelaporan Kerusakan Fasilitas Sekolah.
    </p>
</footer>

</body>
</html>
