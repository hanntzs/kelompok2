<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}

require_once "connection.php";

if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION["csrf_token"];
$message = isset($_GET["message"]) ? (string) $_GET["message"] : "";

$query = "SELECT nim, name, birth_city, birth_date, faculty, department, gpa
          FROM student ORDER BY nim ASC";
$result = mysqli_query($connection, $query);

if (!$result) {
    die("Query Error: " . htmlspecialchars(mysqli_error($connection), ENT_QUOTES, "UTF-8"));
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa - Modern Dashboard</title>
    <!-- Google Fonts & FontAwesome untuk ikon -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            padding: 40px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 15px;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            color: var(--text-main);
        }

        nav {
            display: flex;
            gap: 10px;
        }

        nav a, .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            border: none;
        }

        nav a {
            background: #f1f5f9;
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }

        nav a:hover {
            background: #e2e8f0;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .message {
            background: #ecfdf5;
            border: 1px solid #10b981;
            color: #065f46;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
            background: white;
        }

        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
        }

        th {
            background: #f8fafc;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.05em;
        }

        tr:hover td {
            background-color: #f8fafc;
        }

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .actions form {
            margin: 0;
        }

        .btn-edit {
            background: #fef3c7;
            color: #92400e;
            padding: 6px 12px;
            font-size: 13px;
        }
        .btn-edit:hover { background: #fde68a; }

        .button.delete {
            background: #fee2e2;
            color: #991b1b;
            border: none;
            padding: 6px 12px;
            font-size: 13px;
        }
        .button.delete:hover {
            background: #fecaca;
        }

        .badge-gpa {
            font-weight: 600;
            color: #059669;
            background: #d1fae5;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header-flex">
        <h1>📋 Data Mahasiswa</h1>
        <nav>
            <a href="student_view.php">Tampil</a>
            <a href="student_add.php" class="btn-primary">+ Tambah Mahasiswa</a>
            <a href="logout.php" style="background: #fee2e2; color: #991b1b; border-color: #fca5a5;">Logout</a>
        </nav>
    </div>

    <?php if ($message !== ""): ?>
        <div class="message">✨ <?= h($message) ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table>
            <thead>
            <tr>
                <th>No</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>Tempat, Tanggal Lahir</th>
                <th>Fakultas</th>
                <th>Jurusan</th>
                <th>IPK</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($result) === 0): ?>
                <tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">Belum ada data mahasiswa.</td></tr>
            <?php else: ?>
                <?php $no = 1; while ($data = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong><?= h($data["nim"]) ?></strong></td>
                        <td><?= h($data["name"]) ?></td>
                        <td><?= h($data["birth_city"]) ?>, <?= h(date("d-m-Y", strtotime($data["birth_date"]))) ?></td>
                        <td><?= h($data["faculty"]) ?></td>
                        <td><?= h($data["department"]) ?></td>
                        <td><span class="badge-gpa"><?= h($data["gpa"]) ?></span></td>
                        <td>
                            <div class="actions">
                                <a class="button btn-edit" href="student_edit.php?nim=<?= rawurlencode($data["nim"]) ?>">Edit</a>
                                <form action="student_delete.php" method="post"
                                      onsubmit="return confirm('Yakin ingin menghapus data mahasiswa ini?');">
                                    <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                                    <input type="hidden" name="nim" value="<?= h($data["nim"]) ?>">
                                    <button class="button delete" type="submit">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<?php
mysqli_free_result($result);
mysqli_close($connection);
?>