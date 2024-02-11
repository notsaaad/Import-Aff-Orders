<?php 

/*
 * Plugin Name:       Import Aff Orders
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Take Excel File From Affilates and Import it as order
 * Version:           1.10.3
 * Author:            Hatem Amir
 * Author URI:        https://www.facebook.com/hatem.amir.14
 * License:           GPL v2 or later
 *
*/


add_action( 'wp_footer', 'HatemCreateOrder' );

function HatemCreateOrder(){

  /*
  ================== HTML Action FROM ===========================

  
  
  <form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="file">
    <button type="submit" name="import">Import</button>
  </form>
  
  */

  if(isset($_POST['import'])) {

    if((isset($_FILES['file']) && is_array( $_FILES['file']))) {

        $csv = $_FILES['file'];
  
        if(isset($csv['tmp_name']) && !empty($csv['tmp_name'])) {
          $finfo = finfo_open(FILEINFO_MIME_TYPE);
          $mime = finfo_file($finfo, $csv['tmp_name']);
          finfo_close($finfo);
  
          $allowed_mime = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
  
          if(in_array($mime, $allowed_mime) && is_uploaded_file($csv['tmp_name'])) {
            $f = fopen($csv['tmp_name'], 'r');
            $LineCounter = count(file($csv['tmp_name']));

            $data = array();
  
            $Headers = fgetcsv($f);

  
            //Notes:: Did not Checkd For First Column
            if ( trim(strval($Headers[1])) == "Last Name"  && trim(strval($Headers[2])) == "email"  &&
            trim(strval($Headers[3])) == "Phone"
                && trim(strval($Headers[4])) == "address"  && trim(strval($Headers[5])) == "city"  && trim(strval($Headers[6])) == "Product" 
                && trim(strval($Headers[7])) =="QTY" && trim(strval($Headers[8])) =="Commission"){
                  for($i=1; $i<$LineCounter; $i++){
                    $Headers = fgetcsv($f);
                    $FirstName    = $Headers[0];
                    $LastName     = $Headers[1];
                    $Email        = $Headers[2];
                    $Phone        = $Headers[3];
                    $Address      = $Headers[4];
                    $city         = $Headers[5];
                    $Products     = $Headers[6];
                    $QTYs         = $Headers[7];
                    $comission    = $Headers[8];
                    
                      //========================== Start For Create Woocommerce Order =============================
  global $woocommerce;
  $ProductsIDArr  = explode('-', $Products);
  $QtyArr         = explode('-',$QTYs);
  $comissionArr   = explode('-',$comission);
  $qty = 1;
  $qtycounter = 0;
  $address = array(
      'first_name' => $FirstName,
      'last_name'  => $LastName,
      // 'email'  => $Email,
      'phone'      => $Phone,
      'address_1'  => $Address,
      'city'       => $city
  );

  $order = wc_create_order();
  foreach ($ProductsIDArr as $ProductID) {
    if ($QtyArr[$qtycounter] != '' ){
      $qty = $QtyArr[$qtycounter];
    }
    $order->add_product(get_product($ProductID),   $qty);
    $itemFee = new WC_Order_Item_Fee();
    $itemFee->set_name('Product ID : '. $ProductID);
    $itemFee->set_amount($comissionArr[$qtycounter]);
    $itemFee->set_total( $comissionArr[$qtycounter] );
    $order->add_item( $itemFee );
    $qtycounter++;
    $qty = 1;
  }

  $order->set_address($address, 'billing' );
  $order->calculate_totals();
  $order->update_status("on-hold"); 




    global $wpdb;

  $table_name = 'wp_wc_orders';
  $cu = array(
    'customer_id'=>get_current_user_id()
  );

  $wpdb->update($table_name,$cu,array('id'=>$order->id) );

  /*======= Get the Aff ID From DB =======*/

  $userID = get_current_user_id();
  
  $Table_NAME_AFF = 'wp_uap_affiliates';
  $tableName  = 'wp_uap_referrals';
  $results = $wpdb->get_results("SELECT id From $Table_NAME_AFF where uid=$userID");
  if($results[0]->id){
  $userAFFID = $results[0]->id;

    foreach ($comissionArr as $com) {
      $dataArr = array(
        'refferal_wp_uid'     =>  $userID,
        'affiliate_id'        =>  $userAFFID,
        'visit_id'            =>  0,
        'source'              =>  'woo',
        'reference'           =>  $order->id,
        'parent_referral_id'  =>   0,
        'child_referral_id'   =>   0,
        'amount'              =>  $com,
        'currency'            =>  'SAR',
        'status'              =>  1,
        'payment'             =>  0
    
      
      );
      $wpdb->insert($tableName, $dataArr);
      
    }

  }

  ?>
  <script>
    alert(" تم اتمام الطلبات سوف يتم تحويلك الي صفحة الطلبات");
    location.href="http://dropshipping-ksa.com/my-account/orders/";
  </script>
  <?php
                  }
                }else{
                  ?>
                  <script>
                    alert("برجاء التأكد من ترتيب صحة البيانات");
                  </script>
                  <?php
                }
  
  
  
  
  
  
  
          fclose($f);

          // die;
  
  
  }
        }else{
          ?>
          <script>
            alert("برجاء التأكد من من ادخال ملف بامتداد csv");
          </script>
          <?php
        }
    }
  
  
  
  
  }
}

// add_action("wp_head","HatemTestInsertDB");

// function HatemTestInsertDB(){
  // global $wpdb;
  // $tableName  = 'wp_uap_referrals';
  // $dataArr = array(
  //   'refferal_wp_uid'     =>  3,
  //   'affiliate_id'        =>  1,
  //   'visit_id'            =>  0,
  //   'source'              =>  'woo',
  //   'reference'           =>  156,
  //   'parent_referral_id'  =>   0,
  //   'child_referral_id'   =>   0,
  //   'amount'              =>  200,
  //   'currency'            =>  'SAR',
  //   'status'              =>  1,
  //   'payment'             =>  0
  // );

  // $Status = $wpdb->insert($tableName,$dataArr);

  // print_r($Status);



  // $userID = get_current_user_id();
  
  // $tableName  = 'wp_uap_referrals';
  // $results = $wpdb->get_results("SELECT id From $tableName where uid=$userID");

  // print_r($results);


// }

