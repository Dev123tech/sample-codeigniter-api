<?php

namespace App\Controllers;

use App\Models\Roles;
use App\Models\Users;
use App\Models\VehicleCategory;
use Firebase\JWT\JWT;
use App\Models\Documents;
use App\Models\DriverDocuments;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;
use App\Models\VehicleBrand;
use App\Models\VehicleColor;
use App\Models\VehicleModel;
use App\Models\ComplainReason;
use App\Models\BookingCancelReason;
use App\Models\Complaint;
use App\Models\Faq;

class DriverAuthController extends BaseController
{
    use ResponseTrait;
    /*
    * login mobile is use for driver mobile exist or not if exist then generate JWT auth token and provide user details
    *
    */
    public function loginMobile()
    {        
        try{
        $validation = \Config\Services::validation();
        $check = $this->validate([
            'mobile' => [
                'rules'	=> ['required','min_length[13]','max_length[13]'],
           ],
        ]);
        if (!$check) {  // validation errors occurs
            return $this->respondCreated(['status' => false, 'message' => 'Validation errors occurs..!','data' => [],'errors' => $this->validator->getErrors()]);
        }
        // get role id 
        $roleModel = new Roles();
        $roleModel->select('id');
        $role_id = $roleModel->like('name','driver','both')->first();

         // get driver details
        $userModel = new Users();
        $userExist = $userModel->where('mobile',$this->request->getVar('mobile'))->first();
        if(isset($userExist) && !empty($userExist)){ // driver exist generate JWT auth token
            if($userExist['role_id'] == $role_id['id']){
                
                    $userModel->update($userExist['id'], [
                    'online' => 1,                
            ]);
            $userExist = $userModel->find($userExist['id']);
                return $this->respondCreated(['status' => true, 'message' => 'Driver details get successfully..! Login success ...!','data' => $userExist,'access_token' => JWT::encode(['iss' => 'localhost','aud' => 'localhost','exp' => time() + 3600,'data' => ['user_id' => $userExist['id'],'mobile' => $userExist['mobile'],'role' => $userExist['role_id']]],getenv('JWT_SECRET'),'HS256'),"refresh_token" => getenv('JWT_SECRET'),"token_type" => "Bearer","expires_in" => time() + 3600]);
            }else{
                return $this->respondCreated(['status' => false, 'message' => "Number already register as Customer, you can't use this number as Driver...!",'data' => []]);
            }
        }else{ // new driver 
            return $this->respondCreated(['status' => false, 'message' => 'Mobile number not exist need to register...!','data' => []]);
        }          
        }catch(\Exception $ex){
            return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
        }
    }


     /*
    * regiter driver details and create new JWT token
    *
    */
    public function registerDriver()
    {   
        
        try{
        $validation = \Config\Services::validation();        
        if(empty($this->request->getVar('email'))){  // optional email validation      
            $check = $this->validate([
                'fullname' => [
                    'rules'	=> ['required','max_length[100]','string'],
                ],
                'mobile' => [
                    'rules'	=> ['required','min_length[13]','max_length[13]'],
               ],
               'birthdate' => [
                'rules'	=> ['required','dobRules','valid_date[d-m-Y]'],
                'errors' => [
                    'required' => 'The birthdate field is required.',
                    'dobRules' => 'Birthdate must be greater than today.',
                    'valid_date' => 'Please enter a valid date in DD/MM/YYYY format.'
                ]
                ]         
            ]);
        }else{ // email field filled then check validation rules            
            $check = $this->validate([
                'fullname' => [
                    'rules'	=> ['required','max_length[100]','string'],
                ],
                'mobile' => [
                    'rules'	=> ['required','min_length[13]','max_length[13]'],
               ],
               'birthdate' => [
                'rules'	=> ['required','dobRules','valid_date[d/m/Y]'],
                'errors' => [
                    'required' => 'The birthdate field is required.',
                    'dobRules' => 'Birthdate must be greater than today.',
                    'valid_date' => 'Please enter a valid date in DD/MM/YYYY format.'
                ]
                ], 'email' => [
                    'rules'	=> ['required','valid_email','is_unique[ypc_users.email]'],
                 ]            
            ]);
        }        
      
        if (!$check) {  // validation errors occurs
            return $this->respondCreated(['status' => false, 'message' => 'Validation errors occurs..!','data' => [],'errors' => $this->validator->getErrors()]);
        } 
              
        // get role id 
        $roleModel = new Roles();
        $roleModel->select('id');
        $role_id = $roleModel->like('name','driver','both')->first();
        // insert driver details
        $userModel = new Users();  

        $userExist = $userModel->where('mobile',$this->request->getVar('mobile'))->first();
        if(!isset($userExist) && empty($userExist)){ 
            $fullname =$this->request->getVar('fullname');
            $name =explode(" ", $fullname);
            $first_name = $name[0];
            $last_name=ltrim($fullname, $first_name.' ');
        $userModel->insert([
            'first_name' => $first_name,
            'last_name' => $last_name,
                'fullname' => $this->request->getVar('fullname'),
                'mobile'  => $this->request->getVar('mobile'),
                'email'  => (!empty($this->request->getVar('email')))?$this->request->getVar('email'):null,
                'dob'  => date('Y-m-d', strtotime(str_replace('/', '-', $this->request->getVar('birthdate')))),
                'role_id'  => (isset($role_id) && !empty($role_id))?$role_id['id']:null,            
        ]);

        $userData = $userModel->find($userModel->getInsertID());
            if(isset($userData) && !empty($userData)){ // driver exist generate JWT auth token
                return $this->respondCreated(['status' => true, 'message' => 'Driver created successfully..!','data' => $userData,'access_token' => JWT::encode(['iss' => 'localhost','aud' => 'localhost','exp' => time() + 3600,'data' => ['user_id' => $userData['id'],'mobile' => $userData['mobile']]],getenv('JWT_SECRET'),'HS256'),"refresh_token" => getenv('JWT_SECRET'),"token_type" => "Bearer","expires_in" => time() + 3600]);
            }else{ // new driver 
                return $this->respondCreated(['status' => false, 'message' => 'Mobile number not exist need to register...!','data' => []]);
            }  
        }else{
        if($userExist['role_id'] == $role_id['id']){
            return $this->respondCreated(['status' => false, 'message' => 'Mobile number already exist please login...!','data' => []]);
        }else{
            return $this->respondCreated(['status' => false, 'message' => "Number already register as Customer, you can't use this number as Driver...!",'data' => []]);
        }
    }
        
        }catch(\Exception $ex){
            return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
        }
    }

    //Update Driver Profile

