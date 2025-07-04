<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        DB::statement("ALTER TABLE sales MODIFY COLUMN sale_type ENUM('sale', 'transfer', 'local') DEFAULT 'sale'");
    }

    public function down(): void {
        DB::statement("ALTER TABLE sales MODIFY COLUMN sale_type ENUM('sale', 'transfer') DEFAULT 'sale'");
    }
};