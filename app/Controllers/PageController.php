<?php

namespace App\Controllers;
use App\Models\Setting;
use CodeIgniter\API\ResponseTrait;

use App\Controllers\BaseController;

class PageController extends BaseController
{
    use ResponseTrait;
    public function page($key)
    {   
        try{            
            $page = new Setting();
            $page = $page->select('id,key,value')->where('key',$key)->get()->getResult();
            if(isset($page) && !empty($page)){ // faq exist provide response
              return $this->respondCreated(['status' => true, 'message' => 'data fetched successfully..!','data' => $page]);
            }else{ // new page 
                return $this->respondCreated(['status' => false, 'message' => 'data not found ..!','data' => []]);
            }   
          }catch(\Exception $ex){
                return $this->respond(['status' => false, 'message' => 'Something wrong please contact admin ..! '.$ex->getMessage(). ' ...! '.$ex->getFile(). ' ...! '.$ex->getLine(). ' ...!','data' => []]);
          }
    }
}
