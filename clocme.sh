 #!/bin/bash

echo "clocking"
now=`date +"%y%m%d_%H%M"`

cloc ./ --out=./clocreport.$now.txt --exclude-list-file=./clocexcl --exclude-ext=txt
