<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'mst_cerita';

    private const INDEX = 'mst_cerita_total_share_idx';

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE) || ! Schema::hasColumn(self::TABLE, 'total_share')) {
            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table) {
            if (! Schema::hasIndex(self::TABLE, self::INDEX)) {
                $table->index('total_share', self::INDEX);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table) {
            if (Schema::hasIndex(self::TABLE, self::INDEX)) {
                $table->dropIndex(self::INDEX);
            }
        });
    }
};
