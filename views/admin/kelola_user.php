<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

requireRole('admin');

$pageTitle = 'Kelola User';
$activePage = 'kelola_user';

$pdo = getDBConnection();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    try {
        $username = sanitize($_POST['username']);
        $password = $_POST['password'];
        $nama_lengkap = sanitize($_POST['nama_lengkap']);
        $role = sanitize($_POST['role']);
        
        if (empty($username) || empty($password) || empty($nama_lengkap) || empty($role)) {
            throw new Exception('Semua field harus diisi!');
        }
        
        if (!in_array($role, ['siswa', 'admin', 'teknisi'])) {
            throw new Exception('Role tidak valid!');
        }
        
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            throw new Exception('Username sudah digunakan!');
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $hashedPassword, $nama_lengkap, $role]);
        
        $message = 'User berhasil ditambahkan!';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    try {
        $user_id = (int)$_POST['user_id'];
        
        if ($user_id === getCurrentUserId()) {
            throw new Exception('Tidak dapat menghapus akun Anda sendiri!');
        }
        
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        $message = 'User berhasil dihapus!';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    try {
        $user_id = (int)$_POST['user_id'];
        $username = sanitize($_POST['username']);
        $nama_lengkap = sanitize($_POST['nama_lengkap']);
        $role = sanitize($_POST['role']);
        $password = $_POST['password'];
        
        if (empty($username) || empty($nama_lengkap) || empty($role)) {
            throw new Exception('Semua field harus diisi!');
        }
        
        if (!in_array($role, ['siswa', 'admin', 'teknisi'])) {
            throw new Exception('Role tidak valid!');
        }
        
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
        $stmt->execute([$username, $user_id]);
        if ($stmt->fetch()) {
            throw new Exception('Username sudah digunakan!');
        }
        
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, nama_lengkap = ?, role = ? WHERE user_id = ?");
            $stmt->execute([$username, $hashedPassword, $nama_lengkap, $role, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, nama_lengkap = ?, role = ? WHERE user_id = ?");
            $stmt->execute([$username, $nama_lengkap, $role, $user_id]);
        }
        
        $message = 'User berhasil diupdate!';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

$sql = "SELECT user_id, username, nama_lengkap, role, created_at FROM users ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll();

$siswa_list = [];
$teknisi_list = [];
$admin_list = [];

foreach ($users as $user) {
    if ($user['role'] === 'siswa') {
        $siswa_list[] = $user;
    } elseif ($user['role'] === 'teknisi') {
        $teknisi_list[] = $user;
    } elseif ($user['role'] === 'admin') {
        $admin_list[] = $user;
    }
}

include '../partials/header.php';
?>

<style>
.tab-container {
    margin-bottom: 20px;
}

.tabs {
    display: flex;
    gap: 10px;
    border-bottom: 2px solid #ddd;
    margin-bottom: 20px;
}

.tab-button {
    padding: 10px 20px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    color: #666;
    border-bottom: 3px solid transparent;
    transition: all 0.3s;
}

.tab-button:hover {
    color: #333;
}

.tab-button.active {
    color: #007bff;
    border-bottom-color: #007bff;
    font-weight: bold;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
}

.form-group input,
.form-group select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #007bff;
}

.btn-group {
    display: flex;
    gap: 10px;
}

.btn-small {
    padding: 5px 10px;
    font-size: 12px;
}

.alert {
    padding: 12px 20px;
    margin-bottom: 20px;
    border-radius: 4px;
    font-weight: 500;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: white;
    padding: 30px;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    margin-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 10px;
}

.modal-header h2 {
    margin: 0;
    color: #333;
}

.close-modal {
    float: right;
    font-size: 28px;
    font-weight: bold;
    color: #aaa;
    cursor: pointer;
    line-height: 20px;
}

.close-modal:hover {
    color: #000;
}
</style>

<h1>Kelola User</h1>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="tab-container">
            <div class="tabs">
                <button class="tab-button active" data-tab="siswa">Siswa (<?= count($siswa_list) ?>)</button>
                <button class="tab-button" data-tab="teknisi">Teknisi (<?= count($teknisi_list) ?>)</button>
                <button class="tab-button" data-tab="admin">Admin (<?= count($admin_list) ?>)</button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div id="siswa-tab" class="tab-content active">
            <div style="margin-bottom: 20px;">
                <button class="btn btn-primary" onclick="openAddModal('siswa')">+ Tambah Siswa</button>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($siswa_list as $user): ?>
                            <tr>
                                <td>#<?= $user['user_id'] ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                                <td><?= formatDate($user['created_at']) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-secondary btn-small" onclick='openEditModal(<?= json_encode($user) ?>)'>Edit</button>
                                        <button class="btn btn-danger btn-small" onclick="confirmDelete(<?= $user['user_id'] ?>, '<?= htmlspecialchars($user['nama_lengkap']) ?>')">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($siswa_list)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #999;">Belum ada data siswa</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="teknisi-tab" class="tab-content">
            <div style="margin-bottom: 20px;">
                <button class="btn btn-primary" onclick="openAddModal('teknisi')">+ Tambah Teknisi</button>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teknisi_list as $user): ?>
                            <tr>
                                <td>#<?= $user['user_id'] ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                                <td><?= formatDate($user['created_at']) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-secondary btn-small" onclick='openEditModal(<?= json_encode($user) ?>)'>Edit</button>
                                        <button class="btn btn-danger btn-small" onclick="confirmDelete(<?= $user['user_id'] ?>, '<?= htmlspecialchars($user['nama_lengkap']) ?>')">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($teknisi_list)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #999;">Belum ada data teknisi</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="admin-tab" class="tab-content">
            <div style="margin-bottom: 20px;">
                <button class="btn btn-primary" onclick="openAddModal('admin')">+ Tambah Admin</button>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admin_list as $user): ?>
                            <tr>
                                <td>#<?= $user['user_id'] ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                                <td><?= formatDate($user['created_at']) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-secondary btn-small" onclick='openEditModal(<?= json_encode($user) ?>)'>Edit</button>
                                        <?php if ($user['user_id'] !== getCurrentUserId()): ?>
                                            <button class="btn btn-danger btn-small" onclick="confirmDelete(<?= $user['user_id'] ?>, '<?= htmlspecialchars($user['nama_lengkap']) ?>')">Hapus</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($admin_list)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #999;">Belum ada data admin</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close-modal" onclick="closeAddModal()">&times;</span>
            <h2 id="addModalTitle">Tambah User</h2>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="role" id="add_role" value="">
            
            <div class="form-group">
                <label for="add_username">Username *</label>
                <input type="text" id="add_username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="add_password">Password *</label>
                <input type="password" id="add_password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="add_nama_lengkap">Nama Lengkap *</label>
                <input type="text" id="add_nama_lengkap" name="nama_lengkap" required>
            </div>
            
            <div class="btn-group" style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close-modal" onclick="closeEditModal()">&times;</span>
            <h2>Edit User</h2>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="user_id" id="edit_user_id">
            
            <div class="form-group">
                <label for="edit_username">Username *</label>
                <input type="text" id="edit_username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="edit_password">Password (kosongkan jika tidak ingin mengubah)</label>
                <input type="password" id="edit_password" name="password">
            </div>
            
            <div class="form-group">
                <label for="edit_nama_lengkap">Nama Lengkap *</label>
                <input type="text" id="edit_nama_lengkap" name="nama_lengkap" required>
            </div>
            
            <div class="form-group">
                <label for="edit_role">Role *</label>
                <select id="edit_role" name="role" required>
                    <option value="siswa">Siswa</option>
                    <option value="teknisi">Teknisi</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <div class="btn-group" style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<form id="deleteForm" method="POST" action="" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="user_id" id="delete_user_id">