    public function updateProfile()
    {  //return $this->request->getVar('user_id');
        try{
        $validation = \Config\Services::validation();        
        if(empty($this->request->getVar('email'))){  // optional email validation      
            $check = $this->validate([
                'fullname' => [
                    'rules'	=> ['required','max_length[100]'],
                ],
                'mobile' => [
                    'rules'	=> ['required','min_length[13]','max_length[13]','is_unique[ypc_users.mobile,id,{user_id}]'],
               ],
               'birthdate' => [
                'rules'	=> ['required','dobRules','valid_date[d/m/Y]'],
                'gender' => [
                    'rules'	=> ['required','string'],
                ],
                'age' => [
                    'rules'	=> ['required','ageRules','greater_than_equal_to[18]'],
                ],
                'driving_license' => [
                    'rules'	=> ['required','string'],
                ],
                'errors' => [
                    'required' => 'The birthdate field is required.',
                    'dobRules' => 'Birthdate must be greater than today.',
                    'ageRules' => 'Age must be greater than or equal to 18.',
                    'valid_date' => 'Please enter a valid date in DD/MM/YYYY format.'
                ]
                ]         
            ]);
        }else{ // email field filled then check validation rules            
            $check = $this->validate([
                'fullname' => [
                    'rules'	=> ['required','max_length[100]','string'],
                ],
                'mobile' => [
                    'rules'	=> ['required','min_length[13]','max_length[13]','is_unique[ypc_users.mobile,id,{user_id}]'],
               ],
               'birthdate' => [
                'rules'	=> ['required','dobRules','valid_date[d/m/Y]'],
                'gender' => [
                    'rules'	=> ['required','string'],
                ],
                'age' => [
                    'rules'	=> ['required','ageRules','greater_than_equal_to[18]'],
                ],
                'driving_license' => [
                    'rules'	=> ['required','string'],
                ],
                'errors' => [
                    'required' => 'The birthdate field is required.',
                    'dobRules' => 'Birthdate must be greater than today.',
                    'ageRules' => 'Age must be greater than or equal to 18.',
                    'valid_date' => 'Please enter a valid date in DD/MM/YYYY format.'
                ]
                ], 'email' => [
                    'rules'	=> ['required','valid_email','is_unique[ypc_users.email,id,{user_id}]'],
                 ]            
            ]);
        }        
      
        if (!$check) {  // validation errors occurs
            return $this->respondCreated(['status' => false, 'message' => 'Validation errors occurs..!','data' => [],'errors' => $this->validator->getErrors()]);
        } 
        $fullname =$this->request->getVar('fullname');
        $name =explode(" ", $fullname);
        $first_name = $name[0];
        $last_name=ltrim($fullname, $first_name.' ');
       
        // insert driver details
        $userModel = new Users();  
        $userModel->update($this->request->getVar('user_id'), [
                'fullname' => $this->request->getVar('fullname'),
                'first_name'=>$first_name,
                'last_name'=>$last_name,
                'mobile'  => $this->request->getVar('mobile'),
                'email'  => (!empty($this->request->getVar('email')))?$this->request->getVar('email'):null,
                'gender'  => $this->request->getVar('gender'),
                'age'  => $this->request->getVar('age'),
                'drivinglicense'  => $this->request->getVar('driving_license'),
                'dob'  => date('Y-m-d', strtotime(str_replace('/', '-', $this->request->getVar('birthdate')))),                
        ]);

        $userData = $userModel->find($this->request->getVar('user_id'));
        if(isset($userData) && !empty($userData)){ // driver exist provide response
            return $this->respondCreated(['status' => true, 'message' => 'Driver profile updated successfully..!','data' => $userData]);
        }else{ // new driver 
            return $this->respondCreated(['status' => false, 'message' => 'Driver details not updated ..!','data' => []]);
        }   
        
        }catch(\Exception $ex){
            return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
        }
    }


    public function updateProfilePic()
    {   
       try{
        $validation = \Config\Services::validation();          
               $check = $this->validate([
                   'picture' => [
                       'uploaded[picture]',
                       'mime_in[picture,image/jpg,image/jpeg,image/png,image/gif]',
                       'max_size[picture,4096]',
                   ]
           ]);
           
       
           if (!$check) {  // validation errors occurs
               return $this->respondCreated(['status' => false, 'message' => 'Validation errors occurs..!','data' => [],'errors' => $this->validator->getErrors()]);
           }   


           $user = new Users(); 
           $u = $user->where('id',$this->request->getVar('user_id'))->first();
           $u_pic = $u['picture'];
     
           //  delete image if already exist
           if((isset($u_pic) && !empty($u_pic)) && file_exists(FCPATH .$u_pic))
               {
                   unlink(FCPATH .$u_pic);
               }

           // upload new profile_pic     
           $file = $this->request->getFile('picture');            
           $extension = $file->getExtension();
           $new_profile_img ='profile_pic'.date('YmdHis') . '.' . $extension;
           $file->move('./uploads/driver/profile_pic', $new_profile_img);
           
           $userModel = new Users();  
           $userModel->update($this->request->getVar('user_id'), [
            'picture' => '/uploads/driver/profile_pic/'.$new_profile_img,              
    ]);

       $userData = $userModel->find($this->request->getVar('user_id'));
       if(isset($userData) && !empty($userData)){ // driver exist provide response
        return $this->respondCreated(['status' => true, 'message' => 'Profile Image updated successfully..!','data' => $userData]);
    }else{ // new driver 
        return $this->respondCreated(['status' => false, 'message' => 'Profile Image not updated ..!','data' => []]);
    }
           
           }catch(\Exception $ex){
               return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
           }
   }
    
