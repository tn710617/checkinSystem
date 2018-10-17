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
        $this->validate(request(), [
            'token' => 'required',
        ]);
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
            return ['result' => 'false', 'response' => 'You already checked in today'];
        }

        checkIn::forceCreate([
            'user_id'      => $userInfo->id,
            'check_or_not' => 'checked',
        ]);

        return ['result' => 'true', 'response' => 'You\'ve successfully checked in'];
    }

    public function showCheckIn(Request $request)
    {
        $this->validate(request(), [
            'token' => 'required'
        ]);

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

        return ['result' => 'true', 'response' => $finalOutput];
    }
}
