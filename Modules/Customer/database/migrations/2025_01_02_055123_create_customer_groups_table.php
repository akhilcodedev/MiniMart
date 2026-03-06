    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up()
        {

            if (!Schema::hasTable('customer_groups')) {
                Schema::create('customer_groups', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->text('description')->nullable();
                    $table->decimal('discount_rate', 5, 2)->default(0.00);
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable('customer_groups_maps')) {
                Schema::create('customer_groups_maps', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('customer_id')->constrained()->onDelete('cascade');
                    $table->foreignId('group_id')->constrained('customer_groups')->onDelete('cascade');
                    $table->timestamps();
                });
            }

        }


        public function down()
        {
            Schema::dropIfExists('customer_groups_maps'); // Drop dependent table first
            Schema::dropIfExists('customer_groups');
        }
    };
