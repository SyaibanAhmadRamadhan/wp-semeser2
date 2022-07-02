<?php
$koneksi = mysqli_connect('localhost','root','','tugas_semester2');

function data($query){
    global $koneksi;
    $simpan = mysqli_query($koneksi,$query);
    $array = [];
    while ($array2 = mysqli_fetch_assoc($simpan)){
        $array[]=$array2;
    }
    return $array;
}

function masukankeranjang($keranjang){
    global $koneksi;
    
    $id_barang  = $keranjang ['id'];
    $id_user = $_SESSION['id_user'];
    $jumlah = $keranjang['jumlah'];
    $name = $keranjang['name'];
    // $kode = $keranjang['kode'];
    $price = $keranjang['price'];
    $stock = $keranjang['stock'];
    $gambar = $keranjang['gambar'];
    $total = $jumlah * $price;
    // $sisa = $stock - $jumlah;

    
    if ($stock < 1) {
        echo "<script>alert('Stock Habis Pembelian Gagal');
        document.location.href='detail.php?id=$id_barang'</script>";
        exit;
    }

    if ($stock < $jumlah) {
        echo "<script>alert('Pembelian Melampaui Stock');
        document.location.href='detail.php?id=$id_barang'</script>";
        exit;
    }
    if ($jumlah < 1) {
        return[
        'error'=>true,
        'pesan'=>'PEMESANAN HARUS LEBIH DARI 0'
        ];
        exit;
    }
    $resul = mysqli_query($koneksi,"SELECT * FROM keranjang WHERE id_barang=$id_barang AND id_user=$id_user");
    if(mysqli_num_rows($resul)>0){
        echo "<script>alert('Barang Sudah Ada Dikeranjang');
        document.location.href='detail.php?id=$id_barang'</script>";
        exit;
    }
    if ($jumlah > 0 ) {
        echo "<script>
        alert('pesanan sudah dimasukan kedalam keranjang');
       </script>";
    }
    
    $result = "INSERT INTO keranjang VALUES ('','$id_user','$id_barang','$name','$jumlah','$price','$total','$gambar',1)";
    // $simpan = "UPDATE sepeda SET stock='$sisa' WHERE id=$id";
    mysqli_query($koneksi,$result);
    // mysqli_query($koneksi,$simpan);
    return mysqli_affected_rows($koneksi);
}

// hapus
function hapus ($id){
    global $koneksi;
    mysqli_query($koneksi,"DELETE FROM keranjang WHERE id = $id");
    return mysqli_affected_rows($koneksi);
}

function hapuspembayaran ($id){
    global $koneksi;
    $pembeli = mysqli_query($koneksi,"SELECT * FROM pembeli WHERE id_pembeli=$id");
    $arraypembeli = mysqli_fetch_assoc($pembeli);
    $id_pembeli = $arraypembeli['id_pembeli'];
    $stok = mysqli_query($koneksi,"SELECT * FROM pembeli_detail WHERE id_pembeli=$id_pembeli");
    while ($arraystock = mysqli_fetch_assoc($stok)){
        $jumlah = $arraystock['jumlah'];
        $id_produk = $arraystock['id_produk'];
        $produk = mysqli_query($koneksi,"SELECT * FROM sepeda WHERE id=$id_produk");
        $arrayproduk = mysqli_fetch_assoc($produk);
        $stock = $arrayproduk['stock'];
        $updatestock = $stock + $jumlah;
        $update = "UPDATE sepeda SET stock=$updatestock WHERE id=$id_produk";
        mysqli_query($koneksi,$update);
    }
    mysqli_query($koneksi,"DELETE FROM pembeli_detail WHERE id_pembeli = $id");
    mysqli_query($koneksi,"DELETE FROM pembeli WHERE id_pembeli = $id");
    return mysqli_affected_rows($koneksi);
}

function register ($data){
    global $koneksi;
    $username = htmlspecialchars($data['username']);
    $email = htmlspecialchars($data['email']);
    $password = htmlspecialchars($data['password']);
    $password2 = htmlspecialchars($data['password2']);

    $Qusername = mysqli_query($koneksi,"SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($Qusername)>0){
        echo "<script>alert('username sudah tersedia');
        document.location.href='register.php'</script>";
        exit;
    }

    if ($password != $password2){
        echo "<script>alert('password tidak sama');
        document.location.href='register.php'</script>
        </script>";
        exit;
    }

    $password = password_hash($password, PASSWORD_DEFAULT);
    mysqli_query($koneksi,"INSERT INTO users (id,username, email, password) VALUES ('','$username','$email','$password')");
    echo "<script>
    alert('pendaftaran berhasil');
    document.location.href='login.php';</script>";
}


