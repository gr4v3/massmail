#!/bin/bash
# execs a php script continuously or a specific number of times
# args are the php file, the number of times to execute it (0 is forever) and an optional wait time between cycles

# config
MAILTO="fmenezes.tc@hotmail.com"
PHP_BIN=""

# check if we got a php file
if [ -z "$1" ]; then
        printf "\nMissing PHP script to run!\n\n"
        printf "Sintax: exec_loop PHP_FILE [times_to_run] [sleep_time]\n"
        printf "Ex: exec_php_loop \"/home/scripts/do_fake.php arg1 arg2\" 10 5\n\n"
        exit 192
fi

# function to send notification email (args: 1 - subject, 2 - message)
function send_message ()
{
        if [ -n $MAILTO ]; then
                `echo "$2" | mail -s "$1" $MAILTO`
        fi
}

# args
PHPSCRIPT=$1
TIMES=${2-0}
SLEEP=${3-0}
CMD="$PHP_BIN $PHPSCRIPT"
SEP="\n--------------------------------------------------------------------------"
while [ 1 ]
do
        CUR_TIME=`date +"%Y-%m-%d %k:%M:%S"`
        printf "$SEP\n$CUR_TIME - Processing script, $TIMES cycles to finish:$SEP\n"
        $CMD
        EXIT_STATUS="$?"
        CUR_TIME=`date +"%Y-%m-%d %k:%M:%S"`
        printf "$SEP\n$CUR_TIME - Processed with exit status $EXIT_STATUS."
        # abort if exit code is not zero
        if [ $EXIT_STATUS -ne 0 ]; then
                printf "$SEP\nExit status is not zero, aborting loop...\n"
                send_message "MailingXmanager : PHP Loop aborted" "Script $PHPSCRIPT returned error code $EXIT_STATUS"
                exit 1
        fi
        if [ $TIMES -gt 0 ]; then
                TIMES=$(($TIMES - 1))
                if [ $TIMES -eq 0 ]; then
                        printf "$SEP\nFinished Processing.\n"
                        exit 0
                fi
        fi
        if [ $SLEEP -gt 0 ]; then
                printf " Pausing for $SLEEP seconds..."
                sleep $SLEEP
        fi
        printf "$SEP\n"
done

