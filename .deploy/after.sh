# https://forums.rancher.com/t/how-to-specify-first-container-in-rancher-exec/8337/2
CONTAINER_ID=$(rancher ps -c | grep -i $1-app | cut -f 1 -d' ' | head -1)

function run() {
    echo "Running" $@
    script --return --command "rancher exec -it $CONTAINER_ID $@" /dev/null # ./output.txt

    # cat ./output.txt
    # rm -f ./output.txt
}

# Херачим миграции
curl "http://kgf.cic.kz/api/command?command=migrate&key=159753"
# run a view:cache

# Кешируем вьюхи
# run a view:cache
