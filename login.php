<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    
    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
    $data_user = mysqli_fetch_assoc($query);

    if ($data_user) {
        
        if (password_verify($password, $data_user['password'])) {
            
            
            $_SESSION['id_user']      = $data_user['id'];
            $_SESSION['username']     = $username;
            $_SESSION['role']         = $data_user['role']; 
            $_SESSION['nama_lengkap'] = $data_user['nama_lengkap']; 
            $_SESSION['status']       = "login";

            
            
            header("Location: index.php");
            exit;

        } else {
            $error = "Password yang Anda masukkan salah.";
        }
    } else {
        $error = "Username tidak terdaftar.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem WMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #162433;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }
        .login-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .icon {
            width: 150px;
            height: 150px;
            margin-bottom: 20px;
            object-fit: contain;
           
            background: #eee; 
            border-radius: 50%;
        }
        h2 { margin-bottom: 20px; color: #333; }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #667eea;
            outline: none;
        }
        button {
            width: 100%;
            padding: 15px;
            background: #FF7F27;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        button:hover { background: #e89f6e; }
        .error-msg {
            color: #dc3545;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Login WMS</h2>

        <?php if(isset($error)) { ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php } ?>

        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required autocomplete="off">
            <input type="password" name="password" placeholder="Password" required>
            
            <button type="submit" name="login">Masuk</button>
        </form>
        
        <div style="margin-top: 20px; font-size: 12px; color: #666; text-align: left;">
            <b>Akun Demo:</b><br>
            - Admin: admin / 123456<br>
            - Operator: operator / 123456<br>
            - Requester: requester / 123456
        </div>
    </div>

</body>
</html>