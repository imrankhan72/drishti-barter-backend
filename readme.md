##Go to controllers directory
cd app/Http/Controllers/
##Rename file 
mv PersonProductController.php PersonProductController_old.php
##Download new file 
wget https://raw.githubusercontent.com/imrankhan72/drishti-barter-backend/master/app/Http/Controllers/PersonProductController.php
##Delete old file
rm PersonProductController_old.php
##Show git remote repository URL
1) git remote -v
2) git remote show origin
## Git Pull
