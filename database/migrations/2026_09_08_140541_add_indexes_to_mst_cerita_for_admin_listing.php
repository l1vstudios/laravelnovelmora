<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'mst_cerita';

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table) {
            $this->addIndexIfMissing($table, 'created_at', 'mst_cerita_created_at_idx');
            $this->addIndexIfMissing($table, ['status', 'created_at'], 'mst_cerita_status_created_at_idx');
            $this->addIndexIfMissing($table, ['id_kategori', 'created_at'], 'mst_cerita_kategori_created_at_idx');
            $this->addIndexIfMissing($table, ['recomendation', 'created_at'], 'mst_cerita_recomendation_created_at_idx');
            $this->addIndexIfMissing($table, ['wajib_dibaca', 'created_at'], 'mst_cerita_wajib_created_at_idx');
            $this->addIndexIfMissing($table, 'positions_index', 'mst_cerita_positions_index_idx');
            $this->addIndexIfMissing($table, 'total_read', 'mst_cerita_total_read_idx');
            $this->addIndexIfMissing($table, 'total_vote', 'mst_cerita_total_vote_idx');
            $this->addIndexIfMissing($table, 'judul', 'mst_cerita_judul_idx');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'mst_cerita_created_at_idx');
            $this->dropIndexIfExists($table, 'mst_cerita_status_created_at_idx');
            $this->dropIndexIfExists($table, 'mst_cerita_kategori_created_at_idx');
            $this->dropIndexIfExists($table, 'mst_cerita_recomendation_created_at_idx');
            $this->dropIndexIfExists($table, 'mst_cerita_wajib_created_at_idx');
            $this->dropIndexIfExists($table, 'mst_cerita_positions_index_idx');
            $this->dropIndexIfExists($table, 'mst_cerita_total_read_idx');
            $this->dropIndexIfExists($table, 'mst_cerita_total_vote_idx');
            $this->dropIndexIfExists($table, 'mst_cerita_judul_idx');
        });
    }

    private function addIndexIfMissing(Blueprint $table, string|array $columns, string $index): void
    {
        $columns = (array) $columns;

        foreach ($columns as $column) {
            if (! Schema::hasColumn(self::TABLE, $column)) {
                return;
            }
        }

        if (! Schema::hasIndex(self::TABLE, $index)) {
            $table->index($columns, $index);
        }
    }

    private function dropIndexIfExists(Blueprint $table, string $index): void
    {
        if (Schema::hasIndex(self::TABLE, $index)) {
            $table->dropIndex($index);
        }
    }
};
