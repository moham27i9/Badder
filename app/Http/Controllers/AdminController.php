<?php

namespace App\Http\Controllers;

use App\Models\Ads;
use App\Models\Article;
use App\Models\Course;
use App\Models\Donation_number;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\Help_request;
use App\Models\Notification;
use App\Models\statics;
use App\Models\Subscribe;
use App\Models\Subscribe_course;
use App\Models\Suggestion;
use App\Models\User;
use App\Models\Volunteer;
use App\Notifications\Add_Ads_Notify;
use App\Notifications\AddArticle_Notify;
use App\Notifications\AddCourse_Notify;
use App\Notifications\AddEvent_Notify;
use App\Notifications\AddGalleries_Notify;
use App\Notifications\ChangeRole_Notify;
use App\Notifications\Confirm_HelpR_Notify;
use App\Notifications\Confirm_Suggestions_Notify;
use App\Notifications\Confirm_volunteer_Notify;
use App\Notifications\UpdateArticle_Notify;
use App\Notifications\UpdateCourse_Notify;
use App\Notifications\updateEvent_Notify;
use Carbon\Carbon;
use DateTime;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    public function Add_statics(Request $request , $event_id)
    {
        $validation=$request->validate([
            'description'=>'required'
        ]);
         $eventDate= [];
        if(!$request->has('description')){
            return response()->json([
                'message'=>'failed please enter the description'
            ]);
        }

        $static_exist=Statics::where('id', $event_id)->first();
        if($static_exist){
            return response()->json([
                'message'=>'failed this static founded'
            ]);
        }

        $user_id=auth()->user()->id;


        $event=Event::where('id', $event_id)->first();
        $subscribe=Subscribe::where('event_id', $event_id)->get();

        $startDate = new DateTime($event->start_date);
        $endDate = new DateTime($event->end_date);
        $interval = $endDate->diff($startDate);


            $count_bene=Subscribe::where('event_id', $event_id)
                                  ->where('benefit', 1)
                                  ->count();
            $count_vol=Subscribe::where('event_id', $event_id)
                                  ->where('volunteering', 1)
                                 ->count();

                        $static=statics::create([
                        'user_id'=>$user_id,
                        'event_id'=>$event_id,
                        'description'=>$validation['description'],
                        'count_benefit'=>$count_bene,
                        'count_volunteer'=>$count_vol,
                    ]);

                    $eventDate[]=[
                        'user_id'=>$static->user_id,
                        'event_id'=>$static->event_id,
                        'description'=>$static->description,
                        'Count bene from'=>$count_bene,
                        'Count volunteer from'=>$count_vol,
                        'period'=>  $interval->y.' سنوات ' . $interval->m.' وأشهر ' . $interval->d.' أيام'
                    ];


        return response()->json([
            'Result'=>$eventDate,
        ]);
    }

    public function show_statics(){
        $statics=Statics::get();
        if($statics->count() == 0){
            return response()->json([
                'message'=>'filed not found statics',
            ]);
        }
        return response()->json([
            'message'=>'success',
            'Statics' => $statics,
        ]);

    }

    public function delete_statics($event_id){
        $static = Statics::first();
        if($static){
        $static->delete();
        return response()->json([
            'message'=>'success deleted',
        ]);
        }
        return response()->json([
            'message'=>'filed not found static ',
        ]);
    }

public function statics()
{
    $c=0;
    $count_events=Event::get();
    $eventDate= [];
    foreach($count_events as $event){

        $c++;
        $event_id=$event->id;
        $subscribe=Subscribe::where('event_id', $event_id)->get();
        foreach($subscribe as $sub_event){
        $count_bene=Subscribe::where($sub_event->benefit,1 )->get();
        $count_vol=Subscribe::where($sub_event->volunteer,1 )->get();

        $startDate = new DateTime($event->start_date);
        $endDate = new DateTime($event->end_date);

         $interval = $endDate->diff($startDate);
        $eventDate[]=[
            'Count All events'=>$c,
            'Count bene from'=>$count_bene->count(),
            'Count volunteer from'=>$count_vol->count(),
            'period'=>$interval->y.' سنة '.$interval->m.' وشهر '.$interval->d.' ويوم',

        ];

        }
    }
    return response()->json([
        'Result'=>$eventDate,
    ]);
}

