English | [繁體中文](README.zh-TW.md)
 
<h1 align="center">A user daily check-in and point-rewarding system</h1>

### Basic features
- It comes with admin and user system.
- User register function.
- User login function.
- User personal data update function.
- User logout function.
- Except for register function, other services are only accessible after logged in.

### For admin only
 - Show the check-in breakdown for every user today.
 - Show designated user's check-in breakdown this month
 - Show how many days the designated user has checked in consecutively, and its total reward points
 - Show the breakdown of consecutively checking in and reward-points for all registered users.

### For users
 - Check in today function
 - If the user has checked in today, return the corresponding message.
 - Show user's check-in breakdown this month.
 - When checking in, how many days the user has checked in consecutively will be automatically calculated and shown.
 - When checking in, reward-points will be calculated according to how many days the user has checked in consecutively, and shown to the user.
 
 ### installing instruction (command line):
 1. git clone `git@github.com:tn710617/checkinSystem.git`
 `cd checkinSystem`
 2. Enter `composer install` 
 3. Create a database. 
 4. Enter `cp .env.example .env`
, and replace the parameters with your database setting.
 5. Enter `php artisan key:generate`   
 6. Enter `php artisan migrate`
 7. Hope that you will be enjoying it.
 
 For information in detail, please refer to the documentation as follows:<br/>
 https://tn710617.github.io/API_Document/checkInSystem/
 
