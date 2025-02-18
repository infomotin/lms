<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    //AdminDashboard
    public function AdminDashboard(){
        return view('admin.index');
    }
    //Adminlogout
    public function Adminlogout(Request $request):RedirectResponse{
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'You have been successfully logged out!');
    }
    //AdminLogin
    public function AdminLogin(){
        return view('admin.login');
    }
    //AdminProfile  
    public function AdminProfile(){
        $authUser = Auth::user();
        $userData = User::find($authUser->id);

        return view('admin.profile', compact('userData'));
    }
    //UserProfileStore
    public function UserProfileStore(Request $request){
        $authUser = Auth::user();
        $userData = User::find($authUser->id);
        $userData->name = $request->name;
        $userData->email = $request->email;
        $userData->address = $request->address;
        $userData->phone = $request->phone;
        //image upload
        if($request->file('avatar')){
            $file = $request->file('avatar');
            @unlink(public_path('uploads/admin_images/'.$userData->avatar));
            $filename = date('YmdHi').$file->getClientOriginalName();
            $file->move(public_path('uploads/admin_images'), $filename);
            $userData->avatar = $filename;
        }
        $userData->save();
        $notification = array(
            'message' => 'Profile Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);  
    }
    //AdminChangePassword
    public function AdminChangePassword(){
        return view('admin.change_password');
    }
    //UpdatePassword
    public function UpdatePassword(Request $request){
        $validateData = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required',
        ]);
        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return back()->with("error","Your current password does not matches with the password you provided. Please try again.");
        }
        User::whereId(auth()->user()->id)->update([
            'password' => Hash::make($request->new_password)
        ]);
        $notification = array(
            'message' => 'Password Updated Successfully',
            'alert-type' => 'success'
        );
        Auth::logout();
        return back()->with($notification);
    }

    //InstructorIndex
    public function InstructorIndex(){
        $instructors = User::where('role', 'instructor')->get();
        return view('admin.backend.instructor.index', compact('instructors'));
    }
    //InstructorStatusUpdate
    public function InstructorStatusUpdate(Request $request){
        $user = User::findOrFail($request->user_id);
        $user->status = $request->status;
        $user->save();
        return response()->json(['message'=>'Status change successfully.']);
    }
    //AuthorIndex
    public function AuthorIndex(){
        $authors = User::where('role', 'author')->get();
        return view('admin.backend.author.index', compact('authors'));
    }
    //HeadInstructorIndex
    public function HeadInstructorIndex(){
        $instructors = User::where('role', 'head_instructor')->get();
        return view('admin.backend.head_instructor.index', compact('instructors'));
    }
    //LecturerIndex
    public function LecturerIndex(){
        $lecturers = User::where('role', 'lecturer')->get();
        return view('admin.backend.lecturer.index', compact('lecturers'));
    }
    //StudentIndex
    public function StudentIndex(){
        $students = User::where('role', 'student')->get();
        return view('admin.backend.student.index', compact('students'));
    }
}
