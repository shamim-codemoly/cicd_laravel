<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GtmConfigController extends Controller
{
    public function gtmServerContainerConfig(Request $request)
    {
        $domain = $request->input('gtm_sub_domain_name');
        $port = $this->getNextAvailablePort();
        $email = $request->input('gtm_ssl_email');
        $config = $request->input('gtm_config_id'); // base64 or GTM-ID

        //     GtmDeployment::create([
        // 'shop_id' => getUserShopId(),
        //     'domain' => $domain,
        //     'email' => $email,
        //     'config' => $config,
        //     'port' => $port,
        // ]);

        $scriptPath = base_path('deploy.sh');
        $process = new Process([$scriptPath, $domain, $port, $email, $config]);
        $process->setTimeout(300); // 5 minutes
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }


        return response()->json([
            'message' => 'Deployed successfully!',
            'output' => $process->getOutput()
        ]);
    }

    function getNextAvailablePort($start = 5201, $end = 65000)
    {
        $usedPorts = collect(explode("\n", shell_exec("ss -tuln | awk '{print $5}' | grep ':' | cut -d':' -f2")))
            ->filter()
            ->unique()
            ->map(fn($port) => intval(trim($port)));

        for ($port = $start; $port <= $end; $port++) {
            if (!$usedPorts->contains($port)) {
                return $port;
            }
        }

        throw new \Exception("No free ports available");
    }
}
