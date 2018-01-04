<?php


namespace BaseTree\Console\Publishers;


use File;
use Illuminate\Console\Command;

class PublishBoilerplate extends Command
{
    const OPTION_DOCKER = 'docker-compose';

    protected $signature = "publish:base-tree
                            {--" . self::OPTION_DOCKER . " : Publish the docker structure}";

    protected $description = "Publish some already predefined environments.
    --docker-compose: Docker environment for local development (nginx 1.13, php7.1-fpm + composer, npm 3.3, nodejs 6.7, MariaDB 10.3, phpmyadmin 4.7)";

    protected $path = __DIR__ . '/../../../publish/';

    public function handle()
    {
        if ($this->option(self::OPTION_DOCKER)) {
            $this->publishDockerCompose();
        }
    }

    protected function publishDockerCompose()
    {
        $path = $this->path . self::OPTION_DOCKER;
        File::copyDirectory($path, base_path());
        $this->info("Docker compose structure is published.");
        $this->info("Please set your DOCKER_HOST_UID=1000 in your .env file. (You can find your UID using echo \$UID in your terminal)");
        $this->info("Please set your DOCKER_HOST_GID=1000 in your .env file. (You can find your GID using echo \$GID in your terminal)");
        $this->info("Set your DB_HOST=mariadb in your .env file");
        $this->info("Set your database credentials in your .env file, so the container can set them accordingly");
        $this->comment("NOTE: If you modify this variables after the container is created, You will have to rebuild it using:");
        $this->comment("docker-compose down");
        $this->comment("docker-compose up -d --build");
        $this->info("After this is all set, you can start the container using:");
        $this->comment("docker-compose up -d");
    }
}