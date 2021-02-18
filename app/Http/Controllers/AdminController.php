<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\restaurant_info;
use App\Models\menu;
use App\Models\expense;
use App\Models\order;
use App\Models\customer;
use App\Models\menu_category;
use App\Models\table_unique_id;
use Hash;
use Session;
use Auth;
use DB;

class AdminController extends Controller
{

  public $res_id;
  public $auth_user_id;

    //frontend start




    public function owner_home()
    {
        date_default_timezone_set('Asia/Dhaka');
        $todays_date = date('Y-m-d');
        $todays_month = date('m');

        $orders = order::where('created_at','Like',$todays_date."%")->get();
        $todays_sell_amount = 0;
        $todays_sell_unit = 0;
        foreach($orders as $order)
        {
            $price = $order->menu->price * $order->quantity;
            $todays_sell_amount+=$price;
            $todays_sell_unit+=$order->quantity;
        }

        $orders = order::where('created_at','Like',"%".$todays_month."%")->get();
        $monthly_sell_amount = 0;
        $monthly_sell_unit = 0;
        foreach($orders as $order)
        {
            $price = $order->menu->price * $order->quantity;
            $monthly_sell_amount+=$price;
            $monthly_sell_unit+=$order->quantity;
        }








        return view('owner.dashboard',compact('todays_sell_unit','todays_sell_amount','monthly_sell_unit','monthly_sell_amount'));
    }
    public function logout()
    {

        auth()->logout();
        return redirect()->route('home');

    }

    public function login(Request  $request)
    {
        $credentials = array(
            'email' => $request->email,
            'password'=>$request->password
            );
            if (auth()->attempt($credentials)) {
                $user_role = User::where('email',$request->email)->first()->user_role;
                if($user_role=='owner')
                {
                    return redirect()->route('owner_home');
                }
                else
                {
                    return redirect()->route('admin_home');
                }

            }
            else
            {
                return redirect()->back()->with('error','Email and password credential are wrong');
            }
    }

    public function order_place(Request $request)
    {
        $customer = customer::create(['name'=>$request->customer_name,'mobile_number'=>$request->customer_contact_no]);
        $customer_id = $customer->id;
        $total = 0;
        $table_no = Session::get('table_no');
        $res_id = Session::get('res_id');
        $cart = session()->get('cart');
        foreach( $cart as $id => $details)
        {
            $total += $details['price'] * $details['quantity'];
        }
        foreach($cart as $id => $details)
        {
            order::create(['res_id'=>$res_id,'menu_id'=>$id,'table_no'=>$table_no,'quantity'=>$details['quantity'],'customer_id'=>$customer_id]);
        }
        session()->forget('cart');
        return redirect('main_menu')->with('success', 'Your order has been placed. Thanks for your order!');
    }

    public function checkout()
    {
        $total = 0;
        $cart = session()->get('cart');
        foreach( $cart as $id => $details)
        {
            $total += $details['price'] * $details['quantity'];
        }
        return view('frontend.checkout',compact('cart','total'));
    }

    public function cart_update(Request $request)
    {
        // $myfile = fopen("test2.txt", "a+") or die("Unable to open file!");
        // fwrite($myfile,$request->quantity."\n");

       // file_put_contents('test.txt',$request->id.' '.$request->quantity);
        if($request->id && $request->quantity)
        {
            $cart = session()->get('cart');
            $cart[$request->id]["quantity"] = $request->quantity+1;
              session()->put('cart', $cart);
            //session()->flash('success', 'Cart updated successfully');
        }
    }

