desc "Setup www-data group"
task :setup_group do
  on roles :all do 
    execute "sudo chown -R ubuntu:www-data #{deploy_to}"
  end
end