      /*
    * update driver information by driver id
    *
    */
    public function updateBasicInformation()
    {  //return $this->request->getVar('user_id');
        try{
        $validation = \Config\Services::validation();        
        if(empty($this->request->getVar('email'))){  // optional email validation      
            $check = $this->validate([
                'fullname' => [
                    'rules'	=> ['required','max_length[100]']
                ],
                'mobile' => [
                    'rules'	=> ['required','min_length[13]','max_length[13]','is_unique[ypc_users.mobile,id,{user_id}]'],
               ],
               'birthdate' => [
                'rules'	=> ['required','dobRules','valid_date[d/m/Y]'],
                'gender' => [
                    'rules'	=> ['required','string'],
                ],
                'age' => [
                    'rules'	=> ['required','ageRules','greater_than_equal_to[18]'],
                ],
                'driving_license' => [
                    'rules'	=> ['required','string'],
                ],
                'email' => [
                    'rules'	=> ['required','valid_email','is_unique[ypc_users.email,id,{user_id}]'],
                ],
                'errors' => [
                    'required' => 'The birthdate field is required.',
                    'dobRules' => 'Birthdate must be greater than today.',
                    'ageRules' => 'Age must be greater than or equal to 18.',
                    'valid_date' => 'Please enter a valid date in DD/MM/YYYY format.'
                ]
                ]        
            ]);
        }else{ // email field filled then check validation rules            
            $check = $this->validate([
                'fullname' => [
                    'rules'	=> ['required','max_length[100]','string'],
                ],
                'mobile' => [
                    'rules'	=> ['required','min_length[13]','max_length[13]','is_unique[ypc_users.mobile,id,{user_id}]'],
               ],
               'birthdate' => [
                'rules'	=> ['required','dobRules','valid_date[d/m/Y]'],
                'gender' => [
                    'rules'	=> ['required','string'],
                ],
                'age' => [
                    'rules'	=> ['required','ageRules','greater_than_equal_to[18]'],
                ],
                'driving_license' => [
                    'rules'	=> ['required','string'],
                ],
                'email' => [
                    'rules'	=> ['required','valid_email','is_unique[ypc_users.email,id,{user_id}]'],
                ],
                'errors' => [
                    'required' => 'The birthdate field is required.',
                    'dobRules' => 'Birthdate must be greater than today.',
                    'ageRules' => 'Age must be greater than or equal to 18.',
                    'valid_date' => 'Please enter a valid date in DD/MM/YYYY format.'
                ]
                ]            
            ]);
        }        
      
        if (!$check) {  // validation errors occurs
            return $this->respondCreated(['status' => false, 'message' => 'Validation errors occurs..!','data' => [],'errors' => $this->validator->getErrors()]);
        } 
              
        $fullname =$this->request->getVar('fullname');
        $name =explode(" ", $fullname);
        $first_name = $name[0];
        $last_name=ltrim($fullname, $first_name.' ');
       
        // insert driver details
        $userModel = new Users();  
        $userModel->update($this->request->getVar('user_id'), [
            'first_name'=>$first_name,
            'last_name' =>$last_name,
                'fullname' => $this->request->getVar('fullname'),
                'mobile'  => $this->request->getVar('mobile'),
                'email'  => (!empty($this->request->getVar('email')))?$this->request->getVar('email'):null,
                'gender'  => $this->request->getVar('gender'),
                'age'  => $this->request->getVar('age'),
                'drivinglicense'  => $this->request->getVar('driving_license'),
                'dob'  => date('Y-m-d', strtotime(str_replace('/', '-', $this->request->getVar('birthdate')))),                
        ]);

        $userData = $userModel->find($this->request->getVar('user_id'));
        if(isset($userData) && !empty($userData)){ // driver exist provide response
            return $this->respondCreated(['status' => true, 'message' => 'Driver details updated successfully..!','data' => $userData]);
        }else{ // new driver 
            return $this->respondCreated(['status' => false, 'message' => 'Driver details not updated ..!','data' => []]);
        }   
        
        }catch(\Exception $ex){
            return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
        }
    }

      /*
    * update driver vehicle information by driver id
    *
    */
    public function updateVehicleInformation()
    {   
        try{
        $validation = \Config\Services::validation();        
                
        $check = $this->validate([
            'vehiclecategory_id' => [
                'rules'	=> ['required','integer'],
            ],
            'vehiclebrand_id' => [
                'rules'	=> ['required','integer'],
            ],
            'vehiclemodel_id' => [
                'rules'	=> ['required','integer'],
            ],
            'vehiclecolor_id' => [
                'rules'	=> ['required','integer'],
            ],
            'vehicleyear' => [
            'rules'	=> ['required','valid_date[d/m/Y]'],
            ]         
        ]);
      
        if (!$check) {  // validation errors occurs
            return $this->respondCreated(['status' => false, 'message' => 'Validation errors occurs..!','data' => [],'errors' => $this->validator->getErrors()]);
        } 
              
       
        // insert driver details
        $userModel = new Users();  
        $userModel->update($this->request->getVar('user_id'), [
                'vehiclecategory_id' => $this->request->getVar('vehiclecategory_id'),
                'vehiclebrand_id'  => $this->request->getVar('vehiclebrand_id'),
                'vehiclemodel_id'  => $this->request->getVar('vehiclemodel_id'),
                'vehiclecolor_id'  => $this->request->getVar('vehiclecolor_id'),               
                'vehicleyear'  => date('Y-m-d', strtotime(str_replace('/', '-', $this->request->getVar('vehicleyear')))),                
        ]);

        $userData = $userModel->find($this->request->getVar('user_id'));
        if(isset($userData) && !empty($userData)){ // driver exist provide response
            return $this->respondCreated(['status' => true, 'message' => 'Driver details updated successfully..!','data' => $userData]);
        }else{ // new driver 
            return $this->respondCreated(['status' => false, 'message' => 'Driver details not updated ..!','data' => []]);
        }   
        
        }catch(\Exception $ex){
            return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
        }
    }

    /*
    * update driver referal code by driver id
    *
    */
    public function updateReferalCode()
    {   
        try{
        $validation = \Config\Services::validation();          
            $check = $this->validate([
                'referalcode' => [
                    'rules'	=> ['required','min_length[8]','max_length[8]','string'],
                ]      
            ]);
            
      
        if (!$check) {  // validation errors occurs
            return $this->respondCreated(['status' => false, 'message' => 'Validation errors occurs..!','data' => [],'errors' => $this->validator->getErrors()]);
        } 
              
       
        // update driver details
        $userModel = new Users();  
        $userModel->update($this->request->getVar('user_id'), [
                'referalcode' => $this->request->getVar('referalcode'),              
        ]);

        $userData = $userModel->find($this->request->getVar('user_id'));
        if(isset($userData) && !empty($userData)){ // driver exist provide response
            return $this->respondCreated(['status' => true, 'message' => 'Driver referal code updated successfully..!','data' => $userData]);
        }else{ // new driver 
            return $this->respondCreated(['status' => false, 'message' => 'Driver referal code not updated ..!','data' => []]);
        }   
        
         }catch(\Exception $ex){
            return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
        }
    }

