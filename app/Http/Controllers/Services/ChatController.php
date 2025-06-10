<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ChatService;
use App\Models\User;
use App\Models\Order;
use Kreait\Firebase\Contract\Database;

class ChatController extends Controller
{
    protected $chatService;
    protected $database;

    public function __construct(ChatService $chatService, Database $database)
    {
        $this->chatService = $chatService;
        $this->database = $database;
    }

    public function sendMessage(Request $request)
    {
        try {
            $validated = $request->validate([
                'receiver_id' => 'required|string',
                'message' => 'required_without:image|string|nullable',
                'image' => 'nullable|image|max:2048', // 2MB Max
                'order_id' => 'required|string'
            ]);

            // Handle image upload if present
            $imageUrl = null;
            if ($request->hasFile('image')) {
                $imageUrl = $this->chatService->uploadImage($request->file('image'));
            }

            // Create message data
            $messageData = [
                'sender_id' => auth()->id(),
                'receiver_id' => $validated['receiver_id'],
                'message' => $validated['message'] ?? '',
                'image_url' => $imageUrl,
                'order_id' => $validated['order_id'],
                'timestamp' => now()->timestamp,
                'read' => false
            ];

            // Save to Firebase
            $messageId = $this->chatService->saveMessage($messageData);

            // Send FCM notification
            $this->chatService->sendNotification($validated['receiver_id'], [
                'title' => 'New Message',
                'body' => $validated['message'] ?? 'Sent you an image',
                'type' => 'chat',
                'order_id' => $validated['order_id']
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Message sent successfully',
                'data' => [
                    'message_id' => $messageId,
                    'timestamp' => $messageData['timestamp']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function getMessages(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|string',
                'order_id' => 'required|string',
                'last_timestamp' => 'nullable|integer'
            ]);

            // Get messages from Firebase
            $messages = $this->chatService->getMessages(
                $validated['user_id'], 
                $validated['order_id'],
                $validated['last_timestamp'] ?? null
            );

            // Mark messages as read
            if (!empty($messages)) {
                $this->chatService->markAsRead(
                    $validated['user_id'],
                    auth()->id(),
                    $validated['order_id']
                );
            }

            return response()->json([
                'status' => 'success',
                'data' => $messages
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|string',
                'order_id' => 'required|string'
            ]);

            $this->chatService->markAsRead(
                $validated['user_id'],
                auth()->id(),
                $validated['order_id']
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Messages marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function updateDeviceToken(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'fcm_token' => 'required|string',
        ]);

        $request->user()->update(['fcm_token' => $request->fcm_token]);

        return response()->json(['message' => 'Device token updated successfully']);
    }

    public function sendFcmNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $user = \App\Models\User::find($request->user_id);
        $fcm = $user->fcm_token;

        if (!$fcm) {
            return response()->json(['message' => 'User does not have a device token'], 400);
        }

        $title = $request->title;
        $description = $request->body;
        $projectId = config('services.fcm.project_id'); # INSERT COPIED PROJECT ID

        $credentialsFilePath = Storage::path('app/json/file.json');
        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();

        $access_token = $token['access_token'];

        $headers = [
            "Authorization: Bearer $access_token",
            'Content-Type: application/json'
        ];

        $data = [
            "message" => [
                "token" => $fcm,
                "notification" => [
                    "title" => $title,
                    "body" => $description,
                ],
            ]
        ];
        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose output for debugging
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return response()->json([
                'message' => 'Curl Error: ' . $err
            ], 500);
        } else {
            return response()->json([
                'message' => 'Notification has been sent',
                'response' => json_decode($response, true)
            ]);
        }
    }
}