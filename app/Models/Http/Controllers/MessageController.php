<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;
use App\ChatSession;
use App\UserMessages;
use App\Roles;
use Session;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class MessageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        deleteNotification();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
   
    public function index(Request $request)
    {
        $name = $request->search_name;
        $id = $request->id;
        $userId = Auth::user()->id;   
        $data = array();
        $chat = array();
        $count_mess = 0;
        $chat_session_id = '';
        if($name != "" && isset($name)){
            $data = User::where('status','enable')->where('first_name','LIKE','%'.$name.'%')->orWhere('last_name','LIKE','%'.$name.'%')->where('id','!=',$userId)->get();
            if(!empty($data)){
                foreach($data as $key => $value){
                    $count1 = ChatSession::where('from_id',$userId)->where('to_id',$value->id)->first();
                    $count2 = ChatSession::where('from_id',$value->id)->where('to_id',$userId)->first();

                    if(count($count1) != 0){
                        $chat_session_id = $count1->id;
                    }
                    if(count($count2) != 0){
                        $chat_session_id = $count2->id;
                    }
                
                    if($chat_session_id != ''){
                        $chat = UserMessages::where('chat_session_id',$chat_session_id)->orderBy('created_at','desc')->first();
                    }
                    if($chat->content != ''){
                        $data[$key]->last_message = $chat->content;
                    }else{
                        $data[$key]->last_message = '';
                    }
                    $data[$key]->m_count = '';
                }
            }
        }
        else if($id != "" && isset($id)){
            $data = User::where('status','enable')->where('id','!=',$userId)->where('id',$id)->get();

            $chat_unread = UserMessages::where('to_id',$userId)->where('from_id',$id)->update(['read' => 'read']);

            $count1 = ChatSession::where('from_id',$userId)->where('to_id',$id)->first();
            $count2 = ChatSession::where('from_id',$id)->where('to_id',$userId)->first();

            if(count($count1) != 0){
                $chat_session_id = $count1->id;
            }
            if(count($count2) != 0){
                $chat_session_id = $count2->id;
            }
            $username = User::where('status','enable')->where('id','!=',$userId)->where('id',$id)->first();
            if($chat_session_id != ''){
                $chat = UserMessages::where('chat_session_id',$chat_session_id)->get();
                
            }
            return ['data' => $data, 'status' => 'true', 'chat' => $chat, 'count_mess' => $count_mess,'username' => $username];
        }
        else{
            $data1 = ChatSession::select('to_id')->where('from_id',$userId)->get();
            $data2 = ChatSession::select('from_id')->where('to_id',$userId)->get();

            $ids = array_merge($data1->toArray(), $data2->toArray());

            $data = User::whereIn('id',$ids)->get();

            if(!empty($data)){
                $count1 = 0;
                $count2 = 0;
                foreach($data as $key => $value){
                    $count1 = UserMessages::where('read','unread')->where('to_id',$userId)->where('from_id',$value->id)->count();
                    if($count1 == 0 && $count1 == '' && $count1 == null){
                        $data[$key]->m_count = '';
                    }else{
                        $data[$key]->m_count = $count1;
                    }
                
                    $count1 = ChatSession::where('from_id',$userId)->where('to_id',$value->id)->first();
                    $count2 = ChatSession::where('from_id',$value->id)->where('to_id',$userId)->first();

                    if(count($count1) != 0){
                        $chat_session_id = $count1->id;
                    }
                    if(count($count2) != 0){
                        $chat_session_id = $count2->id;
                    }
                    if($chat_session_id != ''){
                        $chat = UserMessages::where('chat_session_id',$chat_session_id)->orderBy('created_at','desc')->first();
                    }
                    $username = User::where('status','enable')->where('id','!=',$userId)->where('id',$value->id)->first();
                    if(isset($chat) && $chat->content != '' && isset($username)){
                        $chat->first_name = $username->first_name;
                        $chat->last_name = $username->last_name;
                        $data[$key]->last_message = $chat->content;
                    }else{
                        $data[$key]->last_message = '';
                    }
                }
            }else{
                $data['m_count'] = '';
                $data['last_message'] = '';
            }
        }   
        return ['data' => $data, 'status' => 'true', 'chat' => $chat, 'count_mess' => $count_mess];
    }

    public function store(Request $request)
    {
        $message = $request->message;
        $to_id = $request->to_id;
        $from_id = Auth::user()->id;
        $time = date("h:i:s");

        $count1 = ChatSession::where('from_id',$from_id)->where('to_id',$to_id)->first();
        $count2 = ChatSession::where('from_id',$to_id)->where('to_id',$from_id)->first();

        if(count($count1) == 0 && count($count2) == 0){
            $chat_detail = new ChatSession();
            $chat_detail->from_id = $from_id;
            $chat_detail->to_id = $to_id;
            $chat_detail->save();
        }
        if(count($count1) != 0){
            $chat_session_id = $count1->id;
        }else if(count($count2) != 0){
            $chat_session_id = $count2->id;
        }else{
            $chat_session_id = $chat_detail->id; 
        }

        $chat = new UserMessages();
        $chat->chat_session_id = $chat_session_id;
        $chat->from_id = $from_id;
        $chat->to_id = $to_id;
        $chat->content = $message;
        $chat->time = $time;
        $chat->save();

        $update = UserMessages::where('from_id',$to_id)->where('to_id',$from_id)->update(['read'=>'read']);
        $data = UserMessages::where('chat_session_id',$chat_session_id)->get();
        $username = User::where('status','enable')->where('id','!=',$from_id)->where('id',$to_id)->first();

        return ['data' => $data, 'status' => 'true','username' => $username];
    }
    
    public function getMessage(Request $request)
    {
        $to_id = $request->to_id;
        $from_id = Auth::user()->id;
        $reason = $request->reason;
        $data_read = 0;
        $data = array();
        $chat_session_id = '';

        if($reason == "update_message"){
            $chat_unread = UserMessages::where('to_id',$from_id)->where('from_id',$to_id)->update(['read' => 'read']);
        }else{
            $count1 = ChatSession::where('from_id',$from_id)->where('to_id',$to_id)->first();
            $count2 = ChatSession::where('from_id',$to_id)->where('to_id',$from_id)->first();

            if(count($count1) != 0){
                $chat_session_id = $count1->id;
            } 
            if(count($count2) != 0){
                $chat_session_id = $count2->id;
            }
            $username = User::where('status','enable')->where('id','!=',$from_id)->where('id',$to_id)->first();
            if($chat_session_id != '' && $chat_session_id != null){
                $data = UserMessages::where('chat_session_id',$chat_session_id)->get();
                $data_read = UserMessages::where('to_id',$from_id)->where('chat_session_id',$chat_session_id)->where('read','unread')->count();

            }
            return ['data' => $data, 'status' => 'true', "data_read" => $data_read,'username' => $username];
        }

        return ['data' => $data, 'status' => 'true', "data_read" => $data_read];
    }

    public function getAllcount(Request $request)
    {
        $to_id = $request->to_id;
        $data = '';

        $data = UserMessages::where('to_id',$to_id)->where('from_id','!=',$to_id)->where('read','unread')->count();

        if($data == 0){
            $data = '';
        }

        return ['data' => $data, 'status' => 'true'];
    }

    public function get_broadcast_user(Request $request){
        $roles_name = Roles::where('status','enable')->get();
        $user = Auth::user()->id;
        if(!empty($roles_name)){
            foreach($roles_name as $key => $value){
                $user_detail = array();
                $user_detail = User::where('user_role', $value->id)->where('id','!=', $user)->get();
                $value->users = $user_detail;
            }
        }
        // pre($roles_name->toArray()); exit;
        return ['data' => $roles_name, 'status' => 'true'];
    }

    public function send_broadcast_message(Request $request){
        $message = $request->message;
        $user = Auth::user()->id;
        if(isset($request->all)){
            foreach($request->all as $key => $value){
                $tos = User::where('user_role', $key)->where('id','!=', $user)->get();
                foreach($tos as $k => $v){
                    $to_ids[] = $v->id;
                }
            }
        }else{
            foreach($request->user as $key => $value){
                $to_ids[] = $key;
            }
        }
        
        if(isset($to_ids) && !empty($to_ids)){
            foreach($to_ids as $to_id){
                $from_id = Auth::user()->id;
                $time = date("h:i:s");

                $count1 = ChatSession::where('from_id',$from_id)->where('to_id',$to_id)->first();
                $count2 = ChatSession::where('from_id',$to_id)->where('to_id',$from_id)->first();

                if(count($count1) == 0 && count($count2) == 0){
                    $chat_detail = new ChatSession();
                    $chat_detail->from_id = $from_id;
                    $chat_detail->to_id = $to_id;
                    $chat_detail->save();
                }
                if(count($count1) != 0){
                    $chat_session_id = $count1->id;
                }else if(count($count2) != 0){
                    $chat_session_id = $count2->id;
                }else{
                    $chat_session_id = $chat_detail->id; 
                }

                $chat = new UserMessages();
                $chat->chat_session_id = $chat_session_id;
                $chat->from_id = $from_id;
                $chat->to_id = $to_id;
                $chat->content = $message;
                $chat->time = $time;
                $chat->save();
            }
        }

        return ['status' => 'true'];
    }
}