      /*
    * update driver selfi details by driver id
    *
    */
    public function updateSelfiDetails()
    {   
       
      
        try{
                $encrypter = \Config\Services::encrypter();     
                $validation = \Config\Services::validation();          
                    $check = $this->validate([
                        'adharnumber' => [
                            'rules'	=> ['required','min_length[12]','max_length[12]','string'],
                        ], 'addphoto' => [
                            'uploaded[addphoto]',
                            'mime_in[addphoto,image/jpg,image/jpeg,image/png,image/gif]',
                            'max_size[addphoto,4096]',
                        ], 'adharcardfront' => [
                            'uploaded[adharcardfront]',
                            'mime_in[adharcardfront,image/jpg,image/jpeg,image/png,image/gif]',
                            'max_size[adharcardfront,4096]',
                        ], 'adharcardback' => [
                            'uploaded[adharcardback]',
                            'mime_in[adharcardback,image/jpg,image/jpeg,image/png,image/gif]',
                            'max_size[adharcardback,4096]',
                        ], 'policeclearcerti' => [
                            'uploaded[policeclearcerti]',
                            'mime_in[policeclearcerti,image/jpg,image/jpeg,image/png,image/gif]',
                            'max_size[policeclearcerti,4096]',
                        ]  
                ]);
                
            
                if (!$check) {  // validation errors occurs
                    return $this->respondCreated(['status' => false, 'message' => 'Validation errors occurs..!','data' => [],'errors' => $this->validator->getErrors()]);
                } 
              
               
                // get driver document ids
                $driverDocumentModel = new DriverDocuments();
                    
                // get driver document ids
                $documentModel = new Documents();
                $documentModel->select('id');
                $documentModel = $documentModel->where('type','DRIVER');  
                
               
                // user model initialize
                $userModel = new Users();  

                if($this->request->getFile('adharcardback')){ // adhar back photo
                    $documentModelAdharBack = $documentModel->where('name','Aadhar Card Back')->where('type','DRIVER')->first();
                  

                    // retrive already exist adhar back photo
                    $documentModelAdharBackGet = clone $driverDocumentModel; 
                    $documentModelAdharBackGet->where('driver_id', $this->request->getVar('user_id'));
                    $documentModelAdharBackGet->where('document_id', $documentModelAdharBack['id'] ?? null);
                    $documentModelAdharBackGet = $documentModelAdharBackGet->first();             

                    // delete image if exist
                    if((isset($documentModelAdharBackGet) && !empty($documentModelAdharBackGet)) && file_exists(FCPATH .'uploads/driver/'.$documentModelAdharBackGet['document_name']))
                    {
                        unlink(FCPATH .'uploads/driver/'.$documentModelAdharBackGet['document_name']);
                    }

                    // delete already exist adhar back photo
                    $driverDocumentModelAdharBack = clone $driverDocumentModel;            
                    $driverDocumentModelAdharBack->where('driver_id', $this->request->getVar('user_id'));
                    $driverDocumentModelAdharBack->where('document_id', $documentModelAdharBack['id'] ?? null);
                    $driverDocumentModelAdharBack->delete();

                    $file = $this->request->getFile('adharcardback');            
                    $extension = $file->getExtension();
                
                    $adharcardback ='adharback_'.date('YmdHis') . '.' . $extension;
                    if($file->move('./uploads/driver', $adharcardback)){
                        $driverDocumentModelAdharBack->insert([
                            'driver_id' => $this->request->getVar('user_id'),              
                            'document_id' => $documentModelAdharBack['id'] ?? null,  
                            'document_name' => $adharcardback,
                            'document_path' => FCPATH .'uploads/driver/'.$adharcardback,
                    ]);          
                    }
                }

                if($this->request->getFile('adharcardfront')){ // adhar front photo
                    $documentModelAdharFront = $documentModel->where('name','Aadhar Card Front')->where('type','DRIVER')->first();
                 
                    // retrive already exist adhar front photo
                    $documentModelAdharFrontGet = clone $driverDocumentModel; 
                    $documentModelAdharFrontGet->where('driver_id', $this->request->getVar('user_id'));
                    $documentModelAdharFrontGet->where('document_id', $documentModelAdharFront['id'] ?? null);
                    $documentModelAdharFrontGet = $documentModelAdharFrontGet->first();             

                    // delete image if exist
                    if((isset($documentModelAdharFrontGet) && !empty($documentModelAdharFrontGet)) && file_exists(FCPATH .'uploads/driver/'.$documentModelAdharFrontGet['document_name']))
                    {
                        unlink(FCPATH .'uploads/driver/'.$documentModelAdharFrontGet['document_name']);
                    }

                    // delete already exist adhar front photo
                    $driverDocumentModelAdharFront = clone $driverDocumentModel;            
                    $driverDocumentModelAdharFront->where('driver_id', $this->request->getVar('user_id'));
                    $driverDocumentModelAdharFront->where('document_id', $documentModelAdharFront['id'] ?? null);
                    $driverDocumentModelAdharFront->delete();

                    $file = $this->request->getFile('adharcardfront');            
                    $extension = $file->getExtension();
                
                    $adharcardfront ='adharfront_'.date('YmdHis') . '.' . $extension;
                    if($file->move('./uploads/driver', $adharcardfront)){
                        $driverDocumentModelAdharFront->insert([
                            'driver_id' => $this->request->getVar('user_id'),              
                            'document_id' => $documentModelAdharFront['id'] ?? null,  
                            'document_name' => $adharcardfront,
                            'document_path' => FCPATH .'uploads/driver/'.$adharcardfront,
                    ]);          
                    } 
                }

                if($this->request->getFile('policeclearcerti')){ // police clearance photo
                    $documentModelPoliceClear = $documentModel->where('name','Police Clearance Certificate')->where('type','DRIVER')->first();
                            

                    // retrive already exist police clearance photo
                    $driverDocumentModelPoliceClearGet = clone $driverDocumentModel; 
                    $driverDocumentModelPoliceClearGet->where('driver_id', $this->request->getVar('user_id'));
                    $driverDocumentModelPoliceClearGet->where('document_id', $documentModelPoliceClear['id'] ?? null);
                    $driverDocumentModelPoliceClearGet = $driverDocumentModelPoliceClearGet->first();

                    // delete image if exist
                    if((isset($driverDocumentModelPoliceClearGet) && !empty($driverDocumentModelPoliceClearGet)) && file_exists(FCPATH .'uploads/driver/'.$driverDocumentModelPoliceClearGet['document_name']))
                    {
                        unlink(FCPATH .'uploads/driver/'.$driverDocumentModelPoliceClearGet['document_name']);
                    }

                    // delete already exist police clearance photo
                    $driverDocumentModelPoliceClear = clone $driverDocumentModel;            
                    $driverDocumentModelPoliceClear->where('driver_id', $this->request->getVar('user_id'));
                    $driverDocumentModelPoliceClear->where('document_id', $documentModelPoliceClear['id'] ?? null);
                    $driverDocumentModelPoliceClear->delete();

                    $file = $this->request->getFile('policeclearcerti');            
                    $extension = $file->getExtension();
                
                    $addphoto ='policeclear_'.date('YmdHis') . '.' . $extension;
                    if($file->move('./uploads/driver', $addphoto)){
                        $driverDocumentModelPoliceClear->insert([
                            'driver_id' => $this->request->getVar('user_id'),              
                            'document_id' => $documentModelPoliceClear['id'] ?? null,  
                            'document_name' => $addphoto,
                            'document_path' => FCPATH .'uploads/driver/'.$addphoto,
                    ]);          
                    } 
                }

                if($this->request->getFile('addphoto')){ // profile photo add here
                    $documentModelProfile = $documentModel->where('name','Photo')->where('type','DRIVER')->first();
                   

                    // retrive already exist add profile photo
                    $documentModelProfileGet = clone $driverDocumentModel; 
                    $documentModelProfileGet->where('driver_id', $this->request->getVar('user_id'));
                    $documentModelProfileGet->where('document_id', $documentModelProfile['id'] ?? null);
                    $documentModelProfileGet = $documentModelProfileGet->first();             
                  
                    // delete image if exist
                    if((isset($documentModelProfileGet) && !empty($documentModelProfileGet)) && file_exists(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']))
                    {
                        unlink(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']);
                    }

                    // delete already exist profile photo
                    $driverDocumentModelProfile = clone $driverDocumentModel;            
                    $driverDocumentModelProfile->where('driver_id', $this->request->getVar('user_id'));
                    $driverDocumentModelProfile->where('document_id', $documentModelProfile['id'] ?? null);
                    $driverDocumentModelProfile->delete();
                    
                    $file = $this->request->getFile('addphoto');            
                    $extension = $file->getExtension();
                
                    $addphoto ='addprofile_'.date('YmdHis') . '.' . $extension;
                    if($file->move('./uploads/driver', $addphoto)){
                        $driverDocumentModelProfile->insert([
                            'driver_id' => $this->request->getVar('user_id'),              
                            'document_id' => $documentModelProfile['id'] ?? null,  
                            'document_name' => $addphoto,
                            'document_path' => FCPATH .'uploads/driver/'.$addphoto,
                    ]);          
                    }           
                }        
            // dd($this->request->getVar('user_id'));eit;
                // update driver details
                $userModel = new Users();  
                $userModel->update($this->request->getVar('user_id'), [
                        'adharnumber' =>$this->request->getVar('adharnumber'),              
                ]);
               
                $userData = $userModel->find($this->request->getVar('user_id'));
                if(isset($userData) && !empty($userData)){ // driver exist provide response
                    return $this->respondCreated(['status' => true, 'message' => 'Driver selfi with id updated successfully..!','data' => $userData]);
                }else{ // new driver 
                    return $this->respondCreated(['status' => false, 'message' => 'Driver selfi with id not updated ..!','data' => []]);
                } 
        
        }catch(\Exception $ex){
            return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
        }
    }

    /*
    * update driver driving license details by driver id
    *
    */
    public function updateDrivingLicense()
    {   
        try{
            $encrypter = \Config\Services::encrypter();     
            $validation = \Config\Services::validation();          
                $check = $this->validate([
                    'licensenumber' => [
                        'rules'	=> ['required','min_length[15]','max_length[15]','string'],
                    ],'dateofexpiration' => [
                        'rules'	=> ['required','valid_date[d/m/Y]'],
                    ], 'licensefront' => [
                        'uploaded[licensefront]',
                        'mime_in[licensefront,image/jpg,image/jpeg,image/png,image/gif]',
                        'max_size[licensefront,4096]',
                    ], 'licenseback' => [
                        'uploaded[licenseback]',
                        'mime_in[licenseback,image/jpg,image/jpeg,image/png,image/gif]',
                        'max_size[licenseback,4096]',
                    ]
            ]);
            
        
            if (!$check) {  // validation errors occurs
                return $this->respondCreated(['status' => false, 'message' => 'Validation errors occurs..!','data' => [],'errors' => $this->validator->getErrors()]);
            }   

            // get driver document ids
            $driverDocumentModel = new DriverDocuments();
              
            // get driver document ids
            $documentModel = new Documents();
            $documentModel->select('id');
            $documentModel = $documentModel->where('type','DRIVER');        
            // user model initialize
            $userModel = new Users(); 
            if($this->request->getFile('licensefront')){ // license front here
                $documentModelProfile = $documentModel->where('name','Driving License Front')->where('type','DRIVER')->first();
               
                // retrive already exist driving license front photo
                $documentModelProfileGet = clone $driverDocumentModel; 
                $documentModelProfileGet->where('driver_id', $this->request->getVar('user_id'));
                $documentModelProfileGet->where('document_id', $documentModelProfile['id'] ?? null);
                $documentModelProfileGet = $documentModelProfileGet->first();

                // delete image if exist
                if((isset($documentModelProfileGet) && !empty($documentModelProfileGet)) && file_exists(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']))
                {
                    unlink(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']);
                }
    
                // delete already exist profile photo
                $driverDocumentModelProfile = clone $driverDocumentModel;            
                $driverDocumentModelProfile->where('driver_id', $this->request->getVar('user_id'));
                $driverDocumentModelProfile->where('document_id', $documentModelProfile['id'] ?? null);
                $driverDocumentModelProfile->delete();
                
                $file = $this->request->getFile('licensefront');            
                $extension = $file->getExtension();
             
                $licensefront ='licensefront_'.date('YmdHis') . '.' . $extension;
                if($file->move('./uploads/driver', $licensefront)){
                       $driverDocumentModelProfile->insert([
                        'driver_id' => $this->request->getVar('user_id'),              
                        'document_id' => $documentModelProfile['id'] ?? null,  
                        'document_name' => $licensefront,
                        'document_path' => FCPATH .'uploads/driver/'.$licensefront,
                ]);          
                }           
            } 

            if($this->request->getFile('licenseback')){ // license back here
                $documentModelProfile = $documentModel->where('name','Driving License Back')->where('type','DRIVER')->first();
             
                // retrive already exist driving license back photo
                $documentModelProfileGet = clone $driverDocumentModel; 
                $documentModelProfileGet->where('driver_id', $this->request->getVar('user_id'));
                $documentModelProfileGet->where('document_id', $documentModelProfile['id'] ?? null);
                $documentModelProfileGet = $documentModelProfileGet->first();

                // delete image if exist
                if((isset($documentModelProfileGet) && !empty($documentModelProfileGet)) && file_exists(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']))
                {
                    unlink(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']);
                }
    
                // delete already exist profile photo
                $driverDocumentModelProfile = clone $driverDocumentModel;            
                $driverDocumentModelProfile->where('driver_id', $this->request->getVar('user_id'));
                $driverDocumentModelProfile->where('document_id', $documentModelProfile['id'] ?? null);
                $driverDocumentModelProfile->delete();
                
                $file = $this->request->getFile('licenseback');            
                $extension = $file->getExtension();
             
                $licenseback ='licenseback_'.date('YmdHis') . '.' . $extension;
                if($file->move('./uploads/driver', $licenseback)){
                       $driverDocumentModelProfile->insert([
                        'driver_id' => $this->request->getVar('user_id'),              
                        'document_id' => $documentModelProfile['id'] ?? null,  
                        'document_name' => $licenseback,
                        'document_path' => FCPATH .'uploads/driver/'.$licenseback,
                ]);          
                }           
            } 
           
            // update driver details
            $userModel = new Users();  
            $userModel->update($this->request->getVar('user_id'), [
                    'drivinglicense' => $this->request->getVar('licensenumber'),              
                    'dateofexpiration' => date('Y-m-d', strtotime(str_replace('/', '-', $this->request->getVar('dateofexpiration'))))
            ]);

            $userData = $userModel->find($this->request->getVar('user_id'));
            if(isset($userData) && !empty($userData)){ // driver exist provide response
                return $this->respondCreated(['status' => true, 'message' => 'Driver License details updated successfully..!','data' => $userData]);
            }else{ // new driver 
                return $this->respondCreated(['status' => false, 'message' => 'Driver License details not updated ..!','data' => []]);
            }   

        }catch(\Exception $ex){
                return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
        }
    }  

    /*
    * update driver register certificate details by driver id
    *
    */
    public function updateRegisterCertificate()
    {   
        try{
            $encrypter = \Config\Services::encrypter();     
            $validation = \Config\Services::validation();          
                $check = $this->validate([
                    'vehiclenumberplate' => [
                        'rules'	=> ['required','string'],
                    ],'vehiclenumberplatephoto' => [
                        'uploaded[vehiclenumberplatephoto]',
                        'mime_in[vehiclenumberplatephoto,image/jpg,image/jpeg,image/png,image/gif]',
                        'max_size[vehiclenumberplatephoto,4096]',
                    ], 'registercertifront' => [
                        'uploaded[registercertifront]',
                        'mime_in[registercertifront,image/jpg,image/jpeg,image/png,image/gif]',
                        'max_size[registercertifront,4096]',
                    ], 'registercertiback' => [
                        'uploaded[registercertiback]',
                        'mime_in[registercertiback,image/jpg,image/jpeg,image/png,image/gif]',
                        'max_size[registercertiback,4096]',
                    ], 'permitcommercialcerti' => [
                        'uploaded[permitcommercialcerti]',
                        'mime_in[permitcommercialcerti,image/jpg,image/jpeg,image/png,image/gif]',
                        'max_size[permitcommercialcerti,4096]',
                    ], 'permitcommercialcertitwo' => [
                        'uploaded[permitcommercialcertitwo]',
                        'mime_in[permitcommercialcertitwo,image/jpg,image/jpeg,image/png,image/gif]',
                        'max_size[permitcommercialcertitwo,4096]',
                    ], 'permitcommercialcertithree' => [
                        'uploaded[permitcommercialcertithree]',
                        'mime_in[permitcommercialcertithree,image/jpg,image/jpeg,image/png,image/gif]',
                        'max_size[permitcommercialcertithree,4096]',
                    ], 'vehicleinsurance' => [
                        'uploaded[vehicleinsurance]',
                        'mime_in[vehicleinsurance,image/jpg,image/jpeg,image/png,image/gif]',
                        'max_size[vehicleinsurance,4096]',
                    ]
            ]);
            
        
            if (!$check) {  // validation errors occurs
                return $this->respondCreated(['status' => false, 'message' => 'Validation errors occurs..!','data' => [],'errors' => $this->validator->getErrors()]);
            }   

            // get driver document ids
            $driverDocumentModel = new DriverDocuments();
              
            // get driver document ids
            $documentModel = new Documents();
            $documentModel->select('id');
            $documentModel = $documentModel->where('type','DRIVER');        
            // user model initialize
            $userModel = new Users(); 
            if($this->request->getFile('vehiclenumberplatephoto')){ // vehical register photo here
                $documentModelProfile = $documentModel->where('name','Vehicle Photo With Number Plate')->where('type','DRIVER')->first();
             
               
                // retrive already exist vehical register photo photo
                $documentModelProfileGet = clone $driverDocumentModel; 
                $documentModelProfileGet->where('driver_id', $this->request->getVar('user_id'));
                $documentModelProfileGet->where('document_id', $documentModelProfile['id'] ?? null);
                $documentModelProfileGet = $documentModelProfileGet->first();

                // delete image if exist
                if((isset($documentModelProfileGet) && !empty($documentModelProfileGet)) && file_exists(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']))
                {
                    unlink(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']);
                }
    
                // delete already exist profile photo
                $driverDocumentModelProfile = clone $driverDocumentModel;            
                $driverDocumentModelProfile->where('driver_id', $this->request->getVar('user_id'));
                $driverDocumentModelProfile->where('document_id', $documentModelProfile['id'] ?? null);
                $driverDocumentModelProfile->delete();
                
                $file = $this->request->getFile('vehiclenumberplatephoto');            
                $extension = $file->getExtension();
             
                $vehiclenumberplatephoto ='vehiclenumberplatephoto_'.date('YmdHis') . '.' . $extension;
                if($file->move('./uploads/driver', $vehiclenumberplatephoto)){
                       $driverDocumentModelProfile->insert([
                        'driver_id' => $this->request->getVar('user_id'),              
                        'document_id' => $documentModelProfile['id'] ?? null,  
                        'document_name' => $vehiclenumberplatephoto,
                        'document_path' => FCPATH .'uploads/driver/'.$vehiclenumberplatephoto,
                ]);          
                }           
            }
            
            if($this->request->getFile('registercertifront')){ // vehical register certificate front photo here
                $documentModelProfile = $documentModel->where('name','Registration Certificate Front')->where('type','DRIVER')->first();
              
               
                // retrive already exist register certificate front photo
                $documentModelProfileGet = clone $driverDocumentModel; 
                $documentModelProfileGet->where('driver_id', $this->request->getVar('user_id'));
                $documentModelProfileGet->where('document_id', $documentModelProfile['id'] ?? null);
                $documentModelProfileGet = $documentModelProfileGet->first();

                // delete image if exist
                if((isset($documentModelProfileGet) && !empty($documentModelProfileGet)) && file_exists(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']))
                {
                    unlink(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']);
                }
    
                // delete already exist register certificate front
                $driverDocumentModelProfile = clone $driverDocumentModel;            
                $driverDocumentModelProfile->where('driver_id', $this->request->getVar('user_id'));
                $driverDocumentModelProfile->where('document_id', $documentModelProfile['id'] ?? null);
                $driverDocumentModelProfile->delete();
                
                $file = $this->request->getFile('registercertifront');            
                $extension = $file->getExtension();
             
                $registercertifront ='registercertifront_'.date('YmdHis') . '.' . $extension;
                if($file->move('./uploads/driver', $registercertifront)){
                       $driverDocumentModelProfile->insert([
                        'driver_id' => $this->request->getVar('user_id'),              
                        'document_id' => $documentModelProfile['id'] ?? null,  
                        'document_name' => $registercertifront,
                        'document_path' => FCPATH .'uploads/driver/'.$registercertifront,
                ]);          
                }           
            }

            if($this->request->getFile('registercertiback')){ // vehical register certificate back photo here
                $documentModelProfile = $documentModel->where('name','Registration Certificate Back')->where('type','DRIVER')->first();
               
                // retrive already exist register certificate back photo
                $documentModelProfileGet = clone $driverDocumentModel; 
                $documentModelProfileGet->where('driver_id', $this->request->getVar('user_id'));
                $documentModelProfileGet->where('document_id', $documentModelProfile['id'] ?? null);
                $documentModelProfileGet = $documentModelProfileGet->first();

                // delete image if exist
                if((isset($documentModelProfileGet) && !empty($documentModelProfileGet)) && file_exists(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']))
                {
                    unlink(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']);
                }
    
                // delete already exist register certificate back
                $driverDocumentModelProfile = clone $driverDocumentModel;            
                $driverDocumentModelProfile->where('driver_id', $this->request->getVar('user_id'));
                $driverDocumentModelProfile->where('document_id', $documentModelProfile['id'] ?? null);
                $driverDocumentModelProfile->delete();
                
                $file = $this->request->getFile('registercertiback');            
                $extension = $file->getExtension();
             
                $registercertiback ='registercertiback_'.date('YmdHis') . '.' . $extension;
                if($file->move('./uploads/driver', $registercertiback)){
                       $driverDocumentModelProfile->insert([
                        'driver_id' => $this->request->getVar('user_id'),              
                        'document_id' => $documentModelProfile['id'] ?? null,  
                        'document_name' => $registercertiback,
                        'document_path' => FCPATH .'uploads/driver/'.$registercertiback,
                ]);          
                }           
            }

            if($this->request->getFile('permitcommercialcerti')){ // vehical permit commercial certificate photo here
                $documentModelProfile = $documentModel->where('name','Permit Commercial Certificate One')->where('type','DRIVER')->first();
              
               
                // retrive already exist permit commercial certificate photo
                $documentModelProfileGet = clone $driverDocumentModel; 
                $documentModelProfileGet->where('driver_id', $this->request->getVar('user_id'));
                $documentModelProfileGet->where('document_id', $documentModelProfile['id'] ?? null);
                $documentModelProfileGet = $documentModelProfileGet->first();

                // delete image if exist
                if((isset($documentModelProfileGet) && !empty($documentModelProfileGet)) && file_exists(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']))
                {
                    unlink(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']);
                }
    
                // delete already exist permit commercial certificate
                $driverDocumentModelProfile = clone $driverDocumentModel;            
                $driverDocumentModelProfile->where('driver_id', $this->request->getVar('user_id'));
                $driverDocumentModelProfile->where('document_id', $documentModelProfile['id'] ?? null);
                $driverDocumentModelProfile->delete();
                
                $file = $this->request->getFile('permitcommercialcerti');            
                $extension = $file->getExtension();
             
                $permitcommercialcerti ='permitcommercialcerti_'.date('YmdHis') . '.' . $extension;
                if($file->move('./uploads/driver', $permitcommercialcerti)){
                       $driverDocumentModelProfile->insert([
                        'driver_id' => $this->request->getVar('user_id'),              
                        'document_id' => $documentModelProfile['id'] ?? null,  
                        'document_name' => $permitcommercialcerti,
                        'document_path' => FCPATH .'uploads/driver/'.$permitcommercialcerti,
                ]);          
                }           
            }

            if($this->request->getFile('permitcommercialcertitwo')){ // vehical permit commercial certificate two photo here
                $documentModelProfile = $documentModel->where('name','Permit Commercial Certificate Two')->where('type','DRIVER')->first();
               
                // retrive already exist permit commercial certificate two photo
                $documentModelProfileGet = clone $driverDocumentModel; 
                $documentModelProfileGet->where('driver_id', $this->request->getVar('user_id'));
                $documentModelProfileGet->where('document_id', $documentModelProfile['id'] ?? null);
                $documentModelProfileGet = $documentModelProfileGet->first();

                // delete image if exist
                if((isset($documentModelProfileGet) && !empty($documentModelProfileGet)) && file_exists(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']))
                {
                    unlink(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']);
                }
    
                // delete already exist permit commercial two certificate
                $driverDocumentModelProfile = clone $driverDocumentModel;            
                $driverDocumentModelProfile->where('driver_id', $this->request->getVar('user_id'));
                $driverDocumentModelProfile->where('document_id', $documentModelProfile['id'] ?? null);
                $driverDocumentModelProfile->delete();
                
                $file = $this->request->getFile('permitcommercialcertitwo');            
                $extension = $file->getExtension();
             
                $permitcommercialcertitwo ='permitcommercialcertitwo_'.date('YmdHis') . '.' . $extension;
                if($file->move('./uploads/driver', $permitcommercialcertitwo)){
                       $driverDocumentModelProfile->insert([
                        'driver_id' => $this->request->getVar('user_id'),              
                        'document_id' => $documentModelProfile['id'] ?? null,  
                        'document_name' => $permitcommercialcertitwo,
                        'document_path' => FCPATH .'uploads/driver/'.$permitcommercialcertitwo,
                ]);          
                }           
            }

            if($this->request->getFile('permitcommercialcertithree')){ // vehical permit commercial certificate three photo here
                $documentModelProfile = $documentModel->where('name','Permit Commercial Certificate Three')->where('type','DRIVER')->first();
               
               
                // retrive already exist permit commercial certificate two photo
                $documentModelProfileGet = clone $driverDocumentModel; 
                $documentModelProfileGet->where('driver_id', $this->request->getVar('user_id'));
                $documentModelProfileGet->where('document_id', $documentModelProfile['id'] ?? null);
                $documentModelProfileGet = $documentModelProfileGet->first();

                // delete image if exist
                if((isset($documentModelProfileGet) && !empty($documentModelProfileGet)) && file_exists(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']))
                {
                    unlink(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']);
                }
    
                // delete already exist permit commercial three certificate
                $driverDocumentModelProfile = clone $driverDocumentModel;            
                $driverDocumentModelProfile->where('driver_id', $this->request->getVar('user_id'));
                $driverDocumentModelProfile->where('document_id', $documentModelProfile['id'] ?? null);
                $driverDocumentModelProfile->delete();
                
                $file = $this->request->getFile('permitcommercialcertithree');            
                $extension = $file->getExtension();
             
                $permitcommercialcertithree ='permitcommercialcertithree_'.date('YmdHis') . '.' . $extension;
                if($file->move('./uploads/driver', $permitcommercialcertithree)){
                       $driverDocumentModelProfile->insert([
                        'driver_id' => $this->request->getVar('user_id'),              
                        'document_id' => $documentModelProfile['id'] ?? null,  
                        'document_name' => $permitcommercialcertithree,
                        'document_path' => FCPATH .'uploads/driver/'.$permitcommercialcertithree,
                ]);          
                }           
            }

            if($this->request->getFile('vehicleinsurance')){ // vehical vehicle insurance photo here
                $documentModelProfile = $documentModel->where('name','Vehicle Insurance Photo')->where('type','DRIVER')->first();
          
               
                // retrive already exist vehicle insurance photo photo
                $documentModelProfileGet = clone $driverDocumentModel; 
                $documentModelProfileGet->where('driver_id', $this->request->getVar('user_id'));
                $documentModelProfileGet->where('document_id', $documentModelProfile['id'] ?? null);
                $documentModelProfileGet = $documentModelProfileGet->first();

                // delete image if exist
                if((isset($documentModelProfileGet) && !empty($documentModelProfileGet)) && file_exists(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']))
                {
                    unlink(FCPATH .'uploads/driver/'.$documentModelProfileGet['document_name']);
                }
    
                // delete already exist vehicle insurance photo
                $driverDocumentModelProfile = clone $driverDocumentModel;            
                $driverDocumentModelProfile->where('driver_id', $this->request->getVar('user_id'));
                $driverDocumentModelProfile->where('document_id', $documentModelProfile['id'] ?? null);
                $driverDocumentModelProfile->delete();
                
                $file = $this->request->getFile('vehicleinsurance');            
                $extension = $file->getExtension();
             
                $vehicleinsurance ='vehicleinsurance_'.date('YmdHis') . '.' . $extension;
                if($file->move('./uploads/driver', $vehicleinsurance)){
                       $driverDocumentModelProfile->insert([
                        'driver_id' => $this->request->getVar('user_id'),              
                        'document_id' => $documentModelProfile['id'] ?? null,  
                        'document_name' => $vehicleinsurance,
                        'document_path' => FCPATH .'uploads/driver/'.$vehicleinsurance,
                ]);          
                }           
            }

           
           
            // update driver details
            $userModel = new Users();  
            $userModel->update($this->request->getVar('user_id'), [
                    'vehiclenumberplate' => $this->request->getVar('vehiclenumberplate'),                                  
            ]);

            $userData = $userModel->find($this->request->getVar('user_id'));
            if(isset($userData) && !empty($userData)){ // driver exist provide response
                return $this->respondCreated(['status' => true, 'message' => 'Driver Registration Certificate details updated successfully..!','data' => $userData]);
            }else{ // new driver 
                return $this->respondCreated(['status' => false, 'message' => 'Driver Registration Certificate details not updated ..!','data' => []]);
            }   

        }catch(\Exception $ex){
                return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
        }
    } 

     /*
    * get driver all vehicle category list details
    *
    */
    public function getAllVehicleCategories()
    {   
        try{            
          $vehicleCategoryModel = new VehicleCategory();
          $vehicleCategoryModel = $vehicleCategoryModel->where('status',1)->where('deleted_at', null)->findAll();
          if(isset($vehicleCategoryModel) && !empty($vehicleCategoryModel)){ // driver exist provide response
            return $this->respondCreated(['status' => true, 'message' => 'Driver Vehicle Categories details list successfully..!','data' => $vehicleCategoryModel]);
            }else{ // new driver 
                return $this->respondCreated(['status' => false, 'message' => 'Driver Vehicle Categories details list not found ..!','data' => []]);
            }   
        }catch(\Exception $ex){
                return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
        }
    }

    /*
    * get driver all vehicle brand list details
    *
    */
    public function getAllVehicleBrand()
    {
        try{            
            $vehicleCategoryBrand = new VehicleBrand();
            $vehicleCategoryBrand = $vehicleCategoryBrand->where('status',1)->where('deleted_at', null)->findAll();
            if(isset($vehicleCategoryBrand) && !empty($vehicleCategoryBrand)){ // driver exist provide response
              return $this->respondCreated(['status' => true, 'message' => 'Driver Vehicle Brand list successfully..!','data' => $vehicleCategoryBrand]);
            }else{ // new driver 
                return $this->respondCreated(['status' => false, 'message' => 'Driver Vehicle Brand list not found ..!','data' => []]);
            }   
          }catch(\Exception $ex){
                return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
          }
    }

    /*
    * get driver all vehicle model list details by brand id 
    *
    */
    public function getAllVehicleModel($id = "")
    {
        try{            
            $db      = \Config\Database::connect();
            $builder = $db->table('ypc_vehiclebrand_master');
            $builder->select('ypc_vehiclebrand_master.id as vehiclebrandid, ypc_vehiclemodel_master.id as vehiclemodelid, ypc_vehiclemodel_master.name as vehiclemodelname');
            $builder->join('ypc_vehiclemodel_master','ypc_vehiclebrand_master.id = ypc_vehiclemodel_master.brand_id');
            $builder->where('ypc_vehiclemodel_master.brand_id',$id);
            $builder->where('ypc_vehiclemodel_master.status',1);
            $builder->where('ypc_vehiclebrand_master.status',1);
            $query = $builder->get()->getResultArray();

            if(isset($query) && !empty($query)){ // driver exist provide response
              return $this->respondCreated(['status' => true, 'message' => 'Driver Vehicle Model list successfully..!','data' => $query]);
            }else{ // new driver 
                return $this->respondCreated(['status' => false, 'message' => 'Driver Vehicle Model list not found ..!','data' => []]);
            }   
          }catch(\Exception $ex){
                return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
          }
    }

     /*
    * get driver all vehicle color list details
    *
    */
    public function getAllVehicleColor()
    {
        try{            
            $vehicleColor = new VehicleColor();
            $vehicleColor = $vehicleColor->where('status',1)->where('deleted_at', null)->findAll();
            if(isset($vehicleColor) && !empty($vehicleColor)){ // driver exist provide response
              return $this->respondCreated(['status' => true, 'message' => 'Driver Vehicle Color list successfully..!','data' => $vehicleColor]);
            }else{ // new driver 
                return $this->respondCreated(['status' => false, 'message' => 'Driver Vehicle Color list not found ..!','data' => []]);
            }   
          }catch(\Exception $ex){
                return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
          }
    }

    
    public function getDriverFaq()
    {  
        try{            
            $faq = new Faq();
            $faq = $faq->where('faq_for',2)->findAll();
            if(isset($faq) && !empty($faq)){ // faq exist provide response
              return $this->respondCreated(['status' => true, 'message' => 'Faq for driver list successfully..!','data' => $faq]);
            }else{ // new faq 
                return $this->respondCreated(['status' => false, 'message' => 'Faq for driver list not found ..!','data' => []]);
            }   
          }catch(\Exception $ex){
                return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
          }
    }

        /*
    * get driver all cancel reason list details
    *
    */
    public function getAllCancelReason()
    {   
        try{            
          $cancelReason = new BookingCancelReason();
          $cancelReason = $cancelReason->where('status',1)->where('deleted_at', null)->findAll();
          if(isset($cancelReason) && !empty($cancelReason)){ // driver exist provide response
            return $this->respondCreated(['status' => true, 'message' => 'Driver Cancellation Reason list successfully..!','data' => $cancelReason]);
            }else{ // new driver 
                return $this->respondCreated(['status' => false, 'message' => 'Driver Cancellation Reason list not found ..!','data' => []]);
            }   
        }catch(\Exception $ex){
                return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
        }
    }

 /*
    * get driver all complain list details
    *
    */
    public function  getAllDriverComplain(){   
        try{            
          $ComplainReason = new ComplainReason();
          $ComplainReason = $ComplainReason->where('status',1)->where('type','driver')->where('deleted_at', null)->findAll();
        
          if(isset($ComplainReason) && !empty($ComplainReason)){ // driver exist provide response
            return $this->respondCreated(['status' => true, 'message' => 'Driver complain reason list successfully..!','data' => $ComplainReason]);
            }else{ // new driver 
                return $this->respondCreated(['status' => false, 'message' => 'Driver complain reason list not found ..!','data' => []]);
            }   
        }catch(\Exception $ex){
                return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
        }
    }
  
    /*
    * add driver complains 
    *
    */
    public function  addDriverCancelReason(){  
        try{
            $validation = \Config\Services::validation();  
                
             
                $check = $this->validate
                ([
                    'subject' => [
                        'rules'	=> ['required'],
                    ],
                    'message' => [
                        'rules'	=> ['required'],
                   ],
                    ]);
           
            if(!$check) 
            {  // validation errors occurs
                return $this->respondCreated(['status' => false, 'message' => 'Validation errors occurs..!','data' => [],'errors' => $this->validator->getErrors()]);
            } 
            $userModel = new Users(); 
            $userData = $userModel->find($this->request->getVar('user_id'));
            
           if($userData['role_id']=='7')
          {
              $complain = new Complaint();
            $complain->insert
            ([
                'user_id'=>$this->request->getVar('user_id'),
                'name' => $userData['fullname'],
                'phone'  => $userData['mobile'],
                'email'  => $userData['email'],
                'subject'=>$this->request->getVar('subject'),
                'message'  =>$this->request->getVar('message'),
                'status' =>'0',
                'type'  => 'driver',            
            ]);
            return $this->respondCreated(['status' => true, 'message' => 'Driver complain reason added successfully..!']);
        }
        else
        {
            return $this->respondCreated(['status' => false, 'message' => 'invaalid user']);
        }
            }catch(\Exception $ex){
                return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
            } 
      
    }

  
}
