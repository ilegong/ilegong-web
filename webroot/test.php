<?php

if(extension_loaded('xhprof'))
{
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}

function bar($x) {
    if ($x > 0) {
        bar($x - 1);
    }
}

function foo() {
    for ($idx = 0; $idx < 5; $idx++) {
        bar($idx);
        $x = strlen("abc");
    }
}

// start profiling
xhprof_enable();

// run program
foo();

// stop profiler
$xhprof_data = xhprof_disable();

// display raw xhprof data for the profiler run
print_r($xhprof_data);

include_once __DIR__ . '/../lib/Xhprof_lib/utils/xhprof_lib.php';
include_once __DIR__ . '/../lib/Xhprof_lib/utils/xhprof_runs.php';

$xhprof_runs = new XHProfRuns_Default();

// save the run under a namespace "xhprof_foo"
$run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_foo");

die;
if(extension_loaded('xhprof')) {
    include_once __DIR__ . '/../lib/Xhprof_lib/utils/xhprof_lib.php';
    include_once __DIR__ . '/../lib/Xhprof_lib/utils/xhprof_runs.php';

    $objXhprofRun = new XHProfRuns_Default();
    $data = xhprof_disable();
    $run_id = $objXhprofRun->save_run($data, "xhprof");
    error_log($run_id.PHP_EOL , 3 , '/tmp/xhprof.log');
}
