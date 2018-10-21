<?php

namespace App\Http\Controllers;

use App\CheckIn;
use App\Token;
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

        // Response the successfully checked in message.
        return array_merge(array('result' => 'true', 'response' => 'You\'ve successfully checked in'),
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
            array('result' => 'true', 'response' => $checkInBreakDownThisMonth),
            // If updatedToken does exist, return updatedToken
            (($request->get('updatedToken') !== null) ? array('updatedToken' => $request->get('updatedToken')) : array()));

    }


    public function consecutiveCheckInCount(Request $request)
    {
        // Get user_id through token
        $user_id = Token::getUserIdThroughToken($request->token);

        // check if the user has checked in or not
        $todayCheckInExists = CheckIn::ifUserCheckInToday($user_id);

        // if the user has checked in today, calculate how many days the user has checked in consecutively, starting from today and go backwards.
        // if the check-in record exists, keep calculating.
        // if not, calculate from yesterday and go backwards.
       $consecutivelyCheckingInDays = $todayCheckInExists
           ? checkIn::howManyDaysTheUserHasCheckedInConsecutivelyUntilToday($user_id)
           : checkIn::howManyDaysTheUserHasCheckedInConsecutivelyUntilYesterday($user_id);

        // Check if it should day or days.
        $dayOrDays = str_plural('day', $consecutivelyCheckingInDays);


        // if the user has checked in today, return the message with how many days the user has checked in until today.
        // If not, also return with how many days the user has checked in until yesterday.
        return array_merge(
            array('result' => 'true', 'response' =>
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
