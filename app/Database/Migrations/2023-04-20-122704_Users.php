<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\RawSql;
use CodeIgniter\Database\Migration;

class Users extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'firstname' => [
                'type'           => 'VARCHAR',
                'constraint'       => 100,  
                'default' => NULL              
            ],
            'lastname' => [
                'type'           => 'VARCHAR',
                'constraint'       => 100,  
                'default' => NULL              
            ],
            'email' => [
                'type'           => 'VARCHAR',
                'constraint'       => 100,
                'unique' => true,
                'default' => NULL
            ],
            'mobile' => [
                'type'           => 'VARCHAR',
                'constraint'       => 13,
                'unique' => true,  
                'default' => NULL              
            ],
            'gender' => [
                'type'           => 'CHAR',
                'constraint'       => 6,  
                'default' => NULL              
            ],
            'birthdate' => [
                'type'           => 'DATE', 
                'default' => NULL              
            ],
            'role_id' => [
                'type'           => 'INT',
                'constraint'       => 11,
                'default' => NULL
            ],
            'password' => [
                'type'           => 'VARCHAR',
                'constraint'       => 255,
                'default' => NULL
            ],
            'otp' => [
                'type'           => 'CHAR',
                'constraint'       => 6,
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
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
