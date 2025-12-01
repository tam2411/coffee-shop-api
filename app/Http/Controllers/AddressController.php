<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function index()
{
    $userId = Auth::id();

    $addresses = Address::where('user_id', $userId)
        ->orderByDesc('is_default')
        ->orderBy('id', 'asc')
        ->get();

    return response()->json([
        'ok' => true,
        'data' => $addresses,
    ]);
}


    public function add(Request $request)
    {
        $isDefault = Address::where('user_id', Auth::id())->exists();
        $address = Address::create([
            'user_id'=> Auth::id(),
            'label' => $request->label,
            'street' => $request->street,
            'receiver_name' => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'address_line' => $request->address_line,
            'ward' => $request->ward,
            'district' => $request->district,
            'province' => $request->province,
            'is_default' => $isDefault ? 0 : 1,
        ]);
        return response()->json([
            'ok' => true,
            'data' => $address,
        ]);
    }
    public function update(Request $request, $id)
    {
        $address = Address::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$address) {
            return response()->json(['ok' => false, 'msg' => 'Address not found'], 404);
        }

        $address->update([
            'user_id'=> Auth::id(),
            'label'=> $request->label,
            'street'=> $request->street,
            'receiver_name'=> $request->receiver_name,
            'receiver_phone'=> $request->receiver_phone,
            'address_line'=> $request->address_line,
            'ward'=> $request->ward,
            'district'=> $request->district,
            'province'=> $request->province,
        ]);

        return response()->json([
            'ok' => true,
            'data' => $address,
        ]);
    }
    public function delete($id)
    {
        $userId = Auth::id();

        $address = Address::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$address) {
            return response()->json([
                'ok' => false,
                'msg' => 'Address not found'
            ], 404);
        }

        $wasDefault = $address->is_default;
        $address->delete();
        if ($wasDefault) {
            $next = Address::where('user_id', $userId)
                ->orderBy('id', 'asc')
                ->first();

            if ($next) {
                $next->update(['is_default' => 1]);
            }
        }
        $addresses = Address::where('user_id', $userId)
        ->orderByDesc('is_default')
        ->orderBy('id', 'asc')
        ->get();

        return response()->json([
            'ok' => true,
            'msg' => 'Address deleted successfully',
            'data' => $addresses,
        ]);
    }

    public function setDefault($id)
    {
        $userID = Auth::id();
        $address = Address::where('id', $id)
            ->where('user_id', $userID)
            ->first();

        if (!$address) {
            return response()->json(['ok' => false, 'msg' => 'Address not found'], 404);
        }

        Address::where('user_id', $userID)->update(['is_default' => 0]);

        $address->is_default = 1;
        $address->save();

        return response()->json(['ok' => true]);
    }

    public function getDefault()
    {
        $userID = Auth::id();
        $address = Address::where('user_id', $userID)
            ->where('is_default', 1)
            ->first();

        if (!$address) {
            return response()->json(['ok' => false, 'msg' => 'Default address not found'], 404);
        }

        return response()->json([
            'ok' => true,
            'data' => $address,
        ]);
    }
}