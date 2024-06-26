desc "Restart PHP FPM Service"
task :php_fpm_restart do
  on roles :all do 
    execute "cd #{deploy_to}/current && sudo systemctl restart php7.4-fpm.service"
  end
end
