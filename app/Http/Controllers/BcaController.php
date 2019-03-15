<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BcaController extends Controller
{
    public function index(){
        $corp_id = "BCAAPI2016";
        $client_key = "e089c870-b407-481f-aa67-be6838c54a89";
        $client_secret = "61fdda86-c1bb-4361-a6a0-6d928cee1f95";
        $apikey = "38e7dfb4-8849-4ad9-b231-d2b477c692a7";
        $secret = "03942231-e348-44f4-b8de-5e00cea01b64";

        $bca = new \Bca\BcaHttp($corp_id, $client_key, $client_secret, $apikey, $secret);

        // Request Login dan dapatkan nilai OAUTH
        $response = $bca->httpAuth();

        // Cek hasil response berhasil atau tidak
        $access_token = $response->body->access_token;


        $amount = '50000.00';

        // Nilai akun bank anda
        $nomorakun = '0201245680';

        // Nilai akun bank yang akan ditransfer
        $nomordestinasi = '0201245681';

        // Nomor PO, silahkan sesuaikan
        $nomorPO = '12345/PO/2017';

        // Nomor Transaksi anda, Silahkan generate sesuai kebutuhan anda
        $nomorTransaksiID = '00000001';

        $remark1 = 'Transfer Test Using Odenktools BCA';

        $remark2 = 'Online Transfer Using Odenktools BCA';

        $response = $bca->fundTransfers($access_token,
                            $amount,
                            $nomorakun,
                            $nomordestinasi,
                            $nomorPO,
                            $remark1,
                            $remark2,
                            $nomorTransaksiID);

        // Cek hasil response berhasil atau tidak
        echo json_encode($response->body);
    }
}
