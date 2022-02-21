<?php

namespace AbcDaConstrucao\AutenticacaoPackage\Console\Commands;

use AbcDaConstrucao\AutenticacaoPackage\Facades\ACL;
use Illuminate\Console\Command;

class SyncronizeRoutesCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'abc-auth:sync-routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza todas as rotas da aplicação com a API de autenticação/Autorização.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $time_start = microtime(true);

            $res = ACL::syncRoutes();

            $time_end = microtime(true);
            $execution_time = number_format((($time_end - $time_start) / 60), 2);
            $this->line('');
            dump($res);
            $this->line("Tempo de execução: {$execution_time} min(s)");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
