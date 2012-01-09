set :application, "FWMLabs"
set :domain,      "flywithmonkey.biz"
set :deploy_to,   "/var/www/craftycomponents.com"

set :repository,  "git@github.com:ahilles107/CraftyComponents.git"
set :scm,         :git
# Or: `accurev`, `bzr`, `cvs`, `darcs`, `subversion`, `mercurial`, `perforce`, `subversion` or `none`

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain                         # This may be the same as your `Web` server
role :db,         domain, :primary => true       # This is where Rails migrations will run

set :shared_files,      ["app/config/parameters.ini"]
set  :keep_releases,  3
set :user, "root"