<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Lấy lịch sử tin nhắn giữa 2 người
    public function getMessages($userId)
    {
        $authId = Auth::id();

        $messages = Message::where(function ($q) use ($authId, $userId) {
                $q->where('sender_id', $authId)->where('receiver_id', $userId);
            })
            ->orWhere(function ($q) use ($authId, $userId) {
                $q->where('sender_id', $userId)->where('receiver_id', $authId);
            })
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'ok' => true,
            'data' => $messages
        ]);
    }


    // Gửi tin nhắn
    public function send(Request $request)
    {

        $senderId = Auth::id();

        // Lưu vào database
        $message = Message::create([
        'sender_id' => $senderId,
        'receiver_id' => $request->receiver_id,
        'content' => $request->input('content'),
        'message_type' => $request->input('message_type'),
        ]);


        // Bắn event (real-time)
        broadcast(new MessageSent($message, $request->receiver_id))->toOthers();

        return response()->json([
            'ok' => true,
            'data' => $message
        ]);
    }
}
