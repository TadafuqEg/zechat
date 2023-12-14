<?php

namespace Database\Seeders;

use App\Http\Services\AttendanceService;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
class AttendanceSeeder extends Seeder
{
    
    public function run(): void
    {
        $attendanceService = new AttendanceService();
        $users = User::where('guard','user')->get();
        $months = [6,7,8,9];
        $days = ['01','02','03','04','05','06','07','08','09','10','11','12','13','14','15',
        '16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31'];
        foreach($users as $user)
        {
            foreach($months as $month){
                foreach($days as $day){
                    $date = Carbon::parse('2023-0'.$month.'-'.$day);
                    if($date->format('Y-m-d') <= now()->format('Y-m-d')){
                        if($date->format('l') != 'Friday' && $date->format('l') != 'Saturday')
                        {
                            $attendance = Attendance::create([
                                'day_name' => $date->format('l'),
                                'date' => $date->format('Y-m-d'),
                                'user_id' => $user->id,
                            ]);
                            $start = AttendanceLog::create([
                                // 'date_time_recording' => $date->format('Y-m-d').' 0'.rand(7,9).':'.rand(0,5).rand(0,9).':00',
                                'date_time_recording' => $date->format('Y-m-d').' 09:00:00',
                                'action' => 'start_time',
                                'map_link' => 'google.com',
                                'attendance_id' => $attendance->id,
                            ]);
    
                            $end = AttendanceLog::create([
                                // 'date_time_recording' => $date->format('Y-m-d').' 1'.rand(3,8).':'.rand(0,5).rand(0,9).':00',
                                'date_time_recording' => $date->format('Y-m-d').' 18:00:00',
                                'action' => 'end_time',
                                'map_link' => 'google.com',
                                'attendance_id' => $attendance->id,
                            ]);
                            $attendance->total_worked_hours = (string)$attendanceService->calculateRageBetweenTwoTimes($start->date_time_recording,$end->date_time_recording);
                            $attendance->save();
                        }
                    }
                }
            }
        }

    }
}
