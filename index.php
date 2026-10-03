<?php
session_start();
if (!isset($_SESSION['pendaftar'])) $_SESSION['pendaftar'] = [];

$matkul = ['Pengembangan Aplikasi Website', 'Pemrograman Berorientasi Objek', 'Basis Data', 'Pemrograman Mobile'];
$errors = [];
$old = ['nama' => '', 'wa' => '', 'email' => '', 'matkul' => '', 'motivasi' => ''];
$view = (isset($_GET['page']) && $_GET['page'] === 'data') ? 'data' : 'form';
$card = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $_) $old[$k] = trim($_POST[$k] ?? '');

    // 1. Cek kolom kosong
    if ($old['nama'] === '')     $errors['nama'] = 'Nama lengkap tidak boleh kosong';
    if ($old['wa'] === '')       $errors['wa'] = 'Nomor WhatsApp tidak boleh kosong';
    if ($old['email'] === '')    $errors['email'] = 'Email institusi tidak boleh kosong';
    if ($old['matkul'] === '')   $errors['matkul'] = 'Pilihan mata kuliah praktikum wajib dipilih';
    if ($old['motivasi'] === '') $errors['motivasi'] = 'Motivasi mendaftar tidak boleh kosong';

    // 2. Cek format (hanya jika kolom terisi)
    if (!isset($errors['nama']) && !preg_match('/^[\p{L}\s.\'-]+$/u', $old['nama']))
        $errors['nama'] = 'Nama hanya boleh berupa huruf';
    if (!isset($errors['wa']) && !preg_match('/^(0|62)[0-9]{8,13}$/', $old['wa']))
        $errors['wa'] = 'Nomor WhatsApp harus diawali 0 atau 62 (hanya angka)';
    if (!isset($errors['email']) && !filter_var($old['email'], FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'Format email institusi tidak valid';
    if (!isset($errors['matkul']) && !in_array($old['matkul'], $matkul, true))
        $errors['matkul'] = 'Pilihan mata kuliah tidak valid';

    if (!$errors) {
        $card = $old;
        $card['noreg'] = 'REG-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
        $_SESSION['pendaftar'][] = $card;
        $view = 'card';
    }
}
function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function err($errors, $k) { return isset($errors[$k]) ? '<div class="error">* ' . e($errors[$k]) . '</div>' : ''; }
function row($label, $value) {
    return '<div class="id-card-row"><div class="id-card-label">' . e($label) . '</div><div class="id-card-value">' . $value . '</div></div>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pendaftaran Asisten Praktikum</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>

<?php if ($view === 'card'): ?>
  <div class="id-card">
    <div class="id-card-header">
      <img src="logo.png" alt="" class="logo" onerror="this.style.display='none'">
      <h2>Kartu Registrasi</h2>
      <p>Calon Asisten Praktikum Laboratorium</p>
    </div>
    <div class="alert alert-info"><b>Berhasil!</b> Data pendaftaran telah diterima.</div>
    <div class="id-card-body">
      <?= row('Nama Lengkap', e($card['nama'])) ?>
      <?= row('No. WhatsApp', e($card['wa'])) ?>
      <hr class="id-card-divider">
      <?= row('Email Institusi', e($card['email'])) ?>
      <?= row('Mata Kuliah', e($card['matkul'])) ?>
      <?= row('Motivasi', nl2br(e($card['motivasi']))) ?>
      <div style="text-align:center;margin-top:18px"><span class="id-card-badge">Pendaftaran Berhasil</span></div>
    </div>
    <a class="btn-kembali" href="index.php">Kembali ke Form</a>
    <div class="id-card-footer">Nomor Registrasi: <?= e($card['noreg']) ?> • Dibuat otomatis oleh sistem</div>
  </div>

<?php elseif ($view === 'data'): ?>
  <div class="id-card">
    <div class="id-card-header">
      <img src="logo.png" alt="" class="logo" onerror="this.style.display='none'">
      <h2>Data Pendaftar</h2>
      <p>Total: <?= count($_SESSION['pendaftar']) ?> calon asisten praktikum</p>
    </div>
    <div class="id-card-body">
      <?php if (!$_SESSION['pendaftar']): ?>
        <div class="alert alert-info" style="text-align:center">Belum ada pendaftar.</div>
      <?php endif; ?>
      <?php foreach (array_reverse($_SESSION['pendaftar']) as $i => $p): ?>
        <?php if ($i > 0): ?><hr class="id-card-divider"><?php endif; ?>
        <?= row('No. Registrasi', e($p['noreg'])) ?>
        <?= row('Nama Lengkap', e($p['nama'])) ?>
        <?= row('No. WhatsApp', e($p['wa'])) ?>
        <?= row('Email Institusi', e($p['email'])) ?>
        <?= row('Mata Kuliah', e($p['matkul'])) ?>
        <?= row('Motivasi', nl2br(e($p['motivasi']))) ?>
      <?php endforeach; ?>
    </div>
    <a class="btn-kembali" href="index.php">Kembali ke Form</a>
  </div>

<?php else: ?>
  <div class="container">
    <img src="logo.png" alt="" class="logo" onerror="this.style.display='none'">
    <h2>Pendaftaran Asisten Praktikum</h2>
    <p class="subtitle">Laboratorium Enterprise Application Development</p>

    <?php if ($errors): ?>
      <div class="alert alert-danger"><b>Pendaftaran gagal!</b> Harap perbaiki data yang salah.</div>
    <?php endif; ?>

    <form method="post" action="index.php">
      <div class="form-group">
        <label for="nama">Nama Lengkap <span class="required">*</span></label>
        <input type="text" id="nama" name="nama" placeholder="Contoh: Budi Santoso" value="<?= e($old['nama']) ?>">
        <?= err($errors, 'nama') ?>
      </div>

      <div class="form-group">
        <label for="wa">Nomor WhatsApp <span class="required">*</span></label>
        <input type="text" id="wa" name="wa" placeholder="Contoh: 081234567890" value="<?= e($old['wa']) ?>">
        <?= err($errors, 'wa') ?>
      </div>

      <div class="form-group">
        <label for="email">Email Institusi <span class="required">*</span></label>
        <input type="email" id="email" name="email" placeholder="Contoh: budi@university.ac.id" value="<?= e($old['email']) ?>">
        <?= err($errors, 'email') ?>
      </div>

      <div class="form-group">
        <label for="matkul">Pilihan Mata Kuliah Praktikum <span class="required">*</span></label>
        <select id="matkul" name="matkul">
          <option value="">-- Pilih Mata Kuliah --</option>
          <?php foreach ($matkul as $m): ?>
            <option value="<?= e($m) ?>" <?= $old['matkul'] === $m ? 'selected' : '' ?>><?= e($m) ?></option>
          <?php endforeach; ?>
        </select>
        <?= err($errors, 'matkul') ?>
      </div>

      <div class="form-group">
        <label for="motivasi">Motivasi Mendaftar <span class="required">*</span></label>
        <textarea id="motivasi" name="motivasi" placeholder="Tuliskan alasan kamu ingin menjadi asisten praktikum..."><?= e($old['motivasi']) ?></textarea>
        <?= err($errors, 'motivasi') ?>
      </div>

      <div class="button-container">
        <button type="submit">Daftar Sekarang</button>
        <a class="btn-lihat-data" href="index.php?page=data">Lihat Data Pendaftar</a>
      </div>
    </form>
  </div>
<?php endif; ?>

</body>
</html>