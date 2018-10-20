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
        $token_count = token::where('api_token', $request->token)->count();
        if (!$token_count)
        {
            return ['result' => 'false', 'response' => 'Please login before checking in'];
        }
        $user_id = token::where('api_token', $request->token)->first()->user_id;
        $userInfo = User::where('id', $user_id)->first();

        $currentDate = Carbon::now()->day;
        $checkInCount = checkIn::whereday('created_at', $currentDate)->where('user_id', $user_id)->count();

        if ($checkInCount)
        {
            return array_merge(
                $result = array('result' => 'true', 'response' => 'You already checked in today'),
                (($request->get('updatedToken') !== null)
                    ? array('updatedToken' => $request->get('updatedToken'))
                    : array()));
        }

        checkIn::forceCreate([
            'user_id'      => $userInfo->id,
            'check_or_not' => 'checked',
        ]);
//        if ($request->get('updatedToken'))
//        {
//            return ['result' => 'true', 'response' => 'You\'ve successfully checked in', 'updatedToken' => $request->get('updatedToken')];
//        }

        return array_merge(
            $result = array('result' => 'true', 'response' => 'You\'ve successfully checked in'),
            (($request->get('updatedToken') !== null)
                ? array('updatedToken' => $request->get('updatedToken'))
                : array()));
    }

    public function showCheckIn(Request $request)
    {
        $time = Carbon::now();
        $currentYear = $time->year;
        $currentMonth = $time->month;
        $currentDate = $time->day;
        $user_id = token::where('api_token', $request->token)->first()->user_id;

        $checkInInDetail = DB::table('check_ins')
            ->select(DB::raw('day(created_at)date, check_or_not'))
            ->whereMonth('created_at', $currentMonth)
            ->where('user_id', $user_id)
            ->get()->toArray();

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

        return array_merge(
            $result = array('result' => 'true', 'response' => $finalOutput),
            (($request->get('updatedToken') !== null) ? array('updatedToken' => $request->get('updatedToken')) : array()));

    }

    public function consecutiveCheckInCount(Request $request)
    {
        $token = $request->token;
        $user_id = Token::where('api_token', $token)->first()->user_id;
        $todayCheckInExists = DB::table('tokens')
            ->where('api_token', $token)
            ->join('check_ins', 'tokens.user_id', '=', 'check_ins.user_id')
            ->select('check_ins.check_or_not')
            ->whereDay('check_ins.created_at', Carbon::now()->day)
            ->exists();

        $i = 0;
        $count = 1;
        while ($count == 1)
        {
            if ($todayCheckInExists)
            {
                $count = checkIn::where('user_id', $user_id)->whereDay('created_at', Carbon::now()->subDays($i)->day)->count();
            } else
            {
                $count = checkIn::where('user_id', $user_id)->whereDay('created_at', Carbon::yesterday()->subDays($i)->day)->count();
            }

            $i = $i + 1;

        };

        $consecutivelyCheckingInDays = $i - 1;

        return array_merge(
            $result = array('result' => 'true', 'response' =>
                ($todayCheckInExists
                    ? 'You already checked in today, and you\'ve consecutively checked in for '
                    : 'You haven\'t checked in today, and you\'ve consecutively checked in for ')
                . $consecutivelyCheckingInDays
                . ($consecutivelyCheckingInDays > 1
                    ? ' days'
                    : ' day')),
            (($request->get('updatedToken') !== null)
                ? array('updatedToken' => $request->get('updatedToken'))
                : array()));
    }
}
