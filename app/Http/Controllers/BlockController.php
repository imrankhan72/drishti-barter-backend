<?php

namespace App\Http\Controllers;

use App\Block;
use Illuminate\Http\Request;
use App\Repositories\Repository\BlockRepository;
use App\Http\Requests\BlockRequest;

class BlockController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    private $repository;
    public function __construct(BlockRepository $repository)
    {
        $this->repository = $repository;
    }
    public function index()
    {
        return response()->json($this->repository->all(),200);
        
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
    public function store(BlockRequest $request)
    {
        return response()->json($this->repository->create($request->all()), 201);
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Village  $village
     * @return \Illuminate\Http\Response
     */
    public function show(Block $block)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Village  $village
     * @return \Illuminate\Http\Response
     */
    public function edit(Block $block)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Village  $village
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Block $block)
    {
        return response()->json($this->repository->update($request->all(), $block->id), 201);
    
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Block  $block
     * @return \Illuminate\Http\Response
     */
    public function destroy(Block $block)
    {
         $block = $this->repository->changeActiveStatus($block->id);
        return response()->json($block,200);
    }
    public function deleteBlock(Request $request,$id)
    {
        $block = Block::find($id);
        if($block) {
           // $bdis = $block->district;
           $bvill = $block->villages;
           if( count($bvill)>0) {
                return response()->json(['error'=>'you can not delete this block'],400);

           }
           else {
            $block->destroy($id);
            return response()->json(true,200);
           }
        }
        return response()->json(['error'=>'Block Not Found'],404);
    }
}
