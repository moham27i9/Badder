<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
//Auth
Route::post('register' , [AuthController::class,'register']);
Route::post('login' , [AuthController::class,'login']);
Route::post('logout' , [AuthController::class,'logout']);

//account
Route::post('update_info_account' , [AuthController::class,'update_info_account'])->middleware(['jwt.auth']);
Route::delete('delete_my_account' , [UserController::class,'delete_my_account'])->middleware(['jwt.auth']);
Route::post('delete_user_account/{id}' , [AdminController::class,'delete_user_account'])->middleware(['jwt.auth','admin']);
Route::get('show_my_account' , [UserController::class,'show_my_account'])->middleware(['jwt.auth']);
Route::post('update_info_vol' , [UserController::class,'update_info_vol'])->middleware(['jwt.auth']);
Route::get('Show_volInfo' , [UserController::class,'Show_volInfo'])->middleware(['jwt.auth']);
// Route::delete('delete_my_account' , [UserController::class,'delete_my_account'])->middleware(['jwt.auth']);

Route::get('Show_User_by_id/{id}' , [AdminController::class,'Show_User_by_id'])->middleware(['jwt.auth']);

Route::post('changeRole/{id}' , [AdminController::class,'changeRole'])->middleware(['jwt.auth','superAdmin']);
Route::get('show_ourTeam' , [AdminController::class,'show_ourTeam'])->middleware(['jwt.auth']);

Route::get('show_users' , [AdminController::class,'show_users'])->middleware(['jwt.auth','admin']);

//Event
Route::post('addEvent' , [AdminController::class,'addEvent'])->middleware(['jwt.auth','admin']);
Route::post('Subscribe_to_event_bene/{id}' , [UserController::class,'Subscribe_to_event_bene'])->middleware(['jwt.auth']);
Route::post('Subscribe_to_events_vol/{id}' , [UserController::class,'Subscribe_to_events_vol'])->middleware(['jwt.auth']);
Route::get('ShowEvent' , [UserController::class,'ShowEvent'])->middleware(['jwt.auth']);
Route::get('Show_PreviousEvents' , [UserController::class,'Show_PreviousEvents'])->middleware(['jwt.auth']);
Route::get('Show_FutureEvents' , [UserController::class,'Show_FutureEvents'])->middleware(['jwt.auth']);
Route::get('Show_Event_by_id/{id}' , [UserController::class,'Show_Event_by_id'])->middleware(['jwt.auth']);
Route::post('updateEvent/{id}' , [AdminController::class,'updateEvent'])->middleware(['jwt.auth','admin']);
Route::post('deleteEvent/{id}' , [AdminController::class,'deleteEvent'])->middleware(['jwt.auth','admin']);
Route::post('React_to_events/{id}' , [UserController::class,'React_to_events'])->middleware(['jwt.auth']);
Route::post('confirm_volEvent_requests/{subscribe_id}' , [AdminController::class,'confirm_volEvent_requests'])->middleware(['jwt.auth','admin']);
Route::post('delete_volEvent_requests/{subscribe_id}' , [AdminController::class,'delete_volEvent_requests'])->middleware(['jwt.auth','admin']);
Route::post('confirm_benEvent_requests/{subscribe_id}' , [AdminController::class,'confirm_benEvent_requests'])->middleware(['jwt.auth','admin']);
Route::post('delete_benEvent_requests/{subscribe_id}' , [AdminController::class,'delete_benEvent_requests'])->middleware(['jwt.auth','admin']);
Route::get('show_volEvent_requests/{event_id}' , [AdminController::class,'show_volEvent_requests'])->middleware(['jwt.auth','admin']);
Route::get('show_benEvent_requests/{event_id}' , [AdminController::class,'show_benEvent_requests'])->middleware(['jwt.auth','admin']);
Route::post('show_accepted_vol_requests/{event_id}' , [AdminController::class,'show_accepted_vol_requests'])->middleware(['jwt.auth','admin']);
Route::post('show_accepted_ben_requests/{event_id}' , [AdminController::class,'show_accepted_ben_requests'])->middleware(['jwt.auth','admin']);

//Article
Route::post('save_article/{id}' , [UserController::class,'save_article'])->middleware(['jwt.auth']);
Route::post('cancelSave_article/{id}' , [UserController::class,'cancelSave_article'])->middleware(['jwt.auth']);
Route::get('Show_Article_by_id/{id}' , [UserController::class,'Show_Article_by_id'])->middleware(['jwt.auth']);
Route::get('show_saved_article' , [UserController::class,'show_saved_article'])->middleware(['jwt.auth']);
Route::post('addArticle' , [AdminController::class,'addArticle'])->middleware(['jwt.auth','admin']);
Route::get('ShowArticle' , [AdminController::class,'ShowArticle'])->middleware(['jwt.auth']);
Route::post('updateArticle/{id}' , [AdminController::class,'updateArticle'])->middleware(['jwt.auth','admin']);
Route::post('deleteArticle/{id}' , [AdminController::class,'deleteArticle'])->middleware(['jwt.auth','admin']);

