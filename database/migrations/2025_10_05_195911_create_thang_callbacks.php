<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('thang_callbacks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owning_hook_id')
                ->nullable(false)
                ->comment("the hook that spawned this callback")
                ->index()
                ->constrained('thang_hooks')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();


            $table->foreignId('source_command_id')
                ->nullable(false)
                ->comment("The command this callback is used")
                ->index()
                ->constrained('thang_commands')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();



            $table->uuid('source_command_ref')
                ->index()
                ->nullable()
                ->comment("to link this callback to the command in the thang.");

            $table->uuid('owning_hook_ref')
                ->index()
                ->nullable()
                ->comment("to link this callback to the hook");



            $table->integer('callback_http_code')->nullable()->default(null)
                ->comment('When the callback was made, what was the http code from that url');

            $table->timestamps();


            $table->uuid('ref_uuid')
                ->unique()
                ->nullable(false)
                ->comment("used for display and id outside the code");

            $table->jsonb('callback_data')
                ->nullable()->default(null)
                ->comment("The data sent back by the callback");


        });


        DB::statement("CREATE TYPE type_of_thang_callback_status AS ENUM (
            'building',
            'running',
            'manual',
            'successful',
            'fail',
            'error'
            );");

        DB::statement("ALTER TABLE thang_callbacks Add COLUMN callback_status type_of_thang_callback_status NOT NULL default 'building';");



        DB::statement("ALTER TABLE thang_callbacks ALTER COLUMN created_at SET DEFAULT NOW();");

        DB::statement(  "
            CREATE TRIGGER update_modified_time BEFORE UPDATE ON thang_callbacks FOR EACH ROW EXECUTE PROCEDURE thang_update_modified_column();
        ");

        DB::statement('ALTER TABLE thang_callbacks ALTER COLUMN ref_uuid SET DEFAULT uuid_generate_v4();');



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thang_callbacks');
        DB::statement("DROP TYPE type_of_thang_callback_status;");
    }
};
