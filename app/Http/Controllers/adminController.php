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
        // Get the checked-in information today from check_ins table.
        $checkedInUsersId = checkIn::whereDay('created_at', Carbon::now()->day)->get();

        // Name variables for further usage
        $checkedInUsersIdArray = array();
        $checkedInUsers = array();

        // Use foreach to get the information that we've got from check_ins table
        foreach ($checkedInUsersId as $data)
        {
            // Get the individual checked-in user with information got from check_ins table
            $checkedInUser = User::where('id', $data->user_id)->first();

            // Put these checked-in user that we've got from users table into an array for further usage
            $checkedInUsers[] = $checkedInUser;

            // Also fetch the checked-in user_id from check_ins table and put them into an array for further usage
            $checkedInUsersIdArray[] = $data->user_id;
        }

        // select all the users from users table except for those checked-in user_id to get not-yet checked in users.
        $notYetCheckedInUsers = User::whereNotIn('id', $checkedInUsersIdArray)->get();

        // Return the result
        return array_merge(
            $result = array('result'   => 'true',
                            'response' => [
                                'checkedInUser'        => $checkedInUsers,
                                'notYetCheckedInUsers' => $notYetCheckedInUsers
                            ]),
            // If updated token that we might get from middleware does exist, return it.
            (($request->get('updatedToken') !== null)
                ? array('updatedToken' => $request->get('updatedToken'))
                : array()));
    }


    public function showSingleUserCheckIn(Request $request)
    {
        // Validate to-be-checked user_id
        $validator = validator::make($request->all(), [
            'user_id' => 'required'
        ]);
        // If not, return with error message.
        if ($validator->fails())
        {
            return ['result' => 'false', 'response' => $validator->errors()->first()];
        }


        // Set the time for further usage.
        $time = Carbon::now();
        $currentYear = $time->year;
        $currentMonth = $time->month;
        $currentDate = $time->day;

        // Get the Day and check_or_not information from check_ins table with designated user_id.
        $checkInInDetail = DB::table('check_ins')
            ->select(DB::raw('day(created_at)date, check_or_not'))
            ->whereMonth('created_at', $currentMonth)
            ->where('user_id', $request->user_id)
            ->get()->toArray();

        // Set an empty array for further usage.
        $finalOutput = array();

        // Get how many days in the current month
        $howManyDaysInAMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);

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

            // If not, return 'no' on that day.
            if (!isset($finalOutput[$daysInAMonth]))
            {
                // If the checked day is later than today, show 'to be seen'
                $finalOutput[$daysInAMonth] = $daysInAMonth > $currentDate ? 'To be seen' : 'no';
            }
        }


        // return the result.
        return array_merge(
            $result = array('result' => 'true', 'response' => $finalOutput),
            // If updatedToken does exist, return updatedToken
            (($request->get('updatedToken') !== null)
                ? array('updatedToken' => $request->get('updatedToken'))
                : array()));

    }

}
