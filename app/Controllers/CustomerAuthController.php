<?php

namespace App\Controllers;

use App\Models\Roles;
use App\Models\Users;
use Firebase\JWT\JWT;
use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;
use App\Models\Faq;
use App\Models\ComplainReason;
use App\Models\Complaint;
use App\Validation\Rules\ComplainReasonSubjectRule;

class CustomerAuthController extends BaseController
{
    use ResponseTrait;
    /*
    * login mobile is use for customer mobile exist or not if exist then generate JWT auth token and provide user details
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

            $roleModel = new Roles();
            $roleModel->select('id');
            $role_id = $roleModel->like('name','customer','both')->first();
            
            $userModel = new Users();
            $userExist = $userModel->where('mobile',$this->request->getVar('mobile'))->first();

            if(isset($userExist) && !empty($userExist)){ // customer exist generate JWT auth token
                if($userExist['role_id'] == $role_id['id']){
                    $userModel->update($userExist['id'], [
                        'online' => 1,                
                ]);
                $userExist = $userModel->find($userExist['id']);
                   return $this->respondCreated(['status' => true, 'message' => 'User details get successfully..! Login success ...!','data' => $userExist,'access_token' => JWT::encode(['iss' => 'localhost','aud' => 'localhost','exp' => time() + 3600,'data' => ['user_id' => $userExist['id'],'mobile' => $userExist['mobile'],'role' => $userExist['role_id']]],getenv('JWT_SECRET'),'HS256'),"refresh_token" => getenv('JWT_SECRET'),"token_type" => "Bearer","expires_in" => time() + 3600]);
                }else{
                    return $this->respondCreated(['status' => false, 'message' => "Number already register as Driver, you can't use this number as Customer...!",'data' => []]);
                }
            }else{ // new customer 
                return $this->respondCreated(['status' => false, 'message' => 'Mobile number not exist need to register...!','data' => []]);
            } 
        }catch(\Exception $ex){
            return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
        }       
    }


    /*
    * regiter customer details and create new JWT token
    *
    */
    public function registerCustomer()
    {      
        try{  
            $validation = \Config\Services::validation();                
            $check = $this->validate([
                'firstname' => [
                    'rules'	=> ['required','max_length[50]','string'],
                ],
                'mobile' => [
                'rules'	=> ['required','min_length[13]','max_length[13]'],
            ],
            'email' => [
                'rules'	=> ['required','valid_email','is_unique[ypc_users.email]'],
                ],
            ]);
            if (!$check) {  // validation errors occurs
                return $this->respondCreated(['status' => false, 'message' => 'Validation errors occurs..!','data' => [],'errors' => $this->validator->getErrors()]);
            }
            $userModel = new Users();        
            // get role id 
            $roleModel = new Roles();
            $roleModel->select('id');
            $role_id = $roleModel->like('name','customer','both')->first();

            $userExist = $userModel->where('mobile',$this->request->getVar('mobile'))->first();
            if(!isset($userExist) && empty($userExist)){ 
            // insert customer details
            $fullname =$this->request->getVar('firstname');
            $name =explode(" ", $fullname);
            $first_name = $name[0];
            $last_name='';
            
            // $last_name=ltrim($fullname, $first_name.' ');
            $userModel->insert([
                // 'first_name' => $this->request->getVar('firstname'),
                'fullname' => $fullname,
                'first_name'=>$fullname,
                'last_name'=>$last_name,

                'mobile'  => $this->request->getVar('mobile'),
                'email'  => $this->request->getVar('email'),
                'role_id'  => (isset($role_id) && !empty($role_id))?$role_id['id']:null,  
            ]);

            $userData = $userModel->find($userModel->getInsertID());
            if(isset($userData) && !empty($userData)){ // customer exist generate JWT auth token
                return $this->respondCreated(['status' => true, 'message' => 'User created successfully..!','data' => $userData,'access_token' => JWT::encode(['iss' => 'localhost','aud' => 'localhost','exp' => time() + 3600,'data' => ['user_id' => $userData['id'],'mobile' => $userData['mobile']]],getenv('JWT_SECRET'),'HS256'),"refresh_token" => getenv('JWT_SECRET'),"token_type" => "Bearer","expires_in" => time() + 3600]);
            }else{ // new customer 
                return $this->respondCreated(['status' => false, 'message' => 'Mobile number not exist need to register...!','data' => []]);
            }  
        }else{
            if($userExist['role_id'] == $role_id['id']){
                return $this->respondCreated(['status' => false, 'message' => 'Mobile number already exist please login...!','data' => []]);
            }else{
                return $this->respondCreated(['status' => false, 'message' => "Number already register as Driver, you can't use this number as Customer...!",'data' => []]);
            }
        }
        }catch(\Exception $ex){
            return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
        }      
    }

