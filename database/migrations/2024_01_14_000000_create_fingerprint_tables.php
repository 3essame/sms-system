<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFingerprintTables extends Migration
{
    public function up()
    {
        Schema::create('fingerprintshifts', function (Blueprint $table) {
            $table->string('shifttype', 1)->nullable();
            $table->dateTime('datetimein')->nullable();
            $table->string('timein', 11)->nullable();
            $table->date('dateout')->nullable();
            $table->string('timeout', 11)->nullable();
            $table->string('shift', 1)->nullable();
            
            $table->unique(['shift', 'datetimein'], 'fingerprintshifts_uk1');
            
            // MySQL doesn't support CHECK constraints in older versions, but we'll add it
            // $table->check("shift IN ('A', 'B', 'C', 'D')");
        });

        Schema::create('fingerprint24', function (Blueprint $table) {
            $table->decimal('id', 12, 0)->primary();
            $table->string('printtype', 26);
            $table->string('printtime', 26);
            $table->date('printdate');
            $table->decimal('civilid', 14, 0);
            $table->decimal('masterid', 10, 0)->nullable();
            $table->decimal('userid', 10, 0)->nullable();
            $table->timestamp('crdate')->useCurrent();
            
            // $table->check("printtype IN ('f1', 'f2')");
        });

        Schema::create('empinfo', function (Blueprint $table) {
            $table->decimal('civilid', 10, 0)->primary();
            $table->decimal('filno', 10, 0)->nullable();
            $table->string('empname', 100);
            $table->decimal('secid', 10, 0);
            $table->timestamp('hiredate')->useCurrent();
            $table->string('sex', 8)->nullable();
            $table->string('card', 1)->default('A');
            $table->decimal('empid', 10, 0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('fingerprintshifts');
        Schema::dropIfExists('fingerprint24');
        Schema::dropIfExists('empinfo');
    }
}
