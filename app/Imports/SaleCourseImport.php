<?php

namespace App\Imports;

use App\Models\Sale;
use App\Models\ProductOrder;
use App\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use DB;
use App\Models\Webinar;

class SaleCourseImport implements ToModel,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        foreach($row as $key=>$value){
        $salesinstallments =DB::table('sales') 
        ->select('*')
        ->where('webinar_id',$row['webinar_id'])
        ->where('buyer_id',$row['user_id'])
        ->get();
        $webinars =DB::table('webinars') 
        ->select('*')
        ->where('id',$row['webinar_id'])
        ->first();
       if(!empty($value) && count($salesinstallments)==0){
           
        
         $itemType =  Sale::$webinar;
         $itemColumnName = 'product_order_id';
         $user = User::find($row['user_id']);
         
        //   $product = Product::find(/['product_id']);
        return new Sale([
                    'buyer_id' =>$row['user_id'],
                    'seller_id' => $webinars->creator_id,
                    'webinar_id' =>$row['webinar_id'],
                    // $itemColumnName => $itemId,
                    'type' => $itemType,
                    'manual_added' => true,
                    'payment_method' => Sale::$credit,
                    'amount' => 0,
                    'total_amount' => 0,
                    'created_at' => time(),
        ]);
       }
    
}
}
}
