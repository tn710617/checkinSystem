<?php

namespace App\Http\Controllers;

use App\CheckIn;
use App\Token;
use App\User;
use Illuminate\Http\Request;

class checkInController extends Controller {

    public function checkIn(Request $request)
    {
        // Get the user_id from tokens table.
        $user_id = Token::getUserIdThroughToken($request->token);

        // Check if the user has checked in
        $ifUserCheckedInToday = CheckIn::ifUserCheckInToday($user_id);

        // If so, response that the user has already checked in.
        if ($ifUserCheckedInToday)
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
            'user_id'      => $user_id,
            'check_or_not' => 'checked',
        ]);

        // Get how many days the user has checked in.
        $howManyDaysTheUserHasCheckedIn = checkIn::howManyDaysTheUserHasCheckedInConsecutivelyUntilToday($user_id);

        // Get current total reward points of the user
        $totalRewardPoints = User::getTotalRewardPoints($user_id);

        // Get the rewards points and update it in database
        $rewardPointsToday = $howManyDaysTheUserHasCheckedIn * 2;
        User::where('id', $user_id)->update(['reward_points'               => ($totalRewardPoints + $rewardPointsToday),
                                             'consecutive_checked_in_days' => $howManyDaysTheUserHasCheckedIn
        ]);

        // Response the successfully checked in message.

        return array_merge(array('result' => 'true', 'response' =>
            [
                'memo'                           => 'You\'ve successfully checked in',
                'howManyDaysTheUserHasCheckedIn' => $howManyDaysTheUserHasCheckedIn,
                'rewardPointToday'               => $rewardPointsToday
            ]),
            (($request->get('updatedToken') !== null)
                ? array('updatedToken' => $request->get('updatedToken'))
                : array()));
    }


    public function showCheckIn(Request $request)
    {
        // Get the user_id through token
        $user_id = Token::getUserIdThroughToken($request->token);

        // Get the Day and check_or_not information from check_ins table with designated user_id.
        $dateAndCheckOrNotInformation = checkIn::getDateAndCheckOrNotInformation($user_id);

        // Ge check in or not information on each day in this month
        $checkInBreakDownThisMonth = checkIn::getCheckedInBreakDownThisMonth($dateAndCheckOrNotInformation);

        // return the result.
        return array_merge(
            array('result' => 'true', 'response'
                           => ['checkInBreakdown'               => $checkInBreakDownThisMonth,
                               'howManyDaysTheUserHasCheckedIn' => User::getTotalConsecutiveCheckedInDays($user_id),
                               'totalRewardPoints'              => User::getTotalRewardPoints($user_id)
                ]),
            // If updatedToken does exist, return updatedToken
            (($request->get('updatedToken') !== null) ? array('updatedToken' => $request->get('updatedToken')) : array()));

    }
}
