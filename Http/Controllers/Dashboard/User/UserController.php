<?php

namespace App\Http\Controllers\Dashboard\User;

use App\Models\Division;
use App\Models\Media;

use App\Models\User;

use App\Models\Countries;
use App\Models\Specialty;
use Illuminate\Http\Request;

use App\Http\Requests\UserRequest;
use App\Http\Controllers\Controller;


class UserController extends Controller
{

//    public function __construct()
//    {
//        $this->middleware('permission:user-list' . session('appKey'), ['only' => ['index']]);
//        $this->middleware('permission:user-create' . session('appKey'), ['only' => ['create', 'store']]);
//        $this->middleware('permission:user-edit' . session('appKey'), ['only' => ['edit', 'update']]);
//        $this->middleware('permission:user-delete' . session('appKey'), ['only' => ['destroy']]);
//    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::whereNotNull('full_name')->orderBy('id','DESC')->get();

        return view('admin.User.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create()
    {
        return view('admin.User.create', compact('countries', 'divisions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function store(UserRequest $request)
    {
        $data = $request->validated();

        $data += [
            'appKey' => session('appKey'),
            'birth' => $request->birth,
            'nickName' => $request->nickName,
            'type' => $request->type,
            'email' => $request->email,
            'userName' => $request->userName,
            'cashBack' => $request->cashBack,
            'title' => $request->title,
            'division_id' => $request->division_id,
            'about' => $request->about
        ];

        $user = User::create($data);

        if($request->hasFile('image')){
                $media = new Media();
                $media->filename = $request->file('image')->getClientOriginalName();
                $media->filetype = $request->file('image')->getClientMimeType();
                $media->type = 'image';
                $media->createBy_type = get_class($user);
                $media->createBy_id = $user->id;
                $media->updateBy_type = null;
                $media->updateBy_id = null;

                $request->file('image')->move('JobClinic\images', $media->filename);
                $user->medias()->save($media);
        }

        return redirect()->route('users.index')
            ->with('success', __('words.client_created'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::where('id', $id)->first();

        return view('admin.User.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::where("id", $id)->first();
        return view('admin.User.update', compact('user' ));
    }


    public function update(UserRequest $request, $id)
    {
        $data = $request->validated();

        $user = User::with('medias')->where("id", $id)->where('appKey', session('appKey'))->first();

        $data += [
            'birth' => $request->birth,
            'nickName' => $request->nickName,
            'type' => $request->type,
            'email' => $request->email,
            'userName' => $request->userName,
            'cashBack' => $request->cashBack,
            'title' => $request->title,
            'about' => $request->about,
            'division_id' => $request->division_id
        ];

        $user->update($data);

        if ($request->hasFile('image')) {
            $media = $user->medias;
            $media->filename = $request->file('image')->getClientOriginalName();
            $media->filetype = $request->file('image')->getClientMimeType();
            $media->type = 'image';
            $media->createBy_type = get_class($user);
            $media->createBy_id = $user->id;
            $media->updateBy_type = null;
            $media->updateBy_id = null;

            $request->file('image')->move('JobClinic\images', $media->filename);

            $user->medias()->save($media);
        }


        return redirect()->route('users.index')
            ->with('success', __('words.client_updated'));
    }


    public function destroy($id)
    {
        User::where("id", $id)->where('appKey', session('appKey'))->delete();

        return redirect()->route('users.index')
            ->with('success', __('words.client_deleted'));
    }

    public function activation($id)
    {
        $user = User::where('id', $id)->where('appKey', session('appKey'))->first();
        $message = __('words.client_active');

        if ($user->is_active) {
            $user->update(['is_active' => 0]);
            $message = __('words.client_not_active');
        } else
            $user->update(['is_active' => 1]);

        return redirect()->route('users.index')
            ->with('success', $message);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);

        $user->is_approved = $request->input('is_approved');
        $user->under_review = $request->input('is_approved');
        $user->save();

        return redirect()->back()->with('success', 'User status updated successfully.');
    }

    public function deleteUser($id)
    {
        $user = User::find($id);
        $user->medias()->delete();

        return redirect()->back()->with('success', 'Media records deleted successfully.');
    }

    public function createSchedule($doctor){
        $users = User::where('appKey', session('appKey'))->get();

        return view('admin.Schedule.create', compact('users', 'doctor'));
    }


    // public function activation($id)
    // {
    //     $driver = User::findOrFail($id);
    //     if ($driver->is_active) {
    //         $driver->is_active = 0;
    //     } else {
    //         $driver->is_active = 1;
    //     }
    //     $driver->save();
    //     return response()->json(['success' => __('words.updated')]);
    // }
}
