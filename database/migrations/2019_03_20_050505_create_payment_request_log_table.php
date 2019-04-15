<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentRequestLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_request_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('company_code',5);
            $table->string('customer_number',18);
            $table->string('request_id',30);
            $table->string('channel_type',4);
            $table->string('customer_name',30);
            $table->string('currency_code',3);
            $table->decimal('paid_amount',17,2);
            $table->decimal('total_amount',17,2);
            $table->string('sub_company',5);
            $table->string('transaction_date',19);
            $table->string('reference',15);
            $table->string('flag_advice',1);
            $table->string('additional_data',999);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_request_log');
    }
}
