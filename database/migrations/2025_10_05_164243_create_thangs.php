<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('thangs', function (Blueprint $table) {

            $table->id();

            $table->foreignId('owning_namespace_id')
                ->nullable()
                ->comment("the namespace this thang is running under")
                ->index()
                ->constrained('user_namespaces')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->uuid('ref_uuid')
                ->unique()
                ->nullable(false)
                ->comment("used for display and id outside the code");

            $table->jsonb('finished_data')->nullable()->default(null)
                ->comment('stores the final data result');

        });

        DB::statement("CREATE TYPE type_of_thang_async_policy AS ENUM (
            'never_async',
            'always_async',
            'auto_async'
            );");

        DB::statement("ALTER TABLE thangs Add COLUMN thang_async_policy type_of_thang_async_policy NOT NULL default 'auto_async';");


        DB::statement("CREATE TYPE type_of_thang_save_policy AS ENUM (
            'never_save',
            'always_save',
            'auto_save'
            );");

        DB::statement("ALTER TABLE thangs Add COLUMN thang_save_policy type_of_thang_save_policy NOT NULL default 'auto_save';");


        DB::statement('ALTER TABLE thangs ALTER COLUMN ref_uuid SET DEFAULT uuid_generate_v4();');


        DB::statement("ALTER TABLE thangs ALTER COLUMN created_at SET DEFAULT NOW();");

        DB::statement("
            CREATE TRIGGER update_modified_time BEFORE UPDATE ON thangs FOR EACH ROW EXECUTE PROCEDURE thang_update_modified_column();
        ");



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thangs');
        DB::statement("DROP TYPE type_of_thang_async_policy;");
        DB::statement("DROP TYPE type_of_thang_save_policy;");
    }
};
