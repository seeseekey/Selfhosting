#!/bin/sh

# Script for upgrade
# by seeseekey (https://seeseekey.net)
# licensed under MIT license

apt autoremove -y && apt autoclean -y && apt update && apt full-upgrade -y && apt autoremove -y && apt autoclean -y
snap refresh
