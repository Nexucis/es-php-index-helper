#!/bin/bash

loop=0
response=7 # node not available

while [ $response != 0 ] && [ $loop != 5 ]; do
    curl -s -XHEAD http://localhost:9200
    response=$?
    loop=$(expr $loop + 1)
    sleep 1
done

if [ $loop == 5 ]; then
    echo "elasticsearch is not available"
    exit 1;
else
    echo "elasticsearch is available, starting php test"
fi