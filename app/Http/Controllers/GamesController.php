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

class GamesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
 public function index()
{
    $games = DB::table('Games')->get();
    return view('games.index', compact('games'));
}

    public function create()
    {
        return view("games.create");
    }

    public function insert(Request $request)
    {
        $lastGameID = DB::table('Games')->orderBy('GameID', 'desc')->first();

        if ($lastGameID == Null)
            $thisGameID = 1;
        else
            $thisGameID = $lastGameID->GameID + 1;

        DB::table('Games')->insert(
            array(
                'GameID' => $thisGameID,
                'Title' => $request->title,
                'GameDescription' => $request->description,
                'Rules' => $request->rules,
                'PointSystem' => $request->point_system,
                'AgeGroup' => $request->age_group,
                'Target' => $request->target,
                'ReferenceLink' => $request->reference_link
            )
        );

        return redirect()->route('games.index')->with('status', 'تم ادخال اللعبة بنجاح: ' . $request->title);
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
        $game = DB::table('Games')->where('GameID', $id)->first();
        return view("games.edit", array('game' => $game, 'title' => "تعديل لعبة"));
    }

    public function updates(Request $request, $id)
    {
        $game = DB::table('Games')->where('GameID', $id)->first();

        $affected = DB::table('Games')->where('GameID', $id)->update([
            'Title' => $request->title,
            'GameDescription' => $request->description,
            'Rules' => $request->rules,
            'PointSystem' => $request->point_system,
            'AgeGroup' => $request->age_group,
            'Target' => $request->target,
            'ReferenceLink' => $request->reference_link
        ]);

        return redirect()->route('games.index')->with('status', 'تم تعديل اللعبة بنجاح: ' . $request->title);
    }

    public function deletes($id)
    {
        $game = DB::table('Games')->where('GameID', $id)->first();
        return view("games.delete", array('game' => $game, 'title' => "حذف لعبة"));
    }

    public function destroy($id)
    {
        $deleted = DB::table('Games')->where('GameID', $id)->delete();

        return redirect()->route('games.index')->with('status', 'تم حذف اللعبة بنجاح');
    }
}