<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class GamesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $this->authorize('viewAny', Game::class);
        $games = DB::table('Games')->get();

        return view('games.index', compact('games'));
    }

    public function create()
    {
        $this->authorize('create', Game::class);

        return view('games.create');
    }

    public function insert(Request $request)
    {
        $this->authorize('create', Game::class);
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

        return redirect()->route('games.index')->with('status', __('Game added successfully: ').$request->title);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('viewAny', Game::class);
        $game = DB::table('Games')->where('GameID', $id)->first();

        return view('games.show', ['game' => $game, 'title' => __('Game details')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $this->authorize('update', new Game);
        $game = DB::table('Games')->where('GameID', $id)->first();

        return view('games.edit', ['game' => $game, 'title' => __('Edit game')]);
    }

    public function updates(Request $request, $id)
    {
        $this->authorize('update', new Game);
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

        return redirect()->route('games.index')->with('status', __('Game updated successfully: ').$request->title);
    }

    public function deletes($id)
    {
        $this->authorize('delete', new Game);
        $game = DB::table('Games')->where('GameID', $id)->first();

        return view('games.delete', ['game' => $game, 'title' => __('Delete game')]);
    }

    public function destroy($id)
    {
        $this->authorize('delete', new Game);
        $deleted = DB::table('Games')->where('GameID', $id)->delete();

        return redirect()->route('games.index')->with('status', __('Game deleted successfully'));
    }
}
