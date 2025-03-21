<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Swoole\Process;

class ProcessProductQueue extends Command
{
    protected $signature = 'queue:process-products';
    protected $description = 'Processa a fila de produtos com Swoole';

    public function handle()
    {
        echo "Iniciando processamento com Swoole...\n";

        $workerCount = 1;

        for ($i = 0; $i < $workerCount; $i++) {
            $process = new Process(function () use ($i) {
                echo "Worker #$i iniciado...\n";
                $this->consumeQueue($i);
            });

            $process->start();
        }

        Process::wait(true);
    }

    /**
     * Consome mensagens da fila de forma eficiente.
     */
    private function consumeQueue($workerId)
    {
        $redis = Redis::connection();
        
        while (true) {
            $message = $redis->lpop('product_queue'); // Tenta pegar um item da fila
            if (!$message) {
                usleep(500000); // Aguarda 500ms se a fila estiver vazia
                continue;
            }

            $product = json_decode($message, true);

            echo "Worker #$workerId processando produto: {$product['name']}...\n";

            if (isset($product['quantity'], $product['minimum_quantity'], $product['company_id'])) {
                if ($product['quantity'] <= $product['minimum_quantity']) {
                    $this->notifyCompanyUsers($product);
                }
            }
        }
    }

    /**
     * Notifica os usuários da empresa sobre o produto.
     */
    private function notifyCompanyUsers(array $product)
    {
        $logMessage = sprintf(
            'Produto %s da empresa %d está com quantidade (%d) menor ou igual ao mínimo (%d).',
            $product['name'] ?? 'Desconhecido',
            $product['company_id'],
            $product['quantity'],
            $product['minimum_quantity']
        );

        Log::channel('product_notifications')->info($logMessage);
    }
}
