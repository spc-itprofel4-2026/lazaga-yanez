<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWeatherReadingTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
 'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
 'city' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
 'notes' => ['type' => 'TEXT', 'null' => true],
 'temperature' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => false],
 'humidity' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
 'recorded_at' => ['type' => 'DATETIME', 'null' => false],
 'is_alert' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('weather_readings');
    }

    public function down()
    {
        $this->forge->dropTable('weather_readings');
    }
}
