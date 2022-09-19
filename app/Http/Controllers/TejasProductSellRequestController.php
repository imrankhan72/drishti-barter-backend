<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\TejasProductSellRequest;
use App\SellRequestProduct;
use App\PersonProduct;
use Carbon\Carbon;
use App\SellRequestComment;
use App\Product;
use App\DrishteeMitra;
use App\Ledger;
use Auth;
use App\UserProduct;
use App\UserProductLog;
use Log;
class TejasProductSellRequestController extends Controller
{
    /**
     * @param App\DrishteeMitra $dm_id
     * @param Illuminate\Http\Request $request
     * @return App\TejasProductSellRequest $tpsr, App\SellRequestProduct $str
     * do store TejasProductSellRequest product request
     * 
     */
    public function requestStore($dm_id, Request $request){

        $regex = "/^(?=.+)(?:[1-9]\d*|0)?(?:\.\d+)?$/";

        $date =  Carbon::now(); 
        $validation = Validator::make($request->all(),[
            "data.*.product_id"   => "required|exists:products,id",
            "data.*.product_name" => "required|string",
            "data.*.quantity"     => array('required','regex:'.$regex),
            "data.*.unit"         => "required|string",
            "data.*.lp_applicable"=> array('required','regex:'.$regex)
        ]);
        // return response()->json($request->all(),200);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        $temp['requester_id'] = $dm_id;
        $temp['request_date'] = $date;
        $temp['status'] = 'Open';
        $tpsr = TejasProductSellRequest::create($temp);
        $resarray = [];
        foreach ($request->all() as $data) {
           $data['sell_request_id'] = $tpsr->id;
           // return response()->json($data,200);
           $str = SellRequestProduct::create($data);
           $str->product = $str->product;
           array_push($resarray,$str);
         }
        return response()->json(["res"=>$resarray,"tejas"=>$tpsr],200);
    }

    /**
     * @param App\DrishteeMitra $dm_id
     * @return App\TejasProductSellRequest $tpsrs
     * do return list of object TejasProductSellRequest with list of sellRequestProducts and sellRequestComments
     * 
     */
    public function getSellRequestList($dm_id){
        $tpsrs = TejasProductSellRequest::where('requester_id',$dm_id)->get();
        return response()->json($tpsrs->load('sellRequestProducts.product.units','sellRequestComments','approvedByAdmin'),200);
    }

    /** 
     * @return App\TejasProductSellRequest $tpsr
     * do return single object of TejasProductSellRequest with DrishteeMitra object, list of sellRequestProducts and sellRequestComments
     * 
     */
    public function getSellRequestListAdmin(){
        $sellRequests = TejasProductSellRequest::orderBy('request_date','DESC')->get();
        return response()->json($sellRequests->load('sellRequestProducts.product.units','dm.dmProfile','sellRequestComments','approvedByAdmin'));
    }

     /**
     * @param App\TejasProductSellRequest $request_id
     * @param App\DrishteeMitra $requester_id
     * @return App\TejasProductSellRequest $tpsr
     * do return single object of TejasProductSellRequest with list of sellRequestProducts and sellRequestComments
     * 
     */
    public function getSellRequest($requester_id, $request_id){
        $tpsr = TejasProductSellRequest::find($request_id);
        return response()->json($tpsr->load('sellRequestProducts.product.units','sellRequestComments','approvedBy'),200);
    }

