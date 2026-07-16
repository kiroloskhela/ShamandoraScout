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

class BetakaTakaddomController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {
            $betakat = DB::table('EgazetBetakatTaqaddom')->get();
            return view("betakat-takaddom.betaka-index", array('betakat' => $betakat, 'title'=> "الرتب الكشفية"));
        }

        public function create()
        {
            return view("betakat-takaddom.betaka-create");
        }

        public function insert(Request  $request)
        {
            DB::table('EgazetBetakatTaqaddom')->insert(
                array(
                    'EgazetBetakatTaqaddomName' => $request -> betaka_name
                )
            );
            return redirect()->route('betaka.index')->with('status',' :تم ادخال بنجاح ' .$request->EgazetBetakatTaqaddomName);
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
            $betakat = DB::table('EgazetBetakatTaqaddom')->where('EgazetBetakatTaqaddomID', $id)->first();

            return view("betakat-takaddom.betaka-edit", array('betakat' => $betakat));
        }
    
        public function updates(Request $request, $id)
        {
            $betakat = DB::table('EgazetBetakatTaqaddom')->where('EgazetBetakatTaqaddomID', $id)->first();

            $affected = DB::table('EgazetBetakatTaqaddom')->where('EgazetBetakatTaqaddomID', $id)->update(['EgazetBetakatTaqaddomName' => $request->betaka_name]);

            return redirect()->route('betaka.index')->with('status',' :تم تعديل بنجاح' .$request->betaka_name);
        }
    
        public function deletes($id)
        {
            $betakat = DB::table('EgazetBetakatTaqaddom')->where('EgazetBetakatTaqaddomID', $id)->first();

            return view("betakat-takaddom.betaka-delete", array('betakat' => $betakat));
        }

        public function destroy($id)
        {
            $deleted = DB::table('EgazetBetakatTaqaddom')->where('EgazetBetakatTaqaddomID',$id)->delete();

            return redirect()->route('betaka.index')->with('status','تم الغاء الرتبة بنجاح');
        }
}