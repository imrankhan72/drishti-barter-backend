<?php

namespace App\Http\Controllers;

use Validator;
use App\TejasProductBuyRequest;
use App\TejasProductSellRequest;
use Illuminate\Http\Request;
use App\PersonProduct;
use App\BuyRequestProduct;
use Carbon\Carbon;
use App\Product;
use App\BuyRequestComment;
use App\DrishteeMitra;
use App\Ledger;
use App\LedgerTransaction;
use Auth;
use Mail;
use App\Mail\TejasBuyFromDrishteeMail;
use App\UserGeography;
use App\User;
use App\UserProduct;

class TejasProductBuyRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\TejasProductBuyRequest  $tejasProductBuyRequest
     * @return \Illuminate\Http\Response
     */
    public function show(TejasProductBuyRequest $tejasProductBuyRequest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TejasProductBuyRequest  $tejasProductBuyRequest
     * @return \Illuminate\Http\Response
     */
    public function edit(TejasProductBuyRequest $tejasProductBuyRequest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\TejasProductBuyRequest  $tejasProductBuyRequest
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TejasProductBuyRequest $tejasProductBuyRequest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\TejasProductBuyRequest  $tejasProductBuyRequest
     * @return \Illuminate\Http\Response
     */
    public function destroy(TejasProductBuyRequest $tejasProductBuyRequest)
    {
        //
    }

    /**
     * @param App\DrishteeMitra $dm_id
     * @param Illuminate\Http\Request $request
     * @return App\TejasProductBuyRequest $tpbr, App\BuyRequestProduct $str
     * do store TejasProductBuyRequest product request
     * 
     */
    public function requestStore($dm_id, Request $request)
    {
        // dd("csc");
        $regex = "/^(?=.+)(?:[1-9]\d*|0)?(?:\.\d+)?$/";

        $date =  Carbon::now();
        $validation = Validator::make($request->all(), [
            "data.*.product_id"   => "required|exists:products,id",
            "data.*.product_name" => "required|string",
            "data.*.quantity"     => array('required', 'regex:' . $regex),
            "data.*.unit"         => "required|string",
            "data.*.lp_applicable" => array('required', 'regex:' . $regex)
        ]);


        if ($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors, 400);
        }

        $temp['requester_id'] = $dm_id;
        $temp['request_date'] = $date;
        $temp['status'] = 'Open';
        $tpbr = TejasProductBuyRequest::create($temp);
        $resarray = [];
        foreach ($request->all() as $data) {
            $data['buy_request_id'] = $tpbr->id;
            $str = BuyRequestProduct::create($data);
            $str->product = $str->product;
            array_push($resarray, $str);
        }

        $dm = DrishteeMitra::find($dm_id);
        $dm_geography = $dm->dmGeography;
        $users =  User::whereHas('userGeographies', function ($query) use ( $dm_geography) {
            $query->where('geography_id', '=', $dm_geography->geography_id);
        })->where("is_super_admin",1)->get();

        foreach ($users as $user) {
                // Mail::to($user->email)->send(new TejasBuyFromDrishteeMail($user));
                sendSMS('You have received a new Tejas Product Buy Request. Please login to admin panel to process.', $user->mobile);
        }
        return response()->json(["res" => $resarray, "tejas" => $tpbr], 200);
    }

    /**
     * @param App\TejasProductBuyRequest $request_id
     * @param App\DrishteeMitra $requester_id
     * @return App\TejasProductBuyRequest $tpsr
     * do return single object of TejasProductBuyRequest with list of buyRequestProducts and buyRequestComments
     * 
     */
    public function getBuyRequest($requester_id, $request_id)
    {
        $tpsr = TejasProductBuyRequest::find($request_id);
        return response()->json($tpsr->load('buyRequestProducts.product.units', 'buyRequestComments'), 200);
    }

    /**
     * @param App\DrishteeMitra $dm_id
     * @return App\TejasProductBuyRequest $tpsr
     * do return single object of TejasProductBuyRequest with list of buyRequestProducts and buyRequestComments
     * 
     */
    public function getBuyRequestList($dm_id)
    {
        $tpsrs = TejasProductBuyRequest::where('requester_id', $dm_id)->get();
        return response()->json($tpsrs->load('buyRequestProducts.product.units', 'buyRequestComments'), 200);
    }

    /**
     * @param App\TejasProductBuyRequest $request_id
     * @param Illuminate\Http\Request $request
     * @return App\TejasProductBuyRequest $sellRequestComment
     * do change status of TejasProductBuyRequest, create transaction history, update ledger and return TejasProductBuyRequest object
     * 
     */
    public function updateStatusRequestAdmin(Request $request, $request_id)
    {
        $validation = Validator::make($request->all(), [
            "status"   => "required|string",
        ]);

        if ($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors, 400);
        }

        $buyRequest = TejasProductBuyRequest::find($request_id);

        if ($request['status'] == 'Accepted') {
            $user = Auth::User();
            if ($buyRequest->status == 'Accepted') {
                return response()->json(["error" => "Status Already Accepted"], 404);
            }

            $dm = DrishteeMitra::find($buyRequest->requester_id);
            $dm_person = $dm->person;
            $ledger =  Ledger::find($dm->ledger_id);
            $user_ledger = Ledger::find($user->ledger_id);
            $total_lp = 0;

            $buyRequestProducts = BuyRequestProduct::where('buy_request_id', $request_id)->get();
            

            foreach ($buyRequestProducts as $brp) {
                $up = UserProduct::where('user_id',$user->id)->where('product_id',$brp->product_id)->first();
                if($up == null) {
                 return response()->json(['error'=>'Product Not Available in the Inventory'],400);

                }
            }
            foreach ($buyRequestProducts as $key) {

                $up = UserProduct::where('user_id',$user->id)->where('product_id',$key->product_id)->first();
                  
                $up->quantity_available = $up->quantity_available - $key->quantity;
                $up->save();
                $pp = PersonProduct::where('person_id', $dm_person->id)->where('product_id', $key->product_id)->first();
                if ($pp == null) {
                    
                    $product = Product::find($key->product_id);
                    $lp = $product->geoDefaultLivehoodPoints($dm_person->geography_id);
                    $total_lp += (($lp) ? $lp : $product->default_livehood_points);
                    // $personp = PersonProduct::where('product_id', $key->product_id)->first();
                    $pp = new PersonProduct();
                    $pp->geography_id = $dm_person->geography_id;
                    $pp->geography_type = $dm_person->geography_type;
                    $pp->dm_id = $dm->id;
                    $pp->person_id = $dm_person->id;
                    $pp->product_id = $key->product_id;
                    $pp->unit_id = $product->unit_id;
                    $pp->quantity_available = $key->quantity;
                    $pp->product_lp = (($lp) ? $lp : $product->default_livehood_points);
                    $pp->active_on_barterplace = true;
                    $pp->save();
                } else {
                    $total_lp += $pp->product_lp;
                    $pp->quantity_available = $pp->quantity_available + $key->quantity;
                    $pp->save();
                }
                
            }
            $buyRequest->createTejasBuyLedgerTransactions('Success', $ledger->id, 'Dr', $total_lp, 'Buy Product From Drishtee', $ledger->balance - $total_lp);
            $buyRequest->createTejasBuyLedgerTransactions('Success', $user_ledger->id, 'Cr', $total_lp, 'Sell Product To Mitra', $user_ledger->balance + $total_lp);
            $ledger->balance = $ledger->balance - $total_lp;
            $ledger->save();
            $user_ledger->balance = $user_ledger->balance + $total_lp;
            $user_ledger->save();
        }
        $buyRequest->status = $request->status;
        if ($buyRequest->save()) {
            return response()->json($buyRequest->load('dm'), 200);
        }
        return response()->json(["error" => "Not Found."], 404);
    }

    /**
     * @param App\User $commentor_id
     * @param App\TejasProductBuyRequest $request_id
     * @param Illuminate\Http\Request $request
     * @return App\BuyRequestComment $buyRequestComment
     * do store comment by admin and return BuyRequestComment object
     * 
     */
    public function addCommentRequestAdmin(Request $request, $commentor_id, $request_id)
    {
        $date =  Carbon::now();
        $validation = Validator::make($request->all(), [
            "comment" => "required|string",
        ]);
        if ($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors, 400);
        }

        $buyRequest = TejasProductBuyRequest::find($request_id);

        $request['buy_request_id'] = $buyRequest->id;
        $request['commentor_id'] = $commentor_id;
        $request['date_time'] = $date;
        $buyRequestComment = BuyRequestComment::create($request->all());

        return response()->json($buyRequestComment, 200);
    }

    /**
     * @param App\DrishteeMitra $requester_id
     * @param App\TejasProductBuyRequest $request_id
     * @return App\TejasProductBuyRequest $buyRequest
     * do change status of TejasProductBuyRequest and return same object
     * 
     */
    public function cancelBuyRequest($requester_id, $request_id)
    {
        $collection = [];

        $buyRequest = TejasProductBuyRequest::find($request_id);
        $buyRequest->status = "Cancelled by DM";

        if ($buyRequest->save()) {
            return response()->json($buyRequest, 200);
        }

        return response()->json($collection, 200);
    }

    /**
     * @return App\TejasProductBuyRequest $buyRequests
     * return list of TejasProductBuyRequest object
     * 
     */
    public function getBuyRequestListAdmin()
    {
        $buyRequests = TejasProductBuyRequest::orderBy('request_date','DESC')->get();
        return response()->json($buyRequests->load('buyRequestProducts.product.units', 'dm.dmProfile', 'buyRequestComments'));
    }

    /**
     * @param Illuminate\Http\Request $request 
     * @param App\TejasProductBuyRequest $request_id
     * @param App\User $commentor_id
     * @return App\TejasProductBuyRequest $trstp
     * @return App\BuyRequestComment $buyRequestComment
     * do store BuyRequestComment and update TejasProductBuyRequest status
     * 
     */
    public function updateBuyRequestAdmin(Request $request, $commentor_id, $request_id)
    {
        $collection = [];
        $date =  Carbon::now();
        $validation = Validator::make($request->all(), [
            "status"   => "required|string",
            "comment" => "required|string",
        ]);
        if ($validation->fails()) {
            $errors = $validation->errors();
            return response()->json($errors, 400);
        }
        $buyRequest = TejasProductBuyRequest::find($request_id);
        $buyRequest->status = $request->status;
        $buyRequestComment = new BuyRequestComment();
        $buyRequestComment->buy_request_id = $buyRequest->id;
        $buyRequestComment->commentor_id = $commentor_id;
        $buyRequestComment->comment = $request->comment;
        $buyRequestComment->date_time = $date;

        if ($buyRequest->save() && $buyRequestComment->save()) {
            array_push($collection, ["buyRequest" => $buyRequest, "buyRequestComment" => $buyRequestComment]);
            return response()->json($collection, 200);
        }
        return response()->json($collection, 200);
    }

    /**
     * @param App\TejasProductBuyRequest $request_id
     * @return App\TejasProductBuyRequest $trstp
     * return single object of TejasProductBuyRequest related to request_id with list of buyRequestProducts, buyRequestComments 
     * 
     */
    public function getSingleBuyRequest($request_id)
    {
        // $tpbr = TejasProductBuyRequest::find();
        // $tpbr->destroy()
        // BuyRequestProduct::find()
        // BuyRequestComment::find()


        $tpbr = TejasProductBuyRequest::find($request_id);
        return response()->json($tpbr->load('buyRequestProducts.product.units', 'buyRequestComments', 'dm','ledgerTransactions'), 200);
    }
}
