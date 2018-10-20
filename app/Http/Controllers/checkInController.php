<?php

namespace App\Http\Controllers;

use App\checkIn;
use App\Token;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class checkInController extends Controller {

    public function checkIn(Request $request)
    {
        // Get the user_id from tokens table.
        $user_id = token::where('api_token', $request->token)->first()->user_id;

        // Use the user_id we've got from tokens table to get the user information
        $userInfo = User::where('id', $user_id)->first();

        // Check if the user has checked in
        $checkInCount = checkIn::whereday('created_at', Carbon::now()->day)->where('user_id', $user_id)->count();

        // If so, response that the user has already checked in.
        if ($checkInCount)
        {
            return array_merge(
                $result = array('result' => 'true', 'response' => 'You already checked in today'),
                // If updatedToken does exist, also return with it.
                (($request->get('updatedToken') !== null)
                    ? array('updatedToken' => $request->get('updatedToken'))
                    : array()));
        }

        // If not, check the user in.
        checkIn::forceCreate([
            'user_id'      => $userInfo->id,
            'check_or_not' => 'checked',
        ]);

        // Response the successfully checked in message.
        return array_merge(
            $result = array('result' => 'true', 'response' => 'You\'ve successfully checked in'),
            (($request->get('updatedToken') !== null)
                ? array('updatedToken' => $request->get('updatedToken'))
                : array()));
    }


    public function showCheckIn(Request $request)
    {
        // Set the time variable for further usage.
        $time = Carbon::now();
        $thisYear = $time->year;
        $thisMonth = $time->month;
        $today = $time->day;

        // Get the user_id through token
        $user_id = token::where('api_token', $request->token)->first()->user_id;

        // Get the check in record and check in date of designated user_id
        $checkInInDetail = DB::table('check_ins')
            ->select(DB::raw('day(created_at)date, check_or_not'))
            ->whereMonth('created_at', $thisMonth)
            ->where('user_id', $user_id)
            ->get()->toArray();

        // Set an empty array for further usage
        $finalOutput = array();

        // Get how many days in this month
        $howManyDaysInAMonth = cal_days_in_month(CAL_GREGORIAN, $thisMonth, $thisYear);


        // If the day information we might get from check_ins table does exist,
        // return the value of check_or_not column.
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
            // If not, set the key as the date, and the value as no
            if (!isset($finalOutput[$daysInAMonth]))
            {
                // If the checked day is later than today, show 'to be seen'
                $finalOutput[$daysInAMonth] = $daysInAMonth > $today ? 'To be seen' : 'no';
            }
        }

        // return the result.
        return array_merge(
            $result = array('result' => 'true', 'response' => $finalOutput),
            // If updatedToken does exist, return updatedToken
            (($request->get('updatedToken') !== null) ? array('updatedToken' => $request->get('updatedToken')) : array()));

    }

    public function consecutiveCheckInCount(Request $request)
    {
        // check if the user has checked in or not
        $token = $request->token;
        $user_id = Token::where('api_token', $token)->first()->user_id;
        $todayCheckInExists = DB::table('tokens')
            ->where('api_token', $token)
            ->join('check_ins', 'tokens.user_id', '=', 'check_ins.user_id')
            ->select('check_ins.check_or_not')
            ->whereDay('check_ins.created_at', Carbon::now()->day)
            ->exists();

        // if the user has checked in today, calculate how many days the user has checked in consecutively, starting from today and go backwards.
        $i = 0;
        $count = 1;
        // if the count == 1, keep calculating.
        while ($count == 1)
        {
            if ($todayCheckInExists)
            {
                $count = checkIn::where('user_id', $user_id)->whereDay('created_at', Carbon::now()->subDays($i)->day)->count();
            }
            // if not, calculate from yesterday and go backwards.
            else
            {
                $count = checkIn::where('user_id', $user_id)->whereDay('created_at', Carbon::yesterday()->subDays($i)->day)->count();
            }

            $i = $i + 1;

        };


        $consecutivelyCheckingInDays = $i - 1;

        // Check if it should day or days.
        $dayOrDays = str_plural('day', $consecutivelyCheckingInDays);


        // if the user has checked in today, return the message with how many days the user has checked in until today.
        // If not, also return with how many days the user has checked in until yesterday.
        return array_merge(
            $result = array('result' => 'true', 'response' =>
                ($todayCheckInExists
                    ? 'You already checked in today, and you\'ve consecutively checked in for '
                    : 'You haven\'t checked in today, and you\'ve consecutively checked in for ')
                . $consecutivelyCheckingInDays
                . ' '
                . $dayOrDays),
            // If updatedToken does exist, return updatedToken
            (($request->get('updatedToken') !== null)
                ? array('updatedToken' => $request->get('updatedToken'))
                : array()));
    }
}
