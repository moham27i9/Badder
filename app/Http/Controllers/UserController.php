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
use App\Models\Rating;
use App\Models\React;
use App\Models\SaveArticle;
use App\Models\Subscribe;
use App\Models\Suggestion;
use App\Models\User;
use App\Models\Volunteer;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getNotifications()
    {
        $notifications = Notification::orderBy('created_at', 'desc')->get();
        return response()->json([
            'notifications' => $notifications
        ]);
    }

    public function show_User_rate($user_id)
    {
        $rates = Rating::where('ratingUser_id', $user_id)->get();
        $formattedRates = [];
        foreach ($rates as $rate) {
            if (is_null($rate->rate) &&  !is_null($rate->comment)) {
                $formattedRates[] = [
                    'comment_id'=>$rate->id,
                    'commentedUserId' => $rate->ratedUser_id,
                    'comment' => $rate->comment,
                ];
            }
            if ( !is_null($rate->rate) &&  !is_null($rate->comment)) {
                $formattedRates[] = [
                    'comment_id'=>$rate->id,
                    'commentedUserId' => $rate->ratedUser_id,
                    'comment' => $rate->comment,
                ];
            }
        }
        $fiveStarCount = Rating::where('ratingUser_id', $user_id)
                            ->where('rate', 5)
                            ->count();
        $totalRateCount = $rates->count();
        $fiveStarPercentage = ($totalRateCount > 0)
                        ? ($fiveStarCount / $totalRateCount) * 100
                        : 0;
        return response()->json([
            'Rate' => $fiveStarPercentage,
            'Comments' => $formattedRates
        ]);
    }

    public function rate_member_team(Request $request ,$ratingUser_id)
    {
        $in_team=User::find($ratingUser_id);
        $in_vol=Volunteer::where('user_id' ,$ratingUser_id )->first();
        $user_id = auth()->user()->id;
        $validation = $request->validate([
            'rate' => 'nullable',
            'comment' => 'nullable|string',
        ]);
        if($request->has('rate') && ($validation['rate'] < 1 || $validation['rate'] > 5) ){
            return response()->json([
                'message' => 'failed التقييم يجب ان يكون بين 1 و 5 ',
            ], 400);
        }
        if ($in_team->role == 'admin' || $in_team->role == 'superAdmin' || ($in_vol && $in_vol->request_status == 1)) {
            if ($request->has('rate') || $request->has('comment')) {
                $existingRating = Rating::where('ratedUser_id', $user_id)
                ->where('ratingUser_id', $ratingUser_id)
                ->first();
                    if( $existingRating){
                        if( !$existingRating->rate ){
                            $existingRating->rate= $request->has('rate') ? $validation['rate'] : null;
                            $averageRating = Rating::where('ratingUser_id', $ratingUser_id)->avg('rate');
                            $existingRating->save();
                                return response()->json([
                                    'message' => 'success',
                                    'Rated'=>$existingRating,
                                    'averageRating' => $averageRating
                                ], 201);
                        }
                        if( !$existingRating->comment ){
                            $existingRating->comment= $request->has('comment') ? $validation['comment'] : null;
                            $existingRating->save();
                            return response()->json([
                                'message' => 'success',
                                'Rated'=>$existingRating,
                            ], 201);
                        }
                        return response()->json([
                            'message' => 'failed لديك تعليق وتقييم سابقين ',
                        ], 400);
                    }
                    else{
                           $rating = Rating::create([
                                'ratedUser_id' => $user_id,
                                'ratingUser_id' => $ratingUser_id,
                                'rate' => $request->has('rate') ? $validation['rate'] : null,
                                'comment' => $request->has('comment') ? $validation['comment'] : null,
                            ]);
                            $averageRating = Rating::where('ratingUser_id', $ratingUser_id)->avg('rate');
                            return response()->json([
                                'message' => 'success',
                                'Rated'=>$rating,
                                'averageRating' => $averageRating
                            ], 201);
                    }
              }
              else {
                  return response()->json([
                      'message' => 'failed الحقول فارغة ',
                  ], 400);
              }
            }
            return response()->json([
                'message' => 'failed ليس ادمن او متطوع هذا ' ,
            ], 400);
    }

    public function delete_User_comment( $ratingUser_id)
    {
        $user_id = auth()->user()->id;
        $rating = Rating::where('ratedUser_id', $user_id)
                         ->where('ratingUser_id', $ratingUser_id)->first();
        if ($rating) {
            if ($rating->comment) {
                if($rating->rate){
                    $rating->comment = null;
                    $rating->save();
                    return response()->json([
                        'message' => 'success ',
                    ], 200);
                }
                if(!$rating->rate){
                    $rating->delete();
                    return response()->json([
                        'message' => 'success ',
                    ], 200);
                }
            }
        }
        return response()->json([
            'message' => 'filed ليس لديك تعليق مسبق ',
        ], 200);
    }

    public function delete_User_rate( $ratingUser_id)
    {
        $user_id = auth()->user()->id;
        $rating = Rating::where('ratedUser_id', $user_id)
                         ->where('ratingUser_id', $ratingUser_id)->first();
        if ($rating) {
            if ($rating->rate) {
                if($rating->comment){
                    $rating->rate = null;
                    $rating->save();
                    return response()->json([
                        'message' => 'success ',
                    ], 200);
                }
                if(!$rating->comment){
                    $rating->delete();
                    return response()->json([
                        'message' => 'success ',
                    ], 200);
                }
            }
        }
        return response()->json([
            'message' => 'filed ليس لديك تعليق مسبق ',
        ], 200);
    }

    public function Show_category()
    {
        $catData = [
            '1' => 'medical',
            '2' => 'social',
            '3' => 'educational',
            '4' => 'recreational',
        ];
        return response()->json([
            'message' => 'success',
            'category' => $catData,
        ]);
    }

    public function Show_ads()
    {
        $ads_exist=Ads::get();
        if ($ads_exist->count()==0) {
            return response()->json(['message' => 'failed not found ads']);
        }
         $adsData=[];
        foreach($ads_exist as $ads){
            $adsData[]=[
                'id' => $ads->id,
                'title' => $ads->title,
                'description' => $ads->description,
                'image' => $ads->image ? url('pictures/' . $ads->image) : null,
            ];
        }
        return response()->json([
            'message' => 'success',
            'Ads' => $adsData,
        ]);
    }

    public function Show_volInfo()
    {
        $user = auth()->user();
        $user_id = $user->id;
        $vol_exist=Volunteer::where('user_id' , $user_id)->first();
        if (!$vol_exist) {
            return response()->json(['message' => 'failed']);
        }
        $volData = [
            'id' => $vol_exist->id,
            'skills' => $vol_exist->skills,
            'studding' => $vol_exist->studding,
            'availableTime' => $vol_exist->availableTime,
        ];
        return response()->json([
            'message' => 'success',
            'volunteer' => $volData,
        ]);
    }

