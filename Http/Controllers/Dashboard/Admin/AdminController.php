<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Session;

use function App\helpers\appKey;

class AdminController extends Controller
{
    public function __construct()
    {
      $this->middleware('permission:admin-list', ['only' => ['index']]);
      $this->middleware('permission:admin-create', ['only' => ['create','store']]);
      $this->middleware('permission:admin-edit', ['only' => ['edit','update']]);
      $this->middleware('permission:admin-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // dd(session('appKey'));
        $admins = Admin::orderBy('id', 'DESC')->get();
        
        return view('admin.index', compact('admins'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::pluck('name','name')->all();
        return view('admin.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAdminRequest $request)
    {
        $data = $request->validated();
        $data['type'] = 'admin';
        $data['password']= Hash::make($request->input('password'));
       // dd($data);

        $admin = Admin::create($data);

        $admin->assignRole($request->input('roles'));
        return redirect()->route('admin.index')->with('success', __('words.admin_created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $admin = Admin::where('id',$id)->first();
        $roles = Role::pluck('name','name')->all();
        return view('admin.update', compact('admin', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreAdminRequest $request, $id)
    {
        $data = $request->validated();
        $data['password']= Hash::make('password');

        $admin = Admin::where('id',$id)->update($data);

        $admin = Admin::findOrFail($id);
        DB::table('model_has_roles')->where('model_id',$id)->delete();
        $admin->assignRole($request->input('roles'));

        return redirect()->route('admin.index')->with('success', __('words.admin_updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        Admin::where('id', $id)->delete();

        return redirect()->route('admin.index')
                        ->with('success', __('words.admin_deleted'));
    }
}
