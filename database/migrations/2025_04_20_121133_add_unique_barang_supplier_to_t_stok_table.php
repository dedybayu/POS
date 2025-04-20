<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('t_stok', function (Blueprint $table) {
            $table->unique(['barang_id', 'supplier_id'], 'unique_barang_supplier');
        });
    }
    
    public function down()
    {
        Schema::table('t_stok', function (Blueprint $table) {
            $table->dropUnique('unique_barang_supplier');
        });
    }
    
};
