<?php // app/views/rooms/create.php
// Pastikan $cats di-pass dari controller, dan set_old/flash digunakan
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Tambah Ruangan</h3>
    <a href="index.php" class="btn btn-secondary">Kembali</a>
  </div>

  <form id="roomForm" method="post" enctype="multipart/form-data" action="/?p=rooms_create">
    <div class="mb-3">
      <label class="form-label">Nama Ruangan</label>
      <input name="name" class="form-control" value="<?= e(old('name')) ?>" required minlength="3">
    </div>

    <div class="mb-3">
      <label class="form-label">Kategori</label>
      <select name="category_id" class="form-select" required>
        <option value="">-- Pilih --</option>
        <?php foreach($cats as $c): ?>
          <option value="<?= $c['id'] ?>" <?= (old('category_id') == $c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Kapasitas</label>
      <input name="capacity" type="number" class="form-control" value="<?= e(old('capacity') ?: 10) ?>" required min="1">
    </div>

    <div class="mb-3">
      <label class="form-label">Gambar (jpg/png/webp) — max 2MB</label>
      <input name="image" type="file" accept="image/jpeg,image/png,image/webp" class="form-control">
    </div>

    <button class="btn btn-primary">Simpan</button>
  </form>
</div>
