#! /bin/bash
# A modification of Dean Clatworthy's deploy script as found here: https://github.com/deanc/wordpress-plugin-git-svn
# plus some other script
#
# changes by NP
# 20150216
# * use rsync to add/remove files when syncing from git to svn
# * use checkedout version of svn

# error out in case of problems
set -e

# main config
PLUGINSLUG="piwigopress"
CURRENTDIR=`pwd`
MAINFILE="piwigopress.php" # this should be the name of your main php file in the wordpress plugin

# git config
GITPATH="$CURRENTDIR/" # this file should be in the base of your git repository

# svn config
SVNPATH="$CURRENTDIR/../${PLUGINSLUG}-wordpress.svn" # path to a temp SVN repo. No trailing slash required and don't add trunk.
SVNURL="http://plugins.svn.wordpress.org/${PLUGINSLUG}/" # Remote SVN repo on wordpress.org, with no trailing slash
SVNUSER="norbusan" # your svn username


# Let's begin...
echo ".........................................."
echo 
echo "Preparing to deploy wordpress plugin"
echo 
echo ".........................................."
echo 

# Check if subversion is installed before getting all worked up
if [ $(dpkg-query -W -f='${Status}' subversion 2>/dev/null | grep -c "ok installed") != "1" ]
then
	echo "You'll need to install subversion before proceeding. Exiting....";
	exit 1;
fi

# We are using rsync to delete unused files
if [ $(dpkg-query -W -f='${Status}' rsync 2>/dev/null | grep -c "ok installed") != "1" ]
then
	echo "You'll need to install rsync before proceeding. Exiting....";
	exit 1;
fi

# make sure that there are no uncommitted changes in he subversion repo
if [ "`svn status $SVNPATH`" != "" ] 
then
	echo "svn repo in $SVNPATH is not clean, first commit/revert changes there. Exiting ..."
	exit 1
fi



# version checks have been moved into Makefile
make version-check
# we still need NEWVERSION1 for setting!

NEWVERSION1=`grep "^Stable tag:" $GITPATH/readme.txt | awk -F' ' '{print $NF}'`

echo "All versions match. Let's proceed..."


if git show-ref --tags --quiet --verify -- "refs/tags/$NEWVERSION1"
	then 
		echo "Version $NEWVERSION1 already exists as git tag. Exiting...."; 
		exit 1; 
	else
		echo "Git version does not exist. Let's proceed..."
fi


cd $GITPATH
echo "Tagging new version in git"
git tag -a "$NEWVERSION1" -s -m "Tagging version $NEWVERSION1"

echo "Pushing latest commit to origin, with tags"
git push 
git push --tags

tmpd=`mktemp -d`
echo "Exporting the HEAD of master from git to temp direcory $tmpd"
# don't forget the trailing slash!!!
git checkout-index -a -f --prefix=$tmpd/

echo "Updating svn repository"
svn up $SVNPATH

echo "syncing temp directory into trunk"
rsync -av --delete $tmpd/ $SVNPATH/trunk/

#echo "Ignoring github specific files and deployment script"
#svn propset svn:ignore "deploy.sh
#README.md
#.git
#.gitignore" "$SVNPATH/trunk/"

echo "Changing directory to SVN and committing to trunk"
cd $SVNPATH/trunk/
# Add all new files that are not set to be ignored
# don't run when no files are added/removed - this is a GNU extension!
svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2}' | xargs --no-run-if-empty svn add
# remove deleted files
svn status | grep -v "^.[ \t]*\..*" | grep "^!" | awk '{print $2}' | xargs --no-run-if-empty svn rm

echo -e "SVN commit: enter a commit message: \c"
read COMMITMSG
svn commit --username=$SVNUSER -m "$COMMITMSG"

echo "Creating new SVN tag & committing it"
cd $SVNPATH
svn copy trunk/ tags/$NEWVERSION1/
cd $SVNPATH/tags/$NEWVERSION1
svn commit --username=$SVNUSER -m "Tagging version $NEWVERSION1"


echo "Removing temp dir"
if [ -d ${tmpd} ]
then
	rm -rf $tmpd
fi

echo "*** FIN ***"
