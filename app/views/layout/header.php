<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MDM Dashboard</title>
  <link rel="shortcut icon" href="public/assets/img/favicon.ico" type="image/x-icon">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap Select CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <!-- Custom CSS -->
  <link href="public/assets/css/style.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">
  <nav class="navbar navbar-expand-lg navbar-light" style="background-color: #1e90ff;">
    <div class="container-fluid">
      <span class="navbar-brand text-white fw-bold">MDM Dashboard</span>
      <div class="d-flex align-items-center">
        <span class="text-white me-3">
          <i class="bi bi-person-fill"></i> <?= $_SESSION['username'] ?> (<?= $_SESSION['role'] ?>)
        </span>
        <a href="index.php?page=logout" class="btn btn-success btn-sm">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </div>
    </div>
  </nav>

  <!-- Wrapper utama -->
  <div class="d-flex flex-grow-1">