    public function cart_update_dec(Request $request)
    {
        // $myfile = fopen("test2.txt", "a+") or die("Unable to open file!");
        // fwrite($myfile,$request->quantity."\n");

       // file_put_contents('test.txt',$request->id.' '.$request->quantity);
        if($request->id && $request->quantity)
        {
            $cart = session()->get('cart');
            $cart[$request->id]["quantity"] = $request->quantity-1;
              session()->put('cart', $cart);
            //session()->flash('success', 'Cart updated successfully');
        }
    }
    public function get_cart_data()
    {
       //session()->forget('cart');
        $data ='';
        $total = 0;
        $cart =session()->get('cart');
        file_put_contents("test.txt",json_encode($cart));
        foreach( $cart as $id => $details)
        {
            $total += $details['price'] * $details['quantity'];

            $data.='  <tr>
            <td class="product-thumbnail"><a href="#"><img src="menu_photos/'.$details['photo'].'" alt="product img" /></a></td>
            <td class="product-name"><a href="#">'. $details['name'].'</a></td>
            <td class="product-price"><span class="amount">'.$details['price'].'</span></td>
            <td class="product-quantity" style="padding: 20px 2px">
            <div>
            <label for="name"></label>
                <div class="dec button" onclick="dec('.$id.')">-</div>
                <input class="quantity quantity-'.$id.'" value="'.$details['quantity'].'" min="0" name="quantity" type="number" id="quantity-'.$id.'"   />
                    <input type="hidden" id="input_quantity-'.$id.'">
                <div class="inc button" onclick="inc('.$id.')">+</div>
            </div>



            </td>
            <td class="product-subtotal">'.$details['price']*$details['quantity'].'</td>
            <td class="product-remove">  <button class="cartbox__item__remove" onclick = "delete_cart('.$id.')">
            <i class="fa fa-trash"></i>
        </button></td>
        </tr>';
        }

        $vat = $total*.15;
        $sub_total = floor($total+$vat);
        $data2 ='';
        $data2 = ' <div class="cartbox-total d-flex justify-content-between">
        <ul class="cart__total__list">
            <li>Cart total</li>
            <li>Vat</li>
        </ul>
        <ul class="cart__total__tk">
            <li>Tk '.$total.'</li>
            <li>Tk '.$vat.'</li>
        </ul>
    </div>
    <div class="cart__total__amount">
        <span>Grand Total</span>
        <span>Tk '.$sub_total.'</span>
    </div>

    <div class="cartbox__buttons">

    <a class="food__btn" href="checkout"><span>Proceed to Checkout</span></a>
</div>
<script src="assets/frontend/js/cart.js?"'.time().'></script>
    ';

        $main_data = ['cart_data'=>$data,'cart_footer'=>$data2];
        echo json_encode($main_data);


    }
    public function view_cart()
    {
        $cart = session()->get('cart');
        return view('frontend.cart',compact('cart'));
    }

    public function cart_delete($id)
    {

            $cart = session()->get('cart');
            if(isset($cart[$id])) {
                unset($cart[$id]);
                session()->put('cart', $cart);
            }


    }

    public function get_cart_box()
    {

        $data ='';
        $total = 0;
        $cart = session()->get('cart');


        //file_put_contents('test.txt',json_encode($cart));
       foreach( $cart as $id => $details)
       {
        $total += $details['price'] * $details['quantity'];

        $data.=

       ' <div class="cartbox__item">
            <div class="cartbox__item__thumb">
                <a href="product-details.html">
                    <img src="menu_photos/'.$details['photo'].'" alt="small thumbnail">
                </a>
            </div>
            <div class="cartbox__item__content">
                <h5><a href="product-details.html" class="product-name">'.$details['name'].'</a></h5>
                <p>Qty: <span>'.$details['quantity'].'</span></p>
                <span class="price">'.$details['quantity']*$details['price'].'</span>
            </div>
            <button class="cartbox__item__remove" onclick = "delete_cart('.$id.')">
                <i class="fa fa-trash"></i>
            </button>
        </div>


    </div>';
       }
       $data.=' <div class="cartbox__total">
       <ul>

           <li class="grandtotal">Total<span class="price">'.$total.'</span></li>
       </ul>
     </div>
        <div class="cartbox__buttons">
                <a class="food__btn" href="view_cart"><span>View cart</span></a>
                <a class="food__btn" href="checkout"><span>Checkout</span></a>
            </div>';

            echo $data;

    }
    public function get_cart_count()
    {
        if(session()->has('cart'))
        {
        $total_product = count(session()->get('cart'));
        }
        else
        {
            $total_product = 0;
        }
        echo $total_product;
    }

