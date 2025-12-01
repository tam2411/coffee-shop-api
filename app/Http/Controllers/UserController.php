<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
class UserController extends Controller
{
    public function index()
    {
        $users = User::select([
            'id',
            'full_name',
            'email',
            'email_verified_at',
            'role',
            'is_active',
            'failed_attempts',
            'locked_until',
            'last_login_at',
            'created_at',
            'updated_at',
            'voucher_id'
        ])
        ->orderBy('id', 'desc')
        ->get();
        return response()->json([
            'ok' => true,
            'data' => $users
        ]);
    }
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'ok' => false,
                'msg' => 'User not found'
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'data' => $user
        ]);
    }
    public function update(Request $req, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'ok' => false,
                'msg' => 'User not found'
            ], 404);
        }

        if ($req->email) {
            $exists = User::where('email', $req->email)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Email already exists'
                ], 400);
            }
        }

        $validRoles = ['CUSTOMER','ADMIN','CONSULTING','WAREHOUSE','CASHIER'];

        if ($req->role && !in_array($req->role, $validRoles)) {
            return response()->json([
                'ok' => false,
                'msg' => 'Invalid role'
            ], 400);
        }

        if ($req->full_name){$user->full_name = $req->full_name;}
            
        if ($req->email){$user->email= $req->email;}
        if ($req->role)      $user->role      = $req->role;

        if (!is_null($req->is_active)) {
            $user->is_active = $req->is_active ? 1 : 0;
        }

        $user->save();

        return response()->json([
            'ok' => true,
            'msg' => 'User updated successfully',
            'data' => $user
        ]);
    }
    public function getByRole($role)
    {
        $validRoles = ['CUSTOMER','ADMIN','CONSULTING','WAREHOUSE','CASHIER'];

        if (!in_array($role, $validRoles)) {
            return response()->json([
                'ok' => false,
                'msg' => 'Invalid role'
            ], 400);
        }

        $users = User::where('role', $role)->get();

        return response()->json([
            'ok' => true,
            'data' => $users
        ]);
    }
}