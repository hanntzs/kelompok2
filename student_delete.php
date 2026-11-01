<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}

require_once "connection.php";

function redirectWithMessage(string $message): void
{
    header("Location: student_view.php?message=" . urlencode($message));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    mysqli_close($connection);
    redirectWithMessage("Permintaan hapus tidak valid.");
}

$csrfToken = (string) ($_POST["csrf_token"] ?? "");
if (!isset($_SESSION["csrf_token"]) || !hash_equals($_SESSION["csrf_token"], $csrfToken)) {
    mysqli_close($connection);
    redirectWithMessage("Permintaan tidak valid. Silakan coba lagi.");
}

$nim = trim((string) ($_POST["nim"] ?? ""));
if (!preg_match("/^[0-9]{8}$/", $nim)) {
    mysqli_close($connection);
    redirectWithMessage("NIM tidak valid.");
}

$stmt = mysqli_prepare($connection, "DELETE FROM student WHERE nim = ?");
mysqli_stmt_bind_param($stmt, "s", $nim);

if (mysqli_stmt_execute($stmt)) {
    $deletedRows = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($connection);

    if ($deletedRows > 0) {
        redirectWithMessage("Data mahasiswa dengan NIM $nim berhasil dihapus.");
    }
    redirectWithMessage("Data mahasiswa tidak ditemukan atau sudah dihapus.");
}

mysqli_stmt_close($stmt);
mysqli_close($connection);
redirectWithMessage("Data mahasiswa gagal dihapus.");
