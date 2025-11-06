#!/bin/bash
# Posiable options to execute this script
# sudo bash ./deploy.sh
# sudo bash ./deploy.sh -t current 
# sudo bash ./deploy.sh -t other -b [branch_name] 
# sudo bash ./deploy.sh -t redeploy  
# sudo bash ./deploy.sh -t redeploy -s skip -m skip 
# Note: -s to skip seeder and -m to skip migration


# Read flag parameters as an input to the script
while getopts t:b:s:m: flag
do
    case "${flag}" in
        t) type=${OPTARG};;
        b) branch=${OPTARG};;
        s) seeder=${OPTARG};;
        m) migration=${OPTARG};;
    esac
done
if [[ $type == "current" ]]; then
    selected_option=1
elif [[ $type == "other" ]]; then
    selected_option=2
elif [[ $type == "redeploy" ]]; then
    selected_option=3
fi

repo_folder="/home/ec2-user/ibim-license-management/"
deploy_folder="/usr/share/nginx/ibim-license-management/"
do_deployment_process="false"
skip_seeder="false"
skip_migration="false"
if [[ $seeder == "skip" ]]; then
    skip_seeder="true"
fi
if [[ $migration == "skip" ]]; then
    skip_migration="true"
fi


cd $repo_folder
current_branch=$(git rev-parse --abbrev-ref HEAD)
echo "How to deploy? $type"
echo "[1] Pull and deploy the current branch ($current_branch)"
echo "[2] Checkout and deploy other branch"
echo "[3] Redeploy the existing code base"

if [[ $selected_option == "1" || $selected_option == "2" || $selected_option == "3" ]]; then
    echo "Selected option: $selected_option"
else
    echo "Enter option: "
    read selected_option
fi

if [[ $selected_option == "1" ]]; then
    echo "selected option: [1] Pull and deploy the current branch."

    if sudo git pull origin $current_branch
    then
        echo "Code pulled successfully."
        do_deployment_process="true"
    else
        echo "Code pull failed."
    fi

elif [[ $selected_option == "2" ]];
then
    echo "selected option: [2] Checkout and deploy other branch."
    if [ -z "$branch" ]
    then
        echo "Enter a branch name to checkout:"
        read branch_name
    else
        branch_name=$branch
    fi

    if sudo git checkout $branch_name
    then
        if sudo git pull origin $branch_name
        then
            echo "Code pulled successfully."
            do_deployment_process="true"
        else
            echo "Code pull failed."
        fi
    else
        echo "Checkout failed."
    fi
elif [[ $selected_option == "3" ]];
then
    echo "selected option: [3] Redeploy the existing code base."
    do_deployment_process="true"
else
    echo "Invalid option selected."
fi

if [[ $do_deployment_process == "true" ]];
then
    step_continue="true"
    echo "Deployment process initated."
    # Install/update composer dependecies
    #if /usr/local/bin/composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
    if /usr/local/bin/composer install
    then
        echo "1 - Composer completed."
    else
        step_continue="false"
        echo "1 - Composer failed."
    fi
    if [[ $step_continue == "true" && $skip_migration == "false" ]];
    then
        if php artisan migrate --force
        then
            echo "2 - Migration completed."
        else
            step_continue="false"
            echo "2 - Migration failed."
        fi
    fi
    if [[ $step_continue == "true" && $skip_seeder == "false" ]];
    then
        if php artisan db:seed
        then
            echo "3 - Seeder completed."
        else
            echo "3 - Seeder failed and retry initiated."
            if /usr/local/bin/composer dumpautoload
            then
                if php artisan db:seed
                then
                    echo "3.1 - Seeder retry completed."
                else
                    step_continue="false"
                    echo "3.1 - Seeder retry failed."
                fi
            fi
        fi
    fi
    if [[ $step_continue == "true" ]];
    then
        echo "Pre-deployment process is completed."
        if sudo rsync -avzh --exclude 'storage' $repo_folder $deploy_folder
        then
            echo "4 - File moved successfully."
            sudo chmod -R 777 $deploy_folder/storage/
            sudo systemctl restart supervisord
        else
            step_continue="false"
            echo "4 - File move failed."
            systemctl restart supervisord
        fi
    else
        echo "Pre-deployment process is failed."
    fi
    #sudo rsync -avzh --exclude 'storage' /home/ec2-user/ibim-license-management/ /usr/share/nginx/ibim-license-management/
    #sudo chmod -R 777 /usr/share/nginx/ibim-license-management/storage/
    #sudo rsync -avzh /home/ec2-user/dhana-backend/ /usr/share/nginx/ibim-license-management/
    # * * * * * cd /usr/share/nginx/ibim-license-management/ && php artisan schedule:run >> /dev/null 2>&1
    # sudo grep CRON /var/log/cron

    #sudo chmod -R 777 /usr/share/nginx/ibim-license-management/storage/
else
    echo "Deployment process not happend."
fi
# sudo bash ./deploy-backend.sh