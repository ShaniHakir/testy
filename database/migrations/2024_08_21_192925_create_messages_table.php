<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyMessagesTable extends Migration
{
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('conversation_id')->after('id');
            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
            $table->dropForeign(['recipient_id']);
            $table->dropColumn('recipient_id');
        });
    }

    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('recipient_id')->after('sender_id');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
            $table->dropForeign(['conversation_id']);
            $table->dropColumn('conversation_id');
        });
    }
}