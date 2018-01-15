<?php


namespace BaseTree\Console\Generators\BLL;


use BaseTree\Console\Generators\BaseGenerator;
use BaseTree\Resources\Contracts\ResourceCallbacks;
use BaseTree\Resources\Contracts\ResourceValidations;

class GenerateBll extends BaseGenerator
{
    const STUB = 'StubBll.stub';

    const OPTION_DAL = 'dal-interface';
    const OPTION_MODEL = 'model';

    protected $signature = "basetree:bll
                            {--" . self::OPTION_MODEL . "= : Fully qualified model name including namespace}
                            {--" . self::OPTION_DAL . "= : Fully qualified data access layer name including namespace}
                            {--" . parent::OPTION_FOLDER . "=app/BLL/ : Folder where to create the BLL}
                            {--" . parent::OPTION_NAMESPACE . "=App\BLL : Namespace to create the BLL under}";

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
        $file = $this->writeFromStub($stub, base_path($this->modify($folder)), parent::KEY_BLL_NAME);
        if ( ! empty($file)) {
            $this->info("BLL created: {$file}");
        }
        $this->comment("");
        $this->comment("NOTE: If you need validations, implement interface: " . ResourceValidations::class);
        $this->comment("NOTE: If you need callback actions, implement interface: " . ResourceCallbacks::class);
    }

    protected function extractModifiers()
    {
        [$dalName, $dalNamespace] = $this->extractParentDependency($this->option(self::OPTION_DAL));
        [$modelName, $modelNamespace] = $this->extractParentDependency($this->option(self::OPTION_MODEL));

        $modifiers = [
            parent::KEY_DAL_INTERFACE_NAME => $dalName,
            parent::KEY_DAL_INTERFACE_NAMESPACE => $dalNamespace,
            parent::KEY_BLL_NAMESPACE => $this->returnNamespace($this->option(parent::OPTION_NAMESPACE)),
            parent::KEY_BLL_NAME => "{$modelName}Resource",
        ];

        $this->setModifiers($modifiers, true);
    }
}