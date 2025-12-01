<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$servername = "localhost";
$username = "username"; // Ganti dengan username MySQL Anda
$password = "password"; // Ganti dengan password MySQL Anda
$dbname = "dazx_finance"; // Ganti dengan nama database Anda

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// GET - Load semua data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT * FROM transactions ORDER BY id DESC";
    $result = $conn->query($sql);
    
    $transactions = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
    }
    
    echo json_encode($transactions);
}

// POST - Simpan data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action']) && $input['action'] === 'save_all') {
        // Hapus semua data lama
        $conn->query("DELETE FROM transactions");
        
        // Simpan semua data baru
        foreach ($input['data'] as $item) {
            $stmt = $conn->prepare("INSERT INTO transactions (item, specs, buy_price, sell_price, date) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiis", 
                $item['item'],
                $item['specs'],
                $item['buyPrice'],
                $item['sellPrice'],
                $item['date']
            );
            $stmt->execute();
        }
        
        echo json_encode(["success" => true, "message" => "Data saved successfully"]);
    } else {
        // Simpan data tunggal
        $item = $input['item'];
        $specs = $input['specs'];
        $buyPrice = $input['buyPrice'];
        $sellPrice = $input['sellPrice'];
        $date = date('Y-m-d H:i:s');
        
        $stmt = $conn->prepare("INSERT INTO transactions (item, specs, buy_price, sell_price, date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiis", $item, $specs, $buyPrice, $sellPrice, $date);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Data saved successfully"]);
        } else {
            echo json_encode(["error" => "Failed to save data"]);
        }
    }
}

// DELETE - Hapus data
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'];
    
    $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Data deleted successfully"]);
    } else {
        echo json_encode(["error" => "Failed to delete data"]);
    }
}

$conn->close();
?>