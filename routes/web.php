<?php

use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('testing',function(){

    
    

    // Define your two datetime values
    $datetime1 = Carbon::parse('2023-09-06 10:00:00');
    $datetime2 = Carbon::parse('2023-09-06 15:30:00');

    // Calculate the difference in hours and minutes
    $diff = $datetime1->diff($datetime2);

    // Extract hours and minutes from the difference
    $hours = $diff->h;
    $minutes = $diff->i;

    // Output the result
    echo "Total Hours and Minutes Difference: $hours hours and $minutes minutes <br><br><br><br><br>";



    //-------------------------------------

    // Sample durations in "h:i" format
    $durations = ["2:30", "3:15", "1:45"];

    // Function to convert "h:i" format to minutes
    function convertToMinutes($time)
    {
        list($hours, $minutes) = explode(':', $time);
        return $hours * 60 + $minutes;
    }

    // Convert "h:i" durations to minutes and sum them up
    $totalMinutes = array_reduce($durations, function ($carry, $duration) {
        return $carry + convertToMinutes($duration);
    }, 0);

    // Calculate total hours and minutes
    $totalHours = floor($totalMinutes / 60);
    $totalMinutes %= 60;

    // Format the result as "h:i"
    $totalTime = Carbon::createFromTime($totalHours, $totalMinutes)->format('H:i');

    // Output the result
    echo "Total Hours: $totalTime";


});