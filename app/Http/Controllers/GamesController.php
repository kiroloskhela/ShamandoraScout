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
    $this->authorize('viewAny', \App\Models\Game::class);
    $games = DB::table('Games')->get();
    return view('games.index', compact('games'));
}

    public function create()
    {
        $this->authorize('create', \App\Models\Game::class);
        return view("games.create");
    }

    public function insert(Request $request)
    {
        $this->authorize('create', \App\Models\Game::class);
        // GameID is AUTO_INCREMENT — never compute MAX+1 by hand.
        DB::table('Games')->insert([
            'Title' => $request->title,
            'GameDescription' => $request->description,
            'Rules' => $request->rules,
            'PointSystem' => $request->point_system,
            'AgeGroup' => $request->age_group,
            'Target' => $request->target,
            'RequireCustody' => $request->require_custody,
            'ReferenceLink' => $request->reference_link,
        ]);

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
        $this->authorize('viewAny', \App\Models\Game::class);
        $game = DB::table('Games')->where('GameID', $id)->first();
        return view("games.show", array('game' => $game, 'title' => "تفاصيل اللعبة"));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $this->authorize('create', \App\Models\Game::class);
        $game = DB::table('Games')->where('GameID', $id)->first();
        return view("games.edit", array('game' => $game, 'title' => "تعديل لعبة"));
    }

    public function updates(Request $request, $id)
    {
        $this->authorize('create', \App\Models\Game::class);
        $game = DB::table('Games')->where('GameID', $id)->first();

        $affected = DB::table('Games')->where('GameID', $id)->update([
            'Title' => $request->title,
            'GameDescription' => $request->description,
            'Rules' => $request->rules,
            'PointSystem' => $request->point_system,
            'AgeGroup' => $request->age_group,
            'Target' => $request->target,
            'RequireCustody' => $request->require_custody,
            'ReferenceLink' => $request->reference_link,
           
        ]);

        return redirect()->route('games.index')->with('status', 'تم تعديل اللعبة بنجاح: ' . $request->title);
    }

   
    public function deletes($id)
    {
        $this->authorize('create', \App\Models\Game::class);
        $game = DB::table('Games')->where('GameID', $id)->first();
        return view("games.delete", array('game' => $game, 'title' => "حذف لعبة"));
    }

    public function destroy($id)
    {
        $this->authorize('create', \App\Models\Game::class);
        $deleted = DB::table('Games')->where('GameID', $id)->delete();

        return redirect()->route('games.index')->with('status', 'تم حذف اللعبة بنجاح');
    }
}