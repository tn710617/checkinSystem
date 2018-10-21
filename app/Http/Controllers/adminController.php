<?php

namespace App\Http\Controllers;

use App\checkIn;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
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



        // Get the Day and check_or_not information from check_ins table with designated user_id.
        $dateAndCheckOrNotInformation = checkIn::getDateAndCheckOrNotInformation($request->user_id);

        // Ge check in or not information on each day in this month
        $checkInBreakDownThisMonth = checkIn::getCheckedInBreakDownThisMonth($dateAndCheckOrNotInformation);

        // Check if the user checked in today
        $ifTheUserCheckedInToday = checkIn::ifUserCheckInToday($request->user_id);

        // Get how many days the user has checked in consecutively
        $howManyDaysTheUserHasCheckedInConsecutively = ($ifTheUserCheckedInToday)
            ? checkIn::howManyDaysTheUserHasCheckedInConsecutivelyUntilToday($request->user_id)
            : checkIn::howManyDaysTheUserHasCheckedInConsecutivelyUntilYesterday($request->user_id);

        // return the result.
        return array_merge(
            $result = array('result' => 'true', 'response' => ['CheckedInBreakDown' => $checkInBreakDownThisMonth, 'howManyDaysTheUserHasCheckedIn' => $howManyDaysTheUserHasCheckedInConsecutively]),
            // If updatedToken does exist, return updatedToken
            (($request->get('updatedToken') !== null)
                ? array('updatedToken' => $request->get('updatedToken'))
                : array()));

    }

}
