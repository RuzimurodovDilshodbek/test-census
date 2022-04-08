<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('fathers_name');
            $table->string('phone');
            $table->integer('status')->default(0);
            $table->date('birth_date');
            $table->integer('social_areas_id');
            $table->integer('region_id');
            $table->integer('city_id');
            $table->string('address');
            $table->string('passport')->unique();
            $table->string('tin')->unique();
            $table->integer('number');
            $table->integer('code');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('applications');
    }
}
