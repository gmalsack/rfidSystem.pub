# rfidSystem.pub
Code used to create an opensource RFID system

avea.php is the file that is placed in the webserver root and accessed by the web08s.

refreshDB.php is a cron job that uses the other files to perform the tasks needed to keep a controller
updated with the main mysql server and upload access log information.

all files (except avea.php) where in /usr/local/bin when the project was developed. If you place them elsewhere you'll
have to scrub each file to modify the path you've placed them in.
