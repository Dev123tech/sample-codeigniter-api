<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
      
        $this->db->table('users')->truncate(); 
        $this->db->table('users')->insertBatch([[
            'firstname' => 'customer1',
            'lastname' => 'lastname',
            'email'    => 'customer@yopmail.com',
            "password" => md5("customer@123"),
            'mobile'    => '9999999999',
            'role_id'    => 2, // customer
        ],[
            'firstname' => 'customer2',
            'lastname' => 'lastname',
            'email'    => 'customer2@yopmail.com',
            "password" => md5("customer@123"),
            'mobile'    => '9999999990',
            'role_id'    => 2, // customer
        ],[
            'firstname' => 'admin',
            'lastname' => 'lastname',
            'email'    => 'admin@yopmail.com',
            "password" => md5("admin@123"),
            'mobile'    => '9999999991',
            'role_id'    => 1, // customer
        ],[
            'firstname' => 'admin2',
            'lastname' => 'lastname',
            'email'    => 'admin2@yopmail.com',
            "password" => md5("admin@123"),
            'mobile'    => '9999999992',
            'role_id'    => 1, // admin
        ],[
            'firstname' => 'driver',
            'lastname' => 'lastname',
            'email'    => 'driver@yopmail.com',
            "password" => md5("driver@123"),
            'mobile'    => '9999999993',
            'role_id'    => 3, // driver
        ],[
            'firstname' => 'driver2',
            'lastname' => 'lastname',
            'email'    => 'driver2@yopmail.com',
            "password" => md5("driver@123"),
            'mobile'    => '9999999994',
            'role_id'    => 3, // driver
        ]]); 
    }
}
