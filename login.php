<?php
  if (isset($_POST["submit"])) {
    $username = htmlentities(strip_tags(trim($_POST["username"])));
    $password = htmlentities(strip_tags(trim($_POST["password"])));

    $error_message = "";

    if (empty($username)) {
      $error_message .= "- Username belum diisi <br>";
    }

    if (empty($password)) {
      $error_message .= "- Password belum diisi <br>";
    }

    include("connection.php");

    $username = mysqli_real_escape_string($connection, $username);
    $password = mysqli_real_escape_string($connection, $password);

    $password_sha1 = sha1($password);

    $query = "
      SELECT * FROM admin 
      WHERE username = '$username' AND password = '$password_sha1'";
    $result = mysqli_query($connection, $query);

    if (mysqli_num_rows($result) == 0) {
      $error_message .= "- Username dan/atau Password tidak sesuai";
    }

    mysqli_free_result($result);
    mysqli_close($connection);

    if ($error_message === "") {
      session_start();
      $_SESSION["username"] = $username;
      header("Location: student_view.php");
      exit();
    }
  } else {
    $error_message = "";
    $username = "";
    $password = "";
  }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Mahasiswa - Login</title>
  <link rel="stylesheet" href="assets/login.css">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background-color: #f4f6f9;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .container {
      background-color: #ffffff;
      width: 100%;
      max-width: 400px;
      padding: 35px 30px;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    h1 {
      font-size: 24px;
      color: #333333;
      text-align: center;
      margin-bottom: 20px;
    }

    fieldset {
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      padding: 20px;
    }

    legend {
      font-weight: 600;
      color: #0056b3;
      padding: 0 8px;
    }

    p {
      margin-bottom: 15px;
    }

    label {
      display: block;
      font-size: 14px;
      color: #555555;
      margin-bottom: 6px;
      font-weight: 500;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #cccccc;
      border-radius: 6px;
      font-size: 14px;
      outline: none;
      transition: border-color 0.2s ease-in-out;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
      border-color: #0056b3;
    }

    input[type="submit"] {
      width: 100%;
      background-color: #0056b3;
      color: #ffffff;
      border: none;
      padding: 12px;
      border-radius: 6px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      margin-top: 10px;
      transition: background-color 0.2s ease-in-out;
    }

    input[type="submit"]:hover {
      background-color: #004085;
    }

    .error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
      padding: 10px 14px;
      border-radius: 6px;
      font-size: 13px;
      margin-bottom: 15px;
      line-height: 1.4;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Data Mahasiswa</h1>
    <?php
      if ($error_message !== "")
        echo "<div class='error'>$error_message</div>";
    ?>
    <form action="login.php" method="post">
      <fieldset>
        <legend>Login</legend>
        <p>
          <label for="username">Username : </label>
          <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>">
        </p>
        <p>
          <label for="password">Password : </label>
          <input type="password" name="password" id="password" value="">
        </p>
        <p>
          <input type="submit" name="submit" value="Log In">
        </p>
      </fieldset>
    </form>
  </div>
</body>
</html>