    /**
     * @param App\TejasProductSellRequest $request_id
     * @param Illuminate\Http\Request $request
     * @return App\TejasProductSellRequest $sellRequestComment
     * do change status of TejasProductSellRequest, create transaction history, update ledger and return TejasProductSellRequest object
     * 
     */
    public function updateStatusRequestAdmin(Request $request, $request_id){
        $validation = Validator::make($request->all(),[
            "status"   => "required|string",
        ]);

        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        if($request['status'] == 'Accepted') {
            $user = Auth::User();
            $sellRequest = TejasProductSellRequest::find($request_id);

            if($sellRequest->status == 'Accepted'){
                return response()->json(["error"=>"Status Already Accepted"],404);
            }
            $sellRequest->approved_by = $user->id;
            $sellRequest->save();
            $dm = DrishteeMitra::find($sellRequest->requester_id);
            $ledger =  Ledger::find($dm->ledger_id);
            $user_ledger = Ledger::find($user->ledger_id);

            $total_lp = 0;

            $sellRequestProducts = SellRequestProduct::where('sell_request_id',$request_id)->get();
            foreach ($sellRequestProducts as $key) {
                $pp = PersonProduct::where('person_id',$dm->person_id)->where('product_id',$key->product_id)->first();
                if($pp->quantity_available >= $key->quantity){
                    $total_lp += $key['lp_applicable'];
                    $pp->quantity_available = $pp->quantity_available - $key->quantity;
                    $pp->save();
                }
                $up = UserProduct::where('user_id',$user->id)->where('product_id',$key->product_id)->first();
                if($up == null) {
                    $data['user_id'] = $user->id;
                    $data['product_id'] = $key->product_id;
                    $data['quantity_available'] = $key->quantity;
                    $data['product_lp'] = $key->lp_applicable / $key->quantity; 
                    $usp = UserProduct::create($data);
                    $usp->tag = 'From Tejas Sell Request';
                    $usp->save(); 
                    $temp['user_product_id'] = $usp->id;
                    $temp['product_id'] = $usp->product_id;
                    $temp['quantity'] = $usp->quantity_available;
                    $temp['product_lp'] = $usp->product_lp;
                    $temp['message'] = $usp->quantity_available.' Quantity added By Tejas Product'; 
                    $upl = UserProductLog::create($temp);
                    //$up->update($request->all()); 
                }
                else {
                  $up->quantity_available = $up->quantity_available + $key->quantity;
                  $temp['user_product_id'] = $up->id;
                  $temp['product_id'] = $up->product_id;
                  $temp['quantity'] = $up->quantity_available;
                  $temp['product_lp'] = $up->product_lp;
                  $temp['message'] = $up->quantity_available + $key->quantity > $up->quantity_available ? ' Quantity increased with '.$key->quantity : ' Quantity Decreased with '.$up->quantity_available - $key->quantity; 
                  $upl = UserProductLog::create($temp);
                  $up->save();
                  
                }
            }
            // Log::info("1 gaja".$total_lp);
            $sellRequest->createTejasSellLedgerTransactions('Success',$user_ledger->id,'Dr',$total_lp,'Product Buy From Mitra',$user_ledger->balance - $total_lp);
            $sellRequest->createTejasSellLedgerTransactions('Success',$ledger->id,'Cr',$total_lp,'Product Sell To Drishtee',$ledger->balance + $total_lp,$dm->person_id);
            $user_ledger->balance = $user_ledger->balance - $total_lp;
            $user_ledger->save();
            $ledger->balance = $ledger->balance + $total_lp;
            $ledger->save();
            $sellRequest->status = $request->status;
            if($sellRequest->save()){
                return response()->json($sellRequest->load('dm'),200);
            }
        }else{        
            return response()->json(["error"=>"Not Change Status"],404);
        }
    }

    /**
     * @param App\User $commentor_id
     * @param App\TejasProductSellRequest $request_id
     * @param Illuminate\Http\Request $request
     * @return App\SellRequestComment $sellRequestComment
     * do store comment by admin and return SellRequestComment object
     * 
     */
    public function addCommentRequestAdmin(Request $request, $commentor_id, $request_id){
        $date =  Carbon::now(); 
        $validation = Validator::make($request->all(),[
            "comment" => "required|string",
        ]);
        if($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors,400);
        }
        $sellRequest = TejasProductSellRequest::find($request_id);
        $request['sell_request_id'] = $sellRequest->id;
        $request['commentor_id'] = $commentor_id;
        $request['date_time'] = $date;
        $sellRequestComment = SellRequestComment::create($request->all());
        
        return response()->json($sellRequestComment,200);
    }

    /**
     * @param App\DrishteeMitra $requester_id
     * @param App\TejasProductSellRequest $request_id
     * @return App\TejasProductSellRequest $sellRequest
     * do change status of TejasProductSellRequest and return same object
     * 
     */
    public function cancelSellRequest($requester_id, $request_id){
        $collection = [];
        $sellRequest = TejasProductSellRequest::find($request_id);
        $sellRequest->status = "Cancelled by DM";
        if($sellRequest->save()){
            return response()->json($sellRequest,200);
        }
        return response()->json($collection,200);
    }

    /**
     * @param App\TejasProductSellRequest $request_id
     * @return App\TejasProductSellRequest $trstp
     * return single object of TejasProductSellRequest related to request_id 
     * 
     */
    public function getSingleSellRequest($request_id){
        $tpsr = TejasProductSellRequest::find($request_id);
        return response()->json($tpsr->load('sellRequestProducts.product.units','sellRequestComments','dm'),200);
    }
}
