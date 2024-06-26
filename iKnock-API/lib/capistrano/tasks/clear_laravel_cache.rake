desc "Clear the Laravel Cached Files"
task :clear_laravel_cache do
  on roles :all do 
    execute "cd #{deploy_to}/current && php artisan cache:clear"
  end
end
