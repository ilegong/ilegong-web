<?php

if(extension_loaded('xhprof'))
{
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}


if(extension_loaded('xhprof')) {
    include_once __DIR__ . '/../lib/Xhprof_lib/utils/xhprof_lib.php';
    include_once __DIR__ . '/../lib/Xhprof_lib/utils/xhprof_runs.php';

    $objXhprofRun = new XHProfRuns_Default();
    $data = xhprof_disable();
    $run_id = $objXhprofRun->save_run($data, "xhprof");
    error_log($run_id.PHP_EOL , 3 , '/tmp/xhprof.log');
}
