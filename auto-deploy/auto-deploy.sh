##########################################################
################### DECLARE VARIABLES ####################

PARAMETER_SSH_KEY_PATH=$1;
PARAMETER_WEBSITE_PATH=$2;

################ END DECLARE VARIABLES ###################
##########################################################

eval "$(ssh-agent -s)";
ssh-add $PARAMETER_SSH_KEY_PATH;

#################### DEPLOY MASTER #######################

cd $PARAMETER_WEBSITE_PATH;

git fetch origin develop-test;

COUNT=$(git rev-list HEAD...origin/develop-test --count);

if [ "$COUNT" -ge  "1" ]; then
    (
		echo
		echo $SEPARATOR" "$DATE_DEPLOY" "$SEPARATOR
		echo

		git pull origin develop-test
		echo

	) >> $PARAMETER_WEBSITE_PATH/git-log.txt

fi

################### END DEPLOY MASTER ####################

eval "$(ssh-agent -k)"

exit