//Galleries
Route::post('addEve_to_Galleries/{id}' , [AdminController::class,'addEve_to_Galleries'])->middleware(['jwt.auth','admin']);
Route::post('deleteEve_to_Galleries/{id}' , [AdminController::class,'deleteEve_to_Galleries'])->middleware(['jwt.auth','admin']);
Route::get('Show_Gallery_by_id/{id}' , [UserController::class,'Show_Gallery_by_id'])->middleware(['jwt.auth']);
Route::get('show_gallery' , [UserController::class,'show_gallery'])->middleware(['jwt.auth']);


//suggestions
Route::get('show_suggestions' , [AdminController::class,'show_suggestions'])->middleware(['jwt.auth','admin']);
Route::post('confirm_suggestions/{id}' , [AdminController::class,'confirm_suggestions'])->middleware(['jwt.auth','admin']);
Route::get('show_accepted_suggestions' , [AdminController::class,'show_accepted_suggestions'])->middleware(['jwt.auth','admin']);
Route::post('delete_suggestions/{id}' , [AdminController::class,'delete_suggestions'])->middleware(['jwt.auth','admin']);
Route::post('write_suggestion' , [UserController::class,'write_suggestion'])->middleware(['jwt.auth']);

//Courses
Route::post('add_course' , [AdminController::class,'add_course'])->middleware(['jwt.auth','admin']);
Route::post('update_course/{id}' , [AdminController::class,'update_course'])->middleware(['jwt.auth','admin']);
Route::post('delete_course/{id}' , [AdminController::class,'delete_course'])->middleware(['jwt.auth','admin']);
Route::get('show_courses' , [UserController::class,'show_courses'])->middleware(['jwt.auth']);
Route::post('filterCourses' , [UserController::class,'filterCourses'])->middleware(['jwt.auth']);
Route::get('/show_course/{id}', [AdminController::class, 'showCourse']);

//volunteer_requests
Route::get('show_volunteer_requests' , [AdminController::class,'show_volunteer_requests'])->middleware(['jwt.auth','admin']);
Route::get('confirm_volunteer_requests/{id}' , [AdminController::class,'confirm_volunteer_requests'])->middleware(['jwt.auth','admin']);
Route::get('show_accepted_volunteer_requests' , [AdminController::class,'show_accepted_volunteer_requests'])->middleware(['jwt.auth','admin']);
Route::post('delete_volunteer_requests/{id}' , [AdminController::class,'delete_volunteer_requests'])->middleware(['jwt.auth','admin']);
Route::post('volunteer_request' , [UserController::class,'volunteer_request'])->middleware(['jwt.auth']);

//help_requests
Route::get('show_help_requests' , [AdminController::class,'show_help_requests'])->middleware(['jwt.auth','admin']);
Route::get('confirm_help_requests/{id}' , [AdminController::class,'confirm_help_requests'])->middleware(['jwt.auth','admin']);
Route::post('delete_help_requests/{id}' , [AdminController::class,'delete_help_requests'])->middleware(['jwt.auth','admin']);
Route::get('show_accepted_help_requests' , [AdminController::class,'show_accepted_help_requests'])->middleware(['jwt.auth','admin']);
Route::post('add_donation_number' , [AdminController::class,'add_donation_number'])->middleware(['jwt.auth','admin']);
Route::post('delete_donation_number/{id}' , [AdminController::class,'delete_donation_number'])->middleware(['jwt.auth','admin']);
Route::post('help_request' , [UserController::class,'help_request'])->middleware(['jwt.auth']);


Route::get('show_donation_numbers' , [UserController::class,'show_donation_numbers'])->middleware(['jwt.auth']);

Route::get('hotList' , [UserController::class,'hotList']);

Route::get('Show_category' , [UserController::class,'Show_category']);

Route::post('add_Ads' , [AdminController::class,'add_Ads'])->middleware(['jwt.auth','admin']);
Route::get('Show_ads' , [UserController::class,'Show_ads']);

Route::post('rate_member_team/{ratingUser_id}' , [UserController::class,'rate_member_team'])->middleware(['jwt.auth']);
Route::post('delete_User_comment/{ratingUser_id}' , [UserController::class,'delete_User_comment'])->middleware(['jwt.auth']);;
Route::post('delete_User_rate/{ratingUser_id}' , [UserController::class,'delete_User_rate'])->middleware(['jwt.auth']);;
Route::get('show_User_rate/{User_id}' , [UserController::class,'show_User_rate'])->middleware(['jwt.auth']);;

Route::get('getNotifications' , [UserController::class,'getNotifications'])->middleware(['jwt.auth']);

// Route::get('statics' , [AdminController::class,'statics'])->middleware(['jwt.auth','admin']);

Route::post('Add_statics/{event_id}' , [AdminController::class,'Add_statics'])->middleware(['jwt.auth','admin']);
Route::get('show_statics' , [AdminController::class,'show_statics'])->middleware(['jwt.auth']);
Route::post('delete_statics/{event_id}' , [AdminController::class,'delete_statics'])->middleware(['jwt.auth','admin']);
