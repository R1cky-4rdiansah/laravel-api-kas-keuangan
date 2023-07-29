<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kas', function (Blueprint $table) {
            $table->increments('id_kas')->bigInteger()->autoIncrement();
            $table->date('tanggal');
            $table->string('deskripsi', 100);
            $table->char('gambar', 250)->nullable(true);
            $table->bigInteger('pemasukkan')->nullable(true);
            $table->bigInteger('pengeluaran')->nullable(true);
            $table->bigInteger('saldo')->nullable(true);
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
        Schema::dropIfExists('kas');
    }
}
