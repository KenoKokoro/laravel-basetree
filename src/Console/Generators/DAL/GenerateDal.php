<?php


namespace BaseTree\Console\Generators\DAL;


use BaseTree\Console\Generators\BaseGenerator;

class GenerateDal extends BaseGenerator
{
    const INTERFACE_STUB = 'StubDalInterface.stub';
    const DAL_STUB = 'StubDal.stub';

    const OPTION_MODEL = 'model';
    const OPTION_INTERFACE_FOLDER = 'interface-folder';
    const OPTION_INTERFACE_NAMESPACE = 'interface-namespace';
    const OPTION_DAL_FOLDER = 'dal-folder';
    const OPTION_DAL_NAMESPACE = 'dal-namespace';

    protected $signature = "basetree:dal
                            {--" . self::OPTION_MODEL . "= : Fully qualified model name including the namespace}
                            {--" . self::OPTION_INTERFACE_FOLDER . "=app/DAL/[model-name] : Folder where to create the DAL interface}
                            {--" . self::OPTION_INTERFACE_NAMESPACE . "=App\DAL\[model-name] : Namespace to create the DAL interface under}
                            {--" . self::OPTION_DAL_FOLDER . "=app/DAL/[model-name] : Folder where to create the DAL implementation}
                            {--" . self::OPTION_DAL_NAMESPACE . "=App\DAL\[model-name] : Namespace to create the DAL implementation under}";

    protected $description = "Generate Data Access Layer interface with implementation";

    protected $stubPath = __DIR__ . '/';

    public function __construct()
    {
        parent::__construct();
    }

    protected function go()
    {
        $this->createInterface($this->option(self::OPTION_INTERFACE_FOLDER));
        $this->createImplementation($this->option(self::OPTION_DAL_FOLDER));
        $this->comment("");
        $this->comment("NOTE: Do not forget to bind this in your service provider!");
    }

    protected function extractModifiers()
    {
        [$modelName, $modelNamespace] = $this->extractParentDependency($this->option(self::OPTION_MODEL));

        $modifiers = [
            parent::KEY_MODEL_NAME => $modelName,
            parent::KEY_MODEL_NAMESPACE => $modelNamespace,
            parent::KEY_DAL_INTERFACE_NAMESPACE => $this->returnNamespace($this->option(self::OPTION_INTERFACE_NAMESPACE)),
            parent::KEY_DAL_INTERFACE_NAME => "{$modelName}Repository",
            parent::KEY_DAL_NAMESPACE => $this->returnNamespace($this->option(self::OPTION_DAL_NAMESPACE)),
            parent::KEY_DAL_NAME => "Eloquent{$modelName}",
        ];
        $this->setModifiers($modifiers, true);
    }

    protected function createInterface(string $folder)
    {
        $stub = $this->stubPath . self::INTERFACE_STUB;
        $file = $this->writeFromStub($stub, base_path($this->modify($folder)), parent::KEY_DAL_INTERFACE_NAME);
        if ( ! empty($file)) {
            $this->info("DAL Interface created: {$file}");
        }
    }

    protected function createImplementation(string $folder)
    {
        $stub = $this->stubPath . self::DAL_STUB;
        $file = $this->writeFromStub($stub, base_path($this->modify($folder)), parent::KEY_DAL_NAME);
        if ( ! empty($file)) {
            $this->info("DAL Implementation created: {$file}");
        }
    }
}