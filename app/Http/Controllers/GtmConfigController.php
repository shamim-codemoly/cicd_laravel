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
        $port = $request->input('port');
        $email = $request->input('gtm_ssl_email');
        $config = $request->input('gtm_config_id'); // base64 or GTM-ID

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
}