public function add_Ads(Request $request)
{
    $validation = $request->validate([
        'title' => 'required',
        'description' => 'required',
        'image' => 'required',

    ]);

    $user_id=auth()->user()->id;
    $image = $request->file('image');
    if($request->hasFile('image')){

            $fileName = date('dmY').time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path("/pictures"), $fileName);
        }

                $ads = Ads::create([
                'user_id' => $user_id,
                'title' => $validation['title'],
                'description' => $validation['description'],
                'image' => $fileName,
                ]);

                foreach (User::all() as $user) {
                    if($user->id != $user_id){

                        $user->notify(new Add_Ads_Notify($ads));
                    }
                }
                $adsData = [
                    'id' => $ads->id,
                    'username' => $ads->user->first_name,
                    'title' => $ads->title,
                    'description' => $ads->description,
                    'image' => $ads->image ? url('pictures/' . $fileName) : null,
                ];

                return response()->json([
                    'message'=>'success',
                    'Ads' => $adsData,
                ]);

}


    public function Show_User_by_id($user_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['message' => 'failed', 'user does not exist']);
        }
        $eventData = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'phone' => $user->phone,
            'email' => $user->email,
            'image' => $user->image ? url('pictures/' . $user->image) : null,
        ];
        return response()->json([
            'message' => 'success',
            'user' => $eventData,
        ]);
    }
    public function changeRole(Request $request, $userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['message' =>'failed', 'User not found']);
        }
        $oldRole=$user->role;
        $request->validate([
        'role' => 'required',
       ]);
        $newRole=$request->role;
        $user->role = $newRole;
        $user->save();
        $user->notify(new ChangeRole_Notify($newRole , $oldRole));
        return response()->json(['message' => 'success','User role updated successfully']);
    }

    public function addEvent(Request $request)
    {
        $validation = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'required',
            'location' => 'required',
            'image' => 'required',
        ]);
        $image = $request->file('image');
        if($request->hasFile('image')){
                $fileName = date('dmY').time().'.'.$image->getClientOriginalExtension();
                $image->move(public_path("/pictures"), $fileName);
            }
            $existingEvent = Event::
              where('name', $validation['name'])
            ->where('type', $validation['type'])
            ->where('start_date', $validation['start_date'])
            ->where('end_date', $validation['end_date'])
            ->where('location', $validation['location'])
            ->first();
            if ( !$existingEvent) {
                    $events = Event::create([
                    'name' => $validation['name'],
                    'type' => $validation['type'],
                    'start_date' => $validation['start_date'],
                    'end_date' => $validation['end_date'],
                    'description' => $validation['description'],
                    'location' => $validation['location'],
                    'image' => $fileName,
                    ]);
                    $user_id=auth()->user()->id;
                    foreach (User::all() as $user) {
                        if($user->id != $user_id){

                            $user->notify(new AddEvent_Notify($events));
                        }
                    }
                    return response()->json([
                        'message'=>'success',
                        'Events' => $events,
                        'image' => $events->image ? url('pictures/' . $events->image) : null,
                    ]);
            }
        return response()->json([
            'message' => 'A similar event already exists!',
        ], 400);
    }

    public function deleteEvent($event_id)
    {
        $event = Event::find($event_id);
        if (!$event) {
            return response()->json(['message' => 'failed','this event does not exist']);
        }
        $event->delete();
        return response()->json(['message' =>'success', 'deleted successfully']);
    }

        public function updateEvent(Request $request, $event_id)
    {
        $eventDataImage =[];
        $event = Event::find($event_id);
        if (!$event) {
            return response()->json(['message' => 'failed','this event does not exist']);
        }
        if($request->all() == [])
        { return response()->json(['message' => 'failed', 'Please fill in at least one field']); }

        $image = $request->file('image');
        if($request->hasFile('image')){

                $fileName = date('dmY').time().'.'.$image->getClientOriginalExtension();
                $image->move(public_path("/pictures"), $fileName);


            }


            $event->update($request->only(['name', 'type', 'start_date', 'end_date','location','description']));


            if( $image){
                $event->update([
                    'image'=>$fileName
                ]);
                $eventDataImage = [
                'image' => $event->image ? url('pictures/' . $fileName) : null,
            ];
            }
            $eventData = [
                'id' => $event->id,
                'name' => $event->name,
                'type' => $event->type,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'location' => $event->location,
                'description' => $event->description,
            ];

        foreach (User::all() as $user) {
            if($user->role != 'superAdmin'){

                $user->notify(new updateEvent_Notify($event));
            }
        }

        return response()->json([
            'message' => 'success',
            'event'=>$eventData,
            'image'=>$eventDataImage,
      ]);
    }

    public function addArticle(Request $request)
    {
        $validation = $request->validate([
            'title' => 'required',
            'category' => 'required',
            'description' => 'required',
            'image' => 'required',
        ]);
        $image = $request->file('image');
        if ($request->hasFile('image')) {
            $fileName = date('dmY') . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path("/pictures"), $fileName);
        }
        $existingArticle = Article::
        where('title', $validation['title'])
      ->where('category', $validation['category'])
      ->where('description', $validation['description'])
      ->first();
      if ( !$existingArticle) {
        $article = Article::create([
            'title' => $validation['title'],
            'category' => $validation['category'],
            'description' => $validation['description'],
            'image' => $fileName,
        ]);
        $user_id=auth()->user()->id;
        foreach (User::all() as $user) {
            if($user->id != $user_id){
                $user->notify(new AddArticle_Notify($article));
            }
        }
        return response()->json([
            'message'=>'success',
            'Articles' => $article,
            'image' => $article->image ? url('pictures/' . $article->image) : null,
        ]);
        }
        return response()->json([
            'message' => 'A similar article already exists!',
        ], 400);

    }

    public function deleteArticle($article_id)
    {
        $article = Article::find($article_id);
        if (!$article) {
            return response()->json(['message' => 'failed', 'this article does not exist']);
        }
        $article->delete();
        return response()->json(['message' => 'success', 'deleted successfully']);
    }


