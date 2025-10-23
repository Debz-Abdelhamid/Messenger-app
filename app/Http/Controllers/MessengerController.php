<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Models\Favorite;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use App\Events\Message as MessageEvent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MessengerController extends Controller
{
    public function index(Request $request): View
    {
        $GetFavoriteList = $request->user()->favorites()->select('users.name','users.id','users.avatar')->get();

        return view('messenger.index', compact('GetFavoriteList'));
    }

    //search Users




    public function search(Request $request)
    {
        $getRecords = null;
        $search = $request['query'];
        if(!empty($search)){

            $records = User::whereNot('id', Auth::user()->id)
            ->where(function($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('user_name', 'LIKE', "%{$search}%");

            })->paginate(10);

            if($records->total() < 1)
            {
                $getRecords .= "<p class='text-center'>Nothing To Show.</p>";
            }

            foreach($records as $record)
            {
                $getRecords.= View('messenger.components.search-item', compact('record'))->render();
            }



            return response()->json([
                'records' => $getRecords,
                'last_page' => $records->lastPage(),
            ]);
        }
    }

    //Fetch User By Id
    public function fetchData(Request $request)
    {
        $request->validate([
            'id' => ['required','integer'],
        ]);

        $fetch = User::where('id',$request['id'])->select('name','avatar','user_name','id')->first();
        if(!$fetch)
        {
            return response()->json(['error' => 'User Not Found'], 404);
        }

        $isFavorite = $request->user()->favorites()->where('favorite_id', $fetch->id)->exists();


        return response()->json([
            'fetch' => $fetch,
            'isFavorite' => $isFavorite,
            // 'SharedPhotos' => $content,
        ]);
    }


    function FetchGallery(Request $request)
    {
        $request->validate([
            'id' => ['required','integer','exists:users,id'],
        ]);

        $sentMessages = $request->user()->sentMessages()
                                    ->where('to_id', $request->id)
                                    ->whereNotNull('attachment')
                                    ->select('attachment','created_at');

        $receivedMessages = $request->user()->receivedMessages()
                                    ->where('from_id', $request->id)
                                    ->whereNotNull('attachment')
                                    ->select('attachment','created_at');

        $SharedPhotos = $sentMessages->union($receivedMessages)
        ->latest()
        ->paginate(20);



        $content = '';
        foreach($SharedPhotos as $photo)
        {
            $content .= View('messenger.components.Gallery-item', compact('photo'))->render();
        }
        return response()->json([
            'SharedPhotos' => $content,
            'last_page' => $SharedPhotos->lastPage()
        ]);

    }


    public function sendMessage(Request $request)
    {

        $attachment = null;
        try {

            $request->validate([
                'message' => ['nullable'],
                'id' => ['required', 'integer'],
                'temporaryMsgId' => ['required'],
                'attachment' => ['nullable','image','mimes:png,jpg,jpeg', 'max:1028']
            ]);

            $user = User::findOrFail($request->id);


            if($request->hasFile('attachment'))
            {
                $attachment = $request->file('attachment')->store('Attachment','public');

            }

            if(empty($request->message) && empty($request->attachment))
            {
                return response()->json(['error' => 'Message Cant be empty!'],
                422);
            }



            $message = $request->user()->sentMessages()->create([
                'body' => $request->message,
                'to_id' => $request->id,
                'attachment' => $attachment ? json_encode($attachment) : null,
            ]);


            // BroadCast  Event
            MessageEvent::dispatch($message);


            return response()->json([
                'message' =>  $attachment ?  $this->MessageCard($message,true) : $this->MessageCard($message),
                'tempID' => $request->temporaryMsgId,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'error' => 'User not found.',
            ], 401);
        }
    }


    function MessageCard($message, $attach = false)
    {
        return View('messenger.components.message-card', compact('message', 'attach'))->render();
    }


    //Fetch Messages

    function FetchMessages(Request $request)
    {
        $user = User::findOrFail($request->id);
        if(!$user)
        {
            return response()->json(['error' => 'User not found.'], 401);
        }
        // $messages = Message::where(function($query) use ($request) {
        //     $query->where('from_id', Auth::user()->id)
        //           ->where('to_id', $request->id);
        // })->orWhere(function($query) use ($request) {
        //     $query->where('from_id', $request->id)
        //           ->where('to_id', Auth::user()->id);
        // })->get();

        $sentMessages = $request->user()->sentMessages()->where('to_id', $request->id);
        $receivedMessages = $request->user()->receivedMessages()->where('from_id', $request->id);

        $messages = $sentMessages->union($receivedMessages)->latest()->paginate(15);

        $response = [
            'last_page' => $messages->lastPage(),
            'messages' => ''
        ];

        $AllMessages='';
        if(count($messages) < 1)
        {
            $response['messages']= "
            <div class='d-flex justify-content-center align-items-center h-100'>
                <p class='no-messages'>Type 'Hi' And Start Messaging</p>
            </div>";

            return response()->json($response);
        }

        foreach ($messages->reverse() as $message)
        {
             $AllMessages .= $this->MessageCard($message, $message->attachment ? true : false);
        }

        $response['messages'] = $AllMessages;

        return response()->json($response);
    }


    //Fetch Contacts
    function FetchContacts(Request $request)
    {

        $users = Message::join("users", function($join){
            $join->on('messages.from_id', '=', 'users.id')
            ->orOn('messages.to_id', '=' , 'users.id');
        })
        ->where(function($query) use ($request){
            $query->where('messages.from_id', $request->user()->id)
            ->orWhere('messages.to_id', $request->user()->id);
        })
        ->where('users.id', '!=' , $request->user()->id)
        ->select('users.id', 'users.name', 'users.email','users.avatar', DB::raw('MAX(messages.created_at) as max_created_at'))
        ->groupBy('users.id', 'users.name', 'users.email','users.avatar')
        ->orderBy('max_created_at', 'desc')
        ->paginate(10);

        if(count($users) > 0 )
        {
            $contacts = '';
            foreach ($users as $user)
            {

                $contacts .= $this->getContactItem($user);

            }

        }else
        {
            $contacts = "<p class='text-center no-contact text-primary' >Your Contact List Is Empty</p>";
        }

        return response()->json([

            'contacts' => $contacts,
            'last_page' => $users->lastPage()

        ]);

    }

    function  getContactItem($user)
    {

        $LastMessage = Message::where('from_id', Auth::user()->id)
        ->where('to_id',$user->id)->orwhere('from_id', $user->id)->where('to_id', Auth::user()->id)->latest()->select('body','from_id')->first();

        $UnseenCounter = Message::where('from_id',  $user->id)->where('to_id',Auth::user()->id)->where('seen', 0)->count();

        return View('messenger.components.contact-list-item', compact(['LastMessage','UnseenCounter','user']))->render();
    }

    function UpdateContacts(Request $request)
    {
        //Get User Data
        $user = User::findOrFail($request->user_id);

        if(!$user)
        {
            return response()->json(['message' => 'User Not Found'], 401);
        }

        $contactItem = $this->getContactItem($user);

        return response()->json([
            'contactItem' => $contactItem,
        ],200);


    }



    function MakeMsgSeen(Request $request) {
        $request->validate([
            'id' => ['required','integer','exists:users,id']
        ]);

        $user = User::findOrFail($request->id);

        if(!$user)
        {
            return response()->json(['message' => 'User Not Found'], 401);
        }

         Message::where('from_id', $request->id)
        ->where('to_id', Auth::user()->id)
        ->where('seen', 0)->update(['seen' => 1]);

        return true;
    }



    function MakeFavoriteUser(Request $request)
    {
        $user = User::findOrFail($request->id);
        if(!$user)
        {
            return response()->json(['message' => 'User Not Found'], 401);
        }

        $favoriteStatus = $request->user()->favorites()->where('favorite_id', $user->id)->exists();


        if(!$favoriteStatus)
        {
            $request->user()->favorites()->attach($request->id);

            return response()->json([
                'status' => 'User-Added',


            ], 200);

        }else
        {
            $request->user()->favorites()->detach($request->id);
            return response()->json(['status' => 'User-Removed'], 200);

        }

    }





    function DestroyMessage(Request $request)
    {
        try {

            $message = Message::findOrFail($request->msg_id);


            $this->authorize('delete', $message);


            event(new MessageEvent($message, 'delete'));
            $message->delete();

            return response()->json([
                'message' => 'Message Deleted',
                'id' => $request->msg_id
            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'error' => 'Message not found'
            ], 404);
        } catch (AuthorizationException $e) {

            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        } catch (QueryException $e) {

            return response()->json([
                'error' => 'error'
            ], 500);
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'An unexpected error occurred'
            ], 500);
        }

        return;
    }







}