public function update_info_vol(Request $request)
    {
        if($request->all() == [])
        { return response()->json(['message' => 'field']); }

        $user = auth()->user();
        $user_id = $user->id;
        $vol_exist=Volunteer::where('user_id' , $user_id)->first();

        if ($vol_exist->count()== 0 ) {
            return response()->json(['message' => 'field']);
        }

        $vol_exist->update($request->only(['studding', 'skills', 'availableTime']));

        return response()->json(['message' => 'success']);
    }
    public function hotList()
    {
        $eventData=[];
        $events = Event::orderBy('created_at', 'desc')->limit(2)->get();
        foreach ($events as $event) {
            $eventData['events'] = [
                'id' => $event->id,
                'name' => $event->name,
                'type' => $event->type,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'location' => $event->location,
                'description' => $event->description,
               'image' => $event->image ? url('pictures/' . $event->image) : null,
            ];
        }
        $courses = Course::orderBy('created_at', 'desc')->limit(2)->get();
        foreach ($courses as $course) {
        $eventData['courses'] = [
            'id' => $course->id,
            'name' => $course->name,
            'discount' => $course->discount,
            'url' => $course->url,
            'description' => $course->description,
            'image' => $course->image ? url('pictures/' . $course->image) : null,
        ];
         }
        $articles = Article::orderBy('created_at', 'desc')->limit(2)->get();
        foreach ($articles as $article) {
            $eventData['articles'] = [
                'id' => $article->id,
                'title' => $article->title,
                'category' => $article->category,
                'description' => $article->description,
                'image' => $article->image ? url('pictures/' . $article->image) : null,
            ];
        }
        $gallery = Gallery::orderBy('created_at', 'desc')->limit(2)->get();
        foreach ($gallery as $gall) {
            $eventData['gallery'] = [
                'id' => $gall->id,
                'user_id' => $gall->user_id,
                'event_id' => $gall->event_id,
                'description' => $gall->description,
                'image' => $gall->image ? url('pictures/' . $gall->image) : null,
            ];
        }
        return response()->json([
            'message' => 'success',
            'HotList'=>$eventData
        ]);
    }

    public function React_to_events($event_id)
    {
        $user = auth()->user();
        $user_id = $user->id;
        $event = Event::find($event_id);
        if (!$event) {
            return response()->json(['Error' => 'Event not found']);
        }
        $react_exist=React::where('user_id' ,$user_id)->
                            where('event_id' ,$event_id)->first();
         $react_count = React:: where('event_id' ,$event_id)->count();
        if($react_exist){
            $react_exist->delete();
            return response()->json([
                'message'=>'success',
                'countAll'=>$react_count-1
             ]);
        }
        $react=React::create([
            'user_id'=>$user_id,
            'event_id'=>$event_id,
            'react'=>1,
        ]);
        return response()->json([
           'message'=>'success',
           'reacted'=>$react,
           'countAll'=>$react_count+1,
        ]);
    }

    public function delete_my_account()
    {
        $user_id = Auth::user()->id;
        $user = User::find($user_id);
        if($user->role != 'superAdmin'){
            $user->delete();
            return response()->json([
                'message' => 'success','success account deleted'
            ]);
        }
    }

    public function ShowEvent_information($event_id)
    {
        $event = Event::find($event_id);
        if (!$event) {
            return response()->json(['message' => 'failed', 'events does not exist']);
        }
        return response()->json([
            'message' => 'success',
            'event' => $event,
        ]);
    }

    public function Subscribe_to_event_bene( $event_id)
    {
        $user_id = auth()->user()->id;
        $event = Event::find($event_id);
        if (!$event) {
            return response()->json(['message' => 'failed', 'events does not exist']);
        }
        $current_date = now();
        $event_end_date = Carbon::parse($event->end_date);
        $diffInDays = $event_end_date->diff($current_date);
        $existingSubscribe = Subscribe::
        where('user_id', $user_id)
        ->where('event_id', $event_id )
        ->where('request_status_ben', 1 )->first();
        $Subscribe_v = Subscribe::
        where('user_id', $user_id)
        ->where('event_id', $event_id )->first();
        if ($diffInDays->invert > 0) {
                if ( !$existingSubscribe) {
                if(! $Subscribe_v){
                    $subscribe = Subscribe::create([
                        'user_id' => $user_id,
                        'event_id' => $event_id,
                        'request_status_ben' => 1,
                    ]);
                    return response()->json([
                        'message' => 'success',
                        'Subscribed' => $subscribe,
                    ]);
                }
                else{
                    $Subscribe_v->request_status_ben=1;
                    $Subscribe_v->save();
                    return response()->json([
                        'message' => 'success',
                    ]);
                }
                }
                else{
                    return response()->json([
                        'message' => 'A similar subscribe already exists!',
                    ], 400);
                }
        }
        else {
            return response()->json(['message' => 'failed', 'event has ended']);
        }
    }

    public function Subscribe_to_events_vol( $event_id)
    {
        $user_id = auth()->user()->id;
        $vol=Volunteer::where('user_id' , $user_id)->first();
        if(!$vol)  return response()->json(['message' => 'failed', 'you are not volunteer in Bader']);
        if($vol->request_status != 1 ){
            return response()->json(['message' => 'failed', 'you are not volunteer in Bader --']);
        }
        else{
        $event = Event::find($event_id);
        if (!$event) {
            return response()->json(['message' => 'failed', 'events does not exist']);
        }
        $current_date = now();
        $event_end_date = Carbon::parse($event->end_date);
        $diffInDays = $event_end_date->diff($current_date);
        $existingSubscribe = Subscribe::
        where('user_id', $user_id)
      ->where('event_id', $event_id )
      ->where('request_status_vol', 1 )->first();
      $Subscribe_b = Subscribe::
      where('user_id', $user_id)
        ->where('event_id', $event_id )->first();
         if ($diffInDays->invert > 0) {
              if ( !$existingSubscribe) {
                if(! $Subscribe_b){
                    $subscribe = Subscribe::create([
                        'user_id' => $user_id,
                        'event_id' => $event_id,
                        'request_status_vol' => 1,
                    ]);
                    return response()->json([
                        'message' => 'success',
                        'Subscribed' => $subscribe,
                    ]);
                }else{
                    $Subscribe_b->request_status_vol=1;
                            $Subscribe_b->save();
                            return response()->json([
                                'message' => 'success',
                            ]);
                }
                }
                else{
                    return response()->json([
                        'message' => 'A similar subscribe already exists!',
                    ], 400);
                }
        }
        else {
            return response()->json(['message' => 'failed', 'event has ended']);
        }
      }
    }

    public function show_gallery()
    {
        $gallery = Gallery::get();
        if ($gallery->count()==0) {
            return response()->json(['message' => 'failed', 'gallery not found']);
        }
        $eventData = [];
        foreach ($gallery as $gall) {
            $eventData[] = [
                'id' => $gall->id,
                'description' => $gall->description,
                'image' => $gall->image ? url('pictures/' . $gall->image) : null,
            ];
        }
        return response()->json([
            'message' => 'success',
            'gallery' => $eventData,
        ]);
    }

    public function write_suggestion(Request $request)
    {
        $validation = $request->validate([
            'description' => 'required',
        ]);
        $user_id = auth()->user()->id;
        $existingsuggestion = Suggestion::
        where('user_id', $user_id)
      ->where('description', $validation['description'] )->first();
      if(! $existingsuggestion){
          $suggestion=Suggestion::create([
              'description'=>$validation['description'],
              'user_id'=>$user_id,
              'date'=>now(),
            ]);
            return response()->json([
                'message' => 'success',
                'Suggestion' => $suggestion,
            ]);
        }
        return response()->json([
            'message' => 'A similar suggestion already exists!',
        ], 400);
    }

    public function show_donation_numbers()
    {
        $donation_number = Donation_number::get();
        if ($donation_number->count()==0) {
            return response()->json(['message' => 'failed', 'donation number not found']);
        }
        return response()->json([
            'message' => 'success',
            'donation_number' => $donation_number,
        ]);
    }

    public function show_my_account()
    {
        $user_id=auth()->user()->id;
        $my_account=User::find($user_id);
        return response()->json([
            'message'=>'success',
            'my_account'=>$my_account
        ]);
    }

    public function ShowEvent()
    {
        $events = Event::get();
        if ($events->isEmpty()) {
            return response()->json(['message' => 'failed', 'events does not exist']);
        }
        $eventData = [];
        foreach ($events as $event) {
            $eventData[] = [
                'id' => $event->id,
                'name' => $event->name,
                'type' => $event->type,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'location' => $event->location,
                'description' => $event->description,
                'target_category' => $event->target_category,
                'image' => $event->image ? url('pictures/' . $event->image) : null,
            ];
        }
        return response()->json([
            'message' => 'success',
            'events' => $eventData,
        ]);
    }

    public function Show_Event_by_id($event_id)
    {
        $event = Event::find($event_id);
        if (!$event) {
            return response()->json(['message' => 'failed', 'event does not exist']);
        }
        $eventData = [
            'id' => $event->id,
            'name' => $event->name,
            'type' => $event->type,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'location' => $event->location,
            'description' => $event->description,
            'target_category' => $event->target_category,
            'image' => $event->image ? url('pictures/' . $event->image) : null,
        ];
        return response()->json([
            'message' => 'success',
            'event' => $eventData,
        ]);
    }

    public function Show_Article_by_id($article_id)
    {
        $article = Article::find($article_id);
        if (!$article) {
            return response()->json(['message' => 'failed', 'article does not exist']);
        }
        $eventData = [
            'id' => $article->id,
            'title' => $article->title,
            'category' => $article->category,
            'description' => $article->description,
            'image' => $article->image ? url('pictures/' . $article->image) : null,
        ];
        return response()->json([
            'message' => 'success',
            'article' => $eventData,
        ]);
    }

    public function Show_Gallery_by_id($gallery_id)
    {
        $gallery = Gallery::find($gallery_id);
        if (!$gallery) {
            return response()->json(['message' => 'failed', 'gallery does not exist']);
        }
        $eventData = [
            'id' => $gallery->id,
            'description' => $gallery->description,
            'image' => $gallery->image ? url('pictures/' . $gallery->image) : null,
        ];
        return response()->json([
            'message' => 'success',
            'gallery' => $eventData,
        ]);
    }

    public function Show_PreviousEvents()
    {
        $current_date = now();
        $expiredEvents = collect();
        Event::all()->each(function ($event) use ($current_date, $expiredEvents) {
            $event_end_date = Carbon::parse($event->end_date);
            $diffInDays = $event_end_date->diff($current_date);
             if ($diffInDays->invert <= 0) {
                $expiredEvents->push($event);
            }
        });
        if ($expiredEvents->isEmpty()) {
            return response()->json(['message' => 'failed', 'events does not exist']);
        }
        return response()->json([
            'message' => 'success',
            'events' => $expiredEvents,
        ]);
    }

    public function Show_FutureEvents()
    {
        $current_date = now();
        $expiredEvents = collect();
        Event::all()->each(function ($event) use ($current_date, $expiredEvents) {
            $event_end_date = Carbon::parse($event->end_date);
            $diffInDays = $event_end_date->diff($current_date);
             if ($diffInDays->invert > 0) {
                $expiredEvents->push($event);
            }
        });
        if ($expiredEvents->isEmpty()) {
            return response()->json(['message' => 'failed', 'events does not exist']);
        }
        return response()->json([
            'message' => 'success',
            'events' => $expiredEvents,
        ]);
    }

    public function volunteer_request(Request $request)
    {
        $validation=$request->validate([
            'studding'=>'required',
            'skills'=>'required',
            'availableTime'=>'required',
        ]);
        $user_id=auth()->user()->id;
        $existingvol_request = Volunteer::
        where('user_id', $user_id)->first();
        if(! $existingvol_request){
            $info=Volunteer::create([
                'user_id'=>$user_id,
                'studding'=>$validation['studding'],
                'skills'=>$validation['skills'],
                'vol_Date'=>now(),
                'availableTime'=>$validation['availableTime'],
            ]);
            return response()->json([
                'message'=>'success',
                'info'=>$info
            ]);
        }
        return response()->json([
            'message' => 'A similar request already exists!',
        ], 400);
    }


    public function help_request(Request $request)
    {
        $validation=$request->validate([
            'description'=>'required',
            'city'=>'required',
            'street'=>'required',
            'neighborhood'=>'required',
        ]);
        $user_id=auth()->user()->id;
        $h_request = new Help_request();
        $h_request->user_id = $user_id;
        $h_request->city = $validation['city'];
        $h_request->street = $validation['street'];
        $h_request->neighborhood = $validation['neighborhood'];
        $h_request->description = $validation['description'];
        $h_request->save();
        return response()->json([
            'message'=>'success',
            'info'=>$h_request
        ]);
        return response()->json([
            'message' => 'A similar request already exists!',
        ], 400);
    }

    public function save_article($id)
    {
        $article=Article::find($id);
        if(!$article){
            return response()->json([
                'message'=>'failed'
            ]);
        }
        $user_id=auth()->user()->id;
        $existingArticle = SaveArticle::
        where('user_id', $user_id )->
        where('article_id', $id)->first();
        if(! $existingArticle){
        $save=SaveArticle::create([
            'user_id'=>$user_id,
            'article_id'=>$id,
        ]);
        return response()->json([
            'message'=>'success' ,$save
        ]);
        }
        return response()->json([
            'message' => 'A similar article already exists!',
        ], 400);
    }

    public function cancelSave_article($id)
    {
        $article=SaveArticle::find($id);
        if(!$article){
            return response()->json([
                'message'=>'failed'
            ]);
        }
        $user_id=auth()->user()->id;
        if($user_id==$article->user_id){

            $article->delete();
            return response()->json([
                'message' => 'success','success article deleted'
            ]);
        }
        return response()->json([
            'message' => 'failed','can not article deleted'
        ]);
    }

    public function show_saved_article()
    {
        $user_id=auth()->user()->id;
        $saved=SaveArticle::where('user_id', $user_id)->get();
        if($saved->isEmpty() ){
            return response()->json([
                'message'=>'failed',
            ]);
        }
        return response()->json([
            'message'=>'success',
            'article'=>$saved
        ]);
    }

    public function show_courses()
    {
        $courses=Course::get();
        if($courses->isEmpty()){
            return response()->json([
                'message'=>'failed',
            ]);
        }
        $courseData = [];
        foreach ($courses as $course) {
            $courseData[] = [
                'id' => $course->id,
                'name' => $course->name,
                'url' => $course->url,
                'discount' => $course->discount,
                'description' => $course->description,
                'image' => $course->image ? url('pictures/' . $course->image) : null,
            ];
        }
        return response()->json([
            'message'=>'success',
            'courses'=>$courseData
        ]);
    }

    public function filterCourses(Request $request)
    {
        $description = $request->input('description');
        $name = $request->input('name');
        $courses = Course::query();
        if ($description) {
            $courses->where('description', 'like', '%'.$description.'%');
        }
        if ($name) {
            $courses->where('name', 'like', '%'.$name.'%');
        }
        $filteredCourses = $courses->get();
        return response()->json([
            'message'=>'success',
            'course'=>$filteredCourses
        ]);
    }
}