public function updateArticle(Request $request, $article_id)
    {
        $article = Article::find($article_id);
        if (!$article) {
            return response()->json(['message' => 'failed', 'this article does not exist']);
        }
        if($request->all() == [])
        { return response()->json(['message' => 'failed', 'Please fill in at least one field']); }



        $image = $request->file('image');
        if($request->hasFile('image')){

                $fileName = date('dmY').time().'.'.$image->getClientOriginalExtension();
                $image->move(public_path("/pictures"), $fileName);


            }

            $article->update($request->only(['title', 'category', 'description', 'image']));

            $eventData = [
                'title' => $article->title,
                'category' => $article->category,
                'description' => $article->description,
                'image' => $article->image ? url('pictures/' . $article->image) : null,
            ];

        foreach (User::all() as $user) {
            if($user->role != 'superAdmin'){

                $user->notify(new UpdateArticle_Notify($article));
            }
        }

        return response()->json([
            'message' => 'success',
            'article'=>$eventData
          ]);
    }


    public function ShowArticle()
    {
        $articles = Article::get();
        if ($articles->isEmpty()) {
            return response()->json(['message' => 'failed', ' articles does not exist']);
        }
        $eventData = [];
        foreach ($articles as $article) {
            $eventData[] = [
                'id' => $article->id,
                'title' => $article->title,
                'category' => $article->category,
                'description' => $article->description,
                'image' => $article->image ? url('pictures/' . $article->image) : null,
            ];
        }
        return response()->json([
            'message' => 'success',
            'article' => $eventData,
        ]);
    }




    public function addEve_to_Galleries(Request $request, $event_id)
    {
        $validation = $request->validate([
            'description' => 'required',
            'image' => 'required',
        ]);
        $image = $request->file('image');
        if ($request->hasFile('image')) {
            $fileName = date('dmY') . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path("/pictures"), $fileName);
        }
        $user_id = auth()->user()->id;
        $existingGallery = Gallery::
        where('user_id', $user_id)
      ->where('event_id', $event_id)
      ->where('description', $validation['description'])
      ->first();
      if ( !$existingGallery) {
        $event = Gallery::create([
            'user_id' => $user_id,
            'event_id' => $event_id,
            'description' => $validation['description'],
            'image' => $fileName,
        ]);
        foreach (User::all() as $user) {
            if($user->id != $user_id){
                $user->notify(new AddGalleries_Notify($event));
            }
        }
        return response()->json([
            'message' => 'success',
            'Galleries' => $event,
            'image' => $event->image ? url('pictures/' . $event->image) : null,
        ]);
        }
            return response()->json([
                'message' => 'A similar Gallery already exists!',
            ], 400);
    }

    public function deleteEve_to_Galleries($gallery_id)
    {
        $event = Gallery::find($gallery_id);
        if (!$event) {
            return response()->json(['message' => 'failed', 'this event does not exist']);
        }
        $event->delete();
        return response()->json(['message' => 'success', 'deleted successfully']);
    }

    public function show_users()
    {
        $users = User::get();
        if (!$users) {
            return response()->json(['message' => 'failed', 'not found users']);
        }
        $eventData = [];
        foreach ($users as $user) {
                if($user->role !='superAdmin'){
                    $eventData[] = [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'role' => $user->role,
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'image' => $user->image ? url('pictures/' . $user->image) : null,
                    ];
                }
            }
        return response()->json([
            'message' => 'success',
            'Users' => $eventData,
        ]);
    }

    public function show_suggestions()
    {
        $suggestions = Suggestion::where('suggestion_status', 0)->get();
        if ($suggestions->count() == 0) {
            return response()->json(['message' => 'failed', 'not found suggestions']);
        }
        foreach ($suggestions as &$request) {
            $user = User::find($request['user_id']);
            $request['username'] = $user->first_name.' '.$user->last_name;
            $request['phone'] = $user->phone;
        }
        return response()->json([
            'message' => 'success',
            'Suggestions' => $suggestions,
        ]);
    }

    public function confirm_suggestions($request_id)
    {
        $suggestions_request = Suggestion::find($request_id);
        $user_id=$suggestions_request->user_id;
        $user=User::find($user_id);
        if (!$suggestions_request) {
            return response()->json(['message' => 'failed', 'Suggestion not found']);
        }
        if ($suggestions_request->suggestion_status == 0) {
            $suggestions_request->suggestion_status = 1;
            $suggestions_request->save();
            $user->notify(new Confirm_Suggestions_Notify($suggestions_request));
            return response()->json([
                'message' => 'success', 'The suggestion has been accepted ',
            ]);
        }
        return response()->json([
            'message' => 'failed', 'The operation failed ',
        ]);
    }

    public function show_accepted_suggestions()
    {
        $acceptedSuggestions = Suggestion::where('suggestion_status', 1)->get();
        if ($acceptedSuggestions->count() == 0) {
            return response()->json(['message' => 'failed', 'No accepted suggestions found']);
        }
        foreach ($acceptedSuggestions as &$request) {
            $user = User::find($request['user_id']);
            $request['username'] = $user->first_name.' '.$user->last_name;
            $request['phone'] = $user->phone;
        }
        return response()->json([
            'message' => 'success',
            'Accepted_suggestions' => $acceptedSuggestions,
        ]);
    }

    public function delete_suggestions($suggestions_id)
    {
        $suggestions = Suggestion::find($suggestions_id);
        if (!$suggestions) {
            return response()->json(['message' => 'failed this suggestion does not exist']);
        }
        $suggestions->delete();
        return response()->json(['message' => 'success deleted successfully']);
    }

    public function show_volunteer_requests()
    {
        $volunteer_requests = Volunteer::where('request_status' , 0)->get();
        if ($volunteer_requests->count() == 0) {
            return response()->json(['message' => 'failed', 'not found volunteer requests']);
        }
        foreach ($volunteer_requests as &$request) {
            $user = User::find($request['user_id']);
            $request['username'] = $user->first_name.' '.$user->last_name;
        }
        return response()->json([
            'message' => 'success',
            'Volunteer_requests' => $volunteer_requests,
        ]);
    }

    public function confirm_volunteer_requests($request_id)
    {
        $volunteer_request=Volunteer::find($request_id);
        $user_id=$volunteer_request->user_id;
        $user=User::find($user_id);
        if($volunteer_request->request_status == 0){
            $volunteer_request->request_status = 1;
            $volunteer_request->save();
            $user->notify(new Confirm_volunteer_Notify($volunteer_request));
            $user->role='volunteer';
            $user->save();
            return response()->json([
                'message' => 'success', 'The request has been accepted'
            ]);
        }
        return response()->json([
            'message' => 'failed', 'The operation failed',
        ]);
    }

    public function confirm_volEvent_requests($subscribe_id)
    {
        $volunteer_request=Subscribe::find($subscribe_id);
        $user_id=$volunteer_request->user_id;
        $user=User::find($user_id);
        if($volunteer_request->volunteering == 0){
            $volunteer_request->volunteering = 1;
            $volunteer_request->save();
            $user->notify(new Confirm_volunteer_Notify($volunteer_request));
            return response()->json([
                'message' => 'success ,The request has been accepted'
            ]);
        }
        return response()->json([
            'message' => 'failed , The operation failed',
        ]);

    }

    public function delete_volEvent_requests($subscribe_id)
    {
        $vol_request=Subscribe::find($subscribe_id);
        if($vol_request->request_status_vol==1){
            $vol_request->request_status_vol=0;
            if($vol_request->request_status_ben==0){
                $vol_request->delete();
                return response()->json(['message' => 'success', 'deleted successfully']);
            }
            $vol_request->save();
            return response()->json(['message' => 'success', 'deleted successfully']);
        }
        return response()->json(['message' => 'failed', 'not founded request ']);
    }

    public function confirm_benEvent_requests($subscribe_id)
    {
        $volunteer_request=Subscribe::find($subscribe_id);
        $user_id=$volunteer_request->user_id;
        $user=User::find($user_id);
        if($volunteer_request->benefit == 0){
            $volunteer_request->benefit = 1;
            $volunteer_request->save();
            $user->notify(new Confirm_volunteer_Notify($volunteer_request));
            return response()->json([
                'message' => 'success', 'The request has been accepted'
            ]);
        }
        return response()->json([
            'message' => 'failed', 'The operation failed',
        ]);
    }

    public function delete_benEvent_requests($subscribe_id)
    {
        $vol_request=Subscribe::find($subscribe_id);
        if($vol_request->request_status_ben==1){
            $vol_request->request_status_ben=0;
            if( $vol_request->request_status_vol==0 ){
                $vol_request->delete();
                return response()->json(['message' => 'success']);
            }
            $vol_request->save();
            return response()->json(['message' => 'success']);
        }
        return response()->json(['message' => 'failed']);
    }

    public function show_volEvent_requests($event_id)
    {
        $volunteer_requests = Subscribe::
            where('request_status_vol', 1)
            ->where('event_id', $event_id)
            ->get();
        if ($volunteer_requests->count() == 0) {
            return response()->json(['message' => 'failed', 'not found volunteer requests']);
        }
        foreach ($volunteer_requests as &$request) {
            $user = User::find($request['user_id']);
            $request['username'] = $user->first_name.' '.$user->last_name;
            $request['role'] = $user->role ;
            $request['study'] = $user->volunteer->studding ;
            $request['subscribe_id'] = $request-> id ;
        }
        return response()->json([
            'message' => 'success',
            'Volunteer_requests' => $volunteer_requests,
        ]);
    }

    public function show_benEvent_requests($event_id)
    {
        $volunteer_requests = Subscribe::
        where('request_status_ben' , 1)->
        where('event_id' , $event_id)->get();
        if ($volunteer_requests->count() == 0) {
            return response()->json(['message' => 'failed', 'not found volunteer requests']);
        }
        foreach ($volunteer_requests as &$request) {
            $user = User::find($request['user_id']);
            $request['username'] = $user->first_name.' '.$user->last_name;
            $request['role'] = $user->role ;
            }
            return response()->json([
                'message' => 'success',
                'Volunteer_requests' => $volunteer_requests,
            ]);
    }

    public function show_accepted_vol_requests($event_id)
    {
        $vol_requests = Subscribe::
        where('volunteering', 1)->
        where('event_id' , $event_id)->get();
        if ($vol_requests->count() == 0) {
            return response()->json(['message' => 'failed', 'No accepted vol_requests found']);
        }
        foreach ($vol_requests as &$request) {
            $user = User::find($request['user_id']);
            $request['username'] = $user->first_name.' '.$user->last_name;
            $request['role'] = $user->role ;
            $request['study'] = $user->volunteer->studding ;
        }
        return response()->json([
            'message' => 'success',
            'Accepted_vol_requests' => $vol_requests,
        ]);
    }

    public function show_accepted_ben_requests($event_id)
    {
        $ben_requests = Subscribe::
        where('benefit', 1)->
        where('event_id' , $event_id)->get();
        if ($ben_requests->count() == 0) {
            return response()->json(['message' => 'failed', 'No accepted ben_requests found']);
        }
        foreach ($ben_requests as &$request) {
            $user = User::find($request['user_id']);
            $request['username'] = $user->first_name.' '.$user->last_name;
            $request['role'] = $user->role ;
        }
        return response()->json([
            'message' => 'success',
            'Accepted_ben_requests' => $ben_requests,
        ]);
    }

    public function show_accepted_volunteer_requests()
    {
        $acceptedRequests = Volunteer::where('request_status', 1)->get();
        if ($acceptedRequests->count() == 0) {
            return response()->json(['message' => 'failed', 'No accepted volunteer requests found']);
        }
        foreach ($acceptedRequests as &$request) {
            $user = User::find($request['user_id']);
            $request['username'] = $user->first_name.' '.$user->last_name;
        }
        return response()->json([
            'message' => 'success',
            'Accepted_volunteer_requests' => $acceptedRequests,
        ]);
    }

    public function delete_volunteer_requests($request_id)
    {
        $volunteer_request=Volunteer::find($request_id);
        if (!$volunteer_request) {
            return response()->json(['message' => 'failed', 'this volunteer_requests does not exist']);
        }
        $volunteer_request->delete();
        return response()->json(['message' => 'success', 'deleted successfully']);
    }

    public function delete_user_account($user_id)
    {
        $user=User::find($user_id);
        if(!$user){
            return response()->json(['message' => 'failed', 'User not found']);
        }
        $user->delete();
        return response()->json(['message' => 'success', 'User account deleted successfully']);
    }

    public function show_help_requests()
    {
        $help_request = Help_request::get();
        if ($help_request->count() == 0) {
            return response()->json(['message' => 'failed', 'not found Help_requests']);
        }
        foreach ($help_request as &$request) {
            $user = User::find($request['user_id']);
            $request['username'] = $user->first_name.' '.$user->last_name;
        }
        return response()->json([
            'message' => 'success',
            'Help_requests' => $help_request,
        ]);
    }

    public function confirm_help_requests($request_id)
    {
        $help_requests=Help_request::find($request_id);
        $user_id=$help_requests->user_id;
        $user=User::find($user_id);
        if($help_requests->request_status == 0){
            $help_requests->request_status = 1;
            $help_requests->save();
            $user->notify(new Confirm_HelpR_Notify($help_requests));
            return response()->json([
                'message' => 'success The request has been accepted',
            ]);
        }
        return response()->json([
            'message' => 'failed The operation failed',
        ]);
    }


    public function delete_help_requests($request_id)
    {
        $help_request=Help_request::find($request_id);
        if (!$help_request) {
            return response()->json(['message' => 'failed', 'this help_request does not exist']);
        }
        $help_request->delete();
        return response()->json(['message' => 'success', 'deleted successfully']);
    }

    public function show_accepted_help_requests()
    {
        $help_requests = Help_request::where('request_status', 1)->get();
        if ($help_requests->count() == 0) {
            return response()->json(['message' => 'failed', 'No accepted help_requests found']);
        }
        foreach ($help_requests as &$request) {
            $user = User::find($request['user_id']);
            $request['username'] = $user->first_name.' '.$user->last_name;
        }
        return response()->json([
            'message' => 'success',
            'Accepted_help_requests' => $help_requests,
        ]);
    }

    public function add_donation_number( Request $request)
    {
        $validation = $request->validate([
            'phone' => 'required|regex:/^09[0-9]{8}$/',
        ]);
        $user_id=auth()->user()->id;
        $user=User::find($user_id);
        if($user->role != 'user' && $user->role != 'volunteer'){
            $existingnumber = Donation_number::
           where('phone', $validation['phone'] )->first();
          if(! $existingnumber){
              $donation_number=Donation_number::create([
                  'phone'=>$validation['phone'],
                  'user_id'=>$user_id,
              ]);
              return response()->json([
                  'message' => 'success',
                  'donation_number' => $donation_number,
              ]);
            }
            return response()->json([
                'message' => 'A similar number already exists!',
            ], 400);
         }
    }

    public function delete_donation_number($id)
    {
        $number=Donation_number::find($id);
        if (!$number) {
            return response()->json(['message' => 'failed', 'this number does not exist']);
        }
        $user_id=auth()->user()->id;
        $user=User::find($user_id);
        if($user->role != 'user' && $user->role != 'volunteer'){
            $number->delete();
        return response()->json([
            'message' => 'success','deleted successfully'
        ]);
         }
    }

    public function add_course(Request $request)
    {
        $validation = $request->validate([
            'name' => 'required',
            'image' => 'required',
            'url' => 'required',
            'discount' => 'required',
            'description' => 'required',
        ]);
        $image = $request->file('image');
        if ($request->hasFile('image')) {
            $fileName = date('dmY') . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path("/pictures"), $fileName);
        }
        $existingCourse = Course::
      where('url', $validation['url'])
      ->first();
      if ( !$existingCourse) {
        $course=Course::create([
            'name'=>$validation['name'],
            'url'=>$validation['url'],
            'discount'=>$validation['discount'],
            'description'=>$validation['description'],
            'image'=>$fileName,
        ]);
        $user_id=auth()->user()->id;
        foreach (User::all() as $user) {
            if($user->id != $user_id){
                $user->notify(new AddCourse_Notify($course));
            }
        }
        return response()->json([
            'message'=>'success',
            'course'=>$course,
            'image' => $course->image ? url('pictures/' . $course->image) : null,
        ]);
      }
        return response()->json([
            'message' => 'A similar Course already exists!',
        ], 400);
    }

