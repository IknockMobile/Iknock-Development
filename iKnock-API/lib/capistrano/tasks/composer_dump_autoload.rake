desc "Composer Dump Autoload"
task :composer_dump_autoload do
  on roles :all do
    execute "cd #{deploy_to}/current && composer dump-autoload"
  end
end