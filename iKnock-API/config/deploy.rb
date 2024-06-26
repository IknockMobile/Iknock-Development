# config valid for current version and patch releases of Capistrano
lock "~> 3.17.3"

set :application, "reiknock"
set :repo_url, "git@github.com:semaphoremobile/iKnock-API.git"
set :keep_releases, 10
set :default_stage, 'staging'

# The version of laravel being deployed
set :laravel_version, 8.0

set :laravel_upload_dotenv_file_on_deploy, false

# Default branch is :master
#set :branch, `git rev-parse --abbrev-ref HEAD`.chomp
set :branch, ENV.fetch('REVISION', 'master')

# Default deploy_to directory is /var/www/my_app_name
# set :deploy_to, "/var/www/my_app_name"

# Default value for :format is :airbrussh.
# set :format, :airbrussh

# You can configure the Airbrussh format using :format_options.
# These are the defaults.
# set :format_options, command_output: true, log_file: "log/capistrano.log",color: :auto, truncate: :auto

# Default value for :pty is false
# set :pty, true

# Default value for :linked_files is []
#append :linked_files, "config/database.php"
append :linked_files, ".env", "storage/oauth-private.key", "storage/oauth-public.key"

append :linked_dirs, "public/uploads"
append :linked_dirs, "public/thumbnail"
append :linked_dirs, "vendor"
#append :linked_dirs, "vendor/crocodicstudio"
# Default value for linked_dirs is []
#append :linked_dirs, "images", "storage/app/public/images"
#append :linked_dirs, "vendor", "vendor"

# Default value for default_env is {}
# set :default_env, { path: "/opt/ruby/bin:$PATH" }

# Default value for local_user is ENV['USER']
# set :local_user, -> { `git config user.name`.chomp }

# Default value for keep_releases is 5
# set :keep_releases, 5

# Uncomment the following to require manually verifying the host key before first deploy.
# set :ssh_options, verify_host_key: :secure

namespace :deploy do
    after "deploy:finished", :composer_update
    after "deploy:finished", :php_fpm_restart
    after "deploy:finished", :setup_group
end