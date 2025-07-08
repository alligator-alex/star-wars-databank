<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const int PUBLISHED_OLD = 2;
    private const int PUBLISHED_NEW = 1;

    private const int DRAFT_OLD = 1;
    private const int DRAFT_NEW = 0;

    public function up(): void
    {
        DB::table('factions')->where('status', '=', self::DRAFT_OLD)->update(['status' => self::DRAFT_NEW]);
        DB::table('factions')->where('status', '=', self::PUBLISHED_OLD)->update(['status' => self::PUBLISHED_NEW]);

        DB::table('manufacturers')->where('status', '=', self::DRAFT_OLD)->update(['status' => self::DRAFT_NEW]);
        DB::table('manufacturers')->where('status', '=', self::PUBLISHED_OLD)->update(['status' => self::PUBLISHED_NEW]);

        DB::table('media')->where('status', '=', self::DRAFT_OLD)->update(['status' => self::DRAFT_NEW]);
        DB::table('media')->where('status', '=', self::PUBLISHED_OLD)->update(['status' => self::PUBLISHED_NEW]);

        DB::table('vehicles')->where('status', '=', self::DRAFT_OLD)->update(['status' => self::DRAFT_NEW]);
        DB::table('vehicles')->where('status', '=', self::PUBLISHED_OLD)->update(['status' => self::PUBLISHED_NEW]);
    }

    public function down(): void
    {

        DB::table('factions')->where('status', '=', self::PUBLISHED_NEW)->update(['status' => self::PUBLISHED_OLD]);
        DB::table('factions')->where('status', '=', self::DRAFT_NEW)->update(['status' => self::DRAFT_OLD]);

        DB::table('manufacturers')->where('status', '=', self::PUBLISHED_NEW)->update(['status' => self::PUBLISHED_OLD]);
        DB::table('manufacturers')->where('status', '=', self::DRAFT_NEW)->update(['status' => self::DRAFT_OLD]);

        DB::table('media')->where('status', '=', self::PUBLISHED_NEW)->update(['status' => self::PUBLISHED_OLD]);
        DB::table('media')->where('status', '=', self::DRAFT_NEW)->update(['status' => self::DRAFT_OLD]);

        DB::table('vehicles')->where('status', '=', self::PUBLISHED_NEW)->update(['status' => self::PUBLISHED_OLD]);
        DB::table('vehicles')->where('status', '=', self::DRAFT_NEW)->update(['status' => self::DRAFT_OLD]);
    }
};
