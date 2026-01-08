<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use \Illuminate\Http\Response;
use Session;

class CurriculaCategoryController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {
            $CurriculaCategory = DB::table('CurriculaCategory')->get();
            return view("CurriculaCategory.index", array('CurriculaCategory' => $CurriculaCategory));
        }

        public function create()
        {
            return view("CurriculaCategory.create");
        }

        public function insert(Request  $request)
        {
            $lastCurriculaCategoryID = DB::table('CurriculaCategory')->orderBy('CurriculaCategoryID','desc')->first();

            if($lastCurriculaCategoryID==Null)
                $thisCurriculaCategoryID = 1;
            else
                $thisCurriculaCategoryID = $lastCurriculaCategoryID->CurriculaCategoryID + 1;

            DB::table('CurriculaCategory')->insert(
                array(
                    'CurriculaCategoryID' => $thisCurriculaCategoryID,
                    'CurriculaCategoryName' => $request -> CurriculaCategoryName,
                )
            );
            return redirect()->route('CurriculaCategory.index');
        }
    
        /**
            * Display the specified resource.
            *
            * @param  int  $id
            * @return Response
            */
        public function show($id)
        {
            //
        }
    
        /**
            * Show the form for editing the specified resource.
            *
            * @param  int  $id
            * @return Response
            */
        public function edit($id)
        {
            $CurriculaCategory = DB::table('CurriculaCategory')->where('CurriculaCategoryID', $id)->first();
            return view("CurriculaCategory.edit", array('CurriculaCategory' => $CurriculaCategory));
        }
    
        public function updates(Request $request, $id)
        {
            $CurriculaCategory = DB::table('CurriculaCategory')->where('CurriculaCategoryID', $id)->first();

            $affected = DB::table('CurriculaCategory')->where('CurriculaCategoryID', $id)->update(['CurriculaCategoryName' => $request->CurriculaCategoryName]);

            return redirect()->route('CurriculaCategory.index');
        }
    
        public function deletes($id)
        {
            $CurriculaCategory = DB::table('CurriculaCategory')->where('CurriculaCategoryID', $id)->first();
            return view("CurriculaCategory.delete", array('CurriculaCategory' => $CurriculaCategory));
        }

        public function destroy($id)
        {
            $deleted = DB::table('CurriculaCategory')->where('CurriculaCategoryID',$id)->delete();
            
            return redirect()->route('CurriculaCategory.index');
        }
}