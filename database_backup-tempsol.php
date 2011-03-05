<?php
    /*
     * Backup Script v1.0
     *
     * Script made by Chris Engstrom
     *      Purpose: Create a backup of each of our databases individually and
     *               save them to the server via a cron job to be downloaded at
     *               a later date.
     *
     * Updates needed for v2.0
     *      Install the libssh2 library on the server and implement the sftp
     *      for this script.
     *
     * Crontab entry for later reference:
     *      0 4 * * * nice -n 15 /usr/local/bin/php /usr/www/users/USER/PATH_TO_FILE/database_backup-tempsol.php
     */

    define('NL', "\n");

    echo 'COMPANY: Database Backup Script Output' . NL;

    // Define the host, username, and that users password
    $dbhost = 'localhost';
    $dbuser = 'MYSQL_BACKUP_USER';
    $dbpass = 'MYSQL_BACKUP_USER_PASS';

    // Set up the file that it is going to save the gzipped databases to
    $file = '/usr/home/LINUX_USER/database_backup/mysql_backup_' . date("Y-m-d");

    // Open a mysql connection with the above info, if it can't
    //  open the connection it displays an error message
    $conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');


    $i = 0;
    $db_array = array();

    $result = mysql_list_dbs($conn);

    // Put the database names into $db_array
    while($row = mysql_fetch_object($result))
    {
        // Put the $result item into the $db_array
        $db_array[$i] = $row->Database;

        $i++;
    }


    echo 'Going to try to create ' . $file . NL;

    // Check to see if the file already exists
    if (file_exists($file))
    {
      echo 'Directory already exists.' . NL;
    }
    else
    {
        // Create the directory to put the gzipped backups in
        mkdir($file);
        echo 'Directory created.' . NL;


        // Store the number of databases to be backed up
        $db_count = count($db_array);
        // Print the number of databases to be backed up
        echo $db_count . ' databases to backup' . NL;

        // gzip all the databases individually and save them on our server
        $i = 0; // Variable for the index of $db_array
        $j = 0; // Variable to hold the number of successful backups
        while($i < $db_count)
        {
            $dbname = $db_array[$i];

            // Set up the name of the backup file
            $backupFile = $file . '/' . $dbname . '_backup_' . '.sql.gz';

            // Build the command to send to mysql
            $command = "/usr/local/bin/mysqldump  -u$dbuser -h$dbhost --password=$dbpass $dbname | gzip > $backupFile";

            // Execute the command we built previously and check to see if it executed successfully.
            if(!system($command))
            {
                echo $dbname . ' backed up successfully. ' .  ($i + 1) .  ' of ' . $db_count . NL;

                // Increment successful command counter
                $j++;
            }
            else
            {
                echo 'ERROR- ' . $dbname . ' did not backup successfully! ' .  ($i + 1) .  ' of ' . $db_count . NL;
            }

            // Increment array counter
            $i++;
        }

        // Tell the user/console how many databases successfully backed up
        echo $j . ' of ' . $db_count . ' databases were successfully backed up to the server.' . NL;
    }

    // Close the mysql connection
    mysql_close($conn);
?>
