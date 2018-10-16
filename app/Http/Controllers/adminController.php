<?php

namespace App\Http\Controllers;

use App\checkIn;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class adminController extends Controller {

    public function showCheckInToday(Request $request)
    {
        $this->validate(request(), [
            'token' => 'required'
        ]);
        $time = Carbon::now();
        $currentYear = $time->year;
        $currentMonth = $time->month;
        $today = $time->day;

        $checkedInUsersId = checkIn::whereDay('created_at', $today)->get();

        $checkedInUsersIdArray = array();
        $checkedInUsers = array();
        foreach ($checkedInUsersId as $data)
        {
            $checkedInUser = User::where('id', $data->user_id)->first();
            $checkedInUsers[] = $checkedInUser;
            $checkedInUsersIdArray[] = $data->user_id;
        }

        $notYetCheckedInUsers = User::whereNotIn('id', $checkedInUsersIdArray)->get();

        return ['result' => 'true', 'response' => ['checkedInUser' => $checkedInUsers, 'notYetCheckedInUserrrs' => $notYetCheckedInUsers]];

    }

    public function showSingleUserCheckIn(Request $request)
    {
        $this->validate(request(), [
            'token' => 'required',
            'user_id' => 'required'
        ]);

        $time = Carbon::now();
        $currentYear = $time->year;
        $currentMonth = $time->month;
        $currentDate = $time->day;

        $checkInInDetail = DB::table('check_ins')
            ->select(DB::raw('day(created_at)date, check_or_not'))
            ->whereMonth('created_at', $currentMonth)
            ->where('user_id', $request->user_id)
            ->get()->toArray();

//
        $finalOutput = array();


        $howManyDaysInAMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);

        for ($daysInAMonth = 1; $daysInAMonth <= $howManyDaysInAMonth; $daysInAMonth ++)
        {

            foreach ($checkInInDetail as $data)
            {
                if ($daysInAMonth == $data->date)
                {
                    $finalOutput[$data->date] = $data->check_or_not;
                    break;
                }
            }

            if (!isset($finalOutput[$data->date]))
            {
                $finalOutput[$daysInAMonth] = 'no';
            }
        }
        return $finalOutput;

    }

}
