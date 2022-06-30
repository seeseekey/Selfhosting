#!/bin/sh

# Script for upgrade
# by seeseekey (https://seeseekey.net)
# licensed under MIT license

apt autoremove -y && apt autoclean -y && apt update -y && apt dist-upgrade -y && apt autoremove -y && apt autoclean -y
snap refresh