function login($data){
    global $koneksi;
    $username = $data['username'];
    $password = $data['password'];
    $Qusername = mysqli_query($koneksi,"SELECT * FROM users WHERE username='$username' && status = 0");
    // $username = mysqli_query($koneksi,"SELECT * FROM users WHERE username='$username' && status = 0");
    // if (mysqli_num_rows($username)>0){
    //     echo "<script>alert('aktivasi akun terlebih dahulu');
    // document.location.href='register.php'</script>";
    // exit;
    // }
    if (mysqli_num_rows($Qusername)>0){
        $AQusername = mysqli_fetch_assoc($Qusername);
        if (password_verify($password,$AQusername['password'])){
            $_SESSION['id_user']=$AQusername['id'];
            $_SESSION['username']=$username;
            $_SESSION['login']=true;
            header("Location:index.php");
            exit;
        }
    }else {
        echo "<script>alert('username tidak tersedia');
    document.location.href='register.php'</script>";
    exit;
    }
}

function krjg ($data){
    global $koneksi;
    $id_barang = $data['id_barang'];
    $sepeda = mysqli_query($koneksi, "SELECT * FROM sepeda WHERE id=$id_barang");
    $array = mysqli_fetch_assoc($sepeda);
    $name = $array['name'];
    $id_keranjang = $data['id_keranjang'];
    $total = $data['total'];
    $stock = $array['stock'];
    $price = $data['price'];
    $subtotal = $total * $price;
    if ($stock < 1) {
        echo "
            <script>
            alert('stock $name habis');
            document.location.href='keranjang.php';
            </script>";
        exit;
    }
    if ($stock < $total) {
        echo "
            <script>
            alert('stock melampau batas');
            document.location.href='keranjang.php';
            </script>";
        exit;
    }
    $update = "UPDATE keranjang SET 
                total = $total,
                total_harga = $subtotal
            WHERE id=$id_keranjang
                ";
    if (mysqli_query($koneksi, $update)) {
        echo "
            <script>
            alert('data berhasil diubah!');
            document.location.href='keranjang.php';
            </script>";
    } else {
        echo "
            <script>
            alert('tidak ada perubahan data');
            document.location.href='keranjang.php';
            </script>";
    }
}

function checkout (){
    global $koneksi;
    $id_user = $_SESSION['id_user'];
    $keranjang = mysqli_query($koneksi, "SELECT * FROM keranjang WHERE id_user=$id_user");
    while ($array_keranjang = mysqli_fetch_assoc($keranjang)){
        $id_barang = $array_keranjang['id_barang'];
        // var_dump($id_barang);
        $sepeda = mysqli_query($koneksi, "SELECT * FROM sepeda WHERE id=$id_barang");
        $array = mysqli_fetch_assoc($sepeda);
        $name = $array ['name'];
        $stock = $array['stock'];
        $total = $array_keranjang['total'];
        // var_dump($stock); die;
        if ($stock < 1 ){
            echo "
                <script>
                alert('stock barang ($name) habis');
                document.location.href='../keranjang.php';
                </script>";
            exit;
        }else {
            echo "
                <script>
                document.location.href='../checkout.php';
                </script>";
            exit;
        }
    }
}

