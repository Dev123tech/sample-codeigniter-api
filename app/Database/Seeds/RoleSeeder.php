<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
      
        $this->db->table('role_master')->truncate(); 
        $this->db->table('role_master')->insertBatch([[
            'name' => 'admin',
            'slug' => 'admin',
            'description' => 'Admin role management',

        ],[
            'name' => 'customer',
            'slug' => 'customer',
            'description' => 'Customer role management',
        ],[
            'name' => 'driver',
            'slug' => 'driver',
            'description' => 'Driver role management',
        ]]); 
    }
}