    public function updateProfile()
    {   
        try{
            $validation = \Config\Services::validation();        
            if(empty($this->request->getVar('email'))){  // optional email validation      
                $check = $this->validate([
                    'fullname' => [
                        'rules'	=> ['required','max_length[100]','string'],
                    ],
                    'mobile' => [
                        'rules'	=> ['required','min_length[13]','max_length[13]','is_unique[ypc_users.mobile,id,{user_id}]'],
                   ],
                   'birthdate' => [
                    'rules'	=> ['required','dobRules','valid_date[d-m-Y]'],
                    'gender' => [
                        'rules'	=> ['required','string'],
                    ],
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
                        'rules'	=> ['required','min_length[13]','max_length[13]','is_unique[ypc_users.mobile,id,{user_id}]'],
                   ],
                   'birthdate' => [
                    'rules'	=> ['required','dobRules','valid_date[d/m/Y]'],
                    'gender' => [
                        'rules'	=> ['required','string'],
                    ],
                    'errors' => [
                        'required' => 'The birthdate field is required.',
                        'dobRules' => 'Birthdate must be greater than today.',
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
            $fullname =$this->request->getVar('firstname');
            $name =explode(" ", $fullname);
            $first_name = $name[0];
            $last_name='';
           
           
            // insert driver details
            $userModel = new Users();  
            $userModel->update($this->request->getVar('user_id'), [
                'fullname' => $fullname,
                'first_name'=>$fullname,
                'last_name'=>$last_name,
                    'mobile'  => $this->request->getVar('mobile'),
                    'email'  => (!empty($this->request->getVar('email')))?$this->request->getVar('email'):null,
                    'gender'  => $this->request->getVar('gender'),
                    'dob'  => date('Y-m-d', strtotime(str_replace('/', '-', $this->request->getVar('birthdate')))),                
            ]);
    
            $userData = $userModel->find($this->request->getVar('user_id'));
            if(isset($userData) && !empty($userData)){ // driver exist provide response
                return $this->respondCreated(['status' => true, 'message' => 'Customer details updated successfully..!','data' => $userData]);
            }else{ // new driver 
                return $this->respondCreated(['status' => false, 'message' => 'Customer details not updated ..!','data' => []]);
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
            $file->move('./uploads/customer/profile_pic', $new_profile_img);
            
            $userModel = new Users();  
            $userModel->update($this->request->getVar('user_id'), [
             'picture' => '/uploads/customer/profile_pic/'.$new_profile_img,              
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

    public function getCustomerFaq()
    {  
        try{            
            $faq = new Faq();
            $faq = $faq->where('faq_for',1)->findAll();
            if(isset($faq) && !empty($faq)){ // faq exist provide response
              return $this->respondCreated(['status' => true, 'message' => 'Faq for customer list successfully..!','data' => $faq]);
            }else{ // new faq 
                return $this->respondCreated(['status' => false, 'message' => 'Faq for customer list not found ..!','data' => []]);
            }   
          }catch(\Exception $ex){
                return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
          }
    }
  
     /*
    * get customer all complain list details
    *
    */
    public function getCustomerComplains()
    {   
        try{            
          $ComplainReason = new ComplainReason();
          $ComplainReason = $ComplainReason->where('status',1)->where('type','user')->where('deleted_at', null)->findAll();
          if(isset($ComplainReason) && !empty($ComplainReason)){ // driver exist provide response
            return $this->respondCreated(['status' => true, 'message' => 'Customer complain reason list successfully..!','data' => $ComplainReason]);
            }else{ // new driver 
                return $this->respondCreated(['status' => false, 'message' => 'Customer complain reason list not found ..!','data' => []]);
            }   
        }catch(\Exception $ex){
                return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
        }
    }

     /*
    * add customer complains 
    *
    */

    public function  addCustomerCancelReason(){  
        try{
            $validation = \Config\Services::validation();  
                
          
                $check = $this->validate
                ([
                    'user_id' => [
                        'rules'	=> ['required'],
                    ],
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
           
           if($userData['role_id']=='8')
          {
              $complain = new Complaint();
            $complain->insert
            ([
                'user_id'=>$this->request->getVar('user_id'),
                'name' => $userData['first_name'],
                'phone'  => $userData['mobile'],
                'email'  => $userData['email'],
                'subject'=>$this->request->getVar('subject'),
                'message'  =>$this->request->getVar('message'),
                'status' =>'0',
                'type'  => 'user',            
            ]);
            return $this->respondCreated(['status' => true, 'message' => 'Customer complain reason added successfully..!']);
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
