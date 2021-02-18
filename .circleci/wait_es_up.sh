#!/bin/bash

loop=0
endLoop=5
wait=1

# for some strange reason, HEAD request go in infinity loop ...
curl -s -XGET http://localhost:9200 > /dev/null
response=$?

while [ ${response} != 0 ] && [ ${loop} != ${endLoop} ]; do
    sleep ${wait}
    curl -s -XGET http://localhost:9200 > /dev/null
    response=$?
    loop=$(( $loop + 1))
    wait=$(( $wait*2))
done

if [ ${response} != 0 ]; then
    echo "elasticsearch is not available"
    exit 1;
else
    echo "elasticsearch is available, starting php test"
fi