public function update_course(Request $request, $course_id)
    {
        $course = Course::find($course_id);

        if (!$course) {
            return response()->json(['message' => 'failed this course does not exist']);
        }

        if ($request->all() == []) {
            return response()->json(['message' => 'failed Please fill in at least one field']);
        }

        $image = $request->file('image');
        if ($request->hasFile('image')) {
            $fileName = date('dmY') . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path("/pictures"), $fileName);
            $course->image = $fileName;
        }


        $course->update($request->only(['name', 'discount', 'description', 'url']));

        if( $image){
            $course->update([
                'image'=>$fileName
            ]);
        }
        $courseData = [
            'id' => $course->id,
            'name' => $course->name,
            'discount' => $course->discount,
            'description' => $course->description,
            'image' => $course->image ? url('pictures/' . $fileName) : null,
            'url' => $course->url,
        ];

        foreach (User::all() as $user) {
            if($user->role != 'superAdmin'){

                $user->notify(new UpdateCourse_Notify($course));
            }
        }

        return response()->json([
            'message' => 'success',
            'course' => $courseData
        ]);
    }
    public function showCourse($id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json(['message' => 'failed', 'this course does not exist']);
        }
        $courseData = [
            'id' => $course->id,
            'name' => $course->name,
            'discount' => $course->discount,
            'description' => $course->description,
            'image' => $course->image ? url('pictures/' . $course->image) : null,
            'url' => $course->url,
        ];
        return response()->json([
            'message' => 'success',
            'course' => $courseData
        ]);
    }

    public function delete_course($id)
    {
        $course=Course::find($id);
        if(!$course){
            return response()->json([
                'message'=>'failed',
            ]);
        }
        $sub_course=Subscribe_course::where('course_id', $id)->get();
        if($sub_course->isEmpty()){
            $course->delete();
            return response()->json([
                'message'=>'success',
            ]);
        }
        return response()->json([
            'message'=>'failed',
        ]);
    }

    public function show_ourTeam()
    {
        $eventData = [];
        $admins = User::whereIn('role', ['admin' ,'superAdmin'])->get();
        foreach ($admins as $user) {
            $eventData[] = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $user->role,
                'phone' => $user->phone,
                'email' => $user->email,
                'image' => $user->image ? url('pictures/' . $user->image) : null,
            ];
        }
        $volunteers = Volunteer::where('request_status', 1)->get();
        foreach ($volunteers as $request) {
            $user = User::where('id', $request->user_id)->first();
            if (!in_array($user->id, array_column($eventData, 'id'))) {
                $eventData[] = [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'role' => 'volunteer',
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'image' => $user->image ? url('pictures/' . $user->image) : null,
                ];
            }
        }
        return response()->json([
            'message' => 'success',
            'Team' => $eventData,
        ]);
    }
}
