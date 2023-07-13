<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\RawSql;
use CodeIgniter\Database\Migration;

class RoleMaster extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'           => 'VARCHAR',
                'constraint'       => 100,  
                'default' => NULL              
            ],
            'slug' => [
                'type'           => 'VARCHAR',
                'constraint'       => 50,  
                'default' => NULL              
            ],
            'description' => [
                'type'           => 'TEXT',  
                'default' => NULL              
            ],
            'status' => [
                'type'           => 'TINYINT',
                'constraint'       => 1,
                'default' => 1
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('role_master');
    }

    public function down()
    {
        $this->forge->dropTable('role_master');
    }
}
