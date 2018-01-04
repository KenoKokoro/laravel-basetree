<?php


namespace BaseTree\Console\Generators\Endpoint;


use BaseTree\Console\Generators\BaseGenerator;

class GenerateEndpoint extends BaseGenerator
{
    const OPTION_TYPE = 'type';
    const OPTION_FOLDER = 'folder';

    const TYPE_ERP = 'erp';
    const TYPE_DAL = 'dal';

    const ARGUMENT_NAME = 'name';
    const ARGUMENT_PLURAL = 'plural';

    const STUB_CONTROLLER = 'ControllerClass.stub';
    const STUB_RESOURCE = 'ResourceClass.stub';
    const STUB_REPOSITORY = 'RepositoryInterface.stub';
    const STUB_ERP = 'ErpClass.stub';
    const STUB_DAL = 'DalClass.stub';

    protected $name = "Helper";

    protected $signature = "generate:resource 
                            {" . self::ARGUMENT_NAME . " : Singular name of the wanted resource} 
                            {" . self::ARGUMENT_PLURAL . "? : Plural name of the wanted resource. This is useful if the resource doesn't have simple plural}
                            {--" . self::OPTION_TYPE . "= : Resource type. Allowed values are " . self::TYPE_ERP . " or " . self::TYPE_DAL . "}
                            {--" . self::OPTION_FOLDER . "='' : Folder for controller and resource}";

    protected $description = "Generate needed classes and interfaces for new resource on the API";

    private $type;

    private $enums = [
        self::OPTION_TYPE => [self::TYPE_ERP, self::TYPE_DAL]
    ];

    private $stubsPath;

    /**
     * @var Collection
     */
    private $settings;

    public function __construct()
    {
        parent::__construct();
        $this->settings = collect([]);
        $this->stubsPath = app_path('Console/Generators/Stubs/');
    }

    public function handle()
    {
        $this->settings = collect($this->settings($this->init()));
        $this->addController();
        $this->addResource();
        $this->addRepository();
    }

    private function init(): array
    {
        $this->validateType((string)$this->option(self::OPTION_TYPE));

        return $this->parseArguments((string)$this->argument(self::ARGUMENT_NAME),
            (string)$this->argument(self::ARGUMENT_PLURAL), (string)$this->option(self::OPTION_FOLDER));
    }

    private function addController()
    {
        /** @var Controller $controller */
        $controller = $this->settings->get('controller');
        /** @var \App\Console\Generators\Settings\Resource $resource */
        $resource = $this->settings->get('resource');
        $writeInfo = $this->writeFromStub(self::STUB_CONTROLLER, ['{controller}', '{resource}'],
            [$controller->className(), $resource->className()], $controller->file());

        $this->createdInfo("Controller created at: {$controller->file()}", $writeInfo);
    }

    private function addResource()
    {
        /** @var \App\Console\Generators\Settings\Resource $resource */
        $resource = $this->settings->get('resource');
        /** @var Repository $repository */
        $repository = $this->settings->get('repository');
        $writeInfo = $this->writeFromStub(self::STUB_RESOURCE, ['{resource}', '{repository}'],
            [$resource->className(), $repository->className()], $resource->file());

        $this->createdInfo("Resource created at: {$resource->file()}", $writeInfo);
    }

    private function addRepository()
    {
        # Interface
        /** @var Repository $repository */
        $repository = $this->settings->get('repository');
        $writeInfo = $this->writeFromStub(self::STUB_REPOSITORY, ['{repository}'], [$repository->className()],
            $repository->file());

        $this->createdInfo("Repository Interface created at: {$repository->file()}", $writeInfo);

        # Implementation
        /** @var Implementation $implementation */
        $implementation = $this->settings->get('implementation');
        $key = ($this->type == self::TYPE_ERP) ? self::STUB_ERP : self::STUB_DAL;
        $writeInfo = $this->writeFromStub($key, ['{repository}', '{implementation}'],
            [$repository->className(), $implementation->className()], $implementation->file());

        $this->createdInfo("Repository Implementation created at: {$implementation->file()}", $writeInfo);

        $serviceProvider = ($this->type == self::TYPE_ERP) ? ERPServiceProvider::class : DALServiceProvider::class;
        $this->line("########################################");
        $this->comment("Register the repository at \\{$serviceProvider} using:");
        $this->comment("{$repository->fullClass()}::class => {$implementation->fullClass()}::class");
    }

    private function validateType(string $type): void
    {
        if (empty($type)) {
            throw new RuntimeException('Not enough options (missing: "--' . self::OPTION_TYPE . '").');
        }

        if ( ! in_array($type, $enums = $this->enums[self::OPTION_TYPE])) {
            throw new InvalidArgumentException('--type value must be in ' . implode(', ', $enums));
        }

        $this->type = $type;
    }

    private function writeFromStub(string $key, array $search, array $replace, string $filePath): bool
    {
        if (File::exists($filePath)) {
            $this->error("{$filePath} already exists. Skipping. Ignore the bellow message");

            return false;
        }

        $content = File::get($this->stubsPath . $key);
        $content = str_replace($search, $replace, $content);

        File::put($filePath, $content);

        return true;
    }

    private function parseArguments(string $name, string $plural, string $subFolder): array
    {
        if (empty($plural)) {
            $plural = Str::plural($name);
        }

        return [$name, $plural, $subFolder];
    }

    private function settings(array $config): array
    {
        return [
            'controller' => new Controller($config),
            'resource' => new ResourceSettings($config),
            'repository' => new Repository($config),
            'implementation' => new Implementation($config, $this->type),
        ];
    }

    private function createdInfo(string $write, bool $writeInfo = true)
    {
        if ($writeInfo) {
            $this->info($write);
        }
    }

}