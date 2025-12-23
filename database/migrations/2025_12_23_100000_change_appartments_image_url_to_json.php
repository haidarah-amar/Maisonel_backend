<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert existing non-JSON image_url strings into JSON arrays, then alter column to JSON
        // For records where image_url is a plain string, wrap it in a JSON array.
        DB::statement("UPDATE `appartments` SET `image_url` = JSON_ARRAY(`image_url`) WHERE `image_url` IS NOT NULL AND JSON_VALID(`image_url`) = 0");

        // Modify column type to JSON (MySQL) — keep it nullable
        DB::statement("ALTER TABLE `appartments` MODIFY `image_url` JSON NULL");
    }

    public function down(): void
    {
        // Convert JSON arrays back to string by taking first element (best-effort), then alter column to VARCHAR
        DB::statement("UPDATE `appartments` SET `image_url` = JSON_UNQUOTE(JSON_EXTRACT(`image_url`, '$[0]')) WHERE `image_url` IS NOT NULL AND JSON_VALID(`image_url`) = 1");
        DB::statement("ALTER TABLE `appartments` MODIFY `image_url` VARCHAR(5000) NULL");
    }
};

