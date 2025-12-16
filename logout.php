<?php
session_start();

// 1. Hapus semua variabel session (id_user, role, dll)
session_unset();

// 2. Hancurkan session fisik di server
session_destroy();

// 3. Kembalikan pengguna ke halaman login
header("Location: login.php");
exit;
?>