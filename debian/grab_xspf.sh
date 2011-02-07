#!/bin/sh
############################################################################
#Due to the fact that there are no free tools to compile xspf_jukebox at
#build time in Debian/Ubuntu, xspf_jukebox was removed from the Amapche
#package.  This script downloads and links the flash player so Ampache can
#use it.
############################################################################

set -e

tmp="/usr/share/ampache/tmp"
xspf_dir="/usr/share/ampache/www/modules/flash"
swf="/usr/share/ampache/www/modules/flash/source/xspfjukebox/xspf_jukebox.swf"
amp="/usr/share/ampache/www/modules/flash/xspf_jukebox.swf"

#create out temp dir and grab the source
if [ -e $xspf_dir -a -e /etc/ampache ]; then
	mkdir $tmp
	cd $tmp
	wget http://lacymorrow.com/projects/jukebox/source.zip
	if [ -e $tmp/source.zip ]; then
		unzip $tmp/source.zip
		mv $tmp/xspf* $tmp/xspfjukebox
		mv $tmp/xspfjukebox/xspf_jukebox.swf $amp
	fi
	if [ -e $amp ]; then
		rm -rf $tmp
	fi
else
	printf "The Ampache package is not installed"
	exit 1
fi

exit 0
