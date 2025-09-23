<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class AdminPasswordController extends Controller
{
    // List all users for password management
    public function index()
    {
        $users = DB::table('PersonInformation')
            ->leftJoin('PersonSystemPassword', 'PersonInformation.PersonID', '=', 'PersonSystemPassword.PersonID')
            ->select('PersonInformation.*', 'PersonSystemPassword.Password')
            ->get();
        return view('admin.passwords-index', compact('users'));
    }

    // Show edit form for a user's password
    public function edit($id)
    {
        $user = DB::table('PersonInformation')->where('PersonID', $id)->first();
        return view('admin.passwords-edit', compact('user'));
    }

    // Update a user's password
    public function update(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|min:6',
        ]);
        DB::table('PersonSystemPassword')
            ->where('PersonID', $id)
            ->update(['Password' => Hash::make($request->input('password'))]);
        return Redirect::route('admin.passwords')->with('success', 'Password updated successfully.');
    }
}