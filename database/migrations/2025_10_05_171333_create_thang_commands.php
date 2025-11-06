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
        Schema::create('thang_commands', function (Blueprint $table) {

            $table->id();

            $table->foreignId('owning_thang_id')
                ->nullable(false)
                ->comment("the thang this command belongs to")
                ->index()
                ->constrained('thangs')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->default(null)
                ->comment("A parent command")
                ->index()
                ->constrained('thang_commands')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();


            $table->boolean('is_async')->default(false)->nullable(false)
                ->comment('if true then this command itself is async');

            $table->timestamps();

            $table->uuid('ref_uuid')
                ->unique()
                ->nullable(false)
                ->comment("the name of the command");

            $table->uuid('parent_ref_uuid')
                ->index()
                ->nullable()
                ->default(null)
                ->comment("the name of the parent");

            $table->boolean('bubble_exceptions')->default(false)->nullable(false)
                ->comment('if true then exceptions are thrown and not logged ');



        });

        DB::statement('ALTER TABLE thang_commands ALTER COLUMN ref_uuid SET DEFAULT uuid_generate_v4();');


        DB::statement("ALTER TABLE thang_commands ALTER COLUMN created_at SET DEFAULT NOW();");

        DB::statement("
            CREATE TRIGGER update_modified_time BEFORE UPDATE ON thang_commands FOR EACH ROW EXECUTE PROCEDURE thang_update_modified_column();
        ");

        DB::statement("CREATE TYPE type_of_cmd_status AS ENUM (
            'cmd_waiting',
            'cmd_running',
            'cmd_fail',
            'cmd_success',
            'cmd_error'
            );");

        DB::statement("ALTER TABLE thang_commands Add COLUMN command_status type_of_cmd_status NOT NULL default 'cmd_waiting';");

        Schema::table('thang_commands', function (Blueprint $table) {

            $table->jsonb('command_args')->nullable()->default(null)
                ->comment('stores the data to pass to the target class');

            $table->jsonb('staging_data_from_children')->nullable()->default(null)
                ->comment('stores the data from the children of this command');

            $table->jsonb('command_errors')->nullable()->default(null)
                ->comment('Exception chain info');

            $table->jsonb('command_tags')->nullable()->default(null)
                ->comment('stores array of key value pairs');

            $table->string('command_class')->nullable()->default(null)
                ->comment('the classFQN of the interface that is called with the params');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thang_commands');
        DB::statement("DROP TYPE type_of_cmd_status;");
    }
};