    public function cart_add($id)
    {

        $product = menu::find($id);

        $cart = session()->get('cart');
        // if cart is empty then this the first product
        if(!$cart) {
            $cart = [
                    $id => [
                        "name" => $product->name,
                        "quantity" => 1,
                        "price" => $product->price,
                        "photo" => $product->image
                    ]
            ];
            session()->put('cart', $cart);
            //return redirect()->back()->with('success', 'Product added to cart successfully!');
        }
        else{
        // if cart not empty then check if this product exist then increment quantity
        if(isset($cart[$id])) {
            $update_quantity = $cart[$id]['quantity'] +1;
            $cart[$id]['quantity'] = $update_quantity;
            session()->put('cart', $cart);
            file_put_contents('test.txt',json_encode($cart));
            //return redirect()->back()->with('success', 'Product added to cart successfully!');
        }
        else{
        // if item not exist in cart then add to cart with quantity = 1
        $cart[$id] = [
            "name" => $product->name,
            "quantity" => 1,
            "price" => $product->price,
            "photo" => $product->image
        ];
        session()->put('cart', $cart);
             }
            }



       // return redirect()->back()->with('success', 'Product added to cart successfully!');
    }


    public function check_table_code(Request $request)
    {
        $res = table_unique_id::where('unique_number',$request->unique_number)->first();
        if($res)
        {
        Session::put('table_no',$request->unique_number);
        $res_id = $res->res_id;//res_auth
        Session::put('res_id',$res_id);
        $datas = menu::where('res_id',$res_id)->get();
        $datas = $datas->groupBy('category_id');
        $all_foods = menu::where('res_id',$res_id)->get();
        $current_date = date("Y-m-d");
        $current_date = date("Y-m-d", strtotime($current_date."+1 days"));
        $previous_one_month_date = date("Y-m-d", strtotime($current_date."-30 days"));
        $previous_one_week_date = date("Y-m-d", strtotime($current_date."-7 days"));

       //whereBetween('created_at',[$current_date,$previous_one_month_date])
        $recomended = order::whereBetween('created_at',[$previous_one_month_date,$current_date])->where('active_status',1)->where('res_id',$res_id)->get();
       // file_put_contents('test.txt',$current_date." ".$previous_one_month_date." ".json_encode($recomended));
        $recomended = $recomended->groupBy('menu_id')->map->count();

        $menu_id_recomended = array();
        foreach($recomended as $menu_id =>$menu)
        {
            array_push($menu_id_recomended,$menu_id);
        }

        //$menu_id_recomended = json_encode($recomended);
//file_put_contents('test.txt',json_encode($menu_id_recomended)." ".$recomended);
        $recomended = menu::whereIn('id', $menu_id_recomended)->get();

        $fow = order::whereBetween('created_at',[$previous_one_week_date,$current_date])->where('active_status',1)->where('res_id',$res_id)->get();
        // file_put_contents('test.txt',$current_date." ".$previous_one_month_date." ".json_encode($recomended));
         $fow = $fow->groupBy('menu_id')->map->count();

         $menu_id_fow = array();
         foreach($fow as $menu_id =>$menu)
         {
             array_push($menu_id_fow,$menu_id);
         }

         //$menu_id_recomended = json_encode($recomended);
 //file_put_contents('test.txt',json_encode($menu_id_recomended)." ".$recomended);
         $food_of_weeks = menu::whereIn('id', $menu_id_recomended)->get();

        // foreach($recomended as $id => $details)
        // {
        //     array_push($menu_id_recomended,$details[0]);
        // }
       // file_put_contents('test.txt',json_encode($recomended));
        $categories = menu_category::where('res_id',$res_id)->get();
        // file_put_contents('test.txt',json_encode($data2));
        return view('frontend.menu',compact('datas','categories','all_foods','recomended','food_of_weeks'));
        }
        else
        {
            return redirect()->to('/')->with('error','Restaurant Not Found');
        }
    }


