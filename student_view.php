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
    <title>Data Mahasiswa</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; color: #222; }
        .container { max-width: 1100px; margin: 0 auto; }
        nav { margin: 16px 0; }
        nav a, .button { display: inline-block; margin-right: 8px; padding: 8px 12px;
            border: 1px solid #aaa; border-radius: 4px; text-decoration: none; color: #222;
            background: #f5f5f5; cursor: pointer; font-size: 14px; }
        table { border-collapse: collapse; width: 100%; margin-top: 16px; }
        th, td { border: 1px solid #ccc; padding: 9px; text-align: left; }
        th { background: #f2f2f2; }
        .message { background: #eaf7ea; border: 1px solid #8ac58a; padding: 10px; margin: 12px 0; }
        .actions { display: flex; gap: 6px; align-items: center; }
        .actions form { margin: 0; }
        .delete { background: #fff0f0; border-color: #d99; }
    </style>
</head>
<body>
<div class="container">
    <h1>Data Mahasiswa</h1>
    <nav>
        <a href="student_view.php">Tampil</a>
        <a href="student_add.php">Tambah Mahasiswa</a>
        <a href="logout.php">Logout</a>
    </nav>

    <?php if ($message !== ""): ?>
        <div class="message"><?= h($message) ?></div>
    <?php endif; ?>

    <table>
        <thead>
        <tr>
            <th>NIM</th>
            <th>Nama</th>
            <th>Tempat Lahir</th>
            <th>Tanggal Lahir</th>
            <th>Fakultas</th>
            <th>Jurusan</th>
            <th>IPK</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($result) === 0): ?>
            <tr><td colspan="8">Belum ada data mahasiswa.</td></tr>
        <?php else: ?>
            <?php while ($data = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= h($data["nim"]) ?></td>
                    <td><?= h($data["name"]) ?></td>
                    <td><?= h($data["birth_city"]) ?></td>
                    <td><?= h(date("d-m-Y", strtotime($data["birth_date"]))) ?></td>
                    <td><?= h($data["faculty"]) ?></td>
                    <td><?= h($data["department"]) ?></td>
                    <td><?= h($data["gpa"]) ?></td>
                    <td>
                        <div class="actions">
                            <a class="button" href="student_edit.php?nim=<?= rawurlencode($data["nim"]) ?>">Edit</a>
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
</body>
</html>
<?php
mysqli_free_result($result);
mysqli_close($connection);
?>