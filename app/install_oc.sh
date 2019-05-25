#!/bin/bash

/usr/bin/wget https://github.com/opencart/opencart/archive/$1.zip
if [ $? -eq 0 ]
then
    echo 1
  else
    echo 0

fi