    public function frontend_index(Request $request)
    {
       // file_put_contents('test.txt',"gello");
        $res_id = Session::get('res_id');

        $datas = menu::where('res_id',$res_id)->get();
        $datas = $datas->groupBy('category_id');
        $all_foods = menu::where('res_id',$res_id)->get();


       //
       $current_date = date("Y-m-d");
       $current_date = date("Y-m-d", strtotime($current_date."+1 days"));
       $previous_one_month_date = date("Y-m-d", strtotime($current_date."-30 days"));
       $previous_one_week_date = date("Y-m-d", strtotime($current_date."-7 days"));

      //whereBetween('created_at',[$current_date,$previous_one_month_date])
       $recomended = order::whereBetween('created_at',[$previous_one_month_date,$current_date])->where('active_status',1)->where('res_id',$res_id)->get();
      // file_put_contents('test.txt',$current_date." ".$previous_one_month_date." ".json_encode($recomended));
       $recomended = $recomended->groupBy('menu_id')->map->count();

       $menu_id_recomended = array();
       foreach($recomended as $menu_id =>$menu)
       {
           array_push($menu_id_recomended,$menu_id);
       }

       //$menu_id_recomended = json_encode($recomended);
//file_put_contents('test.txt',json_encode($menu_id_recomended)." ".$recomended);
       $recomended = menu::whereIn('id', $menu_id_recomended)->get();

       $fow = order::whereBetween('created_at',[$previous_one_week_date,$current_date])->where('active_status',1)->where('res_id',$res_id)->get();
       // file_put_contents('test.txt',$current_date." ".$previous_one_month_date." ".json_encode($recomended));
        $fow = $fow->groupBy('menu_id')->map->count();

        $menu_id_fow = array();
        foreach($fow as $menu_id =>$menu)
        {
            array_push($menu_id_fow,$menu_id);
        }

        //$menu_id_recomended = json_encode($recomended);
//file_put_contents('test.txt',json_encode($menu_id_recomended)." ".$recomended);
        $food_of_weeks = menu::whereIn('id', $menu_id_recomended)->get();

        $categories = menu_category::where('res_id',$res_id)->get();
        // file_put_contents('test.txt',json_encode($data2));
        return view('frontend.menu',compact('datas','categories','all_foods','recomended','food_of_weeks'));


    }

    //frontend end

    //Restaurant Start
    public function show_all_restaurant()
    {
        $res_info = restaurant_info::get();

        return view('admin.all_restaurant',['datas'=>$res_info]);
    }
    public function add_restaurant_ui()
    {
        return view('admin.add_restaurant');
    }

    public function edit_restaurant_ui(Request $request)
    {
        $id = $request->id;
        $res_info = restaurant_info::where('id',$id)->first();
        return view('admin.edit_restaurant_info',['data'=>$res_info]);
    }
    public function update_restaurant(Request $request)
    {
        $request->validate([
            'restaurant_name' => 'required',
            'email'=>'required',
            'restaurant_contact_no' => 'required',
            'restaurant_address'=>'required',


        ]);
        $user_id = restaurant_info::where('id',$request->id)->first()->user_id;
        $user = user::where('id',$user_id)->update(['email'=>$request->email]);
        $image = time().'.'.request()->image->getClientOriginalExtension();

        $request->image->move(public_path('../res_photos'), $image);
        restaurant_info::where('id',$request->id)->update(['restaurant_contact_no'=>$request->restaurant_contact_no,'restaurant_address'=>$request->restaurant_address,'restaurant_name'=>$request->restaurant_name,'restaurant_image'=>$image]);
        return redirect()->route('show_all_restaurant')->with('success','Data Updated Successfully');
    }
    public function add_restaturant(Request $request)
    {
        $request->validate([
            'restaurant_name' => 'required',
            'email'=>'required|unique:users',
            'restaurant_contact_no' => 'required',
            'restaurant_address'=>'required',
            'password' => 'required|confirmed',

        ]);
        $user = user::create(['email'=>$request->email,'password'=>Hash::make($request->password),'user_role'=>'owner']);
        $image = time().'.'.request()->image->getClientOriginalExtension();

        $request->image->move(public_path('../res_photos'), $image);
        restaurant_info::create(['user_id'=>$user->id,'restaurant_contact_no'=>$request->restaurant_contact_no,'restaurant_address'=>$request->restaurant_address,'restaurant_name'=>$request->restaurant_name,'restaurant_image'=>$image]);
        return redirect()->route('show_all_restaurant')->with('success','New Restaurant Create Successfully');


    }
    public function delete_res_data(Request $request)
    {
        $id = $request->id;
        restaurant_info::where('id',$id)->delete();
    }
    //Restaurant End

