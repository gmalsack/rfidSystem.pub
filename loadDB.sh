#!/bin/bash
# create ram disk
if [ ! -f /mnt/sqlite/rfid.db ]; then
	mount -t tmpfs -o size=5m tmpfs /mnt/sqlite
fi

# create mysql export file
mysqldump -h [dbHost] -u [dbUser] -p[dbPass] [dbName] --skip-extended-insert --compact --ignore-table=rfidSystem.accessLog > /tmp/rfid_dump.sql

# import mysql export into sqlite3 database
/usr/local/bin/mysql2sqlite /tmp/rfid_dump.sql | sqlite3 /mnt/sqlite/rfid.db

# remove mysql export file
rm -R /tmp/rfid_dump.sql

# create sqlite3 access log
sqlite3 /mnt/sqlite/accessLog.db "CREATE TABLE accessLog (id integer PRIMARY KEY,keyFobID int(11) NOT NULL,empName varchar(45) NOT NULL,doorName varchar(45) NOT NULL,timestamp varchar(20) NOT NULL,result varchar(20) NOT NULL);"

# set permissions on access log database
chmod 666 /mnt/sqlite/accessLog.db
