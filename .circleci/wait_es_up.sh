#!/bin/bash

loop=0
response=7 # node not available
wait=1

while [ $response != 0 ] && [ $loop != 5 ]; do
    curl -s -XHEAD http://localhost:9200
    response=$?
    loop=$(expr $loop + 1)
    wait=$(expr $wait * 2)
    sleep $wait
done

if [ $loop == 5 ]; then
    echo "elasticsearch is not available"
    exit 1;
else
    echo "elasticsearch is available, starting php test"
fi