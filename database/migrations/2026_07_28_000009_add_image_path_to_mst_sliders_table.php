<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mst_sliders')) {
            return;
        }

        $this->expandImageUrlColumn();

        if (! Schema::hasColumn('mst_sliders', 'image_path')) {
            Schema::table('mst_sliders', function (Blueprint $table) {
                $table->string('image_path')->nullable()->after('image_url');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('mst_sliders') || ! Schema::hasColumn('mst_sliders', 'image_path')) {
            return;
        }

        Schema::table('mst_sliders', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }

    private function expandImageUrlColumn(): void
    {
        if (! Schema::hasColumn('mst_sliders', 'image_url')) {
            return;
        }

        match (DB::getDriverName()) {
            'pgsql' => DB::statement('alter table "mst_sliders" alter column "image_url" type varchar(2048)'),
            'mysql' => DB::statement('alter table `mst_sliders` modify `image_url` varchar(2048) not null'),
            default => null,
        };
    }
};
