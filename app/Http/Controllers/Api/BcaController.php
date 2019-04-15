<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Wallet;
use App\Models\WalletDetail;
use App\Models\UserVirtualAccount;
use App\Models\PaymentRequestLog;
use App\Http\Resources\Wallet as WalletResource;

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
        return json_encode($response->body);
    }

    public function bills(Request $request){
        try{
            //get parameters
            $billsInquiryRequest = $request->json()->all();

            $billsInquiryResponse = $this->validateBillsInquiryRequest($billsInquiryRequest);

            //get user wallet based on virtual account no.
            $userVirtualAccount = UserVirtualAccount::with('wallet')->where('virtual_account_no',$billsInquiryRequest['CustomerNumber'])->first();

            //no billing method used, so just return no bill response

            //return response
            $response = $billsInquiryRequest;
            $response["InquiryStatus"] = $billsInquiryResponse['inquiryStatus'];
            $response["InquiryReason"] = $billsInquiryResponse['inquiryReason'];
            $response["CustomerName"] = "Customer BCA Virtual Account";
            $response["CurrencyCode"] = "IDR";
            $response["TotalAmount"] = "0.00";
            $response["SubCompany"] = "00000";
            $response["DetailBills"]= null;
            $response["FreeTexts"]= null;

            return response()->json($response);
        }
        catch(\Exception $e){
            return response()->json([
                'code' => '-1',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function validateBillsInquiryRequest($request){
        $billsInquiryResponse = array();
        if($request['CompanyCode'] == '' || is_null($request['CompanyCode'])){
            $billsInquiryResponse['inquiryStatus'] = '01';
            $billsInquiryResponse['inquiryReason'] = array(
                "Indonesian" => "Company Code tidak boleh kosong",
                "English" => "Company Code must not be empty"
            );
        }
        elseif($request['CustomerNumber'] == '' || is_null($request['CustomerNumber'])){
            $billsInquiryResponse['inquiryStatus'] = '01';
            $billsInquiryResponse['inquiryReason'] = array(
                "Indonesian" => "Customer Number tidak boleh kosong",
                "English" => "Customer Number must not be empty"
            );
        }
        elseif($request['RequestID'] == '' || is_null($request['RequestID'])){
            $billsInquiryResponse['inquiryStatus'] = '01';
            $billsInquiryResponse['inquiryReason'] = array(
                "Indonesian" => "Request ID tidak boleh kosong",
                "English" => "Request ID must not be empty"
            );
        }
        elseif($request['ChannelType'] == '' || is_null($request['ChannelType'])){
            $billsInquiryResponse['inquiryStatus'] = '01';
            $billsInquiryResponse['inquiryReason'] = array(
                "Indonesian" => "Channel Type tidak boleh kosong",
                "English" => "Channel Type must not be empty"
            );
        }
        elseif($request['TransactionDate'] == '' || is_null($request['TransactionDate'])){
            $billsInquiryResponse['inquiryStatus'] = '01';
            $billsInquiryResponse['inquiryReason'] = array(
                "Indonesian" => "Transaction Date tidak boleh kosong",
                "English" => "Transaction Date must not be empty"
            );
        }
        else{
            try{
                if(Carbon::createFromFormat('d/m/Y H:i:s', $request['TransactionDate']) == false){
                    $billsInquiryResponse['inquiryStatus'] = '01';
                    $billsInquiryResponse['inquiryReason'] = array(
                        "Indonesian" => "Format Transaction Date salah",
                        "English" => "Wrong Transaction Date format"
                    );
                }
                else{
                    $billsInquiryResponse['inquiryStatus'] = '00';
                    $billsInquiryResponse['inquiryReason'] = array(
                        "Indonesian" => "Sukses",
                        "English" => "Success"
                    );
                }
            }
            catch(\Exception $e){
                $paymentResponse['inquiryStatus'] = '01';
                $paymentResponse['inquiryReason'] = array(
                    "Indonesian" => "Format Transaction Date salah",
                    "English" => "Wrong Transaction Date format"
                );
                return $paymentResponse;
            }
        }
        return $billsInquiryResponse;
    }

    //payments non-bill
    public function payments(Request $request){
        try{
            //get parameters
            $paymentRequest = $request->json()->all();

            //validate parameters
            $paymentResponse = $this->validatePaymentRequest($paymentRequest);

            if($paymentResponse['paymentFlagStatus'] == '00'){
                $transactionDate = Carbon::createFromFormat('d/m/Y H:i:s', $paymentRequest['TransactionDate'])->format('Y-m-d H:i:s');

                //add payment request log
                $paymentRequestLog = new PaymentRequestLog;
                $paymentRequestLog->company_code = $paymentRequest['CompanyCode'];
                $paymentRequestLog->customer_number = $paymentRequest['CustomerNumber'];
                $paymentRequestLog->request_id = $paymentRequest['RequestID'];
                $paymentRequestLog->channel_type = $paymentRequest['ChannelType'];
                $paymentRequestLog->customer_name = $paymentRequest['CustomerName'];
                $paymentRequestLog->currency_code = $paymentRequest['CurrencyCode'];
                $paymentRequestLog->paid_amount = $paymentRequest['PaidAmount'];
                $paymentRequestLog->total_amount = $paymentRequest['TotalAmount'];
                $paymentRequestLog->sub_company = $paymentRequest['SubCompany'];
                $paymentRequestLog->transaction_date = $transactionDate;
                $paymentRequestLog->reference = $paymentRequest['Reference'];
                $paymentRequestLog->flag_advice = $paymentRequest['FlagAdvice'];
                $paymentRequestLog->additional_data = isset($paymentRequest['Additionaldata']) ? $paymentRequest['Additionaldata'] : '';
                $paymentRequestLog->save();

                //get user wallet based on virtual account no.
                $userVirtualAccount = UserVirtualAccount::with('wallet')->where('virtual_account_no',$paymentRequest['CustomerNumber'])->first();

                //data exists
                if($userVirtualAccount){
                    $paymentRequest['CustomerName'] = $userVirtualAccount->virtual_account_name;

                    // top up amount in user wallet
                    $userWallet = $userVirtualAccount->wallet;
                    $userWallet->timestamps = false; //disable laravel timestamp feature
                    $userWallet->amount = $userWallet->Amount + $paymentRequest['PaidAmount'];
                    $userWallet->save();

                    // get latest wallet detail balance
                    $latestWalletDetail = WalletDetail::where('Id',$userWallet->Id)->orderBy('Detail_wallet_id','desc')->first();
                    $latestBalance = 0;
                    if($latestWalletDetail){
                        $latestBalance = $latestWalletDetail->balance;
                    }

                    // add wallet detail
                    $userWalletDetail = new WalletDetail;
                    $userWalletDetail->Id = $userWallet->Id;
                    $userWalletDetail->Date_transaction = $transactionDate;
                    $userWalletDetail->Amount =  $paymentRequest['PaidAmount'];
                    $userWalletDetail->Notes =  $paymentRequest['Reference'];
                    $userWalletDetail->tipe_dana =  1;
                    $userWalletDetail->User_id =  $userWallet->User_id;
                    $userWalletDetail->kode_transaksi = $paymentRequest['RequestID'];
                    $userWalletDetail->loan_organizer_id = 0;
                    $userWalletDetail->balance = $latestBalance + $paymentRequest['PaidAmount'];
                    $userWalletDetail->timestamps = false; //disable laravel timestamp feature
                    $userWalletDetail->save();

                    $response = $paymentRequest;
                    $response['PaymentFlagStatus'] = $paymentResponse['paymentFlagStatus'];
                    $response['PaymentFlagReason'] =$paymentResponse['paymentFlagReason'];
                    return response()->json($response);
                }
                //error, no data found!
                else{
                    $response = $paymentRequest;
                    $response['PaymentFlagStatus'] = "01";
                    $response['PaymentFlagReason'] = array(
                        "Indonesian" => "Customer Number tidak ditemukan",
                        "English" => "Customer Number not found"
                    );
                    return response()->json($response);
                }
            }
            else{
                $response = $paymentRequest;
                $response['PaymentFlagStatus'] = $paymentResponse['paymentFlagStatus'];
                $response['PaymentFlagReason'] =$paymentResponse['paymentFlagReason'];
                return response()->json($response);
            }
        }
        catch(\Exception $e){
            return response()->json([
                'code' => '-1',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function validatePaymentRequest($request){
        $paymentResponse = array();
        if($request['CompanyCode'] == '' || is_null($request['CompanyCode'])){
            $paymentResponse['paymentFlagStatus'] = '01';
            $paymentResponse['paymentFlagReason'] = array(
                "Indonesian" => "Company Code tidak boleh kosong",
                "English" => "Company Code must not be empty"
            );
        }
        elseif($request['CustomerNumber'] == '' || is_null($request['CustomerNumber'])){
            $paymentResponse['paymentFlagStatus'] = '01';
            $paymentResponse['paymentFlagReason'] = array(
                "Indonesian" => "Customer Number tidak boleh kosong",
                "English" => "Customer Number must not be empty"
            );
        }
        elseif($request['RequestID'] == '' || is_null($request['RequestID'])){
            $paymentResponse['paymentFlagStatus'] = '01';
            $paymentResponse['paymentFlagReason'] = array(
                "Indonesian" => "Request ID tidak boleh kosong",
                "English" => "Request ID must not be empty"
            );
        }
        elseif($request['ChannelType'] == '' || is_null($request['ChannelType'])){
            $paymentResponse['paymentFlagStatus'] = '01';
            $paymentResponse['paymentFlagReason'] = array(
                "Indonesian" => "Channel Type tidak boleh kosong",
                "English" => "Channel Type must not be empty"
            );
        }
        elseif($request['CustomerName'] == '' || is_null($request['CustomerName'])){
            $paymentResponse['paymentFlagStatus'] = '01';
            $paymentResponse['paymentFlagReason'] = array(
                "Indonesian" => "Customer Name tidak boleh kosong",
                "English" => "Customer Name must not be empty"
            );
        }
        elseif($request['CurrencyCode'] == '' || is_null($request['CurrencyCode'])){
            $paymentResponse['paymentFlagStatus'] = '01';
            $paymentResponse['paymentFlagReason'] = array(
                "Indonesian" => "Customer Name tidak boleh kosong",
                "English" => "Customer Name must not be empty"
            );
        }
        elseif($request['PaidAmount'] == '' || is_null($request['PaidAmount'])){
            $paymentResponse['paymentFlagStatus'] = '01';
            $paymentResponse['paymentFlagReason'] = array(
                "Indonesian" => "Paid Amount tidak boleh kosong",
                "English" => "Paid Amount must not be empty"
            );
        }
        elseif($request['TotalAmount'] == '' || is_null($request['TotalAmount'])){
            $paymentResponse['paymentFlagStatus'] = '01';
            $paymentResponse['paymentFlagReason'] = array(
                "Indonesian" => "Total Amount tidak boleh kosong",
                "English" => "Total Amount must not be empty"
            );
        }
        elseif($request['TransactionDate'] == '' || is_null($request['TransactionDate'])){
            $paymentResponse['paymentFlagStatus'] = '01';
            $paymentResponse['paymentFlagReason'] = array(
                "Indonesian" => "Transaction Date tidak boleh kosong",
                "English" => "Transaction Date must not be empty"
            );
        }
        elseif($request['FlagAdvice'] == '' || is_null($request['FlagAdvice'])){
            $paymentResponse['paymentFlagStatus'] = '01';
            $paymentResponse['paymentFlagReason'] = array(
                "Indonesian" => "Flag Advice tidak boleh kosong",
                "English" => "Flag Advice must not be empty"
            );
        }
        elseif($request['FlagAdvice'] != 'Y' && $request['FlagAdvice'] != 'N'){
            $paymentResponse['paymentFlagStatus'] = '01';
            $paymentResponse['paymentFlagReason'] = array(
                "Indonesian" => "Flag Advice hanya dapat bernilai Y atau N",
                "English" => "Flag Advice value must be either Y or N"
            );
        }
        elseif($request['SubCompany'] == '' && is_null($request['SubCompany'])){
            $paymentResponse['paymentFlagStatus'] = '01';
            $paymentResponse['paymentFlagReason'] = array(
                "Indonesian" => "Sub Company tidak boleh kosong",
                "English" => "Sub Company must not be empty"
            );
        }
        elseif($request['Reference'] == '' && is_null($request['Reference'])){
            $paymentResponse['paymentFlagStatus'] = '01';
            $paymentResponse['paymentFlagReason'] = array(
                "Indonesian" => "Reference tidak boleh kosong",
                "English" => "Reference must not be empty"
            );
        }
        else{
            try{
                if(Carbon::createFromFormat('d/m/Y H:i:s', $request['TransactionDate']) == false){
                    $paymentResponse['paymentFlagStatus'] = '01';
                    $paymentResponse['paymentFlagReason'] = array(
                        "Indonesian" => "Format Transaction Date salah",
                        "English" => "Wrong Transaction Date format"
                    );
                }
                else{
                    $paymentResponse['paymentFlagStatus'] = '00';
                    $paymentResponse['paymentFlagReason'] = array(
                        "Indonesian" => "Sukses",
                        "English" => "Success"
                    );
                }
            }
            catch(\Exception $e){
                $paymentResponse['paymentFlagStatus'] = '01';
                $paymentResponse['paymentFlagReason'] = array(
                    "Indonesian" => "Format Transaction Date salah",
                    "English" => "Wrong Transaction Date format"
                );
                return $paymentResponse;
            }

        }
        return $paymentResponse;
    }




}