function beli($data){
    global $koneksi;
    date_default_timezone_set('Asia/Jakarta');
    // $totalsemua = $data['total_price'];
    $grand_total = $data['grand_total'];
    $id_bank = $data['bank'];
    $name = $data['name'];
    $no_hp = $data['no_hp'];
    $provinsi = $data['provinsi'];
    $postal_code = $data['postal_code'];
    $kota = $data['kota'];
    $estimasi = $data['estimasi'];
    $ekspedisi = $data['ekspedisi'];
    $paket_yang_diambil = $data['paket_yang_diambil'];
    $ongkir = $data['ongkir'];
    $totalsemua = $ongkir + $grand_total;
    $alamat_lengkap = $data['alamatlengkap'];
    $product = $data['product'];
    $id_user = $_SESSION['id_user'];
    // $user = mysqli_query($koneksi,"SELECT * FROM pembeli");
    // $array1 = mysqli_fetch_assoc($user);
    // $_SESSION['id_pembeli']=$array1['id_pembeli'];
    // $user1 = $array1['id_user'];
    // if($user1 === $id_user){
    //     echo "
    //         <script>
    //         alert('silahkan lakukan pembayaran anda terlebi dahulu');
    //         document.location.href='pembayaran.php';
    //         </script>";
    //     exit;
    // }
    $dateout = date('d-m-Y h:i:s', strtotime('+15 second'));
    $tanggal = date("Y-m-d h:i:s");
    $simpan = mysqli_query ($koneksi,"INSERT INTO pembeli (id_pembeli,id_user,id_bank,nama,produk,no_hp,date,dateout,provinsi,kota_kabupaten,kode_pos,alamat,ekspedisi,paket_ekspedisi,estimasi_pengiriman,ongkir,total_produk,totalharga)
                                                    VALUES ('',$id_user,$id_bank,'$name','$product','$no_hp','$tanggal','$dateout','$provinsi','$kota','$postal_code','$alamat_lengkap','$ekspedisi','$paket_yang_diambil','$estimasi','$ongkir',$grand_total,$totalsemua)");
                                                    
    $pembeli = data("SELECT * FROM pembeli WHERE id_user=$id_user");
    foreach ($pembeli as $x){
        $id_pembeli = $x['id_pembeli']; 
    }
    $keranjang_barang = data("SELECT * FROM keranjang WHERE id_user=$id_user");
    foreach ($keranjang_barang as $i){
        $id_produk = $i['id_barang'];
        $total = $i['total'];
        mysqli_query ($koneksi,"INSERT INTO pembeli_detail (id_pembeli_detail,id_pembeli,id_user,id_produk,jumlah)
                                                            VALUES ('',$id_pembeli,$id_user,$id_produk,$total)");
    }
    $keranjang_stock = mysqli_query($koneksi,"SELECT * FROM pembeli_detail WHERE id_user=$id_user AND id_pembeli=$id_pembeli");
    while ($array = mysqli_fetch_assoc($keranjang_stock)){
        $a = $array['jumlah'];
        $id = $array['id_produk'];
        $keranjang2 = mysqli_query($koneksi,"SELECT * FROM sepeda WHERE id=$id");
        $array1=mysqli_fetch_assoc($keranjang2);
        $stock2 = $array1['stock'];
        $sisa = $stock2 - $a;
        $update = "UPDATE sepeda SET stock=$sisa WHERE id=$id";
        mysqli_query($koneksi,$update);    
    }
//     mysqli_query($koneksi,"DELETE FROM keranjang WHERE id_user = $id_user"); 
    echo"
        <script>
            alert('pembelian berhasil');
            document.location.href='pembayaran.php';
        </script>
    ";
}

function invoice($query){
    global $koneksi;
    
    $query = mysqli_query($koneksi,$query);
    while ($datearray = mysqli_fetch_assoc($query)){
        date_default_timezone_set('Asia/Jakarta');
        $dateout1=$datearray['dateout'];
        $date = $datearray['date'];
        $id_pembeli = $datearray['id_pembeli'];
        $tanggal = date("Y-m-d h:i:s");
        if(strtotime($tanggal) > strtotime($dateout1)){
            $stok = mysqli_query($koneksi,"SELECT * FROM pembeli_detail WHERE id_pembeli=$id_pembeli");
            while ($arraystock = mysqli_fetch_assoc($stok)){
                $jumlah = $arraystock['jumlah'];
                $id_produk = $arraystock['id_produk'];
                $produk = mysqli_query($koneksi,"SELECT * FROM sepeda WHERE id=$id_produk");
                $arrayproduk = mysqli_fetch_assoc($produk);
                $stock = $arrayproduk['stock'];
                $updatestock = $stock + $jumlah;
                $update = "UPDATE sepeda SET stock=$updatestock WHERE id=$id_produk";
                mysqli_query($koneksi,$update);
            }
            mysqli_query($koneksi,"DELETE FROM pembeli WHERE date='$date'");
            mysqli_query($koneksi,"DELETE FROM pembeli_detail WHERE id_pembeli = $id_pembeli");    
        }
    }
}

// beli langsung
function belilangsung($keranjang){
    global $koneksi;
    
    $id_barang  = $keranjang ['id'];
    $id_user = $_SESSION['id_user'];
    $jumlah = $keranjang['jumlah'];
    $name = $keranjang['name'];
    // $kode = $keranjang['kode'];
    $price = $keranjang['price'];
    $stock = $keranjang['stock'];
    $gambar = $keranjang['gambar'];
    $total = $jumlah * $price;
    // $sisa = $stock - $jumlah;
    
    if ($stock < 1) {
        echo "<script>alert('Stock Habis Pembelian Gagal');
        document.location.href='detail.php?id=$id_barang'</script>";
        exit;
    }

    if ($stock < $jumlah) {
        echo "<script>alert('Pembelian Melampaui Stock');
        document.location.href='detail.php?id=$id_barang'</script>";
        exit;
    }
    if ($jumlah < 1) {
        return[
            'error'=>true,
            'pesan'=>'PEMESANAN HARUS LEBIH DARI 1'
        ];
        exit;
    }
    // $resul = mysqli_query($koneksi,"SELECT * FROM keranjang WHERE id_barang=$id_barang AND id_user=$id_user");
    // if(mysqli_num_rows($resul)>0){
        //     document.location.href='detail.php?id=$id_barang'</script>";
        //     echo "<script>alert('Barang Sudah Ada Dikeranjang');
    //     exit;
    // }
    // if ($jumlah > 0 ) {
    //     echo "<script>
    //     alert('pesanan sudah dimasukan kedalam keranjang');
    //     header.location.href=detail.php;</script>";
    // }
    
    $result = "INSERT INTO keranjang VALUES ('','$id_user','$id_barang','$name','$jumlah','$price','$total','$gambar',0)";
    // $simpan = "UPDATE sepeda SET stock='$sisa' WHERE id=$id";
    mysqli_query($koneksi,$result);
    // mysqli_query($koneksi,$update);
    // mysqli_query($koneksi,$simpan);
    return mysqli_affected_rows($koneksi);
}

function hapusstatus1 ($query){
    global $koneksi;
    mysqli_query($query,"DELETE FROM keranjang WHERE status = 0");
}
?>