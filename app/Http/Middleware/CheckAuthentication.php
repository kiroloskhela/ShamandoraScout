<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Import the DB facade
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use App\Http\Middleware\Request;

class CheckAuthentication
{
    public function handle($request, Closure $next, $role)
    {
        if (! auth()->check()) {
            return redirect()->route('login-auth');
        }

        $rolesArraySentInRequest = explode('|', (string) $role);
        $superAdminOnly = $rolesArraySentInRequest === ['SuperAdmin'];

        // When the permission matrix is on, skip RoleName checks except SuperAdmin-only
        // routes — those must stay SuperAdmin even if someone is granted other keys.
        if (config('permissions.enforce') && ! $superAdminOnly) {
            return $next($request);
        }
        $userRole = auth()->user()->role;
        //dd($userRole);

        $flagUnAuthorized = true;
        // Check if the user has the required role
        foreach($rolesArraySentInRequest as $roleSent){
            if ($userRole->contains('RoleName', $roleSent)) {
                $flagUnAuthorized = false;
            }
        }

        //dd($flagUnAuthorized);

        if($flagUnAuthorized)
            return response()->view('unauthorized', [], 403);
    
        return $next($request);
    }

    /*
    public function handle($request, Closure $next)
    {
        
        
        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers' => 'Access-Control-Allow-Headers, Origin, Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Authorization, Access-Control-Request-Headers',
        ];

        
        if ($request->getMethod() === 'OPTIONS') {
            // The client-side application can set only headers allowed in Access-Control-Allow-Headers
            return new Response('OK', 200, $headers);
        }
        
        $response = $next($request);

        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value); // Use Symfony's set() method
        }

        //return $response;
        
        //return $request;
        if (Auth::check()) {
            return "Logged In";
            $person = DB::select("SELECT DISTINCT  pi.PersonID,
                                                    r.RoleName,
                                                    psp.Password
                                                FROM PersonInformation pi
                                                LEFT JOIN PersonSystemPassword psp ON pi.PersonID = psp.PersonID
                                                LEFT JOIN PersonRole pr ON pi.PersonID = pr.PersonID
                                                LEFT JOIN Roles r ON r.RoleID = pr.RoleID
                                                WHERE pi.PersonID = ?;", [$request->person_id]);
            return $person;
            $user = DB::table('Person') // Use the 'users' table
                ->where('email', $request->input('email')) // Replace with your input field (email/username)
                ->first();

            if ($person && $request->person_id == $person->Password) {
                // Password is correct
                // Now check the user's role(s)
                if ($this->userHasRole($person, 'SuperAdmin')) {
                    return $next($request); // User has the required role, proceed
                } else {
                    // Handle unauthorized access (e.g., show 403 page)
                    return "403, UnAuthorized";
                    abort(403, 'Unauthorized');
                }
            } else {
                // Authentication failed
                // Handle invalid credentials (e.g., show error message)
                return "Invalid Credentials";
            }
        }

        // User is not authenticated, redirect to the login page
        return redirect()->route('login-auth');
    }
    */

    private function userHasRole($person, $roles)
    {
        return in_array($person->role, $roles);
    }
}
