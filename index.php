<?php 
$env = parse_ini_file('.env');
// Koneksi ke database
// $host = 'localhost';
// $user = 'andre';
// $pass = '#Admin123';
// $db = 'mydb';

$host = $env['DB_HOST'];
$user = $env['DB_USER'];
$pass = $env['DB_PASS'];
$db = $env['DB_NAME'];

$conn = new mysqli($host, $user, $pass, $db);

// Debug koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

try {
    // Handle form submission untuk menambah todo baru
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['title'])) {
            $title = $_POST['title'];

            // Insert ke tabel todo
            $stmt = $conn->prepare("INSERT INTO todo (title, done) VALUES (?, 0)");
            if (!$stmt) {
                throw new Exception("Error prepare statement: " . $conn->error);
            }
            $stmt->bind_param("s", $title);
            if (!$stmt->execute()) {
                throw new Exception("Error execute statement: " . $stmt->error);
            }
            $stmt->close();

            // Refresh halaman setelah submit
            header("Location: index.php");
            exit;
        } elseif (isset($_POST['update_status'])) {
            // Handle perubahan status todo
            $id = $_POST['id'];
            $currentStatus = $_POST['current_status'];

            // Toggle status
            $newStatus = $currentStatus ? 0 : 1;

            // Update status di database
            $stmt = $conn->prepare("UPDATE todo SET done = ? WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Error prepare statement: " . $conn->error);
            }
            $stmt->bind_param("ii", $newStatus, $id);
            if (!$stmt->execute()) {
                throw new Exception("Error execute statement: " . $stmt->error);
            }
            $stmt->close();

            // Refresh halaman setelah update status
            header("Location: index.php");
            exit;
        } elseif (isset($_POST['delete_todo'])) {
            // Handle penghapusan todo
            $id = $_POST['id'];

            // Hapus todo berdasarkan id
            $stmt = $conn->prepare("DELETE FROM todo WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Error prepare statement: " . $conn->error);
            }
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                throw new Exception("Error execute statement: " . $stmt->error);
            }
            $stmt->close();

            // Refresh halaman setelah delete
            header("Location: index.php");
            exit;
        }
    }

    // Ambil semua todo dari database
    $todos = $conn->query("SELECT * FROM todo");
    if (!$todos) {
        throw new Exception("Error query: " . $conn->error);
    }
} catch (Exception $e) {
    // Tampilkan error
    echo "Terjadi kesalahan: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Todo List</h1>
        
        <!-- Tombol untuk membuka modal -->
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addTodoModal">
            Tambah Todo
        </button>

        <!-- Tabel Todo -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $todos->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo $row['done'] ? 'Selesai' : 'Belum Selesai'; ?></td>
                    <td>
                        <!-- Tombol untuk ubah status -->
                        <form method="POST" action="index.php" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="current_status" value="<?php echo $row['done']; ?>">
                            <button type="submit" name="update_status" class="btn btn-warning">
                                <?php echo $row['done'] ? 'Tandai Belum Selesai' : 'Tandai Selesai'; ?>
                            </button>
                        </form>

                        <!-- Tombol untuk hapus todo -->
                        <form method="POST" action="index.php" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_todo" class="btn btn-danger">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Tambah Todo -->
    <div class="modal fade" id="addTodoModal" tabindex="-1" aria-labelledby="addTodoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="index.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTodoModalLabel">Tambah Todo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>

<?php 
$conn->close(); 
?>
