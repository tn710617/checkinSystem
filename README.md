每日簽到獲取積分系統
===
基本功能
- 分為管理員、會員系統
- 使用者註冊功能
- 使用者登入功能
- 使用者個人資料更新功能
- 使用者登出功能
- 使用者需要登入才能使用服務

後台（管理員） 
 - 顯示當日簽到狀態（未簽到、已簽到列表） 
 - 顯示某用戶的簽到狀態未(簽到、已簽到列表)
 - 顯示特定用戶連續簽到天數與總累積積分
 - 顯示所有用戶連續簽到天數與總累積積分
 
前台（使用者）
 - 進行今日簽到
 - 重複簽到判斷，顯示「今日已簽到」 
 - 顯示過去簽到狀態（每日的未簽到、已簽到列表)
 - 使用者登入時自動計算連續簽到天數
 - 使用者登入時依據連續簽到天數給予積分
 
 安裝步驟(終端機)：
 1. git clone `git@github.com:tn710617/checkinSystem.git`
 2. 輸入：`cd checkinSystem`
 `composer install`
 3. 創建您自己的資料庫。
 4. 輸入: `cp .env.example .env`
 `vim .env`，並輸入您自己的資料庫配置。
 5. 輸入：`php artisan key:generate`
 6. 輸入：`php artisan migrate`
 7. 希望您有好的使用體驗。
 
 
A user daily check-in and point-rewarding system
===
Basic features
- It comes with admin and user system.
- User register function.
- User login function.
- User personal data update function.
- User logout function.
- Except for register function, other services are only accessible after logged in.

For admin only
 - Show the check-in breakdown for every user today.
 - Show designated user's check-in breakdown this month
 - Show how many days the designated user has checked in consecutively, and its total reward points
 - Show the breakdown of consecutively checking in and reward-points for all registered users.

For users
 - Check in today function
 - If the user has checked in today, return the corresponding message.
 - Show user's check-in breakdown this month.
 - When checking in, how many days the user has checked in consecutively will be automatically calculated and shown.
 - When checking in, reward-points will be calculated according to how many days the user has checked in consecutively, and shown to the user.
 
 installing instruction (command line):
 1. git clone `git@github.com:tn710617/checkinSystem.git`
 `cd checkinSystem`
 2. Enter `composer install` 
 3. Create a database. 
 4. Enter `cp .env.example .env`
, and replace the parameters with your database setting.
 5. Enter `php artisan key:generate`   
 6. Enter `php artisan migrate`
 7. Hope that you will be enjoying it.
