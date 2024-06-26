desc "Composer Update"
task :composer_update do
  on roles :all do 
    execute "cd #{deploy_to}/current && sudo composer update --no-interaction"
  end
end