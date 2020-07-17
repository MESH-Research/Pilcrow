if [ ! -f "./.env" ]; then
    echo "No ./.env file found, preparing initial setup."
    cat <<EOE >> ./.env
APP_NAME=CCR Local Development
APP_ENV=local
APP_DEBUG=true
APP_HOSTNAME=ccr.local
APP_URL=https://ccr.local

LOG_CHANNEL=stack

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=homestead
DB_USERNAME=homestead
DB_PASSWORD=secret
EOE
fi


echoR() {
    tput setaf 1; echo "$@"; tput setaf 7
}

echoG() {
    tput setaf 2; echo "$@"; tput setaf 7
}

checkRequirements() {
    echo -n "Checking docker is installed ... "
    docker -v 2>&1 > /dev/null
    if [ $? -ne 0 ]; then
        echoR "fail."
        echoR "----"
        echoR "Docker is required to start a development environment."
    fi
    echoG "OK"
    echo -n "Checking port 443 is available ... "
    nc -z 127.0.0.1 443
    if [ $? -eq 0 ]; then
        echoR "fail"
        echoR "-----"
        echoR "Port 443 appears to be in use.  Close any programs / containers that may be using this port and try again."
        exit 1
    fi
    echoG "OK"
}

startMysqlandWait() {
    echo "Starting MySQL container."
    docker-compose start mysql
    echo -n "Waiting for mysql container to be ready ..."
    MYSQL_WAITING=1
    TIMEOUT=$(($(date +%s) + 30))
    while [ "$MYSQL_WAITING" -ne "0" ] && [ "$(date +%s)" -lt "$TIMEOUT" ]; do
        docker-compose exec mysql mysql -u${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE} -e "select 1;" 2>&1 > /dev/null
        MYSQL_WAITING=$?
        sleep 1
        echo -n "."
    done
    if [ "$MYSQL_WAITING" -ne "0" ]; then
        echoR " timeout."
        echoR "----"
        echoR "MySQL container did not start within 30 seconds, aborting"
        exit 1
    fi
    echoG " ready!"
}

checkDbEmpty() {
    local NUMTABLES=$(docker-compose exec mysql mysql -u${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE} -s -N -e "SELECT COUNT(DISTINCT 'table_name') FROM information_schema.columns WHERE table_schema = 'homestead'" | grep -v "Warning")
    if [ "${NUMTABLES:0:1}" -eq "0" ]; then
        return 1
    else
        return 0
    fi
}
startNginxandWait() {
    docker-compose start nginx
    NGINX_WAITING=1
    TIMEOUT=$(($(date +%s) + 60))
    echo -n "Waiting for nginx to be ready ..."
    
    while [ "$NGINX_WAITING" -ne "0" ] && [ "$(date +%s)" -lt "$TIMEOUT" ]; do
        if [ -z `docker-compose ps -q nginx` ] || [ -z `docker ps -q --no-trunc | grep $(docker-compose ps -q nginx)` ]; then
            echoR " fail"
            echoR "-----"
            echoR "Nginx service failed to start (check container logs for details or run docker-composer up nginx)"
            exit 1
        fi
        echo -n "."  
        docker-compose exec nginx /bin/bash -c "(echo >/dev/tcp/localhost/443) &>/dev/null"; NGINX_WAITING=$?
        sleep 1
    done
    if [ "$NGINX_WAITING" -ne "0" ]; then
        echoR "timeout."
        echoR "-----"
        echoR "Nginx service failed to start in time (check the container logs for details or run docker-compose up nginx)"
    fi
    echoG " ready!"

}
startNodeandWait() {
    docker-compose start node
    NODE_WAITING=1
    TIMEOUT=$(($(date +%s) + 300))
    echo -n "Waiting for node dev server to be ready (this could take a minute or two) ..."
    while [ "$NODE_WAITING" -ne "0" ] && [ "$(date +%s)" -lt "$TIMEOUT" ]; do
        sleep 5
        if [ -z `docker-compose ps -q node` ] || [ -z `docker ps -q --no-trunc | grep $(docker-compose ps -q node)` ]; then
            echoR " fail"
            echoR "-----"
            echoR "Node service failed to start (check the node container logs for details or run docker-composer up node)"
            exit 1
        fi
        echo -n "."  
        docker-compose exec node /bin/bash -c "(echo >/dev/tcp/localhost/8080) &>/dev/null"; NODE_WAITING=$?
    done
    if [ "$NODE_WAITING" -ne "0" ]; then
        echoR "timeout."
        echoR "-----"
        echoR "Node service failed to start in time (check the container logs for details or run docker-compose up node"
    fi
    echoG " ready!"
}



    export $(egrep -v '^#' .env | xargs)
    #Check that docker is installed and that the nginx port is available
    checkRequirements
    
    #Build and create containers
    docker-compose build --pull --parallel -q
    if [ "$?" -ne "0" ]; then
        echoR "Build failed"
        exit 1
    fi
    docker-compose up --no-start

    #Start the mysql container and wait for it to be ready.
    startMysqlandWait

    docker-compose start phpfpm
    echo "Loading dependencies."

    docker-compose exec -T phpfpm composer install 2>&1 | ts '[php] ' &
    docker-compose run -T --rm node yarn install 2>&1 | ts '[node] ' &
    wait
    docker-compose up -d 

    checkDbEmpty; DBEMPTY=$?

    if [ "$DBEMPTY" -eq "1" ]; then
        echo -n "Empty database, migrating ... "
        docker-compose exec phpfpm php artisan migrate > /dev/null
        if [ "$?" -ne "0" ]; then
            echoR " fail."
            echoR "-----"
            echoR "Error while migrating initial database."
            exit 1
        fi
        echoG "done"
    fi    

    if [ -z "$APP_KEY" ]; then
        echo -n "Generating new app key ... "
        APP_KEY=$(docker-compose exec phpfpm php artisan key:generate --show --no-ansi | tr -d '\n')
        echo "APP_KEY=${APP_KEY}" >> ./.env
        echoG "done"
    fi

    startNodeandWait

    startNginxandWait

    echo 
    echo
    echoG "Development environment started!"

    echoG "Goto https://ccr.local/ in your browser"
    echoG "------"
    echoG "To stop the development environment run: docker-compose stop"