    //Menu Start

    public function show_all_menu()
    {
        $user_id = Auth::user()->id;
        $res_id = restaurant_info::where('user_id',$user_id)->first()->id;
        $res_info = menu::where('res_id',$res_id)->get();

        return view('owner.all_menu',['datas'=>$res_info]);
    }
    public function add_menu_ui()
    {
        $user_id = Auth::user()->id;
        $res_id = restaurant_info::where('user_id',$user_id)->first()->id;

        $categories = menu_category::where('res_id',$res_id)->get();
        return view('owner.add_menu',['categories'=>$categories]);
    }

    public function edit_menu_ui(Request $request)
    {
        $id = $request->id;
        $user_id = Auth::user()->id;
        $res_id = restaurant_info::where('user_id',$user_id)->first()->id;

        $categories = menu_category::where('res_id',$res_id)->get();
        $res_info = menu::where('id',$id)->first();
        return view('owner.edit_menu_info',['data'=>$res_info,'categories'=>$categories]);
    }
    public function update_menu(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'category_id'=>'required',
            'description'=>'required',
            'price' => 'required'


        ]);
        if($request->image){
        $image = time().'.'.request()->image->getClientOriginalExtension();
        $request->image->move(public_path('../menu_photos'), $image);
        menu::where('id',$request->id)->update(['name'=>$request->name,'price'=>$request->price,'description'=>$request->description,'category_id'=>$request->category_id,'image'=>$image]);
        }
        else
        {
            menu::where('id',$request->id)->update(['name'=>$request->name,'price'=>$request->price,'description'=>$request->description,'category_id'=>$request->category_id]);
        }

       return redirect()->route('show_all_menu')->with('success','Menu Updated Successfully');
    }
    public function add_menu(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'category_id'=>'required',
            'description'=>'required',
            'price' => 'required',
            'image'=>'required',

        ]);

        $image = time().'.'.request()->image->getClientOriginalExtension();
        $request->image->move(public_path('../menu_photos'), $image);
        $user_id = Auth::user()->id;
        $res_id = restaurant_info::where('user_id',$user_id)->first()->id;
        menu::create(['res_id'=>$res_id,'name'=>$request->name,'price'=>$request->price,'description'=>$request->description,'category_id'=>$request->category_id,'image'=>$image]);
        return redirect()->route('show_all_menu')->with('success','New menu Create Successfully');


    }
    public function delete_menu_data(Request $request)
    {
        $id = $request->id;
        menu::where('id',$id)->delete();
    }
    public function menu_active_status_update(Request $request)
    {

        $id = $request->id;
        $status = menu::where('id', $id)->first()->active_status;
        if ($status == 1)
        {
            menu::where('id', $id)->update(['active_status' => 0]);
        }
        else
        {
            menu::where('id', $id)->update(['active_status' => 1]);
        }

    }
    //Menu End

    //Category Start

    public function show_all_category()
    {

        $user_id = Auth::user()->id;
        $res_id = restaurant_info::where('user_id',$user_id)->first()->id;
        $res_info = menu_category::where('res_id',$res_id)->get();

        return view('owner.all_category',['datas'=>$res_info]);
    }
    public function add_category_ui()
    {
        return view('owner.add_category');
    }

    public function edit_category_ui(Request $request)
    {
        $id = $request->id;
        $res_info = menu_category::where('id',$id)->first();
        return view('owner.edit_category_info',['data'=>$res_info]);
    }
    public function update_category(Request $request)
    {
        $request->validate([
            'category_name' => 'required',



        ]);


        menu_category::where('id',$request->id)->update(['category_name'=>$request->category_name]);
        return redirect()->route('show_all_category')->with('success','Data Updated Successfully');
    }
    public function add_category(Request $request)
    {
        $request->validate([
            'category_name' => 'required',

        ]);
        $user_id = Auth::user()->id;
        $res_id = restaurant_info::where('user_id',$user_id)->first()->id;


       $request['res_id'] = $res_id;
       menu_category::create($request->all());
        return redirect()->route('show_all_category')->with('success','New category Create Successfully');


    }
    public function delete_category_data(Request $request)
    {
        $id = $request->id;
        menu_category::where('id',$id)->delete();
    }

    public function category_active_status_update(Request $request)
    {

        $id = $request->id;
        $status = menu_category::where('id', $id)->first()->status;
        if ($status == 1)
        {
            menu_category::where('id', $id)->update(['status' => 0]);
        }
        else
        {
            menu_category::where('id', $id)->update(['status' => 1]);
        }

    }

    //Category End

    //table id start

    public function show_all_table_id()
    {
        $user_id = Auth::user()->id;
        $res_id = restaurant_info::where('user_id',$user_id)->first()->id;
        $res_info = table_unique_id::where('res_id',$res_id)->get();

        return view('owner.all_table_id',['datas'=>$res_info]);
    }
    public function add_table_id_ui()
    {
        return view('owner.add_table_id');
    }

    public function edit_table_id_ui(Request $request)
    {
        $id = $request->id;
        $res_info = table_unique_id::where('id',$id)->first();
        return view('owner.edit_table_id_info',['data'=>$res_info]);
    }
    public function update_table_id(Request $request)
    {
        $request->validate([
            'table_no' => 'required',

        ]);


        table_unique_id::where('id',$request->id)->update(['table_no'=>$request->table_no]);
        return redirect()->route('show_all_table_id')->with('success','Data Updated Successfully');
    }
    public function add_table_id(Request $request)
    {
        $request->validate([
            'table_no' => 'required',

        ]);
        $user_id = Auth::user()->id;
        $res_id = restaurant_info::where('user_id',$user_id)->first()->id;
        $unique_number =mt_rand(10000000,99999999);
       table_unique_id::create(['table_no'=>$request->table_no,'unique_number'=>$unique_number,'res_id'=>$res_id]);
        return redirect()->route('show_all_table_id')->with('success','New Table Create Successfully');


    }
    public function delete_table_id_data(Request $request)
    {
        $id = $request->id;
        table_unique_id::where('id',$id)->delete();
    }

    public function table_id_active_status_update(Request $request)
    {

        $id = $request->id;
        $status = table_unique_id::where('id', $id)->first()->status;
        if ($status == 1)
        {
            table_unique_id::where('id', $id)->update(['status' => 0]);
        }
        else
        {
            table_unique_id::where('id', $id)->update(['status' => 1]);
        }

    }


    //table id end

    //new order start

  public function show_all_new_order()
    {
        $user_id = Auth::user()->id;

        $res_id = restaurant_info::where('user_id',$user_id)->first()->id;
        $order_info = order::where('res_id',$res_id)->where('active_status',0)->groupBy('customer_id')->get();

        foreach($order_info as $order)

        {
            $total = 0;
            $customer_name = customer::where('id',$order->customer_id)->first()->name;
            $orders = order::where('active_status',0)->where('customer_id',$order->customer_id)->get();
            foreach($orders as $order_price)

            {
                $total+=$order_price->menu->price;
            }
            $vat = floor($total*.15);
            $total = $total+$vat;
            $table_no = table_unique_id::where('unique_number',$order->table_no)->first()->table_no;
            $order['customer_name'] = $customer_name;
            $order['table_no'] = $table_no;
            $order['total'] = $total;
         }


        // $res_info = menu::where('res_id',$res_id)->get();
        return view('owner.new_order',compact('order_info','total'));


    }

    public function bill_show(Request $request)
    {
        $datas = order::where('customer_id',$request->id)->get();
        $total = 0;

        foreach($datas as $order)

        {
            $total+=$order->menu->price*$order->quantity;

         }
         $vat = floor($total*.15);
         $sub_total = $total+$vat;


        $date = date('d-m-Y');
        // $data = rent_confirmation::where('id',$Request->id)->first();
        // $data['contract_end'] = date('Y-m-d H:i:s', strtotime('+1 years', strtotime($data->created_at)));
        // $data['total'] = (int)$data->advance_payment+(int)$data->apartment->apartment_rent;
        // $data['date'] = $date;
        return view('owner.bill',compact('datas','date','total','vat','sub_total'));
    }

    public function show_all_order()
    {
        $user_id = Auth::user()->id;

        $res_id = restaurant_info::where('user_id',$user_id)->first()->id;
        $order_info = order::where('res_id',$res_id)->where('active_status',1)->groupBy('customer_id')->get();

        $i=1;
        foreach($order_info as $order)

        {
            $total = 0;

            $customer_name = customer::where('id',$order->customer_id)->first()->name;
            $orders = order::where('active_status',1)->where('customer_id',$order->customer_id)->get();
            foreach($orders as $order_price)

            {
                $total+=$order_price->menu->price*$order_price->quantity;
            }
            $vat = floor($total*.15);
            $total = $total+$vat;
            $table_no = table_unique_id::where('unique_number',$order->table_no)->first()->table_no;
            $order['customer_name'] = $customer_name;
            $order['table_no'] = $table_no;
            $order['serial_no'] = $i++;
            $order['total'] = $total;
         }


        // $res_info = menu::where('res_id',$res_id)->get();
        return view('owner.all_order',compact('order_info'));


    }

    public function show_order_menu($customer_id)
    {
        $order_info = order::where('active_status',0)->where('customer_id',$customer_id)->get();
        $data = '';
        foreach($order_info as  $order)
        {
            $data.='
            <tr>
                <td>'.$order->menu->name.'</td>
                <td>'.$order->quantity.'</td>
            </tr>
            ';
        }
        echo $data;


    }

    public function show_completed_order_menu($customer_id)
    {
        $order_info = order::where('active_status',1)->where('customer_id',$customer_id)->get();
        $data = '';
        foreach($order_info as  $order)
        {
            $data.='
            <tr>
                <td>'.$order->menu->name.'</td>
                <td>'.$order->quantity.'</td>
            </tr>
            ';
        }
        echo $data;


    }
    public function confirm_payment($customer_id)
    {
        order::where('customer_id',$customer_id)->update(['active_status'=>1]);
    }
    //new order end

    //expense start
    public function show_all_expense()
    {
        $user_id = Auth::user()->id;
        $res_id = restaurant_info::where('user_id',$user_id)->first()->id;
        $res_info = expense::where('res_id',$res_id)->get();

        return view('owner.all_expense',['datas'=>$res_info]);
    }
    public function add_expense_ui()
    {


        return view('owner.add_expense');
    }

    public function edit_expense_ui(Request $request)
    {
        $id = $request->id;
        $user_id = Auth::user()->id;
        $res_id = restaurant_info::where('user_id',$user_id)->first()->id;


        $res_info = expense::where('id',$id)->first();
        return view('owner.edit_expense_info',['data'=>$res_info]);
    }
    public function update_expense(Request $request)
    {
        $request->validate([
            'expense_name' => 'required',
            'expense_amount'=>'required',
            'expense_note'=>'required',



        ]);



            expense::where('id',$request->id)->update(['expense_name'=>$request->expense_name,'expense_amount'=>$request->expense_amount,'expense_note'=>$request->expense_note]);


       return redirect()->route('show_all_expense')->with('success','Expense Updated Successfully');
    }
    public function add_expense(Request $request)
    {
         $request->validate([
        'expense_name' => 'required',
        'expense_amount'=>'required',
        'expense_note'=>'required',



    ]);




        $user_id = Auth::user()->id;
        $res_id = restaurant_info::where('user_id',$user_id)->first()->id;
        expense::create(['res_id'=>$res_id,'expense_name'=>$request->expense_name,'expense_amount'=>$request->expense_amount,'expense_note'=>$request->expense_note]);
        return redirect()->route('show_all_expense')->with('success','New expense Create Successfully');


    }
    public function delete_expense_data(Request $request)
    {
        $id = $request->id;
        expense::where('id',$id)->delete();
    }

    //expense end
    //salary start
    public function show_all_salary()
    {
        $user_id = Auth::user()->id;
        $res_id = restaurant_info::where('user_id',$user_id)->first()->id;
        $res_info = expense::where('res_id',$res_id)->where('expense_type','salary')->get();

        return view('owner.all_salary',['datas'=>$res_info]);
    }
    public function add_salary_ui()
    {


        return view('owner.add_salary');
    }

    public function edit_salary_ui(Request $request)
    {
        $id = $request->id;
        $user_id = Auth::user()->id;
        $res_id = restaurant_info::where('user_id',$user_id)->first()->id;


        $res_info = expense::where('id',$id)->first();
        return view('owner.edit_salary_info',['data'=>$res_info]);
    }
    public function update_salary(Request $request)
    {
        $request->validate([
            'expense_name' => 'required',
            'expense_amount'=>'required',
            'expense_note'=>'required',



        ]);



        expense::where('id',$request->id)->update(['expense_name'=>$request->expense_name,'expense_amount'=>$request->expense_amount,'expense_note'=>$request->expense_note,'expense_type'=>'salary']);


       return redirect()->route('show_all_salary')->with('success','salary Updated Successfully');
    }
    public function add_salary(Request $request)
    {
         $request->validate([
        'expense_name' => 'required',
        'expense_amount'=>'required',
        'expense_note'=>'required',



    ]);




        $user_id = Auth::user()->id;
        $res_id = restaurant_info::where('user_id',$user_id)->first()->id;
        expense::create(['res_id'=>$res_id,'expense_name'=>$request->expense_name,'expense_amount'=>$request->expense_amount,'expense_note'=>$request->expense_note,'expense_type'=>'salary']);
        return redirect()->route('show_all_salary')->with('success','New salary Create Successfully');


    }
    public function delete_salary_data(Request $request)
    {
        $id = $request->id;
        expense::where('id',$id)->delete();
    }
    //salary end
    //Report start
    function date_compare($a, $b)
    {
        $t1 = $a['created_at'];
        $t2 = $b['created_at'];
        return $t2 < $t1;
      // return bccomp($t2, $t1, 2);
    }
    public function show_all_report(Request $request)
    {
        $user_id = Auth::user()->id;
        $res_id = restaurant_info::where('user_id',$user_id)->first()->id;
        $from_date = $request->from_date;
        $to_date =
        $to_date = date("Y-m-d h:i:s", strtotime($request->to_date."+1 days"));
        $orders = order::whereBetween('created_at',[$from_date,$to_date])->where('active_status',1)->where('res_id',$res_id)->get();
        $sale_total = 0;
        $expense_total =0;
        foreach($orders as $order)
        {

            $price = $order->menu->price * $order->quantity;
            $sale_total+= $price;
            $order['price'] = $price;
            $order['type'] = 'debit';




        }

        $expenses = expense::whereBetween('created_at',[$from_date,$to_date])->where('res_id',$res_id)->get();
        foreach($expenses as $expense)
        {
            $expense_total+=$expense['expense_amount'];
            $expense['type'] = 'credit';


        }

        $collection = collect([$orders,$expenses]);
        $collection = $collection->collapse()->toArray();
        //$collection = $collection->sortBy('date');
        usort($collection,array($this,'date_compare'));


        $datas = json_decode(json_encode($collection));
        $i=1;
        foreach($datas as $data)
        {
            $data->sl_no = $i++;
            $data->date = date("Y-m-d h:i:s", strtotime($data->created_at));
        }

        $profit = $sale_total - $expense_total;

       return view('owner.all_report',compact('datas','profit','sale_total','expense_total'));
    }

    //Report end




}
