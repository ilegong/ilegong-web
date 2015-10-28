git status | grep modified | grep webroot/static | sed "s/.modified:   webroot\///g" | sed "N;s/\n/ /g"
