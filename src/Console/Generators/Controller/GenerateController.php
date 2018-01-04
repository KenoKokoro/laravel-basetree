<?php


namespace BaseTree\Console\Generators\Controller;


use BaseTree\Console\Generators\BaseGenerator;

class GenerateController extends BaseGenerator
{
    const STUB = 'StubController.stub';

    const OPTION_BLL = 'bll';
    const OPTION_MODEL_PLURAL = 'model-plural';

    protected $signature = "generate:base-tree-controller
                            {--" . self::OPTION_MODEL_PLURAL . "= : Plural form of the model name. For instance if the model is User, you should send here Users}
                            {--" . self::OPTION_BLL . "= : Fully qualified business logic layer name including namespace}
                            {--" . parent::OPTION_FOLDER . "=app/Http/Controllers/Api/ : Folder where to create the controller}
                            {--" . parent::OPTION_NAMESPACE . "=App\Http\Controllers\Api : Namespace to create the controller under}";

    protected $description = "Generate Business Logic Layer class";

    protected $stubPath = __DIR__ . '/';

    public function __construct()
    {
        parent::__construct();
    }

    protected function go()
    {
        $folder = $this->option(parent::OPTION_FOLDER);
        $stub = $this->stubPath . self::STUB;
        $file = $this->writeFromStub($stub, base_path($this->modify($folder)), parent::KEY_CONTROLLER_NAME);
        $this->info("Controller created: {$file}");
    }

    protected function extractModifiers()
    {
        [$bllName, $bllNamespace] = $this->extractParentDependency($this->option(self::OPTION_BLL));
        $controllerName = ucfirst($this->option(self::OPTION_MODEL_PLURAL)) . "Controller";

        $modifiers = [
            parent::KEY_BLL_NAME => $bllName,
            parent::KEY_BLL_NAMESPACE => $bllNamespace,
            parent::KEY_CONTROLLER_NAME => $controllerName,
            parent::KEY_CONTROLLER_NAMESPACE => $this->returnNamespace($this->option(parent::OPTION_NAMESPACE))
        ];

        $this->setModifiers($modifiers, true);
    }
}