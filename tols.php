<?php
session_start();

// ===== 1. EDIT LIST HARGA DI SINI MANG =====
$list_barang = [
    ["id" => 1, "nama" => "jazy kretek", "harga" => 9000],
    ["id" => 2, "nama" => "win kretek bery", "harga" => 11000],
    ["id" => 3, "nama" => "garam merah 16", "harga" => 18400],
    ["id" => 4, "nama" => "climax campur", "harga" => 27500],
    ["id" => 5, "nama" => "camel ungu 16", "harga" => 26800],
["id" => 6, "nama" => "sampoerna mild 16", "harga" => 35100],
["id" => 7, "nama" => "boro filter 12", "harga" => 24000],
["id" => 8, "nama" => "boro filter 20", "harga" => 38950],
["id" => 9, "nama" => "surya 16", "harga" => 35000],
["id" => 10, "nama" => "surya 12", "harga" => 26000],
["id" => 11, "nama" => "signatur", "harga" => 26000],
["id" => 12, "nama" => "garfit", "harga" => 26000],
["id" => 13, "nama" => "magnum filfer", "harga" => 26250],
["id" => 14, "nama" => "magnum bintang", "harga" => 23100],
["id" => 15, "nama" => "djarum coklat", "harga" => 16350],
["id" => 16, "nama" => "76 apel", "harga" => 14500],






];
// ===========================================

if(!isset($_SESSION['keranjang'])){ $_SESSION['keranjang'] = []; }

$total_semua = 0;

// Proses Tambah
if(isset($_POST['tambah'])){
    $id_barang = $_POST['id_barang'];
    $qty = (int)$_POST['qty'];
    $satuan = $_POST['satuan'];

    foreach($list_barang as $barang){
        if($barang['id'] == $id_barang){
            $subtotal = $barang['harga'] * $qty;
            $_SESSION['keranjang'][] = [
                "nama" => $barang['nama'],
                "harga" => $barang['harga'],
                "qty" => $qty,
                "satuan" => $satuan,
                "subtotal" => $subtotal
            ];
        }
    }
    header("Location: ".$_SERVER['PHP_SELF']); // biar ga double submit
    exit;
}

// Proses Download CSV
if(isset($_POST['download'])){
    $filename = "laporan_harga_". date("Ymd_His"). ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['No', 'Nama Barang', 'Harga Modal', 'Satuan', 'Qty', 'Subtotal']);

    $no=1; $grand=0;
    foreach($_SESSION['keranjang'] as $k){
        $satuan = $k['satuan'] ?? '-'; // <-- KUNCI: kalau ga ada satuan isi -
        fputcsv($output, [$no++, $k['nama'], $k['harga'], $satuan, $k['qty'], $k['subtotal']]);
        $grand += $k['subtotal'];
    }
    fputcsv($output, ['', '', 'GRAND TOTAL', $grand]);
    fclose($output);
    exit();
}

// Reset
if(isset($_POST['reset'])){ 
    $_SESSION['keranjang'] = []; 
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

function rupiah($angka){
    return "Rp ". number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tools List Harga + Satuan</title>
<style>
    *{box-sizing:border-box; font-family: 'Segoe UI', Arial;}
    body{background:#f4f6f9; padding:20px;}
    .container{max-width:1000px; margin:auto; background:white; padding:25px; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.1);}
    h1{text-align:center; color:#2c3e50;}
    form{display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px;}
    select, input, button{padding:10px; font-size:16px; border:1px solid #ccc; border-radius:5px;}
    select{flex:2;} input[name="satuan"]{flex:1;} input[name="qty"]{width:100px;}
    button{background:#27ae60; color:white; border:none; cursor:pointer; font-weight:bold;}
    button.download{background:#2980b9;} button.reset{background:#e74c3c;}
    table{width:100%; border-collapse: collapse; margin-top:20px;}
    th{background:#3498db; color:white; padding:12px;}
    td{padding:10px; border-bottom:1px solid #ddd; text-align:center;}
    .total{font-size:22px; font-weight:bold; text-align:center; margin-top:15px; color:#c0392b;}
</style>
</head>
<body>

<div class="container">
    <h1>🧮 Tools Hitung Harga + Satuan</h1>

    <form method="post">
        <select name="id_barang" required>
            <option value="">-- Pilih Barang --</option>
            <?php foreach($list_barang as $b):?>
                <option value="<?= $b['id']?>"><?= $b['nama']?> - <?= rupiah($b['harga'])?></option>
            <?php endforeach;?>
        </select>
        <input type="text" name="satuan" placeholder="Satuan: pcs/bungkus/kg" required>
        <input type="number" name="qty" placeholder="Qty" min="0" value="0" required>
        <button type="submit" name="tambah">+ Tambah</button>
    </form>

    <?php if(count($_SESSION['keranjang']) > 0):?>
    <table>
        <tr><th>No</th><th>Nama Barang</th><th>Harga Modal</th><th>Satuan</th><th>Qty</th><th>Subtotal</th></tr>
        <?php $no=1; foreach($_SESSION['keranjang'] as $k): 
            $total_semua += $k['subtotal'];
            $satuan = $k['satuan'] ?? '-'; // <-- KUNCI: cegah error lagi
        ?>
        <tr>
            <td><?= $no++?></td>
            <td><?= $k['nama']?></td>
            <td><?= rupiah($k['harga'])?></td>
            <td><?= $satuan?></td>
            <td><?= $k['qty']?></td>
            <td><?= rupiah($k['subtotal'])?></td>
        </tr>
        <?php endforeach;?>
    </table>

    <div class="total">GRAND TOTAL: <?= rupiah($total_semua)?></div>

    <form method="post" style="justify-content:flex-end;">
        <!--button type="submit" name="download" class="download">📥 Download CSV</button-->
        <button type="submit" name="reset" class="reset">🗑️ Reset Semua</button>
    </form>
<p style= "text-align: center;font-size: 20px; color: black;">powered by <a style="text-decoration: none;color: black;" href="https://gobed007.github.io/index.html">DhimGobed</a></p>

    <?php endif;?>
</div>

</body>
</html>
