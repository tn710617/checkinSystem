<?php

namespace App\Http\Controllers;

use App\checkIn;
use App\Http\Middleware\Admin;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class adminController extends Controller {

    public function showCheckInToday(Request $request)
    {
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

        if ($request->get('updatedToken'))
        {
            return ['result' => 'true', 'response' => ['checkedInUser' => $checkedInUsers, 'notYetCheckedInUsers' => $notYetCheckedInUsers, 'updatedToken' => $request->get('updatedToken')]];
        }
        return ['result' => 'true', 'response' => ['checkedInUser' => $checkedInUsers, 'notYetCheckedInUsers' => $notYetCheckedInUsers]];
    }

    public function showSingleUserCheckIn(Request $request)
    {
        $validator = validator::make($request->all(), [
            'user_id' => 'required'
        ]);
        if ($validator->fails())
        {
            return ['result' => 'false', 'response' => $validator->errors()->first()];
        }


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
                    $finalOutput[$daysInAMonth] = $data->check_or_not;
                    break;
                }
            }

            if (!isset($finalOutput[$daysInAMonth]))
            {
                $finalOutput[$daysInAMonth] = $daysInAMonth > $currentDate ? 'To be seen' : 'no';
            }
        }
        if ($request->get('updatedToken'))
        {
            return ['result' => 'true', 'response' => $finalOutput, 'updatedToken' => $request->get('updatedToken')];
        }

        return ['result' => 'true', 'response' => $finalOutput];
    }

}