</form>

<script>
document.querySelectorAll('.tab-button').forEach(button => {
    button.addEventListener('click', function() {
        const tabName = this.getAttribute('data-tab');
        
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        this.classList.add('active');
        document.getElementById(tabName + '-tab').classList.add('active');
    });
});

function openAddModal(role) {
    const roleName = role === 'siswa' ? 'Siswa' : (role === 'teknisi' ? 'Teknisi' : 'Admin');
    document.getElementById('addModalTitle').textContent = 'Tambah ' + roleName;
    document.getElementById('add_role').value = role;
    document.getElementById('addModal').classList.add('active');
}

function closeAddModal() {
    document.getElementById('addModal').classList.remove('active');
    document.getElementById('add_username').value = '';
    document.getElementById('add_password').value = '';
    document.getElementById('add_nama_lengkap').value = '';
}

function openEditModal(user) {
    document.getElementById('edit_user_id').value = user.user_id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_nama_lengkap').value = user.nama_lengkap;
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_password').value = '';
    document.getElementById('editModal').classList.add('active');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}

function confirmDelete(userId, userName) {
    if (confirm('Apakah Anda yakin ingin menghapus user "' + userName + '"?\n\nSemua data terkait user ini akan ikut terhapus!')) {
        document.getElementById('delete_user_id').value = userId;
        document.getElementById('deleteForm').submit();
    }
}

window.onclick = function(event) {
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    
    if (event.target === addModal) {
        closeAddModal();
    }
    if (event.target === editModal) {
        closeEditModal();
    }
}
</script>

<?php include '../partials/footer.php'; ?>
