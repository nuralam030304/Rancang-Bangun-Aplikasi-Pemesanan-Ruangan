<?php // app/views/rooms/index.php ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Ruangan</title>

  <!-- Hubungkan ke CSS -->
  <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>

  <header>
    <h1>Daftar Ruangan</h1>
  </header>

  <div class="container">
    <div class="card">
      <div class="d-flex justify-between align-center mb-3">
        <h2>Data Ruangan</h2>
        <a href="create.php" class="btn">Tambah Ruangan</a>
      </div>

      <form method="get" action="/" class="mb-3">
        <input type="hidden" name="p" value="rooms">
        <div class="form-group">
          <input name="q" value="<?= e($q ?? '') ?>" placeholder="Cari nama atau kode..." class="input-text" />
          <button class="btn btn-outline">Cari</button>
        </div>
      </form>

      <table class="table">
        <thead>
          <tr>
            <th>Kode</th>
            <th>Nama</th>
            <th>Kategori</th>
            <th>Kap.</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($rows as $r): ?>
          <tr>
            <td><?= e($r['code']) ?></td>
            <td><?= e($r['name']) ?></td>
            <td><?= e($r['category_name']) ?></td>
            <td><?= e($r['capacity']) ?></td>
            <td>
              <a href="/?p=rooms_edit&id=<?= $r['id'] ?>" class="btn btn-warning">Edit</a>
              <a href="/?p=rooms_delete&id=<?= $r['id'] ?>" class="btn btn-danger" onclick="return confirm('Hapus?')">Hapus</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <?php $pages = max(1, ceil($total / $perPage)); ?>
      <div class="pagination">
        <?php for($i=1;$i<=$pages;$i++): ?>
          <a class="<?= $i==$page ? 'active' : '' ?>" href="/?p=rooms&page=<?= $i ?><?= $q ? '&q='.urlencode($q): '' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    </div>
  </div>

  <footer>
    <p>© <?= date('Y') ?> Sistem Ruangan</p>
  </footer>

</body>
</html>
