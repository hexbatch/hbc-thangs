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
        Schema::create('thang_hooks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owning_namespace_id')
                ->nullable()
                ->comment("the namespace this hook is registered under")
                ->index()
                ->constrained('user_namespaces')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->boolean('is_on')->default(true)->nullable(false)
                ->comment('if false then this hook is not used');

            $table->boolean('is_async')->default(false)->nullable(false)
                ->comment('if true then event called in queue');

            $table->boolean('is_pre')->default(false)->nullable(false)
                ->comment('if true then hook called before thang runs');


            $table->integer('hook_priority')
                ->nullable(false)->default(0)
                ->comment("the higher priority will run their callbacks first first")
                ->index()
            ;

            $table->timestamps();

            $table->uuid('ref_uuid')
                ->unique()
                ->nullable(false)
                ->comment("used for display and id outside the code");


            $table->jsonb('hook_data')
                ->nullable()->default(null)
                ->comment("This data is passed to the event");

            $table->jsonb('hook_tags')
                ->nullable()->default(null)
                ->comment("array of string tags to match up with the thing tags");

            $table->text('hook_notes')->nullable()->default(null)
                ->comment('optional notes');

            $table->string('hook_name',30)
                ->nullable()->default(null)
                ->comment('optional name that must be unique for owner if given');

            $table->string('event_name',50)
                ->nullable(false)
                ->index()
                ->comment('the event that is called when this hook is run');

            $table->unique(["owning_namespace_id","hook_name"]);

        });




        DB::statement("ALTER TABLE thang_hooks ALTER COLUMN created_at SET DEFAULT NOW();");

        DB::statement("
            CREATE TRIGGER update_modified_time BEFORE UPDATE ON thang_hooks FOR EACH ROW EXECUTE PROCEDURE thang_update_modified_column();
        ");

        DB::statement('ALTER TABLE thang_hooks ALTER COLUMN ref_uuid SET DEFAULT uuid_generate_v4();');




    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thang_hooks');
    }
};
