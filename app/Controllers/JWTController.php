<?php

namespace App\Controllers;

use App\Models\Users;
use Firebase\JWT\JWT;
use \Firebase\JWT\KEY;
use CodeIgniter\HTTP\Request;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;

class JWTController extends BaseController
{
    use ResponseTrait;
  
    /*
    * JWT refresh token will re generate new token by providing old token
    *
    */
    public function refreshToken()
    {        
        $validation = \Config\Services::validation();
        $check = $this->validate([
            'user_id' => [
                'rules'	=> ['required'],
            ],
            'refreshToken' => [
               'rules'	=> ['required'],
           ],
           /*'access_token' => [
            'rules'	=> ['required'],
            ],*/
        ]);
        
        if (!$check || getenv('JWT_SECRET') != $this->request->getVar('refreshToken')) {  // validation errors occurs
            return $this->respondCreated(['status' => false, 'message' => 'Validation errors occurs..!','data' => [],'errors' => $this->validator->getErrors()]);
        }  
               
        $userModel = new Users(); 
        $userData = $userModel->where('id', $this->request->getVar('user_id'))->first(); 
        if(isset($userData) && !empty($userData)) {
            return $this->respondCreated(['status' => true, 'message' => 'Token refreshed successfully..! User details found successfully..!','data' => $userData,'access_token' => JWT::encode(['iss' => 'localhost','aud' => 'localhost','exp' => time() + 3600,'data' => ['user_id' => $userData['id'],'mobile' => $userData['mobile']]],getenv('JWT_SECRET'),'HS256'),"refresh_token" => getenv('JWT_SECRET'),"token_type" => "Bearer","expires_in" => time() + 3600]);              
        }else{
            return $this->respondCreated(['status' => false, 'message' => 'User details not found..!','data' => [],'errors' => $this->validator->getErrors()]);
        }
        
    }
}

