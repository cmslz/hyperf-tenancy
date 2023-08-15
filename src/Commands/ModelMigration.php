<?php

namespace Cmslz\HyperfTenancy\Commands;

use Cmslz\HyperfTenancy\Concerns\HasATenantsOption;
use Cmslz\HyperfTenancy\Kernel\Tenancy;
use Hyperf\CodeParser\Project;
use Hyperf\Database\Commands\Ast\ModelRewriteConnectionVisitor;
use Hyperf\Database\Commands\Ast\ModelUpdateVisitor;
use Hyperf\Database\Commands\ModelCommand;
use Hyperf\Database\Commands\ModelData;
use Hyperf\Database\Commands\ModelOption;
use Hyperf\Database\Model\Model;
use Hyperf\Stringable\Str;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

use function Hyperf\Support\make;

class ModelMigration extends ModelCommand
{
    use HasATenantsOption;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct($container);
        $this->setDescription('Create new model classes by tenant.');
        parent::setName('tenants:model');
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption('tenants', null, InputOption::VALUE_REQUIRED, 'Which tenant');
    }

    /**
     * Build the class with the given name.
     */
    protected function buildClass(string $table, string $name, ModelOption $option): string
    {
        $stub = file_get_contents(__DIR__ . '/stubs/Model.stub');

        return $this->replaceNamespace($stub, $name)
            ->replaceInheritance($stub, $option->getInheritance())
            ->replaceUses($stub, $option->getUses())
            ->replaceClass($stub, $name)
            ->replaceTable($stub, $table);
    }

    protected function createModel(string $table, ModelOption $option)
    {
        $builder = $this->getSchemaBuilder($option->getPool());
        $table = Str::replaceFirst($option->getPrefix(), '', $table);
        $columns = $this->formatColumns($builder->getColumnTypeListing($table));
        $project = new Project();
        $class = $option->getTableMapping()[$table] ?? Str::studly(Str::singular($table));
        $class = $project->namespace($option->getPath()) . $class;
        $path = BASE_PATH . '/' . $project->path($class);
        if (! file_exists($path)) {
            $this->mkdir($path);
            file_put_contents($path, $this->buildClass($table, $class, $option));
        }
        $columns = $this->getColumns($class, $columns, $option->isForceCasts());
        $traverser = new NodeTraverser();
        $traverser->addVisitor(make(ModelUpdateVisitor::class, [
            'class' => $class,
            'columns' => $columns,
            'option' => $option,
        ]));
        $data = make(ModelData::class, ['class' => $class, 'columns' => $columns]);
        foreach ($option->getVisitors() as $visitorClass) {
            $traverser->addVisitor(make($visitorClass, [$option, $data]));
        }
        $traverser->addVisitor(new CloningVisitor());

        $originStmts = $this->astParser->parse(file_get_contents($path));
        $originTokens = $this->lexer->getTokens();
        $newStmts = $traverser->traverse($originStmts);
        $code = $this->printer->printFormatPreserving($newStmts, $originStmts, $originTokens);
        file_put_contents($path, $code);
        $this->output->writeln(sprintf('<info>Model %s was created.</info>', $class));

        if ($option->isWithIde()) {
            $this->generateIDE($code, $option, $data);
        }
    }

    public function handle()
    {
        Tenancy::runForMultiple(Tenancy::baseDatabase(), function ($tenant) {
            $this->line("Tenant: {$tenant['id']}");
            if (empty($this->input->getOption('inheritance'))) {
                $this->input->setOption('inheritance', '\\' . Model::class);
            }
            $this->input->setOption('pool', Tenancy::tenancyDatabase($tenant['id']));
            $this->input->setOption('with-comments', true);
            parent::handle();
        });
    }
}