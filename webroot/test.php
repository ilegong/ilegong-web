<?php
xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
$data = xhprof_disable();

include_once __DIR__.'/../lib/Xhprof_lib/utils/xhprof_lib.php';
include_once __DIR__.'/../lib/Xhprof_lib/utils/xhprof_runs.php';

$objXhprofRun = new XHProfRuns_Default();

$run_id = $objXhprofRun->save_run($data, "xhprof");
var_dump($run_id);

die;