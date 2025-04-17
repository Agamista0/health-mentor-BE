<?php

namespace App\Http\Controllers\Dashboard\Notification;

use App\helpers\Notifications\AddNotificationService;
use App\Http\Controllers\Controller;
use App\helpers\Notifications;
use App\helpers\Notifications\notificationServices;
use App\Models\Notification;
use App\Models\Notify;
use App\Models\SubscriptionUser;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function App\helpers\groupUsers;

class NotificationController extends Controller
{
//    public function __construct()
//    {
//        $this->middleware('permission:notification-list'.session('appKey'), ['only' => ['index']]);
//        $this->middleware('permission:notification-create'.session('appKey'), ['only' => ['create','store']]);
//        $this->middleware('permission:notification-delete'.session('appKey'), ['only' => ['destroy']]);
//    }

    public function index()
    {
        $notifications = Notification::orderBy('id','DESC')->get();
        return view('.admin.notification.index', compact('notifications'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.notification.create');
    }



    PUBLIC function groupUsers($user_id){
        $user_group = UserGroup::findOrFail($user_id);
        $group = User::withCount('Order')->get();
        $users =(object)[];
        if(!empty($group)){
            foreach($group as $user):
                if(($user->order_count > $user_group->order_num || $user_group->order_num==null) &&
                    ($user->created_at < $user_group->created_before||$user_group->created_before==null) ){
                    $users = collect();
                    $users->push($user);
                }
            endforeach;
        }
        return $users;
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
    ]);

    $title = $validatedData['title'];
    $content = $validatedData['content'];
        $users = []; // تعريف المتغير $users بقيمة فارغة
        if ($request->user == 1) {
            $users = User::where('type', 'user')->whereNotNull('full_name')->get();
        } elseif ($request->user == 2) {
            $users = User::where('type', 'user')->where('id', $request->user_id)->get();
        } elseif ($request->user == 3) {
            $users = $this->groupUsers($request->user_id);
        } elseif ($request->user == 4) {
            $user_id = $request->input('user_id');
            $subscriptionUser = SubscriptionUser::where('user_id',$user_id)->first();
            $users = User::where('type', 'user')->where('id', $subscriptionUser->user_id)->get();
        } elseif ($request->user == 5) {
            $user_id = $request->input('user_id');
            $users = User::where('type', 'user')->where('id', $user_id)->get();
        }elseif ($request->user == 6){
            $subscriptionUsers = SubscriptionUser::with('user')->get();

            foreach ($subscriptionUsers as $subscriptionUser) {
                $users[] = $subscriptionUser->user; // Add each user to the $users array
            }
        }elseif ($request->user == 7){
            $users =  User::leftJoin('subscription_users as su', 'users.id', '=', 'su.user_id')
                ->whereNull('su.user_id')
                ->select('users.id')
                ->get();
        } else {
            $ageRanges = [
                ['start' => 18, 'end' => 35],
                ['start' => 36, 'end' => 50],
                ['start' => 51, 'end' => 50],
                // يمكنك إضافة المزيد من النطاقات حسب الحاجة
            ];

            foreach ($ageRanges as $range) {
                $usersInRange = User::whereBetween('age', [$range['start'], $range['end']])->get();
                $users = array_merge($users, $usersInRange->toArray());
            }
        }

        $notify = new AddNotificationService();
        $notify->notificationStore($users, $request->title, $request->content);

        return redirect()->route('notification.index')->with('success', __('words.created'));
    }


    public function show($id)
    {
        $seen = Notify::where('notification_id', $id)->where('is_seen', 1)->count();
        $not_seen = Notify::where('notification_id', $id)->where('is_seen', 0)->count();
        $notifications = Notify::where('notification_id', $id)->paginate(10);

        // dd($notifications);

        return view('admin.notification.show', compact('notifications', 'seen', 'not_seen'));
    }


    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        $notify = Notification::where('id', $id)->first();
        $notify->delete();
        return redirect()->route('notification.index')->with('success', __('words.notification_deleted'));
    }

    public function notificationToUsers($type)
    {
        $applied_id['data'] = 0;
        if ($type == 2) {
            $applied_id['data'] = User::orderby("full_name", "asc")
                ->select('id', 'full_name')
                ->whereNotNull('full_name')
                ->where('type', 'user')
                ->get();
        }
         elseif ($type == 3) {
                    $applied_id['data'] = User::orderby("age", "asc")
                        ->select('id', 'age')
                        ->get();
                }
//          elseif($type == 4){
//            $applied_id['data'] = UserGroup::orderby("full_name", "asc")
//                ->get();
//         }
        elseif ($type == 4){
            $applied_id['data']= DB::table('subscription_users')
                ->join('users', 'subscription_users.user_id', '=', 'users.id')
                ->select('users.*')
                ->get();

        }  elseif ($type == 5){
            $applied_id['data'] = User::leftJoin('subscription_users as su', 'users.id', '=', 'su.user_id')
                ->whereNull('su.user_id')
                ->select('users.*')
                ->get();
        }

        return response()->json($applied_id);
    }
}
