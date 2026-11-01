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
$errors = [];

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function redirectWithMessage(string $message): void
{
    header("Location: student_view.php?message=" . urlencode($message));
    exit;
}

$student = [
    "nim" => "",
    "name" => "",
    "birth_city" => "",
    "birth_date" => "",
    "faculty" => "FTIB",
    "department" => "",
    "gpa" => ""
];
$oldNim = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $oldNim = trim((string) ($_POST["old_nim"] ?? ""));
    $student["nim"] = trim((string) ($_POST["nim"] ?? ""));
    $student["name"] = trim((string) ($_POST["name"] ?? ""));
    $student["birth_city"] = trim((string) ($_POST["birth_city"] ?? ""));
    $student["birth_date"] = trim((string) ($_POST["birth_date"] ?? ""));
    $student["faculty"] = trim((string) ($_POST["faculty"] ?? ""));
    $student["department"] = trim((string) ($_POST["department"] ?? ""));
    $student["gpa"] = trim((string) ($_POST["gpa"] ?? ""));

    if (!isset($_POST["csrf_token"]) || !hash_equals($csrfToken, (string) $_POST["csrf_token"])) {
        $errors[] = "Permintaan tidak valid. Silakan muat ulang halaman dan coba lagi.";
    }
    if (!preg_match("/^[0-9]{8}$/", $oldNim)) {
        $errors[] = "NIM awal tidak valid.";
    }
    if (!preg_match("/^[0-9]{8}$/", $student["nim"])) {
        $errors[] = "NIM harus terdiri dari tepat 8 digit angka.";
    }
    if ($student["name"] === "") {
        $errors[] = "Nama mahasiswa wajib diisi.";
    }
    if ($student["birth_city"] === "") {
        $errors[] = "Tempat lahir wajib diisi.";
    }
    if ($student["department"] === "") {
        $errors[] = "Jurusan wajib diisi.";
    }
    if (!in_array($student["faculty"], ["FTIB", "FTEIC"], true)) {
        $errors[] = "Fakultas yang dipilih tidak valid.";
    }

    $dateObject = DateTime::createFromFormat("!Y-m-d", $student["birth_date"]);
    if (!$dateObject || $dateObject->format("Y-m-d") !== $student["birth_date"]) {
        $errors[] = "Tanggal lahir tidak valid.";
    }
    if (!is_numeric($student["gpa"]) || (float) $student["gpa"] <= 0 || (float) $student["gpa"] > 4) {
        $errors[] = "IPK harus berupa angka lebih dari 0 dan maksimal 4.";
    }

    if (count($errors) === 0 && $student["nim"] !== $oldNim) {
        $check = mysqli_prepare($connection, "SELECT nim FROM student WHERE nim = ?");
        mysqli_stmt_bind_param($check, "s", $student["nim"]);
        mysqli_stmt_execute($check);
        $existing = mysqli_stmt_get_result($check);
        if (mysqli_num_rows($existing) > 0) {
            $errors[] = "NIM baru sudah digunakan oleh mahasiswa lain.";
        }
        mysqli_free_result($existing);
        mysqli_stmt_close($check);
    }

    if (count($errors) === 0) {
        $gpa = (float) $student["gpa"];
        $update = mysqli_prepare(
            $connection,
            "UPDATE student
             SET nim = ?, name = ?, birth_city = ?, birth_date = ?, faculty = ?, department = ?, gpa = ?
             WHERE nim = ?"
        );
        mysqli_stmt_bind_param(
            $update,
            "ssssssds",
            $student["nim"],
            $student["name"],
            $student["birth_city"],
            $student["birth_date"],
            $student["faculty"],
            $student["department"],
            $gpa,
            $oldNim
        );

        if (mysqli_stmt_execute($update)) {
            mysqli_stmt_close($update);
            mysqli_close($connection);
            redirectWithMessage("Data mahasiswa berhasil diperbarui bangg.");
        }

        $errors[] = "Data gagal diperbarui. Periksa kembali data yang dimasukkan.";
        mysqli_stmt_close($update);
    }
} else {
    $oldNim = trim((string) ($_GET["nim"] ?? ""));
    if (!preg_match("/^[0-9]{8}$/", $oldNim)) {
        mysqli_close($connection);
        redirectWithMessage("NIM tidak valid atau tidak ditemukan.");
    }

    $select = mysqli_prepare(
        $connection,
        "SELECT nim, name, birth_city, birth_date, faculty, department, gpa FROM student WHERE nim = ?"
    );
    mysqli_stmt_bind_param($select, "s", $oldNim);
    mysqli_stmt_execute($select);
    $result = mysqli_stmt_get_result($select);
    $row = mysqli_fetch_assoc($result);

    mysqli_free_result($result);
    mysqli_stmt_close($select);

    if (!$row) {
        mysqli_close($connection);
        redirectWithMessage("Data mahasiswa tidak ditemukan.");
    }
    $student = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mahasiswa</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 40px 20px; 
            background-color: #f4f6f9; 
            color: #333; 
        }
        .container { 
            max-width: 550px; 
            margin: 0 auto; 
            background: #ffffff; 
            padding: 28px 32px; 
            border-radius: 8px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.08); 
        }
        h1 { 
            margin-top: 0; 
            margin-bottom: 24px; 
            font-size: 24px; 
            color: #1a252f; 
            border-bottom: 2px solid #e9ecef; 
            padding-bottom: 12px; 
        }
        label { 
            display: block; 
            margin-top: 14px; 
            font-weight: 600; 
            font-size: 14px; 
            color: #495057; 
        }
        input, select { 
            box-sizing: border-box; 
            width: 100%; 
            padding: 10px 12px; 
            margin-top: 6px; 
            border: 1px solid #ced4da; 
            border-radius: 6px; 
            font-size: 14px; 
            transition: border-color 0.2s, box-shadow 0.2s; 
        }
        input:focus, select:focus { 
            border-color: #0d6efd; 
            outline: none; 
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15); 
        }
        .button-group { 
            margin-top: 24px; 
            display: flex; 
            gap: 10px; 
        }
        .button { 
            display: inline-block; 
            padding: 10px 18px; 
            border-radius: 6px; 
            font-size: 14px; 
            font-weight: 600; 
            text-decoration: none; 
            text-align: center; 
            cursor: pointer; 
            border: none; 
        }
        .button-primary { 
            background-color: #0d6efd; 
            color: #fff; 
        }
        .button-primary:hover { 
            background-color: #0b5ed7; 
        }
        .button-secondary { 
            background-color: #6c757d; 
            color: #fff; 
        }
        .button-secondary:hover { 
            background-color: #5c636a; 
        }
        .errors { 
            background: #f8d7da; 
            border: 1px solid #f5c2c7; 
            color: #842029; 
            padding: 12px 16px; 
            border-radius: 6px; 
            margin-bottom: 20px; 
        }
        .errors ul { 
            margin: 6px 0 0 0; 
            padding-left: 20px; 
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Edit Data Mahasiswa</h1>

    <?php if ($errors): ?>
        <div class="errors">
            <strong>Periksa kembali data berikut:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= h($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="student_edit.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
        <input type="hidden" name="old_nim" value="<?= h($oldNim) ?>">

        <label for="nim">NIM (8 digit angka)</label>
        <input type="text" id="nim" name="nim" maxlength="8" pattern="[0-9]{8}" required value="<?= h($student["nim"]) ?>">

        <label for="name">Nama</label>
        <input type="text" id="name" name="name" maxlength="100" required value="<?= h($student["name"]) ?>">

        <label for="birth_city">Tempat Lahir</label>
        <input type="text" id="birth_city" name="birth_city" maxlength="50" required value="<?= h($student["birth_city"]) ?>">

        <label for="birth_date">Tanggal Lahir</label>
        <input type="date" id="birth_date" name="birth_date" required value="<?= h($student["birth_date"]) ?>">

        <label for="faculty">Fakultas</label>
        <select id="faculty" name="faculty" required>
            <option value="FTIB" <?= $student["faculty"] === "FTIB" ? "selected" : "" ?>>FTIB</option>
            <option value="FTEIC" <?= $student["faculty"] === "FTEIC" ? "selected" : "" ?>>FTEIC</option>
        </select>

        <label for="department">Jurusan</label>
        <input type="text" id="department" name="department" maxlength="50" required value="<?= h($student["department"]) ?>">

        <label for="gpa">IPK (lebih dari 0 sampai 4)</label>
        <input type="number" id="gpa" name="gpa" min="0.01" max="4" step="0.01" required value="<?= h($student["gpa"]) ?>">

        <div class="button-group">
            <button class="button button-primary" type="submit">Simpan Perubahan</button>
            <a class="button button-secondary" href="student_view.php">Batal</a>
        </div>
    </form>
</div>
</body>
</html>
<?php mysqli_close($connection); ?>