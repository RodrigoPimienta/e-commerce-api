<?php
namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class authController extends Controller
{
    public function register(Request $request)
    {
        $request = (object) $request->validate([
            "email"        => "required|email|unique:companies",
            'name'         => 'required|max:255',
            'last_name'    => 'nullable|max:255',
            'address'      => 'required|max:255',
            'company_type' => 'required|integer|in:1,2',
        ]);

        DB::beginTransaction();

        // create company
        $company = Company::create([
            'email'        => $request->email,
            'name'         => $request->name,
            'last_name'    => $request->last_name,
            'address'      => $request->address,
            'company_type' => $request->company_type,
            'created_at'   => now(),
        ]);

        if (! $company) {
            DB::rollBack();
            return Controller::response(400, true, $message = 'Error creating company');
        }

        // create user
        // generate random password
        $randomPassword = Controller::randomPassword();

        $user = User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => bcrypt($randomPassword),
            'created_at' => now(),
        ]);

        if (! $user) {
            DB::rollBack();
            return Controller::response(400, true, $message = 'Error creating user');
        }

        // create company user

        $Company_user = CompanyUser::create([
            'id_company' => $company->id_company,
            'id_user'    => $user->id,
            'created_at' => now(),
        ]);

        if (! $Company_user) {
            DB::rollBack();
            return Controller::response(400, true, $message = 'Error creating company user');
        }

        $user->load('companies');

        $dataReturn = [
            'username' => $request->email,
            'password' => $randomPassword,
            'company'  => $company,
        ];

        DB::commit();
        return Controller::response(201, true, $message = 'Company registered successfully', $dataReturn);
    }

    public function login(Request $request)
    {

        $request = (object) $request->validate([
            "email"    => "required|email|exists:users",
            'password' => 'required|confirmed',
        ]);

        $user = User::where('email', $request->email)->first(['id', 'name', 'email', 'password', 'status']);

        if (! $user || Hash::check($request->password, $user->password) == false) {
            return Controller::response(401, true, $message = 'Provided credentials are incorrect.');
        }

        // check if the user have status 1
        if ($user->status == 0) {
            return Controller::response(401, true, $message = 'User is inactive, please contact the administrator.');
        }

        // delete all tokens
        $user->tokens()->delete();
        $expiration  = now()->addMinutes(config('sanctum.expiration'));
        $token       = $user->createToken($user->email, ['*'], $expiration);
        $user->token = $token->plainTextToken;
        unset($user->password);

        // load company user
       $user->load('companies');

        return Controller::response(200, false, $message = 'Login', $user);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return Controller::response(200, false, $message = 'Logout');

    }
}
