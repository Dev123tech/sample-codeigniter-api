<?php

namespace App\Validation;

class dobRules
{
	public function dobRules(string $value, string $options = null): bool
    {
        $dob = date_create($value);

       
        if ($dob > date_create('today')) {
          
            return false;
        }

        return true;
    }

}										