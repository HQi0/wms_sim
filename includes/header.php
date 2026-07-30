<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'WMS System' ?></title>
    
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* CSS UTAMA: FIX SIDEBAR FULL HEIGHT */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f0f0f0; 
            margin: 0; 
            padding: 0; 
            display: flex; 
            height: 100vh; /* Tinggi layar penuh */
            overflow: hidden; /* Mencegah scroll ganda pada body */
        }
        
        .wrapper { 
            display: flex; 
            width: 100%; 
            height: 100%; 
        }
        
        /* Sidebar Styles */
        .sidebar { 
            width: 260px; 
            background: #162433; 
            color: white; 
            padding: 20px; 
            box-sizing: border-box; 
            flex-shrink: 0; 
            display: flex; 
            flex-direction: column; 
            height: 100%; /* Penuh ke bawah */
            overflow-y: auto; /* Scroll sendiri jika menu panjang */
        }

        .sidebar h2 { font-size: 20px; margin-bottom: 10px; border-bottom: 1px solid #444; padding-bottom: 10px; }
        .sidebar .user-info { font-size: 13px; color: #bbb; margin-bottom: 30px; }
        .sidebar a { display: flex; align-items: center; gap: 10px; color: #ccc; text-decoration: none; padding: 12px 15px; border-bottom: 1px solid #2a3c50; transition: 0.3s; font-size: 14px; }
        .sidebar a:hover, .sidebar a.active { color: #FF7F27; background: #2a3c50; padding-left: 20px; }
        .sidebar .badge { background: #dc3545; color: white; padding: 2px 6px; border-radius: 10px; font-size: 10px; margin-left: auto; }

        /* Content Styles */
        .content { 
            flex: 1; 
            padding: 30px; 
            overflow-y: auto; /* Konten scroll sendiri */
            height: 100%; 
        }
        
        .card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); margin-bottom: 20px; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 13px; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        
        .btn { padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; color: white; text-decoration: none; display: inline-block; font-size: 13px; margin-right: 5px; }
        .btn-primary { background: #28a745; } 
        .btn-warning { background: #ffc107; color: black; } 
        .btn-danger { background: #dc3545; } 
        .btn-info { background: #17a2b8; }

        /* Table & Search Styles */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; }
        th, td { border: 1px solid #eee; padding: 12px; text-align: left; }
        th { background-color: #FF7F27; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }

        .search-box { display: flex; gap: 10px; margin-bottom: 15px; }
        .search-box input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .search-box button { padding: 10px 20px; background: #162433; color: white; border: none; border-radius: 4px; cursor: pointer; display: flex; gap: 5px; align-items: center; }
        .btn-reset { padding: 10px; background: #fee2e2; color: #dc2626; border-radius: 4px; text-decoration: none; display: flex; align-items: center; }

        .alert-success { background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-danger { background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 4px; }

        /* Badges */
        .badge-danger { background: #ffebee; color: #c62828; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }
        .badge-success { background: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }
        .badge-warning { background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }
        .badge-loc { background: #e3f2fd; color: #1565c0; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="wrapper">
